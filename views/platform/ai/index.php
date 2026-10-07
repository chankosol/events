<?php
// views/platform/ai/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ការគ្រប់គ្រងបញ្ញាសិប្បនិម្មិត (AI Management)</h2>
        <p class="text-muted mb-0">កំណត់រចនាសម្ព័ន្ធ និងគ្រប់គ្រងមុខងារឆ្លាតវៃ AI ទូទាំងប្រព័ន្ធ Workshop OS។</p>
    </div>
    <div>
        <?php if ($aiEnabled === '1'): ?>
            <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i> AI កំពុងដំណើរការ (Active)</span>
        <?php else: ?>
            <span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-dash-circle me-1"></i> AI ត្រូវបានបិទ (Disabled)</span>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-sliders me-2 text-primary"></i> ការកំណត់រចនាសម្ព័ន្ធម៉ាស៊ីន AI (Provider & Model)</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?php echo APP_URL; ?>/platform/ai">
                    <?php echo csrfField(); ?>

                    <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                        <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="ai_enabled" name="ai_enabled" value="1" <?php echo ($aiEnabled === '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="ai_enabled">
                            បើកដំណើរការមុខងារ AI ទូទាំងប្រព័ន្ធ (Enable Global AI Engine)
                        </label>
                        <div class="text-muted small ms-5">អនុញ្ញាតឱ្យស្ថាប័ននានាប្រើប្រាស់មុខងារឆ្លាតវៃក្នុងការរៀបចំសិក្ខាសាលា។</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ក្រុមហ៊ុនផ្តល់សេវា AI (Provider)</label>
                            <select name="ai_provider" class="form-select" id="ai_provider">
                                <option value="openai" <?php echo ($aiProvider === 'openai') ? 'selected' : ''; ?>>OpenAI (ChatGPT)</option>
                                <option value="google" <?php echo ($aiProvider === 'google') ? 'selected' : ''; ?>>Google DeepMind (Gemini)</option>
                                <option value="anthropic" <?php echo ($aiProvider === 'anthropic') ? 'selected' : ''; ?>>Anthropic (Claude)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ម៉ូដែលដំណើរការ (Model)</label>
                            <input type="text" name="ai_model" class="form-control" value="<?php echo e($aiModel); ?>" placeholder="ឧ. gpt-4o-mini, gemini-1.5-flash">
                            <div class="form-text small text-muted">ឧទាហរណ៍៖ gpt-4o-mini, gpt-4o, gemini-1.5-flash, claude-3-5-sonnet</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">លេខកូដសម្ងាត់សេវា (API Key)</label>
                        <div class="input-group">
                            <input type="password" name="ai_api_key" id="ai_api_key" class="form-control font-monospace" value="<?php echo e($aiApiKey); ?>" placeholder="sk-...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="form-text small text-muted">API Key ត្រូវបានរក្សាទុកដោយសុវត្ថិភាព និងមិនបង្ហាញជាសាធារណៈឡើយ។</div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="bi bi-toggles me-1 text-primary"></i> មុខងារឆ្លាតវៃដែលអាចជ្រើសរើស (AI Feature Modules)</h6>

                    <div class="list-group mb-4">
                        <label class="list-group-item d-flex gap-3 align-items-center">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="ai_feature_desc" value="1" <?php echo ($aiFeatDesc === '1') ? 'checked' : ''; ?>>
                            <span>
                                <strong class="d-block">ស្វ័យប្រវត្តបង្កើតអត្ថបទសិក្ខាសាលា (Workshop Content Generator)</strong>
                                <small class="text-muted">ជួយស្ថាប័នសរសេរការពិពណ៌នា កាលវិភាគ និងលក្ខខណ្ឌចូលរួមដោយស្វ័យប្រវត្តិពីប្រធានបទ។</small>
                            </span>
                        </label>

                        <label class="list-group-item d-flex gap-3 align-items-center">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="ai_feature_moderation" value="1" <?php echo ($aiFeatMod === '1') ? 'checked' : ''; ?>>
                            <span>
                                <strong class="d-block">សម្រួល និងតម្រៀបសំណួរផ្ទាល់ Q&A (Smart Q&A Moderation)</strong>
                                <small class="text-muted">ស្វែងរកសំណួរដែលស្ទួនគ្នា (Duplicates) និងសំណួរមិនសមរម្យដោយស្វ័យប្រវត្តិ។</small>
                            </span>
                        </label>

                        <label class="list-group-item d-flex gap-3 align-items-center">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="ai_feature_cert" value="1" <?php echo ($aiFeatCert === '1') ? 'checked' : ''; ?>>
                            <span>
                                <strong class="d-block">ជំនួយតាក់តែងវិញ្ញាបនបត្រ (Certificate Designer Assistant)</strong>
                                <small class="text-muted">ផ្ដល់យោបល់ពាក្យពេចន៍លើវិញ្ញាបនបត្របញ្ជាក់ការសិក្សាជាភាសាខ្មែរ និងអង់គ្លេស។</small>
                            </span>
                        </label>

                        <label class="list-group-item d-flex gap-3 align-items-center">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="ai_feature_email" value="1" <?php echo ($aiFeatEmail === '1') ? 'checked' : ''; ?>>
                            <span>
                                <strong class="d-block">ជំនួយការសរសេរសារជូនដំណឹង (Smart Notifications Writer)</strong>
                                <small class="text-muted">តាក់តែងសារ Email និង SMS រំលឹកសិក្ខាកាមមុនពេលចាប់ផ្តើមកម្មវិធី។</small>
                            </span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-save me-1"></i> រក្សាទុកការកំណត់ AI
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Test Connection Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge text-warning me-2"></i> ធ្វើតេស្តការតភ្ជាប់ (Test Connection)</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">ផ្ទៀងផ្ទាត់ថាតើ API Key និងការកំណត់ AI អាចដំណើរការបានត្រឹមត្រូវឬទេ។</p>
                <form method="POST" action="<?php echo APP_URL; ?>/platform/ai/test">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="ai_api_key" value="<?php echo e($aiApiKey); ?>">
                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold" <?php echo empty($aiApiKey) ? 'disabled' : ''; ?>>
                        <i class="bi bi-broadcast me-1"></i> សាកល្បងតភ្ជាប់ (Ping API)
                    </button>
                    <?php if (empty($aiApiKey)): ?>
                        <div class="form-text text-danger small mt-1">សូមរក្សាទុក API Key ជាមុនសិន។</div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- AI Engine Usage Info -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-speedometer2 text-info me-2"></i> ស្ថិតិ & កូតាប្រើប្រាស់ (AI Engine Status)</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">ស្ថានភាព Engine:</span>
                    <span class="badge <?php echo $aiEnabled === '1' ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $aiEnabled === '1' ? 'Ready (រួចរាល់)' : 'Standby (រង់ចាំ)'; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">ជំនួយការដែលគាំទ្រ:</span>
                    <span class="fw-bold">ភាសាខ្មែរ & English</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">សំណួរ Q&A ក្នុងប្រព័ន្ធ:</span>
                    <span class="fw-bold"><?php echo number_format($totalQuestions); ?> សំណួរ</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">សិក្ខាសាលាទាំងអស់:</span>
                    <span class="fw-bold"><?php echo number_format($totalWorkshops); ?> វគ្គ</span>
                </div>

                <div class="alert alert-info py-2 px-3 small mb-0">
                    <i class="bi bi-lightbulb-fill me-1"></i>
                    AI Engine នឹងជួយសម្រួលដល់ការងារស្វ័យប្រវត្តិកម្មរបស់ម្ចាស់ស្ថាប័ននីមួយៗក្នុងការរៀបចំសិក្ខាសាលាបានលឿនជាងមុន។
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const input = document.getElementById('ai_api_key');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
