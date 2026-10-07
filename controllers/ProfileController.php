<?php
// C:\xampp\htdocs\workshopos\controllers\ProfileController.php

class ProfileController {
    public static function index(): void {
        requireAuth();
        $db = Database::getInstance();
        $userId = Auth::id();
        $user = $db->queryOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$userId]);
        
        if (!$user) {
            Session::flash('error', 'រកមិនឃើញគណនីរបស់អ្នកប្រើប្រាស់ឡើយ។');
            redirect(APP_URL . '/login');
        }

        $isPlatform = empty($user['business_id']);
        $title = 'ព័ត៌មានផ្ទាល់ខ្លួន - ' . APP_NAME;

        ob_start();
        require VIEWS_PATH . '/profile/index.php';
        $content = ob_get_clean();

        if ($isPlatform) {
            require VIEWS_PATH . '/layouts/platform.php';
        } else {
            require VIEWS_PATH . '/layouts/business.php';
        }
    }

    public static function update(): void {
        requireAuth();
        requireCsrf();
        $db = Database::getInstance();
        $userId = Auth::id();
        $user = $db->queryOne("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL", [$userId]);

        if (!$user) {
            Session::flash('error', 'រកមិនឃើញគណនីរបស់អ្នកប្រើប្រាស់ឡើយ។');
            redirect(APP_URL . '/profile');
        }

        $name  = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name)) {
            Session::flash('error', 'សូមបញ្ចូលឈ្មោះពេញរបស់អ្នក។');
            redirect(APP_URL . '/profile');
        }

        // Handle profile photo upload
        $photoPath = $user['profile_photo'];
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = uploadFile($_FILES['profile_photo'], 'profile_photo', $user['business_id'] ? (int)$user['business_id'] : null);
            if ($upload['success']) {
                $photoPath = $upload['path'];
            } else {
                Session::flash('error', 'ការផ្ទុករូបភាពបរាជ័យ៖ ' . ($upload['message'] ?? 'ប្រភេទឯកសារមិនត្រឹមត្រូវ'));
                redirect(APP_URL . '/profile');
            }
        } elseif (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            $photoPath = null;
        }

        // Handle password update if requested
        $passwordHash = $user['password'];
        if (!empty($newPassword)) {
            if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
                Session::flash('error', 'ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវឡើយ។');
                redirect(APP_URL . '/profile');
            }
            if (strlen($newPassword) < 6) {
                Session::flash('error', 'ពាក្យសម្ងាត់ថ្មីត្រូវមានយ៉ាងតិច ៦ តួអក្សរ។');
                redirect(APP_URL . '/profile');
            }
            if ($newPassword !== $confirmPassword) {
                Session::flash('error', 'ពាក្យសម្ងាត់ថ្មី និងការផ្ទៀងផ្ទាត់មិនត្រូវគ្នាឡើយ។');
                redirect(APP_URL . '/profile');
            }
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $db->execute(
            "UPDATE users SET name = ?, phone = ?, profile_photo = ?, password = ?, updated_at = NOW() WHERE id = ?",
            [$name, $phone, $photoPath, $passwordHash, $userId]
        );

        // Refresh user in session
        $updatedUser = $db->queryOne("SELECT * FROM users WHERE id = ?", [$userId]);
        unset($updatedUser['password'], $updatedUser['remember_token'], $updatedUser['password_reset_token'], $updatedUser['password_reset_expires']);
        Session::set('user', $updatedUser);

        auditLog('profile_updated', 'user', $userId, null, ['name' => $name, 'phone' => $phone]);

        Session::flash('success', 'ព័ត៌មានផ្ទាល់ខ្លួន និងរូបថតត្រូវបានកែប្រែដោយជោគជ័យ!');
        
        $redirectUrl = $_POST['redirect_back'] ?? (APP_URL . '/profile');
        redirect($redirectUrl);
    }
}
