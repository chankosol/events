<?php
// C:\xampp\htdocs\workshopos\controllers\platform\WorkshopController.php

class PlatformWorkshopController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $status = $_GET['status'] ?? '';
        $bizId  = (int)($_GET['business_id'] ?? 0);
        $search = trim($_GET['q'] ?? '');

        $where = "WHERE w.deleted_at IS NULL";
        $params = [];

        if (!empty($status)) {
            $where .= " AND w.status = ?";
            $params[] = $status;
        }

        if ($bizId > 0) {
            $where .= " AND w.business_id = ?";
            $params[] = $bizId;
        }

        if (!empty($search)) {
            $where .= " AND (w.name LIKE ? OR b.name LIKE ? OR w.trainer_name LIKE ? OR b.contact_person LIKE ? OR b.phone LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $workshops = $db->query(
            "SELECT w.*, 
                    b.name as business_name, 
                    b.slug as business_slug,
                    COALESCE(NULLIF(b.contact_person, ''), (SELECT u.name FROM users u WHERE u.business_id = b.id AND u.deleted_at IS NULL ORDER BY u.id ASC LIMIT 1)) as contact_person,
                    COALESCE(NULLIF(b.phone, ''), (SELECT u.phone FROM users u WHERE u.business_id = b.id AND u.deleted_at IS NULL ORDER BY u.id ASC LIMIT 1)) as business_phone,
                    COALESCE(NULLIF(b.email, ''), (SELECT u.email FROM users u WHERE u.business_id = b.id AND u.deleted_at IS NULL ORDER BY u.id ASC LIMIT 1)) as business_email,
                    bb.logo_path,
                    wb.id as billing_id,
                    wb.platform_fee, 
                    wb.payment_status as billing_status, 
                    wb.currency as billing_currency,
                    (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id AND r.deleted_at IS NULL) as reg_count,
                    (SELECT COUNT(*) FROM attendance a WHERE a.workshop_id = w.id) as attend_count
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN business_branding bb ON bb.business_id = b.id
             LEFT JOIN workshop_billing wb ON wb.workshop_id = w.id
             {$where}
             ORDER BY w.created_at DESC",
            $params
        );

        $businesses = $db->query("SELECT id, name FROM businesses WHERE deleted_at IS NULL ORDER BY name ASC");

        $totalWorkshops = count($workshops);
        $activeWorkshops = $db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE status IN ('active','registration_open','in_progress') AND deleted_at IS NULL")['c'] ?? 0;

        $title = 'សិក្ខាសាលាទាំងអស់ - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/workshops/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    /**
     * Super Admin directly approves / activates a workshop (fee waived or approved without online pay)
     */
    public static function approve(int $id): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();

        $db = Database::getInstance();
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$workshop) {
            Session::flash('error', 'រកមិនឃើញសិក្ខាសាលានេះទេ។');
            redirect(APP_URL . '/platform/workshops');
        }

        $adminUser = Auth::user();
        $adminName = $adminUser['name'] ?? 'Super Admin';
        $note = trim($_POST['admin_note'] ?? '');
        $noteText = 'អនុម័ត និងលើកលែងថ្លៃប្រព័ន្ធដោយ Super Admin (' . $adminName . ')' . ($note ? ': ' . $note : '');

        $db->beginTransaction();
        try {
            // 1. Check or create workshop_billing
            $billing = $db->queryOne("SELECT * FROM workshop_billing WHERE workshop_id = ?", [$id]);
            if ($billing) {
                $db->execute(
                    "UPDATE workshop_billing 
                     SET payment_status = 'paid', 
                         paid_at = NOW(), 
                         payment_method = 'Admin Waiver / Approval',
                         transaction_reference = COALESCE(NULLIF(transaction_reference, ''), 'ADMIN_WAIVED'),
                         notes = CONCAT(COALESCE(notes, ''), '\n', ?)
                     WHERE id = ?",
                    [$noteText, $billing['id']]
                );
                $billingId = (int)$billing['id'];
            } else {
                $invoiceNo = generateInvoiceNumber();
                $db->execute(
                    "INSERT INTO workshop_billing (business_id, workshop_id, capacity, platform_fee, currency, payment_status, payment_method, invoice_number, transaction_reference, notes, paid_at)
                     VALUES (?, ?, ?, 0.00, 'USD', 'paid', 'Admin Waiver / Approval', ?, 'ADMIN_WAIVED', ?, NOW())",
                    [$workshop['business_id'], $id, $workshop['capacity'], $invoiceNo, $noteText]
                );
                $billingId = (int)$db->lastInsertId();
            }

            // 2. Update or create platform_invoices
            $invoice = $db->queryOne("SELECT id FROM platform_invoices WHERE workshop_billing_id = ?", [$billingId]);
            if ($invoice) {
                $db->execute("UPDATE platform_invoices SET status = 'paid', paid_date = CURDATE() WHERE id = ?", [$invoice['id']]);
            } else {
                $invoiceNo = $billing['invoice_number'] ?? generateInvoiceNumber();
                $db->execute(
                    "INSERT INTO platform_invoices (invoice_number, business_id, workshop_billing_id, invoice_type, description, amount, currency, status, issue_date, paid_date)
                     VALUES (?, ?, ?, 'workshop_activation', 'Workshop Activation Fee (Waived by Super Admin)', 0.00, 'USD', 'paid', CURDATE(), CURDATE())",
                    [$invoiceNo, $workshop['business_id'], $billingId]
                );
            }

            // 3. Activate workshop if in draft or pending_payment
            $newStatus = in_array($workshop['status'], ['draft', 'pending_payment']) ? 'active' : $workshop['status'];
            $db->execute("UPDATE workshops SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

            // 4. Record auto-approved proof record
            $existingProof = $db->queryOne("SELECT id FROM platform_payment_proofs WHERE workshop_billing_id = ?", [$billingId]);
            if (!$existingProof) {
                $db->execute(
                    "INSERT INTO platform_payment_proofs (workshop_billing_id, business_id, proof_file, amount_claimed, currency, payment_method, transaction_reference, payment_date, notes, status, reviewed_by, reviewed_at, review_notes, submitted_at)
                     VALUES (?, ?, 'superadmin_waiver', 0.00, 'USD', 'Admin Waiver', 'ADMIN_WAIVED', CURDATE(), ?, 'approved', ?, NOW(), 'Waived and approved directly by Super Admin', NOW())",
                    [$billingId, $workshop['business_id'], $noteText, Auth::id()]
                );
            } else {
                $db->execute(
                    "UPDATE platform_payment_proofs 
                     SET status = 'approved', reviewed_by = ?, reviewed_at = NOW(), review_notes = 'Approved directly by Super Admin' 
                     WHERE workshop_billing_id = ? AND status = 'pending'",
                    [Auth::id(), $billingId]
                );
            }

            auditLog('platform_workshop_approved', 'workshops', $id, ['previous_status' => $workshop['status']], ['status' => $newStatus, 'payment_status' => 'paid', 'approved_by' => $adminName]);

            $db->commit();
            Session::flash('success', 'បានអនុម័ត និងបើកដំណើរការសិក្ខាសាលា «' . $workshop['name'] . '» ដោយជោគជ័យ!');
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Approve workshop failed: ' . $e->getMessage());
            Session::flash('error', 'មានបញ្ហាក្នុងការអនុម័តសិក្ខាសាលា៖ ' . $e->getMessage());
        }

        redirect(APP_URL . '/platform/workshops');
    }
}
