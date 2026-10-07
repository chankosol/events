<?php
// Prevent direct access
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Load .env file
$envFile = ROOT_PATH . '/config/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        // Remove surrounding quotes
        if (strlen($value) > 1 && 
            ((($value[0] === '"') && $value[-1] === '"') ||
             (($value[0] === "'") && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}

// Set timezone
$timezone = getenv('APP_TIMEZONE') ?: 'Asia/Phnom_Penh';
date_default_timezone_set($timezone);

// Application constants
$httpHost = $_SERVER['HTTP_HOST'] ?? null;
if (!empty($httpHost)) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $appUrl = "{$scheme}://{$httpHost}/workshopos";
} else {
    $appUrl = getenv('APP_URL') ?: 'http://localhost/workshopos';
}
define('APP_NAME',      getenv('APP_NAME')      ?: 'Workshop OS');
define('APP_URL',       rtrim($appUrl, '/'));
define('APP_ENV',       getenv('APP_ENV')       ?: 'development');
define('APP_DEBUG',     filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('APP_KEY',       getenv('APP_KEY')       ?: '');
define('APP_TIMEZONE',  $timezone);
define('APP_VERSION',   '1.0.0');

// Paths
define('UPLOAD_PATH',   ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL',    APP_URL . '/uploads');
define('VIEWS_PATH',    ROOT_PATH . DIRECTORY_SEPARATOR . 'views');
define('ASSETS_URL',    APP_URL . '/assets');
define('UPLOAD_MAX_SIZE', (int)(getenv('UPLOAD_MAX_SIZE') ?: 10485760));

// Session
define('SESSION_NAME',      getenv('SESSION_NAME')      ?: 'workshopos_session');
define('SESSION_LIFETIME',  (int)(getenv('SESSION_LIFETIME') ?: 7200));
define('SESSION_SECURE',    filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN));
define('SESSION_HTTPONLY',  filter_var(getenv('SESSION_HTTPONLY') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// Error handling based on environment
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Simple autoloader for core classes, helpers, and models
spl_autoload_register(function($class) {
    $dirs = [
        ROOT_PATH . '/core/',
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load all helpers
$helpers = glob(ROOT_PATH . '/helpers/*.php');
foreach ($helpers as $helper) {
    require_once $helper;
}

// Ensure logs and uploads directories exist
$dirs = [
    ROOT_PATH . '/logs',
    ROOT_PATH . '/uploads',
    ROOT_PATH . '/uploads/temp',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
