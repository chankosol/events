<?php
class OnboardingController {
    public static function index(): void {
        $user = Auth::user();
        if (!$user || empty($user['business_id'])) {
            redirect(APP_URL . '/login');
        }

        $db = Database::getInstance();
        $business = $db->queryOne("SELECT * FROM businesses WHERE id = ?", [$user['business_id']]);
        
        if ($business['onboarding_completed']) {
            redirect(APP_URL . '/dashboard');
        }

        $step = (int)$business['onboarding_step'];
        if ($step < 1) $step = 1;
        if ($step > 5) $step = 5;

        $branding = $db->queryOne("SELECT * FROM business_branding WHERE business_id = ?", [$business['id']]);

        $title = 'Onboarding - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/business/onboarding.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php'; // Reuse auth layout for center focus
    }

    public static function saveStep(int $step): void {
        $_POST['step'] = $step;
        self::process();
    }

    public static function process(): void {
        requireCsrf();
        
        $user = Auth::user();
        if (!$user || empty($user['business_id'])) {
            redirect(APP_URL . '/login');
        }

        $db = Database::getInstance();
        $business = $db->queryOne("SELECT * FROM businesses WHERE id = ?", [$user['business_id']]);
        
        $step = (int)($_POST['step'] ?? 1);

        try {
            if ($step === 1) {
                // Save Business Info
                $db->execute("
                    UPDATE businesses 
                    SET name = ?, business_type = ?, contact_person = ?, phone = ?, country = ?, city = ?, address = ?, onboarding_step = 2 
                    WHERE id = ?",
                    [
                        $_POST['name'] ?? '',
                        $_POST['business_type'] ?? '',
                        $_POST['contact_person'] ?? '',
                        $_POST['phone'] ?? '',
                        $_POST['country'] ?? '',
                        $_POST['city'] ?? '',
                        $_POST['address'] ?? '',
                        $business['id']
                    ]
                );
            } elseif ($step === 2) {
                // Save Branding
                $primary = $_POST['primary_color'] ?? '#0d6efd';
                $secondary = $_POST['secondary_color'] ?? '#6c757d';
                $db->execute("
                    UPDATE business_branding 
                    SET primary_color = ?, secondary_color = ? 
                    WHERE business_id = ?",
                    [$primary, $secondary, $business['id']]
                );
                $db->execute("UPDATE businesses SET onboarding_step = 3 WHERE id = ?", [$business['id']]);
            } elseif ($step === 3) {
                // Save Payment Methods placeholder
                $db->execute("UPDATE businesses SET onboarding_step = 4 WHERE id = ?", [$business['id']]);
            } elseif ($step === 4) {
                // Save Notifications settings placeholder
                $db->execute("UPDATE businesses SET onboarding_step = 5 WHERE id = ?", [$business['id']]);
            } elseif ($step === 5) {
                // Complete
                $db->execute("UPDATE businesses SET onboarding_completed = 1 WHERE id = ?", [$business['id']]);
                Session::flash('success', 'Onboarding completed! Welcome to your dashboard.');
                redirect(APP_URL . '/dashboard');
            }
            
            redirect(APP_URL . '/onboarding');
            
        } catch (Exception $e) {
            error_log('Onboarding error: ' . $e->getMessage());
            Session::flash('error', 'An error occurred saving your progress.');
            redirect(APP_URL . '/onboarding');
        }
    }
}
