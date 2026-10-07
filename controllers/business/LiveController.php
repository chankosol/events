<?php
class LiveController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die(); }
        
        // Require platform activation fee to be paid
        $billing = $db->queryOne("SELECT payment_status FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);
        if (!$billing || $billing['payment_status'] !== 'paid') {
            Session::flash('error', 'បន្ទប់បញ្ជាផ្សាយផ្ទាល់ (Live Command Center) ដំណើរការបានលុះត្រាតែបានបង់ថ្លៃសេវាប្រព័ន្ធរួចរាល់។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/activate');
        }
        
        // Real-time stats
        $stats = $db->queryOne(
            "SELECT
               COUNT(r.id) as total_registered,
               SUM(CASE WHEN r.status='confirmed' OR r.status='attended' THEN 1 ELSE 0 END) as confirmed,
               SUM(CASE WHEN r.payment_status IN ('paid','paid_cash','complimentary','waived') THEN 1 ELSE 0 END) as paid,
               SUM(CASE WHEN r.payment_status='pending' THEN 1 ELSE 0 END) as pending_payment,
               SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in,
               SUM(CASE WHEN r.is_vip=1 THEN 1 ELSE 0 END) as vip_count
             FROM registrations r
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        
        $openQuestions = $db->queryOne("SELECT COUNT(*) as c FROM questions WHERE workshop_id = ? AND status='pending'", [$workshopId])['c'];
        $openRequests  = $db->queryOne("SELECT COUNT(*) as c FROM participant_requests WHERE workshop_id = ? AND status IN ('new','assigned','in_progress')", [$workshopId])['c'];
        $giftCount     = $db->queryOne("SELECT COUNT(*) as c FROM gift_distributions WHERE workshop_id = ?", [$workshopId])['c'];
        $certCount     = $db->queryOne("SELECT COUNT(*) as c FROM certificates WHERE workshop_id = ? AND status='issued'", [$workshopId])['c'];
        $feedbackCount = $db->queryOne("SELECT COUNT(*) as c FROM feedback_responses WHERE workshop_id = ?", [$workshopId])['c'];
        
        // Recent questions
        $questions = $db->query(
            "SELECT q.*, p.name as participant_name
             FROM questions q LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status IN ('pending','approved','pinned')
             ORDER BY q.is_pinned DESC, q.upvotes DESC, q.created_at DESC LIMIT 20",
            [$workshopId]
        );
        
        // Open requests
        $requests = $db->query(
            "SELECT pr.*, p.name as participant_name
             FROM participant_requests pr JOIN participants p ON p.id = pr.participant_id
             WHERE pr.workshop_id = ? AND pr.status IN ('new','assigned','in_progress')
             ORDER BY pr.priority DESC, pr.created_at ASC LIMIT 20",
            [$workshopId]
        );
        
        $sessions = $db->query("SELECT * FROM workshop_sessions WHERE workshop_id = ? ORDER BY session_date, start_time", [$workshopId]);
        
        $title = 'LIVE - ' . $workshop['name'];
        // Full screen live mode layout
        require VIEWS_PATH . '/business/live/command_center.php';
    }
}
