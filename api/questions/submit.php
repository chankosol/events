<?php
// C:\xampp\htdocs\workshopos\api\questions\submit.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';

Session::start();
header('Content-Type: application/json');

$workshopId   = (int)($_POST['workshop_id'] ?? 0);
$questionText = trim($_POST['question'] ?? '');
$isAnonymous  = !empty($_POST['is_anonymous']) ? 1 : 0;
$category     = trim($_POST['category'] ?? 'General');

if (empty($questionText) || !$workshopId) {
    echo json_encode(['success' => false, 'message' => 'Question text and workshop are required.']);
    exit;
}

$db = Database::getInstance();
$workshop = $db->queryOne("SELECT id, business_id FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
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
    "INSERT INTO questions (business_id, workshop_id, registration_id, participant_id, question_text, category, is_anonymous, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')",
    [$workshop['business_id'], $workshopId, $registrationId, $participantId, $questionText, $category, $isAnonymous]
);

echo json_encode(['success' => true, 'message' => 'Your question was submitted and is in the moderator queue!']);
