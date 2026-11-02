<?php
// api/questions/upvote.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';

Session::start();
if (!headers_sent()) {
    header('Content-Type: application/json');
}

function api_upvote_question(int $id): void {
    $db = Database::getInstance();
    $q = $db->queryOne("SELECT id, upvotes FROM questions WHERE id = ?", [$id]);
    if (!$q) {
        echo json_encode(['success' => false, 'message' => 'រកមិនឃើញសំណួរទេ។']);
        exit;
    }

    $votedKey = 'voted_q_' . $id;
    if (!empty($_SESSION[$votedKey])) {
        echo json_encode(['success' => false, 'message' => 'អ្នកបានគាំទ្រសំណួរនេះរួចហើយ!']);
        exit;
    }

    $db->execute("UPDATE questions SET upvotes = upvotes + 1 WHERE id = ?", [$id]);
    $_SESSION[$votedKey] = true;

    $updated = $db->queryOne("SELECT upvotes FROM questions WHERE id = ?", [$id]);

    echo json_encode([
        'success' => true,
        'message' => 'បានគាំទ្រសំណួររួចរាល់!',
        'upvotes' => (int)($updated['upvotes'] ?? 0)
    ]);
}
