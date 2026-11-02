<?php
// api/feedback/submit.php

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
$overallRating = (float)($_POST['overall_rating'] ?? 5);
$speakerRating = (int)($_POST['speaker_rating'] ?? 5);
$contentRating = (int)($_POST['content_rating'] ?? 5);
$comments      = trim($_POST['comments'] ?? '');
$isAnonymous   = !empty($_POST['is_anonymous']) ? 1 : 0;
$name          = trim($_POST['participant_name'] ?? '');

if (!$workshopId || $overallRating < 1 || $overallRating > 5) {
    echo json_encode(['success' => false, 'message' => 'សូមផ្ដល់ពិន្ទុវាយតម្លៃឱ្យបានត្រឹមត្រូវ។']);
    exit;
}

$db = Database::getInstance();
$workshop = $db->queryOne("SELECT id, business_id FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'រកមិនឃើញសិក្ខាសាលានេះទេ។']);
    exit;
}

// Get or create form
$form = $db->queryOne("SELECT id FROM feedback_forms WHERE workshop_id = ?", [$workshopId]);
if (!$form) {
    $db->execute(
        "INSERT INTO feedback_forms (business_id, workshop_id, title, status) VALUES (?, ?, ?, 'active')",
        [$workshop['business_id'], $workshopId, 'Workshop Feedback']
    );
    $formId = (int)$db->lastInsertId();
} else {
    $formId = (int)$form['id'];
}

$registrationId = null;
if (Auth::isParticipant()) {
    $p = Auth::participant();
    $reg = $db->queryOne("SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ?", [$workshopId, $p['id'] ?? 0]);
    $registrationId = $reg['id'] ?? null;
    if (empty($name)) {
        $name = $p['name'] ?? '';
    }
}

$answersJson = json_encode([
    'speaker_rating'   => $speakerRating,
    'content_rating'   => $contentRating,
    'comments'         => $comments,
    'participant_name' => $isAnonymous ? 'មិនបញ្ចេញឈ្មោះ' : ($name ?: 'សិក្ខាកាម')
], JSON_UNESCAPED_UNICODE);

$db->execute(
    "INSERT INTO feedback_responses (form_id, registration_id, business_id, workshop_id, answers, overall_rating, is_anonymous)
     VALUES (?, ?, ?, ?, ?, ?, ?)",
    [$formId, $registrationId, $workshop['business_id'], $workshopId, $answersJson, $overallRating, $isAnonymous]
);

echo json_encode(['success' => true, 'message' => 'ការវាយតម្លៃរបស់អ្នកត្រូវបានរក្សាទុកដោយជោគជ័យ!']);
