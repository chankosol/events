<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>រកមិនឃើញទំព័រ (404) - <?= defined('APP_NAME') ? APP_NAME : 'Workshop OS' ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body, html, .btn {
    font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
  }
</style>
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container text-center py-5">
  <div class="display-1 fw-bold text-primary mb-3"><?= isset($customMessage) ? '<i class="bi bi-clock-history"></i>' : '404' ?></div>
  <h2 class="fw-bold mb-3"><?= isset($customMessage) ? 'មិនទាន់បើកដំណើរការទេ' : 'រកមិនឃើញទំព័រដែលអ្នកស្នើសុំទេ' ?></h2>
  <p class="text-muted mb-4 lead"><?= $customMessage ?? 'ទំព័រដែលអ្នកកំពុងស្វែងរកអាចត្រូវបានផ្លាស់ប្តូរ ឬមិនមាននៅក្នុងប្រព័ន្ធឡើយ។' ?></p>
  <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-primary px-4 py-2">
    <i class="bi bi-house-door me-2"></i>ត្រឡប់ទៅទំព័រដើម
  </a>
</div>
</body>
</html>
