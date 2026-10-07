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

header('Content-Type: application/json');

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
$workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'Workshop not found.', 'error_code' => 'WORKSHOP_NOT_FOUND']);
    exit;
}

// Find QR token
$qr = $db->queryOne(
    "SELECT pq.*, r.id as registration_id, r.status as reg_status, r.payment_status, r.is_vip, r.registration_code, r.id_card_number,
            p.name, p.email, p.phone, p.company, p.photo, p.gender, p.province as participant_province,
            d.province as delegation_province, d.organization as delegation_org, d.head_name as delegation_head,
            t.name as ticket_name, t.ticket_type
     FROM participant_qr pq
     JOIN registrations r ON r.id = pq.registration_id
     JOIN participants p ON p.id = pq.participant_id
     LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
     LEFT JOIN tickets t ON t.id = r.ticket_id
     WHERE (pq.token = ? OR r.registration_code = ?) AND pq.workshop_id = ? AND pq.is_active = 1",
    [$token, $token, $workshopId]
);

// Log this scan attempt
$scanResult = 'invalid';
if ($qr) {
    $scanResult = 'success'; // will be updated below
}

if (!$qr) {
    $db->execute("INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,'invalid')",
        [$businessId, $workshopId, $token, $scanType, Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '']);
    echo json_encode(['success' => false, 'message' => 'Invalid or unrecognized QR code.', 'error_code' => 'INVALID_QR']);
    exit;
}

// Check registration status
if (!in_array($qr['reg_status'], ['confirmed', 'attended', 'completed'])) {
    $db->execute("INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'unauthorized')",
        [$businessId, $workshopId, $token, $scanType, $qr['registration_id'], $qr['participant_id'], Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '']);
    echo json_encode([
        'success'      => false,
        'message'      => 'Registration not confirmed. Status: ' . $qr['reg_status'],
        'error_code'   => 'NOT_CONFIRMED',
        'participant'  => ['name' => $qr['name'], 'email' => $qr['email']],
        'reg_status'   => $qr['reg_status'],
        'payment_status' => $qr['payment_status'],
    ]);
    exit;
}

if ($scanType === 'checkin') {
    // Check if already checked in
    $existing = $db->queryOne("SELECT * FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$qr['registration_id'], $workshopId]);

    if ($existing && $existing['checked_in_at']) {
        $db->execute("INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'already_scanned')",
            [$businessId, $workshopId, $token, $scanType, $qr['registration_id'], $qr['participant_id'], Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '']);
        $photoUrl = '';
        if (!empty($qr['photo'])) {
            $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($qr['photo']) : APP_URL . '/' . ltrim($qr['photo'], '/\\');
        }

        echo json_encode([
            'success'    => true,
            'already_in' => true,
            'message'    => 'បានកត់ត្រាវត្តមានរួចហើយកាលពីម៉ោង ' . date('H:i:s, d/m/Y', strtotime($existing['checked_in_at'])),
            'participant'=> [
                'reg_id'       => $qr['registration_id'],
                'name'         => $qr['name'],
                'email'        => $qr['email'],
                'phone'        => $qr['phone'],
                'gender'       => $qr['gender'],
                'company'      => $qr['company'] ?: ($qr['delegation_org'] ?? ''),
                'province'     => $qr['delegation_province'] ?: ($qr['participant_province'] ?? 'ទូទៅ'),
                'photo_url'    => $photoUrl,
                'id_card'      => $qr['id_card_number'],
                'ticket'       => $qr['ticket_name'] ?? 'ស្តង់ដារ',
                'reg_code'     => $qr['registration_code'],
                'is_vip'       => (bool)$qr['is_vip'],
                'checked_in'   => $existing['checked_in_at'],
            ],
        ]);
        exit;
    }

    // Perform check-in
    if ($existing) {
        $db->execute("UPDATE attendance SET checked_in_at=NOW(), check_in_method='qr', check_in_staff_id=? WHERE registration_id=?",
            [Auth::id(), $qr['registration_id']]);
    } else {
        $db->execute("INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, check_in_staff_id, is_synced) VALUES (?,?,?,?,NOW(),'qr',?,1)",
            [$businessId, $workshopId, $qr['registration_id'], $qr['participant_id'], Auth::id()]);
    }

    // Update registration status
    $db->execute("UPDATE registrations SET status='attended' WHERE id=? AND status='confirmed'", [$qr['registration_id']]);

    $db->execute("INSERT INTO qr_scan_logs (business_id, workshop_id, token, scan_type, registration_id, participant_id, scanned_by, ip_address, result) VALUES (?,?,?,?,?,?,?,?,'success')",
        [$businessId, $workshopId, $token, $scanType, $qr['registration_id'], $qr['participant_id'], Auth::id(), $_SERVER['REMOTE_ADDR'] ?? '']);

    $photoUrl = '';
    if (!empty($qr['photo'])) {
        $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($qr['photo']) : APP_URL . '/' . ltrim($qr['photo'], '/\\');
    }

    echo json_encode([
        'success'    => true,
        'already_in' => false,
        'message'    => 'កត់ត្រាវត្តមានជោគជ័យ! (Checked In)',
        'participant'=> [
            'reg_id'       => $qr['registration_id'],
            'name'         => $qr['name'],
            'email'        => $qr['email'],
            'phone'        => $qr['phone'],
            'gender'       => $qr['gender'],
            'company'      => $qr['company'] ?: ($qr['delegation_org'] ?? ''),
            'province'     => $qr['delegation_province'] ?: ($qr['participant_province'] ?? 'ទូទៅ'),
            'photo_url'    => $photoUrl,
            'id_card'      => $qr['id_card_number'],
            'ticket'       => $qr['ticket_name'] ?? 'ស្តង់ដារ',
            'reg_code'     => $qr['registration_code'],
            'is_vip'       => (bool)$qr['is_vip'],
            'payment_status' => $qr['payment_status'],
        ],
    ]);

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
}
