<?php
class PlatformDashboardController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        
        $db = Database::getInstance();
        
        $stats = [
            'total_businesses'  => $db->queryOne("SELECT COUNT(*) as c FROM businesses WHERE deleted_at IS NULL")['c'],
            'active_businesses' => $db->queryOne("SELECT COUNT(*) as c FROM businesses WHERE status='active' AND deleted_at IS NULL")['c'],
            'total_workshops'   => $db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE deleted_at IS NULL")['c'],
            'active_workshops'  => $db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE status IN ('registration_open','in_progress','active') AND deleted_at IS NULL")['c'],
            'total_participants'=> $db->queryOne("SELECT COUNT(*) as c FROM participants WHERE deleted_at IS NULL")['c'],
            'total_registrations'=> $db->queryOne("SELECT COUNT(*) as c FROM registrations WHERE deleted_at IS NULL")['c'],
            'platform_revenue'  => $db->queryOne("SELECT COALESCE(SUM(platform_fee),0) as total FROM workshop_billing WHERE payment_status='paid'")['total'],
            'pending_payments'  => $db->queryOne("SELECT COUNT(*) as c FROM platform_payment_proofs WHERE status='pending'")['c'],
            'this_month_revenue'=> $db->queryOne("SELECT COALESCE(SUM(wb.platform_fee),0) as total FROM workshop_billing wb WHERE wb.payment_status='paid' AND MONTH(wb.paid_at)=MONTH(NOW()) AND YEAR(wb.paid_at)=YEAR(NOW())")['total'],
        ];
        
        // Recent businesses
        $recentBusinesses = $db->query("SELECT b.*, (SELECT COUNT(*) FROM workshops w WHERE w.business_id=b.id) as workshop_count FROM businesses b WHERE b.deleted_at IS NULL ORDER BY b.created_at DESC LIMIT 10");
        
        // Monthly revenue (last 12 months)
        $monthlyRevenue = $db->query(
            "SELECT DATE_FORMAT(paid_at, '%Y-%m') as month, SUM(platform_fee) as revenue, COUNT(*) as count
             FROM workshop_billing WHERE payment_status='paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m') ORDER BY month"
        );
        
        // Pending payment proofs
        $pendingProofs = $db->query(
            "SELECT ppp.*, b.name as business_name, w.name as workshop_name, wb.platform_fee, wb.currency
             FROM platform_payment_proofs ppp
             JOIN workshop_billing wb ON wb.id = ppp.workshop_billing_id
             JOIN businesses b ON b.id = ppp.business_id
             JOIN workshops w ON w.id = wb.workshop_id
             WHERE ppp.status = 'pending'
             ORDER BY ppp.submitted_at ASC LIMIT 20"
        );
        
        $title = 'Platform Dashboard - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/dashboard.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
}
