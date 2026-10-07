<?php
function auth(): ?array {
    return Auth::user();
}

function isLoggedIn(): bool {
    return Auth::check();
}

function currentUser(): ?array {
    return Auth::user();
}

function currentUserId(): ?int {
    return Auth::id();
}

function currentBusinessId(): ?int {
    return Tenant::id();
}

function requireAuth(string $redirectTo = '/login'): void {
    if (!Auth::check()) {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        Session::flash('redirect_after_login', $uri);
        redirect(APP_URL . $redirectTo);
    }
}

function requireParticipantAuth(string $redirectTo = '/participant/login'): void {
    if (!Auth::isParticipant()) {
        redirect(APP_URL . $redirectTo);
    }
}

function requirePermission(string $permission): void {
    requireAuth();
    Permission::requirePermission($permission);
}

function requirePlatformAdmin(): void {
    requireAuth();
    if (!Auth::isPlatformAdmin()) {
        http_response_code(403);
        if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . '/errors/403.php')) {
            require VIEWS_PATH . '/errors/403.php';
        } else {
            die('<h3>403 Forbidden</h3><p>Platform admin access required.</p>');
        }
        exit;
    }
}

function requireBusinessUser(): void {
    requireAuth();
    if (!Auth::isBusinessUser()) {
        http_response_code(403);
        if (defined('VIEWS_PATH') && file_exists(VIEWS_PATH . '/errors/403.php')) {
            require VIEWS_PATH . '/errors/403.php';
        } else {
            die('<h3>403 Forbidden</h3><p>Business account required.</p>');
        }
        exit;
    }
}

function generateCsrfToken(): string {
    if (!Session::has('_csrf_token')) {
        Session::set('_csrf_token', bin2hex(random_bytes(32)));
    }
    return Session::get('_csrf_token');
}

function csrfToken(): string {
    return generateCsrfToken();
}

function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function validateCsrf(): bool {
    $token   = $_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $session = Session::get('_csrf_token', '');
    return !empty($token) && !empty($session) && hash_equals($session, $token);
}

function requireCsrf(): void {
    if (!validateCsrf()) {
        http_response_code(419);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.', 'error_code' => 'CSRF_MISMATCH']);
        } else {
            die('<h3>419 - CSRF Token Mismatch</h3><p>Your session may have expired. Please go back and try again.</p>');
        }
        exit;
    }
}

function redirect(string $url): void {
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
        $path = '/' . ltrim($url, '/');
        $base = parse_url(APP_URL, PHP_URL_PATH) ?: '';
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $url = APP_URL . $path;
    }
    // Prevent accidental duplicate base path like /workshopos/workshopos
    $url = preg_replace('#(/workshopos)+/workshopos/#', '/workshopos/', $url);
    header('Location: ' . $url);
    exit;
}

function jsonResponse(bool $success, string $message, array $data = [], int $statusCode = 200, string $errorCode = ''): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    $response = ['success' => $success, 'message' => $message];
    if (!empty($data)) $response['data'] = $data;
    if (!$success && $errorCode) $response['error_code'] = $errorCode;
    echo json_encode($response);
    exit;
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): void {
        extract($data);
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($file)) {
            http_response_code(500);
            error_log("View not found: {$file}");
            die('<h3>500 - View Not Found</h3><p>Template: ' . htmlspecialchars($template) . '</p>');
        }
        require $file;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
