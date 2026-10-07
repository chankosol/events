<?php
// C:\xampp\htdocs\workshopos\api\polls\respond.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';

Session::start();
header('Content-Type: application/json');

function api_poll_respond(int $pollId): void {
    $db = Database::getInstance();
    $poll = $db->queryOne("SELECT * FROM polls WHERE id = ? AND status = 'active'", [$pollId]);
    if (!$poll) {
        echo json_encode(['success' => false, 'message' => 'Poll not active.']);
        exit;
    }

    $optionId = (int)($_POST['option_id'] ?? 0);
    $rating   = (int)($_POST['rating'] ?? 0);

    $db->execute("UPDATE poll_options SET response_count = response_count + 1 WHERE id = ? AND poll_id = ?", [$optionId, $pollId]);

    echo json_encode(['success' => true, 'message' => 'Vote recorded!']);
}
