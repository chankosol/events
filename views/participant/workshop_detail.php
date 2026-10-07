<div class="container-fluid py-4">
    <div class="mb-3">
        <a href="<?= APP_URL ?>/participant/portal/workshops" class="text-decoration-none">&larr; ត្រឡប់ទៅសិក្ខាសាលារបស់ខ្ញុំ</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="fw-bold mb-2"><?= htmlspecialchars($reg['name']) ?></h2>
                    <p class="text-muted"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($reg['business_name']) ?></p>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">លេខកូដចុះឈ្មោះ:</div>
                        <div class="col-sm-8"><span class="badge bg-dark font-monospace"><?= htmlspecialchars($reg['registration_code']) ?></span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">ស្ថានភាព:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?= $reg['status'] === 'confirmed' ? 'success' : 'secondary' ?>">
                                <?= $reg['status'] === 'confirmed' ? 'បានបញ្ជាក់' : 'រង់ចាំ' ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">កាលបរិច្ឆេទ:</div>
                        <div class="col-sm-8"><?= htmlspecialchars($reg['start_date']) ?> ដល់ <?= htmlspecialchars($reg['end_date']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">ទីកន្លែង:</div>
                        <div class="col-sm-8"><?= htmlspecialchars($reg['location']) ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($sessions)): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">វគ្គសិក្សា & កាលវិភាគ</h5></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($sessions as $s): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($s['name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($s['session_date']) ?> | <?= htmlspecialchars($s['start_time']) ?> - <?= htmlspecialchars($s['end_time']) ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <?php if ($reg['status'] === 'confirmed' && $reg['qr_token']): ?>
            <div class="card shadow-sm border-0 mb-4 text-center">
                <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">កូដ QR ស្កេនវត្តមានរបស់អ្នក</h5></div>
                <div class="card-body p-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($reg['qr_token']) ?>" alt="QR Code" class="img-fluid mb-3 rounded border p-2">
                    <p class="small text-muted mb-0">សូមបង្ហាញកូដ QR នេះនៅច្រកចូលកម្មវិធី ដើម្បីស្កេនវត្តមាន។</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3"><h5 class="mb-0 fw-bold">ស្ថានភាពទូទាត់</h5></div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">ចំនួនទឹកប្រាក់ត្រូវបង់:</span>
                        <span class="fw-bold"><?= htmlspecialchars($reg['currency']) ?> <?= number_format($reg['final_amount'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">ស្ថានភាព:</span>
                        <span class="badge bg-<?= $reg['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= $reg['payment_status'] === 'paid' ? 'បានបង់ប្រាក់' : 'រង់ចាំបង់ប្រាក់' ?></span>
                    </div>
                    
                    <?php if ($reg['payment_status'] !== 'paid' && !empty($paymentMethods)): ?>
                        <hr>
                        <h6 class="fw-bold mb-3">ផ្ទុកឡើងបង្កាន់ដៃបង់ប្រាក់</h6>
                        <?php if (Session::has('error')): ?>
                            <div class="alert alert-danger py-1"><?= Session::flash('error') ?></div>
                        <?php endif; ?>
                        <?php if (Session::has('success')): ?>
                            <div class="alert alert-success py-1"><?= Session::flash('success') ?></div>
                        <?php endif; ?>
                        <form action="<?= APP_URL ?>/participant/portal/workshop/<?= $reg['id'] ?>/proof" method="POST" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">វិធីសាស្ត្រទូទាត់</label>
                                <select name="payment_method_id" class="form-select form-select-sm" required>
                                    <option value="">-- ជ្រើសរើស --</option>
                                    <?php foreach ($paymentMethods as $pm): ?>
                                        <option value="<?= $pm['id'] ?>"><?= htmlspecialchars($pm['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">ចំនួនទឹកប្រាក់ដែលបានបង់</label>
                                <input type="number" step="0.01" name="amount_paid" class="form-control form-control-sm" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">លេខកូដប្រតិបត្តិការធនាគារ</label>
                                <input type="text" name="transaction_reference" class="form-control form-control-sm">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">រូបថតបង្កាន់ដៃ (រូបភាព/PDF)</label>
                                <input type="file" name="proof_file" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100 py-2 fw-bold">បញ្ជូនបង្កាន់ដៃ</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
