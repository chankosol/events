<?php
// api/requests/resolve.php

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

function api_resolve_request(int $id): void {
    if (!Auth::check() || (!Permission::has('workshop.view') && !Auth::isBusinessUser() && !Auth::isPlatformAdmin())) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $db = Database::getInstance();
    $businessId = Tenant::id();

    if (Auth::isPlatformAdmin()) {
        $req = $db->queryOne("SELECT * FROM participant_requests WHERE id = ?", [$id]);
    } else {
        $req = $db->queryOne("SELECT * FROM participant_requests WHERE id = ? AND business_id = ?", [$id, $businessId]);
    }

    if (!$req) {
        echo json_encode(['success' => false, 'message' => 'រកមិនឃើញសំណើជំនួយទេ។']);
        exit;
    }

    $status = $_POST['status'] ?? 'resolved';
    $db->execute("UPDATE participant_requests SET status = ?, resolved_at = NOW(), assigned_to = ? WHERE id = ?", 
        [$status, Auth::id(), $id]);

    echo json_encode(['success' => true, 'message' => 'សំណើជំនួយត្រូវបានកំណត់ថាដោះស្រាយរួចរាល់!']);
}
