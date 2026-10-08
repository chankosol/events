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

if (!Auth::check() || !Permission::has('checkin.use')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.', 'data' => []]);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'CSRF mismatch.', 'data' => []]);
    exit;
}

$query      = trim($_POST['query'] ?? '');
$workshopId = (int)($_POST['workshop_id'] ?? 0);
$businessId = Tenant::id();

if (!$workshopId || !$businessId) {
    echo json_encode(['success' => false, 'data' => []]);
    exit;
}

$db = Database::getInstance();

$searchParam = '%' . $query . '%';

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
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$sql .= " ORDER BY a.checked_in_at DESC, r.created_at DESC LIMIT 15";

$results = $db->query($sql, $params);

echo json_encode([
    'success' => true,
    'data' => $results ?: []
]);
