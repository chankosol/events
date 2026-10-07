<?php
class ReportController {
    public static function index(): void {
        requireAuth();
        requirePermission('report.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        // Business overview stats
        $stats = [
            'total_workshops'    => $db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE business_id=? AND deleted_at IS NULL",[$businessId])['c'],
            'total_participants' => $db->queryOne("SELECT COUNT(DISTINCT participant_id) as c FROM registrations WHERE business_id=? AND deleted_at IS NULL",[$businessId])['c'],
            'total_registrations'=> $db->queryOne("SELECT COUNT(*) as c FROM registrations WHERE business_id=? AND deleted_at IS NULL",[$businessId])['c'],
            'attendance_rate'    => $db->queryOne("SELECT ROUND(AVG(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END)*100,1) as rate FROM registrations r LEFT JOIN attendance a ON a.registration_id=r.id WHERE r.business_id=? AND r.status IN ('confirmed','attended') AND r.deleted_at IS NULL",[$businessId])['rate'] ?? 0,
            'total_revenue'      => $db->queryOne("SELECT COALESCE(SUM(final_amount),0) as total FROM registrations WHERE business_id=? AND payment_status IN ('paid','paid_cash') AND deleted_at IS NULL",[$businessId])['total'],
            'certificates_issued'=> $db->queryOne("SELECT COUNT(*) as c FROM certificates WHERE business_id=? AND status='issued'",[$businessId])['c'],
        ];
        
        // Workshop performance
        $workshops = $db->query(
            "SELECT w.id, w.name, w.start_date, w.status, w.capacity,
                    COUNT(r.id) as registrations,
                    SUM(CASE WHEN r.status IN ('confirmed','attended') THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended,
                    COALESCE(SUM(CASE WHEN r.payment_status IN ('paid','paid_cash') THEN r.final_amount ELSE 0 END),0) as revenue,
                    COALESCE(AVG(fr.overall_rating),0) as avg_feedback
             FROM workshops w
             LEFT JOIN registrations r ON r.workshop_id=w.id AND r.deleted_at IS NULL
             LEFT JOIN attendance a ON a.registration_id=r.id
             LEFT JOIN feedback_responses fr ON fr.workshop_id=w.id
             WHERE w.business_id=? AND w.deleted_at IS NULL
             GROUP BY w.id ORDER BY w.start_date DESC LIMIT 20",
            [$businessId]
        );
        
        $title = 'Reports - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/reports/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
    
    public static function workshop(int $workshopId): void {
        requireAuth();
        requirePermission('report.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $workshop = $db->queryOne("SELECT w.*, b.name as business_name FROM workshops w JOIN businesses b ON b.id=w.business_id WHERE w.id=? AND w.business_id=? AND w.deleted_at IS NULL",[$workshopId,$businessId]);
        if (!$workshop) { http_response_code(404); die(); }
        
        // Full workshop report data
        $report = [
            'workshop'    => $workshop,
            'registrations' => $db->queryOne("SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status='confirmed' OR status='attended' THEN 1 ELSE 0 END), 0) as confirmed, COALESCE(SUM(CASE WHEN status='waitlisted' THEN 1 ELSE 0 END), 0) as waitlisted FROM registrations WHERE workshop_id=? AND deleted_at IS NULL",[$workshopId]),
            'payments'    => $db->queryOne("SELECT COALESCE(SUM(final_amount), 0) as expected, COALESCE(SUM(CASE WHEN payment_status IN ('paid','paid_cash') THEN final_amount ELSE 0 END), 0) as collected, COALESCE(SUM(CASE WHEN payment_status='pending' THEN final_amount ELSE 0 END), 0) as pending, COALESCE(SUM(CASE WHEN payment_status IN ('unpaid') THEN final_amount ELSE 0 END), 0) as outstanding FROM registrations WHERE workshop_id=? AND deleted_at IS NULL",[$workshopId]),
            'attendance'  => $db->queryOne("SELECT COUNT(a.id) as checked_in FROM attendance a JOIN registrations r ON r.id=a.registration_id WHERE r.workshop_id=? AND a.checked_in_at IS NOT NULL",[$workshopId]),
            'sessions'    => $db->query("SELECT ws.*, (SELECT COUNT(*) FROM session_attendance sa WHERE sa.session_id=ws.id AND sa.status='present') as present_count FROM workshop_sessions ws WHERE ws.workshop_id=? ORDER BY ws.session_date, ws.start_time",[$workshopId]),
            'certificates'=> $db->queryOne("SELECT COUNT(*) as issued FROM certificates WHERE workshop_id=? AND status='issued'",[$workshopId]),
            'gifts'       => $db->queryOne("SELECT COUNT(*) as distributed FROM gift_distributions WHERE workshop_id=?",[$workshopId]),
            'feedback'    => $db->queryOne("SELECT COUNT(*) as responses, COALESCE(AVG(overall_rating), 0) as avg_rating FROM feedback_responses WHERE workshop_id=?",[$workshopId]),
            'top_questions'=> $db->query("SELECT * FROM questions WHERE workshop_id=? ORDER BY upvotes DESC LIMIT 10",[$workshopId]),
            'ticket_breakdown'=> $db->query("SELECT t.name, t.ticket_type, COUNT(r.id) as sold FROM registrations r JOIN tickets t ON t.id=r.ticket_id WHERE r.workshop_id=? AND r.deleted_at IS NULL GROUP BY r.ticket_id",[$workshopId]),
        ];
        
        $title = 'Report: ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/reports/workshop.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}
