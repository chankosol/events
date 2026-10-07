<?php
// C:\xampp\htdocs\workshopos\controllers\platform\InvoiceController.php

class PlatformInvoiceController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $invoices = $db->query(
            "SELECT pi.*, b.name as business_name, w.name as workshop_name, wb.capacity
             FROM platform_invoices pi
             JOIN businesses b ON b.id = pi.business_id
             LEFT JOIN workshop_billing wb ON wb.id = pi.workshop_billing_id
             LEFT JOIN workshops w ON w.id = wb.workshop_id
             ORDER BY pi.created_at DESC"
        );

        $totalInvoiced = $db->queryOne("SELECT COALESCE(SUM(amount), 0) as s FROM platform_invoices")['s'] ?? 0;
        $totalPaid     = $db->queryOne("SELECT COALESCE(SUM(amount), 0) as s FROM platform_invoices WHERE status = 'paid'")['s'] ?? 0;

        $title = 'Platform Invoices - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/invoices/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
}
