<?php
// Layout: views/layouts/business.php
// Variables expected: $title, $content
$appName = defined('APP_NAME') ? APP_NAME : 'Workshop OS';
$appUrl  = defined('APP_URL')  ? APP_URL  : '';
$user = Auth::user();
$business = Tenant::getBusiness();
$bizBranding = ($business && !empty($business['id'])) ? Database::getInstance()->queryOne("SELECT logo_path FROM business_branding WHERE business_id = ?", [$business['id']]) : null;
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo csrfToken(); ?>">
<title><?php echo htmlspecialchars($title ?? 'ផ្ទាំងគ្រប់គ្រង - Workshop OS', ENT_QUOTES); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?php echo $appUrl; ?>/assets/css/app.css" rel="stylesheet">
<style>
  body, html, input, button, select, textarea, .btn, .nav-link, .sidebar, .topbar {
    font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
  }
  .breadcrumb-item + .breadcrumb-item::before {
    content: "›" !important;
    font-size: 1.25rem !important;
    line-height: 1 !important;
    vertical-align: middle !important;
    color: #64748b !important;
    font-weight: bold !important;
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
  }
  .breadcrumb-item a {
    color: #0d6efd !important;
  }
  .breadcrumb-item a:hover {
    color: #0a58ca !important;
    text-decoration: underline !important;
  }
  .breadcrumb-item.active {
    color: #1e293b !important;
    font-weight: 600 !important;
  }
</style>
</head>
<body>

<div class="layout-with-sidebar">
    <!-- Sidebar in Khmer -->
    <aside class="sidebar" id="sidebar">
        <!-- Brand Header with Business Logo -->
        <a href="<?php echo $appUrl; ?>/dashboard" class="sidebar-brand text-truncate d-flex align-items-center text-decoration-none">
            <?php 
                $bizLogo = $bizBranding['logo_path'] ?? '';
                echo businessLogoHtml(['name' => $business['name'] ?? $appName, 'logo_path' => $bizLogo], 32, 'me-2');
            ?>
            <span class="text-truncate"><?php echo htmlspecialchars($business['name'] ?? $appName); ?></span>
        </a>

        <!-- Scrollable Navigation Menu -->
        <nav class="sidebar-nav">
            <a href="<?php echo $appUrl; ?>/dashboard" class="nav-link <?php echo navActive('/dashboard', true); ?>">
                <i class="bi bi-speedometer2"></i> ផ្ទាំងគ្រប់គ្រង
            </a>
            
            <div class="nav-label">ការគ្រប់គ្រងកម្មវិធី</div>
            <?php if (Permission::has('workshop.view')): ?>
                <a href="<?php echo $appUrl; ?>/workshops" class="nav-link <?php echo navActive(['/workshops', '/checkin', '/live'], ['/workshops/create']); ?>">
                    <i class="bi bi-collection-play"></i> សិក្ខាសាលាទាំងអស់
                </a>
            <?php endif; ?>
            <?php if (Permission::has('workshop.create')): ?>
                <a href="<?php echo $appUrl; ?>/workshops/create" class="nav-link <?php echo navActive('/workshops/create', true); ?>">
                    <i class="bi bi-plus-circle"></i> បង្កើតសិក្ខាសាលាថ្មី
                </a>
            <?php endif; ?>
            
            <div class="nav-label">ហិរញ្ញវត្ថុ និងវិក្កយបត្រ</div>
            <?php if (Permission::has('billing.view')): ?>
                <a href="<?php echo $appUrl; ?>/billing" class="nav-link <?php echo navActive('/billing'); ?>">
                    <i class="bi bi-receipt"></i> ថ្លៃសេវាប្រព័ន្ធ (System A)
                </a>
            <?php endif; ?>

            <div class="nav-label">របាយការណ៍ និងវិញ្ញាបនបត្រ</div>
            <?php if (Permission::has('report.view')): ?>
                <a href="<?php echo $appUrl; ?>/reports" class="nav-link <?php echo navActive('/reports'); ?>">
                    <i class="bi bi-graph-up"></i> របាយការណ៍សរុប
                </a>
            <?php endif; ?>

            <div class="nav-label">ការកំណត់ និងក្រុមការងារ</div>
            <?php if (Permission::has('staff.view')): ?>
                <a href="<?php echo $appUrl; ?>/staff" class="nav-link <?php echo navActive('/staff'); ?>">
                    <i class="bi bi-people"></i> ក្រុមការងារ និងសិទ្ធិ
                </a>
            <?php endif; ?>
            <?php if (Permission::has('business.settings')): ?>
                <a href="<?php echo $appUrl; ?>/settings" class="nav-link <?php echo navActive('/settings'); ?>">
                    <i class="bi bi-sliders"></i> ការកំណត់ និង QR ទទួលប្រាក់
                </a>
            <?php endif; ?>
        </nav>

        <!-- Profile and Name of Logged-In User at Bottom of Left Menu -->
        <div class="sidebar-footer">
            <div class="dropup w-100">
                <a href="#" class="sidebar-user-card d-flex align-items-center text-decoration-none dropdown-toggle w-100" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="me-2 flex-shrink-0">
                        <?php echo userAvatarHtml($user, 38); ?>
                    </div>
                    <div class="flex-grow-1 text-truncate text-start" style="min-width: 0;">
                        <div class="sidebar-user-name fw-bold text-truncate text-white" style="font-size: 0.85rem; line-height: 1.2;">
                            <?php echo htmlspecialchars($user['name'] ?? 'អ្នកប្រើប្រាស់'); ?>
                        </div>
                        <div class="sidebar-user-role text-truncate text-white-50" style="font-size: 0.72rem;">
                            <?php echo htmlspecialchars(userRoleTitle($user)); ?>
                        </div>
                    </div>
                    <i class="bi bi-three-dots-vertical text-white-50 ms-1 flex-shrink-0" style="font-size: 0.95rem;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary mb-2 w-100" style="font-size: 0.875rem;">
                    <li class="px-3 py-2 border-bottom border-secondary bg-dark-subtle">
                        <div class="fw-bold text-white text-truncate"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                    </li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/profile"><i class="bi bi-person-gear me-2 text-warning"></i> ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/settings"><i class="bi bi-gear me-2 text-primary"></i> ការកំណត់អាជីវកម្ម</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/staff"><i class="bi bi-people me-2 text-info"></i> សមាជិកក្រុមការងារ</a></li>
                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?php echo $appUrl; ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> ចាកចេញ (Logout)</a></li>
                </ul>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-sm btn-light d-md-none me-3" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <?php 
                    if (empty($breadcrumbs)) {
                        $breadcrumbs = buildBreadcrumbs($workshop ?? null, $title ?? null);
                    }
                ?>
                <?php if (!empty($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 align-items-center" style="font-size:0.85rem;">
                        <?php foreach ($breadcrumbs as $i => $crumb): ?>
                            <?php $isLast = ($i === count($breadcrumbs) - 1); ?>
                            <?php if ($isLast): ?>
                                <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">
                                    <?php if (!empty($crumb['icon'])): ?><i class="bi bi-<?= $crumb['icon'] ?> me-1 text-primary"></i><?php endif; ?>
                                    <?= htmlspecialchars($crumb['label']) ?>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= $crumb['url'] ?>" class="text-decoration-none text-primary fw-medium">
                                        <?php if (!empty($crumb['icon'])): ?><i class="bi bi-<?= $crumb['icon'] ?> me-1"></i><?php endif; ?>
                                        <?= htmlspecialchars($crumb['label']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo $appUrl; ?>/" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-globe me-1"></i> ទំព័រសាធារណៈ
                </a>
                <a href="<?php echo $appUrl; ?>/logout" class="btn btn-sm btn-outline-danger" title="ចាកចេញ">
                    <i class="bi bi-box-arrow-right me-1"></i> <span class="d-none d-sm-inline">ចាកចេញ</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="p-4 pb-0">
            <?php if ($msg = Session::flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($msg = Session::flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page Content -->
        <main class="page-content">
            <?php echo $content ?? ''; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $appUrl; ?>/assets/js/app.js"></script>
</body>
</html>
