<?php
// C:\xampp\htdocs\workshopos\core\Response.php

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        
        $output = [
            'success' => $status >= 200 && $status < 300,
            'message' => $data['message'] ?? '',
            'data' => $data['data'] ?? [],
        ];
        
        if (isset($data['error_code'])) {
            $output['error_code'] = $data['error_code'];
        }

        echo json_encode($output);
        exit;
    }

    public static function redirect($url) {
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = APP_URL . '/' . ltrim($url, '/');
        }
        header("Location: $url");
        exit;
    }

    public static function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        header("Location: $referer");
        exit;
    }

    public static function view($template, $data = []) {
        extract($data);
        $file = __DIR__ . '/../views/' . $template . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die("View $template not found.");
        }
    }

    public static function error($code, $message) {
        http_response_code($code);
        if (Request::isAjax()) {
            self::json(['message' => $message], $code);
        } else {
            die("Error $code: " . htmlspecialchars($message));
        }
    }
}
