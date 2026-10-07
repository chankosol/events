<?php
class ParticipantPortalController {
    public static function dashboard(): void {
        requireParticipantAuth();
        $participantId = Auth::participantId();
        $db = Database::getInstance();

        $upcomingWorkshops = $db->query(
            "SELECT r.*, w.name, w.start_date, w.end_date, COALESCE(w.venue, w.address, '') as location, b.name as business_name, pq.token as qr_token
             FROM registrations r
             JOIN workshops w ON w.id = r.workshop_id
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN participant_qr pq ON pq.registration_id = r.id
             WHERE r.participant_id = ? AND r.deleted_at IS NULL AND w.start_date >= CURDATE()
             ORDER BY w.start_date ASC LIMIT 5",
            [$participantId]
        );

        $title = 'Dashboard - Portal';
        ob_start();
        require VIEWS_PATH . '/participant/dashboard.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/participant.php';
    }

    public static function myWorkshops(): void {
        requireParticipantAuth();
        $participantId = Auth::participantId();
        $db = Database::getInstance();

        $workshops = $db->query(
            "SELECT r.*, w.name, w.start_date, w.end_date, COALESCE(w.venue, w.address, '') as location, b.name as business_name
             FROM registrations r
             JOIN workshops w ON w.id = r.workshop_id
             JOIN businesses b ON b.id = w.business_id
             WHERE r.participant_id = ? AND r.deleted_at IS NULL
             ORDER BY w.start_date DESC",
            [$participantId]
        );

        $title = 'My Workshops';
        ob_start();
        require VIEWS_PATH . '/participant/my_workshops.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/participant.php';
    }

    public static function workshopDetail(int $id): void {
        requireParticipantAuth();
        $participantId = Auth::participantId();
        $db = Database::getInstance();

        $reg = $db->queryOne(
            "SELECT r.*, w.name, w.start_date, w.end_date, COALESCE(w.venue, w.address, '') as location, w.description, b.name as business_name, pq.token as qr_token
             FROM registrations r
             JOIN workshops w ON w.id = r.workshop_id
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN participant_qr pq ON pq.registration_id = r.id
             WHERE r.id = ? AND r.participant_id = ? AND r.deleted_at IS NULL",
            [$id, $participantId]
        );

        if (!$reg) {
            http_response_code(404);
            die('Not found.');
        }

        $paymentMethods = [];
        if ($reg['payment_status'] !== 'paid') {
            $paymentMethods = $db->query("SELECT * FROM business_payment_methods WHERE business_id = ? AND status = 'active'", [$reg['business_id']]);
        }

        $sessions = $db->query("SELECT * FROM workshop_sessions WHERE workshop_id = ? ORDER BY session_date, start_time", [$reg['workshop_id']]);

        $title = $reg['name'];
        ob_start();
        require VIEWS_PATH . '/participant/workshop_detail.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/participant.php';
    }

    public static function uploadProof(?int $id = null): void {
        requireParticipantAuth();
        requireCsrf();
        $id = $id ?? (int)($_POST['registration_id'] ?? 0);
        $participantId = Auth::participantId();
        $db = Database::getInstance();

        $reg = $db->queryOne("SELECT * FROM registrations WHERE id = ? AND participant_id = ? AND deleted_at IS NULL", [$id, $participantId]);
        if (!$reg) {
            Session::flash('error', 'រកមិនឃើញការចុះឈ្មោះទេ។');
            redirect(APP_URL . '/participant/portal');
        }

        if (empty($_FILES['proof_file']['name'])) {
            Session::flash('error', 'សូមជ្រើសរើសឯកសារបង្កាន់ដៃ។');
            redirect(APP_URL . '/participant/workshop/' . $id);
        }

        $upload = uploadFile($_FILES['proof_file'], 'payment_proof', (int)$reg['business_id']);
        if ($upload['success']) {
            $payment = $db->queryOne("SELECT id FROM payments WHERE registration_id = ? ORDER BY id DESC LIMIT 1", [$id]);
            $paymentId = $payment ? (int)$payment['id'] : null;
            if (!$paymentId) {
                $db->execute(
                    "INSERT INTO payments (business_id, workshop_id, registration_id, participant_id, amount, status)
                     VALUES (?, ?, ?, ?, ?, 'pending')",
                    [$reg['business_id'], $reg['workshop_id'], $id, $participantId, $reg['final_amount'] ?? 0]
                );
                $paymentId = (int)$db->lastInsertId();
            }

            $db->execute(
                "INSERT INTO payment_proofs (payment_id, registration_id, business_id, proof_file, amount_claimed, transaction_reference, notes, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')",
                [
                    $paymentId,
                    $id,
                    $reg['business_id'],
                    $upload['path'],
                    (float)($_POST['amount_claimed'] ?? $reg['final_amount'] ?? 0),
                    trim($_POST['transaction_reference'] ?? ''),
                    trim($_POST['notes'] ?? '')
                ]
            );

            $db->execute("UPDATE registrations SET payment_status = 'pending' WHERE id = ?", [$id]);

            Session::flash('success', 'បង្កាន់ដៃបង់ប្រាក់ត្រូវបានបញ្ជូនដោយជោគជ័យ។ សូមរង់ចាំការផ្ទៀងផ្ទាត់។');
        } else {
            Session::flash('error', $upload['message'] ?? 'បរាជ័យក្នុងការផ្ទុកឯកសារឡើង។');
        }

        redirect(APP_URL . '/participant/workshop/' . $id);
    }

    public static function profile(): void {
        requireParticipantAuth();
        $participantId = Auth::participantId();
        $db = Database::getInstance();
        $participant = $db->queryOne("SELECT * FROM participants WHERE id = ?", [$participantId]);
        
        $title = 'ព័ត៌មានផ្ទាល់ខ្លួន - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/participant/profile.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/participant.php';
    }

    public static function updateProfile(): void {
        requireParticipantAuth();
        requireCsrf();
        $participantId = Auth::participantId();
        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $company  = trim($_POST['company'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $city     = trim($_POST['city'] ?? '');

        if (empty($name)) {
            Session::flash('error', 'ឈ្មោះពេញត្រូវបានទាមទារ។');
            redirect(APP_URL . '/participant/profile');
        }

        $db = Database::getInstance();
        $db->execute(
            "UPDATE participants SET name = ?, phone = ?, company = ?, position = ?, province = ?, city = ?, updated_at = NOW() WHERE id = ?",
            [$name, $phone, $company, $position, $province, $city, $participantId]
        );

        $p = Session::get('participant', []);
        $p['name'] = $name;
        Session::set('participant', $p);

        Session::flash('success', 'ព័ត៌មានផ្ទាល់ខ្លួនត្រូវបានធ្វើបច្ចុប្បន្នភាពដោយជោគជ័យ។');
        redirect(APP_URL . '/participant/profile');
    }

    public static function certificate(int $id): void {
        requireParticipantAuth();
        $participantId = Auth::participantId();
        $db = Database::getInstance();
        $cert = $db->queryOne(
            "SELECT c.* FROM certificates c WHERE (c.id = ? OR c.registration_id = ?) AND c.participant_id = ?",
            [$id, $id, $participantId]
        );

        if (!$cert || empty($cert['verification_token'])) {
            Session::flash('error', 'រកមិនឃើញវិញ្ញាបនបត្រទេ។');
            redirect(APP_URL . '/participant/portal');
        }

        redirect(APP_URL . '/certificate/verify/' . $cert['verification_token']);
    }
}
