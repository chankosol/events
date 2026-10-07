<?php
// C:\xampp\htdocs\workshopos\controllers\platform\AuditController.php

class PlatformAuditController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $total = $db->queryOne("SELECT COUNT(*) as c FROM audit_logs")['c'] ?? 0;

        $logs = $db->query(
            "SELECT al.*, u.name as user_name, u.email as user_email, b.name as business_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             LEFT JOIN businesses b ON b.id = al.business_id
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $totalPages = max(1, (int)ceil($total / $perPage));
        $title = 'Audit Logs - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/audit/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
}
