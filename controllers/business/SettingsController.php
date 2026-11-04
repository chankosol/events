<?php
// C:\xampp\htdocs\workshopos\controllers\business\SettingsController.php

class BusinessSettingsController {
    public static function index(): void {
        requireAuth();
        requirePermission('business.settings');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        // Fallback for platform admin without active tenant
        if (!$businessId) {
            $businessId = (int)($db->queryValue("SELECT id FROM businesses WHERE deleted_at IS NULL ORDER BY id ASC LIMIT 1") ?: 1);
        }

        $business = $businessId ? $db->queryOne("SELECT * FROM businesses WHERE id = ?", [$businessId]) : null;
        if (!$business) {
            $business = [
                'id' => $businessId ?: 1,
                'name' => '',
                'email' => '',
                'phone' => '',
                'website' => '',
                'country' => 'Cambodia',
                'city' => 'Phnom Penh',
                'timezone' => 'Asia/Phnom_Penh',
                'address' => '',
                'preferred_currency' => 'USD',
                'preferred_language' => 'km'
            ];
        }

        $branding = $businessId ? $db->queryOne("SELECT * FROM business_branding WHERE business_id = ?", [$businessId]) : null;
        if (!$branding) {
            $branding = [
                'logo_path' => null,
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d'
            ];
        }

        // Resilient query for payment methods with auto-heal for is_default column
        $paymentMethods = [];
        if ($businessId) {
            try {
                $paymentMethods = $db->query("SELECT * FROM business_payment_methods WHERE business_id = ? ORDER BY is_default DESC, sort_order ASC, id ASC", [$businessId]);
            } catch (Throwable $e) {
                try {
                    $db->execute("ALTER TABLE business_payment_methods ADD COLUMN is_default TINYINT(1) DEFAULT 0");
                    $paymentMethods = $db->query("SELECT * FROM business_payment_methods WHERE business_id = ? ORDER BY is_default DESC, sort_order ASC, id ASC", [$businessId]);
                } catch (Throwable $e2) {
                    try {
                        $paymentMethods = $db->query("SELECT * FROM business_payment_methods WHERE business_id = ? ORDER BY sort_order ASC, id ASC", [$businessId]);
                    } catch (Throwable $e3) {
                        $paymentMethods = [];
                    }
                }
            }
        }
        
        // Resilient query for business_settings with auto-heal for table
        $businessSettings = [];
        if ($businessId) {
            try {
                $settingsRows = $db->query("SELECT setting_key, setting_value FROM business_settings WHERE business_id = ?", [$businessId]);
                if (is_array($settingsRows)) {
                    foreach ($settingsRows as $r) {
                        $businessSettings[$r['setting_key']] = $r['setting_value'];
                    }
                }
            } catch (Throwable $e) {
                try {
                    $db->execute("CREATE TABLE IF NOT EXISTS business_settings (
                        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        business_id INT UNSIGNED NOT NULL,
                        setting_key VARCHAR(100) NOT NULL,
                        setting_value TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        UNIQUE KEY unique_business_key (business_id, setting_key)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                } catch (Throwable $e2) {}
            }
        }

        $userId = Auth::id();
        $user = ($userId ? $db->queryOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$userId]) : null) ?? Auth::user();

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

        $isDefault = !empty($_POST['is_default']) ? 1 : 0;
        $existingCount = (int)$db->queryValue("SELECT COUNT(*) FROM business_payment_methods WHERE business_id = ?", [$businessId]);
        if ($existingCount === 0) {
            $isDefault = 1;
        } elseif ($isDefault === 1) {
            $db->execute("UPDATE business_payment_methods SET is_default = 0 WHERE business_id = ?", [$businessId]);
        }

        $db->execute(
            "INSERT INTO business_payment_methods (business_id, name, bank, account_name, account_number, qr_image_path, instructions, currency, status, is_default)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)",
            [$businessId, $name, $bank, $accName, $accNum, $qrPath, $instructions, $currency, $isDefault]
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
        $isDefault    = !empty($_POST['is_default']) ? 1 : 0;

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

        if ($isDefault === 1) {
            $db->execute("UPDATE business_payment_methods SET is_default = 0 WHERE business_id = ?", [$businessId]);
        }

        $db->execute(
            "UPDATE business_payment_methods 
             SET name = ?, bank = ?, account_name = ?, account_number = ?, qr_image_path = ?, instructions = ?, currency = ?, status = ?, is_default = ?, updated_at = NOW()
             WHERE id = ? AND business_id = ?",
            [$name, $bank, $accName, $accNum, $qrPath, $instructions, $currency, $status, $isDefault, $id, $businessId]
        );

        auditLog('payment_method_updated', 'business_payment_methods', $id);
        Session::flash('success', 'បានកែប្រែវិធីសាស្ត្រទូទាត់ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }

    public static function setDefaultPaymentMethod(): void {
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

        try {
            $db->execute("ALTER TABLE business_payment_methods ADD COLUMN is_default TINYINT(1) DEFAULT 0");
        } catch (Throwable $e) {}

        $existing = $db->queryOne("SELECT * FROM business_payment_methods WHERE id = ? AND business_id = ?", [$id, $businessId]);
        if (!$existing) {
            Session::flash('error', 'រកមិនឃើញវិធីសាស្ត្រទូទាត់នេះទេ។');
            redirect(APP_URL . '/settings#paymentsTab');
        }

        $db->execute("UPDATE business_payment_methods SET is_default = 0 WHERE business_id = ?", [$businessId]);
        $db->execute("UPDATE business_payment_methods SET is_default = 1 WHERE id = ? AND business_id = ?", [$id, $businessId]);

        auditLog('payment_method_set_default', 'business_payment_methods', $id);
        Session::flash('success', 'បានកំណត់ «' . $existing['name'] . '» ជាវិធីសាស្ត្រទូទាត់ចម្បង (Default) ដោយជោគជ័យ។');
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

        $wasDefault = !empty($existing['is_default']);

        $db->execute("DELETE FROM business_payment_methods WHERE id = ? AND business_id = ?", [$id, $businessId]);

        // If deleted was default, promote next active
        if ($wasDefault) {
            $next = $db->queryOne("SELECT id FROM business_payment_methods WHERE business_id = ? ORDER BY id ASC LIMIT 1", [$businessId]);
            if ($next) {
                $db->execute("UPDATE business_payment_methods SET is_default = 1 WHERE id = ?", [$next['id']]);
            }
        }

        auditLog('payment_method_deleted', 'business_payment_methods', $id);
        Session::flash('success', 'បានលុបវិធីសាស្ត្រទូទាត់ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }

    public static function saveGatewaySetup(): void {
        requireAuth();
        requirePermission('business.settings');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();

        try {
            $db->execute("CREATE TABLE IF NOT EXISTS business_settings (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                business_id INT UNSIGNED NOT NULL,
                setting_key VARCHAR(100) NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_business_key (business_id, setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Throwable $e) {}

        $fields = [
            'payment_provider',
            'bakong_account_id',
            'merchant_name',
            'bakong_api_token',
            'bakong_api_base_url',
            'bakong_static_qr_text',
            'aba_merchant_id',
            'aba_merchant_name',
            'aba_api_key',
            'aba_api_url'
        ];

        foreach ($fields as $key) {
            $val = trim($_POST[$key] ?? '');
            $db->execute(
                "INSERT INTO business_settings (business_id, setting_key, setting_value) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                [$businessId, $key, $val]
            );
        }

        auditLog('gateway_setup_updated', 'business_settings', $businessId);
        Session::flash('success', 'បានរក្សាទុកការកំណត់ Bakong QR និង ABA PayWay ដោយជោគជ័យ។');
        redirect(APP_URL . '/settings#paymentsTab');
    }
}

