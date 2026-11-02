<?php
// api/face/descriptors.php
// Returns all active face descriptors for a specific workshop

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';

Session::start();
if (Auth::check()) {
    Tenant::resolve();
}

header('Content-Type: application/json; charset=utf-8');

$workshopId = (int)($_GET['workshop_id'] ?? ($_POST['workshop_id'] ?? 0));
$passToken = trim($_GET['pass_token'] ?? ($_POST['pass_token'] ?? ''));

$db = Database::getInstance();

if (!$workshopId && !empty($passToken)) {
    $pass = $db->queryOne("SELECT workshop_id FROM checkin_helper_passes WHERE token = ? AND is_active = 1 AND expires_at > NOW()", [$passToken]);
    if ($pass) {
        $workshopId = (int)$pass['workshop_id'];
    }
}

if (!$workshopId) {
    echo json_encode(['success' => false, 'message' => 'Workshop ID required.', 'attendees' => []]);
    exit;
}

$rows = $db->query(
    "SELECT pf.id as face_id, pf.face_descriptor, pf.photo_path as face_photo, pf.enrolled_at,
            r.id as registration_id, r.registration_code, r.status as reg_status, r.is_vip,
            p.id as participant_id, p.name, p.phone, p.email, p.company, p.position, p.photo as participant_photo,
            COALESCE(d.organization, p.company, '-') as org_name,
            COALESCE(d.province, p.province, 'ទូទៅ') as province,
            t.name as ticket_name,
            a.checked_in_at
     FROM participant_faces pf
     JOIN registrations r ON r.id = pf.registration_id
     JOIN participants p ON p.id = r.participant_id
     LEFT JOIN workshop_delegations d ON d.id = r.delegation_id
     LEFT JOIN tickets t ON t.id = r.ticket_id
     LEFT JOIN attendance a ON a.registration_id = r.id AND a.workshop_id = r.workshop_id
     WHERE r.workshop_id = ? AND pf.is_active = 1 AND r.deleted_at IS NULL AND r.status IN ('confirmed', 'attended', 'completed')
     ORDER BY r.id ASC",
    [$workshopId]
);

$attendees = [];
foreach ($rows as $row) {
    $desc = json_decode($row['face_descriptor'], true);
    if (!is_array($desc) || count($desc) !== 128) {
        continue;
    }

    $photoUrl = '';
    if (!empty($row['face_photo'])) {
        $photoUrl = APP_URL . '/' . ltrim($row['face_photo'], '/\\');
    } elseif (!empty($row['participant_photo'])) {
        $photoUrl = (function_exists('getUploadUrl')) ? getUploadUrl($row['participant_photo']) : APP_URL . '/' . ltrim($row['participant_photo'], '/\\');
    } else {
        $photoUrl = 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($row['name']);
    }

    $attendees[] = [
        'face_id'           => (int)$row['face_id'],
        'registration_id'   => (int)$row['registration_id'],
        'registration_code' => $row['registration_code'],
        'name'              => $row['name'],
        'phone'             => $row['phone'],
        'company'           => $row['company'] ?: $row['org_name'],
        'position'          => $row['position'] ?: '-',
        'ticket_name'       => $row['ticket_name'] ?: 'ទូទៅ',
        'is_vip'            => (bool)$row['is_vip'],
        'checked_in'        => !empty($row['checked_in_at']),
        'checked_in_at'     => $row['checked_in_at'] ? date('H:i:s, d/m/Y', strtotime($row['checked_in_at'])) : null,
        'photo_url'         => $photoUrl,
        'descriptor'        => $desc // 128 floats array
    ];
}

echo json_encode([
    'success'     => true,
    'workshop_id' => $workshopId,
    'total'       => count($attendees),
    'attendees'   => $attendees
]);
