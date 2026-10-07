<?php
class LoginController {
    public static function showForm(): void {
        if (Auth::check()) {
            if (Auth::isPlatformAdmin()) {
                redirect(APP_URL . '/platform');
            } else {
                redirect(APP_URL . '/dashboard');
            }
        }
        $title = 'Login - ' . APP_NAME;
        $flash_error   = Session::flash('error');
        $flash_success = Session::flash('success');
        ob_start();
        require VIEWS_PATH . '/auth/login.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public static function login(): void {
        requireCsrf();
        
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            Session::flash('error', 'Email and password are required.');
            redirect(APP_URL . '/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Invalid email format.');
            redirect(APP_URL . '/login');
        }

        $user = Auth::login($email, $password, $remember);

        if (!$user) {
            Session::flash('error', 'Invalid email or password. Please try again.');
            redirect(APP_URL . '/login');
        }

        // Redirect based on role or stored destination
        $redirectTo = Session::flash('redirect_after_login');
        if ($redirectTo && is_string($redirectTo)) {
            $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
            if ($base !== '' && str_starts_with($redirectTo, $base)) {
                $redirectTo = substr($redirectTo, strlen($base));
            }
            $cleanPath = '/' . ltrim($redirectTo, '/');
            if ($cleanPath !== '/' && $cleanPath !== '/login' && $cleanPath !== '/dashboard' && !str_contains($cleanPath, 'login')) {
                redirect(APP_URL . $cleanPath);
            }
        }

        if (Auth::isPlatformAdmin()) {
            redirect(APP_URL . '/platform');
        } else {
            // Check if onboarding needed
            $user = Auth::user();
            if (!empty($user['business_id'])) {
                $db = Database::getInstance();
                $biz = $db->queryOne("SELECT onboarding_completed FROM businesses WHERE id = ?", [$user['business_id']]);
                if ($biz && !$biz['onboarding_completed']) {
                    redirect(APP_URL . '/onboarding');
                }
            }
            redirect(APP_URL . '/dashboard');
        }
    }

    public static function logout(): void {
        Auth::logout();
        Session::flash('success', 'You have been logged out successfully.');
        redirect(APP_URL . '/login');
    }
}
