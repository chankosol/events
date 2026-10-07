<div class="row justify-content-center w-100">
    <div class="col-md-5">
        <div class="card auth-card border-0 shadow-sm">
            <div class="card-body p-5">
                <a href="<?php echo APP_URL; ?>" class="d-block auth-logo text-center mb-3">
                    <i class="bi bi-calendar-event-fill me-2 text-primary"></i><?php echo APP_NAME; ?>
                </a>
                <h4 class="text-center mb-3 fw-bold">ភ្លេចពាក្យសម្ងាត់</h4>
                
                <?php if (!empty($flash_error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
                <?php endif; ?>
                <?php if (!empty($flash_success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
                <?php endif; ?>

                <p class="text-muted text-center mb-4 small">សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែលរបស់អ្នក ដើម្បីទទួលបានតំណភ្ជាប់កំណត់ពាក្យសម្ងាត់ឡើងវិញ។</p>

                <form method="POST" action="<?php echo APP_URL; ?>/forgot-password">
                    <?php echo csrfField(); ?>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="name@company.com">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">ផ្ញើតំណភ្ជាប់កំណត់ឡើងវិញ</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0"><a href="<?php echo APP_URL; ?>/login" class="text-decoration-none fw-bold">&larr; ត្រឡប់ទៅទំព័រចូល</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
