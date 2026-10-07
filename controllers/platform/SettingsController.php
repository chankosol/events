<?php
// C:\xampp\htdocs\workshopos\controllers\platform\SettingsController.php

class PlatformSettingsController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $settings = $db->query("SELECT * FROM platform_settings ORDER BY id ASC");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['setting_key']] = $s['setting_value'];
        }

        $title = 'Platform System Settings - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/settings/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function save(): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        $db = Database::getInstance();

        $fields = [
            'app_name', 'app_tagline', 'default_currency', 'default_language',
            'support_email', 'maintenance_mode', 'registration_enabled', 'invoice_prefix',
            // Bakong Gateway Settings
            'bakong_api_base_url', 'bakong_api_token', 'bakong_account_id',
            'bakong_merchant_name', 'bakong_merchant_city', 'bakong_mcc', 'bakong_static_qr_text'
        ];

        foreach ($fields as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $exists = $db->queryOne("SELECT id FROM platform_settings WHERE setting_key = ?", [$key]);
                if ($exists) {
                    $db->execute("UPDATE platform_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$val, $key]);
                } else {
                    $db->execute("INSERT INTO platform_settings (setting_key, setting_value, setting_type, description, is_public) VALUES (?, ?, 'string', '', 0)", [$key, $val]);
                }
            }
        }

        auditLog('platform_settings_updated', 'platform_settings', 1);
        Session::flash('success', 'Platform settings and Bakong gateway updated successfully.');
        redirect(APP_URL . '/platform/settings');
    }

    public static function testBakong(): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();

        $tokenConfig = trim($_POST['bakong_api_token'] ?? bakong_get_platform_setting('bakong_api_token', ''));
        $baseUrl = rtrim(trim($_POST['bakong_api_base_url'] ?? 'https://api-bakong.nbc.gov.kh'), '/');

        if ($tokenConfig === '') {
            jsonResponse(false, 'សូមបញ្ចូល Bakong API Token មុនពេលធ្វើតេស្ត។');
        }

        $tokens = array_filter(array_map('trim', preg_split('/[\s,]+/', $tokenConfig)));
        if (empty($tokens)) {
            jsonResponse(false, 'គ្មាន Token ត្រឹមត្រូវ។');
        }

        $results = [];
        foreach ($tokens as $idx => $token) {
            $ch = curl_init($baseUrl . '/v1/check_transaction_by_md5');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode(['md5' => md5('test_ping_' . time())]),
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $res = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            $json = json_decode((string)$res, true);
            $tokenNum = $idx + 1;
            $masked = (strlen($token) > 10) ? substr($token, 0, 6) . '...' . substr($token, -4) : 'Token #' . $tokenNum;

            if ($code === 401) {
                $results[] = [
                    'token' => $masked,
                    'status' => 'invalid',
                    'message' => "Token #{$tokenNum}: Token ផុតកំណត់ ឬមិនត្រឹមត្រូវ (HTTP 401 Unauthorized - សូមពិនិត្យ ឬស្នើសុំ Token ថ្មីពី NBC)"
                ];
            } elseif ($code === 429 || (isset($json['errorCode']) && $json['errorCode'] == 17) || (isset($json['responseMessage']) && stripos($json['responseMessage'], 'limit') !== false)) {
                $results[] = [
                    'token' => $masked,
                    'status' => 'limit_exceeded',
                    'message' => "Token #{$tokenNum}: លើសដែនកំណត់ប្រចាំថ្ងៃ (Rate Limit Hit - ប្រព័ន្ធនឹងប្តូរទៅ Token បន្ទាប់ដោយស្វ័យប្រវត្តិ)"
                ];
            } elseif ($code >= 200 && $code < 300) {
                $results[] = [
                    'token' => $masked,
                    'status' => 'valid',
                    'message' => "Token #{$tokenNum}: តភ្ជាប់បានជោគជ័យ និងមានសុពលភាព (HTTP {$code})"
                ];
            } else {
                $results[] = [
                    'token' => $masked,
                    'status' => 'unknown',
                    'message' => "Token #{$tokenNum}: ស្ថានភាព HTTP {$code} (" . ($json['responseMessage'] ?? $err ?? 'Response received') . ")"
                ];
            }
        }

        jsonResponse(true, 'បានធ្វើតេស្តរួចរាល់', ['tokens_count' => count($tokens), 'results' => $results]);
    }
}
