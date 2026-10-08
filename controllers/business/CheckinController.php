<?php
class CheckinController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        if (!$workshop) { http_response_code(404); die('Workshop not found.'); }

        // Require platform activation fee to be paid
        $billing = $db->queryOne("SELECT payment_status FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);
        if (!$billing || $billing['payment_status'] !== 'paid') {
            Session::flash('error', 'មុខងារស្កេនវត្តមាន (Check-in) ដំណើរការបានលុះត្រាតែបានបង់ថ្លៃសេវាប្រព័ន្ធរួចរាល់។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/activate');
        }

        $stats = $db->queryOne(
            "SELECT
               COUNT(r.id) as total_confirmed,
               SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in
             FROM registrations r
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.status = 'confirmed' AND r.deleted_at IS NULL",
            [$workshopId]
        );

        $sessions = $db->query(
            "SELECT * FROM workshop_sessions WHERE workshop_id = ? AND status != 'cancelled' ORDER BY session_date, start_time",
            [$workshopId]
        );

        $recentAttendance = $db->query(
            "SELECT a.checked_in_at, a.check_in_method, p.name, p.phone, p.company, r.id as registration_id, r.registration_code, t.name as ticket_name
             FROM attendance a
             JOIN registrations r ON r.id = a.registration_id
             JOIN participants p ON p.id = a.participant_id
             LEFT JOIN tickets t ON t.id = r.ticket_id
             WHERE a.workshop_id = ? AND a.checked_in_at IS NOT NULL
             ORDER BY a.checked_in_at DESC
             LIMIT 10",
            [$workshopId]
        );

        $title = 'ស្កេនវត្តមាន - ' . $workshop['name'];
        $breadcrumbs = [
            ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill'],
            ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'],
            ['label' => mb_strimwidth($workshop['name'], 0, 35, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
            ['label' => 'ស្កេនវត្តមាន', 'url' => null, 'icon' => 'qr-code-scan'],
        ];

        ob_start();
        require VIEWS_PATH . '/business/checkin/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}
