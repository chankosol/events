<?php
// views/platform/settings/index.php
$actualBakongTokens = $settingsMap['bakong_api_token'] ?? '';
$hasBakongToken = !empty(trim($actualBakongTokens));
$tokenCount = 0;
if ($hasBakongToken) {
    $tokenCount = count(array_filter(array_map('trim', preg_split('/[\s,]+/', $actualBakongTokens))));
}

$actualBakongAccounts = $settingsMap['bakong_account_id'] ?? 'kosol@abaa';
$bakongAccountsList = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', $actualBakongAccounts))));
$accountCount = count($bakongAccountsList);

$isTgBotEnabled = ($settingsMap['telegram_bot_enabled'] ?? '0') === '1';
$tgBotUsername = $settingsMap['telegram_bot_username'] ?? '';
$tgBotToken = $settingsMap['telegram_bot_token'] ?? '';
$hasTgBot = $isTgBotEnabled && !empty($tgBotUsername) && !empty($tgBotToken);
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
                    <strong>ការការពារបញ្ហា Rate Limit & Daily Limit (Auto-Switching Mechanism):</strong><br>
                    • <strong>Multi-Token Switching:</strong> NBC Bakong Open API មានកម្រិតកំណត់ 100 requests/day ក្នុងមួយ Token។ ប្រព័ន្ធគាំទ្រ Pool of Tokens ដោយប្តូរ Token បន្ទាប់ដោយស្វ័យប្រវត្តិនៅពេលមួយជាប់ Limit (ErrorCode 17 ឬ HTTP 429)។<br>
                    • <strong>Multi-Account Auto-Switch:</strong> អាចបញ្ចូល Bakong Account ID ច្រើន (ខណ្ឌដោយសញ្ញាក្បៀស <code>,</code>)។ ប្រព័ន្ធនឹងបង្វិលគណនីទទួលប្រាក់ជាជុំ (Auto-Switch Account) ដោយស្វ័យប្រវត្តិនូវរាល់ការបង្កើត KHQR ដើម្បីការពារកុំឱ្យគណនីណាមួយពេញ Daily Limit។
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
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">
                        Bakong Account ID <span class="badge <?php echo $accountCount > 1 ? 'bg-success' : 'bg-secondary'; ?> rounded-pill ms-1" id="bakong-account-count-badge"><?php echo $accountCount; ?></span>
                    </label>
                    <input type="text" class="form-control font-monospace" name="bakong_account_id" 
                           value="<?php echo e($settingsMap['bakong_account_id'] ?? 'kosol@abaa'); ?>" placeholder="kosol@abaa, kosol2@abaa" required>
                    <div class="form-text small">ដាក់បានច្រើន Account ខណ្ឌដោយក្បៀស (,) សម្រាប់ Auto-Switch គណនីទទួលប្រាក់ ការពារការជាប់ Daily Limit</div>
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

    <!-- Card 3: Official Telegram Bot Integration (Platform System Ticket Bot) -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="bg-info-subtle text-info p-2 rounded-3">
                    <i class="bi bi-telegram fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        ការកំណត់ Official Telegram Bot (System Ticket Bot)
                    </h5>
                    <small class="text-muted">កំណត់ Bot Token និង Username សម្រាប់ផ្ញើសំបុត្រ និង QR Code ចូលទៅ Telegram សិក្ខាកាមដោយស្វ័យប្រវត្តិ។ ងាយស្រួលផ្លាស់ប្តូរនៅទីនេះដោយផ្ទាល់ដោយមិនបាច់ប៉ះពាល់កូដ!</small>
                </div>
            </div>
            <span class="badge <?php echo $hasTgBot ? 'bg-success' : 'bg-secondary'; ?> px-3 py-2 rounded-pill">
                <i class="bi <?php echo $hasTgBot ? 'bi-check-circle-fill' : 'bi-x-circle'; ?> me-1"></i>
                <?php echo $hasTgBot ? "Bot Active (@{$tgBotUsername})" : 'Bot មិនទាន់ដំណើរការ'; ?>
            </span>
        </div>

        <div class="card-body p-4">
            <!-- Instructions Alert -->
            <div class="alert alert-primary-subtle border-0 rounded-3 p-3 mb-4 d-flex align-items-start gap-3">
                <i class="bi bi-lightbulb-fill text-primary fs-4 mt-1"></i>
                <div class="small">
                    <strong>របៀបបង្កើត និងកំណត់ Bot ក្នុងរយៈពេល ១ នាទី៖</strong>
                    <ol class="mb-0 ps-3 mt-1 text-muted">
                        <li>បើកកម្មវិធី Telegram ហើយស្វែងរកគណនីផ្លូវការ <code>@BotFather</code> រួចផ្ញើពាក្យបញ្ជា <code>/newbot</code></li>
                        <li>ដាក់ឈ្មោះ Bot និង Username (ឧ. <code>my_workshop_ticket_bot</code>) រួចចម្លង <strong>HTTP API Token</strong> យកមកបិទភ្ជាប់ (Paste) ក្នុងប្រអប់ខាងក្រោម</li>
                        <li>ចុចប៊ូតុង <strong>«រក្សាទុកការកំណត់ប្រព័ន្ធ»</strong> និងចុចប៊ូតុង <strong>«ភ្ជាប់ Webhook ទៅ Telegram»</strong> ជាការស្រេច!</li>
                    </ol>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">ស្ថានភាពដំណើរការ Bot</label>
                    <select name="telegram_bot_enabled" class="form-select" id="telegram_bot_enabled">
                        <option value="1" <?php echo ($settingsMap['telegram_bot_enabled'] ?? '') === '1' ? 'selected' : ''; ?>>✓ បើកដំណើរការ (Active)</option>
                        <option value="0" <?php echo ($settingsMap['telegram_bot_enabled'] ?? '') !== '1' ? 'selected' : ''; ?>>○ បិទដំណើរការ (Inactive)</option>
                    </select>
                    <small class="text-muted">ពេលបិទ ប្រព័ន្ធនឹងប្រើតំណភ្ជាប់ Telegram Share ធម្មតាជំនួសវិញ។</small>
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Telegram Bot Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted">@</span>
                        <input type="text" name="telegram_bot_username" id="telegram_bot_username" class="form-control" placeholder="my_workshop_ticket_bot" value="<?php echo e(ltrim($settingsMap['telegram_bot_username'] ?? '', '@')); ?>">
                        <?php if (!empty($settingsMap['telegram_bot_username'])): ?>
                            <a href="https://t.me/<?php echo e(ltrim($settingsMap['telegram_bot_username'], '@')); ?>" target="_blank" class="btn btn-outline-info">
                                <i class="bi bi-box-arrow-up-right me-1"></i> បើក Bot
                            </a>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">Username របស់ Bot ដែលទទួលបានពី @BotFather (មិនបាច់ដាក់សញ្ញា @)។</small>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Telegram Bot API Token (HTTP Token)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                        <input type="password" name="telegram_bot_token" id="telegram_bot_token" class="form-control font-monospace" placeholder="7891234567:AAFxxxxxxxxx-xxxxxxxxxxxx" value="<?php echo e($settingsMap['telegram_bot_token'] ?? ''); ?>">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-telegram-token" title="បង្ហាញ/លាក់ Token">
                            <i class="bi bi-eye" id="toggle-telegram-icon"></i>
                        </button>
                    </div>
                    <small class="text-muted">Token សម្ងាត់សម្រាប់បញ្ជា Bot ផ្ញើសំបុត្រ។ កុំចែករំលែក Token នេះទៅអ្នកដទៃ។</small>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Webhook URL (System Endpoint)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                        <input type="text" class="form-control font-monospace bg-light" id="telegramWebhookUrlInput" readonly value="<?php echo APP_URL; ?>/api/telegram/webhook">
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('telegramWebhookUrlInput').value); alert('បានចម្លង Webhook URL!');">
                            <i class="bi bi-clipboard me-1"></i> ចម្លង URL
                        </button>
                    </div>
                    <small class="text-muted">តំណភ្ជាប់ដែល Telegram បញ្ជូនព័ត៌មានមកប្រព័ន្ធ។ ចុចប៊ូតុងខាងក្រោមដើម្បីភ្ជាប់ Webhook ដោយស្វ័យប្រវត្តិ។</small>
                </div>
            </div>

            <!-- Action buttons for Telegram -->
            <div class="d-flex flex-wrap align-items-center gap-2 mt-4 pt-3 border-top">
                <button type="button" class="btn btn-info text-white fw-bold px-3 py-2 shadow-xs" id="btnSetTelegramWebhook">
                    <i class="bi bi-lightning-charge-fill me-1"></i> ភ្ជាប់ Webhook ទៅ Telegram
                </button>
                <button type="button" class="btn btn-outline-primary fw-bold px-3 py-2" id="btnTestTelegramBot">
                    <i class="bi bi-search me-1"></i> ពិនិត្យស្ថានភាព Bot (Test Connection)
                </button>
                <div class="text-muted small ms-md-2">
                    <i class="bi bi-info-circle me-1"></i> ចុច Test ដើម្បីផ្ទៀងផ្ទាត់ Token ជាមួយ Telegram Server
                </div>
            </div>

            <!-- Feedback box -->
            <div id="telegramTestFeedback" class="mt-3 d-none"></div>
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

    // Telegram Bot Token Toggle Visibility
    var tgTokenInput = document.getElementById('telegram_bot_token');
    var tgToggleBtn = document.getElementById('toggle-telegram-token');
    var tgToggleIcon = document.getElementById('toggle-telegram-icon');
    if (tgToggleBtn && tgTokenInput) {
        tgToggleBtn.addEventListener('click', function() {
            if (tgTokenInput.type === 'password') {
                tgTokenInput.type = 'text';
                tgToggleIcon.className = 'bi bi-eye-slash';
            } else {
                tgTokenInput.type = 'password';
                tgToggleIcon.className = 'bi bi-eye';
            }
        });
    }

    // Telegram Bot Test Connection
    var btnTestTg = document.getElementById('btnTestTelegramBot');
    var tgFeedback = document.getElementById('telegramTestFeedback');
    if (btnTestTg && tgFeedback) {
        btnTestTg.addEventListener('click', function() {
            var token = tgTokenInput ? tgTokenInput.value.trim() : '';
            if (!token) {
                tgFeedback.className = 'alert alert-warning py-2 px-3 mt-3';
                tgFeedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> សូមបញ្ចូល Telegram Bot Token មុនពេលធ្វើតេស្ត។';
                tgFeedback.classList.remove('d-none');
                return;
            }

            var origHtml = btnTestTg.innerHTML;
            btnTestTg.disabled = true;
            btnTestTg.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងពិនិត្យ...';
            tgFeedback.className = 'alert alert-info py-2 px-3 mt-3';
            tgFeedback.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> កំពុងភ្ជាប់ទៅកាន់ Telegram Server...';
            tgFeedback.classList.remove('d-none');

            var formData = new FormData();
            formData.append('telegram_bot_token', token);
            formData.append('_csrf_token', '<?php echo csrfToken(); ?>');

            fetch('<?php echo APP_URL; ?>/platform/settings/test-telegram', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                btnTestTg.disabled = false;
                btnTestTg.innerHTML = origHtml;
                if (res.success) {
                    tgFeedback.className = 'alert alert-success py-2 px-3 mt-3';
                    tgFeedback.innerHTML = '<div class="fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ' + res.message + '</div>' +
                                          '<div class="small text-muted mt-1">Bot ID: ' + (res.data ? res.data.bot_id : '') + ' | Username: ' + (res.data ? res.data.bot_username : '') + '</div>';
                } else {
                    tgFeedback.className = 'alert alert-danger py-2 px-3 mt-3';
                    tgFeedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + (res.message || 'មិនអាចភ្ជាប់បានទេ។');
                }
            })
            .catch(err => {
                btnTestTg.disabled = false;
                btnTestTg.innerHTML = origHtml;
                tgFeedback.className = 'alert alert-danger py-2 px-3 mt-3';
                tgFeedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> មានបញ្ហាក្នុងការភ្ជាប់ សូមព្យាយាមម្តងទៀត។';
            });
        });
    }

    // Telegram Set Webhook
    var btnSetWebhook = document.getElementById('btnSetTelegramWebhook');
    if (btnSetWebhook && tgFeedback) {
        btnSetWebhook.addEventListener('click', function() {
            var token = tgTokenInput ? tgTokenInput.value.trim() : '';
            if (!token) {
                tgFeedback.className = 'alert alert-warning py-2 px-3 mt-3';
                tgFeedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> សូមបញ្ចូល Telegram Bot Token មុនពេលភ្ជាប់ Webhook។';
                tgFeedback.classList.remove('d-none');
                return;
            }

            var origHtml = btnSetWebhook.innerHTML;
            btnSetWebhook.disabled = true;
            btnSetWebhook.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងភ្ជាប់ Webhook...';
            tgFeedback.className = 'alert alert-info py-2 px-3 mt-3';
            tgFeedback.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i> កំពុងផ្ញើស្នើសុំ Webhook ទៅ Telegram API...';
            tgFeedback.classList.remove('d-none');

            var formData = new FormData();
            formData.append('telegram_bot_token', token);
            formData.append('_csrf_token', '<?php echo csrfToken(); ?>');

            fetch('<?php echo APP_URL; ?>/platform/settings/set-telegram-webhook', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(res => {
                btnSetWebhook.disabled = false;
                btnSetWebhook.innerHTML = origHtml;
                if (res.success) {
                    tgFeedback.className = 'alert alert-success py-2 px-3 mt-3';
                    tgFeedback.innerHTML = '<div class="fw-bold"><i class="bi bi-check-circle-fill me-1"></i> ' + res.message.replace(/\n/g, '<br>') + '</div>' +
                                          '<div class="small mt-1">ឥឡូវនេះ Telegram Bot នឹងឆ្លើយតប និងផ្ញើសំបុត្រដោយស្វ័យប្រវត្តិហើយ!</div>';
                } else {
                    tgFeedback.className = 'alert alert-danger py-2 px-3 mt-3';
                    tgFeedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> ' + (res.message || 'មិនអាចភ្ជាប់ Webhook បានទេ។');
                }
            })
            .catch(err => {
                btnSetWebhook.disabled = false;
                btnSetWebhook.innerHTML = origHtml;
                tgFeedback.className = 'alert alert-danger py-2 px-3 mt-3';
                tgFeedback.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> មានបញ្ហាក្នុងការភ្ជាប់ សូមព្យាយាមម្តងទៀត។';
            });
        });
    }
});
</script>
