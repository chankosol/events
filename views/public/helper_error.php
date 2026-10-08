<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($errorTitle ?? 'កំហុសលីងជំនួយការ') ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kantumruy Pro', -apple-system, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            max-width: 460px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            padding: 35px 25px;
            text-align: center;
        }
        .icon-circle {
            width: 76px;
            height: 76px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="bi bi-shield-x"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2"><?= htmlspecialchars($errorTitle ?? 'មិនអាចចូលប្រើប្រាស់បាន') ?></h4>
        <p class="text-muted mb-4 small" style="line-height: 1.6;">
            <?= htmlspecialchars($errorMessage ?? 'លីងជំនួយការស្កេននេះមិនត្រឹមត្រូវ ឬបានផុតសុពលភាពហើយ។') ?>
        </p>
        <div class="d-grid gap-2">
            <a href="<?= APP_URL ?>" class="btn btn-primary fw-semibold py-2">
                <i class="bi bi-house-door me-1"></i> ត្រឡប់ទៅទំព័រដើម
            </a>
        </div>
    </div>
</body>
</html>
