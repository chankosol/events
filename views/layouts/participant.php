<?php
// Layout: views/layouts/participant.php
// Variables expected: $title, $content
$appName = defined('APP_NAME') ? APP_NAME : 'Workshop OS';
$appUrl  = defined('APP_URL')  ? APP_URL  : '';
$p = Auth::participant();
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'ផ្ទាំងសិក្ខាកាម', ENT_QUOTES); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body, html, input, button, select, textarea, .btn, .nav-link, .navbar-brand {
    font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
  }
  .bg-portal {
    background-color: #f8fafc;
  }
</style>
</head>
<body class="bg-portal d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $appUrl; ?>/participant/portal">
      <i class="bi bi-person-badge me-2"></i><?php echo htmlspecialchars($appName); ?> <span class="badge bg-light text-primary ms-1">សិក្ខាកាម</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#participantNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="participantNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $appUrl; ?>/participant/portal">
            <i class="bi bi-speedometer2 me-1"></i> ទំព័រដើម
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $appUrl; ?>/participant/workshops">
            <i class="bi bi-calendar2-check me-1"></i> សិក្ខាសាលារបស់ខ្ញុំ
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if ($p): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white fw-medium" href="#" id="pDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($p['name'] ?? 'សិក្ខាកាម'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li><a class="dropdown-item" href="<?php echo $appUrl; ?>/participant/profile"><i class="bi bi-gear me-2"></i>ព័ត៌មានផ្ទាល់ខ្លួន</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?php echo $appUrl; ?>/participant/logout"><i class="bi bi-box-arrow-right me-2"></i>ចាកចេញ</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-light btn-sm text-primary fw-medium" href="<?php echo $appUrl; ?>/login">ចូលប្រើប្រាស់</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="flex-grow-1">
  <?php if ($flash_success = Session::flash('success')): ?>
    <div class="container mt-3">
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($flash_success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($flash_error = Session::flash('error')): ?>
    <div class="container mt-3">
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($flash_error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  <?php endif; ?>

  <?php echo $content ?? ''; ?>
</main>

<footer class="bg-white border-top py-3 mt-auto">
  <div class="container text-center text-muted small">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. រក្សាសិទ្ធិគ្រប់យ៉ាង។
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
