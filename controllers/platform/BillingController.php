<?php
class BillingController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();
        
        $proofs = $db->query(
            "SELECT ppp.*, b.name as business_name, w.name as workshop_name, wb.platform_fee, wb.currency
             FROM platform_payment_proofs ppp
             JOIN workshop_billing wb ON wb.id = ppp.workshop_billing_id
             JOIN businesses b ON b.id = ppp.business_id
             JOIN workshops w ON w.id = wb.workshop_id
             ORDER BY ppp.submitted_at DESC"
        );
        
        $title = 'Billing Management - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/billing.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
    
    public static function approveProof(int $proofId): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        
        $db = Database::getInstance();
        $proof = $db->queryOne("SELECT * FROM platform_payment_proofs WHERE id = ? AND status = 'pending'", [$proofId]);
        if (!$proof) jsonResponse(false, 'Proof not found or not pending.');
        
        $db->beginTransaction();
        try {
            $db->execute("UPDATE platform_payment_proofs SET status = 'approved', verified_at = NOW(), verified_by = ? WHERE id = ?", [Auth::id(), $proofId]);
            $db->execute("UPDATE workshop_billing SET payment_status = 'paid', paid_at = NOW() WHERE id = ?", [$proof['workshop_billing_id']]);
            $db->execute("UPDATE platform_invoices SET status = 'paid' WHERE workshop_billing_id = ?", [$proof['workshop_billing_id']]);
            
            $billing = $db->queryOne("SELECT workshop_id FROM workshop_billing WHERE id = ?", [$proof['workshop_billing_id']]);
            $db->execute("UPDATE workshops SET status = 'active' WHERE id = ?", [$billing['workshop_id']]);
            
            auditLog('platform_payment_approved', 'platform_payment_proofs', $proofId, 'Approved by ' . Auth::user()['name']);
            $db->commit();
            jsonResponse(true, 'Payment proof approved and workshop activated.');
        } catch (Exception $e) {
            $db->rollBack();
            error_log($e->getMessage());
            jsonResponse(false, 'Error approving payment.');
        }
    }
    
    public static function rejectProof(int $proofId): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        
        $reason = $_POST['reason'] ?? '';
        if (empty($reason)) jsonResponse(false, 'Rejection reason is required.');
        
        $db = Database::getInstance();
        $proof = $db->queryOne("SELECT * FROM platform_payment_proofs WHERE id = ? AND status = 'pending'", [$proofId]);
        if (!$proof) jsonResponse(false, 'Proof not found or not pending.');
        
        $db->execute("UPDATE platform_payment_proofs SET status = 'rejected', rejection_reason = ?, verified_at = NOW(), verified_by = ? WHERE id = ?", [$reason, Auth::id(), $proofId]);
        auditLog('platform_payment_rejected', 'platform_payment_proofs', $proofId, 'Rejected by ' . Auth::user()['name'] . ' Reason: ' . $reason);
        
        jsonResponse(true, 'Payment proof rejected.');
    }
}

class PlatformBillingController extends BillingController {}

