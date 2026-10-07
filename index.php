<?php
// C:\xampp\htdocs\workshopos\index.php

define('ROOT_PATH', __DIR__);

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
$router = new Router('/workshopos');

// Register routes
require_once ROOT_PATH . '/routes.php';

// Dispatch request
$router->dispatch();
