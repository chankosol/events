<?php
// C:\xampp\htdocs\workshopos\controllers\platform\BusinessController.php

class PlatformBusinessController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $search = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $where = 'WHERE b.deleted_at IS NULL';
        $params = [];

        if ($search) {
            $where .= ' AND (b.name LIKE ? OR b.email LIKE ? OR b.contact_person LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where .= ' AND b.status = ?';
            $params[] = $status;
        }

        $total = $db->queryOne("SELECT COUNT(*) as c FROM businesses b {$where}", $params)['c'] ?? 0;
        $params[] = $perPage;
        $params[] = $offset;

        $businesses = $db->query(
            "SELECT b.*, bb.logo_path, bb.primary_color,
                    (SELECT COUNT(*) FROM workshops w WHERE w.business_id = b.id AND w.deleted_at IS NULL) as workshop_count,
                    (SELECT COUNT(*) FROM registrations r WHERE r.business_id = b.id AND r.deleted_at IS NULL) as participant_count,
                    (SELECT COALESCE(SUM(wb.platform_fee), 0) FROM workshop_billing wb WHERE wb.business_id = b.id AND wb.payment_status = 'paid') as platform_fee_total
             FROM businesses b
             LEFT JOIN business_branding bb ON bb.business_id = b.id
             {$where}
             ORDER BY b.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );

        $totalPages = max(1, (int)ceil($total / $perPage));
        $title = 'All Businesses - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/businesses/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function show(int $id): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $business = $db->queryOne("SELECT b.*, bb.logo_path, bb.primary_color FROM businesses b LEFT JOIN business_branding bb ON bb.business_id = b.id WHERE b.id = ?", [$id]);
        if (!$business) {
            http_response_code(404);
            die('Business not found.');
        }

        $workshops = $db->query("SELECT * FROM workshops WHERE business_id = ? AND deleted_at IS NULL ORDER BY created_at DESC", [$id]);
        $billings  = $db->query("SELECT * FROM workshop_billing WHERE business_id = ? ORDER BY created_at DESC", [$id]);
        $staff     = $db->query("SELECT * FROM users WHERE business_id = ? AND deleted_at IS NULL", [$id]);

        $title = $business['name'] . ' - Platform Business Detail';
        ob_start();
        require VIEWS_PATH . '/platform/businesses/show.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
}
