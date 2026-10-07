<?php
// C:\xampp\htdocs\workshopos\api\certificates\eligibility.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';

header('Content-Type: application/json');

function api_check_eligibility(int $regId): void {
    $db = Database::getInstance();
    $reg = $db->queryOne("SELECT * FROM registrations WHERE id = ?", [$regId]);
    if (!$reg) {
        echo json_encode(['success' => false, 'message' => 'Registration not found.']);
        exit;
    }

    $attended = $db->queryOne("SELECT id FROM attendance WHERE registration_id = ? AND checked_in_at IS NOT NULL", [$regId]);

    echo json_encode([
        'success'  => true,
        'eligible' => (bool)$attended,
        'details'  => [
            'attended' => (bool)$attended,
            'status'   => $reg['status'],
        ]
    ]);
}
