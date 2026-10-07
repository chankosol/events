<?php
// views/platform/settings/index.php
$actualBakongTokens = $settingsMap['bakong_api_token'] ?? '';
$hasBakongToken = !empty(trim($actualBakongTokens));
$tokenCount = 0;
if ($hasBakongToken) {
    $tokenCount = count(array_filter(array_map('trim', preg_split('/[\s,]+/', $actualBakongTokens))));
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">
            <i class="bi bi-gear-fill text-primary me-2"></i>ការកំណត់ប្រព័ន្ធសកល (Global Platform Settings)
        </h2>
        <p class="text-muted mb-0">ការកំណត់រចនាសម្ព័ន្ធប្រព័ន្ធទូទៅ និងច្រកទូទាត់ប្រាក់ផ្លូវការ Bakong NBC KHQR Gateway សម្រាប់ទទួលថ្លៃសេវាដំណើរការប្រព័ន្ធ។</p>
    </div>
</div>

<form method="POST" action="<?php echo APP_URL; ?>/platform/settings" id="platformSettingsForm">
    <?php echo csrfField(); ?>

    <!-- Card 1: General Platform Configuration -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <div class="d-flex align-items-center gap-2">
                <span class="bg-primary-subtle text-primary p-2 rounded-3">
                    <i class="bi bi-sliders fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0">ព័ត៌មានទូទៅនៃប្រព័ន្ធ (General Settings)</h5>
                    <small class="text-muted">ឈ្មោះប្រព័ន្ធ រូបិយប័ណ្ណ និងការកំណត់មូលដ្ឋាន</small>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">ឈ្មោះកម្មវិធីប្រព័ន្ធ (App Name)</label>
                    <input type="text" name="app_name" class="form-control" value="<?php echo e($settingsMap['app_name'] ?? 'Workshop OS'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">ពាក្យស្លោកប្រព័ន្ធ (Tagline)</label>
                    <input type="text" name="app_tagline" class="form-control" value="<?php echo e($settingsMap['app_tagline'] ?? 'Complete Workshop Operating System'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">អ៊ីមែលជំនួយការ (Support Email)</label>
                    <input type="email" name="support_email" class="form-control" value="<?php echo e($settingsMap['support_email'] ?? 'support@workshopos.com'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">រូបិយប័ណ្ណលំនាំដើម</label>
                    <select name="default_currency" class="form-select">
                        <option value="USD" <?php echo ($settingsMap['default_currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>ដុល្លារ ($ USD)</option>
                        <option value="KHR" <?php echo ($settingsMap['default_currency'] ?? '') === 'KHR' ? 'selected' : ''; ?>>រៀល (៛ KHR)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">ភាសាលំនាំដើម</label>
                    <select name="default_language" class="form-select">
                        <option value="km" <?php echo ($settingsMap['default_language'] ?? '') === 'km' ? 'selected' : ''; ?>>ភាសាខ្មែរ</option>
                        <option value="en" <?php echo ($settingsMap['default_language'] ?? '') === 'en' ? 'selected' : ''; ?>>English</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">បុព្វបទលេខវិក្កយបត្រ (Invoice Prefix)</label>
                    <input type="text" name="invoice_prefix" class="form-control" value="<?php echo e($settingsMap['invoice_prefix'] ?? 'INV'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">ការអនុញ្ញាតឱ្យស្ថាប័នថ្មីចុះឈ្មោះ</label>
                    <select name="registration_enabled" class="form-select">
                        <option value="1" <?php echo ($settingsMap['registration_enabled'] ?? '') === '1' ? 'selected' : ''; ?>>បើកដំណើរការ (ចុះឈ្មោះជាសាធារណៈ)</option>
                        <option value="0" <?php echo ($settingsMap['registration_enabled'] ?? '') === '0' ? 'selected' : ''; ?>>បិទ (តាមការអញ្ជើញប៉ុណ្ណោះ)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Official Bakong NBC KHQR Gateway (System A Fee Receiver) -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="bg-danger-subtle text-danger p-2 rounded-3">
                    <i class="bi bi-qr-code fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        ច្រកទូទាត់ផ្លូវការ Bakong NBC KHQR Gateway (Platform System Fee Receiver)
                    </h5>
                    <small class="text-muted">គណនីទទួលប្រាក់ និង Token ផ្ទៀងផ្ទាត់ដោយស្វ័យប្រវត្តិនូវថ្លៃដំណើរការប្រព័ន្ធ (Workshop Activation Fee - System A)</small>
                </div>
            </div>
            <span class="badge <?php echo $hasBakongToken ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2 rounded-pill">
                <i class="bi <?php echo $hasBakongToken ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-1"></i>
                <?php echo $hasBakongToken ? "Bakong Configured ({$tokenCount} Tokens)" : 'Token មិនទាន់កំណត់'; ?>
            </span>
        </div>

        <div class="card-body p-4">
            <!-- Feature Callout Alert -->
            <div class="alert alert-info border-info-subtle bg-info-subtle text-dark rounded-3 d-flex align-items-start gap-2 mb-4">
                <i class="bi bi-shield-check text-primary fs-4 flex-shrink-0 mt-1"></i>
                <div class="small">
                    <strong>ការការពារបញ្ហា Rate Limit (Multi-Token Switching Mechanism):</strong>
                    NBC Bakong Open API មានកម្រិតកំណត់ចំនួនសំណើប្រចាំថ្ងៃ (Rate Limit: 100 requests/day ក្នុងមួយ Token)។ ប្រព័ន្ធ Workshop OS គាំទ្រការបញ្ចូល <strong>Pool of Tokens</strong>។ នៅពេល Token ណាមួយជាប់ Limit (ErrorCode 17 ឬ HTTP 429) ប្រព័ន្ធនឹង <strong>ប្តូរទៅប្រើ Token បន្ទាប់ដោយស្វ័យប្រវត្តិ</strong> ដើម្បីធានាថាការស្កេនទូទាត់ប្រាក់ និងបើកដំណើរការសិក្ខាសាលាមិនមានការរអាក់រអួលឡើយ។
                </div>
            </div>

            <!-- Main 4 Fields Row (Exact Match to User UI Reference) -->
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Bakong API Base URL</label>
                    <input type="text" class="form-control" name="bakong_api_base_url" id="bakong_api_base_url" 
                           value="<?php echo e($settingsMap['bakong_api_base_url'] ?? 'https://api-bakong.nbc.gov.kh'); ?>" required>
                    <div class="form-text small">Production: <code>https://api-bakong.nbc.gov.kh</code></div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-0">
                            Bakong API Token <span class="badge <?php echo $tokenCount > 1 ? 'bg-success' : 'bg-secondary'; ?> rounded-pill ms-1" id="bakong-token-count-badge"><?php echo $tokenCount; ?></span>
                        </label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" id="manage-bakong-tokens-btn" style="font-size: 12px;" data-actual-token="<?php echo e($actualBakongTokens); ?>">
                            <i class="bi bi-plus-circle me-1"></i>Manage List
                        </button>
                    </div>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace" id="bakong_api_token" name="bakong_api_token" 
                               value="<?php echo e($actualBakongTokens); ?>" placeholder="Paste Bakong API token here..." autocomplete="off">
                        <?php if ($hasBakongToken): ?>
                            <span class="input-group-text bg-white text-success border-start-0" id="token-saved-icon" title="Bakong token saved">
                                <i class="bi bi-check-circle-fill"></i>
                            </span>
                        <?php endif; ?>
                        <button class="btn btn-outline-secondary bg-white text-muted" type="button" id="toggle-token-visibility" title="Show / Hide token">
                            <i class="bi bi-eye" id="toggle-token-icon"></i>
                        </button>
                    </div>
                    <div class="form-text small">ដាក់បានច្រើន Token ដោយខណ្ឌដោយសញ្ញាក្បៀស (,)</div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Bakong Account ID</label>
                    <input type="text" class="form-control font-monospace" name="bakong_account_id" 
                           value="<?php echo e($settingsMap['bakong_account_id'] ?? 'kosol@abaa'); ?>" placeholder="kosol@abaa" required>
                    <div class="form-text small">គណនី Bakong ទទួលប្រាក់ (ឧទាហរណ៍: <code>kosol@abaa</code>)</div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Merchant Name</label>
                    <input type="text" class="form-control" name="bakong_merchant_name" 
                           value="<?php echo e($settingsMap['bakong_merchant_name'] ?? 'Chan Kosol'); ?>" placeholder="Chan Kosol" required>
                    <div class="form-text small">ឈ្មោះពាណិជ្ជករដែលបង្ហាញលើ KHQR</div>
                </div>
            </div>

            <!-- Advanced / Secondary Settings -->
            <div class="row g-3 pt-2 border-top">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted mb-1">Merchant City</label>
                    <input type="text" class="form-control form-control-sm" name="bakong_merchant_city" 
                           value="<?php echo e($settingsMap['bakong_merchant_city'] ?? 'PHNOM PENH'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-muted mb-1">MCC (Merchant Category Code)</label>
                    <input type="text" class="form-control form-control-sm" name="bakong_mcc" 
                           value="<?php echo e($settingsMap['bakong_mcc'] ?? '5999'); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-muted mb-1">Static KHQR Text Override (ទុកនៅទំនេរសម្រាប់ Dynamic)</label>
                    <input type="text" class="form-control form-control-sm" name="bakong_static_qr_text" 
                           value="<?php echo e($settingsMap['bakong_static_qr_text'] ?? ''); ?>" placeholder="ទុកនៅទំនេរ ប្រសិនបើចង់ឱ្យប្រព័ន្ធបង្កើតតាមចំនួនទឹកប្រាក់ជាក់ស្តែង">
                </div>
            </div>

            <!-- Connection Test Button and Feedback Area -->
            <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm px-3 fw-semibold rounded-3" id="btnTestBakongConnection">
                    <i class="bi bi-lightning-charge me-1"></i> ធ្វើតេស្តការតភ្ជាប់ Bakong API (Test Connection)
                </button>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i> ចុច Test ដើម្បីផ្ទៀងផ្ទាត់ Token ទាំងអស់ក្នុង Pool ជាមួយ NBC Server
                </div>
            </div>

            <!-- Live Test Feedback Box -->
            <div id="bakongTestFeedback" class="mt-3 d-none"></div>
        </div>
    </div>

    <!-- Form Submit Bar -->
    <div class="d-flex justify-content-end gap-2 pb-5">
        <a href="<?php echo APP_URL; ?>/platform" class="btn btn-light px-4">បោះបង់</a>
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
            <i class="bi bi-check2-circle me-1"></i> រក្សាទុកការកំណត់ប្រព័ន្ធ (Save All Settings)
        </button>
    </div>
</form>

<!-- Modal for Managing Multiple Bakong Tokens (Pool List) -->
<div class="modal fade" id="bakongTokensModal" tabindex="-1" aria-labelledby="bakongTokensModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom border-light py-3">
                <h5 class="modal-title fw-bold text-dark" id="bakongTokensModalLabel">
                    <i class="bi bi-key-fill text-primary me-2"></i>Manage Bakong API Tokens
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small mb-3">
                    Add multiple Bakong API Tokens here. The system will automatically cycle to the next token if one hits the 100 daily limit to avoid limitation errors.
                </p>
                <div id="bakong-tokens-container">
                    <!-- Dynamic token inputs inserted here by JS -->
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2" id="add-token-row-btn">
                    <i class="bi bi-plus-circle me-1"></i>Add Another Token
                </button>
            </div>
            <div class="modal-footer border-top border-light py-3">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="save-bakong-tokens-btn">Save List</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tokenInput = document.getElementById('bakong_api_token');
    var badge = document.getElementById('bakong-token-count-badge');
    var manageBtn = document.getElementById('manage-bakong-tokens-btn');
    var container = document.getElementById('bakong-tokens-container');
    var addBtn = document.getElementById('add-token-row-btn');
    var saveBtn = document.getElementById('save-bakong-tokens-btn');
    var toggleEye = document.getElementById('toggle-token-visibility');
    var toggleIcon = document.getElementById('toggle-token-icon');
    var testBtn = document.getElementById('btnTestBakongConnection');
    var testFeedback = document.getElementById('bakongTestFeedback');

    var bootstrapModal = null;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrapModal = new bootstrap.Modal(document.getElementById('bakongTokensModal'));
    }

    // Toggle eye password/text visibility
    if (toggleEye && tokenInput) {
        toggleEye.addEventListener('click', function() {
            if (tokenInput.type === 'password') {
                tokenInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                tokenInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        });
    }

    function updateBadgeCount() {
        if (!tokenInput || !badge || !manageBtn) return;
        var val = manageBtn.getAttribute('data-actual-token') || tokenInput.value || '';
        val = val.trim();
        if (val === '') {
            badge.textContent = '0';
            badge.className = 'badge bg-secondary rounded-pill ms-1';
        } else {
            var count = val.split(/[\s,]+/).filter(function(t) { return t.trim() !== ''; }).length;
            badge.textContent = count;
            badge.className = count > 1 ? 'badge bg-success rounded-pill ms-1' : 'badge bg-secondary rounded-pill ms-1';
        }
    }

    updateBadgeCount();

    if (manageBtn) {
        manageBtn.addEventListener('click', function() {
            container.innerHTML = '';
            var val = manageBtn.getAttribute('data-actual-token') || tokenInput.value || '';
            val = val.trim();
            var tokens = val.split(/[\s,]+/).map(function(t) { return t.trim(); }).filter(function(t) { return t !== ''; });

            if (tokens.length === 0) {
                tokens.push('');
            }

            tokens.forEach(function(token) {
                addTokenRow(token);
            });

            if (bootstrapModal) {
                bootstrapModal.show();
            } else {
                var modalEl = document.getElementById('bakongTokensModal');
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        });
    }

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addTokenRow('');
        });
    }

    function addTokenRow(val) {
        var row = document.createElement('div');
        row.className = 'd-flex align-items-center mb-2 token-row';
        row.innerHTML = `
            <input type="text" class="form-control rounded-3 me-2 token-input-value font-monospace" placeholder="Paste Bakong API Token here..." value="${val}">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1 remove-token-row-btn" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-trash-fill" style="font-size: 14px;"></i>
            </button>
        `;
        container.appendChild(row);

        row.querySelector('.remove-token-row-btn').addEventListener('click', function() {
            row.remove();
            if (container.children.length === 0) {
                addTokenRow('');
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            var inputs = container.querySelectorAll('.token-input-value');
            var values = [];
            inputs.forEach(function(input) {
                var v = input.value.trim();
                if (v !== '') {
                    values.push(v);
                }
            });

            var merged = values.join(', ');
            tokenInput.value = merged;
            manageBtn.setAttribute('data-actual-token', merged);
            updateBadgeCount();

            if (bootstrapModal) {
                bootstrapModal.hide();
            } else {
                var modalEl = document.getElementById('bakongTokensModal');
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
    }

    // Test Bakong Connection AJAX
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            var originalHtml = testBtn.innerHTML;
            testBtn.disabled = true;
            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងធ្វើតេស្តការតភ្ជាប់...';
            testFeedback.className = 'd-none';

            var fd = new FormData();
            fd.append('_csrf_token', '<?php echo csrfToken(); ?>');
            fd.append('bakong_api_token', tokenInput.value);
            fd.append('bakong_api_base_url', document.getElementById('bakong_api_base_url').value);

            fetch('<?php echo APP_URL; ?>/platform/settings/test-bakong', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                testBtn.disabled = false;
                testBtn.innerHTML = originalHtml;
                testFeedback.classList.remove('d-none');

                if (res.success && res.data && res.data.results) {
                    var html = '<div class="card border shadow-sm p-3 rounded-3 bg-light">';
                    html += '<h6 class="fw-bold mb-2"><i class="bi bi-activity text-primary me-2"></i>លទ្ធផលធ្វើតេស្ត Bakong API Token Pool (' + res.data.tokens_count + ' Tokens):</h6>';
                    html += '<ul class="list-group list-group-flush rounded-3">';

                    res.data.results.forEach(function(item) {
                        var badgeClass = 'bg-secondary';
                        var icon = 'bi-info-circle';
                        if (item.status === 'valid') {
                            badgeClass = 'bg-success';
                            icon = 'bi-check-circle-fill';
                        } else if (item.status === 'invalid') {
                            badgeClass = 'bg-danger';
                            icon = 'bi-x-circle-fill';
                        } else if (item.status === 'limit_exceeded') {
                            badgeClass = 'bg-warning text-dark';
                            icon = 'bi-exclamation-triangle-fill';
                        }

                        html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2">';
                        html += '<div><span class="font-monospace fw-bold text-dark me-2">' + item.token + '</span> ' + item.message + '</div>';
                        html += '<span class="badge ' + badgeClass + '"><i class="bi ' + icon + ' me-1"></i>' + item.status + '</span>';
                        html += '</li>';
                    });

                    html += '</ul></div>';
                    testFeedback.innerHTML = html;
                } else {
                    testFeedback.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0">' + (res.message || 'មានបញ្ហាក្នុងការធ្វើតេស្ត') + '</div>';
                }
            })
            .catch(err => {
                testBtn.disabled = false;
                testBtn.innerHTML = originalHtml;
                testFeedback.classList.remove('d-none');
                testFeedback.innerHTML = '<div class="alert alert-danger py-2 px-3 mb-0">មិនអាចតភ្ជាប់ទៅកាន់ម៉ាស៊ីនបម្រើបានឡើយ។</div>';
            });
        });
    }
});
</script>
