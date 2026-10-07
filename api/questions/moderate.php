<?php
// C:\xampp\htdocs\workshopos\api\questions\moderate.php

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

function api_moderate_question(int $id): void {
    if (!Auth::check() || !Permission::has('question.moderate')) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $businessId = Tenant::id();
    $db = Database::getInstance();

    $q = $db->queryOne("SELECT * FROM questions WHERE id = ? AND business_id = ?", [$id, $businessId]);
    if (!$q) {
        echo json_encode(['success' => false, 'message' => 'Question not found.']);
        exit;
    }

    switch ($action) {
        case 'approve':
            $db->execute("UPDATE questions SET status = 'approved', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            break;
        case 'pin':
            $db->execute("UPDATE questions SET is_pinned = 1, status = 'pinned', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            break;
        case 'answer':
            $db->execute("UPDATE questions SET status = 'answered', answer_text = ?, answered_by = ?, answered_at = NOW() WHERE id = ?", 
                [trim($_POST['answer'] ?? ''), Auth::id(), $id]);
            break;
        case 'reject':
            $db->execute("UPDATE questions SET status = 'rejected', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            exit;
    }

    echo json_encode(['success' => true, 'message' => 'Question updated successfully.']);
}
