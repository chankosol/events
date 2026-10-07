<?php
class ParticipantAuthController {
    public static function showLogin(): void {
        if (Auth::isParticipant()) redirect(APP_URL . '/participant/portal');
        $title = 'Participant Login - ' . APP_NAME;
        $errors = Session::flash('errors') ?? [];
        ob_start();
        require VIEWS_PATH . '/participant/login.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public static function login(): void {
        requireCsrf();
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('errors', ['email' => 'Valid email is required.']);
            redirect(APP_URL . '/participant/login');
        }

        $db = Database::getInstance();
        $participant = $db->queryOne(
            "SELECT * FROM participants WHERE email = ? AND status = 'active' AND deleted_at IS NULL",
            [$email]
        );

        if (!$participant) {
            Session::flash('errors', ['general' => 'No account found with this email.']);
            redirect(APP_URL . '/participant/login');
        }

        // If participant has a password, verify it; otherwise send magic link
        if ($participant['password'] && !empty($password)) {
            if (!password_verify($password, $participant['password'])) {
                Session::flash('errors', ['general' => 'Invalid email or password.']);
                redirect(APP_URL . '/participant/login');
            }
        } elseif (empty($password)) {
            // Check by registration code or email only (magic/simple login)
            // Allow login by email if they have registrations
            $hasRegistration = $db->queryOne("SELECT id FROM registrations WHERE participant_id = ? LIMIT 1", [$participant['id']]);
            if (!$hasRegistration) {
                Session::flash('errors', ['general' => 'No registrations found for this email.']);
                redirect(APP_URL . '/participant/login');
            }
        }

        Session::regenerate();
        unset($participant['password']);
        Session::set('participant', $participant);
        $db->execute("UPDATE participants SET last_login_at = NOW() WHERE id = ?", [$participant['id']]);
        redirect(APP_URL . '/participant/portal');
    }

    public static function logout(): void {
        Session::remove('participant');
        Session::flash('success', 'Logged out successfully.');
        redirect(APP_URL . '/participant/login');
    }
}
