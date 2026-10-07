<?php
// C:\xampp\htdocs\workshopos\controllers\platform\RoleController.php

class PlatformRoleController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        // 1. Fetch all roles with assigned user counts and permission counts
        $roles = $db->query(
            "SELECT r.*,
                    (SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id = r.id) as user_count,
                    (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) as perm_count
             FROM roles r
             ORDER BY r.scope DESC, r.id ASC"
        );

        // 2. Fetch all permissions grouped by category
        $permissions = $db->query(
            "SELECT * FROM permissions ORDER BY group_name ASC, slug ASC"
        );

        $permGroups = [];
        foreach ($permissions as $p) {
            $permGroups[$p['group_name']][] = $p;
        }

        // 3. Fetch role_permission matrix mapping: [role_id => [perm_slug => true]]
        $matrixRows = $db->query(
            "SELECT rp.role_id, p.slug
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id"
        );
        $rolePermMatrix = [];
        foreach ($matrixRows as $row) {
            $rolePermMatrix[$row['role_id']][$row['slug']] = true;
        }

        // 4. Role details with permission list for individual role cards
        $roleDetails = [];
        foreach ($roles as $r) {
            $rolePerms = $db->query(
                "SELECT p.id, p.name, p.slug, p.group_name, p.description
                 FROM role_permissions rp
                 JOIN permissions p ON p.id = rp.permission_id
                 WHERE rp.role_id = ?
                 ORDER BY p.group_name, p.slug",
                [$r['id']]
            );
            $roleDetails[$r['id']] = $rolePerms;
        }

        $totalRoles = count($roles);
        $totalPermissions = count($permissions);

        $title = 'តួនាទី & សិទ្ធិអនុញ្ញាត (Roles & Permissions) - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/roles/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function updateRolePermissions(int $roleId): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        $db = Database::getInstance();

        $role = $db->queryOne("SELECT * FROM roles WHERE id = ?", [$roleId]);
        if (!$role) {
            Session::flash('error', 'រកមិនឃើញតួនាទីនេះឡើយ។');
            redirect(APP_URL . '/platform/roles');
        }

        $selectedPerms = $_POST['permissions'] ?? [];
        if (!is_array($selectedPerms)) {
            $selectedPerms = [];
        }

        // Safety: ensure Super Admin retains platform.admin
        if ($role['slug'] === 'super_admin') {
            $adminPerm = $db->queryOne("SELECT id FROM permissions WHERE slug = 'platform.admin'");
            if ($adminPerm && !in_array((string)$adminPerm['id'], $selectedPerms, true)) {
                $selectedPerms[] = (string)$adminPerm['id'];
            }
        }

        $db->beginTransaction();
        try {
            // Delete current permissions
            $db->execute("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);

            // Insert new permissions
            foreach ($selectedPerms as $permId) {
                $permId = (int)$permId;
                if ($permId > 0) {
                    $db->execute(
                        "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
                        [$roleId, $permId]
                    );
                }
            }

            $db->commit();
            auditLog('role_permissions_batch_updated', 'role', $roleId, null, ['permissions_count' => count($selectedPerms)]);
            Session::flash('success', "បានធ្វើបច្ចុប្បន្នភាពសិទ្ធិសម្រាប់តួនាទី «{$role['name']}» ដោយជោគជ័យ (" . count($selectedPerms) . " សិទ្ធិ)។");
        } catch (Exception $e) {
            $db->rollback();
            Session::flash('error', 'មានបញ្ហាក្នុងការរក្សាទុកសិទ្ធិ៖ ' . $e->getMessage());
        }

        redirect(APP_URL . '/platform/roles');
    }

    public static function togglePermission(): void {
        requireAuth();
        requirePlatformAdmin();
        
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];

        $roleId = (int)($data['role_id'] ?? $_POST['role_id'] ?? 0);
        $permId = (int)($data['permission_id'] ?? $_POST['permission_id'] ?? 0);
        $token  = $data['_csrf_token'] ?? $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        header('Content-Type: application/json; charset=utf-8');

        // CSRF validation
        $sessionToken = Session::get('_csrf_token', '');
        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'CSRF Token ផុតកំណត់ សូម Refresh ទំព័រឡើងវិញ។']);
            exit;
        }

        if (!$roleId || !$permId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ទិន្នន័យមិនគ្រប់គ្រាន់។']);
            exit;
        }

        $db = Database::getInstance();
        $role = $db->queryOne("SELECT * FROM roles WHERE id = ?", [$roleId]);
        $perm = $db->queryOne("SELECT * FROM permissions WHERE id = ?", [$permId]);

        if (!$role || !$perm) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'រកមិនឃើញតួនាទី ឬសិទ្ធិនេះឡើយ។']);
            exit;
        }

        // Safety: protect platform.admin for Super Admin
        if ($role['slug'] === 'super_admin' && $perm['slug'] === 'platform.admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'មិនអាចដកសិទ្ធិ Platform Admin ចេញពី Super Admin បានឡើយ។']);
            exit;
        }

        // Check if granted
        $existing = $db->queryOne(
            "SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permId]
        );

        if ($existing) {
            // Revoke
            $db->execute("DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $permId]);
            auditLog('permission_revoked', 'role_permission', $roleId, ['permission_id' => $permId, 'slug' => $perm['slug']], null);
            echo json_encode([
                'success' => true,
                'granted' => false,
                'role_id' => $roleId,
                'permission_id' => $permId,
                'message' => "បានដកសិទ្ធិ «{$perm['name']}» ចេញពីតួនាទី «{$role['name']}»។"
            ]);
        } else {
            // Grant
            $db->execute("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$roleId, $permId]);
            auditLog('permission_granted', 'role_permission', $roleId, null, ['permission_id' => $permId, 'slug' => $perm['slug']]);
            echo json_encode([
                'success' => true,
                'granted' => true,
                'role_id' => $roleId,
                'permission_id' => $permId,
                'message' => "បានបន្ថែមសិទ្ធិ «{$perm['name']}» ជូនតួនាទី «{$role['name']}»។"
            ]);
        }
        exit;
    }
}
