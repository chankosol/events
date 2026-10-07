<?php
// C:\xampp\htdocs\workshopos\api\requests\submit.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';

Session::start();
header('Content-Type: application/json');

$workshopId  = (int)($_POST['workshop_id'] ?? 0);
$requestType = trim($_POST['request_type'] ?? 'General Help');
$description = trim($_POST['description'] ?? '');
$priority    = in_array($_POST['priority'] ?? '', ['normal','high','urgent']) ? $_POST['priority'] : 'normal';

if (empty($description) || !$workshopId) {
    echo json_encode(['success' => false, 'message' => 'Description and workshop are required.']);
    exit;
}

$db = Database::getInstance();
$workshop = $db->queryOne("SELECT id, business_id FROM workshops WHERE id = ?", [$workshopId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'Workshop not found.']);
    exit;
}

$participantId = null;
$registrationId = null;
if (Auth::isParticipant()) {
    $p = Auth::participant();
    $participantId = $p['id'] ?? null;
    $reg = $db->queryOne("SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ?", [$workshopId, $participantId]);
    $registrationId = $reg['id'] ?? null;
}

$db->execute(
    "INSERT INTO participant_requests (business_id, workshop_id, registration_id, participant_id, request_type, description, priority, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'new')",
    [$workshop['business_id'], $workshopId, $registrationId ?: 0, $participantId ?: 0, $requestType, $description, $priority]
);

echo json_encode(['success' => true, 'message' => 'Your request was dispatched to event staff!']);
