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

        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ?", [$pass['workshop_id']]);

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
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = Database::getInstance();

            $passToken = trim($_POST['pass_token'] ?? '');
            $qrToken   = trim($_POST['qr_token'] ?? '');
            $devToken  = trim($_POST['device_token'] ?? '');
            $scanType  = trim($_POST['scan_type'] ?? 'qr');

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

            $workshop = $db->queryOne("SELECT id, name, daily_scans_required FROM workshops WHERE id = ?", [$workshopId]);
            $dailyScansRequired = (int)($workshop['daily_scans_required'] ?? 1);
            $todayDate = date('Y-m-d');
            $currentTime = date('H:i');

            // Determine current slot number
            if (isset($_POST['slot_number']) && (int)$_POST['slot_number'] >= 1) {
                $slotNumber = min(4, max(1, (int)$_POST['slot_number']));
            } else {
                // Auto-detect slot based on current time
                if ($dailyScansRequired <= 1) {
                    $slotNumber = 1;
                } elseif ($dailyScansRequired == 2) {
                    $slotNumber = ($currentTime < '12:30') ? 1 : 2;
                } elseif ($dailyScansRequired == 3) {
                    $slotNumber = ($currentTime < '12:00') ? 1 : (($currentTime < '15:30') ? 2 : 3);
                } else {
                    $slotNumber = ($currentTime < '10:30') ? 1 : (($currentTime < '13:00') ? 2 : (($currentTime < '15:30') ? 3 : 4));
                }
            }

            // Determine human-readable slot name in Khmer
            if ($dailyScansRequired <= 1) {
                $slotName = 'ទូទៅ';
            } elseif ($dailyScansRequired == 2) {
                $slotName = ($slotNumber == 1) ? 'ពេលព្រឹក' : 'ពេលរសៀល';
            } elseif ($dailyScansRequired == 3) {
                $slotName = ($slotNumber == 1) ? 'ពេលព្រឹក' : (($slotNumber == 2) ? 'ពេលរសៀល' : 'ពេលល្ងាច');
            } else {
                $slotNames = [
                    1 => 'ព្រឹកចូល',
                    2 => 'ព្រឹកចេញ',
                    3 => 'រសៀលចូល',
                    4 => 'រសៀលចេញ'
                ];
                $slotName = $slotNames[$slotNumber] ?? "វេនទី {$slotNumber}";
            }

            // Find participant by QR token or Registration Code
            $qr = $db->queryOne(
                "SELECT COALESCE(pq.token, '') as token, r.id as registration_id, r.participant_id, r.status as reg_status, r.payment_status, r.is_vip, r.registration_code, r.id_card_number,
                        p.name, p.email, p.phone, p.company, p.position, p.photo, p.gender, p.province as participant_province,
                        d.province as delegation_province, d.organization as delegation_org,
                        t.name as ticket_name, t.ticket_type
                 FROM registrations r
                 JOIN participants p ON p.id = r.participant_id
                 LEFT JOIN participant_qr pq ON pq.registration_id = r.id AND pq.is_active = 1
                 LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
                 LEFT JOIN tickets t ON t.id = r.ticket_id
                 WHERE (pq.token = ? OR r.registration_code = ?) AND r.workshop_id = ? AND r.deleted_at IS NULL
                 LIMIT 1",
                [$qrToken, $qrToken, $workshopId]
            );

            if (!$qr) {
                try {
                    $db->execute(
                        "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,'invalid')",
                        [$businessId, $workshopId, $qrToken, 'checkin', $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
                    );
                } catch (Throwable $e) {}
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
                'payment_status' => $qr['payment_status'],
                'slot_number'  => $slotNumber,
                'slot_name'    => $slotName
            ];

            // If main attendance was deleted (e.g. by Reset Attendance) or status is confirmed, purge any orphaned slots
            $mainAtt = $db->queryOne("SELECT id FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);
            if (!$mainAtt && $qr['reg_status'] === 'confirmed') {
                try {
                    $db->execute("DELETE FROM attendance_slots WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);
                } catch (Throwable $pe) {}
            }

            // Check if already checked in FOR THIS SPECIFIC SLOT TODAY
            $existingSlot = null;
            try {
                $existingSlot = $db->queryOne(
                    "SELECT * FROM attendance_slots WHERE registration_id = ? AND checkin_date = ? AND slot_number = ?",
                    [$qr['registration_id'], $todayDate, $slotNumber]
                );
            } catch (Throwable $se) {
                // Auto-heal table if missing on live server
                try {
                    $db->execute("CREATE TABLE IF NOT EXISTS attendance_slots (
                      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                      business_id INT UNSIGNED NOT NULL,
                      workshop_id INT UNSIGNED NOT NULL,
                      registration_id INT UNSIGNED NOT NULL,
                      participant_id INT UNSIGNED NOT NULL,
                      checkin_date DATE NOT NULL,
                      slot_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
                      slot_name VARCHAR(50) NULL,
                      checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      check_in_method ENUM('qr','manual','offline','face','helper_station') DEFAULT 'qr',
                      check_in_staff_id INT UNSIGNED NULL,
                      notes VARCHAR(255) NULL,
                      UNIQUE KEY unique_reg_date_slot (registration_id, checkin_date, slot_number),
                      INDEX idx_workshop_date_slot (workshop_id, checkin_date, slot_number)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                } catch (Throwable $te) {}
            }

            if ($existingSlot && $existingSlot['checked_in_at']) {
                try {
                    $db->execute(
                        "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'already_scanned')",
                        [$businessId, $workshopId, $qrToken, 'checkin', $qr['registration_id'], $qr['participant_id'], $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
                    );
                } catch (Throwable $e) {}
                $formattedTime = date('H:i:s, d/m/Y', strtotime($existingSlot['checked_in_at']));
                $participantPayload['checked_in'] = $existingSlot['checked_in_at'];
                echo json_encode([
                    'success'        => true,
                    'already_in'     => true,
                    'slot_number'    => $slotNumber,
                    'slot_name'      => $slotName,
                    'checked_in_at'  => $formattedTime,
                    'message'        => "បានកត់ត្រាវត្តមានរួចនៅម៉ោង\n{$formattedTime}",
                    'participant'    => $participantPayload
                ]);
                exit;
            }

            // Save Attendance
            $helperNote = !empty($device['helper_name']) ? ('ជំនួយការ៖ ' . $device['helper_name']) : ('Helper Pass: ' . $pass['label']);
            $method = ($scanType === 'face') ? 'face' : 'helper_station';

            // 1. Record into attendance_slots
            $insertedSlotId = 0;
            try {
                $db->execute(
                    "INSERT INTO attendance_slots 
                     (business_id, workshop_id, registration_id, participant_id, checkin_date, slot_number, slot_name, checked_in_at, check_in_method, check_in_staff_id, notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
                    [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $todayDate, $slotNumber, $slotName, $method, $pass['created_by'], $helperNote]
                );
                $insertedSlotId = (int)$db->lastInsertId();
            } catch (Throwable $se) {
                error_log('attendance_slots helper insert non-fatal: ' . $se->getMessage());
            }

            // 2. Maintain backward compatibility with main attendance table
            $existing = $db->queryOne("SELECT * FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);
            try {
                if ($existing) {
                    if ($slotNumber >= 2) {
                        $db->execute(
                            "UPDATE attendance SET checked_out_at = NOW(), check_in_staff_id = ?, notes = ? WHERE registration_id = ?",
                            [$pass['created_by'], $helperNote, $qr['registration_id']]
                        );
                    } else {
                        $db->execute(
                            "UPDATE attendance SET checked_in_at = COALESCE(checked_in_at, NOW()), check_in_method = ?, check_in_staff_id = ?, notes = ? WHERE registration_id = ?",
                            [$method, $pass['created_by'], $helperNote, $qr['registration_id']]
                        );
                    }
                } else {
                    $db->execute(
                        "INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, notes, is_synced)
                         VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 1)",
                        [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $method, $pass['created_by'], $helperNote]
                    );
                }
            } catch (Throwable $ae) {
                $fallbackMethod = 'qr';
                if ($existing) {
                    $db->execute(
                        "UPDATE attendance SET checked_in_at = COALESCE(checked_in_at, NOW()), check_in_method = ?, check_in_staff_id = ?, notes = ? WHERE registration_id = ?",
                        [$fallbackMethod, $pass['created_by'], $helperNote, $qr['registration_id']]
                    );
                } else {
                    $db->execute(
                        "INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, notes, is_synced)
                         VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 1)",
                        [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $fallbackMethod, $pass['created_by'], $helperNote]
                    );
                }
            }

            $db->execute("UPDATE registrations SET status = 'attended' WHERE id = ? AND status = 'confirmed'", [$qr['registration_id']]);

            try {
                $db->execute(
                    "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'success')",
                    [$businessId, $workshopId, $qrToken, 'checkin', $qr['registration_id'], $qr['participant_id'], $pass['created_by'], $_SERVER['REMOTE_ADDR'] ?? '']
                );
            } catch (Throwable $e) {}

            $participantPayload['checked_in'] = date('Y-m-d H:i:s');

            // Auto-notify Telegram Group about Check-in with Slot Name
            try {
                if (file_exists(ROOT_PATH . '/helpers/telegram_helper.php')) {
                    require_once ROOT_PATH . '/helpers/telegram_helper.php';
                    if (function_exists('telegram_notify_checkin')) {
                        telegram_notify_checkin($workshopId, $participantPayload, $method, $slotName);
                    }
                }
            } catch (Throwable $tge) {
                error_log('Telegram helper checkin notification error: ' . $tge->getMessage());
            }

            $successMsg = ($scanType === 'face')
                ? "ស្គេនមុខបានជោគជ័យ! [{$slotName}]"
                : "កត់ត្រាវត្តមាន [{$slotName}] ជោគជ័យ!";

            $cleanSlot = preg_replace('/^ពេល/', 'វេន', $slotName);
            $checkinTime = date('H:i:s');
            $participantPayload['checked_in'] = date('Y-m-d H:i:s');
            $participantPayload['slot_id'] = $insertedSlotId;
            $participantPayload['slot_display'] = $cleanSlot;

            echo json_encode([
                'success'      => true,
                'already_in'   => false,
                'slot_id'      => $insertedSlotId,
                'slot_number'  => $slotNumber,
                'slot_name'    => $slotName,
                'slot_display' => $cleanSlot,
                'checkin_time' => $checkinTime,
                'checked_in_at'=> date('H:i:s, d/m/Y'),
                'scan_type'    => $scanType,
                'method'       => $method,
                'message'      => $successMsg,
                'participant'  => $participantPayload
            ]);
            exit;


        } catch (Throwable $e) {
            error_log('Helper scan exception: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'កំហុសប្រព័ន្ធម៉ាស៊ីនបម្រើ៖ ' . $e->getMessage()
            ]);
            exit;
        }
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
