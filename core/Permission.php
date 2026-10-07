<?php
class Permission {
    private static array $cache = [];

    public static function load(int $userId, ?int $businessId): void {
        $db = Database::getInstance();
        // Get all permissions for user's roles in this business scope
        $permissions = $db->query(
            "SELECT DISTINCT p.slug
             FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             JOIN user_roles ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?
             AND (ur.business_id = ? OR ur.business_id IS NULL)",
            [$userId, $businessId]
        );
        self::$cache = array_column($permissions, 'slug');
        Session::set('_permissions', self::$cache);
    }

    public static function has(string $permission): bool {
        if (empty(self::$cache)) {
            self::$cache = Session::get('_permissions', []);
        }
        return in_array($permission, self::$cache, true);
    }

    public static function hasAny(array $permissions): bool {
        foreach ($permissions as $p) {
            if (self::has($p)) return true;
        }
        return false;
    }

    public static function hasAll(array $permissions): bool {
        foreach ($permissions as $p) {
            if (!self::has($p)) return false;
        }
        return true;
    }

    public static function requirePermission(string $permission): void {
        if (!self::has($permission)) {
            http_response_code(403);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Permission denied.', 'error_code' => 'PERMISSION_DENIED']);
            } else {
                die('<h3>403 - Permission Denied</h3><p>You do not have permission to perform this action.</p>');
            }
            exit;
        }
    }

    public static function getAll(): array {
        if (empty(self::$cache)) {
            self::$cache = Session::get('_permissions', []);
        }
        return self::$cache;
    }

    public static function clear(): void {
        self::$cache = [];
        Session::remove('_permissions');
    }

    private static function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
