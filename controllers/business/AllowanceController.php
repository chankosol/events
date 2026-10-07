<?php
// controllers/business/AllowanceController.php

class AllowanceController {

    /**
     * View allowance dashboard, configuration, and recipient list
     */
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        if (!$workshop) {
            http_response_code(404);
            die('Workshop not found.');
        }

        // Get or initialize allowance config
        $allowance = $db->queryOne(
            "SELECT * FROM workshop_allowances WHERE workshop_id = ? AND business_id = ?",
            [$workshopId, $businessId]
        );

        if (!$allowance) {
            // Auto-create default allowance rule ($50 default)
            $db->execute(
                "INSERT INTO workshop_allowances (workshop_id, business_id, allowance_name, default_amount, currency, min_attendance_percent, require_feedback, status)
                 VALUES (?, ?, 'ប្រាក់ឧបត្ថម្ភសោហ៊ុយ និងថ្លៃស្នាក់នៅ', 50.00, 'USD', 80, 0, 'active')",
                [$workshopId, $businessId]
            );
            $allowance = $db->queryOne(
                "SELECT * FROM workshop_allowances WHERE workshop_id = ? AND business_id = ?",
                [$workshopId, $businessId]
            );
        }

        // Total workshop sessions for attendance rate calculation
        $totalSessions = (int)$db->queryOne(
            "SELECT COUNT(*) as c FROM workshop_sessions WHERE workshop_id = ? AND status != 'cancelled'",
            [$workshopId]
        )['c'];

        // Get list of all confirmed/attended attendees with attendance & disbursement status
        $attendees = $db->query(
            "SELECT r.id as reg_id, r.registration_code, r.id_card_number, r.bank_name, r.bank_account_number,
                    p.id as participant_id, p.name, p.phone, p.gender, p.province, p.photo,
                    d.province as delegation_province, d.organization as delegation_org, d.id as delegation_id,
                    ad.id as disbursement_id, ad.amount as disbursed_amount, ad.receipt_voucher_no, ad.payout_method,
                    ad.disbursed_to_name, ad.disbursed_to_type, ad.disbursed_at, u.name as disbursed_by_name,
                    (SELECT COUNT(*) FROM attendance a WHERE a.registration_id = r.id AND a.checked_in_at IS NOT NULL) as checked_in_main,
                    (SELECT COUNT(*) FROM session_attendance sa WHERE sa.registration_id = r.id AND sa.status = 'present') as sessions_attended
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
             LEFT JOIN allowance_disbursements ad ON ad.registration_id = r.id
             LEFT JOIN users u ON u.id = ad.disbursed_by
             WHERE r.workshop_id = ? AND r.deleted_at IS NULL AND r.status IN ('confirmed','attended','completed')
             ORDER BY COALESCE(d.province, p.province) ASC, p.name ASC",
            [$workshopId]
        );

        // Process eligibility for each attendee
        $totalEligible = 0;
        $totalDisbursed = 0;
        $totalDisbursedAmount = 0.0;
        $totalBudget = 0.0;

        foreach ($attendees as &$att) {
            // Calculate attendance percentage
            if ($totalSessions > 0) {
                $rate = round(($att['sessions_attended'] / $totalSessions) * 100);
            } else {
                $rate = $att['checked_in_main'] > 0 ? 100 : 0;
            }
            $att['attendance_rate'] = $rate;

            $isEligible = ($rate >= $allowance['min_attendance_percent']) || ($att['checked_in_main'] > 0);
            $att['is_eligible'] = $isEligible;

            if ($isEligible) {
                $totalEligible++;
                $totalBudget += (float)$allowance['default_amount'];
            }

            if (!empty($att['disbursement_id'])) {
                $totalDisbursed++;
                $totalDisbursedAmount += (float)$att['disbursed_amount'];
            }
        }
        unset($att);

        $stats = [
            'total_attendees'       => count($attendees),
            'total_eligible'        => $totalEligible,
            'total_disbursed'       => $totalDisbursed,
            'total_pending'         => $totalEligible - $totalDisbursed,
            'total_budget'          => $totalBudget,
            'total_disbursed_amount'=> $totalDisbursedAmount,
            'remaining_balance'     => max(0, $totalBudget - $totalDisbursedAmount),
        ];

        // Group by province for summary breakdown
        $provinces = cambodiaProvinces();
        $title = 'ការគ្រប់គ្រងកញ្ចប់ថវិកា និងប្រាក់ឧបត្ថម្ភ - ' . $workshop['name'];

        ob_start();
        require VIEWS_PATH . '/business/allowances/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Save Allowance Rules / Settings
     */
    public static function saveConfig(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.edit');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $name           = trim($_POST['allowance_name'] ?? 'ប្រាក់ឧបត្ថម្ភសោហ៊ុយ និងថ្លៃស្នាក់នៅ');
        $defaultAmount  = (float)($_POST['default_amount'] ?? 50.00);
        $currency       = trim($_POST['currency'] ?? 'USD');
        $minAttendance  = max(0, min(100, (int)($_POST['min_attendance_percent'] ?? 80)));
        $allowHeadClaim = isset($_POST['allow_delegation_head_claim']) ? 1 : 0;

        $db->execute(
            "UPDATE workshop_allowances 
             SET allowance_name = ?, default_amount = ?, currency = ?, min_attendance_percent = ?, allow_delegation_head_claim = ?
             WHERE workshop_id = ? AND business_id = ?",
            [$name, $defaultAmount, $currency, $minAttendance, $allowHeadClaim, $workshopId, $businessId]
        );

        Session::flash('success', 'បានរក្សាទុកការកំណត់ប្រាក់ឧបត្ថម្ភជោគជ័យ!');
        redirect(APP_URL . "/workshops/{$workshopId}/allowances");
    }

    /**
     * Dedicated Live Payout Desk (Fast QR Scanning + Anti-Double Payout + Signature Pad)
     */
    public static function desk(int $workshopId): void {
        requireAuth();
        requirePermission('payment.verify');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        if (!$workshop) {
            http_response_code(404);
            die('Workshop not found.');
        }

        $allowance = $db->queryOne(
            "SELECT * FROM workshop_allowances WHERE workshop_id = ? AND business_id = ?",
            [$workshopId, $businessId]
        );

        // Get delegations for group payout
        $delegations = $db->query(
            "SELECT d.*, 
                    COUNT(r.id) as total_members,
                    SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as attended_members,
                    SUM(CASE WHEN ad.id IS NOT NULL THEN 1 ELSE 0 END) as paid_members
             FROM workshop_delegations d
             LEFT JOIN registrations r ON r.delegation_id = d.id AND r.deleted_at IS NULL
             LEFT JOIN attendance a ON a.registration_id = r.id AND a.checked_in_at IS NOT NULL
             LEFT JOIN allowance_disbursements ad ON ad.registration_id = r.id
             WHERE d.workshop_id = ? AND d.business_id = ?
             GROUP BY d.id
             ORDER BY d.province ASC",
            [$workshopId, $businessId]
        );

        $title = 'តុបើកប្រាក់ឧបត្ថម្ភ (Payout Desk) - ' . $workshop['name'];
        $breadcrumbs = [
            ['label' => 'ទំព័រដើម',        'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
            ['label' => 'សិក្ខាសាលា',      'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
            ['label' => mb_strimwidth($workshop['name'], 0, 35, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
            ['label' => 'ថវិកា / ប្រាក់ឧបត្ថម្ភ', 'url' => APP_URL . '/workshops/' . $workshop['id'] . '/allowances', 'icon' => 'cash-stack'],
            ['label' => 'តុស្កេនបើកប្រាក់ (Payout Desk)', 'url' => null,                      'icon' => 'upc-scan'],
        ];

        ob_start();
        require VIEWS_PATH . '/business/allowances/desk.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    /**
     * Verify attendee eligibility via AJAX when QR is scanned
     */
    public static function checkAttendee(int $workshopId): void {
        requireAuth();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $token = trim($_GET['token'] ?? '');
        $code  = trim($_GET['code'] ?? '');

        if (empty($token) && empty($code)) {
            jsonResponse(false, 'សូមស្កេន QR Code ឬបញ្ចូលកូដចុះឈ្មោះ');
        }

        // Find registration
        if (!empty($token)) {
            $reg = $db->queryOne(
                "SELECT r.*, p.name, p.phone, p.gender, p.province, p.photo,
                        d.province as delegation_province, d.organization as delegation_org, d.head_name as delegation_head
                 FROM participant_qr pq
                 JOIN registrations r ON r.id = pq.registration_id
                 JOIN participants p ON p.id = r.participant_id
                 LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
                 WHERE pq.token = ? AND r.workshop_id = ? AND r.business_id = ?",
                [$token, $workshopId, $businessId]
            );
        } else {
            $reg = $db->queryOne(
                "SELECT r.*, p.name, p.phone, p.gender, p.province, p.photo,
                        d.province as delegation_province, d.organization as delegation_org, d.head_name as delegation_head
                 FROM registrations r
                 JOIN participants p ON p.id = r.participant_id
                 LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
                 WHERE (r.registration_code = ? OR p.phone = ?) AND r.workshop_id = ? AND r.business_id = ?",
                [$code, $code, $workshopId, $businessId]
            );
        }

        if (!$reg) {
            jsonResponse(false, 'រកមិនឃើញទិន្នន័យសិក្ខាកាមនៅក្នុងសិក្ខាសាលានេះទេ!', [], 404);
        }

        $allowance = $db->queryOne("SELECT * FROM workshop_allowances WHERE workshop_id = ?", [$workshopId]);

        // Check if ALREADY DISBURSED! (Anti-Double Payout)
        $existingDisbursement = $db->queryOne(
            "SELECT ad.*, u.name as staff_name 
             FROM allowance_disbursements ad
             JOIN users u ON u.id = ad.disbursed_by
             WHERE ad.registration_id = ?",
            [$reg['id']]
        );

        // Check attendance
        $attendance = $db->queryOne(
            "SELECT checked_in_at FROM attendance WHERE registration_id = ? AND checked_in_at IS NOT NULL",
            [$reg['id']]
        );

        $totalSessions = (int)$db->queryOne("SELECT COUNT(*) as c FROM workshop_sessions WHERE workshop_id = ? AND status != 'cancelled'", [$workshopId])['c'];
        $sessionsAttended = (int)$db->queryOne("SELECT COUNT(*) as c FROM session_attendance WHERE registration_id = ? AND status = 'present'", [$reg['id']])['c'];

        $attRate = $totalSessions > 0 ? round(($sessionsAttended / $totalSessions) * 100) : ($attendance ? 100 : 0);
        $isEligible = ($attendance !== null) && ($attRate >= ($allowance['min_attendance_percent'] ?? 80));

        jsonResponse(true, 'ទិន្នន័យសិក្ខាកាម', [
            'registration'      => $reg,
            'allowance'         => $allowance,
            'attendance_rate'   => $attRate,
            'sessions_attended' => $sessionsAttended,
            'total_sessions'    => $totalSessions,
            'is_eligible'       => $isEligible,
            'already_disbursed' => $existingDisbursement !== null,
            'disbursement_info' => $existingDisbursement,
        ]);
    }

    /**
     * Process Individual Payout (With Anti-Double Claim Verification)
     */
    public static function disburse(int $workshopId): void {
        requireAuth();
        requirePermission('payment.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $userId     = Auth::id();
        $db         = Database::getInstance();

        $regId       = (int)($_POST['registration_id'] ?? 0);
        $amount      = (float)($_POST['amount'] ?? 0.0);
        $method      = in_array($_POST['payout_method'] ?? '', ['cash','bakong','bank_transfer']) ? $_POST['payout_method'] : 'cash';
        $disbursedTo = trim($_POST['disbursed_to_name'] ?? '');
        $idCard      = trim($_POST['recipient_id_card'] ?? '');
        $signature   = $_POST['signature_data'] ?? null;
        $notes       = trim($_POST['notes'] ?? '');

        if (!$regId || $amount <= 0 || empty($disbursedTo)) {
            jsonResponse(false, 'ទិន្នន័យមិនគ្រប់គ្រាន់ទេ សូមពិនិត្យឈ្មោះ និងចំនួនទឹកប្រាក់!');
        }

        $reg = $db->queryOne(
            "SELECT r.*, p.name as participant_name 
             FROM registrations r 
             JOIN participants p ON p.id = r.participant_id
             WHERE r.id = ? AND r.workshop_id = ? AND r.business_id = ?",
            [$regId, $workshopId, $businessId]
        );
        if (!$reg) {
            jsonResponse(false, 'រកមិនឃើញការចុះឈ្មោះនេះទេ!', [], 404);
        }

        $allowance = $db->queryOne("SELECT * FROM workshop_allowances WHERE workshop_id = ?", [$workshopId]);

        // CRITICAL: Double Payout Protection Check
        $alreadyPaid = $db->queryOne(
            "SELECT ad.*, u.name as staff_name 
             FROM allowance_disbursements ad 
             JOIN users u ON u.id = ad.disbursed_by 
             WHERE ad.registration_id = ?",
            [$regId]
        );

        if ($alreadyPaid) {
            jsonResponse(false, "កំហុស៖ សិក្ខាកាមនេះបានបើកប្រាក់រួចហើយកាលពីម៉ោង {$alreadyPaid['disbursed_at']} ដោយបុគ្គលិក {$alreadyPaid['staff_name']} (ប័ណ្ណលេខ៖ {$alreadyPaid['receipt_voucher_no']})!", [
                'existing' => $alreadyPaid
            ], 409);
        }

        $voucherNo = 'VCH-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $db->execute(
            "INSERT INTO allowance_disbursements 
             (workshop_id, business_id, allowance_id, registration_id, participant_id, delegation_id,
              amount, currency, payout_method, disbursed_to_type, disbursed_to_name, recipient_id_card,
              receipt_voucher_no, signature_data, disbursed_by, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'individual', ?, ?, ?, ?, ?, ?)",
            [
                $workshopId, $businessId, $allowance['id'], $regId, $reg['participant_id'], $reg['delegation_id'],
                $amount, $allowance['currency'] ?? 'USD', $method, $disbursedTo, $idCard,
                $voucherNo, $signature, $userId, $notes
            ]
        );

        auditLog('allowance_disbursed', 'registration', $regId, null, [
            'amount' => $amount, 'voucher' => $voucherNo, 'method' => $method
        ], $businessId);

        jsonResponse(true, "បានបើកប្រាក់ឧបត្ថម្ភជោគជ័យ! ប័ណ្ណលេខ៖ {$voucherNo}", [
            'voucher_no' => $voucherNo,
            'amount'     => $amount,
            'currency'   => $allowance['currency'] ?? 'USD',
            'disbursed_at' => date('d/m/Y H:i')
        ]);
    }

    /**
     * Batch Payout for a Delegation Head
     */
    public static function disburseDelegation(int $workshopId): void {
        requireAuth();
        requirePermission('payment.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $userId     = Auth::id();
        $db         = Database::getInstance();

        $delegationId = (int)($_POST['delegation_id'] ?? 0);
        $headName     = trim($_POST['head_name'] ?? '');
        $headIdCard   = trim($_POST['head_id_card'] ?? '');
        $signature    = $_POST['signature_data'] ?? null;
        $method       = in_array($_POST['payout_method'] ?? '', ['cash','bakong','bank_transfer']) ? $_POST['payout_method'] : 'cash';

        $delegation = $db->queryOne("SELECT * FROM workshop_delegations WHERE id = ? AND workshop_id = ?", [$delegationId, $workshopId]);
        if (!$delegation) {
            jsonResponse(false, 'រកមិនឃើញប្រតិភូនេះទេ!', [], 404);
        }

        $allowance = $db->queryOne("SELECT * FROM workshop_allowances WHERE workshop_id = ?", [$workshopId]);
        $ratePerPerson = (float)$allowance['default_amount'];

        // Get members of this delegation who have verified attendance AND HAVE NOT BEEN PAID YET
        $unpaidMembers = $db->query(
            "SELECT r.id as reg_id, r.participant_id, p.name 
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             JOIN attendance a ON a.registration_id = r.id AND a.checked_in_at IS NOT NULL
             LEFT JOIN allowance_disbursements ad ON ad.registration_id = r.id
             WHERE r.delegation_id = ? AND r.deleted_at IS NULL AND ad.id IS NULL",
            [$delegationId]
        );

        if (empty($unpaidMembers)) {
            jsonResponse(false, 'គ្មានសមាជិកដែលមានវត្តមាន និងមិនទាន់បើកប្រាក់នៅក្នុងប្រតិភូនេះទេ!');
        }

        $totalMembers = count($unpaidMembers);
        $totalAmount = $totalMembers * $ratePerPerson;
        $batchVoucher = 'DEL-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Disburse for each verified member
        foreach ($unpaidMembers as $mem) {
            $voucherNo = $batchVoucher . '-' . $mem['reg_id'];
            $db->execute(
                "INSERT INTO allowance_disbursements 
                 (workshop_id, business_id, allowance_id, registration_id, participant_id, delegation_id,
                  amount, currency, payout_method, disbursed_to_type, disbursed_to_name, recipient_id_card,
                  receipt_voucher_no, signature_data, disbursed_by, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'delegation_head', ?, ?, ?, ?, ?, ?)",
                [
                    $workshopId, $businessId, $allowance['id'], $mem['reg_id'], $mem['participant_id'], $delegationId,
                    $ratePerPerson, $allowance['currency'] ?? 'USD', $method, $headName, $headIdCard,
                    $voucherNo, $signature, $userId, "បើកតាមប្រធានប្រតិភូ {$delegation['province']} (សរុប {$totalMembers} នាក់)"
                ]
            );
        }

        auditLog('delegation_allowance_disbursed', 'delegation', $delegationId, null, [
            'count' => $totalMembers, 'total_amount' => $totalAmount, 'voucher' => $batchVoucher
        ], $businessId);

        jsonResponse(true, "បានបើកប្រាក់សរុបសម្រាប់ប្រតិភូ {$delegation['province']} ចំនួន {$totalMembers} នាក់ ($" . number_format($totalAmount, 2) . ") ជូនលោក/លោកស្រី {$headName} ជោគជ័យ!", [
            'voucher_no'    => $batchVoucher,
            'total_members' => $totalMembers,
            'total_amount'  => $totalAmount,
        ]);
    }

    /**
     * Printable Payroll / Disbursement Audit Sheet (PDF / Official Document)
     */
    public static function sheet(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT w.*, b.name as business_name FROM workshops w JOIN businesses b ON b.id = w.business_id WHERE w.id = ? AND w.business_id = ?",
            [$workshopId, $businessId]
        );
        if (!$workshop) { die('Workshop not found.'); }

        $allowance = $db->queryOne("SELECT * FROM workshop_allowances WHERE workshop_id = ?", [$workshopId]);

        // Get complete disbursement records with signatures
        $records = $db->query(
            "SELECT ad.*, p.name as participant_name, p.gender, p.province as participant_province, p.phone,
                    d.province as delegation_province, d.organization,
                    u.name as staff_name, r.registration_code
             FROM allowance_disbursements ad
             JOIN registrations r ON r.id = ad.registration_id
             JOIN participants p ON p.id = ad.participant_id
             LEFT JOIN workshop_delegations d ON d.id = ad.delegation_id
             LEFT JOIN users u ON u.id = ad.disbursed_by
             WHERE ad.workshop_id = ?
             ORDER BY COALESCE(d.province, p.province) ASC, ad.disbursed_at ASC",
            [$workshopId]
        );

        $title = 'បញ្ជីបើកប្រាក់ឧបត្ថម្ភសរុប (Payroll Audit Sheet) - ' . $workshop['name'];
        require VIEWS_PATH . '/business/allowances/sheet.php';
    }
}
