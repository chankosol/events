<div class="row justify-content-center w-100">
    <div class="col-md-5">
        <div class="card auth-card border-0 shadow-sm">
            <div class="card-body p-5">
                <a href="<?php echo APP_URL; ?>" class="d-block auth-logo text-center mb-3">
                    <i class="bi bi-calendar-event-fill me-2 text-primary"></i><?php echo APP_NAME; ?>
                </a>
                <h4 class="text-center mb-4 fw-bold">កំណត់ពាក្យសម្ងាត់ថ្មី</h4>
                
                <?php if (!empty($flash_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo APP_URL; ?>/reset-password">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">ពាក្យសម្ងាត់ថ្មី</label>
                        <input type="password" class="form-control" id="password" name="password" required autofocus minlength="8" placeholder="យ៉ាងតិច ៨ តួអក្សរ">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-bold">បញ្ជាក់ពាក្យសម្ងាត់ថ្មី</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8" placeholder="វាយពាក្យសម្ងាត់ម្តងទៀត">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">កំណត់ពាក្យសម្ងាត់</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
