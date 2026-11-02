<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';
require_once ROOT_PATH . '/core/Permission.php';

Session::start();
if (Auth::check()) Tenant::resolve();

header('Content-Type: application/json; charset=utf-8');

try {
    // Auth check
    if (!Auth::check() || !Permission::has('checkin.use')) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.', 'error_code' => 'UNAUTHORIZED']);
        exit;
    }

    // Validate CSRF
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
    if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'CSRF mismatch.', 'error_code' => 'CSRF_MISMATCH']);
        exit;
    }

    $token      = trim($_POST['token'] ?? '');
    $workshopId = (int)($_POST['workshop_id'] ?? 0);
    $scanType   = $_POST['scan_type'] ?? 'checkin';
    $sessionId  = (int)($_POST['session_id'] ?? 0);
    $businessId = Tenant::id();

    if (empty($token) || !$workshopId || !$businessId) {
        echo json_encode(['success' => false, 'message' => 'Invalid scan data.', 'error_code' => 'INVALID_DATA']);
        exit;
    }

    $db = Database::getInstance();

    // Verify workshop belongs to business
    $workshop = $db->queryOne("SELECT id, name, daily_scans_required FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
    if (!$workshop) {
        echo json_encode(['success' => false, 'message' => 'Workshop not found.', 'error_code' => 'WORKSHOP_NOT_FOUND']);
        exit;
    }

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

    // Map scan_type safely for qr_scan_logs ENUM('checkin','checkout','attendance','gift','certificate','identity')
    $allowedLogScanTypes = ['checkin', 'checkout', 'attendance', 'gift', 'certificate', 'identity'];
    $logScanType = in_array($scanType, $allowedLogScanTypes) ? $scanType : 'checkin';

    $safeLog = function($result, $regId = null, $partId = null) use ($db, $businessId, $workshopId, $token, $logScanType) {
        try {
            if ($regId && $partId) {
                $db->execute(
                    "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,?)",
                    [$businessId, $workshopId, $token, $logScanType, $regId, $partId, Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '', $result]
                );
            } else {
                $db->execute(
                    "INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?)",
                    [$businessId, $workshopId, $token, $logScanType, Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '', $result]
                );
            }
        } catch (Throwable $e) {
            error_log('SafeLog qr_scan_logs non-fatal: ' . $e->getMessage());
        }
    };

    // Find registration by QR token or Registration code
    $qr = $db->queryOne(
        "SELECT COALESCE(pq.token, '') as token, r.id as registration_id, r.participant_id, r.status as reg_status, r.payment_status, r.is_vip, r.registration_code, r.id_card_number,
                p.name, p.email, p.phone, p.company, p.position, p.photo, p.gender, p.province as participant_province,
                d.province as delegation_province, d.organization as delegation_org, d.head_name as delegation_head,
                t.name as ticket_name, t.ticket_type
         FROM registrations r
         LEFT JOIN participant_qr pq ON pq.registration_id = r.id AND pq.is_active = 1
         JOIN participants p ON p.id = r.participant_id
         LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
         LEFT JOIN tickets t ON t.id = r.ticket_id
         WHERE (pq.token = ? OR r.registration_code = ?) AND r.workshop_id = ? AND r.deleted_at IS NULL
         LIMIT 1",
        [$token, $token, $workshopId]
    );

    if (!$qr) {
        $safeLog('invalid');
        echo json_encode(['success' => false, 'message' => 'រកមិនឃើញទិន្នន័យសំបុត្រ ឬកូដសម្គាល់នេះទេ។', 'error_code' => 'INVALID_QR']);
        exit;
    }

    // Check registration status
    if (!in_array($qr['reg_status'], ['confirmed', 'attended', 'completed'])) {
        $safeLog('unauthorized', $qr['registration_id'], $qr['participant_id']);
        echo json_encode([
            'success'        => false,
            'message'        => 'ស្ថានភាពចុះឈ្មោះមិនទាន់ត្រូវបានបញ្ជាក់ (Status: ' . $qr['reg_status'] . ')',
            'error_code'     => 'NOT_CONFIRMED',
            'participant'    => ['name' => $qr['name'], 'email' => $qr['email']],
            'reg_status'     => $qr['reg_status'],
            'payment_status' => $qr['payment_status'],
        ]);
        exit;
    }

    if ($scanType === 'checkin' || $scanType === 'face') {
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
            $safeLog('already_scanned', $qr['registration_id'], $qr['participant_id']);
            $photoUrl = '';
            if (!empty($qr['photo'])) {
                $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($qr['photo']) : APP_URL . '/' . ltrim($qr['photo'], '/\\');
            }

            $formattedTime = date('H:i:s, d/m/Y', strtotime($existingSlot['checked_in_at']));
            echo json_encode([
                'success'        => true,
                'already_in'     => true,
                'slot_number'    => $slotNumber,
                'slot_name'      => $slotName,
                'checked_in_at'  => $formattedTime,
                'message'        => "បានកត់ត្រាវត្តមានរួចនៅម៉ោង\n{$formattedTime}",
                'participant' => [
                    'reg_id'         => $qr['registration_id'],
                    'name'           => $qr['name'],
                    'email'          => $qr['email'],
                    'phone'          => $qr['phone'],
                    'gender'         => $qr['gender'],
                    'company'        => $qr['company'] ?: ($qr['delegation_org'] ?? ''),
                    'province'       => $qr['delegation_province'] ?: ($qr['participant_province'] ?? 'ទូទៅ'),
                    'position'       => $qr['position'] ?: '-',
                    'photo_url'      => $photoUrl,
                    'id_card'        => $qr['id_card_number'],
                    'ticket'         => $qr['ticket_name'] ?? 'ស្តង់ដារ',
                    'reg_code'       => $qr['registration_code'],
                    'is_vip'         => (bool)$qr['is_vip'],
                    'payment_status' => $qr['payment_status'],
                    'checked_in'     => $existingSlot['checked_in_at'],
                ],
            ]);
            exit;
        }

        $method = ($scanType === 'face') ? 'face' : 'qr';

        // 1. Record into attendance_slots
        $insertedSlotId = 0;
        try {
            $db->execute(
                "INSERT INTO attendance_slots 
                 (business_id, workshop_id, registration_id, participant_id, checkin_date, slot_number, slot_name, checked_in_at, check_in_method, check_in_staff_id, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)",
                [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $todayDate, $slotNumber, $slotName, $method, Auth::id(), '']
            );
            $insertedSlotId = (int)$db->lastInsertId();
        } catch (Throwable $se) {
            error_log('attendance_slots insert non-fatal: ' . $se->getMessage());
        }

        // 2. Maintain backward compatibility with main attendance table
        $existing = $db->queryOne("SELECT * FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);
        try {
            if ($existing) {
                if ($slotNumber >= 2) {
                    $db->execute("UPDATE attendance SET checked_out_at=NOW(), check_in_staff_id=? WHERE registration_id=?", [Auth::id(), $qr['registration_id']]);
                } else {
                    $db->execute("UPDATE attendance SET checked_in_at=COALESCE(checked_in_at, NOW()), check_in_method=?, check_in_staff_id=? WHERE registration_id=?", [$method, Auth::id(), $qr['registration_id']]);
                }
            } else {
                $db->execute("INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, is_synced) VALUES (?,?,?,?,NOW(),?,?,1)",
                    [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $method, Auth::id()]);
            }
        } catch (Throwable $ae) {
            error_log('Attendance check_in_method fallback to qr: ' . $ae->getMessage());
            $fallbackMethod = 'qr';
            if ($existing) {
                $db->execute("UPDATE attendance SET checked_in_at=COALESCE(checked_in_at, NOW()), check_in_method=?, check_in_staff_id=? WHERE registration_id=?", [$fallbackMethod, Auth::id(), $qr['registration_id']]);
            } else {
                $db->execute("INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, is_synced) VALUES (?,?,?,?,NOW(),?,?,1)",
                    [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], $fallbackMethod, Auth::id()]);
            }
        }

        // Update registration status
        $db->execute("UPDATE registrations SET status='attended' WHERE id=? AND status='confirmed'", [$qr['registration_id']]);

        $safeLog('success', $qr['registration_id'], $qr['participant_id']);

        // Auto-notify Telegram Group about Check-in with Slot Name
        try {
            if (file_exists(ROOT_PATH . '/helpers/telegram_helper.php')) {
                require_once ROOT_PATH . '/helpers/telegram_helper.php';
                if (function_exists('telegram_notify_checkin')) {
                    telegram_notify_checkin($workshopId, [
                        'name'              => $qr['name'],
                        'phone'             => $qr['phone'],
                        'company'           => $qr['company'] ?: ($qr['delegation_org'] ?? ''),
                        'position'          => $qr['position'] ?: '-',
                        'ticket_name'       => $qr['ticket_name'] ?? 'ស្តង់ដារ',
                        'registration_code' => $qr['registration_code'],
                        'is_vip'            => (bool)$qr['is_vip']
                    ], $method, $slotName);
                }
            }
        } catch (Throwable $tge) {
            error_log('Telegram checkin notification non-fatal error: ' . $tge->getMessage());
        }

        $photoUrl = '';
        if (!empty($qr['photo'])) {
            $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($qr['photo']) : APP_URL . '/' . ltrim($qr['photo'], '/\\');
        }

        $successMsg = ($scanType === 'face')
            ? "ស្គេនមុខបានជោគជ័យ! [{$slotName}]"
            : "កត់ត្រាវត្តមាន [{$slotName}] ជោគជ័យ!";

        $cleanSlot = preg_replace('/^ពេល/', 'វេន', $slotName);
        $checkinTime = date('H:i:s');

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
            'participant' => [
                'reg_id'         => $qr['registration_id'],
                'slot_id'        => $insertedSlotId,
                'slot_number'    => $slotNumber,
                'slot_name'      => $slotName,
                'slot_display'   => $cleanSlot,
                'name'           => $qr['name'],
                'email'          => $qr['email'],
                'phone'          => $qr['phone'],
                'gender'         => $qr['gender'],
                'company'        => $qr['company'] ?: ($qr['delegation_org'] ?? ''),
                'province'       => $qr['delegation_province'] ?: ($qr['participant_province'] ?? 'ទូទៅ'),
                'position'       => $qr['position'] ?: '-',
                'photo_url'      => $photoUrl,
                'id_card'        => $qr['id_card_number'],
                'ticket'         => $qr['ticket_name'] ?? 'ស្តង់ដារ',
                'reg_code'       => $qr['registration_code'],
                'is_vip'         => (bool)$qr['is_vip'],
                'payment_status' => $qr['payment_status'],
                'checked_in'     => date('Y-m-d H:i:s'),
            ],
        ]);
        exit;

    } elseif ($scanType === 'session' && $sessionId) {
        // Session attendance scan
        $session = $db->queryOne("SELECT * FROM workshop_sessions WHERE id = ? AND workshop_id = ?", [$sessionId, $workshopId]);
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Session not found.', 'error_code' => 'SESSION_NOT_FOUND']);
            exit;
        }

        $existing = $db->queryOne("SELECT * FROM session_attendance WHERE session_id = ? AND registration_id = ?", [$sessionId, $qr['registration_id']]);
        if ($existing) {
            echo json_encode(['success' => true, 'already_in' => true, 'message' => 'Already marked for this session.', 'participant' => ['name' => $qr['name']]]);
            exit;
        }

        $db->execute(
            "INSERT INTO session_attendance (business_id, workshop_id, session_id, registration_id, participant_id, status, checked_in_at, marked_by) VALUES (?,?,?,?,?,'present',NOW(),?)",
            [$businessId, $workshopId, $sessionId, $qr['registration_id'], $qr['participant_id'], Auth::id()]
        );

        echo json_encode(['success' => true, 'already_in' => false, 'message' => 'Session attendance recorded.', 'participant' => ['name' => $qr['name'], 'company' => $qr['company']]]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'ប្រភេទនៃការស្កេនមិនត្រឹមត្រូវ។', 'error_code' => 'INVALID_SCAN_TYPE']);
        exit;
    }

} catch (Throwable $e) {
    error_log('Check-in scan exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    echo json_encode([
        'success'    => false,
        'message'    => 'កំហុសប្រព័ន្ធម៉ាស៊ីនបម្រើ៖ ' . $e->getMessage(),
        'error_code' => 'SERVER_EXCEPTION'
    ]);
    exit;
}
