<?php
class RegistrationController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('registration.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die(); }
        
        $registrations = $db->query(
            "SELECT r.*, p.name as participant_name, p.email, p.phone,
                    COALESCE(NULLIF(p.gender, ''), (
                        SELECT ra.answer_text FROM registration_answers ra 
                        JOIN registration_fields rf ON rf.id = ra.field_id 
                        WHERE ra.registration_id = r.id AND rf.field_label LIKE '%ភេទ%' 
                        LIMIT 1
                    )) as gender,
                    COALESCE(NULLIF(p.province, ''), (
                        SELECT ra.answer_text FROM registration_answers ra 
                        JOIN registration_fields rf ON rf.id = ra.field_id 
                        WHERE ra.registration_id = r.id AND (rf.field_label LIKE '%ខេត្ត%' OR rf.field_label LIKE '%រាជធានី%') 
                        LIMIT 1
                    )) as province,
                    COALESCE(NULLIF(p.company, ''), (
                        SELECT ra.answer_text FROM registration_answers ra 
                        JOIN registration_fields rf ON rf.id = ra.field_id 
                        WHERE ra.registration_id = r.id AND (rf.field_label LIKE '%ក្រុមហ៊ុន%' OR rf.field_label LIKE '%ស្ថាប័ន%') 
                        LIMIT 1
                    )) as company,
                    COALESCE(NULLIF(p.position, ''), (
                        SELECT ra.answer_text FROM registration_answers ra 
                        JOIN registration_fields rf ON rf.id = ra.field_id 
                        WHERE ra.registration_id = r.id AND (rf.field_label LIKE '%តួនាទី%' OR rf.field_label LIKE '%មុខតំណែង%') 
                        LIMIT 1
                    )) as position,
                    t.name as ticket_name, t.price as ticket_price,
                    w.payment_mode as workshop_payment_mode
             FROM registrations r 
             JOIN workshops w ON w.id = r.workshop_id
             JOIN participants p ON p.id = r.participant_id 
             LEFT JOIN tickets t ON t.id = r.ticket_id 
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC", 
            [$workshopId, $businessId]
        );
        
        $tickets = $db->query(
            "SELECT * FROM tickets WHERE workshop_id = ? AND business_id = ? AND status = 'active' ORDER BY sort_order ASC",
            [$workshopId, $businessId]
        );
        $provinces = cambodiaProvinces();
        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);

        // Form field configuration for modal
        $formConfig = [
            'show_phone'       => 1,
            'require_phone'    => 1,
            'show_email'       => 1,
            'require_email'    => 0,
            'show_gender'      => 0,
            'require_gender'   => 0,
            'show_province'    => 0,
            'require_province' => 0,
            'show_company'     => 1,
            'require_company'  => 0,
            'show_position'    => 1,
            'require_position' => 0,
        ];
        if (!empty($workshop['form_fields'])) {
            $saved = is_string($workshop['form_fields']) ? json_decode($workshop['form_fields'], true) : $workshop['form_fields'];
            if (is_array($saved)) {
                $formConfig = array_merge($formConfig, $saved);
            }
        }
        $customFields = $db->query(
            "SELECT * FROM registration_fields WHERE workshop_id = ? AND is_internal = 0 ORDER BY sort_order ASC",
            [$workshopId]
        );

        $title = 'បញ្ជីចុះឈ្មោះ - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/registrations/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    public static function store(int $workshopId): void {
        requireAuth();
        requirePermission('registration.create');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) {
            Session::flash('error', 'រកមិនឃើញសិក្ខាសាលាទេ។');
            redirect(APP_URL . '/workshops');
        }

        // Require platform activation fee to be paid before registering participants
        $billing = $db->queryOne("SELECT payment_status FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);
        if (!$billing || $billing['payment_status'] !== 'paid') {
            Session::flash('error', 'មិនអាចចុះឈ្មោះសិក្ខាកាមបានទេ! សូមបង់ថ្លៃដំណើរការប្រព័ន្ធជាមុនសិន។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/activate');
        }

        $name     = trim($_POST['name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $gender   = $_POST['gender'] ?? 'male';
        $company  = trim($_POST['company'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $ticketId = !empty($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : null;
        $paymentStatus = $_POST['payment_status'] ?? 'paid_cash';
        $status   = $_POST['status'] ?? 'confirmed';
        $notes    = trim($_POST['notes'] ?? '');
        $isAllowanceEligible = isset($_POST['is_allowance_eligible']) ? 1 : 0;
        $attendeeType = $_POST['attendee_type'] ?? 'general';

        if (empty($name)) {
            Session::flash('error', 'សូមបញ្ចូលឈ្មោះសិក្ខាកាម។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
        }

        // Generate fallback email if blank
        if (empty($email)) {
            $slug = slugify($name ?: 'guest');
            $email = $slug . '.' . substr(uniqid(), -4) . '@workshopos.local';
        }

        // Find or create participant
        $participant = $db->queryOne(
            "SELECT id FROM participants WHERE (email = ? AND email NOT LIKE '%@workshopos.local') OR (phone = ? AND phone != '')",
            [$email, $phone]
        );

        if ($participant) {
            $participantId = (int)$participant['id'];
            $db->execute(
                "UPDATE participants SET name = ?, gender = ?, company = ?, position = ?, province = ? WHERE id = ?",
                [$name, $gender, $company, $position, $province, $participantId]
            );
        } else {
            $db->execute(
                "INSERT INTO participants (name, email, phone, gender, company, position, province, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
                [$name, $email, $phone, $gender, $company, $position, $province]
            );
            $participantId = (int)$db->lastInsertId();
        }

        // Ticket price
        $ticketPrice = 0.00;
        if ($ticketId) {
            $t = $db->queryOne("SELECT price FROM tickets WHERE id = ? AND workshop_id = ?", [$ticketId, $workshopId]);
            if ($t) $ticketPrice = (float)$t['price'];
        }

        // Generate unique code & token
        $regCode = generateCode('REG', 10);
        $qrToken = bin2hex(random_bytes(16));

        $db->execute(
            "INSERT INTO registrations (
                registration_code, business_id, workshop_id, participant_id, ticket_id,
                status, payment_status, ticket_price, final_amount, attendee_type,
                is_allowance_eligible, notes, registered_by, confirmed_at, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $regCode, $businessId, $workshopId, $participantId, $ticketId,
                $status, $paymentStatus, $ticketPrice, $ticketPrice, $attendeeType,
                $isAllowanceEligible, $notes, Auth::id()
            ]
        );
        $regId = (int)$db->lastInsertId();

        // Generate participant QR
        $db->execute(
            "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active, generated_at)
             VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [$regId, $participantId, $businessId, $workshopId, $qrToken]
        );

        auditLog('create_registration', 'registration', $regId, null, ['code' => $regCode, 'name' => $name], $businessId);

        Session::flash('success', "បានបន្ថែមការចុះឈ្មោះដោយជោគជ័យ! លេខកូដ៖ {$regCode}");
        redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
    }

    public static function export(int $workshopId): void {
        requireAuth();
        requirePermission('registration.export');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die('Workshop not found.'); }

        $registrations = $db->query(
            "SELECT r.*, p.name as participant_name, p.gender, p.email, p.phone, p.company, p.position, p.province,
                    t.name as ticket_name, a.checked_in_at
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id 
             LEFT JOIN tickets t ON t.id = r.ticket_id 
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC", 
            [$workshopId, $businessId]
        );

        $filename = 'registrations_' . slugify($workshop['name']) . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'ល.រ',
            'កូដចុះឈ្មោះ',
            'ឈ្មោះសិក្ខាកាម',
            'ភេទ',
            'លេខទូរស័ព្ទ',
            'អ៊ីមែល',
            'អង្គភាព/ស្ថាប័ន',
            'តួនាទី',
            'រាជធានី-ខេត្ត',
            'ប្រភេទសំបុត្រ',
            'តម្លៃ ($)',
            'ស្ថានភាព',
            'ការបង់ប្រាក់',
            'ស្កេនវត្តមាន',
            'កាលបរិច្ឆេទចុះឈ្មោះ'
        ]);

        $no = 1;
        foreach ($registrations as $reg) {
            $genderText = $reg['gender'] === 'female' ? 'ស្រី' : ($reg['gender'] === 'male' ? 'ប្រុស' : 'ផ្សេងៗ');
            $checkinText = !empty($reg['checked_in_at']) ? date('Y-m-d H:i:s', strtotime($reg['checked_in_at'])) : 'មិនទាន់ស្កេន';

            fputcsv($output, [
                $no++,
                $reg['registration_code'],
                $reg['participant_name'],
                $genderText,
                $reg['phone'] ?? '',
                $reg['email'] ?? '',
                $reg['company'] ?? '',
                $reg['position'] ?? '',
                $reg['province'] ?? '',
                $reg['ticket_name'] ?? 'ទូទៅ',
                number_format((float)($reg['ticket_price'] ?? 0), 2),
                $reg['status'],
                $reg['payment_status'],
                $checkinText,
                date('Y-m-d H:i', strtotime($reg['created_at']))
            ]);
        }
        fclose($output);
        exit;
    }
    
    public static function approve(int $workshopId, int $regId): void {
        requireAuth();
        requirePermission('registration.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $reg = $db->queryOne("SELECT * FROM registrations WHERE id = ? AND workshop_id = ? AND business_id = ? AND deleted_at IS NULL", [$regId, $workshopId, $businessId]);
        if (!$reg) jsonResponse(false, 'រកមិនឃើញទិន្នន័យចុះឈ្មោះនេះទេ។');
        
        $workshop = $db->queryOne("SELECT payment_mode FROM workshops WHERE id = ?", [$workshopId]);
        $payStatusSql = ($workshop && $workshop['payment_mode'] === 'free') ? ", payment_status = 'paid'" : "";

        $db->execute("UPDATE registrations SET status = 'confirmed'{$payStatusSql}, confirmed_at = NOW() WHERE id = ?", [$regId]);

        // Ensure participant QR code is generated
        $existingQr = $db->queryOne("SELECT id FROM participant_qr WHERE registration_id = ?", [$regId]);
        if (!$existingQr) {
            $token = bin2hex(random_bytes(32));
            $db->execute(
                "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)",
                [$regId, $reg['participant_id'], $businessId, $workshopId, $token]
            );
        }

        auditLog('registration_approved', 'registration', $regId, ['status' => $reg['status']], ['status' => 'confirmed'], $businessId);
        jsonResponse(true, 'ការចុះឈ្មោះត្រូវបានអនុម័តដោយជោគជ័យ!');
    }
    
    public static function reject(int $workshopId, int $regId): void {
        requireAuth();
        requirePermission('registration.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $reason = trim($_POST['reason'] ?? '');
        
        $reg = $db->queryOne("SELECT * FROM registrations WHERE id = ? AND workshop_id = ? AND business_id = ? AND deleted_at IS NULL", [$regId, $workshopId, $businessId]);
        if (!$reg) jsonResponse(false, 'រកមិនឃើញទិន្នន័យចុះឈ្មោះនេះទេ។');
        
        $notesUpdate = $reason !== '' ? "\nមូលហេតុបដិសេធ: " . $reason : "";
        $db->execute("UPDATE registrations SET status = 'rejected', notes = CONCAT(COALESCE(notes,''), ?) WHERE id = ?", [$notesUpdate, $regId]);
        auditLog('registration_rejected', 'registration', $regId, ['status' => $reg['status']], ['status' => 'rejected'], $businessId);
        jsonResponse(true, 'ការចុះឈ្មោះត្រូវបានបដិសេធ!');
    }

    public static function bulkApprove(int $workshopId): void {
        requireAuth();
        requirePermission('registration.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $ids = $_POST['ids'] ?? [];
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?: explode(',', $ids);
        }
        if (empty($ids) || !is_array($ids)) {
            jsonResponse(false, 'សូមជ្រើសរើសការចុះឈ្មោះយ៉ាងហោចណាស់មួយ។');
        }

        $workshop = $db->queryOne("SELECT payment_mode FROM workshops WHERE id = ?", [$workshopId]);
        $payStatusSql = ($workshop && $workshop['payment_mode'] === 'free') ? ", payment_status = 'paid'" : "";

        $count = 0;
        foreach ($ids as $regId) {
            $regId = (int)$regId;
            $reg = $db->queryOne("SELECT * FROM registrations WHERE id = ? AND workshop_id = ? AND business_id = ? AND deleted_at IS NULL", [$regId, $workshopId, $businessId]);
            if ($reg) {
                $db->execute("UPDATE registrations SET status = 'confirmed'{$payStatusSql}, confirmed_at = NOW() WHERE id = ?", [$regId]);
                $existingQr = $db->queryOne("SELECT id FROM participant_qr WHERE registration_id = ?", [$regId]);
                if (!$existingQr) {
                    $token = bin2hex(random_bytes(32));
                    $db->execute(
                        "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
                         VALUES (?, ?, ?, ?, ?, 1)",
                        [$regId, $reg['participant_id'], $businessId, $workshopId, $token]
                    );
                }
                auditLog('registration_approved', 'registration', $regId, ['status' => $reg['status']], ['status' => 'confirmed'], $businessId);
                $count++;
            }
        }
        jsonResponse(true, "បានអនុម័តការចុះឈ្មោះចំនួន {$count} ដោយជោគជ័យ!");
    }

    public static function sampleCsv(int $workshopId): void {
        requireAuth();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die('Workshop not found.'); }

        $tickets = $db->query("SELECT name FROM tickets WHERE workshop_id = ? AND business_id = ? AND status = 'active' ORDER BY sort_order ASC", [$workshopId, $businessId]);
        $ticketSample = !empty($tickets) ? $tickets[0]['name'] : 'ទូទៅ';

        $filename = 'sample_participants_' . slugify($workshop['name']) . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'ឈ្មោះសិក្ខាកាម',
            'លេខទូរស័ព្ទ',
            'អ៊ីមែល',
            'ភេទ',
            'អង្គភាព/ស្ថាប័ន',
            'តួនាទី',
            'រាជធានី-ខេត្ត',
            'ប្រភេទសំបុត្រ',
            'សម្គាល់'
        ]);

        fputcsv($output, [
            'សុខ ចាន់ដារ៉ា',
            '012345678',
            'sokh.chandara@example.com',
            'ប្រុស',
            'ABC Company',
            'ប្រធានផ្នែក',
            'ភ្នំពេញ',
            $ticketSample,
            'បានបង់ប្រាក់រួច'
        ]);
        fputcsv($output, [
            'កែវ សោភា',
            '098765432',
            'keo.sophea@example.com',
            'ស្រី',
            'XYZ Organization',
            'មន្ត្រីសម្របសម្រួល',
            'កណ្តាល',
            $ticketSample,
            ''
        ]);
        fputcsv($output, [
            'លី ម៉េងហុង',
            '070998877',
            'ly.menghong@example.com',
            'ប្រុស',
            'Global Tech',
            'វិស្វករ',
            'សៀមរាប',
            $ticketSample,
            'VIP Guest'
        ]);

        fclose($output);
        exit;
    }

    public static function import(int $workshopId): void {
        requireAuth();
        requirePermission('registration.create');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) {
            Session::flash('error', 'រកមិនឃើញសិក្ខាសាលាទេ។');
            redirect(APP_URL . '/workshops');
        }

        // Require platform activation fee to be paid before importing participants
        $billing = $db->queryOne("SELECT payment_status FROM workshop_billing WHERE workshop_id = ?", [$workshopId]);
        if (!$billing || $billing['payment_status'] !== 'paid') {
            Session::flash('error', 'មិនអាចនាំចូលសិក្ខាកាមបានទេ! សូមបង់ថ្លៃដំណើរការប្រព័ន្ធជាមុនសិន។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/activate');
        }

        if (empty($_FILES['import_file']['tmp_name']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'សូមជ្រើសរើសឯកសារ CSV ឬ Excel (.csv) ត្រឹមត្រូវ។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::flash('error', 'ទ្រង់ទ្រាយឯកសារមិនត្រឹមត្រូវ។ សូមប្រើប្រាស់ឯកសារប្រភេទ .csv។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
        }

        $defaultTicketId = !empty($_POST['default_ticket_id']) ? (int)$_POST['default_ticket_id'] : null;
        $defaultStatus = in_array($_POST['default_status'] ?? '', ['confirmed', 'pending', 'waitlisted']) ? $_POST['default_status'] : 'confirmed';
        $defaultPaymentStatus = in_array($_POST['default_payment_status'] ?? '', ['paid_cash', 'paid', 'unpaid', 'complimentary']) ? $_POST['default_payment_status'] : 'paid_cash';
        $defaultAttendeeType = !empty($_POST['default_attendee_type']) ? trim($_POST['default_attendee_type']) : 'general';

        $content = file_get_contents($file['tmp_name']);
        if ($content === false || trim($content) === '') {
            Session::flash('error', 'ឯកសារទទេ គ្មានទិន្នន័យ។');
            redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
        }

        // Remove UTF-8 BOM if present
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        // Normalize newlines
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);

        // Detect delimiter on first line
        $firstLine = trim($lines[0] ?? '');
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $availableTickets = $db->query("SELECT id, name, price FROM tickets WHERE workshop_id = ? AND business_id = ?", [$workshopId, $businessId]);
        $ticketMap = [];
        foreach ($availableTickets as $t) {
            $ticketMap[mb_strtolower(trim($t['name']))] = $t;
        }

        if (!$defaultTicketId && !empty($availableTickets)) {
            $defaultTicketId = (int)$availableTickets[0]['id'];
        }

        $imported = 0;
        $skipped = 0;
        $colMap = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $row = str_getcsv($line, $delimiter);
            if (empty($row) || (count($row) === 1 && trim($row[0]) === '')) continue;

            if ($colMap === null) {
                $colMap = [];
                $isHeader = false;
                foreach ($row as $colIdx => $colVal) {
                    $cleanVal = mb_strtolower(trim($colVal));
                    if (in_array($cleanVal, ['name', 'full_name', 'fullname', 'participant_name', 'ឈ្មោះ', 'ឈ្មោះសិក្ខាកាម', 'ឈ្មោះពេញ'])) {
                        $colMap['name'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['phone', 'tel', 'mobile', 'telephone', 'phone_number', 'ទូរស័ព្ទ', 'លេខទូរស័ព្ទ'])) {
                        $colMap['phone'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['email', 'e-mail', 'mail', 'អ៊ីមែល', 'អ៊ីម៉ែល'])) {
                        $colMap['email'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['gender', 'sex', 'ភេទ'])) {
                        $colMap['gender'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['company', 'organization', 'org', 'institution', 'ក្រុមហ៊ុន', 'ស្ថាប័ន', 'អង្គភាព'])) {
                        $colMap['company'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['position', 'role', 'job_title', 'title', 'មុខតំណែង', 'តួនាទី'])) {
                        $colMap['position'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['province', 'city', 'location', 'ខេត្ត', 'រាជធានី-ខេត្ត', 'រាជធានី'])) {
                        $colMap['province'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['ticket', 'ticket_name', 'ticket_type', 'សំបុត្រ', 'ប្រភេទសំបុត្រ'])) {
                        $colMap['ticket'] = $colIdx;
                        $isHeader = true;
                    } elseif (in_array($cleanVal, ['notes', 'note', 'remark', 'remarks', 'សម្គាល់', 'កំណត់សម្គាល់'])) {
                        $colMap['notes'] = $colIdx;
                        $isHeader = true;
                    }
                }

                if ($isHeader && isset($colMap['name'])) {
                    continue;
                } else {
                    $colMap = [
                        'name' => 0,
                        'phone' => 1,
                        'email' => 2,
                        'gender' => 3,
                        'company' => 4,
                        'position' => 5,
                        'province' => 6,
                        'ticket' => 7,
                        'notes' => 8
                    ];
                }
            }

            $name     = isset($colMap['name'], $row[$colMap['name']]) ? trim($row[$colMap['name']]) : '';
            $phone    = isset($colMap['phone'], $row[$colMap['phone']]) ? trim($row[$colMap['phone']]) : '';
            $email    = isset($colMap['email'], $row[$colMap['email']]) ? trim($row[$colMap['email']]) : '';
            $genderRaw= isset($colMap['gender'], $row[$colMap['gender']]) ? trim($row[$colMap['gender']]) : '';
            $company  = isset($colMap['company'], $row[$colMap['company']]) ? trim($row[$colMap['company']]) : '';
            $position = isset($colMap['position'], $row[$colMap['position']]) ? trim($row[$colMap['position']]) : '';
            $province = isset($colMap['province'], $row[$colMap['province']]) ? trim($row[$colMap['province']]) : '';
            $ticketRaw= isset($colMap['ticket'], $row[$colMap['ticket']]) ? trim($row[$colMap['ticket']]) : '';
            $notes    = isset($colMap['notes'], $row[$colMap['notes']]) ? trim($row[$colMap['notes']]) : '';

            if (empty($name) || in_array(mb_strtolower($name), ['name', 'ឈ្មោះ', 'ឈ្មោះសិក្ខាកាម', 'ល.រ'])) {
                $skipped++;
                continue;
            }

            $genderLower = mb_strtolower($genderRaw);
            if (str_contains($genderLower, 'ស្រី') || str_contains($genderLower, 'female') || $genderLower === 'f') {
                $gender = 'female';
            } elseif (str_contains($genderLower, 'ប្រុស') || str_contains($genderLower, 'male') || $genderLower === 'm') {
                $gender = 'male';
            } else {
                $gender = 'male';
            }

            if (empty($email)) {
                $slug = slugify($name ?: 'guest');
                $email = $slug . '.' . substr(uniqid(), -4) . '@workshopos.local';
            }

            $ticketId = $defaultTicketId;
            $ticketPrice = 0.00;
            if (!empty($ticketRaw)) {
                $cleanTicketName = mb_strtolower(trim($ticketRaw));
                if (isset($ticketMap[$cleanTicketName])) {
                    $ticketId = (int)$ticketMap[$cleanTicketName]['id'];
                    $ticketPrice = (float)$ticketMap[$cleanTicketName]['price'];
                }
            }
            if (!$ticketPrice && $ticketId) {
                foreach ($availableTickets as $t) {
                    if ((int)$t['id'] === $ticketId) {
                        $ticketPrice = (float)$t['price'];
                        break;
                    }
                }
            }

            $participant = null;
            if (!empty($email) && !str_contains($email, '@workshopos.local')) {
                $participant = $db->queryOne("SELECT id FROM participants WHERE email = ?", [$email]);
            }
            if (!$participant && !empty($phone)) {
                $participant = $db->queryOne("SELECT id FROM participants WHERE phone = ?", [$phone]);
            }

            if ($participant) {
                $participantId = (int)$participant['id'];
                $db->execute(
                    "UPDATE participants SET name = ?, gender = ?, company = COALESCE(NULLIF(?, ''), company), position = COALESCE(NULLIF(?, ''), position), province = COALESCE(NULLIF(?, ''), province) WHERE id = ?",
                    [$name, $gender, $company, $position, $province, $participantId]
                );
            } else {
                $db->execute(
                    "INSERT INTO participants (name, email, phone, gender, company, position, province, status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())",
                    [$name, $email, $phone, $gender, $company, $position, $province]
                );
                $participantId = (int)$db->lastInsertId();
            }

            $existingReg = $db->queryOne(
                "SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ? AND deleted_at IS NULL",
                [$workshopId, $participantId]
            );
            if ($existingReg) {
                $skipped++;
                continue;
            }

            $regCode = generateCode('REG', 10);
            $qrToken = bin2hex(random_bytes(16));

            $db->execute(
                "INSERT INTO registrations (
                    registration_code, business_id, workshop_id, participant_id, ticket_id,
                    status, payment_status, ticket_price, final_amount, attendee_type,
                    is_allowance_eligible, notes, registered_by, confirmed_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW(), NOW())",
                [
                    $regCode, $businessId, $workshopId, $participantId, $ticketId,
                    $defaultStatus, $defaultPaymentStatus, $ticketPrice, $ticketPrice,
                    $defaultAttendeeType, $notes, Auth::id()
                ]
            );
            $regId = (int)$db->lastInsertId();

            $db->execute(
                "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active, generated_at)
                 VALUES (?, ?, ?, ?, ?, 1, NOW())",
                [$regId, $participantId, $businessId, $workshopId, $qrToken]
            );

            $imported++;
        }

        auditLog('import_registrations', 'workshop', $workshopId, null, ['imported' => $imported, 'skipped' => $skipped], $businessId);

        if ($imported > 0) {
            Session::flash('success', "បាននាំចូលសិក្ខាកាមដោយជោគជ័យចំនួន <strong>{$imported}</strong> នាក់!" . ($skipped > 0 ? " (រំលង/ស្ទួនចំនួន {$skipped} នាក់)" : ""));
        } else {
            Session::flash('error', "ពុំមានទិន្នន័យសិក្ខាកាមត្រូវបាននាំចូលទេ។ (ទិន្នន័យស្ទួន ឬមិនត្រឹមត្រូវ: {$skipped} នាក់)។");
        }

        redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
    }

    public static function saveFormFields(int $workshopId): void {
        requireAuth();
        if (!Permission::has('workshop.edit') && !Permission::has('registration.edit')) {
            Permission::requirePermission('workshop.edit');
        }
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) {
            if (Request::isAjax()) {
                jsonResponse(false, 'រកមិនឃើញសិក្ខាសាលាទេ។');
            }
            Session::flash('error', 'រកមិនឃើញសិក្ខាសាលាទេ។');
            redirect(APP_URL . '/workshops');
        }

        $config = [
            'show_phone'       => isset($_POST['show_phone']) ? 1 : 0,
            'require_phone'    => (isset($_POST['show_phone']) && isset($_POST['require_phone'])) ? 1 : 0,
            'show_email'       => isset($_POST['show_email']) ? 1 : 0,
            'require_email'    => (isset($_POST['show_email']) && isset($_POST['require_email'])) ? 1 : 0,
            'show_gender'      => isset($_POST['show_gender']) ? 1 : 0,
            'require_gender'   => (isset($_POST['show_gender']) && isset($_POST['require_gender'])) ? 1 : 0,
            'show_province'    => isset($_POST['show_province']) ? 1 : 0,
            'require_province' => (isset($_POST['show_province']) && isset($_POST['require_province'])) ? 1 : 0,
            'show_company'     => isset($_POST['show_company']) ? 1 : 0,
            'require_company'  => (isset($_POST['show_company']) && isset($_POST['require_company'])) ? 1 : 0,
            'show_position'    => isset($_POST['show_position']) ? 1 : 0,
            'require_position' => (isset($_POST['show_position']) && isset($_POST['require_position'])) ? 1 : 0,
        ];

        // Update custom fields active/inactive status if passed
        if (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
            foreach ($_POST['custom_fields'] as $fieldId => $statusVal) {
                $status = ($statusVal === 'active' || $statusVal === '1' || $statusVal === 1) ? 'active' : 'inactive';
                $db->execute(
                    "UPDATE registration_fields SET status = ? WHERE id = ? AND workshop_id = ?",
                    [$status, (int)$fieldId, $workshopId]
                );
            }
        }

        // If company or position is toggled off in standard fields, make sure any duplicate custom fields are also set to inactive
        if ($config['show_company'] === 0) {
            $db->execute("UPDATE registration_fields SET status = 'inactive' WHERE workshop_id = ? AND (field_label LIKE '%ក្រុមហ៊ុន%' OR field_label LIKE '%ស្ថាប័ន%')", [$workshopId]);
        }
        if ($config['show_position'] === 0) {
            $db->execute("UPDATE registration_fields SET status = 'inactive' WHERE workshop_id = ? AND (field_label LIKE '%តួនាទី%' OR field_label LIKE '%មុខតំណែង%')", [$workshopId]);
        }

        $db->execute(
            "UPDATE workshops SET form_fields = ? WHERE id = ? AND business_id = ?",
            [json_encode($config, JSON_UNESCAPED_UNICODE), $workshopId, $businessId]
        );

        auditLog('update_form_fields', 'workshop', $workshopId, null, $config, $businessId);

        if (Request::isAjax()) {
            jsonResponse(true, 'បានរក្សាទុកការកំណត់ទម្រង់ចុះឈ្មោះដោយជោគជ័យ!');
        }

        Session::flash('success', 'បានរក្សាទុកការកំណត់ទម្រង់ចុះឈ្មោះដោយជោគជ័យ!');
        redirect(APP_URL . '/workshops/' . $workshopId . '/registrations');
    }
}
