<?php
// C:\xampp\htdocs\workshopos\api\workshops\participants.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';

Session::start();
if (Auth::check()) Tenant::resolve();

header('Content-Type: application/json');

function api_workshop_participants(int $workshopId): void {
    if (!Auth::check()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $businessId = Tenant::id();
    $db = Database::getInstance();
    $search = trim($_GET['q'] ?? '');

    $where = 'WHERE r.workshop_id = ? AND r.business_id = ? AND r.deleted_at IS NULL';
    $params = [$workshopId, $businessId];

    if ($search) {
        $where .= ' AND (p.name LIKE ? OR p.phone LIKE ? OR r.registration_code LIKE ?)';
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $participants = $db->query(
        "SELECT r.id as registration_id, r.registration_code, r.status as reg_status, r.payment_status, r.is_vip,
                p.name, p.email, p.phone, p.company,
                t.name as ticket_name,
                a.checked_in_at
         FROM registrations r
         JOIN participants p ON p.id = r.participant_id
         LEFT JOIN tickets t ON t.id = r.ticket_id
         LEFT JOIN attendance a ON a.registration_id = r.id
         {$where}
         ORDER BY r.created_at DESC
         LIMIT 50",
        $params
    );

    echo json_encode(['success' => true, 'data' => $participants]);
}
