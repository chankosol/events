<?php
// C:\xampp\htdocs\workshopos\api\checkin\offline_sync.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';

Session::start();
if (Auth::check()) Tenant::resolve();

header('Content-Type: application/json');

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$db = Database::getInstance();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$scans = $input['scans'] ?? [];
$deviceId = $input['device_id'] ?? 'offline_device';
$synced = 0;

foreach ($scans as $scan) {
    $token = $scan['token'] ?? '';
    $timestamp = $scan['timestamp'] ?? date('Y-m-d H:i:s');
    
    $qr = $db->queryOne("SELECT * FROM participant_qr WHERE token = ?", [$token]);
    if ($qr) {
        $existing = $db->queryOne("SELECT id FROM attendance WHERE registration_id = ?", [$qr['registration_id']]);
        if (!$existing) {
            $db->execute(
                "INSERT INTO attendance (business_id, workshop_id, registration_id, participant_id, checked_in_at, check_in_method, is_synced)
                 VALUES (?, ?, ?, ?, ?, 'offline', 1)",
                [$qr['business_id'], $qr['workshop_id'], $qr['registration_id'], $qr['participant_id'], $timestamp]
            );
            $db->execute("UPDATE registrations SET status = 'attended' WHERE id = ?", [$qr['registration_id']]);
            $synced++;
        }
    }
}

echo json_encode(['success' => true, 'synced_count' => $synced, 'message' => "Successfully synchronized {$synced} check-in scans."]);
