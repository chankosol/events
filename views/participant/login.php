<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm border-0" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4">
            <h3 class="text-center mb-4 fw-bold">ចូលគណនីសិក្ខាកាម</h3>
            
            <?php if (Session::has('success')): ?>
                <div class="alert alert-success"><?= htmlspecialchars(Session::flash('success')) ?></div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form action="<?= APP_URL ?>/participant/login" method="POST">
                <?= csrfField() ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល</label>
                    <input type="email" name="email" class="form-control" required placeholder="name@example.com">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">ពាក្យសម្ងាត់ <span class="text-muted small">(ទុកទំនេរប្រសិនបើមិនទាន់មាន)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 fw-bold">ចូលប្រើប្រាស់</button>
                </div>
            </form>
        </div>
    </div>
</div>
