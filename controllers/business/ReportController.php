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
        
        // Attendance by method
        $methodsRaw = $db->query(
            "SELECT COALESCE(check_in_method, 'qr') as method, COUNT(*) as count 
             FROM attendance 
             WHERE workshop_id = ? AND checked_in_at IS NOT NULL 
             GROUP BY check_in_method",
            [$workshopId]
        );
        $checkinMethods = [
            'face' => 0,
            'qr' => 0,
            'helper_station' => 0,
            'manual' => 0,
            'total' => 0,
        ];
        foreach ($methodsRaw as $m) {
            $k = $m['method'] ?? 'qr';
            if (isset($checkinMethods[$k])) {
                $checkinMethods[$k] += (int)$m['count'];
            } else {
                $checkinMethods['qr'] += (int)$m['count'];
            }
            $checkinMethods['total'] += (int)$m['count'];
        }

        // Gender demographics
        $gendersRaw = $db->query(
            "SELECT COALESCE(NULLIF(p.gender, ''), 'unspecified') as gender, COUNT(*) as count 
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id 
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL 
             GROUP BY gender",
            [$workshopId]
        );
        $genderStats = ['male' => 0, 'female' => 0, 'other' => 0, 'unspecified' => 0, 'total' => 0];
        foreach ($gendersRaw as $g) {
            $val = strtolower(trim((string)$g['gender']));
            if (in_array($val, ['m', 'male', 'ប្រុស'])) $k = 'male';
            elseif (in_array($val, ['f', 'female', 'ស្រី'])) $k = 'female';
            elseif (in_array($val, ['other', 'ផ្សេងៗ'])) $k = 'other';
            else $k = 'unspecified';
            $genderStats[$k] += (int)$g['count'];
            $genderStats['total'] += (int)$g['count'];
        }

        // Top Provinces
        $provinces = $db->query(
            "SELECT COALESCE(NULLIF(d.province, ''), NULLIF(p.province, ''), 'រាជធានីភ្នំពេញ / ទូទៅ') as province, COUNT(r.id) as count 
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id 
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id 
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL 
             GROUP BY province 
             ORDER BY count DESC 
             LIMIT 10",
            [$workshopId]
        );

        // Top Organizations / Companies
        $organizations = $db->query(
            "SELECT COALESCE(NULLIF(d.organization, ''), NULLIF(p.company, ''), 'ទូទៅ / ឯករាជ្យ') as company_name, COUNT(r.id) as count 
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id 
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id 
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL 
             GROUP BY company_name 
             ORDER BY count DESC 
             LIMIT 8",
            [$workshopId]
        );

        // Detailed tickets
        $tickets = $db->query(
            "SELECT t.id, t.name, t.ticket_type, t.price, t.currency, 
                    COUNT(r.id) as sold_count, 
                    COALESCE(SUM(r.final_amount), 0) as total_revenue, 
                    SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended_count 
             FROM tickets t 
             LEFT JOIN registrations r ON r.ticket_id = t.id AND r.deleted_at IS NULL 
             LEFT JOIN attendance a ON a.registration_id = r.id 
             WHERE t.workshop_id = ? 
             GROUP BY t.id 
             ORDER BY t.sort_order ASC, t.id ASC",
            [$workshopId]
        );

        // Delegations
        $delegations = $db->query(
            "SELECT d.id, d.province, d.organization, d.head_name, d.head_phone, d.quota_seats, d.allowance_rate, 
                    COUNT(r.id) as registered_count, 
                    SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended_count 
             FROM workshop_delegations d 
             LEFT JOIN registrations r ON r.delegation_id = d.id AND r.deleted_at IS NULL 
             LEFT JOIN attendance a ON a.registration_id = r.id 
             WHERE d.workshop_id = ? 
             GROUP BY d.id 
             ORDER BY d.province ASC",
            [$workshopId]
        );

        // Allowances
        $allowances = $db->queryOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total_disbursed 
             FROM allowance_disbursements 
             WHERE workshop_id = ?",
            [$workshopId]
        );

        // Feedback ratings breakdown
        $ratingStars = $db->query(
            "SELECT ROUND(overall_rating) as star_level, COUNT(*) as count 
             FROM feedback_responses 
             WHERE workshop_id = ? 
             GROUP BY star_level 
             ORDER BY star_level DESC",
            [$workshopId]
        );

        // Recent feedback with comments
        $feedbackRecent = $db->query(
            "SELECT fr.overall_rating, fr.answers, fr.submitted_at, fr.is_anonymous, p.name as participant_name 
             FROM feedback_responses fr 
             LEFT JOIN registrations r ON r.id = fr.registration_id 
             LEFT JOIN participants p ON p.id = r.participant_id 
             WHERE fr.workshop_id = ? 
             ORDER BY fr.submitted_at DESC 
             LIMIT 8",
            [$workshopId]
        );

        // All attendees for detailed registry table
        $attendees = $db->query(
            "SELECT r.id, r.registration_code, r.status, r.payment_status, r.final_amount, r.is_vip,
                    p.name, p.phone, p.email, p.gender, 
                    COALESCE(d.organization, p.company, '-') as organization, 
                    COALESCE(d.province, p.province, '-') as province, 
                    t.name as ticket_name, 
                    a.checked_in_at, a.check_in_method 
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id 
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id 
             LEFT JOIN tickets t ON t.id = r.ticket_id 
             LEFT JOIN attendance a ON a.registration_id = r.id 
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL 
             ORDER BY (a.checked_in_at IS NOT NULL) DESC, r.id DESC",
            [$workshopId]
        );

        // Multi-scan slots breakdown for attendees
        $slotsRaw = [];
        try {
            $slotsRaw = $db->query(
                "SELECT registration_id, checkin_date, slot_number, slot_name, checked_in_at, check_in_method 
                 FROM attendance_slots 
                 WHERE workshop_id = ? 
                 ORDER BY checkin_date ASC, slot_number ASC",
                [$workshopId]
            );
        } catch (Throwable $se) {}

        $attendeeSlots = [];
        foreach ($slotsRaw as $s) {
            $attendeeSlots[$s['registration_id']][] = $s;
        }

        foreach ($attendees as &$att) {
            $att['slots'] = $attendeeSlots[$att['id']] ?? [];
        }
        unset($att);

        // Full workshop report data
        $report = [
            'workshop'         => $workshop,
            'registrations'    => $db->queryOne("SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status='confirmed' OR status='attended' THEN 1 ELSE 0 END), 0) as confirmed, COALESCE(SUM(CASE WHEN status='waitlisted' THEN 1 ELSE 0 END), 0) as waitlisted, COALESCE(SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END), 0) as cancelled FROM registrations WHERE workshop_id=? AND deleted_at IS NULL",[$workshopId]),
            'payments'         => $db->queryOne("SELECT COALESCE(SUM(final_amount), 0) as expected, COALESCE(SUM(CASE WHEN payment_status IN ('paid','paid_cash') THEN final_amount ELSE 0 END), 0) as collected, COALESCE(SUM(CASE WHEN payment_status='pending' THEN final_amount ELSE 0 END), 0) as pending, COALESCE(SUM(CASE WHEN payment_status IN ('unpaid') THEN final_amount ELSE 0 END), 0) as outstanding FROM registrations WHERE workshop_id=? AND deleted_at IS NULL",[$workshopId]),
            'attendance'       => $db->queryOne("SELECT COUNT(a.id) as checked_in FROM attendance a JOIN registrations r ON r.id=a.registration_id WHERE r.workshop_id=? AND a.checked_in_at IS NOT NULL",[$workshopId]),
            'checkin_methods'  => $checkinMethods,
            'gender_stats'     => $genderStats,
            'provinces'        => $provinces,
            'organizations'    => $organizations,
            'tickets'          => $tickets,
            'sessions'         => $db->query("SELECT ws.*, (SELECT COUNT(*) FROM session_attendance sa WHERE sa.session_id=ws.id AND sa.status='present') as present_count FROM workshop_sessions ws WHERE ws.workshop_id=? ORDER BY ws.session_date, ws.start_time",[$workshopId]),
            'delegations'      => $delegations,
            'allowances'       => $allowances,
            'certificates'     => $db->queryOne("SELECT COUNT(*) as issued FROM certificates WHERE workshop_id=? AND status='issued'",[$workshopId]),
            'gifts'            => $db->queryOne("SELECT COUNT(*) as distributed FROM gift_distributions WHERE workshop_id=?",[$workshopId]),
            'feedback'         => $db->queryOne("SELECT COUNT(*) as responses, COALESCE(AVG(overall_rating), 0) as avg_rating FROM feedback_responses WHERE workshop_id=?",[$workshopId]),
            'rating_stars'     => $ratingStars,
            'feedback_recent'  => $feedbackRecent,
            'top_questions'    => $db->query("SELECT q.*, p.name as author_real_name FROM questions q LEFT JOIN participants p ON p.id = q.participant_id WHERE q.workshop_id=? ORDER BY q.is_pinned DESC, q.upvotes DESC LIMIT 10",[$workshopId]),
            'ticket_breakdown' => $db->query("SELECT t.name, t.ticket_type, COUNT(r.id) as sold FROM registrations r JOIN tickets t ON t.id=r.ticket_id WHERE r.workshop_id=? AND r.deleted_at IS NULL GROUP BY r.ticket_id",[$workshopId]),
            'attendees'        => $attendees,
        ];
        
        $title = 'របាយការណ៍សរុប៖ ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/reports/workshop.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}
