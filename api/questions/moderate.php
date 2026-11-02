<?php
// api/questions/moderate.php

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

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function api_moderate_question(int $id): void {
    if (!Auth::check() || (!Permission::has('question.moderate') && !Permission::has('workshop.view') && !Auth::isBusinessUser() && !Auth::isPlatformAdmin())) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $businessId = Tenant::id();
    $db = Database::getInstance();

    if (Auth::isPlatformAdmin()) {
        $q = $db->queryOne("SELECT * FROM questions WHERE id = ?", [$id]);
    } else {
        $q = $db->queryOne("SELECT * FROM questions WHERE id = ? AND business_id = ?", [$id, $businessId]);
    }

    if (!$q) {
        echo json_encode(['success' => false, 'message' => 'Question not found.']);
        exit;
    }

    switch ($action) {
        case 'pin':
            $db->execute("UPDATE questions SET is_pinned = 1, moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            $msg = 'បានរុញ/ខ្ទាស់សំណួរនេះទៅលើបង្អស់!';
            break;
        case 'unpin':
            $db->execute("UPDATE questions SET is_pinned = 0, moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            $msg = 'បានដោះខ្ទាស់សំណួរនេះ!';
            break;
        case 'answer':
        case 'done':
            $db->execute("UPDATE questions SET status = 'answered', answered_by = ?, answered_at = NOW() WHERE id = ?", 
                [Auth::id(), $id]);
            $msg = 'បានកំណត់ថាសំណួរនេះឆ្លើយរួចរាល់ (Marked Done)!';
            break;
        case 'restore':
        case 'reopen':
            $db->execute("UPDATE questions SET status = 'approved', answered_at = NULL, answered_by = NULL WHERE id = ?", [$id]);
            $msg = 'បានទាញសំណួរនេះត្រឡប់មកក្នុងបញ្ជីរង់ចាំវិញ!';
            break;
        case 'approve':
            $db->execute("UPDATE questions SET status = 'approved', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            $msg = 'បានអនុម័តសំណួរ និងបង្ហាញលើផ្ទាំងធំ!';
            break;
        case 'hide':
        case 'unapprove':
            $db->execute("UPDATE questions SET status = 'pending', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            $msg = 'បានដកសំណួរនេះចេញពីផ្ទាំងសាធារណៈ (លាក់ទុក)!';
            break;
        case 'reject':
            $db->execute("UPDATE questions SET status = 'rejected', moderated_by = ? WHERE id = ?", [Auth::id(), $id]);
            $msg = 'បានបដិសេធសំណួរ!';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            exit;
    }

    echo json_encode(['success' => true, 'message' => $msg]);
}

function api_bulk_moderate_questions(): void {
    if (!Auth::check() || (!Permission::has('question.moderate') && !Permission::has('workshop.view') && !Auth::isBusinessUser() && !Auth::isPlatformAdmin())) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $rawIds = $_POST['ids'] ?? [];
    if (is_string($rawIds)) {
        $rawIds = json_decode($rawIds, true) ?: explode(',', $rawIds);
    }
    $ids = array_filter(array_map('intval', (array)$rawIds));
    $action = $_POST['action'] ?? '';
    $businessId = Tenant::id();
    $db = Database::getInstance();

    if (empty($ids) || empty($action)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $where = "id IN ($placeholders)";
    $params = $ids;
    if (!Auth::isPlatformAdmin()) {
        $where .= " AND business_id = ?";
        $params[] = $businessId;
    }

    switch ($action) {
        case 'approve':
            $db->execute("UPDATE questions SET status = 'approved', moderated_by = ? WHERE $where", array_merge([Auth::id()], $params));
            $msg = 'បានអនុម័ត និងបង្ហាញសំណួរដែលបានជ្រើស (' . count($ids) . ') លើផ្ទាំងសាធារណៈ!';
            break;
        case 'hide':
        case 'unapprove':
            $db->execute("UPDATE questions SET status = 'pending', moderated_by = ? WHERE $where", array_merge([Auth::id()], $params));
            $msg = 'បានលាក់សំណួរដែលបានជ្រើស (' . count($ids) . ') ពីផ្ទាំងសាធារណៈ!';
            break;
        case 'reject':
            $db->execute("UPDATE questions SET status = 'rejected', moderated_by = ? WHERE $where", array_merge([Auth::id()], $params));
            $msg = 'បានបដិសេធសំណួរដែលបានជ្រើស (' . count($ids) . ')!';
            break;
        case 'done':
        case 'answer':
            $db->execute("UPDATE questions SET status = 'answered', answered_by = ?, answered_at = NOW() WHERE $where", array_merge([Auth::id()], $params));
            $msg = 'បានកំណត់ថាសំណួរបានឆ្លើយរួចរាល់ (' . count($ids) . ')!';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid bulk action.']);
            exit;
    }

    echo json_encode(['success' => true, 'message' => $msg, 'affected' => count($ids)]);
}
