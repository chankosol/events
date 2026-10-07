<?php
class RegisterController {
    public static function showForm(): void {
        if (Auth::check()) redirect(Auth::isPlatformAdmin() ? APP_URL . '/platform' : APP_URL . '/dashboard');
        $title = 'Create Account - ' . APP_NAME;
        $errors = Session::flash('errors') ?? [];
        $old    = Session::flash('old') ?? [];
        ob_start();
        require VIEWS_PATH . '/auth/register.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/auth.php';
    }

    public static function register(): void {
        requireCsrf();
        
        $data = [
            'company_name'        => trim($_POST['company_name'] ?? ''),
            'business_type'       => trim($_POST['business_type'] ?? ''),
            'contact_person'      => trim($_POST['contact_person'] ?? ''),
            'email'               => strtolower(trim($_POST['email'] ?? '')),
            'phone'               => trim($_POST['phone'] ?? ''),
            'country'             => trim($_POST['country'] ?? ''),
            'city'                => trim($_POST['city'] ?? ''),
            'address'             => trim($_POST['address'] ?? ''),
            'website'             => trim($_POST['website'] ?? ''),
            'password'            => $_POST['password'] ?? '',
            'confirm_password'    => $_POST['confirm_password'] ?? '',
            'preferred_language'  => in_array($_POST['preferred_language'] ?? 'en', ['en','km']) ? $_POST['preferred_language'] : 'en',
            'preferred_currency'  => in_array($_POST['preferred_currency'] ?? 'USD', ['USD','KHR']) ? $_POST['preferred_currency'] : 'USD',
        ];

        $errors = self::validate($data);

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', $data);
            redirect(APP_URL . '/register');
        }

        // Check if email exists in users
        $db = Database::getInstance();
        $exists = $db->queryOne("SELECT id FROM users WHERE email = ?", [$data['email']]);
        if ($exists) {
            Session::flash('errors', ['email' => 'This email is already registered.']);
            Session::flash('old', $data);
            redirect(APP_URL . '/register');
        }

        // Check if business email exists
        $bizExists = $db->queryOne("SELECT id FROM businesses WHERE email = ?", [$data['email']]);
        if ($bizExists) {
            Session::flash('errors', ['email' => 'This business email is already registered.']);
            Session::flash('old', $data);
            redirect(APP_URL . '/register');
        }

        try {
            $db->beginTransaction();

            // Create business
            $slug = uniqueSlug($data['company_name'], 'businesses', 'slug');
            $db->execute(
                "INSERT INTO businesses (slug, name, business_type, contact_person, email, phone, country, city, address, website, preferred_language, preferred_currency, status, onboarding_step)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)",
                [
                    $slug, $data['company_name'], $data['business_type'],
                    $data['contact_person'], $data['email'], $data['phone'],
                    $data['country'], $data['city'], $data['address'],
                    $data['website'], $data['preferred_language'], $data['preferred_currency'],
                ]
            );
            $businessId = (int)$db->lastInsertId();

            // Create business owner user
            $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $db->execute(
                "INSERT INTO users (business_id, name, email, phone, password, status, email_verified_at, preferred_language)
                 VALUES (?, ?, ?, ?, ?, 'active', NOW(), ?)",
                [
                    $businessId, $data['contact_person'], $data['email'],
                    $data['phone'], $hash, $data['preferred_language'],
                ]
            );
            $userId = (int)$db->lastInsertId();

            // Assign business_owner role
            $role = $db->queryOne("SELECT id FROM roles WHERE slug = 'business_owner'");
            if ($role) {
                $db->execute(
                    "INSERT INTO user_roles (user_id, role_id, business_id) VALUES (?, ?, ?)",
                    [$userId, $role['id'], $businessId]
                );
            }

            // Create default business branding record
            $db->execute(
                "INSERT INTO business_branding (business_id) VALUES (?)",
                [$businessId]
            );

            $db->commit();

            // Log in the new user
            $user = $db->queryOne("SELECT * FROM users WHERE id = ?", [$userId]);
            unset($user['password']);
            Session::regenerate();
            Session::set('user', $user);
            Permission::load($userId, $businessId);
            Tenant::resolve();

            auditLog('business_registered', 'business', $businessId, null, ['name' => $data['company_name'], 'email' => $data['email']]);

            redirect(APP_URL . '/onboarding');

        } catch (Exception $e) {
            $db->rollback();
            error_log('Registration failed: ' . $e->getMessage());
            Session::flash('errors', ['general' => 'Registration failed. Please try again.']);
            Session::flash('old', $data);
            redirect(APP_URL . '/register');
        }
    }

    private static function validate(array $data): array {
        $errors = [];
        if (empty($data['company_name']))   $errors['company_name'] = 'Company name is required.';
        if (empty($data['contact_person'])) $errors['contact_person'] = 'Contact person is required.';
        if (empty($data['email']))          $errors['email'] = 'Email is required.';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format.';
        if (empty($data['phone']))          $errors['phone'] = 'Phone is required.';
        if (empty($data['country']))        $errors['country'] = 'Country is required.';
        if (empty($data['password']))       $errors['password'] = 'Password is required.';
        elseif (strlen($data['password']) < 8) $errors['password'] = 'Password must be at least 8 characters.';
        elseif ($data['password'] !== $data['confirm_password']) $errors['confirm_password'] = 'Passwords do not match.';
        if (empty($_POST['terms']))         $errors['terms'] = 'You must agree to the Terms of Service.';
        return $errors;
    }
}
