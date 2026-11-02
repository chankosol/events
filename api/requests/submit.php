<?php
// api/requests/submit.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';

Session::start();
if (!headers_sent()) {
    header('Content-Type: application/json');
}

$workshopId    = (int)($_POST['workshop_id'] ?? 0);
$requestType   = trim($_POST['request_type'] ?? 'ជំនួយទូទៅ');
$description   = trim($_POST['description'] ?? '');
$priority      = in_array($_POST['priority'] ?? '', ['normal','high','urgent']) ? $_POST['priority'] : 'normal';
$requesterName = trim($_POST['requester_name'] ?? '');
$seatNumber    = trim($_POST['seat_number'] ?? '');

if (empty($description) || !$workshopId) {
    echo json_encode(['success' => false, 'message' => 'សូមបញ្ជាក់ការពិពណ៌នាសំណើ និងសិក្ខាសាលា។']);
    exit;
}

$db = Database::getInstance();
$workshop = $db->queryOne("SELECT id, business_id FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'រកមិនឃើញសិក្ខាសាលានេះទេ។']);
    exit;
}

$participantId = null;
$registrationId = null;
if (Auth::isParticipant()) {
    $p = Auth::participant();
    $participantId = $p['id'] ?? null;
    if (empty($requesterName)) {
        $requesterName = $p['name'] ?? '';
    }
    $reg = $db->queryOne("SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ?", [$workshopId, $participantId]);
    $registrationId = $reg['id'] ?? null;
}

if (empty($requesterName)) {
    $requesterName = 'សិក្ខាកាម';
}

$db->execute(
    "INSERT INTO participant_requests (business_id, workshop_id, registration_id, participant_id, requester_name, seat_number, request_type, description, priority, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')",
    [$workshop['business_id'], $workshopId, $registrationId, $participantId, $requesterName, $seatNumber, $requestType, $description, $priority]
);

echo json_encode(['success' => true, 'message' => 'សំណើជំនួយរបស់អ្នកត្រូវបានបញ្ជូនទៅកាន់ក្រុមការងាររួចរាល់!']);
