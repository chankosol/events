<?php
// controllers/public/HelperCheckinController.php

class HelperCheckinController {

    public static function station(string $token): void {
        $db = Database::getInstance();
        $token = trim($token);

        $pass = $db->queryOne(
            "SELECT p.*, w.name as workshop_name, w.start_date, w.venue, w.status as workshop_status,
                    b.name as business_name, bb.logo_path as business_logo
             FROM checkin_helper_passes p
             JOIN workshops w ON w.id = p.workshop_id AND w.deleted_at IS NULL
             JOIN businesses b ON b.id = p.business_id
             LEFT JOIN business_branding bb ON bb.business_id = b.id
             WHERE p.token = ?",
            [$token]
        );

        if (!$pass || !$pass['is_active']) {
            $errorTitle = 'លីងជំនួយការមិនត្រឹមត្រូវ';
            $errorMessage = 'លីងជំនួយការស្កេននេះមិនត្រឹមត្រូវ ឬត្រូវបានបិទដំណើរការដោយអ្នកគ្រប់គ្រងរួចហើយ។';
            require VIEWS_PATH . '/public/helper_error.php';
            exit;
        }

        if (strtotime($pass['expires_at']) <= time()) {
            $errorTitle = 'លីងបានផុតសុពលភាព';
            $errorMessage = 'លីងជំនួយការស្កេននេះបានផុតកំណត់សុពលភាពកាលពីម៉ោង ' . date('H:i d/m/Y', strtotime($pass['expires_at'])) . '។';
            require VIEWS_PATH . '/public/helper_error.php';
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::join($token);
            return;
        }

        // Manage Device Token (stored in Cookie)
        $cookieKey = 'ch_dev_' . $pass['id'];
        $devToken = $_COOKIE[$cookieKey] ?? '';

        $device = null;
        if (!empty($devToken)) {
            $device = $db->queryOne("SELECT * FROM checkin_helper_devices WHERE pass_id = ? AND device_token = ?", [$pass['id'], $devToken]);
        }

        // Check if device was revoked/deactivated by manager
        if ($device && isset($device['is_active']) && (int)$device['is_active'] === 0) {
            $errorTitle = 'ការចូលប្រើប្រាស់ត្រូវបានបិទ';
            $errorMessage = 'អ្នកគ្រប់គ្រងបានបិទដំណើរការឧបករណ៍របស់លោកអ្នក (' . htmlspecialchars($device['helper_name'] ?: 'ជំនួយការ') . ') សម្រាប់តុស្កេននេះហើយ។';
            require VIEWS_PATH . '/public/helper_error.php';
            exit;
        }

        // If device is not registered yet OR helper_name is empty, require entering name first
        if (!$device || empty($device['helper_name'])) {
            $cntRow = $db->queryOne("SELECT COUNT(*) as c FROM checkin_helper_devices WHERE pass_id = ? AND is_active = 1", [$pass['id']]);
            $currentCount = (int)($cntRow['c'] ?? 0);
            if (!$device && $currentCount >= (int)$pass['max_devices']) {
                $errorTitle = 'ឧបករណ៍លើសកម្រិតកំណត់';
                $errorMessage = "លីងជំនួយការនេះបានដល់កម្រិតកំណត់ចំនួនឧបករណ៍ហើយ (អនុញ្ញាតត្រឹម {$pass['max_devices']} គ្រឿង)។ ប្រសិនបើលោកអ្នកជាក្រុមការងារ សូមទាក់ទងអ្នកគ្រប់គ្រងដើម្បីបន្ថែមឧបករណ៍ ឬបង្កើតលីងថ្មី។";
                require VIEWS_PATH . '/public/helper_error.php';
                exit;
            }

            require VIEWS_PATH . '/public/helper_join.php';
            exit;
        }

        // Update active timestamp
        $db->execute("UPDATE checkin_helper_devices SET last_active_at = NOW(), ip_address = ? WHERE id = ?", [$_SERVER['REMOTE_ADDR'] ?? '', $device['id']]);

        // Attendance stats for this workshop
        $stats = $db->queryOne(
            "SELECT
               COUNT(r.id) as total_confirmed,
               SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in
             FROM registrations r
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.status IN ('confirmed','attended','completed') AND r.deleted_at IS NULL",
            [$pass['workshop_id']]
        );

        $recentAttendance = $db->query(
            "SELECT a.checked_in_at, a.check_in_method, p.name, p.phone,
                    COALESCE(d.organization, p.company, '-') as company,
                    COALESCE(d.province, p.province, 'ទូទៅ') as province,
                    COALESCE(p.position, '-') as position,
                    r.id as registration_id, r.registration_code
             FROM attendance a
             JOIN registrations r ON r.id = a.registration_id
             JOIN participants p ON p.id = a.participant_id
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
             WHERE a.workshop_id = ? AND a.checked_in_at IS NOT NULL
             ORDER BY a.checked_in_at DESC
             LIMIT 10",
            [$pass['workshop_id']]
        );

        require VIEWS_PATH . '/public/helper_checkin.php';
    }

    public static function join(string $token): void {
        $db = Database::getInstance();
        $token = trim($token);

        $pass = $db->queryOne(
            "SELECT p.*, w.name as workshop_name, w.start_date, w.venue, w.status as workshop_status,
                    b.name as business_name, bb.logo_path as business_logo
             FROM checkin_helper_passes p
             JOIN workshops w ON w.id = p.workshop_id AND w.deleted_at IS NULL
             JOIN businesses b ON b.id = p.business_id
             LEFT JOIN business_branding bb ON bb.business_id = b.id
             WHERE p.token = ?",
            [$token]
        );

        if (!$pass || !$pass['is_active']) {
            $errorTitle = 'លីងជំនួយការមិនត្រឹមត្រូវ';
            $errorMessage = 'លីងជំនួយការស្កេននេះមិនត្រឹមត្រូវ ឬត្រូវបានបិទដំណើរការដោយអ្នកគ្រប់គ្រងរួចហើយ។';
            require VIEWS_PATH . '/public/helper_error.php';
            exit;
        }

        if (strtotime($pass['expires_at']) <= time()) {
            $errorTitle = 'លីងបានផុតសុពលភាព';
            $errorMessage = 'លីងជំនួយការស្កេននេះបានផុតកំណត់សុពលភាពហើយ។';
            require VIEWS_PATH . '/public/helper_error.php';
            exit;
        }

        $helperName = trim($_POST['helper_name'] ?? '');
        if (mb_strlen($helperName) < 2) {
            $joinError = 'សូមបញ្ចូលឈ្មោះរបស់អ្នកយ៉ាងតិច ២ តួអក្សរ។';
            require VIEWS_PATH . '/public/helper_join.php';
            exit;
        }

        // Manage Device Token (stored in Cookie)
        $cookieKey = 'ch_dev_' . $pass['id'];
        $devToken = $_COOKIE[$cookieKey] ?? '';
        if (empty($devToken)) {
            $devToken = 'dev_' . bin2hex(random_bytes(12));
            setcookie($cookieKey, $devToken, time() + (86400 * 30), '/');
            $_COOKIE[$cookieKey] = $devToken;
        }

        $device = $db->queryOne("SELECT * FROM checkin_helper_devices WHERE pass_id = ? AND device_token = ?", [$pass['id'], $devToken]);
        if ($device) {
            if (isset($device['is_active']) && (int)$device['is_active'] === 0) {
                $errorTitle = 'ការចូលប្រើប្រាស់ត្រូវបានបិទ';
                $errorMessage = 'អ្នកគ្រប់គ្រងបានបិទដំណើរការឧបករណ៍របស់លោកអ្នកសម្រាប់តុស្កេននេះហើយ។';
                require VIEWS_PATH . '/public/helper_error.php';
                exit;
            }

            $db->execute(
                "UPDATE checkin_helper_devices SET helper_name = ?, last_active_at = NOW(), ip_address = ? WHERE id = ?",
                [$helperName, $_SERVER['REMOTE_ADDR'] ?? '', $device['id']]
            );
        } else {
            $cntRow = $db->queryOne("SELECT COUNT(*) as c FROM checkin_helper_devices WHERE pass_id = ? AND is_active = 1", [$pass['id']]);
            $currentCount = (int)($cntRow['c'] ?? 0);
            if ($currentCount >= (int)$pass['max_devices']) {
                $errorTitle = 'ឧបករណ៍លើសកម្រិតកំណត់';
                $errorMessage = "លីងជំនួយការនេះបានដល់កម្រិតកំណត់ចំនួនឧបករណ៍ហើយ (អនុញ្ញាតត្រឹម {$pass['max_devices']} គ្រឿង)។";
                require VIEWS_PATH . '/public/helper_error.php';
                exit;
            }

            $devName = self::detectDeviceName();
            $db->execute(
                "INSERT INTO checkin_helper_devices (pass_id, device_token, helper_name, is_active, device_name, ip_address, last_active_at)
                 VALUES (?, ?, ?, 1, ?, ?, NOW())",
                [$pass['id'], $devToken, $helperName, $devName, $_SERVER['REMOTE_ADDR'] ?? '']
            );
            $db->execute(
                "UPDATE checkin_helper_passes SET device_count = (SELECT COUNT(*) FROM checkin_helper_devices WHERE pass_id = ? AND is_active = 1) WHERE id = ?",
                [$pass['id'], $pass['id']]
            );
        }

        header('Location: ' . APP_URL . '/scan/helper/' . $token);
        exit;
    }

    public static function scan(): void {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $passToken = trim($_POST['pass_token'] ?? '');
        $qrToken   = trim($_POST['qr_token'] ?? '');
        $devToken  = trim($_POST['device_token'] ?? '');

        if (empty($passToken) || empty($qrToken)) {
            echo json_encode(['success' => false, 'message' => 'ទិន្នន័យស្កេនមិនត្រឹមត្រូវ។']);
            exit;
        }

        $pass = $db->queryOne("SELECT * FROM checkin_helper_passes WHERE token = ? AND is_active = 1", [$passToken]);
        if (!$pass) {
            echo json_encode(['success' => false, 'message' => 'លីងជំនួយការត្រូវបានបិទ ឬមិនត្រឹមត្រូវ។']);
            exit;
        }

        if (strtotime($pass['expires_at']) <= time()) {
            echo json_encode(['success' => false, 'message' => 'លីងជំនួយការបានផុតសុពលភាពហើយ។']);
            exit;
        }

        // Validate device token if provided
        $device = null;
        if (!empty($devToken)) {
            $device = $db->queryOne("SELECT id, helper_name, is_active FROM checkin_helper_devices WHERE pass_id = ? AND device_token = ?", [$pass['id'], $devToken]);
            if ($device) {
                if (isset($device['is_active']) && (int)$device['is_active'] === 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'ការអនុញ្ញាតរបស់អ្នកត្រូវបានបិទដោយអ្នកគ្រប់គ្រង។',
                        'revoked' => true
                    ]);
                    exit;
                }
                $db->execute("UPDATE checkin_helper_devices SET last_active_at = NOW() WHERE id = ?", [$device['id']]);
            }
        }

        $workshopId = (int)$pass['workshop_id'];
        $businessId = (int)$pass['business_id'];

        // Find participant by QR token or Registration Code
        $qr = $db->queryOne(
            "SELECT pq.*, r.id as registration_id, r.status as reg_status, r.payment_status, r.is_vip, r.registration_code, r.id_card_number,
                    p.name, p.email, p.phone, p.company, p.position, p.photo, p.gender, p.province as participant_province,
                    d.province as delegation_province, d.organization as delegation_org,
                    t.name as ticket_name, t.ticket_type
             FROM participant_qr pq
             JOIN registrations r ON r.id = pq.registration_id
             JOIN participants p ON p.id = pq.participant_id
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
             LEFT JOIN tickets t ON t.id = r.ticket_id
             WHERE (pq.token = ? OR r.registration_code = ?) AND pq.workshop_id = ? AND pq.is_active = 1",
            [$qrToken, $qrToken, $workshopId]
        );

        if (!$qr) {
            $db->execute(
                "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,'invalid')",
                [$businessId, $workshopId, $qrToken, 'checkin', $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
            );
            echo json_encode(['success' => false, 'message' => 'មិនមានកូដ QR នេះក្នុងបញ្ជីសិក្ខាកាមឡើយ។']);
            exit;
        }

        if (!in_array($qr['reg_status'], ['confirmed', 'attended', 'completed'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ការចុះឈ្មោះមិនទាន់ត្រូវបានបញ្ជាក់ (Confirmed) នៅឡើយទេ។',
                'participant' => ['name' => $qr['name'], 'phone' => $qr['phone']]
            ]);
            exit;
        }

        // Check if already checked in
        $existing = $db->queryOne("SELECT * FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);

        $photoUrl = '';
        if (!empty($qr['photo'])) {
            $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($qr['photo']) : APP_URL . '/' . ltrim($qr['photo'], '/\\');
        }

        $participantPayload = [
            'reg_id'       => $qr['registration_id'],
            'name'         => $qr['name'],
            'email'        => $qr['email'],
            'phone'        => $qr['phone'],
            'gender'       => $qr['gender'],
            'company'      => $qr['company'] ?: ($qr['delegation_org'] ?? 'មិនបានបញ្ជាក់ស្ថាប័ន'),
            'province'     => $qr['delegation_province'] ?: ($qr['participant_province'] ?? 'ទូទៅ'),
            'position'     => $qr['position'] ?: '-',
            'photo_url'    => $photoUrl,
            'id_card'      => $qr['id_card_number'],
            'ticket'       => $qr['ticket_name'] ?? 'ស្តង់ដារ',
            'reg_code'     => $qr['registration_code'],
            'is_vip'       => (bool)$qr['is_vip'],
            'payment_status' => $qr['payment_status']
        ];

        if ($existing && $existing['checked_in_at']) {
            $db->execute(
                "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'already_scanned')",
                [$businessId, $workshopId, $qrToken, 'checkin', $qr['registration_id'], $qr['participant_id'], $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
            );
            $participantPayload['checked_in'] = $existing['checked_in_at'];
            echo json_encode([
                'success'     => true,
                'already_in'  => true,
                'message'     => 'បានកត់ត្រាវត្តមានរួចហើយកាលពីម៉ោង ' . date('H:i:s, d/m/Y', strtotime($existing['checked_in_at'])),
                'participant' => $participantPayload
            ]);
            exit;
        }

        // Save Attendance
        $helperNote = !empty($device['helper_name']) ? ('ជំនួយការ៖ ' . $device['helper_name']) : ('Helper Pass: ' . $pass['label']);

        if ($existing) {
            $db->execute(
                "UPDATE attendance SET checked_in_at = NOW(), check_in_method = 'helper_station', check_in_staff_id = ?, notes = ? WHERE registration_id = ?",
                [$pass['created_by'], $helperNote, $qr['registration_id']]
            );
        } else {
            $db->execute(
                "INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, notes, is_synced)
                 VALUES (?, ?, ?, ?, NOW(), 'helper_station', ?, ?, 1)",
                [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $pass['created_by'], $helperNote]
            );
        }

        $db->execute("UPDATE registrations SET status = 'attended' WHERE id = ? AND status = 'confirmed'", [$qr['registration_id']]);

        $db->execute(
            "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'success')",
            [$businessId, $workshopId, $qrToken, 'checkin', $qr['registration_id'], $qr['participant_id'], $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
        );

        $participantPayload['checked_in'] = date('Y-m-d H:i:s');

        echo json_encode([
            'success'     => true,
            'already_in'  => false,
            'message'     => 'កត់ត្រាវត្តមានជោគជ័យ!',
            'participant' => $participantPayload
        ]);
        exit;
    }

    public static function search(): void {
        header('Content-Type: application/json');
        $db = Database::getInstance();

        $passToken = trim($_POST['pass_token'] ?? '');
        $query     = trim($_POST['query'] ?? '');

        if (empty($passToken)) {
            echo json_encode(['success' => false, 'data' => []]);
            exit;
        }

        $pass = $db->queryOne("SELECT * FROM checkin_helper_passes WHERE token = ? AND is_active = 1", [$passToken]);
        if (!$pass || strtotime($pass['expires_at']) <= time()) {
            echo json_encode(['success' => false, 'message' => 'លីងជំនួយការផុតសុពលភាព', 'data' => []]);
            exit;
        }

        $workshopId = (int)$pass['workshop_id'];
        $businessId = (int)$pass['business_id'];

        $sql = "SELECT r.id as registration_id, r.registration_code, r.status, r.is_vip,
                       p.name, p.phone, p.email,
                       COALESCE(d.organization, p.company, '-') as company,
                       COALESCE(d.province, p.province, 'ទូទៅ') as province,
                       COALESCE(p.position, '-') as position,
                       t.name as ticket_name,
                       pq.token,
                       a.checked_in_at
                FROM registrations r
                JOIN participants p ON p.id = r.participant_id
                LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
                LEFT JOIN participant_qr pq ON pq.registration_id = r.id AND pq.is_active = 1
                LEFT JOIN tickets t ON t.id = r.ticket_id
                LEFT JOIN attendance a ON a.registration_id = r.id
                WHERE r.workshop_id = ? AND r.business_id = ? AND r.deleted_at IS NULL";

        $params = [$workshopId, $businessId];

        if ($query !== '') {
            $sql .= " AND (p.name LIKE ? OR p.phone LIKE ? OR p.email LIKE ? OR r.registration_code LIKE ?)";
            $searchParam = '%' . $query . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $sql .= " ORDER BY (a.checked_in_at IS NOT NULL) ASC, r.created_at DESC LIMIT 20";

        $rows = $db->query($sql, $params);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    private static function detectDeviceName(): string {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (preg_match('/iPhone/i', $ua)) return 'Apple iPhone';
        if (preg_match('/iPad/i', $ua)) return 'Apple iPad';
        if (preg_match('/Android/i', $ua)) {
            if (preg_match('/Samsung/i', $ua)) return 'Samsung Android';
            return 'Android Device';
        }
        if (preg_match('/Macintosh/i', $ua)) return 'Mac Computer';
        if (preg_match('/Windows/i', $ua)) return 'Windows PC';
        return 'Web Browser Device';
    }
}
