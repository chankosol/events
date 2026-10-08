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
    echo json_encode(['success' => false, 'message' => 'Unauthorized.', 'error_code' => 'UNAUTHORIZED']);
    exit;
}

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf_token'] ?? '');
if (!hash_equals(Session::get('_csrf_token', ''), $csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'CSRF mismatch.', 'error_code' => 'CSRF_MISMATCH']);
    exit;
}

$regId      = (int)($_POST['registration_id'] ?? 0);
$workshopId = (int)($_POST['workshop_id'] ?? 0);
$businessId = Tenant::id();

if (!$regId || !$workshopId || !$businessId) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.', 'error_code' => 'INVALID_DATA']);
    exit;
}

$db = Database::getInstance();

$workshop = $db->queryOne("SELECT id FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'Workshop not found.', 'error_code' => 'NOT_FOUND']);
    exit;
}

// Remove attendance
$db->execute("DELETE FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$regId, $workshopId]);

// Reset status back to confirmed
$db->execute("UPDATE registrations SET status = 'confirmed' WHERE id = ? AND workshop_id = ? AND status = 'attended'", [$regId, $workshopId]);

echo json_encode([
    'success' => true,
    'message' => 'បានកំណត់វត្តមានឡើងវិញដោយជោគជ័យ (Reset Check-in)',
    'registration_id' => $regId
]);
exit;
