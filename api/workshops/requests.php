<?php
// api/workshops/requests.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function api_workshop_requests(int $workshopId): void {
    if (!$workshopId) {
        echo json_encode(['success' => false, 'message' => 'Workshop ID required']);
        exit;
    }

    $db = Database::getInstance();
    $workshop = $db->queryOne("SELECT id, name FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
    if (!$workshop) {
        echo json_encode(['success' => false, 'message' => 'Workshop not found']);
        exit;
    }

    $requests = $db->query(
        "SELECT pr.*, COALESCE(pr.requester_name, p.name, 'សិក្ខាកាម') as display_name
         FROM participant_requests pr
         LEFT JOIN participants p ON p.id = pr.participant_id
         WHERE pr.workshop_id = ? AND pr.status IN ('new','assigned','in_progress')
         ORDER BY (CASE WHEN pr.priority = 'urgent' THEN 1 WHEN pr.priority = 'high' THEN 2 ELSE 3 END), pr.created_at ASC LIMIT 50",
        [$workshopId]
    );

    echo json_encode([
        'success' => true,
        'requests' => $requests,
        'count' => count($requests)
    ]);
}
