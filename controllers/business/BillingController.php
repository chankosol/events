<?php
// C:\xampp\htdocs\workshopos\controllers\business\BillingController.php

class BusinessBillingController {
    public static function index(): void {
        requireAuth();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $billings = $db->query(
            "SELECT wb.*, w.name as workshop_name, w.capacity as workshop_capacity,
                    pr.name as pricing_tier, pi.status as invoice_status
             FROM workshop_billing wb
             JOIN workshops w ON w.id = wb.workshop_id
             LEFT JOIN platform_pricing_rules pr ON pr.id = wb.pricing_rule_id
             LEFT JOIN platform_invoices pi ON pi.workshop_billing_id = wb.id
             WHERE wb.business_id = ?
             ORDER BY wb.created_at DESC",
            [$businessId]
        );

        $totalPaid = $db->queryOne(
            "SELECT COALESCE(SUM(platform_fee), 0) as s FROM workshop_billing WHERE business_id = ? AND payment_status = 'paid'",
            [$businessId]
        )['s'] ?? 0;

        $totalPending = $db->queryOne(
            "SELECT COALESCE(SUM(platform_fee), 0) as s FROM workshop_billing WHERE business_id = ? AND payment_status IN ('unpaid','pending')",
            [$businessId]
        )['s'] ?? 0;

        $title = 'Platform Billing & Invoices - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/billing/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    public static function uploadProof(int $billingId): void {
        requireAuth();
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE id = ? AND business_id = ?", [$billingId, $businessId]);
        if (!$billing) {
            jsonResponse(false, 'Billing record not found.', [], 404);
        }

        if (empty($_FILES['proof_file']['name'])) {
            jsonResponse(false, 'Please select a proof file to upload.');
        }

        $upload = uploadFile($_FILES['proof_file'], 'platform_proof', $businessId);
        if (!$upload['success']) {
            jsonResponse(false, $upload['message']);
        }

        $invoice = $db->queryOne("SELECT id FROM platform_invoices WHERE workshop_billing_id = ?", [$billingId]);

        $db->execute(
            "INSERT INTO platform_payment_proofs (workshop_billing_id, business_id, invoice_id, proof_file, amount_claimed, currency, payment_method, transaction_reference, payment_date, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $billingId, $businessId,
                $invoice ? $invoice['id'] : null,
                $upload['path'],
                (float)($_POST['amount_claimed'] ?? $billing['platform_fee']),
                $billing['currency'],
                trim($_POST['payment_method'] ?? 'Bank QR'),
                trim($_POST['transaction_reference'] ?? ''),
                $_POST['payment_date'] ?? date('Y-m-d'),
                trim($_POST['notes'] ?? ''),
            ]
        );

        $db->execute("UPDATE workshop_billing SET payment_status = 'pending' WHERE id = ?", [$billingId]);
        auditLog('platform_proof_uploaded', 'workshop_billing', $billingId);

        Session::flash('success', 'Payment proof uploaded successfully.');
        redirect(APP_URL . '/billing');
    }
}
