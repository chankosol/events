<?php
// C:\xampp\htdocs\workshopos\controllers\business\SettingsController.php

class BusinessSettingsController {
    public static function index(): void {
        requireAuth();
        requirePermission('business.settings');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $business = $db->queryOne("SELECT * FROM businesses WHERE id = ?", [$businessId]);
        $branding = $db->queryOne("SELECT * FROM business_branding WHERE business_id = ?", [$businessId]);
        $paymentMethods = $db->query("SELECT * FROM business_payment_methods WHERE business_id = ? ORDER BY sort_order", [$businessId]);

        $title = 'Business Settings - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/settings/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }

    public static function saveGeneral(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $name       = trim($_POST['name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $country    = trim($_POST['country'] ?? '');
        $city       = trim($_POST['city'] ?? '');
        $address    = trim($_POST['address'] ?? '');
        $website    = trim($_POST['website'] ?? '');
        $timezone   = $_POST['timezone'] ?? 'Asia/Phnom_Penh';
        $currency   = $_POST['preferred_currency'] ?? 'USD';
        $language   = $_POST['preferred_language'] ?? 'en';

        if (empty($name)) {
            Session::flash('error', 'Company name is required.');
            redirect(APP_URL . '/settings');
        }

        $db->execute(
            "UPDATE businesses SET name = ?, phone = ?, country = ?, city = ?, address = ?, website = ?, timezone = ?, preferred_currency = ?, preferred_language = ?, updated_at = NOW() WHERE id = ?",
            [$name, $phone, $country, $city, $address, $website, $timezone, $currency, $language, $businessId]
        );

        auditLog('business_settings_updated', 'business', $businessId);
        Session::flash('success', 'General business settings saved successfully.');
        redirect(APP_URL . '/settings');
    }

    public static function saveBranding(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $primaryColor   = $_POST['primary_color'] ?? '#0d6efd';
        $secondaryColor = $_POST['secondary_color'] ?? '#6c757d';

        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            $upload = uploadFile($_FILES['logo'], 'logo', $businessId);
            if ($upload['success']) $logoPath = $upload['path'];
        }

        $branding = $db->queryOne("SELECT id FROM business_branding WHERE business_id = ?", [$businessId]);
        if ($branding) {
            $updates = ['primary_color = ?', 'secondary_color = ?', 'updated_at = NOW()'];
            $params  = [$primaryColor, $secondaryColor];
            if ($logoPath) {
                $updates[] = 'logo_path = ?';
                $params[]  = $logoPath;
            }
            $params[] = $businessId;
            $db->execute("UPDATE business_branding SET " . implode(', ', $updates) . " WHERE business_id = ?", $params);
        } else {
            $db->execute(
                "INSERT INTO business_branding (business_id, logo_path, primary_color, secondary_color) VALUES (?, ?, ?, ?)",
                [$businessId, $logoPath, $primaryColor, $secondaryColor]
            );
        }

        auditLog('branding_updated', 'business_branding', $businessId);
        Session::flash('success', 'Company branding updated successfully.');
        redirect(APP_URL . '/settings');
    }

    public static function savePaymentMethods(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $name       = trim($_POST['method_name'] ?? '');
        $bank       = trim($_POST['bank'] ?? '');
        $accName    = trim($_POST['account_name'] ?? '');
        $accNum     = trim($_POST['account_number'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $currency   = $_POST['currency'] ?? 'USD';

        if (empty($name)) {
            Session::flash('error', 'Payment method name is required.');
            redirect(APP_URL . '/settings');
        }

        $qrPath = null;
        if (!empty($_FILES['qr_image']['name'])) {
            $upload = uploadFile($_FILES['qr_image'], 'qr_image', $businessId);
            if ($upload['success']) $qrPath = $upload['path'];
        }

        $db->execute(
            "INSERT INTO business_payment_methods (business_id, name, bank, account_name, account_number, qr_image_path, instructions, currency, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')",
            [$businessId, $name, $bank, $accName, $accNum, $qrPath, $instructions, $currency]
        );

        auditLog('payment_method_created', 'business_payment_methods', $db->lastInsertId());
        Session::flash('success', 'បានបន្ថែមវិធីសាស្ត្រទូទាត់ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }

    public static function updatePaymentMethod(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $id           = (int)($_POST['id'] ?? 0);
        $name         = trim($_POST['method_name'] ?? '');
        $bank         = trim($_POST['bank'] ?? '');
        $accName      = trim($_POST['account_name'] ?? '');
        $accNum       = trim($_POST['account_number'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $currency     = $_POST['currency'] ?? 'USD';
        $status       = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if (!$id || empty($name)) {
            Session::flash('error', 'ឈ្មោះវិធីសាស្ត្រទូទាត់មិនអាចទទេបានទេ។');
            redirect(APP_URL . '/settings#paymentsTab');
        }

        $existing = $db->queryOne("SELECT * FROM business_payment_methods WHERE id = ? AND business_id = ?", [$id, $businessId]);
        if (!$existing) {
            Session::flash('error', 'រកមិនឃើញវិធីសាស្ត្រទូទាត់នេះទេ។');
            redirect(APP_URL . '/settings#paymentsTab');
        }

        $qrPath = $existing['qr_image_path'];
        if (!empty($_FILES['qr_image']['name'])) {
            $upload = uploadFile($_FILES['qr_image'], 'qr_image', $businessId);
            if ($upload['success']) {
                $qrPath = $upload['path'];
            }
        }

        $db->execute(
            "UPDATE business_payment_methods 
             SET name = ?, bank = ?, account_name = ?, account_number = ?, qr_image_path = ?, instructions = ?, currency = ?, status = ?, updated_at = NOW()
             WHERE id = ? AND business_id = ?",
            [$name, $bank, $accName, $accNum, $qrPath, $instructions, $currency, $status, $id, $businessId]
        );

        auditLog('payment_method_updated', 'business_payment_methods', $id);
        Session::flash('success', 'បានកែប្រែវិធីសាស្ត្រទូទាត់ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }

    public static function deletePaymentMethod(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            Session::flash('error', 'ការស្នើសុំមិនត្រឹមត្រូវ។');
            redirect(APP_URL . '/settings#paymentsTab');
        }

        $existing = $db->queryOne("SELECT * FROM business_payment_methods WHERE id = ? AND business_id = ?", [$id, $businessId]);
        if (!$existing) {
            Session::flash('error', 'រកមិនឃើញវិធីសាស្ត្រទូទាត់នេះទេ។');
            redirect(APP_URL . '/settings#paymentsTab');
        }

        $db->execute("DELETE FROM business_payment_methods WHERE id = ? AND business_id = ?", [$id, $businessId]);

        auditLog('payment_method_deleted', 'business_payment_methods', $id);
        Session::flash('success', 'បានលុបវិធីសាស្ត្រទូទាត់ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }
}
