<?php
// Layout: views/layouts/public.php
// Variables expected: $title, $content
$appName = defined('APP_NAME') ? APP_NAME : 'Workshop OS';
$appUrl  = defined('APP_URL')  ? APP_URL  : '';
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'Workshop OS', ENT_QUOTES); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?php echo $appUrl; ?>/assets/css/app.css" rel="stylesheet">
<style>
  body, html, input, button, select, textarea, .btn, .nav-link, .navbar-brand {
    font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
  }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo $appUrl; ?>">
      <i class="bi bi-calendar-event-fill me-2"></i><?php echo htmlspecialchars($appName); ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (Auth::check()): ?>
          <?php $u = Auth::user(); ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo Auth::isPlatformAdmin() ? $appUrl . '/platform' : $appUrl . '/dashboard'; ?>">
              <i class="bi bi-speedometer2 me-1"></i> ផ្ទាំងគ្រប់គ្រង
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger-subtle" href="<?php echo $appUrl; ?>/logout">
              <i class="bi bi-box-arrow-right me-1"></i> ចាកចេញ
            </a>
          </li>
        <?php elseif (Auth::isParticipant()): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $appUrl; ?>/participant/portal">
              <i class="bi bi-person-circle me-1"></i> គណនីសិក្ខាកាម
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $appUrl; ?>/participant/logout">
              <i class="bi bi-box-arrow-right me-1"></i> ចាកចេញ
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $appUrl; ?>/participant/login">
              <i class="bi bi-person me-1"></i> ចូលសម្រាប់សិក្ខាកាម
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $appUrl; ?>/login">
              <i class="bi bi-building me-1"></i> ចូលសម្រាប់អ្នករៀបចំ
            </a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-light btn-sm px-3 fw-bold shadow-sm" href="<?php echo $appUrl; ?>/register">
              <i class="bi bi-plus-circle me-1"></i> បង្កើតគណនីក្រុមហ៊ុន
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main>
  <?php echo $content ?? ''; ?>
</main>
<footer class="bg-dark text-light py-4 mt-5">
  <div class="container text-center">
    <p class="mb-1 fw-bold"><?php echo htmlspecialchars($appName); ?> &mdash; ប្រព័ន្ធគ្រប់គ្រងសិក្ខាសាលា និងវគ្គបណ្តុះបណ្តាលពេញលេញ</p>
    <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($appName); ?>. រក្សាសិទ្ធិគ្រប់យ៉ាង។</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $appUrl; ?>/assets/js/app.js"></script>
</body>
</html>
