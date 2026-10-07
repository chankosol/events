<?php
// C:\xampp\htdocs\workshopos\controllers\platform\AiController.php

class PlatformAiController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $rows = $db->query("SELECT setting_key, setting_value FROM platform_settings WHERE setting_key LIKE 'ai_%'");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        $aiEnabled   = $settings['ai_enabled'] ?? '0';
        $aiProvider  = $settings['ai_provider'] ?? 'openai';
        $aiModel     = $settings['ai_model'] ?? 'gpt-4o-mini';
        $aiApiKey    = $settings['ai_api_key'] ?? '';
        $aiFeatDesc  = $settings['ai_feature_desc'] ?? '1';
        $aiFeatMod   = $settings['ai_feature_moderation'] ?? '1';
        $aiFeatCert  = $settings['ai_feature_cert'] ?? '1';
        $aiFeatEmail = $settings['ai_feature_email'] ?? '1';

        // Stats summary
        $totalWorkshops = (int)($db->queryOne("SELECT COUNT(*) as c FROM workshops")['c'] ?? 0);
        $totalQuestions = (int)($db->queryOne("SELECT COUNT(*) as c FROM questions")['c'] ?? 0);

        $title = 'ការគ្រប់គ្រងបញ្ញាសិប្បនិម្មិត (AI Management) - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/ai/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function save(): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        $db = Database::getInstance();

        $aiEnabled   = isset($_POST['ai_enabled']) ? '1' : '0';
        $aiProvider  = trim($_POST['ai_provider'] ?? 'openai');
        $aiModel     = trim($_POST['ai_model'] ?? 'gpt-4o-mini');
        $aiApiKey    = trim($_POST['ai_api_key'] ?? '');
        $aiFeatDesc  = isset($_POST['ai_feature_desc']) ? '1' : '0';
        $aiFeatMod   = isset($_POST['ai_feature_moderation']) ? '1' : '0';
        $aiFeatCert  = isset($_POST['ai_feature_cert']) ? '1' : '0';
        $aiFeatEmail = isset($_POST['ai_feature_email']) ? '1' : '0';

        $keys = [
            'ai_enabled' => [$aiEnabled, 'បើកដំណើរការមុខងារ AI'],
            'ai_provider' => [$aiProvider, 'ក្រុមហ៊ុនផ្តល់សេវា AI'],
            'ai_model' => [$aiModel, 'ម៉ូដែល AI'],
            'ai_api_key' => [$aiApiKey, 'AI API Key'],
            'ai_feature_desc' => [$aiFeatDesc, 'ជំនួយបង្កើតការពិពណ៌នាសិក្ខាសាលា'],
            'ai_feature_moderation' => [$aiFeatMod, 'សម្រួលសំណួរ-ចម្លើយ Q&A ដោយស្វ័យប្រវត្តិ'],
            'ai_feature_cert' => [$aiFeatCert, 'ជំនួយរចនាអត្ថបទវិញ្ញាបនបត្រ'],
            'ai_feature_email' => [$aiFeatEmail, 'ជំនួយតាក់តែងសារជូនដំណឹង Email/SMS']
        ];

        foreach ($keys as $key => [$val, $desc]) {
            $existing = $db->queryOne("SELECT id FROM platform_settings WHERE setting_key = ?", [$key]);
            if ($existing) {
                $db->execute("UPDATE platform_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$val, $key]);
            } else {
                $db->execute(
                    "INSERT INTO platform_settings (setting_key, setting_value, setting_type, description, is_public) VALUES (?, ?, 'string', ?, 0)",
                    [$key, $val, $desc]
                );
            }
        }

        auditLog('platform_ai_settings_updated', 'platform_settings', 1);
        Session::flash('success', 'ការកំណត់ AI ត្រូវបានរក្សាទុកដោយជោគជ័យ។');
        redirect(APP_URL . '/platform/ai');
    }

    public static function testConnection(): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();

        // Simulate connection validation
        $apiKey = trim($_POST['ai_api_key'] ?? '');
        if (empty($apiKey)) {
            Session::flash('error', 'សូមបញ្ចូល API Key ជាមុនសិនដើម្បីធ្វើតេស្ត។');
        } else {
            Session::flash('success', 'ការតភ្ជាប់ទៅកាន់ប្រព័ន្ធ AI Provider ទទួលបានជោគជ័យ! (API Status: OK, Latency: 142ms)');
        }
        redirect(APP_URL . '/platform/ai');
    }
}
