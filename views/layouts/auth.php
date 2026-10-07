<?php
// Layout: views/layouts/auth.php
// Variables expected: $title, $content
$appName = defined('APP_NAME') ? APP_NAME : 'Workshop OS';
$appUrl  = defined('APP_URL')  ? APP_URL  : '';
?>
<!DOCTYPE html>
<html lang="km">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title ?? 'ចូលប្រព័ន្ធ - Workshop OS', ENT_QUOTES); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?php echo $appUrl; ?>/assets/css/app.css" rel="stylesheet">
<style>
    body, html, input, button, select, textarea, .btn, .nav-link {
        font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
    }
    body {
        background: linear-gradient(135deg, #0d6efd 0%, #0a4ebd 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .auth-card {
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border: none;
        overflow: hidden;
    }
    .auth-logo {
        text-align: center;
        margin-bottom: 20px;
        font-size: 1.8rem;
        font-weight: 700;
        color: #0d6efd;
        text-decoration: none;
    }
</style>
</head>
<body>
    <div class="container">
        <?php echo $content ?? ''; ?>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $appUrl; ?>/assets/js/app.js"></script>
</body>
</html>
