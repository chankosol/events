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
               SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in,
               SUM(CASE WHEN r.is_vip = 1 THEN 1 ELSE 0 END) as vip_total,
               SUM(CASE WHEN r.is_vip = 1 AND a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as vip_checked_in
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.status IN ('confirmed', 'attended') AND r.deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        $totalConfirmed = (int)($stats['total_confirmed'] ?? 0);
        $totalCheckedIn = (int)($stats['checked_in'] ?? 0);
        $totalPending   = max(0, $totalConfirmed - $totalCheckedIn);
        $checkinRate    = $totalConfirmed > 0 ? round(($totalCheckedIn / $totalConfirmed) * 100, 1) : 0;
        $vipTotal       = (int)($stats['vip_total'] ?? 0);
        $vipCheckedIn   = (int)($stats['vip_checked_in'] ?? 0);

        $activePassesCount = (int)($db->queryOne(
            "SELECT COUNT(*) as c FROM checkin_helper_passes WHERE workshop_id = ? AND business_id = ? AND is_active = 1 AND expires_at > NOW()",
            [$workshopId, $businessId]
        )['c'] ?? 0);

        $activeDevicesCount = (int)($db->queryOne(
            "SELECT COUNT(*) as c FROM checkin_helper_devices d 
             JOIN checkin_helper_passes p ON p.id = d.pass_id 
             WHERE p.workshop_id = ? AND p.business_id = ? AND p.is_active = 1 AND d.is_active = 1 AND p.expires_at > NOW()",
            [$workshopId, $businessId]
        )['c'] ?? 0);

        $sessions = $db->query(
            "SELECT * FROM workshop_sessions WHERE workshop_id = ? AND status != 'cancelled' ORDER BY session_date, start_time",
            [$workshopId]
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

    public static function createHelperPass(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne("SELECT id, name FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) {
            echo json_encode(['success' => false, 'message' => 'Workshop not found.']);
            exit;
        }

        $label = trim($_POST['label'] ?? '');
        if (empty($label)) {
            $label = 'តុស្កេនជំនួយការ';
        }
        $maxDevices = max(1, min(50, (int)($_POST['max_devices'] ?? 1)));
        $hours = max(0.5, min(72, (float)($_POST['duration_hours'] ?? 4)));

        $token = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + (int)($hours * 3600));

        $db->execute(
            "INSERT INTO checkin_helper_passes (business_id, workshop_id, token, label, max_devices, device_count, expires_at, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, 0, ?, 1, ?)",
            [$businessId, $workshopId, $token, $label, $maxDevices, $expiresAt, Auth::id()]
        );
        $passId = (int)$db->lastInsertId();

        $url = APP_URL . '/scan/helper/' . $token;

        echo json_encode([
            'success' => true,
            'message' => 'បានបង្កើតលីងជំនួយការស្កេនដោយជោគជ័យ!',
            'pass' => [
                'id' => $passId,
                'token' => $token,
                'label' => $label,
                'max_devices' => $maxDevices,
                'device_count' => 0,
                'expires_at' => $expiresAt,
                'url' => $url
            ]
        ]);
        exit;
    }

    public static function listHelperPasses(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $businessId = Tenant::id();
        $db = Database::getInstance();

        $passes = $db->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM checkin_helper_devices d WHERE d.pass_id = p.id AND d.is_active = 1) as active_device_count,
                    (SELECT COUNT(*) FROM checkin_helper_devices d WHERE d.pass_id = p.id) as total_device_count
             FROM checkin_helper_passes p
             WHERE p.workshop_id = ? AND p.business_id = ?
             ORDER BY p.created_at DESC",
            [$workshopId, $businessId]
        );

        $now = time();
        $formatted = [];
        foreach ($passes as $p) {
            $isExpired = strtotime($p['expires_at']) <= $now;

            $devices = $db->query(
                "SELECT id, pass_id, device_token, helper_name, is_active, device_name, ip_address, last_active_at, created_at
                 FROM checkin_helper_devices
                 WHERE pass_id = ?
                 ORDER BY created_at ASC",
                [$p['id']]
            );

            $formattedDevices = [];
            foreach ($devices as $d) {
                $formattedDevices[] = [
                    'id'             => (int)$d['id'],
                    'helper_name'    => $d['helper_name'] ?: 'មិនទាន់បំពេញឈ្មោះ',
                    'device_name'    => $d['device_name'] ?: 'ឧបករណ៍ទូទៅ',
                    'is_active'      => (bool)$d['is_active'],
                    'ip_address'     => $d['ip_address'] ?? '',
                    'last_active_at' => $d['last_active_at'] ? date('H:i d/m', strtotime($d['last_active_at'])) : '-',
                    'created_at'     => date('H:i d/m', strtotime($d['created_at']))
                ];
            }

            $formatted[] = [
                'id'            => (int)$p['id'],
                'token'         => $p['token'],
                'label'         => $p['label'],
                'max_devices'   => (int)$p['max_devices'],
                'device_count'  => (int)$p['active_device_count'],
                'total_devices' => (int)$p['total_device_count'],
                'devices'       => $formattedDevices,
                'expires_at'    => $p['expires_at'],
                'is_active'     => (bool)$p['is_active'],
                'is_expired'    => $isExpired,
                'status_label'  => (!$p['is_active']) ? 'បានបិទ' : ($isExpired ? 'ផុតកំណត់' : 'សកម្ម'),
                'url'           => APP_URL . '/scan/helper/' . $p['token'],
                'created_at'    => $p['created_at']
            ];
        }

        echo json_encode(['success' => true, 'passes' => $formatted]);
        exit;
    }

    public static function revokeHelperPass(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        $businessId = Tenant::id();
        $passId = (int)($_POST['pass_id'] ?? 0);
        $db = Database::getInstance();

        $db->execute(
            "UPDATE checkin_helper_passes SET is_active = 0 WHERE id = ? AND workshop_id = ? AND business_id = ?",
            [$passId, $workshopId, $businessId]
        );

        echo json_encode(['success' => true, 'message' => 'បានបិទលីងជំនួយការនេះដោយជោគជ័យ។']);
        exit;
    }

    public static function reactivateHelperPass(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        $businessId = Tenant::id();
        $passId = (int)($_POST['pass_id'] ?? 0);
        $extendHours = (int)($_POST['extend_hours'] ?? 0);
        $db = Database::getInstance();

        $pass = $db->queryOne(
            "SELECT * FROM checkin_helper_passes WHERE id = ? AND workshop_id = ? AND business_id = ?",
            [$passId, $workshopId, $businessId]
        );

        if (!$pass) {
            echo json_encode(['success' => false, 'message' => 'រកមិនឃើញទិន្នន័យតុជំនួយការឡើយ។']);
            exit;
        }

        $now = time();
        $isExpired = strtotime($pass['expires_at']) <= $now;

        if ($isExpired || $extendHours > 0) {
            $hoursToAdd = $extendHours > 0 ? $extendHours : 4;
            $newExpiry = date('Y-m-d H:i:s', $now + ($hoursToAdd * 3600));
            $db->execute(
                "UPDATE checkin_helper_passes SET is_active = 1, expires_at = ? WHERE id = ? AND workshop_id = ? AND business_id = ?",
                [$newExpiry, $passId, $workshopId, $businessId]
            );
        } else {
            $db->execute(
                "UPDATE checkin_helper_passes SET is_active = 1 WHERE id = ? AND workshop_id = ? AND business_id = ?",
                [$passId, $workshopId, $businessId]
            );
        }

        echo json_encode(['success' => true, 'message' => 'បានបើកដំណើរការតុស្កេនជំនួយការនេះឡើងវិញដោយជោគជ័យ។']);
        exit;
    }

    public static function toggleHelperDevice(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        $businessId = Tenant::id();
        $deviceId = (int)($_POST['device_id'] ?? 0);
        $isActive = ((int)($_POST['is_active'] ?? 0)) ? 1 : 0;
        $db = Database::getInstance();

        $device = $db->queryOne(
            "SELECT d.*, p.workshop_id, p.label, p.id as pass_id
             FROM checkin_helper_devices d
             JOIN checkin_helper_passes p ON p.id = d.pass_id
             WHERE d.id = ? AND p.workshop_id = ? AND p.business_id = ?",
            [$deviceId, $workshopId, $businessId]
        );

        if (!$device) {
            echo json_encode(['success' => false, 'message' => 'រកមិនឃើញឧបករណ៍នេះឡើយ។']);
            exit;
        }

        $db->execute("UPDATE checkin_helper_devices SET is_active = ? WHERE id = ?", [$isActive, $deviceId]);
        $db->execute(
            "UPDATE checkin_helper_passes SET device_count = (SELECT COUNT(*) FROM checkin_helper_devices WHERE pass_id = ? AND is_active = 1) WHERE id = ?",
            [$device['pass_id'], $device['pass_id']]
        );

        $actionText = $isActive ? 'បើកដំណើរការឡើងវិញ' : 'បិទដំណើរការ';
        $helperName = $device['helper_name'] ?: 'ជំនួយការ';
        echo json_encode([
            'success'   => true,
            'message'   => "បាន{$actionText}សម្រាប់ «{$helperName}» ដោយជោគជ័យ។",
            'is_active' => (bool)$isActive
        ]);
        exit;
    }

    public static function removeHelperDevice(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
        if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
            echo json_encode(['success' => false, 'message' => 'CSRF mismatch.']);
            exit;
        }

        $businessId = Tenant::id();
        $deviceId = (int)($_POST['device_id'] ?? 0);
        $db = Database::getInstance();

        $device = $db->queryOne(
            "SELECT d.*, p.workshop_id, p.id as pass_id
             FROM checkin_helper_devices d
             JOIN checkin_helper_passes p ON p.id = d.pass_id
             WHERE d.id = ? AND p.workshop_id = ? AND p.business_id = ?",
            [$deviceId, $workshopId, $businessId]
        );

        if (!$device) {
            echo json_encode(['success' => false, 'message' => 'រកមិនឃើញឧបករណ៍នេះឡើយ។']);
            exit;
        }

        $helperName = $device['helper_name'] ?: 'ជំនួយការ';
        $db->execute("DELETE FROM checkin_helper_devices WHERE id = ?", [$deviceId]);
        $db->execute(
            "UPDATE checkin_helper_passes SET device_count = (SELECT COUNT(*) FROM checkin_helper_devices WHERE pass_id = ? AND is_active = 1) WHERE id = ?",
            [$device['pass_id'], $device['pass_id']]
        );

        echo json_encode([
            'success' => true,
            'message' => "បានលុបឧបករណ៍របស់អ្នកជួយ «{$helperName}» ចេញពីប្រព័ន្ធដោយជោគជ័យ។"
        ]);
        exit;
    }

    public static function liveMonitorData(int $workshopId): void {
        requireAuth();
        requirePermission('checkin.use');
        header('Content-Type: application/json');

        $businessId = Tenant::id();
        $db = Database::getInstance();

        // 1. Stats
        $stats = $db->queryOne(
            "SELECT
               COUNT(r.id) as total_confirmed,
               SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in,
               SUM(CASE WHEN r.is_vip = 1 THEN 1 ELSE 0 END) as vip_total,
               SUM(CASE WHEN r.is_vip = 1 AND a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as vip_checked_in
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.status IN ('confirmed', 'attended') AND r.deleted_at IS NULL",
            [$workshopId, $businessId]
        );

        $total = (int)($stats['total_confirmed'] ?? 0);
        $checkedIn = (int)($stats['checked_in'] ?? 0);
        $pending = max(0, $total - $checkedIn);
        $rate = $total > 0 ? round(($checkedIn / $total) * 100, 1) : 0;

        // Helper passes & devices count
        $activePassesCount = (int)($db->queryOne(
            "SELECT COUNT(*) as c FROM checkin_helper_passes WHERE workshop_id = ? AND business_id = ? AND is_active = 1 AND expires_at > NOW()",
            [$workshopId, $businessId]
        )['c'] ?? 0);

        $activeDevicesCount = (int)($db->queryOne(
            "SELECT COUNT(*) as c FROM checkin_helper_devices d 
             JOIN checkin_helper_passes p ON p.id = d.pass_id 
             WHERE p.workshop_id = ? AND p.business_id = ? AND p.is_active = 1 AND d.is_active = 1 AND p.expires_at > NOW()",
            [$workshopId, $businessId]
        )['c'] ?? 0);

        // 2. Recent Live Stream (Last 25)
        $stream = $db->query(
            "SELECT a.checked_in_at, a.check_in_method, a.notes,
                    p.id as participant_id, p.name, p.phone, p.photo,
                    COALESCE(d.organization, p.company, '-') as company,
                    COALESCE(d.province, p.province, 'ទូទៅ') as province,
                    COALESCE(p.position, '-') as position,
                    r.id as registration_id, r.registration_code, r.is_vip,
                    COALESCE(t.name, 'ទូទៅ') as ticket_name
             FROM attendance a
             JOIN registrations r ON r.id = a.registration_id
             JOIN participants p ON p.id = a.participant_id
             LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
             LEFT JOIN tickets t ON t.id = r.ticket_id
             WHERE a.workshop_id = ? AND a.business_id = ? AND a.checked_in_at IS NOT NULL
             ORDER BY a.checked_in_at DESC
             LIMIT 25",
            [$workshopId, $businessId]
        );

        $formattedStream = [];
        foreach ($stream as $s) {
            $sourceText = 'តុចម្បង';
            if ($s['check_in_method'] === 'helper_station') {
                $sourceText = !empty($s['notes']) ? $s['notes'] : 'តុជំនួយការ';
            } elseif ($s['check_in_method'] === 'manual') {
                $sourceText = 'ស្កេនដោយដៃ';
            }

            $photoUrl = '';
            if (!empty($s['photo'])) {
                $photoUrl = function_exists('getUploadUrl') ? getUploadUrl($s['photo']) : (APP_URL . '/' . ltrim($s['photo'], '/\\'));
            }

            $formattedStream[] = [
                'registration_id' => (int)$s['registration_id'],
                'name'            => $s['name'],
                'phone'           => $s['phone'],
                'photo_url'       => $photoUrl,
                'company'         => $s['company'],
                'position'        => $s['position'],
                'province'        => $s['province'],
                'ticket_name'     => $s['ticket_name'],
                'is_vip'          => (bool)$s['is_vip'],
                'time'            => date('H:i:s', strtotime($s['checked_in_at'])),
                'date_time'       => date('d/m H:i:s', strtotime($s['checked_in_at'])),
                'source'          => $sourceText,
                'method'          => $s['check_in_method']
            ];
        }

        // 3. Helper Stations summary
        $passes = $db->query(
            "SELECT p.id, p.label, p.max_devices, p.is_active, p.expires_at,
                    (SELECT COUNT(*) FROM checkin_helper_devices d WHERE d.pass_id = p.id AND d.is_active = 1) as active_devices
             FROM checkin_helper_passes p
             WHERE p.workshop_id = ? AND p.business_id = ? AND p.is_active = 1 AND p.expires_at > NOW()
             ORDER BY p.created_at DESC",
            [$workshopId, $businessId]
        );

        $stations = [];
        foreach ($passes as $p) {
            $devices = $db->query(
                "SELECT d.id, d.helper_name, d.device_name, d.is_active, d.last_active_at
                 FROM checkin_helper_devices d
                 WHERE d.pass_id = ?
                 ORDER BY d.created_at ASC",
                [$p['id']]
            );

            $deviceList = [];
            foreach ($devices as $d) {
                $helperName = $d['helper_name'] ?: 'ជំនួយការ';
                $scanCount = (int)($db->queryOne(
                    "SELECT COUNT(*) as c FROM attendance WHERE workshop_id = ? AND business_id = ? AND notes LIKE ?",
                    [$workshopId, $businessId, '%' . $helperName . '%']
                )['c'] ?? 0);

                $deviceList[] = [
                    'id'             => (int)$d['id'],
                    'helper_name'    => $helperName,
                    'device_name'    => $d['device_name'] ?: 'ឧបករណ៍',
                    'is_active'      => (bool)$d['is_active'],
                    'last_active'    => $d['last_active_at'] ? date('H:i', strtotime($d['last_active_at'])) : '-',
                    'scanned_count'  => $scanCount
                ];
            }

            $stations[] = [
                'id'             => (int)$p['id'],
                'label'          => $p['label'],
                'active_devices' => (int)$p['active_devices'],
                'max_devices'    => (int)$p['max_devices'],
                'devices'        => $deviceList
            ];
        }

        echo json_encode([
            'success'   => true,
            'stats'     => [
                'total'          => $total,
                'checked_in'     => $checkedIn,
                'pending'        => $pending,
                'rate'           => $rate,
                'vip_total'      => (int)($stats['vip_total'] ?? 0),
                'vip_checked_in' => (int)($stats['vip_checked_in'] ?? 0),
                'active_passes'  => $activePassesCount,
                'active_devices' => $activeDevicesCount
            ],
            'stream'    => $formattedStream,
            'stations'  => $stations
        ]);
        exit;
    }
}
