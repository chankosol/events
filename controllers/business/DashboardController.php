<?php
class DashboardController {
    public static function index(): void {
        requireAuth();
        if (Auth::isPlatformAdmin()) {
            redirect(APP_URL . '/platform');
        }
        $user = Auth::user();
        $businessId = Tenant::getId();
        
        if (!$businessId) {
            Auth::logout();
            Session::flash('error', 'Business account not found.');
            redirect(APP_URL . '/login');
        }

        $db = Database::getInstance();
        
        // KPIs
        $stats = $db->queryOne("
            SELECT 
                (SELECT COUNT(*) FROM workshops WHERE business_id = ? AND start_date >= CURRENT_DATE AND status != 'cancelled') as upcoming_workshops,
                (SELECT COUNT(DISTINCT participant_id) FROM registrations r JOIN workshops w ON r.workshop_id = w.id WHERE w.business_id = ?) as total_participants,
                (SELECT COUNT(*) FROM registrations r JOIN workshops w ON r.workshop_id = w.id WHERE w.business_id = ? AND r.status IN ('approved', 'pending')) as active_registrations,
                (SELECT COUNT(*) FROM payments p JOIN workshops w ON p.workshop_id = w.id WHERE w.business_id = ? AND p.status = 'pending') as pending_payments
        ", [$businessId, $businessId, $businessId, $businessId]);

        // Recent workshops
        $recentWorkshops = $db->query("
            SELECT * FROM workshops 
            WHERE business_id = ? 
            ORDER BY created_at DESC 
            LIMIT 5
        ", [$businessId]);
        
        // Recent registrations needing attention
        $recentRegistrations = $db->query("
            SELECT r.*, w.name as workshop_title, p.name as participant_name, p.email as participant_email
            FROM registrations r
            JOIN workshops w ON r.workshop_id = w.id
            JOIN participants p ON r.participant_id = p.id
            WHERE w.business_id = ? AND r.status = 'pending'
            ORDER BY r.created_at DESC
            LIMIT 5
        ", [$businessId]);
        
        $title = 'ផ្ទាំងគ្រប់គ្រង - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/dashboard.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}

class BusinessDashboardController extends DashboardController {}

