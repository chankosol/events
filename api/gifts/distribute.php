<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';
require_once ROOT_PATH . '/core/Permission.php';

Session::start();
if (Auth::check()) Tenant::resolve();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method.']);
    exit;
}

if (!Auth::check() || !Permission::has('gift.distribute')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$businessId = Tenant::id();
$db = Database::getInstance();

$giftId = $_POST['gift_id'] ?? null;
$qrToken = $_POST['qr_token'] ?? null;

if (!$giftId || !$qrToken) {
    echo json_encode(['success' => false, 'message' => 'Missing data.']);
    exit;
}

$gift = $db->queryOne("SELECT * FROM gifts WHERE id = ? AND business_id = ?", [$giftId, $businessId]);
if (!$gift) {
    echo json_encode(['success' => false, 'message' => 'Gift not found.']);
    exit;
}

$reg = $db->queryOne("SELECT * FROM registrations WHERE qr_token = ? AND workshop_id = ? AND business_id = ? AND deleted_at IS NULL", [$qrToken, $gift['workshop_id'], $businessId]);
if (!$reg) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR token for this workshop.']);
    exit;
}

if (!in_array($reg['status'], ['confirmed', 'attended', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Participant must be confirmed or attended.']);
    exit;
}

// Check if already distributed
$existing = $db->queryOne("SELECT id FROM gift_distributions WHERE gift_id = ? AND registration_id = ?", [$giftId, $reg['id']]);
if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Gift already distributed to this participant.']);
    exit;
}

$db->execute("INSERT INTO gift_distributions (gift_id, workshop_id, business_id, registration_id, distributed_at, distributed_by) VALUES (?, ?, ?, ?, NOW(), ?)", [
    $giftId, $gift['workshop_id'], $businessId, $reg['id'], Auth::id()
]);

echo json_encode(['success' => true, 'message' => 'Gift distributed successfully!']);
