<?php
// Layout: views/layouts/platform.php
// Variables expected: $title, $content
$appName = defined('APP_NAME') ? APP_NAME : 'Workshop OS';
$appUrl  = defined('APP_URL')  ? APP_URL  : '';
$user = Auth::user();
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo csrfToken(); ?>">
<title><?php echo htmlspecialchars($title ?? 'អ្នកគ្រប់គ្រងប្រព័ន្ធ - Workshop OS', ENT_QUOTES); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?php echo $appUrl; ?>/assets/css/app.css" rel="stylesheet">
<style>
  body, html, input, button, select, textarea, .btn, .nav-link, .sidebar-brand {
    font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
  }
</style>
</head>
<body>

<div class="layout-with-sidebar">
    <!-- Sidebar in Khmer -->
    <aside class="sidebar sidebar-platform" id="sidebar">
        <!-- Brand Header -->
        <a href="<?php echo $appUrl; ?>/platform" class="sidebar-brand text-danger">
            <i class="bi bi-shield-lock-fill me-2 fs-5"></i> 
            <span>អ្នកគ្រប់គ្រងប្រព័ន្ធ</span>
        </a>

        <!-- Scrollable Navigation Menu -->
        <nav class="sidebar-nav">
            <a href="<?php echo $appUrl; ?>/platform" class="nav-link <?php echo navActive('/platform', true); ?>">
                <i class="bi bi-speedometer2"></i> ផ្ទាំងព័ត៌មាន (Dashboard)
            </a>
            
            <div class="nav-label">ស្ថាប័ន / ក្រុមហ៊ុន (Tenants)</div>
            <a href="<?php echo $appUrl; ?>/platform/businesses" class="nav-link <?php echo navActive('/platform/businesses'); ?>">
                <i class="bi bi-building"></i> បញ្ជីស្ថាប័ន (Businesses)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/workshops" class="nav-link <?php echo navActive('/platform/workshops'); ?>">
                <i class="bi bi-collection"></i> សិក្ខាសាលាទាំងអស់
            </a>
            
            <div class="nav-label">ហិរញ្ញវត្ថុ & ចំណូល</div>
            <a href="<?php echo $appUrl; ?>/platform/billing" class="nav-link <?php echo navActive('/platform/billing'); ?>">
                <i class="bi bi-credit-card"></i> ការទូទាត់ថ្លៃប្រព័ន្ធ
            </a>
            <a href="<?php echo $appUrl; ?>/platform/pricing" class="nav-link <?php echo navActive('/platform/pricing'); ?>">
                <i class="bi bi-tags"></i> តារាងតម្លៃប្រព័ន្ធ (Pricing)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/invoices" class="nav-link <?php echo navActive('/platform/invoices'); ?>">
                <i class="bi bi-receipt"></i> វិក្កយបត្រ (Invoices)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/revenue" class="nav-link <?php echo navActive('/platform/revenue'); ?>">
                <i class="bi bi-graph-up-arrow"></i> ប្រាក់ចំណូលសរុប (Revenue)
            </a>
            
            <div class="nav-label">ការគ្រប់គ្រងប្រព័ន្ធ</div>
            <a href="<?php echo $appUrl; ?>/platform/users" class="nav-link <?php echo navActive('/platform/users'); ?>">
                <i class="bi bi-people"></i> អ្នកប្រើប្រាស់ (Users)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/roles" class="nav-link <?php echo navActive('/platform/roles'); ?>">
                <i class="bi bi-shield-check"></i> តួនាទី & សិទ្ធិ (Roles & Permissions)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/reports" class="nav-link <?php echo navActive('/platform/reports'); ?>">
                <i class="bi bi-pie-chart"></i> របាយការណ៍សកល
            </a>
            <a href="<?php echo $appUrl; ?>/platform/ai" class="nav-link <?php echo navActive('/platform/ai'); ?>">
                <i class="bi bi-robot"></i> ការគ្រប់គ្រង AI
            </a>
            <a href="<?php echo $appUrl; ?>/platform/settings" class="nav-link <?php echo navActive('/platform/settings'); ?>">
                <i class="bi bi-gear"></i> ការកំណត់ប្រព័ន្ធ (Settings)
            </a>
            <a href="<?php echo $appUrl; ?>/platform/audit-logs" class="nav-link <?php echo navActive('/platform/audit-logs'); ?>">
                <i class="bi bi-journal-text"></i> កំណត់ត្រាសវនកម្ម (Audit)
            </a>
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
                            <?php echo htmlspecialchars($user['name'] ?? 'Super Admin'); ?>
                        </div>
                        <div class="sidebar-user-role text-truncate text-white-50" style="font-size: 0.72rem;">
                            <?php echo htmlspecialchars(userRoleTitle($user)); ?>
                        </div>
                    </div>
                    <i class="bi bi-three-dots-vertical text-white-50 ms-1 flex-shrink-0" style="font-size: 0.95rem;"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-secondary mb-2 w-100" style="font-size: 0.875rem;">
                    <li class="px-3 py-2 border-bottom border-secondary bg-dark-subtle">
                        <div class="fw-bold text-white text-truncate"><?php echo htmlspecialchars($user['name'] ?? 'Super Admin'); ?></div>
                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($user['email'] ?? 'admin@workshopos.com'); ?></div>
                    </li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/profile"><i class="bi bi-person-gear me-2 text-warning"></i> ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/platform/settings"><i class="bi bi-gear me-2 text-danger"></i> ការកំណត់ប្រព័ន្ធ</a></li>
                    <li><a class="dropdown-item py-2" href="<?php echo $appUrl; ?>/platform/audit-logs"><i class="bi bi-journal-text me-2 text-info"></i> កំណត់ត្រាសវនកម្ម</a></li>
                    <li><hr class="dropdown-divider border-secondary my-1"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?php echo $appUrl; ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> ចាកចេញ (Logout)</a></li>
                </ul>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-md-none me-2" id="sidebarToggle">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center text-muted small">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 me-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> ប្រព័ន្ធគ្រប់គ្រងជាន់ខ្ពស់ (Super Admin)
                    </span>
                    <span class="d-none d-md-inline text-secondary">
                        <i class="bi bi-clock me-1"></i> <?php echo date('d M Y'); ?>
                    </span>
                </div>
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

        <!-- Page Content -->
        <div class="page-content">
            <?php if ($flashError = Session::flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($flashError); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($flashSuccess = Session::flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($flashSuccess); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php echo $content ?? ''; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $appUrl; ?>/assets/js/app.js"></script>
</body>
</html>
