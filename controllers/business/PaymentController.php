<?php
class PaymentController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('payment.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        // Verify workshop belongs to business
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die('Not found.'); }

        $status = $_GET['status'] ?? '';
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = 'WHERE pp.business_id = ? AND r.workshop_id = ?';
        $params = [$businessId, $workshopId];
        if ($status) { $where .= ' AND pp.status = ?'; $params[] = $status; }
        if ($search) { $where .= ' AND (pa.name LIKE ? OR pa.email LIKE ? OR pp.transaction_reference LIKE ?)'; $params[] = "%{$search}%"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }

        $total = $db->queryOne("SELECT COUNT(*) as c FROM payment_proofs pp JOIN registrations r ON r.id = pp.registration_id JOIN participants pa ON pa.id = r.participant_id {$where}", $params)['c'];
        $params[] = $perPage; $params[] = $offset;
        $proofs = $db->query(
            "SELECT pp.*, pa.name as participant_name, pa.email as participant_email, r.registration_code,
                    r.status as reg_status, bpm.name as payment_method_name, bpm.bank
             FROM payment_proofs pp
             JOIN registrations r ON r.id = pp.registration_id
             JOIN participants pa ON pa.id = r.participant_id
             LEFT JOIN business_payment_methods bpm ON bpm.id = pp.payment_method_id
             {$where} ORDER BY pp.submitted_at DESC LIMIT ? OFFSET ?",
            $params
        );
        
        $totalPages = ceil($total / $perPage);
        $title = 'Payments - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/payments/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    public static function approveProof(int $proofId): void {
        requireAuth();
        requirePermission('payment.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $proof = $db->queryOne("SELECT pp.*, r.workshop_id, r.participant_id FROM payment_proofs pp JOIN registrations r ON r.id = pp.registration_id WHERE pp.id = ? AND pp.business_id = ?", [$proofId, $businessId]);
        if (!$proof) {
            echo json_encode(['success' => false, 'message' => 'Proof not found.']); exit;
        }

        $db->beginTransaction();
        try {
            // Approve proof
            $db->execute("UPDATE payment_proofs SET status='approved', reviewed_by=?, reviewed_at=NOW(), review_notes=? WHERE id=?",
                [Auth::id(), trim($_POST['notes'] ?? ''), $proofId]);

            // Update payment record
            $db->execute("UPDATE payments SET status='paid', verified_by=?, verified_at=NOW() WHERE registration_id=?",
                [Auth::id(), $proof['registration_id']]);

            // Update registration status
            $db->execute("UPDATE registrations SET payment_status='paid', status='confirmed', confirmed_at=NOW() WHERE id=?",
                [$proof['registration_id']]);

            // Generate QR if not exists
            $existingQr = $db->queryOne("SELECT id FROM participant_qr WHERE registration_id=?", [$proof['registration_id']]);
            if (!$existingQr) {
                $token = bin2hex(random_bytes(32));
                $db->execute("INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active) VALUES (?,?,?,?,?,1)",
                    [$proof['registration_id'], $proof['participant_id'], $businessId, $proof['workshop_id'], $token]);
            }

            $db->commit();
            auditLog('payment_approved', 'payment_proof', $proofId, ['status' => 'pending'], ['status' => 'approved']);
            echo json_encode(['success' => true, 'message' => 'Payment approved. Registration confirmed and QR code generated.']);
        } catch (Exception $e) {
            $db->rollback();
            error_log($e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to approve payment.']);
        }
    }

    public static function rejectProof(int $proofId): void {
        requireAuth();
        requirePermission('payment.verify');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $proof = $db->queryOne("SELECT * FROM payment_proofs WHERE id=? AND business_id=?", [$proofId, $businessId]);
        if (!$proof) {
            echo json_encode(['success' => false, 'message' => 'Proof not found.']); exit;
        }

        $reason = trim($_POST['reason'] ?? 'Payment could not be verified.');
        $db->execute("UPDATE payment_proofs SET status='rejected', reviewed_by=?, reviewed_at=NOW(), review_notes=? WHERE id=?",
            [Auth::id(), $reason, $proofId]);
        $db->execute("UPDATE registrations SET payment_status='rejected' WHERE id=?", [$proof['registration_id']]);

        auditLog('payment_rejected', 'payment_proof', $proofId);
        echo json_encode(['success' => true, 'message' => 'Payment proof rejected.']);
    }
}
