<?php
// C:\xampp\htdocs\workshopos\controllers\business\StaffController.php

class StaffController {
    public static function index(): void {
        requireAuth();
        requirePermission('staff.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $staff = $db->query(
            "SELECT u.*, r.name as role_name, r.slug as role_slug
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id AND ur.business_id = ?
             JOIN roles r ON r.id = ur.role_id
             WHERE u.deleted_at IS NULL
             ORDER BY u.created_at DESC",
            [$businessId]
        );

        $roles = $db->query("SELECT * FROM roles WHERE scope = 'business' ORDER BY name ASC");

        $title = 'Staff & Team - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/staff/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    public static function invite(): void {
        requireAuth();
        requirePermission('staff.manage');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $roleId   = (int)($_POST['role_id'] ?? 0);
        $password = $_POST['password'] ?? 'Staff@123456';

        if (empty($name) || empty($email) || !$roleId) {
            Session::flash('error', 'All fields are required.');
            redirect(APP_URL . '/staff');
        }

        $existing = $db->queryOne("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            Session::flash('error', 'A user with this email already exists.');
            redirect(APP_URL . '/staff');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->beginTransaction();
        try {
            $db->execute(
                "INSERT INTO users (business_id, name, email, password, status, email_verified_at) VALUES (?, ?, ?, ?, 'active', NOW())",
                [$businessId, $name, $email, $hash]
            );
            $userId = (int)$db->lastInsertId();

            $db->execute(
                "INSERT INTO user_roles (user_id, role_id, business_id, assigned_by) VALUES (?, ?, ?, ?)",
                [$userId, $roleId, $businessId, Auth::id()]
            );

            $db->commit();
            auditLog('staff_invited', 'user', $userId, null, ['email' => $email, 'role_id' => $roleId]);
            Session::flash('success', "Team member {$name} added successfully! Temporary password: {$password}");
            redirect(APP_URL . '/staff');
        } catch (Exception $e) {
            $db->rollback();
            Session::flash('error', 'Failed to add staff member.');
            redirect(APP_URL . '/staff');
        }
    }

    public static function remove(int $userId): void {
        requireAuth();
        requirePermission('staff.manage');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        if ($userId === Auth::id()) {
            Session::flash('error', 'You cannot remove yourself.');
            redirect(APP_URL . '/staff');
        }

        $db->execute("DELETE FROM user_roles WHERE user_id = ? AND business_id = ?", [$userId, $businessId]);
        $db->execute("UPDATE users SET deleted_at = NOW(), status = 'inactive' WHERE id = ? AND business_id = ?", [$userId, $businessId]);

        auditLog('staff_removed', 'user', $userId);
        Session::flash('success', 'Team member removed.');
        redirect(APP_URL . '/staff');
    }

    public static function update(int $userId): void {
        requireAuth();
        requirePermission('staff.manage');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $name   = trim($_POST['name'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $roleId = (int)($_POST['role_id'] ?? 0);
        $status = $_POST['status'] ?? 'active';
        $newPassword = trim($_POST['password'] ?? '');

        if (empty($name) || !$roleId) {
            Session::flash('error', 'ឈ្មោះ និងតួនាទីត្រូវបានទាមទារ។');
            redirect(APP_URL . '/staff');
        }

        // Verify member belongs to this business
        $member = $db->queryOne("SELECT * FROM users WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$userId, $businessId]);
        if (!$member) {
            Session::flash('error', 'រកមិនឃើញសមាជិកក្រុមនេះឡើយ។');
            redirect(APP_URL . '/staff');
        }

        $db->beginTransaction();
        try {
            $updates = ['name = ?', 'phone = ?', 'status = ?', 'updated_at = NOW()'];
            $params  = [$name, $phone, $status];

            if (!empty($newPassword)) {
                $updates[] = 'password = ?';
                $params[]  = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
            }
            $params[] = $userId;
            $params[] = $businessId;

            $db->execute("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ? AND business_id = ?", $params);

            // Update role
            $db->execute("DELETE FROM user_roles WHERE user_id = ? AND business_id = ?", [$userId, $businessId]);
            $db->execute("INSERT INTO user_roles (user_id, role_id, business_id, assigned_by) VALUES (?, ?, ?, ?)", [$userId, $roleId, $businessId, Auth::id()]);

            $db->commit();
            auditLog('staff_updated', 'user', $userId, null, ['name' => $name, 'role_id' => $roleId]);
            Session::flash('success', "ព័ត៌មានរបស់សមាជិក {$name} ត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ!");
        } catch (Exception $e) {
            $db->rollback();
            Session::flash('error', 'ការកែប្រែព័ត៌មានបុគ្គលិកបានបរាជ័យ។');
        }
        redirect(APP_URL . '/staff');
    }
}
