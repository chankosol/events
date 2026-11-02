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
$slotId     = (int)($_POST['slot_id'] ?? 0);
$slotNumber = (int)($_POST['slot_number'] ?? 0);
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

// 1. Delete slot from attendance_slots if slot_id or slot_number is specified
if ($slotId > 0) {
    try {
        $db->execute("DELETE FROM attendance_slots WHERE id = ? AND registration_id = ? AND workshop_id = ?", [$slotId, $regId, $workshopId]);
    } catch (Throwable $e) {}
} elseif ($slotNumber > 0) {
    try {
        $db->execute("DELETE FROM attendance_slots WHERE registration_id = ? AND workshop_id = ? AND slot_number = ?", [$regId, $workshopId, $slotNumber]);
    } catch (Throwable $e) {}
} else {
    // No specific slot requested: full reset of all slots
    try {
        $db->execute("DELETE FROM attendance_slots WHERE registration_id = ? AND workshop_id = ?", [$regId, $workshopId]);
    } catch (Throwable $e) {}
}

// 2. Check if participant still has any remaining slots in attendance_slots
$remaining = null;
try {
    $remaining = $db->queryOne(
        "SELECT COUNT(*) as cnt, MIN(checked_in_at) as first_in, MAX(checked_in_at) as last_in
         FROM attendance_slots
         WHERE registration_id = ? AND workshop_id = ?",
        [$regId, $workshopId]
    );
} catch (Throwable $e) {}

$totalRemaining = (int)($remaining['cnt'] ?? 0);

if ($totalRemaining > 0) {
    // Participant STILL has attendance records in other slots!
    try {
        if ($totalRemaining == 1) {
            $db->execute(
                "UPDATE attendance SET checked_in_at = ?, checked_out_at = NULL WHERE registration_id = ? AND workshop_id = ?",
                [$remaining['first_in'], $regId, $workshopId]
            );
        } else {
            $db->execute(
                "UPDATE attendance SET checked_in_at = ?, checked_out_at = ? WHERE registration_id = ? AND workshop_id = ?",
                [$remaining['first_in'], $remaining['last_in'], $regId, $workshopId]
            );
        }
    } catch (Throwable $e) {}

    $db->execute("UPDATE registrations SET status = 'attended' WHERE id = ? AND workshop_id = ?", [$regId, $workshopId]);

    echo json_encode([
        'success'         => true,
        'all_cleared'     => false,
        'remaining_slots' => $totalRemaining,
        'message'         => 'បានលុបវេនវត្តមាននេះរួចរាល់ (នៅសល់ ' . $totalRemaining . ' វេនទៀត)',
        'registration_id' => $regId
    ]);
    exit;
} else {
    // 0 slots remaining (or single check-in mode): full reset
    $db->execute("DELETE FROM attendance WHERE registration_id = ? AND workshop_id = ?", [$regId, $workshopId]);
    $db->execute("UPDATE registrations SET status = 'confirmed' WHERE id = ? AND workshop_id = ? AND status = 'attended'", [$regId, $workshopId]);

    echo json_encode([
        'success'         => true,
        'all_cleared'     => true,
        'remaining_slots' => 0,
        'message'         => 'បានកំណត់វត្តមានឡើងវិញដោយជោគជ័យ (Reset Check-in)',
        'registration_id' => $regId
    ]);
    exit;
}
