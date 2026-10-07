<?php
// C:\xampp\htdocs\workshopos\controllers\platform\RevenueController.php

class PlatformRevenueController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $grossRevenue = $db->queryOne("SELECT COALESCE(SUM(platform_fee), 0) as s FROM workshop_billing WHERE payment_status = 'paid'")['s'] ?? 0;
        
        $monthlyRevenue = $db->query(
            "SELECT DATE_FORMAT(paid_at, '%Y-%m') as ym, 
                    DATE_FORMAT(paid_at, '%M %Y') as month_name, 
                    COUNT(*) as total_workshops, 
                    SUM(platform_fee) as revenue
             FROM workshop_billing 
             WHERE payment_status = 'paid'
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
             ORDER BY ym DESC"
        );

        $businessRevenue = $db->query(
            "SELECT b.id, b.name, COUNT(wb.id) as paid_workshops, SUM(wb.platform_fee) as total_spent
             FROM businesses b
             JOIN workshop_billing wb ON wb.business_id = b.id AND wb.payment_status = 'paid'
             GROUP BY b.id
             ORDER BY total_spent DESC
             LIMIT 10"
        );

        $title = 'Platform Revenue Analytics - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/revenue/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
}
