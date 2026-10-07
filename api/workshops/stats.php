<?php
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
if (!Auth::check() || !Permission::has('workshop.view')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Workshop ID required.']);
    exit;
}
api_workshop_stats((int)$id);

function api_workshop_stats(int $id): void {
    $businessId = Tenant::id();
    $db = Database::getInstance();
    
    $workshop = $db->queryOne("SELECT id FROM workshops WHERE id=? AND business_id=? AND deleted_at IS NULL", [$id, $businessId]);
    if (!$workshop) { echo json_encode(['success'=>false,'message'=>'Not found.']); return; }
    
    $stats = $db->queryOne(
        "SELECT
           COUNT(r.id) as total,
           SUM(CASE WHEN r.status IN ('confirmed','attended','completed') THEN 1 ELSE 0 END) as confirmed,
           SUM(CASE WHEN r.payment_status IN ('paid','paid_cash','complimentary','waived') THEN 1 ELSE 0 END) as paid,
           SUM(CASE WHEN a.checked_in_at IS NOT NULL THEN 1 ELSE 0 END) as checked_in
         FROM registrations r
         LEFT JOIN attendance a ON a.registration_id=r.id
         WHERE r.workshop_id=? AND r.business_id=? AND r.deleted_at IS NULL",
        [$id, $businessId]
    );
    $stats['open_questions'] = $db->queryOne("SELECT COUNT(*) as c FROM questions WHERE workshop_id=? AND status='pending'",[$id])['c'];
    $stats['open_requests']  = $db->queryOne("SELECT COUNT(*) as c FROM participant_requests WHERE workshop_id=? AND status IN ('new','in_progress')",[$id])['c'];
    
    echo json_encode(['success'=>true,'data'=>$stats]);
}
