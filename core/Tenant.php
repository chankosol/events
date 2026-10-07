<?php
class Tenant {
    private static ?int $businessId = null;
    private static ?array $business = null;

    public static function resolve(): void {
        // ALWAYS get business_id from session, NEVER from request
        $user = Session::get('user');
        if ($user && isset($user['business_id'])) {
            self::$businessId = $user['business_id'] ? (int)$user['business_id'] : null;
        } else {
            self::$businessId = null;
        }
    }

    public static function id(): ?int {
        if (self::$businessId === null) {
            self::resolve();
        }
        return self::$businessId;
    }

    public static function getId(): ?int {
        return self::id();
    }

    public static function business(): ?array {
        if (self::$business === null && self::id() !== null) {
            $db = Database::getInstance();
            self::$business = $db->queryOne(
                "SELECT * FROM businesses WHERE id = ? AND deleted_at IS NULL",
                [self::id()]
            );
        }
        return self::$business;
    }

    public static function getBusiness(): ?array {
        return self::business();
    }

    public static function assertAccess(int $resourceBusinessId): void {
        $user = Session::get('user');
        // Super admin (null business_id) can access any business
        if ($user && $user['business_id'] === null) return;
        if (self::id() !== $resourceBusinessId) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied.', 'error_code' => 'TENANT_MISMATCH']);
            } else {
                die('<h3>403 - Access Denied</h3><p>You cannot access another business\' data.</p>');
            }
            exit;
        }
    }

    public static function reset(): void {
        self::$businessId = null;
        self::$business   = null;
    }
}
