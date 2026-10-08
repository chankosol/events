<?php
// views/public/helper_join.php
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ចុះឈ្មោះចូលជួយស្កេន - <?= htmlspecialchars($pass['workshop_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kantumruy Pro', -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .join-card {
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .join-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #ffffff;
            padding: 2rem 1.5rem 1.5rem;
            text-align: center;
        }
        .join-body {
            padding: 1.75rem 1.5rem;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

    <div class="join-card">
        <!-- Card Header -->
        <div class="join-header">
            <div class="icon-circle">
                <i class="bi bi-person-badge-fill fs-1 text-white"></i>
            </div>
            <h5 class="fw-bold mb-1 px-2"><?= htmlspecialchars($pass['workshop_name']) ?></h5>
            <div class="d-inline-flex align-items-center gap-1 bg-white bg-opacity-25 text-white px-3 py-1 rounded-pill small mt-1">
                <i class="bi bi-qr-code-scan"></i>
                <span><?= htmlspecialchars($pass['label']) ?></span>
            </div>
        </div>

        <!-- Card Body -->
        <div class="join-body">
            <div class="text-center mb-4">
                <h6 class="fw-bold text-dark mb-1">សូមស្វាគមន៍មកកាន់តុស្កេនវត្តមាន</h6>
                <p class="text-muted small mb-0">សូមបំពេញឈ្មោះរបស់អ្នកដើម្បីឱ្យអ្នកគ្រប់គ្រងងាយស្រួលសម្គាល់ និងចាប់ផ្តើមជួយស្កេន</p>
            </div>

            <?php if (!empty($joinError)): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= htmlspecialchars($joinError) ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/scan/helper/<?= htmlspecialchars($pass['token']) ?>" method="POST" id="joinForm">
                <div class="mb-3">
                    <label for="helper_name" class="form-label fw-bold text-dark small">
                        <i class="bi bi-person me-1 text-primary"></i> ឈ្មោះរបស់អ្នក (Full Name) <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control form-control-lg rounded-3 fs-6"
                           id="helper_name"
                           name="helper_name"
                           placeholder="ឧ. ចាន់ សុខា / Chan Sokha"
                           value="<?= htmlspecialchars($_POST['helper_name'] ?? '') ?>"
                           required
                           autofocus
                           maxlength="70"
                           autocomplete="name">
                    <div class="form-text small text-muted mt-2">
                        <i class="bi bi-shield-check text-success me-1"></i>ឈ្មោះនេះនឹងជួយឱ្យអ្នកគ្រប់គ្រងដឹងថាអ្នកណាជាអ្នកស្កេនវត្តមាន។
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 py-2.5 shadow-sm mt-4" id="btnSubmit">
                    <span>ចូលទៅកាន់តុស្កេនវត្តមាន</span>
                    <i class="bi bi-arrow-right-circle-fill fs-5"></i>
                </button>
            </form>

            <div class="text-center mt-4 pt-2 border-top">
                <span class="badge bg-light text-muted border px-3 py-1.5 rounded-pill small">
                    <i class="bi bi-clock-history me-1 text-warning"></i>ផុតសុពលភាព៖ <?= date('H:i, d/m/Y', strtotime($pass['expires_at'])) ?>
                </span>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('joinForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>កំពុងចូល...';
            }
        });
    </script>
</body>
</html>
