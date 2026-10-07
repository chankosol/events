<div class="row justify-content-center w-100">
    <div class="col-md-5">
        <div class="card auth-card">
            <div class="card-body p-5">
                <a href="<?php echo APP_URL; ?>" class="d-block auth-logo">
                    <i class="bi bi-calendar-event-fill me-2"></i><?php echo APP_NAME; ?>
                </a>
                <h4 class="text-center fw-bold mb-4">ចូលប្រើប្រាស់គណនី</h4>
                
                <?php if (!empty($flash_error)): ?>
                    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($flash_error); ?></div>
                <?php endif; ?>
                <?php if (!empty($flash_success)): ?>
                    <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($flash_success); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo APP_URL; ?>/login">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold small">អាសយដ្ឋានអ៊ីមែល</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@company.com" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label fw-bold small">ពាក្យសម្ងាត់</label>
                            <a href="<?php echo APP_URL; ?>/forgot-password" class="text-decoration-none small">ភ្លេចពាក្យសម្ងាត់?</a>
                        </div>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">ចងចាំការចូលប្រើលើឧបករណ៍នេះ</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">ចូលប្រព័ន្ធ</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0 small text-muted">មិនទាន់មានគណនីក្រុមហ៊ុន? <a href="<?php echo APP_URL; ?>/register" class="text-decoration-none fw-bold text-primary">ចុះឈ្មោះបង្កើតគណនី</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
