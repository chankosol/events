<?php
// views/business/workshops/activate.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 35, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'ការបង់ថ្លៃដំណើរការប្រព័ន្ធ', 'url' => null, 'icon' => 'shield-check'],
];
?>

<div class="activation-page-container" style="max-width: 1060px; margin: 0 auto;">
    <!-- Compact Top Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <h1 class="h4 fw-bold mb-0 text-dark">
                <i class="bi bi-shield-check text-primary me-2"></i>ការបង់ថ្លៃដំណើរការប្រព័ន្ធ (Workshop Activation)
            </h1>
            <span class="badge bg-secondary-subtle text-secondary border">
                កូតា <?= number_format($workshop['capacity']); ?> នាក់
            </span>
        </div>
        <a href="<?= APP_URL; ?>/workshops/<?= $workshop['id']; ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅសិក្ខាសាលា
        </a>
    </div>

    <?php if ($billing && $billing['payment_status'] === 'paid'): ?>
        <!-- Success Card (Fits screen perfectly) -->
        <div class="card shadow-sm border-0 border-top border-success border-4 mx-auto" style="max-width: 650px; margin-top: 2rem;">
            <div class="card-body text-center p-4">
                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center p-3 mb-3 shadow-sm" style="width: 70px; height: 70px;">
                    <i class="bi bi-check-lg fs-1"></i>
                </div>
                <h3 class="fw-bold text-success mb-2">សិក្ខាសាលាត្រូវបានបើកដំណើរការជោគជ័យ!</h3>
                <p class="text-muted mb-3">
                    ថ្លៃសេវាប្រព័ន្ធចំនួន <strong>$<?= number_format($billing['platform_fee'], 2); ?> <?= e($billing['currency']); ?></strong> ត្រូវបានបង់ និងផ្ទៀងផ្ទាត់រួចរាល់នៅថ្ងៃទី <?= formatDate($billing['paid_at']); ?>។<br>
                    លេខវិក្កយបត្រ: <span class="font-monospace fw-bold text-dark"><?= e($billing['invoice_number']); ?></span>
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= APP_URL; ?>/workshops/<?= $workshop['id']; ?>" class="btn btn-primary px-4">
                        <i class="bi bi-gear-fill me-1"></i> គ្រប់គ្រងសិក្ខាសាលា
                    </a>
                    <?php if ($workshop['status'] === 'active'): ?>
                        <form method="POST" action="<?= APP_URL; ?>/workshops/<?= $workshop['id']; ?>/publish" class="d-inline">
                            <?= csrfField(); ?>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-megaphone me-1"></i> បើកចុះឈ្មោះជាសាធារណៈ
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Single Fixed Screen 2-Column Grid (Zero scrollbar on standard display) -->
        <div class="row g-3 align-items-stretch">
            
            <!-- LEFT COLUMN: Workshop Details & Fee Summary -->
            <div class="col-lg-6 d-flex flex-column">
                <div class="card shadow-sm border-0 flex-fill d-flex flex-column h-100">
                    <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-hdd-network me-1"></i> ប្រព័ន្ធទី ១: ថ្លៃសេវាប្រព័ន្ធ SAAS
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    ស្ថានភាព: <?= strtoupper($billing['payment_status'] ?? 'UNPAID'); ?>
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">
                                <?= e($workshop['name']); ?>
                            </h5>
                            <p class="text-muted small mb-3">
                                ថ្លៃសេវាដំណើរការប្រព័ន្ធសិក្ខាសាលាតែមួយលើកគត់ (One-Time Activation Fee) គណនាយ៉ាងសុក្រឹតតាមចំនួនចំណុះសិក្ខាកាម។
                            </p>

                            <!-- Big Highlight Amount Box -->
                            <div class="bg-light rounded-3 p-3 mb-3 border text-center">
                                <div class="text-muted small fw-semibold text-uppercase">ចំនួនទឹកប្រាក់ដែលត្រូវបង់សរុប (Total Amount Due)</div>
                                <div class="display-6 fw-bold text-primary my-1">
                                    $<?= number_format($billing['platform_fee'], 2); ?> <span class="fs-5 text-muted">USD</span>
                                </div>
                                <div class="text-success small fw-semibold">
                                    <i class="bi bi-check-circle-fill me-1"></i> បង់តែមួយដងគត់ (គ្មានការគិតថ្លៃជាវប្រចាំខែឡើយ)
                                </div>
                            </div>

                            <!-- Detailed Specs Table -->
                            <table class="table table-sm table-borderless mb-3 text-secondary" style="font-size: 0.9rem;">
                                <tbody>
                                    <tr class="border-bottom border-light">
                                        <td class="py-1 text-muted w-50"><i class="bi bi-people me-1"></i> ចំណុះកូតាសិក្ខាកាម:</td>
                                        <td class="py-1 text-end fw-bold text-dark"><?= number_format($workshop['capacity']); ?> នាក់</td>
                                    </tr>
                                    <tr class="border-bottom border-light">
                                        <td class="py-1 text-muted"><i class="bi bi-tag me-1"></i> កម្រិតកញ្ចប់តម្លៃ:</td>
                                        <td class="py-1 text-end"><span class="badge bg-secondary"><?= e($billing['pricing_tier'] ?? 'Professional'); ?></span></td>
                                    </tr>
                                    <tr class="border-bottom border-light">
                                        <td class="py-1 text-muted"><i class="bi bi-receipt me-1"></i> លេខវិក្កយបត្រ:</td>
                                        <td class="py-1 text-end font-monospace fw-bold text-dark"><?= e($billing['invoice_number']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-1 text-muted"><i class="bi bi-calendar-check me-1"></i> កាលបរិច្ឆេទផុតកំណត់:</td>
                                        <td class="py-1 text-end fw-medium"><?= formatDate($billing['due_date'] ?? date('Y-m-d')); ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Supported Banking Apps -->
                            <div class="mb-2">
                                <div class="text-muted small fw-semibold mb-1">កម្មវិធីធនាគារដែលអាចស្កេនទូទាត់បាន៖</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-dark border">Bakong</span>
                                    <span class="badge bg-light text-dark border">ABA Mobile</span>
                                    <span class="badge bg-light text-dark border">ACLEDA Mobile</span>
                                    <span class="badge bg-light text-dark border">Wing Bank</span>
                                    <span class="badge bg-light text-dark border">Canadia</span>
                                    <span class="badge bg-light text-dark border">Sathapana</span>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Slip Action Link (Opens Modal cleanly without scrolling) -->
                        <div class="pt-3 border-top mt-2 d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-link btn-sm text-decoration-none text-muted p-0" data-bs-toggle="modal" data-bs-target="#manualUploadModal">
                                <i class="bi bi-upload me-1"></i> ឬ ផ្ទុកឡើងបង្កាន់ដៃផ្ទេរប្រាក់ដោយដៃ (Manual Slip)
                            </button>
                            <span class="badge bg-light text-muted border">Instant 24/7</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Compact Official Bakong KHQR Card -->
            <div class="col-lg-6 d-flex flex-column">
                <div class="card shadow border-0 rounded-4 overflow-hidden flex-fill d-flex flex-column h-100">
                    <!-- Authentic Bakong KHQR Red Header -->
                    <div class="px-3 py-2 text-white" style="background: linear-gradient(135deg, #e11924 0%, #b80010 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-white text-danger fw-bold fs-6 px-3 py-1 shadow-sm" style="letter-spacing: 1.5px;">KHQR</span>
                            <span class="small fw-semibold text-white">
                                <i class="bi bi-shield-check text-white me-1"></i> Bakong NBC Cambodia
                            </span>
                        </div>
                    </div>

                    <!-- KHQR Body -->
                    <div class="card-body p-3 text-center bg-white d-flex flex-column justify-content-between">
                        <div>
                            <?php 
                            $platMerchant = bakong_get_platform_setting('bakong_merchant_name', 'Chan Kosol');
                            $platAccount  = bakong_get_platform_setting('bakong_account_id', 'kosol@abaa');
                            $qrPayload = !empty($billing['bakong_qr_text']) ? $billing['bakong_qr_text'] : ('https://bakong.nbc.gov.kh/pay?merchant=' . urlencode($platMerchant) . '&amount=' . $billing['platform_fee'] . '&currency=USD&bill=' . $billing['invoice_number']);
                            ?>
                            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.75rem;">គណនីរតនាគារប្រព័ន្ធ / Merchant</div>
                            <h6 class="fw-bold text-dark mb-0"><?= e($platMerchant); ?></h6>
                            <div class="small font-monospace text-muted mb-2">(<?= e($platAccount); ?>)</div>

                            <!-- Mini Amount strip -->
                            <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-1 rounded-pill border mb-2">
                                <span class="small text-muted">ទឹកប្រាក់៖</span>
                                <span class="fw-bold text-danger fs-5">$<?= number_format($billing['platform_fee'], 2); ?> USD</span>
                                <span class="text-muted small">|</span>
                                <span class="font-monospace small text-muted"><?= htmlspecialchars($billing['invoice_number']); ?></span>
                            </div>

                            <!-- QR Code Box (Optimized size: 175px for perfect zero-scroll fit) -->
                            <div class="d-flex justify-content-center my-2">
                                <div class="position-relative p-2 bg-white border border-2 border-danger rounded-3 shadow-sm d-inline-block">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode($qrPayload); ?>" 
                                         alt="Bakong KHQR" style="width: 175px; height: 175px; display: block;" class="img-fluid">
                                    
                                    <!-- Center Bakong Badge -->
                                    <div class="position-absolute top-50 start-50 translate-middle bg-white p-1 rounded-circle shadow-sm border border-danger">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 15px;">
                                            $
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted small mb-2" style="font-size: 0.82rem;">
                                បើកកម្មវិធី <strong>Bakong</strong> ឬ <strong>ABA / ACLEDA</strong> រួចស្កេនដើម្បីទូទាត់
                            </p>

                            <!-- Live Polling Listening Indicator -->
                            <div class="alert alert-light border py-1 px-2 mb-2 d-flex align-items-center justify-content-center text-muted mx-auto" style="max-width: 420px;" id="pollingStatus">
                                <div class="spinner-grow spinner-grow-sm text-danger me-2" role="status" style="width: 0.75rem; height: 0.75rem;"></div>
                                <span class="small" style="font-size: 0.8rem;">កំពុងរង់ចាំការស្កេនទូទាត់ប្រាក់ពី Bakong... (Auto Listening)</span>
                            </div>
                        </div>

                        <!-- One-Click Instant Confirm Button -->
                        <div class="mt-1">
                            <button type="button" class="btn btn-danger btn-lg w-100 fw-bold py-2 shadow-sm rounded-3" id="btnAutoConfirm" onclick="confirmBakongPayment()">
                                <i class="bi bi-lightning-charge-fill me-1"></i> ផ្ទៀងផ្ទាត់ និងបើកដំណើរការភ្លាមៗ (Auto Confirm)
                            </button>
                            <div class="text-muted text-center mt-1" style="font-size: 0.72rem;">
                                ប្រព័ន្ធនឹងដំណើរការសិក្ខាសាលាភ្លាមៗដោយស្វ័យប្រវត្តិ
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Manual Slip Upload Modal (Keeps the main page completely clean & zero-scroll) -->
        <div class="modal fade" id="manualUploadModal" tabindex="-1" aria-labelledby="manualUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header bg-light border-bottom py-3">
                        <h6 class="modal-title fw-bold" id="manualUploadModalLabel">
                            <i class="bi bi-upload me-2 text-primary"></i> ផ្ទុកឡើងបង្កាន់ដៃផ្ទេរប្រាក់ដោយដៃ (Manual Slip Upload)
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="<?= APP_URL; ?>/workshops/<?= $workshop['id']; ?>/activate" enctype="multipart/form-data">
                        <?= csrfField(); ?>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">រូបថតបង្កាន់ដៃ <span class="text-danger">*</span></label>
                                <input type="file" name="proof_file" class="form-control" accept="image/*,application/pdf" required>
                                <div class="form-text">គាំទ្រឯកសាររូបភាព (JPG, PNG) ឬ PDF</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">វិធីសាស្ត្រទូទាត់</label>
                                    <select name="payment_method" class="form-select form-select-sm">
                                        <option value="Bakong KHQR">Bakong KHQR</option>
                                        <option value="ABA Bank QR">ABA Bank QR</option>
                                        <option value="ACLEDA Bank">ACLEDA Bank</option>
                                        <option value="Wing">Wing Bank</option>
                                        <option value="Other">ផ្សេងៗ</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">ចំនួនទឹកប្រាក់ ($)</label>
                                    <input type="number" step="0.01" name="amount_claimed" class="form-control form-control-sm" value="<?= number_format($billing['platform_fee'], 2, '.', ''); ?>" required>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold">លេខកូដប្រតិបត្តិការ (Transaction Reference)</label>
                                <input type="text" name="transaction_reference" class="form-control form-control-sm" placeholder="ឧទាហរណ៍: TXN-8923472">
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top py-2 px-3">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                <i class="bi bi-cloud-arrow-up me-1"></i> ដាក់ស្នើបង្កាន់ដៃ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        const WORKSHOP_ID = <?= $workshop['id']; ?>;
        const CSRF_TOKEN = '<?= Session::get('_csrf_token'); ?>';
        const APP_URL = '<?= APP_URL; ?>';
        let pollTimer = null;

        function confirmBakongPayment() {
            const btn = document.getElementById('btnAutoConfirm');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងផ្ទៀងផ្ទាត់ជាមួយ Bakong Network...';

            const fd = new FormData();
            fd.append('_csrf_token', CSRF_TOKEN);

            fetch(`${APP_URL}/workshops/${WORKSHOP_ID}/activate/confirm-bakong`, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    btn.className = 'btn btn-success btn-lg w-100 fw-bold py-2 shadow-sm rounded-3';
                    btn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> បានផ្ទៀងផ្ទាត់ជោគជ័យ! កំពុងបើកដំណើរការ...';
                    if (pollTimer) clearInterval(pollTimer);
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    alert(res.message || 'មានបញ្ហាក្នុងការផ្ទៀងផ្ទាត់។');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> ផ្ទៀងផ្ទាត់ និងបើកដំណើរការភ្លាមៗ (Auto Confirm)';
                }
            })
            .catch(err => {
                console.error(err);
                alert('មានបញ្ហាក្នុងការតភ្ជាប់ សូមព្យាយាមម្តងទៀត។');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> ផ្ទៀងផ្ទាត់ និងបើកដំណើរការភ្លាមៗ (Auto Confirm)';
            });
        }

        // Background live status polling
        function startPolling() {
            pollTimer = setInterval(() => {
                fetch(`${APP_URL}/workshops/${WORKSHOP_ID}/activate/check-status`)
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data && res.data.is_paid) {
                        clearInterval(pollTimer);
                        const pStatus = document.getElementById('pollingStatus');
                        if (pStatus) {
                            pStatus.className = 'alert alert-success py-1 px-2 mb-2 d-flex align-items-center justify-content-center text-success mx-auto';
                            pStatus.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> បានទទួលការទូទាត់ជោគជ័យ! កំពុងដំណើរការ...';
                        }
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    }
                })
                .catch(e => console.error(e));
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            startPolling();
        });
        </script>
    <?php endif; ?>
</div>
