<?php
// C:\xampp\htdocs\workshopos\routes.php

/** @var Router $router */

// ============================================================
// PUBLIC ROUTES
// ============================================================
$router->get('/', function() {
    require_once ROOT_PATH . '/controllers/public/HomeController.php';
    HomeController::index();
});

$router->get('/event', function() {
    redirect(APP_URL . '/');
});

$router->get('/event/{slug}', function($slug) {
    require_once ROOT_PATH . '/controllers/public/WorkshopController.php';
    PublicWorkshopController::show($slug);
});

$router->get('/event/{slug}/register', function($slug) {
    require_once ROOT_PATH . '/controllers/public/RegisterController.php';
    PublicRegisterController::show($slug);
});

$router->post('/event/{slug}/register', function($slug) {
    require_once ROOT_PATH . '/controllers/public/RegisterController.php';
    PublicRegisterController::submit($slug);
});

$router->get('/register/{slug}', function($slug) {
    require_once ROOT_PATH . '/controllers/public/RegisterController.php';
    PublicRegisterController::show($slug);
});

$router->post('/register/{slug}', function($slug) {
    require_once ROOT_PATH . '/controllers/public/RegisterController.php';
    PublicRegisterController::submit($slug);
});

$router->get('/event/{slug}/confirmation', function($slug) {
    $lastReg = Session::get('last_registration');
    $title = 'Registration Confirmation - ' . APP_NAME;
    ob_start();
    require VIEWS_PATH . '/public/confirmation.php';
    $content = ob_get_clean();
    require VIEWS_PATH . '/layouts/public.php';
});

$router->get('/certificate/verify/{token}', function($token) {
    require_once ROOT_PATH . '/controllers/public/CertificateVerifyController.php';
    CertificateVerifyController::show($token);
});

// Public Delegation Self-Service Portal for Provincial Heads
$router->get('/delegation/{token}', function($token) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::portal($token);
});

$router->post('/delegation/{token}', function($token) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::portal($token);
});

// ============================================================
// AUTH ROUTES (BUSINESS / HOST)
// ============================================================
$router->get('/login', function() {
    require_once ROOT_PATH . '/controllers/auth/LoginController.php';
    LoginController::showForm();
});

$router->post('/login', function() {
    require_once ROOT_PATH . '/controllers/auth/LoginController.php';
    LoginController::login();
});

$router->get('/logout', function() {
    require_once ROOT_PATH . '/controllers/auth/LoginController.php';
    LoginController::logout();
});

$router->get('/register', function() {
    require_once ROOT_PATH . '/controllers/auth/RegisterController.php';
    RegisterController::showForm();
});

$router->post('/register', function() {
    require_once ROOT_PATH . '/controllers/auth/RegisterController.php';
    RegisterController::register();
});

$router->get('/forgot-password', function() {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::showForgotForm();
});

$router->post('/forgot-password', function() {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::sendReset();
});

$router->get('/reset-password', function() {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::showResetForm();
});

$router->post('/reset-password', function() {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::resetPassword();
});

$router->get('/reset-password/{token}', function($token) {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::showResetForm($token);
});

$router->post('/reset-password/{token}', function($token) {
    require_once ROOT_PATH . '/controllers/auth/PasswordController.php';
    PasswordController::resetPassword($token);
});

// ============================================================
// PARTICIPANT PORTAL ROUTES
// ============================================================
$router->get('/participant/login', function() {
    require_once ROOT_PATH . '/controllers/participant/AuthController.php';
    ParticipantAuthController::showLogin();
});

$router->post('/participant/login', function() {
    require_once ROOT_PATH . '/controllers/participant/AuthController.php';
    ParticipantAuthController::login();
});

$router->get('/participant/logout', function() {
    require_once ROOT_PATH . '/controllers/participant/AuthController.php';
    ParticipantAuthController::logout();
});

$router->get('/participant/portal', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::dashboard();
});

$router->get('/participant/workshops', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::myWorkshops();
});

$router->get('/participant/portal/workshops', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::myWorkshops();
});

$router->get('/participant/workshop/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::workshopDetail((int)$id);
});

$router->get('/participant/portal/workshop/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::workshopDetail((int)$id);
});

$router->get('/participant/profile', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::profile();
});

$router->get('/participant/portal/profile', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::profile();
});

$router->post('/participant/profile', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::updateProfile();
});

$router->post('/participant/portal/profile', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::updateProfile();
});

$router->get('/participant/certificate/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::certificate((int)$id);
});

$router->post('/participant/upload-proof', function() {
    require_once ROOT_PATH . '/controllers/participant/PortalController.php';
    ParticipantPortalController::uploadProof();
});

// ============================================================
// BUSINESS DASHBOARD & WORKSHOPS
// ============================================================
$router->get('/dashboard', function() {
    require_once ROOT_PATH . '/controllers/business/DashboardController.php';
    BusinessDashboardController::index();
});

$router->get('/onboarding', function() {
    require_once ROOT_PATH . '/controllers/business/OnboardingController.php';
    OnboardingController::index();
});

$router->post('/onboarding/step/{step}', function($step) {
    require_once ROOT_PATH . '/controllers/business/OnboardingController.php';
    OnboardingController::saveStep((int)$step);
});

// Workshops CRUD & Management
$router->get('/workshops', function() {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::index();
});

$router->get('/workshops/create', function() {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::create();
});

$router->post('/workshops/create', function() {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::store();
});

$router->get('/workshops/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::show((int)$id);
});

$router->get('/workshops/{id}/edit', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::edit((int)$id);
});

$router->post('/workshops/{id}/edit', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::update((int)$id);
});

$router->get('/workshops/{id}/activate', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::activate((int)$id);
});

$router->post('/workshops/{id}/activate', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::activate((int)$id);
});

$router->post('/workshops/{id}/activate/confirm-bakong', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::confirmBakong((int)$id);
});

$router->get('/workshops/{id}/activate/check-status', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::checkPaymentStatus((int)$id);
});

$router->post('/workshops/{id}/publish', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::publish((int)$id);
});

$router->post('/workshops/{id}/duplicate', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::duplicate((int)$id);
});

$router->post('/workshops/{id}/upgrade-capacity', function($id) {
    require_once ROOT_PATH . '/controllers/business/WorkshopController.php';
    WorkshopController::upgradeCapacity((int)$id);
});

// Registrations
$router->get('/workshops/{id}/registrations', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::index((int)$id);
});

$router->get('/workshops/{id}/registrations/export', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::export((int)$id);
});

$router->get('/workshops/{id}/registrations/sample-csv', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::sampleCsv((int)$id);
});

$router->post('/workshops/{id}/registrations/import', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::import((int)$id);
});

$router->post('/workshops/{id}/registrations', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::store((int)$id);
});

$router->post('/workshops/{id}/registrations/{regId}/approve', function($id, $regId) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::approve((int)$id, (int)$regId);
});

$router->post('/workshops/{id}/registrations/{regId}/reject', function($id, $regId) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::reject((int)$id, (int)$regId);
});

$router->post('/workshops/{id}/registrations/bulk-approve', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::bulkApprove((int)$id);
});

$router->post('/workshops/{id}/form-fields', function($id) {
    require_once ROOT_PATH . '/controllers/business/RegistrationController.php';
    RegistrationController::saveFormFields((int)$id);
});

// Payments
$router->get('/payments', function() {
    requireAuth();
    $db = Database::getInstance();
    $bizId = Tenant::id();
    $ws = $db->queryOne("SELECT id FROM workshops WHERE business_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1", [$bizId]);
    if ($ws) {
        redirect(APP_URL . '/workshops/' . $ws['id'] . '/payments');
    } else {
        redirect(APP_URL . '/workshops');
    }
});

$router->get('/workshops/{id}/payments', function($id) {
    require_once ROOT_PATH . '/controllers/business/PaymentController.php';
    PaymentController::index((int)$id);
});

$router->post('/payments/{proofId}/approve', function($proofId) {
    require_once ROOT_PATH . '/controllers/business/PaymentController.php';
    PaymentController::approveProof((int)$proofId);
});

$router->post('/payments/{proofId}/reject', function($proofId) {
    require_once ROOT_PATH . '/controllers/business/PaymentController.php';
    PaymentController::rejectProof((int)$proofId);
});

// Check-in & Live Mode
$router->get('/checkin/{workshopId}', function($workshopId) {
    require_once ROOT_PATH . '/controllers/business/CheckinController.php';
    CheckinController::index((int)$workshopId);
});

$router->get('/live/{workshopId}', function($workshopId) {
    require_once ROOT_PATH . '/controllers/business/LiveController.php';
    LiveController::index((int)$workshopId);
});

// ============================================================
// DELEGATIONS (25 PROVINCES & QUOTAS) & MEMBER SUBSTITUTION
// ============================================================
$router->get('/workshops/{id}/delegations', function($id) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::index((int)$id);
});

$router->post('/workshops/{id}/delegations/auto-generate', function($id) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::autoGenerate((int)$id);
});

$router->post('/workshops/{id}/delegations/store', function($id) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::store((int)$id);
});

$router->post('/workshops/{id}/delegations/{delId}/update', function($id, $delId) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::update((int)$id, (int)$delId);
});

$router->post('/workshops/{id}/delegations/{delId}/delete', function($id, $delId) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::delete((int)$id, (int)$delId);
});

$router->post('/workshops/{id}/delegations/substitute', function($id) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::substitute((int)$id);
});

$router->post('/workshops/{id}/delegations/form-fields', function($id) {
    require_once ROOT_PATH . '/controllers/business/DelegationController.php';
    DelegationController::saveFormFields((int)$id);
});

// ============================================================
// ALLOWANCES & PER DIEM DISBURSEMENT (ANTI-DOUBLE PAYOUT)
// ============================================================
$router->get('/workshops/{id}/allowances', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::index((int)$id);
});

$router->post('/workshops/{id}/allowances/config', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::saveConfig((int)$id);
});

$router->get('/workshops/{id}/allowances/desk', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::desk((int)$id);
});

$router->get('/api/workshops/{id}/allowances/check', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::checkAttendee((int)$id);
});

$router->post('/workshops/{id}/allowances/disburse', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::disburse((int)$id);
});

$router->post('/workshops/{id}/allowances/disburse-delegation', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::disburseDelegation((int)$id);
});

$router->get('/workshops/{id}/allowances/sheet', function($id) {
    require_once ROOT_PATH . '/controllers/business/AllowanceController.php';
    AllowanceController::sheet((int)$id);
});

// Business Platform Billing
$router->get('/billing', function() {
    require_once ROOT_PATH . '/controllers/business/BillingController.php';
    BusinessBillingController::index();
});

$router->post('/billing/{billingId}/upload-proof', function($billingId) {
    require_once ROOT_PATH . '/controllers/business/BillingController.php';
    BusinessBillingController::uploadProof((int)$billingId);
});

// Settings & Staff
$router->get('/settings', function() {
    require_once ROOT_PATH . '/controllers/business/SettingsController.php';
    BusinessSettingsController::index();
});

$router->post('/settings/general', function() {
    require_once ROOT_PATH . '/controllers/business/SettingsController.php';
    BusinessSettingsController::saveGeneral();
});

$router->post('/settings/branding', function() {
    require_once ROOT_PATH . '/controllers/business/SettingsController.php';
    BusinessSettingsController::saveBranding();
});

$router->post('/settings/payment-methods', function() {
    require_once ROOT_PATH . '/controllers/business/SettingsController.php';
    BusinessSettingsController::savePaymentMethods();
});

$router->get('/staff', function() {
    require_once ROOT_PATH . '/controllers/business/StaffController.php';
    StaffController::index();
});

$router->post('/staff/invite', function() {
    require_once ROOT_PATH . '/controllers/business/StaffController.php';
    StaffController::invite();
});

$router->post('/staff/{userId}/remove', function($userId) {
    require_once ROOT_PATH . '/controllers/business/StaffController.php';
    StaffController::remove((int)$userId);
});

$router->post('/staff/{userId}/edit', function($userId) {
    require_once ROOT_PATH . '/controllers/business/StaffController.php';
    StaffController::update((int)$userId);
});

// User Profile Management (Global / Host / Platform)
$router->get('/profile', function() {
    require_once ROOT_PATH . '/controllers/ProfileController.php';
    ProfileController::index();
});

$router->post('/profile', function() {
    require_once ROOT_PATH . '/controllers/ProfileController.php';
    ProfileController::update();
});

// Reports
$router->get('/reports', function() {
    require_once ROOT_PATH . '/controllers/business/ReportController.php';
    ReportController::index();
});

$router->get('/reports/workshop/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/business/ReportController.php';
    ReportController::workshop((int)$id);
});

// Certificates
$router->get('/workshops/{id}/certificates', function($id) {
    require_once ROOT_PATH . '/controllers/business/CertificateController.php';
    CertificateController::index((int)$id);
});

$router->post('/workshops/{id}/certificates/issue', function($id) {
    require_once ROOT_PATH . '/controllers/business/CertificateController.php';
    CertificateController::issue((int)$id);
});

// Gifts
$router->get('/workshops/{id}/gifts', function($id) {
    require_once ROOT_PATH . '/controllers/business/GiftController.php';
    GiftController::index((int)$id);
});

// ============================================================
// PLATFORM SUPER ADMIN ROUTES
// ============================================================
$router->get('/platform', function() {
    require_once ROOT_PATH . '/controllers/platform/DashboardController.php';
    PlatformDashboardController::index();
});

$router->get('/platform/businesses', function() {
    require_once ROOT_PATH . '/controllers/platform/BusinessController.php';
    PlatformBusinessController::index();
});

$router->get('/platform/businesses/{id}', function($id) {
    require_once ROOT_PATH . '/controllers/platform/BusinessController.php';
    PlatformBusinessController::show((int)$id);
});

$router->get('/platform/workshops', function() {
    require_once ROOT_PATH . '/controllers/platform/WorkshopController.php';
    PlatformWorkshopController::index();
});

$router->post('/platform/workshops/{id}/approve', function($id) {
    require_once ROOT_PATH . '/controllers/platform/WorkshopController.php';
    PlatformWorkshopController::approve((int)$id);
});

$router->get('/platform/pricing', function() {
    require_once ROOT_PATH . '/controllers/platform/PricingController.php';
    PlatformPricingController::index();
});

$router->post('/platform/pricing/create', function() {
    require_once ROOT_PATH . '/controllers/platform/PricingController.php';
    PlatformPricingController::create();
});

$router->post('/platform/pricing/{id}/edit', function($id) {
    require_once ROOT_PATH . '/controllers/platform/PricingController.php';
    PlatformPricingController::update((int)$id);
});

$router->post('/platform/pricing/{id}/toggle', function($id) {
    require_once ROOT_PATH . '/controllers/platform/PricingController.php';
    PlatformPricingController::toggle((int)$id);
});

$router->get('/platform/billing', function() {
    require_once ROOT_PATH . '/controllers/platform/BillingController.php';
    PlatformBillingController::index();
});

$router->post('/platform/billing/{proofId}/approve', function($proofId) {
    require_once ROOT_PATH . '/controllers/platform/BillingController.php';
    PlatformBillingController::approveProof((int)$proofId);
});

$router->post('/platform/billing/{proofId}/reject', function($proofId) {
    require_once ROOT_PATH . '/controllers/platform/BillingController.php';
    PlatformBillingController::rejectProof((int)$proofId);
});

$router->get('/platform/invoices', function() {
    require_once ROOT_PATH . '/controllers/platform/InvoiceController.php';
    PlatformInvoiceController::index();
});

$router->get('/platform/revenue', function() {
    require_once ROOT_PATH . '/controllers/platform/RevenueController.php';
    PlatformRevenueController::index();
});

$router->get('/platform/settings', function() {
    require_once ROOT_PATH . '/controllers/platform/SettingsController.php';
    PlatformSettingsController::index();
});

$router->post('/platform/settings', function() {
    require_once ROOT_PATH . '/controllers/platform/SettingsController.php';
    PlatformSettingsController::save();
});

$router->post('/platform/settings/test-bakong', function() {
    require_once ROOT_PATH . '/controllers/platform/SettingsController.php';
    PlatformSettingsController::testBakong();
});

$router->get('/platform/audit-logs', function() {
    require_once ROOT_PATH . '/controllers/platform/AuditController.php';
    PlatformAuditController::index();
});

$router->get('/platform/audit', function() {
    require_once ROOT_PATH . '/controllers/platform/AuditController.php';
    PlatformAuditController::index();
});

$router->get('/platform/users', function() {
    require_once ROOT_PATH . '/controllers/platform/UserController.php';
    PlatformUserController::index();
});

$router->post('/platform/users/{id}/toggle', function($id) {
    require_once ROOT_PATH . '/controllers/platform/UserController.php';
    PlatformUserController::toggle((int)$id);
});

$router->post('/platform/users/{id}/role', function($id) {
    require_once ROOT_PATH . '/controllers/platform/UserController.php';
    PlatformUserController::updateRole((int)$id);
});

$router->get('/platform/roles', function() {
    require_once ROOT_PATH . '/controllers/platform/RoleController.php';
    PlatformRoleController::index();
});

$router->post('/platform/roles/{id}/permissions', function($id) {
    require_once ROOT_PATH . '/controllers/platform/RoleController.php';
    PlatformRoleController::updateRolePermissions((int)$id);
});

$router->post('/platform/roles/toggle-permission', function() {
    require_once ROOT_PATH . '/controllers/platform/RoleController.php';
    PlatformRoleController::togglePermission();
});

$router->get('/platform/reports', function() {
    require_once ROOT_PATH . '/controllers/platform/ReportController.php';
    PlatformReportController::index();
});

$router->get('/platform/reports/export', function() {
    require_once ROOT_PATH . '/controllers/platform/ReportController.php';
    PlatformReportController::export();
});

$router->get('/platform/ai', function() {
    require_once ROOT_PATH . '/controllers/platform/AiController.php';
    PlatformAiController::index();
});

$router->post('/platform/ai', function() {
    require_once ROOT_PATH . '/controllers/platform/AiController.php';
    PlatformAiController::save();
});

$router->post('/platform/ai/test', function() {
    require_once ROOT_PATH . '/controllers/platform/AiController.php';
    PlatformAiController::testConnection();
});

// ============================================================
// AJAX / REST API ROUTES
// ============================================================
$router->post('/api/checkin/scan', function() {
    require_once ROOT_PATH . '/api/checkin/scan.php';
});

$router->post('/api/checkin/scan.php', function() {
    require_once ROOT_PATH . '/api/checkin/scan.php';
});

$router->post('/api/checkin/search', function() {
    require_once ROOT_PATH . '/api/checkin/search.php';
});

$router->post('/api/checkin/offline-sync', function() {
    require_once ROOT_PATH . '/api/checkin/offline_sync.php';
});

$router->get('/api/workshop/{id}/stats', function($id) {
    require_once ROOT_PATH . '/api/workshops/stats.php';
    api_workshop_stats((int)$id);
});

$router->get('/api/workshop/{id}/participants', function($id) {
    require_once ROOT_PATH . '/api/workshops/participants.php';
    api_workshop_participants((int)$id);
});

$router->post('/api/questions/submit', function() {
    require_once ROOT_PATH . '/api/questions/submit.php';
});

$router->post('/api/questions/{id}/moderate', function($id) {
    require_once ROOT_PATH . '/api/questions/moderate.php';
    api_moderate_question((int)$id);
});

$router->post('/api/polls/{id}/respond', function($id) {
    require_once ROOT_PATH . '/api/polls/respond.php';
    api_poll_respond((int)$id);
});

$router->post('/api/requests/submit', function() {
    require_once ROOT_PATH . '/api/requests/submit.php';
});

$router->get('/api/pricing/calculate', function() {
    require_once ROOT_PATH . '/api/billing/calculate.php';
    api_calculate_pricing();
});

$router->post('/api/gift/distribute', function() {
    require_once ROOT_PATH . '/api/gifts/distribute.php';
});

$router->get('/api/certificates/check-eligibility/{regId}', function($regId) {
    require_once ROOT_PATH . '/api/certificates/eligibility.php';
    api_check_eligibility((int)$regId);
});
