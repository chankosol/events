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
        
        $pendingCount  = (int)$db->queryOne("SELECT COUNT(*) as c FROM questions WHERE workshop_id = ? AND status='pending'", [$workshopId])['c'];
        $approvedCount = (int)$db->queryOne("SELECT COUNT(*) as c FROM questions WHERE workshop_id = ? AND status='approved'", [$workshopId])['c'];
        $openQuestions = $pendingCount + $approvedCount;
        $answeredCount = (int)$db->queryOne("SELECT COUNT(*) as c FROM questions WHERE workshop_id = ? AND status='answered'", [$workshopId])['c'];
        $openRequests  = (int)$db->queryOne("SELECT COUNT(*) as c FROM participant_requests WHERE workshop_id = ? AND status IN ('new','assigned','in_progress')", [$workshopId])['c'];
        $giftCount     = (int)$db->queryOne("SELECT COUNT(*) as c FROM gift_distributions WHERE workshop_id = ?", [$workshopId])['c'];
        $certCount     = (int)$db->queryOne("SELECT COUNT(*) as c FROM certificates WHERE workshop_id = ? AND status='issued'", [$workshopId])['c'];
        $feedbackCount = (int)$db->queryOne("SELECT COUNT(*) as c FROM feedback_responses WHERE workshop_id = ?", [$workshopId])['c'];
        
        // Pending questions (Waiting for moderation)
        $pendingQuestions = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as participant_name
             FROM questions q LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'pending'
             ORDER BY q.created_at DESC LIMIT 50",
            [$workshopId]
        );

        // Approved questions (Live on screen)
        $approvedQuestions = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as participant_name
             FROM questions q LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'approved'
             ORDER BY q.is_pinned DESC, q.upvotes DESC, q.created_at DESC LIMIT 50",
            [$workshopId]
        );
        $questions = $approvedQuestions; // fallback compatibility

        // Answered questions
        $answeredQuestions = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as participant_name
             FROM questions q LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'answered'
             ORDER BY COALESCE(q.answered_at, q.updated_at) DESC LIMIT 50",
            [$workshopId]
        );
        
        // Open requests
        $requests = $db->query(
            "SELECT pr.*, COALESCE(pr.requester_name, p.name, 'សិក្ខាកាម') as participant_name
             FROM participant_requests pr LEFT JOIN participants p ON p.id = pr.participant_id
             WHERE pr.workshop_id = ? AND pr.status IN ('new','assigned','in_progress')
             ORDER BY (CASE WHEN pr.priority = 'urgent' THEN 1 WHEN pr.priority = 'high' THEN 2 ELSE 3 END), pr.created_at ASC LIMIT 30",
            [$workshopId]
        );
        
        $sessions = $db->query("SELECT * FROM workshop_sessions WHERE workshop_id = ? ORDER BY session_date, start_time", [$workshopId]);
        
        $title = 'LIVE - ' . $workshop['name'];
        // Full screen live mode layout
        require VIEWS_PATH . '/business/live/command_center.php';
    }
}
