<?php
class Session {
    private static bool $started = false;

    public static function start(): void {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => SESSION_SECURE,
            'httponly' => SESSION_HTTPONLY,
            'samesite' => 'Strict',
        ]);
        session_start();
        self::$started = true;
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        if ($key === '_csrf_token' && empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function regenerate(bool $deleteOld = true): void {
        if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id($deleteOld);
        }
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            @setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
        self::$started = false;
    }

    public static function flash(string $key, mixed $value = null): mixed {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function all(): array {
        return $_SESSION;
    }
}
