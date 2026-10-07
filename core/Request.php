<?php
// C:\xampp\htdocs\workshopos\core\Request.php

require_once __DIR__ . '/Session.php';

class Request {
    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function path() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = '/workshopos';
        if (strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        return $uri ?: '/';
    }

    public static function get($key, $default = null) {
        return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key]), ENT_QUOTES, 'UTF-8') : $default;
    }

    public static function post($key, $default = null) {
        return isset($_POST[$key]) ? htmlspecialchars(trim($_POST[$key]), ENT_QUOTES, 'UTF-8') : $default;
    }

    public static function file($key) {
        return $_FILES[$key] ?? null;
    }

    public static function isPost() {
        return self::method() === 'POST';
    }

    public static function isGet() {
        return self::method() === 'GET';
    }

    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public static function ip() {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public static function userAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function validateCsrf() {
        if (self::isPost()) {
            $token = $_POST['csrf_token'] ?? '';
            $sessionToken = Session::get('csrf_token');
            
            if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
                die("Invalid CSRF token.");
            }
        }
        return true;
    }

    public static function input($key, $default = null) {
        if (isset($_POST[$key])) return self::post($key, $default);
        if (isset($_GET[$key])) return self::get($key, $default);
        return $default;
    }
}
