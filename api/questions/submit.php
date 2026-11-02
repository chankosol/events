<?php
// api/questions/submit.php

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

$workshopId   = (int)($_POST['workshop_id'] ?? 0);
$questionText = trim($_POST['question'] ?? '');
$isAnonymous  = !empty($_POST['is_anonymous']) ? 1 : 0;
$authorName   = trim($_POST['author_name'] ?? '');
$category     = trim($_POST['category'] ?? 'General');

if (empty($questionText) || !$workshopId) {
    echo json_encode(['success' => false, 'message' => 'សូមបញ្ជាក់សំណួរ និងសិក្ខាសាលា។']);
    exit;
}

$db = Database::getInstance();
$workshop = $db->queryOne("SELECT id, business_id, qa_status, qa_ends_at FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
if (!$workshop) {
    echo json_encode(['success' => false, 'message' => 'រកមិនឃើញសិក្ខាសាលានេះទេ។']);
    exit;
}

if (($workshop['qa_status'] ?? 'open') === 'closed' || (!empty($workshop['qa_ends_at']) && strtotime($workshop['qa_ends_at']) < time())) {
    echo json_encode([
        'success' => false,
        'message' => 'វេទិកាសួរសំណួរត្រូវបានបិទបញ្ចប់ដោយអ្នករៀបចំ។ មិនអាចផ្ញើសំណួរថ្មីបានទេ!'
    ]);
    exit;
}

$participantId = null;
$registrationId = null;
if (Auth::isParticipant()) {
    $p = Auth::participant();
    $participantId = $p['id'] ?? null;
    if (empty($authorName)) {
        $authorName = $p['name'] ?? '';
    }
    $reg = $db->queryOne("SELECT id FROM registrations WHERE workshop_id = ? AND participant_id = ?", [$workshopId, $participantId]);
    $registrationId = $reg['id'] ?? null;
}

if ($isAnonymous) {
    $authorName = 'មិនបញ្ចេញឈ្មោះ';
} elseif (empty($authorName)) {
    $authorName = 'អ្នកចូលរួម';
}

$db->execute(
    "INSERT INTO questions (business_id, workshop_id, registration_id, participant_id, author_name, question_text, category, is_anonymous, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
    [$workshop['business_id'], $workshopId, $registrationId, $participantId, $authorName, $questionText, $category, $isAnonymous]
);

$newId = (int)$db->lastInsertId();

echo json_encode([
    'success' => true,
    'message' => 'សំណួររបស់អ្នកត្រូវបានបញ្ជូនជោគជ័យ និងកំពុងរង់ចាំការត្រួតពិនិត្យពីអ្នករៀបចំ!',
    'question_id' => $newId,
    'status' => 'pending'
]);
