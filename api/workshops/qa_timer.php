<?php
// api/workshops/qa_timer.php

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

function api_workshop_qa_timer(int $workshopId): void {
    if (!Auth::check() || (!Permission::has('workshop.edit') && !Permission::has('workshop.view') && !Auth::isBusinessUser() && !Auth::isPlatformAdmin())) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $db = Database::getInstance();
    $workshop = $db->queryOne("SELECT id, qa_status, qa_ends_at FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
    if (!$workshop) {
        echo json_encode(['success' => false, 'message' => 'Workshop not found.']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $minutes = (int)($_POST['minutes'] ?? 0);

    switch ($action) {
        case 'set_timer':
            if ($minutes <= 0) $minutes = 15;
            $newEndTime = date('Y-m-d H:i:s', time() + ($minutes * 60));
            $db->execute("UPDATE workshops SET qa_status = 'open', qa_ends_at = ? WHERE id = ?", [$newEndTime, $workshopId]);
            $msg = "បានកំណត់ម៉ោងសួរសំណួរចំនួន $minutes នាទី!";
            break;

        case 'add_time':
            if ($minutes <= 0) $minutes = 5;
            $currentEnd = $workshop['qa_ends_at'];
            $currentTs = $currentEnd ? strtotime($currentEnd) : 0;
            $baseTs = ($currentTs && $currentTs > time()) ? $currentTs : time();
            $newEndTime = date('Y-m-d H:i:s', $baseTs + ($minutes * 60));
            $db->execute("UPDATE workshops SET qa_status = 'open', qa_ends_at = ? WHERE id = ?", [$newEndTime, $workshopId]);
            $msg = "បានបន្ថែមម៉ោងសួរសំណួរចំនួន +$minutes នាទីទៀត!";
            break;

        case 'close':
            $db->execute("UPDATE workshops SET qa_status = 'closed', qa_ends_at = NULL WHERE id = ?", [$workshopId]);
            $msg = "បានបិទវេទិកាសួរសំណួរជាផ្លូវការ!";
            break;

        case 'open':
            $db->execute("UPDATE workshops SET qa_status = 'open', qa_ends_at = NULL WHERE id = ?", [$workshopId]);
            $msg = "បានបើកវេទិកាសួរសំណួរឡើងវិញ (គ្មានកំណត់ម៉ោង)!";
            break;

        case 'clear_timer':
            $db->execute("UPDATE workshops SET qa_ends_at = NULL WHERE id = ?", [$workshopId]);
            $msg = "បានលុបការកំណត់ម៉ោង Countdown (បន្តបើកធម្មតា)!";
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            exit;
    }

    $updated = $db->queryOne("SELECT id, qa_status, qa_ends_at FROM workshops WHERE id = ?", [$workshopId]);
    $remainingSeconds = null;
    if ($updated['qa_ends_at']) {
        $remainingSeconds = max(0, strtotime($updated['qa_ends_at']) - time());
    }

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'qa_control' => [
            'status' => $updated['qa_status'],
            'ends_at' => $updated['qa_ends_at'],
            'remaining_seconds' => $remainingSeconds
        ]
    ]);
}
