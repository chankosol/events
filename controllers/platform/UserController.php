<?php
// C:\xampp\htdocs\workshopos\controllers\platform\UserController.php

class PlatformUserController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $roleFilter = trim($_GET['role'] ?? '');
        $statusFilter = trim($_GET['status'] ?? '');
        $tab = trim($_GET['tab'] ?? 'staff');

        // Staff / System Users Query
        $staffWhere = "WHERE u.deleted_at IS NULL";
        $staffParams = [];

        if (!empty($search)) {
            $staffWhere .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $staffParams[] = "%{$search}%";
            $staffParams[] = "%{$search}%";
            $staffParams[] = "%{$search}%";
        }

        if (!empty($statusFilter)) {
            $staffWhere .= " AND u.status = ?";
            $staffParams[] = $statusFilter;
        }

        $users = $db->query(
            "SELECT u.*, b.name as business_name, b.slug as business_slug,
                    GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') as role_names,
                    GROUP_CONCAT(DISTINCT r.slug SEPARATOR ',') as role_slugs
             FROM users u
             LEFT JOIN businesses b ON b.id = u.business_id
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             {$staffWhere}
             GROUP BY u.id
             ORDER BY u.created_at DESC",
            $staffParams
        );

        if (!empty($roleFilter)) {
            $users = array_filter($users, function($u) use ($roleFilter) {
                $slugs = explode(',', $u['role_slugs'] ?? '');
                return in_array($roleFilter, $slugs, true);
            });
        }

        // Participants Query
        $partWhere = "WHERE p.deleted_at IS NULL";
        $partParams = [];
        if (!empty($search)) {
            $partWhere .= " AND (p.name LIKE ? OR p.email LIKE ? OR p.phone LIKE ? OR p.company LIKE ?)";
            $partParams[] = "%{$search}%";
            $partParams[] = "%{$search}%";
            $partParams[] = "%{$search}%";
            $partParams[] = "%{$search}%";
        }
        if (!empty($statusFilter)) {
            $partWhere .= " AND p.status = ?";
            $partParams[] = $statusFilter;
        }

        $participants = $db->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM registrations r WHERE r.participant_id = p.id AND r.deleted_at IS NULL) as reg_count
             FROM participants p
             {$partWhere}
             ORDER BY p.created_at DESC",
            $partParams
        );

        $roles = $db->query("SELECT * FROM roles ORDER BY scope, name");
        $totalStaff = count($users);
        $totalParticipants = count($participants);

        $title = 'គ្រប់គ្រងអ្នកប្រើប្រាស់ - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/users/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function toggle(int $id): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        $db = Database::getInstance();

        $currentUser = Auth::user();
        if ((int)$currentUser['id'] === $id) {
            Session::flash('error', 'អ្នកមិនអាចបិទគណនីផ្ទាល់ខ្លួនរបស់អ្នកបានទេ។');
            redirect(APP_URL . '/platform/users');
        }

        $user = $db->queryOne("SELECT id, status, name, email FROM users WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$user) {
            Session::flash('error', 'រកមិនឃើញអ្នកប្រើប្រាស់នេះទេ។');
            redirect(APP_URL . '/platform/users');
        }

        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $db->execute("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

        auditLog('user_status_toggled', 'user', $id, ['status' => $user['status']], ['status' => $newStatus]);
        Session::flash('success', "បានផ្លាស់ប្តូរស្ថានភាពរបស់ {$user['name']} ទៅជា '{$newStatus}' ដោយជោគជ័យ។");
        redirect(APP_URL . '/platform/users');
    }

    public static function updateRole(int $userId): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        $db = Database::getInstance();

        $newRoleId = (int)($_POST['role_id'] ?? 0);
        $user = $db->queryOne("SELECT id, name, email, business_id FROM users WHERE id = ? AND deleted_at IS NULL", [$userId]);
        if (!$user) {
            Session::flash('error', 'រកមិនឃើញអ្នកប្រើប្រាស់នេះទេ។');
            redirect(APP_URL . '/platform/users');
        }

        $role = $db->queryOne("SELECT id, name, slug FROM roles WHERE id = ?", [$newRoleId]);
        if (!$role) {
            Session::flash('error', 'តួនាទីដែលបានជ្រើសរើសមិនត្រឹមត្រូវឡើយ។');
            redirect(APP_URL . '/platform/users');
        }

        // Prevent locking oneself out of Super Admin
        $currentAuth = Auth::user();
        if ((int)$currentAuth['id'] === $userId && $role['slug'] !== 'super_admin') {
            Session::flash('error', 'អ្នកមិនអាចដកសិទ្ធិ Super Admin ពីគណនីផ្ទាល់ខ្លួនរបស់អ្នកបានឡើយ។');
            redirect(APP_URL . '/platform/users');
        }

        $existing = $db->queryOne("SELECT id, role_id FROM user_roles WHERE user_id = ?", [$userId]);
        $oldRoleId = $existing['role_id'] ?? null;

        if ($existing) {
            $db->execute("UPDATE user_roles SET role_id = ?, assigned_by = ?, assigned_at = NOW() WHERE user_id = ?", [$newRoleId, Auth::id(), $userId]);
        } else {
            $db->execute("INSERT INTO user_roles (user_id, role_id, business_id, assigned_by) VALUES (?, ?, ?, ?)", [$userId, $newRoleId, $user['business_id'], Auth::id()]);
        }

        auditLog('user_role_updated', 'user', $userId, ['old_role_id' => $oldRoleId], ['new_role_id' => $newRoleId]);
        Session::flash('success', "បានធ្វើបច្ចុប្បន្នភាពតួនាទីរបស់ {$user['name']} ទៅជា '{$role['name']}' ដោយជោគជ័យ។");
        redirect(APP_URL . '/platform/users');
    }
}

