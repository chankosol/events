<?php
// views/business/settings/index.php
$user = $user ?? Auth::user() ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ការកំណត់ស្ថាប័ន / ក្រុមហ៊ុន</h2>
        <p class="text-muted mb-0">កំណត់ព័ត៌មានស្ថាប័ន ស្លាកសញ្ញា និងវិធីសាស្ត្រទទួលប្រាក់ពីសិក្ខាកាម។</p>
    </div>
</div>

<style>
#settingsTabs .nav-link {
    color: #4b5563;
    border-radius: 8px;
    transition: all 0.2s ease-in-out;
}
#settingsTabs .nav-link:hover {
    background-color: #f3f4f6;
    color: #111827;
}
#settingsTabs .nav-link.active {
    background-color: #0d6efd;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);
}
</style>

<!-- Horizontal Settings Navigation Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2">
        <ul class="nav nav-pills nav-fill flex-column flex-sm-row gap-2" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-2.5 px-3 fw-bold d-flex align-items-center justify-content-center gap-2" 
                        data-bs-toggle="pill" data-bs-target="#generalTab" type="button" role="tab" aria-selected="true">
                    <i class="bi bi-building fs-6"></i>
                    <span>ស្ថាប័ន</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2.5 px-3 fw-bold d-flex align-items-center justify-content-center gap-2" 
                        data-bs-toggle="pill" data-bs-target="#brandingTab" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-palette fs-6"></i>
                    <span>ស្លាកសញ្ញា</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2.5 px-3 fw-bold d-flex align-items-center justify-content-center gap-2" 
                        data-bs-toggle="pill" data-bs-target="#paymentsTab" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-credit-card-2-front fs-6"></i>
                    <span>ច្រកទូទាត់ & QR</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-2.5 px-3 fw-bold d-flex align-items-center justify-content-center gap-2" 
                        data-bs-toggle="pill" data-bs-target="#profileTab" type="button" role="tab" aria-selected="false">
                    <i class="bi bi-person-circle fs-6"></i>
                    <span>គណនីផ្ទាល់ខ្លួន</span>
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
            <!-- GENERAL SETTINGS -->
            <div class="tab-pane fade show active" id="generalTab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">ព័ត៌មានស្ថាប័ន / ក្រុមហ៊ុន</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?php echo APP_URL; ?>/settings/general">
                            <?php echo csrfField(); ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ឈ្មោះស្ថាប័ន / ក្រុមហ៊ុន</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($business['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">អ៊ីមែលក្រុមហ៊ុន</label>
                                    <input type="email" class="form-control" value="<?php echo e($business['email'] ?? ''); ?>" readonly>
                                    <div class="form-text small">អ៊ីមែលគណនីគ្រប់គ្រងដោយម្ចាស់ស្ថាប័ន។</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">លេខទូរស័ព្ទទំនាក់ទំនង</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo e($business['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">គេហទំព័រ</label>
                                    <input type="url" name="website" class="form-control" value="<?php echo e($business['website'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ប្រទេស</label>
                                    <input type="text" name="country" class="form-control" value="<?php echo e($business['country'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">រាជធានី / ខេត្ត</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo e($business['city'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">តំបន់ម៉ោង</label>
                                    <input type="text" name="timezone" class="form-control" value="<?php echo e($business['timezone'] ?? 'Asia/Phnom_Penh'); ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">អាសយដ្ឋាន</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo e($business['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">រូបិយប័ណ្ណចម្បង</label>
                                    <select name="preferred_currency" class="form-select">
                                        <option value="USD" <?php echo ($business['preferred_currency'] ?? 'USD') === 'USD' ? 'selected' : ''; ?>>ដុល្លារអាមេរិក ($ USD)</option>
                                        <option value="KHR" <?php echo ($business['preferred_currency'] ?? 'USD') === 'KHR' ? 'selected' : ''; ?>>រៀលខ្មែរ (៛ KHR)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ភាសាចម្បង</label>
                                    <select name="preferred_language" class="form-select">
                                        <option value="km" <?php echo ($business['preferred_language'] ?? 'km') === 'km' ? 'selected' : ''; ?>>ភាសាខ្មែរ</option>
                                        <option value="en" <?php echo ($business['preferred_language'] ?? 'km') === 'en' ? 'selected' : ''; ?>>English</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4">រក្សាទុកការកែប្រែ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- BRANDING SETTINGS -->
            <div class="tab-pane fade" id="brandingTab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">ការរចនាស្លាកសញ្ញាក្រុមហ៊ុន</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?php echo APP_URL; ?>/settings/branding" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ឡូហ្គោក្រុមហ៊ុន / ស្ថាប័ន</label>
                                    <input type="file" name="logo" class="form-control" accept="image/*">
                                    <?php if (!empty($branding['logo_path'])): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo APP_URL . '/' . e($branding['logo_path']); ?>" class="img-thumbnail" style="max-height: 80px;" alt="Logo">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ពណ៌ចម្បងនៃស្លាកសញ្ញា</label>
                                    <div class="input-group">
                                        <input type="color" name="primary_color" class="form-control form-control-color" value="<?php echo e($branding['primary_color'] ?? '#0d6efd'); ?>">
                                        <input type="text" class="form-control" value="<?php echo e($branding['primary_color'] ?? '#0d6efd'); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4">ធ្វើបច្ចុប្បន្នភាពការរចនា</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- PAYMENT RECEIVING METHODS (HOST ORGANIZER) -->
            <div class="tab-pane fade" id="paymentsTab">
                <?php
                $bSettings = $businessSettings ?? [];
                $bHasBakongToken = !empty($bSettings['bakong_api_token']);
                $bHasAbaKey = !empty($bSettings['aba_api_key']);
                $bPaymentProvider = $bSettings['payment_provider'] ?? 'both';
                ?>

                <!-- 1. គណនីទទួលប្រាក់ (Manual Bank Accounts / QR) -->
                <div class="card shadow-sm border-0 mb-3 rounded-3">
                    <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-wallet2 text-primary"></i>
                            <span class="fw-bold text-dark" style="font-size: 13px;">គណនីទទួលប្រាក់</span>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addPaymentModal" style="font-size: 12px;">
                            <i class="bi bi-plus-lg me-1"></i> បន្ថែម
                        </button>
                    </div>
                    <div class="card-body p-3">
                        <!-- Current Payment Methods -->
                        <div class="row g-3">
                            <?php if (empty($paymentMethods)): ?>
                                <div class="col-12 text-center py-4 text-muted border rounded-3 bg-light">
                                    <i class="bi bi-wallet2 fs-3 d-block text-secondary mb-2"></i>
                                    <p class="mb-2 small">មិនទាន់មានវិធីសាស្ត្រទទួលប្រាក់</p>
                                    <button type="button" class="btn btn-sm btn-primary px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addPaymentModal" style="font-size: 12px;">
                                        <i class="bi bi-plus-lg me-1"></i> បន្ថែមថ្មី
                                    </button>
                                </div>
                            <?php else: ?>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <div class="col-md-6">
                                        <div class="card border rounded-3 h-100 p-3 shadow-sm d-flex flex-column justify-content-between">
                                            <div class="d-flex align-items-start gap-3">
                                                <?php if (!empty($pm['qr_image_path'])): ?>
                                                    <img src="<?php echo APP_URL . '/' . e($pm['qr_image_path']); ?>" class="img-thumbnail rounded-3 flex-shrink-0" style="width: 76px; height: 76px; object-fit: contain;" alt="QR">
                                                <?php else: ?>
                                                    <div class="bg-light p-3 rounded-3 text-muted flex-shrink-0"><i class="bi bi-qr-code fs-3"></i></div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <h6 class="fw-bold mb-0 text-truncate" style="font-size: 13px;"><?php echo e($pm['name']); ?></h6>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <?php if (!empty($pm['is_default'])): ?>
                                                                <span class="badge bg-warning text-dark border border-warning px-1.5 py-0.5" style="font-size: 10px;">
                                                                    <i class="bi bi-star-fill text-dark me-0.5"></i> ចម្បង
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if (($pm['status'] ?? 'active') === 'active'): ?>
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5" style="font-size: 10px;">សកម្ម</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1.5 py-0.5" style="font-size: 10px;">អសកម្ម</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-muted small" style="font-size: 12px;"><strong>ធនាគារ:</strong> <?php echo e($pm['bank'] ?: 'ធនាគារ'); ?></div>
                                                    <div class="text-muted small" style="font-size: 12px;"><strong>គណនី:</strong> <?php echo e($pm['account_name'] ?: 'N/A'); ?> (<?php echo e($pm['account_number'] ?: 'N/A'); ?>)</div>
                                                    <div class="text-muted small" style="font-size: 12px;"><strong>រូបិយប័ណ្ណ:</strong> <?php echo e($pm['currency']); ?></div>
                                                    <?php if (!empty($pm['instructions'])): ?>
                                                        <div class="text-muted small mt-1 text-truncate" style="font-size: 11px;" title="<?php echo e($pm['instructions']); ?>">
                                                            <i class="bi bi-info-circle me-1"></i><?php echo e($pm['instructions']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Default Button on Left, Edit & Delete on Right -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <div>
                                                    <?php if (empty($pm['is_default'])): ?>
                                                        <form method="POST" action="<?php echo APP_URL; ?>/settings/payment-methods/set-default" class="d-inline m-0">
                                                            <?php echo csrfField(); ?>
                                                            <input type="hidden" name="id" value="<?php echo (int)$pm['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark py-0.5 px-2" style="font-size: 11px;" title="កំណត់ជាវិធីសាស្ត្រទទួលប្រាក់ចម្បង">
                                                                <i class="bi bi-star me-1 text-warning"></i> កំណត់ជាចម្បង
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5" style="font-size: 10px;">
                                                            <i class="bi bi-check-circle-fill me-1"></i> គណនីចម្បង
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center" style="gap: 8px;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded" style="font-size: 11px;"
                                                            onclick='openEditPaymentMethod(<?php echo json_encode($pm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                                        <i class="bi bi-pencil-square me-1"></i> កែប្រែ
                                                    </button>
                                                    <form method="POST" action="<?php echo APP_URL; ?>/settings/payment-methods/delete" class="d-inline m-0" onsubmit="return confirm('តើអ្នកពិតជាចង់លុប «<?php echo e($pm['name']); ?>» មែនទេ?');">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="id" value="<?php echo (int)$pm['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded" style="font-size: 11px;">
                                                            <i class="bi bi-trash me-1"></i> លុប
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 2. ច្រកទូទាត់ស្វ័យប្រវត្តិ (Bakong & ABA Gateway - Collapsible) -->
                <div class="card shadow-sm border-0 mb-4 rounded-3">
                    <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center"
                         role="button" data-bs-toggle="collapse" data-bs-target="#gatewaySetupCollapse" aria-expanded="false" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-cpu text-primary"></i>
                            <span class="fw-bold text-dark" style="font-size: 13px;">ច្រកទូទាត់ស្វ័យប្រវត្តិ</span>
                            <?php if ($bHasBakongToken): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-1.5" style="font-size: 10px;">Bakong</span>
                            <?php endif; ?>
                            <?php if ($bHasAbaKey): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0.5 px-1.5" style="font-size: 10px;">ABA</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted d-flex align-items-center gap-1" style="font-size: 12px;">
                            <span class="small">កំណត់</span>
                            <i class="bi bi-chevron-down small" id="gatewayChevron"></i>
                        </div>
                    </div>
                    <div class="collapse" id="gatewaySetupCollapse">
                        <div class="card-body p-3">
                            <form method="POST" action="<?php echo APP_URL; ?>/settings/gateway-setup">
                                <?php echo csrfField(); ?>

                                <div class="row align-items-center g-2 mb-3">
                                    <div class="col-auto">
                                        <label class="form-label text-muted mb-0 small" style="font-size: 12px;">ច្រកទូទាត់៖</label>
                                    </div>
                                    <div class="col-auto">
                                        <select class="form-select form-select-sm rounded-2" name="payment_provider" style="font-size: 12px; width: auto;">
                                            <option value="both" <?php echo $bPaymentProvider === 'both' ? 'selected' : ''; ?>>Bakong & ABA</option>
                                            <option value="bakong" <?php echo $bPaymentProvider === 'bakong' ? 'selected' : ''; ?>>Bakong តែប៉ុណ្ណោះ</option>
                                            <option value="aba" <?php echo $bPaymentProvider === 'aba' ? 'selected' : ''; ?>>ABA តែប៉ុណ្ណោះ</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Accordion for Bakong and ABA -->
                                <div class="accordion border rounded-2 mb-3" id="accordionGateways">
                                    <!-- Bakong Item -->
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 bg-light text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBakong" aria-expanded="false" style="font-size: 12px;">
                                                <i class="bi bi-qr-code text-danger me-2"></i>
                                                <span class="fw-bold">Bakong QR</span>
                                                <?php if ($bHasBakongToken): ?>
                                                    <span class="badge bg-success ms-2 py-0.5 px-1.5" style="font-size: 10px;">ភ្ជាប់រួច</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2 py-0.5 px-1.5" style="font-size: 10px;">មិនទាន់ភ្ជាប់</span>
                                                <?php endif; ?>
                                            </button>
                                        </h2>
                                        <div id="collapseBakong" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                                            <div class="accordion-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">Account ID</label>
                                                        <input class="form-control form-control-sm font-monospace" name="bakong_account_id" placeholder="user@bakong" value="<?php echo e($bSettings['bakong_account_id'] ?? ''); ?>" style="font-size: 12px;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">ឈ្មោះគណនី</label>
                                                        <input class="form-control form-control-sm" name="merchant_name" placeholder="ឈ្មោះស្ថាប័ន ឬបុគ្គល" value="<?php echo e($bSettings['merchant_name'] ?? ''); ?>" style="font-size: 12px;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <label class="form-label text-muted mb-0 small" style="font-size: 11px;">API Token</label>
                                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none" style="font-size: 11px;" onclick="testBusinessBakongToken()">
                                                                <i class="bi bi-shield-check me-0.5"></i> ធ្វើតេស្ត Token
                                                            </button>
                                                        </div>
                                                        <div class="input-group input-group-sm">
                                                            <input type="password" class="form-control font-monospace" id="business_bakong_api_token" name="bakong_api_token" placeholder="Token" value="<?php echo e($bSettings['bakong_api_token'] ?? ''); ?>" style="font-size: 12px;">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('business_bakong_api_token', this)">
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                        </div>
                                                        <div id="bakongTestResult" class="small mt-1 d-none" style="font-size: 11.5px;"></div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">Base URL</label>
                                                        <input class="form-control form-control-sm font-monospace" name="bakong_api_base_url" value="<?php echo e($bSettings['bakong_api_base_url'] ?? 'https://api-bakong.nbc.gov.kh'); ?>" style="font-size: 12px;">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">Static QR Text (ជំនួយ)</label>
                                                        <input class="form-control form-control-sm font-monospace" name="bakong_static_qr_text" placeholder="កូដ QR ថេរ (បើមាន)" value="<?php echo e($bSettings['bakong_static_qr_text'] ?? ''); ?>" style="font-size: 12px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ABA Item -->
                                    <div class="accordion-item border-0 border-top">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 px-3 bg-light text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAba" aria-expanded="false" style="font-size: 12px;">
                                                <i class="bi bi-credit-card-2-front text-primary me-2"></i>
                                                <span class="fw-bold">ABA Payway</span>
                                                <?php if ($bHasAbaKey): ?>
                                                    <span class="badge bg-success ms-2 py-0.5 px-1.5" style="font-size: 10px;">ភ្ជាប់រួច</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2 py-0.5 px-1.5" style="font-size: 10px;">មិនទាន់ភ្ជាប់</span>
                                                <?php endif; ?>
                                            </button>
                                        </h2>
                                        <div id="collapseAba" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                                            <div class="accordion-body p-3">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">Merchant ID</label>
                                                        <input class="form-control form-control-sm font-monospace" name="aba_merchant_id" placeholder="Merchant ID" value="<?php echo e($bSettings['aba_merchant_id'] ?? ''); ?>" style="font-size: 12px;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">ឈ្មោះគណនី</label>
                                                        <input class="form-control form-control-sm" name="aba_merchant_name" placeholder="ឈ្មោះគណនី" value="<?php echo e($bSettings['aba_merchant_name'] ?? ''); ?>" style="font-size: 12px;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">API Key</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="password" class="form-control font-monospace" id="business_aba_api_key" name="aba_api_key" placeholder="API Key" value="<?php echo e($bSettings['aba_api_key'] ?? ''); ?>" style="font-size: 12px;">
                                                            <button class="btn btn-outline-secondary" type="button" id="toggle-business-aba-key">
                                                                <i class="bi bi-eye" id="toggle-business-aba-icon"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-muted mb-1 small" style="font-size: 11px;">Checkout URL</label>
                                                        <input class="form-control form-control-sm font-monospace" name="aba_api_url" value="<?php echo e($bSettings['aba_api_url'] ?? 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase'); ?>" style="font-size: 12px;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-dark btn-sm rounded-pill px-3" style="font-size: 12px;">
                                        <i class="bi bi-check2-circle me-1"></i> រក្សាទុក
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MY PROFILE TAB -->
            <div class="tab-pane fade" id="profileTab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person-circle text-primary me-2"></i> ព័ត៌មានផ្ទាល់ខ្លួន
                        </h5>
                        <a href="<?php echo APP_URL; ?>/profile" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> ទំព័រពេញលេញ
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="<?php echo APP_URL; ?>/profile" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="redirect_back" value="<?php echo APP_URL; ?>/settings">
                            
                            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                <div id="settingsAvatarPreview">
                                    <?php echo userAvatarHtml($user, 76, 'border shadow-sm'); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <label class="form-label fw-bold mb-1">ប្តូររូបថតគណនី</label>
                                    <input type="file" name="profile_photo" id="settingsProfilePhotoInput" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp">
                                    <div class="form-text small">គាំទ្រ PNG, JPG, WEBP (ទំហំអតិបរមា 2MB)</div>
                                </div>
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <div class="form-check text-nowrap">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="settingsRemovePhoto">
                                        <label class="form-check-label text-danger small" for="settingsRemovePhoto">
                                            លុបរូបចេញ
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($user['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">អ៊ីមែលគណនី</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo e($user['email'] ?? ''); ?>" readonly>
                                    <div class="form-text small">អ៊ីមែលប្រើសម្រាប់ចូលប្រព័ន្ធ។</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone'] ?? ''); ?>" placeholder="012 345 678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">តួនាទី</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo e(userRoleTitle($user)); ?>" readonly>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded border mb-3">
                                <h6 class="fw-bold mb-2 small text-secondary">
                                    <i class="bi bi-key me-1"></i> ប្តូរពាក្យសម្ងាត់ (ទុកទំនេរប្រសិនបើមិនផ្លាស់ប្តូរ)
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="password" name="current_password" class="form-control form-control-sm" placeholder="ពាក្យសម្ងាត់បច្ចុប្បន្ន">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="password" name="new_password" class="form-control form-control-sm" placeholder="ពាក្យសម្ងាត់ថ្មី (យ៉ាងតិច ៦ ខ្ទង់)">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="ផ្ទៀងផ្ទាត់ពាក្យសម្ងាត់ថ្មី">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check2-circle me-1"></i> រក្សាទុកព័ត៌មានផ្ទាល់ខ្លួន
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?php echo APP_URL; ?>/settings/payment-methods" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addPaymentModalLabel">
                        <i class="bi bi-plus-circle text-primary me-2"></i>បន្ថែមវិធីសាស្ត្រទទួលប្រាក់
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះវិធីសាស្ត្រ <span class="text-danger">*</span></label>
                            <input type="text" name="method_name" class="form-control form-control-sm" placeholder="ABA Bank QR" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះធនាគារ</label>
                            <input type="text" name="bank" list="bankOptionsList" class="form-control form-control-sm" placeholder="ABA / Bakong">
                            <datalist id="bankOptionsList">
                                <option value="ABA Bank">
                                <option value="Bakong KHQR">
                                <option value="ACLEDA Bank">
                                <option value="Canadia Bank">
                                <option value="Wing Bank">
                                <option value="Sathapana Bank">
                                <option value="Prince Bank">
                                <option value="TrueMoney">
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">រូបិយប័ណ្ណ</label>
                            <select name="currency" class="form-select form-select-sm">
                                <option value="USD">ដុល្លារ ($ USD)</option>
                                <option value="KHR">រៀល (៛ KHR)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះម្ចាស់គណនី</label>
                            <input type="text" name="account_name" class="form-control form-control-sm" placeholder="ឈ្មោះម្ចាស់គណនី">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">លេខគណនី</label>
                            <input type="text" name="account_number" class="form-control form-control-sm" placeholder="លេខគណនី ឬ Bakong ID">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">រូបភាព QR <span class="text-danger">*</span></label>
                            <input type="file" name="qr_image" class="form-control form-control-sm" accept="image/*" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ចំណាំបន្ថែម</label>
                            <textarea name="instructions" class="form-control form-control-sm" rows="2" placeholder="ចំណាំសម្រាប់អ្នកស្កេនបង់ប្រាក់..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="add_pm_is_default">
                                <label class="form-check-label small fw-bold" for="add_pm_is_default">
                                    <i class="bi bi-star-fill text-warning me-1"></i> កំណត់ជាចម្បង
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-plus-lg me-1"></i> បន្ថែម
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?php echo APP_URL; ?>/settings/payment-methods/update" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="id" id="edit_pm_id">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editPaymentModalLabel">
                        <i class="bi bi-pencil-square text-primary me-2"></i>កែប្រែវិធីសាស្ត្រទទួលប្រាក់
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះវិធីសាស្ត្រ <span class="text-danger">*</span></label>
                            <input type="text" name="method_name" id="edit_pm_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះធនាគារ</label>
                            <input type="text" name="bank" id="edit_pm_bank" list="bankOptionsList" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">រូបិយប័ណ្ណ</label>
                            <select name="currency" id="edit_pm_currency" class="form-select form-select-sm">
                                <option value="USD">ដុល្លារ ($ USD)</option>
                                <option value="KHR">រៀល (៛ KHR)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ស្ថានភាព</label>
                            <select name="status" id="edit_pm_status" class="form-select form-select-sm">
                                <option value="active">សកម្ម</option>
                                <option value="inactive">អសកម្ម</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះម្ចាស់គណនី</label>
                            <input type="text" name="account_name" id="edit_pm_account_name" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">លេខគណនី</label>
                            <input type="text" name="account_number" id="edit_pm_account_number" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">រូបភាព QR</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img id="edit_pm_qr_preview" src="" class="img-thumbnail rounded d-none" style="width: 60px; height: 60px; object-fit: contain;">
                                <div class="flex-grow-1">
                                    <input type="file" name="qr_image" class="form-control form-control-sm" accept="image/*">
                                    <div class="form-text small">ជ្រើសរើសរូបថ្មីប្រសិនបើចង់ប្តូរ QR</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ចំណាំបន្ថែម</label>
                            <textarea name="instructions" id="edit_pm_instructions" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="edit_pm_is_default">
                                <label class="form-check-label small fw-bold" for="edit_pm_is_default">
                                    <i class="bi bi-star-fill text-warning me-1"></i> កំណត់ជាចម្បង
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="bi bi-check2 me-1"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditPaymentMethod(pm) {
    document.getElementById('edit_pm_id').value = pm.id;
    document.getElementById('edit_pm_name').value = pm.name || '';
    document.getElementById('edit_pm_bank').value = pm.bank || '';
    document.getElementById('edit_pm_currency').value = pm.currency || 'USD';
    document.getElementById('edit_pm_status').value = pm.status || 'active';
    document.getElementById('edit_pm_is_default').checked = (parseInt(pm.is_default, 10) === 1);
    document.getElementById('edit_pm_account_name').value = pm.account_name || '';
    document.getElementById('edit_pm_account_number').value = pm.account_number || '';
    document.getElementById('edit_pm_instructions').value = pm.instructions || '';
    
    const qrPreview = document.getElementById('edit_pm_qr_preview');
    if (pm.qr_image_path) {
        qrPreview.src = '<?php echo APP_URL; ?>/' + pm.qr_image_path;
        qrPreview.classList.remove('d-none');
    } else {
        qrPreview.classList.add('d-none');
    }
    
    const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // ---- ABA API Key show/hide toggle (business settings) ----
    const toggleAbaBtn  = document.getElementById('toggle-business-aba-key');
    const abaKeyInput   = document.getElementById('business_aba_api_key');
    const toggleAbaIcon = document.getElementById('toggle-business-aba-icon');
    if (toggleAbaBtn && abaKeyInput) {
        toggleAbaBtn.addEventListener('click', function() {
            const isPass = abaKeyInput.type === 'password';
            abaKeyInput.type = isPass ? 'text' : 'password';
            toggleAbaIcon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }

    // ---- Bakong token count badge (business settings) ----
    const bakongTokenInput = document.getElementById('business_bakong_api_token');
    const tokenBadge       = document.getElementById('business-token-count-badge');
    if (tokenBadge && bakongTokenInput) {
        // Show 1 if the field has a value (token saved), 0 otherwise
        const updateBadge = () => {
            tokenBadge.textContent = bakongTokenInput.value.trim() ? '1' : '0';
            tokenBadge.className   = bakongTokenInput.value.trim()
                ? 'badge bg-success rounded-pill ms-1'
                : 'badge bg-secondary rounded-pill ms-1';
        };
        updateBadge();
        bakongTokenInput.addEventListener('input', updateBadge);
    }

        // Chevron toggle for gateway collapse
    const gwCollapse = document.getElementById('gatewaySetupCollapse');
    const gwChevron  = document.getElementById('gatewayChevron');
    if (gwCollapse && gwChevron) {
        gwCollapse.addEventListener('show.bs.collapse', function () {
            gwChevron.classList.remove('bi-chevron-down');
            gwChevron.classList.add('bi-chevron-up');
        });
        gwCollapse.addEventListener('hide.bs.collapse', function () {
            gwChevron.classList.remove('bi-chevron-up');
            gwChevron.classList.add('bi-chevron-down');
        });
    }

    // Activate tab from URL hash if present
    if (window.location.hash) {
        const hash = window.location.hash;
        const trigger = document.querySelector('#settingsTabs [data-bs-target="' + hash + '"]')
                     || document.querySelector('#settingsTabs [href="' + hash + '"]');
        if (trigger) {
            const tab = bootstrap.Tab.getOrCreateInstance(trigger);
            tab.show();
        }
    }

    // Keep URL hash updated when switching tabs
    document.querySelectorAll('#settingsTabs [data-bs-toggle="pill"], #settingsTabs [data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target') || e.target.getAttribute('href');
            if (target && target.startsWith('#')) {
                history.replaceState(null, null, target);
            }
        });
    });

    const sPhotoInput = document.getElementById('settingsProfilePhotoInput');
    const sPreview = document.getElementById('settingsAvatarPreview');
    if (sPhotoInput && sPreview) {
        sPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    sPreview.innerHTML = '<img src="' + evt.target.result + '" class="rounded-circle object-fit-cover border shadow-sm" style="width:76px;height:76px;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
}

async function testBusinessBakongToken() {
    const tokenInp = document.getElementById('business_bakong_api_token');
    const resultBox = document.getElementById('bakongTestResult');
    const token = tokenInp ? tokenInp.value.trim() : '';

    if (!token) {
        alert('សូមបញ្ចូល Token ជាមុនសិន!');
        return;
    }

    resultBox.className = 'small mt-1 text-muted';
    resultBox.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងធ្វើតេស្តភ្ជាប់ទៅកាន់ NBC Bakong...';
    resultBox.classList.remove('d-none');

    try {
        const res = await fetch('<?= APP_URL ?>/settings/test-bakong', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ token: token })
        });
        const data = await res.json();
        if (data.success) {
            resultBox.className = 'small mt-1 text-success fw-bold';
            resultBox.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
        } else {
            resultBox.className = 'small mt-1 text-danger fw-bold';
            resultBox.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + data.message;
        }
    } catch (err) {
        resultBox.className = 'small mt-1 text-danger';
        resultBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> មិនអាចភ្ជាប់ទៅកាន់ម៉ាស៊ីនបម្រើបានទេ។';
    }
}
</script>
