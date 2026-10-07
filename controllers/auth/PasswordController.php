<?php
class PasswordController {
    public static function showForgotForm(): void {
        if (Auth::check()) {
            redirect(Auth::isPlatformAdmin() ? APP_URL . '/platform' : APP_URL . '/dashboard');
        }
        $title = 'Forgot Password - ' . APP_NAME;
        $flash_error   = Session::flash('error');
        $flash_success = Session::flash('success');
        
        ob_start();
        require VIEWS_PATH . '/auth/forgot_password.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public static function sendReset(): void {
        self::sendResetLink();
    }

    public static function sendResetLink(): void {
        requireCsrf();
        
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Valid email is required.');
            redirect(APP_URL . '/forgot-password');
        }
        
        $db = Database::getInstance();
        $user = $db->queryOne("SELECT id FROM users WHERE email = ?", [$email]);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $db->execute("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?", [$token, $expires, $user['id']]);
            
            $resetLink = APP_URL . "/reset-password?token=" . $token;
            // Log to file since mail is not configured
            error_log("Password reset link for $email: $resetLink");
        }
        
        Session::flash('success', 'If an account with that email exists, we have sent a password reset link.');
        redirect(APP_URL . '/forgot-password');
    }

    public static function showResetForm(): void {
        if (Auth::check()) {
            redirect(Auth::isPlatformAdmin() ? APP_URL . '/platform' : APP_URL . '/dashboard');
        }
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            Session::flash('error', 'Invalid or missing password reset token.');
            redirect(APP_URL . '/login');
        }
        
        $db = Database::getInstance();
        $user = $db->queryOne("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()", [$token]);
        
        if (!$user) {
            Session::flash('error', 'The password reset token is invalid or has expired.');
            redirect(APP_URL . '/login');
        }
        
        $title = 'Reset Password - ' . APP_NAME;
        $flash_error = Session::flash('error');
        
        ob_start();
        require VIEWS_PATH . '/auth/reset_password.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public static function resetPassword(): void {
        requireCsrf();
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        
        if (empty($token)) {
            redirect(APP_URL . '/login');
        }
        
        if (strlen($password) < 8) {
            Session::flash('error', 'Password must be at least 8 characters.');
            redirect(APP_URL . '/reset-password?token=' . urlencode($token));
        }
        
        if ($password !== $confirm) {
            Session::flash('error', 'Passwords do not match.');
            redirect(APP_URL . '/reset-password?token=' . urlencode($token));
        }
        
        $db = Database::getInstance();
        $user = $db->queryOne("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()", [$token]);
        
        if (!$user) {
            Session::flash('error', 'The password reset token is invalid or has expired.');
            redirect(APP_URL . '/login');
        }
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db->execute("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?", [$hash, $user['id']]);
        
        Session::flash('success', 'Your password has been reset successfully. Please log in.');
        redirect(APP_URL . '/login');
    }
}
