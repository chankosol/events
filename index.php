<?php
// C:\xampp\htdocs\workshopos\index.php

define('ROOT_PATH', __DIR__);

// Handle CORS and preflight OPTIONS requests gracefully
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
    http_response_code(200);
    exit;
}

// Load configurations & core helpers
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';
require_once ROOT_PATH . '/core/Permission.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/Request.php';
require_once ROOT_PATH . '/core/Response.php';

// Initialize session
Session::start();

// Resolve multi-tenant business context if logged in
if (Auth::check()) {
    Tenant::resolve();
}

// Initialize Router
$router = new Router(defined('BASE_PATH') ? BASE_PATH : '');

// Register routes
require_once ROOT_PATH . '/routes.php';

// Dispatch request
$router->dispatch();
