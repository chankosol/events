<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm border-0">
                <?php if (!empty($workshop['cover_image'])): ?>
                    <img src="<?php echo APP_URL . '/' . htmlspecialchars($workshop['cover_image']); ?>" class="card-img-top" alt="Cover Image" style="height: 220px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-header bg-white p-4 border-bottom">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary">ទម្រង់ចុះឈ្មោះ</span>
                        <?php if ($workshop['payment_mode'] === 'free'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-gift me-1"></i> ឥតគិតថ្លៃ
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="fw-bold mb-1"><?= htmlspecialchars($workshop['name']) ?></h3>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar-event me-1"></i> កាលបរិច្ឆេទ: <?= formatDate($workshop['start_date']) ?>
                        <?php if ($workshop['end_date'] && $workshop['end_date'] !== $workshop['start_date']): ?> ដល់ <?= formatDate($workshop['end_date']) ?><?php endif; ?>
                        &bull; <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($workshop['venue'] ?: 'ទីតាំងរៀបចំ') ?>
                    </p>
                </div>
                <div class="card-body p-4">
                    <?php if (Session::has('error')): ?>
                        <div class="alert alert-danger py-2 small"><?= htmlspecialchars(Session::flash('error')) ?></div>
                    <?php endif; ?>

                    <?php
                    $isFreeMode = ($workshop['payment_mode'] === 'free');
                    // Only show ticket selection if there are more than 1 ticket to choose from
                    $showTicketSelection = (count($tickets) > 1);
                    $stepIdx = 1;
                    $khmerNumbers = [1 => '១', 2 => '២', 3 => '៣', 4 => '៤', 5 => '៥'];
                    ?>

                    <form action="<?= APP_URL ?>/event/<?= htmlspecialchars($workshop['slug']) ?>/register" method="POST" enctype="multipart/form-data" id="registrationForm">
                        <?= csrfField() ?>
                        
                        <?php 
                        $inviteParam = htmlspecialchars($_GET['invite'] ?? $_GET['direct'] ?? $_POST['invite'] ?? '');
                        if (!empty($inviteParam)): 
                        ?>
                            <input type="hidden" name="invite" value="<?= $inviteParam ?>">
                            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-4 rounded-3 border-0 shadow-xs">
                                <i class="bi bi-patch-check-fill text-success fs-4 me-2"></i>
                                <div>
                                    <strong class="d-block text-dark">តំណភ្ជាប់ពិសេស</strong>
                                    <small class="text-muted">ទទួលបានការបញ្ជាក់ និងកាត QR ស្កេនភ្លាមៗ។</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($directTicket['_is_direct_private'])): ?>
                            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-4 rounded-3 border-0 shadow-xs">
                                <i class="bi bi-shield-check text-success fs-3 me-2"></i>
                                <div>
                                    <strong class="d-block text-dark">តំណភ្ជាប់សំបុត្រពិសេស</strong>
                                    <small class="text-muted">អ្នកកំពុងចុះឈ្មោះសំបុត្រ «<?= htmlspecialchars($directTicket['name']) ?>» តាមរយៈតំណភ្ជាប់ផ្ទាល់។</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Step 1: Attendee Information -->
                        <div id="step1Section">

                        <?php if ($showTicketSelection): ?>
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-ticket-perforated me-1"></i> <?= $khmerNumbers[$stepIdx++] ?>. ប្រភេទសំបុត្រ
                            </h5>
                            <div class="list-group mb-4">
                                <?php foreach ($tickets as $ticket): ?>
                                <?php 
                                $isTicketChecked = (($old['ticket_id'] ?? '') == $ticket['id']) || (!empty($directTicketId) && $directTicketId == $ticket['id']) || count($tickets) === 1;
                                ?>
                                <label class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <input class="form-check-input me-2" type="radio" name="ticket_id" value="<?= $ticket['id'] ?>" <?= $isTicketChecked ? 'checked' : '' ?> required>
                                        <span class="fw-bold"><?= htmlspecialchars($ticket['name']) ?></span>
                                        <?php if ($ticket['description']): ?>
                                            <small class="d-block text-muted ps-4"><?= htmlspecialchars($ticket['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-primary fs-6">
                                        <?= (float)$ticket['price'] == 0 ? 'ឥតគិតថ្លៃ' : '$' . number_format($ticket['price'], 2) ?>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                                <?php if (isset($errors['ticket_id'])): ?>
                                    <div class="text-danger small mt-1"><?= $errors['ticket_id'] ?></div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($tickets)): ?>
                                <input type="hidden" name="ticket_id" id="hiddenTicketId" value="<?= htmlspecialchars($tickets[0]['id']) ?>" data-name="<?= htmlspecialchars($tickets[0]['name']) ?>" data-price="<?= (float)$tickets[0]['price'] ?>">
                                <?php if ((float)$tickets[0]['price'] > 0): ?>
                                    <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 px-3 mb-3 rounded-3">
                                        <span class="small text-muted"><i class="bi bi-ticket-perforated me-1 text-primary"></i> សំបុត្រ: <strong class="text-dark"><?= htmlspecialchars($tickets[0]['name']) ?></strong></span>
                                        <span class="badge bg-primary fs-6">$<?= number_format($tickets[0]['price'], 2) ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <h5 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person me-1"></i> <?= $khmerNumbers[$stepIdx++] ?>. ព័ត៌មានសិក្ខាកាម
                        </h5>

                        <!-- 1. Full Name (Mandatory) -->
                        <div class="mb-3">
                            <label class="form-label small fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="ឧ. សុខ ពិសិដ្ឋ" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?><div class="text-danger small mt-1"><?= $errors['name'] ?></div><?php endif; ?>
                        </div>

                        <!-- 2. Phone Number (Configurable, default shown & required) -->
                        <?php if (!empty($formConfig['show_phone'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                លេខទូរស័ព្ទ 
                                <?= !empty($formConfig['require_phone']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <input type="tel" name="phone" class="form-control" placeholder="ឧ. 012 345 678" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" <?= !empty($formConfig['require_phone']) ? 'required' : '' ?>>
                            <div class="form-text small text-muted">សម្រាប់ទទួលសារបញ្ជាក់ និងកូដ QR</div>
                            <?php if (isset($errors['phone'])): ?><div class="text-danger small mt-1"><?= $errors['phone'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 3. Email (Configurable, placed AFTER Phone, OPTIONAL by default) -->
                        <?php if (!empty($formConfig['show_email'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                អ៊ីមែល 
                                <?= !empty($formConfig['require_email']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>" <?= !empty($formConfig['require_email']) ? 'required' : '' ?>>
                            <div class="form-text small text-muted">
                                <?= !empty($formConfig['require_email']) ? 'សម្រាប់ផ្ញើសំបុត្រ និងកូដ QR ជូន។' : 'សម្រាប់ទទួលសំបុត្រតាមអ៊ីមែល (ប្រសិនបើមាន)។' ?>
                            </div>
                            <?php if (isset($errors['email'])): ?><div class="text-danger small mt-1"><?= $errors['email'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 4. Gender (Optional/Configurable) -->
                        <?php if (!empty($formConfig['show_gender'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                ភេទ 
                                <?= !empty($formConfig['require_gender']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <select name="gender" class="form-select" <?= !empty($formConfig['require_gender']) ? 'required' : '' ?>>
                                <option value="">-- ជ្រើសរើស --</option>
                                <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>ប្រុស</option>
                                <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>ស្រី</option>
                                <option value="other" <?= ($old['gender'] ?? '') === 'other' ? 'selected' : '' ?>>ផ្សេងទៀត</option>
                            </select>
                            <?php if (isset($errors['gender'])): ?><div class="text-danger small mt-1"><?= $errors['gender'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 5. Province (Optional/Configurable) -->
                        <?php if (!empty($formConfig['show_province'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                រាជធានី-ខេត្ត 
                                <?= !empty($formConfig['require_province']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <select name="province" class="form-select" <?= !empty($formConfig['require_province']) ? 'required' : '' ?>>
                                <option value="">-- ជ្រើសរើស --</option>
                                <?php 
                                $provList = function_exists('cambodiaProvinces') ? cambodiaProvinces() : [];
                                foreach ($provList as $prov): 
                                ?>
                                    <option value="<?= htmlspecialchars($prov) ?>" <?= ($old['province'] ?? '') === $prov ? 'selected' : '' ?>><?= htmlspecialchars($prov) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['province'])): ?><div class="text-danger small mt-1"><?= $errors['province'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 6. Company / Institution (Optional/Configurable) -->
                        <?php if (!empty($formConfig['show_company'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                ស្ថាប័ន 
                                <?= !empty($formConfig['require_company']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <input type="text" name="company" class="form-control" placeholder="ឧ. ក្រុមហ៊ុន ឬស្ថាប័ន" value="<?= htmlspecialchars($old['company'] ?? '') ?>" <?= !empty($formConfig['require_company']) ? 'required' : '' ?>>
                            <?php if (isset($errors['company'])): ?><div class="text-danger small mt-1"><?= $errors['company'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 7. Position / Role (Optional/Configurable) -->
                        <?php if (!empty($formConfig['show_position'])): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                តួនាទី 
                                <?= !empty($formConfig['require_position']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <input type="text" name="position" class="form-control" placeholder="ឧ. ប្រធានផ្នែក, អ្នកគ្រប់គ្រង" value="<?= htmlspecialchars($old['position'] ?? '') ?>" <?= !empty($formConfig['require_position']) ? 'required' : '' ?>>
                            <?php if (isset($errors['position'])): ?><div class="text-danger small mt-1"><?= $errors['position'] ?></div><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- 9. Face Pre-registration Scan (if enabled in form config) -->
                        <?php if (!empty($formConfig['show_face_enroll'])): ?>
                        <div class="mb-4 mt-2" id="faceEnrollSection">
                            <h5 class="fw-bold text-primary mb-2">
                                <i class="bi bi-person-bounding-box me-1"></i>
                                <?= $khmerNumbers[$stepIdx++] ?? $stepIdx ?>. ស្គេនមុខ
                                <?php if (!empty($formConfig['require_face_enroll'])): ?>
                                    <span class="text-danger">*</span>
                                <?php else: ?>
                                    <span class="text-muted fw-normal small">(ស្ម័គ្រចិត្ត)</span>
                                <?php endif; ?>
                            </h5>
                            <p class="text-muted small mb-3">
                                សូមស្គេនមុខរបស់អ្នក ដើម្បីប្រើជំនួសសំបុត្រនៅថ្ងៃចូលរួម។
                            </p>
                            <div class="card border-0 bg-light rounded-4 p-3 shadow-xs">
                                <!-- Video preview with HUD -->
                                <div class="text-center mb-3 position-relative" id="faceEnrollBox" style="background:#0b1120;border-radius:18px;overflow:hidden;min-height:220px;max-width:380px;margin:0 auto;aspect-ratio:4/3;">
                                    <video id="faceEnrollVideo" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);border-radius:18px;"></video>
                                    <canvas id="faceEnrollOverlay" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;transform:scaleX(-1);"></canvas>
                                    <div id="faceEnrollReticle" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:180px;height:240px;border-radius:50%;border:3px dashed rgba(255,255,255,0.45);box-shadow:0 0 0 9999px rgba(11,17,32,0.45);pointer-events:none;transition:all 0.25s ease;"></div>
                                    <div id="faceEnrollFlash" style="position:absolute;inset:0;background:#ffffff;opacity:0;pointer-events:none;transition:opacity 0.15s ease-out;z-index:10;"></div>

                                    <!-- Quality Badge -->
                                    <div class="position-absolute top-0 end-0 m-2" style="z-index: 5;">
                                        <span id="faceEnrollQualityBadge" class="badge rounded-pill bg-dark bg-opacity-75 text-white px-2 py-1 small fw-bold" style="font-size:0.75rem;backdrop-filter:blur(4px);">
                                            គុណភាព៖ 0%
                                        </span>
                                    </div>

                                    <!-- Steady banner -->
                                    <div id="faceEnrollSteadyBanner" class="position-absolute bottom-0 start-0 end-0 p-2 text-white small fw-bold bg-success bg-opacity-90 d-none" style="z-index:5;backdrop-filter:blur(2px);">
                                        <i class="bi bi-camera-fill me-1"></i> ស្ងៀម... កំពុងថតស្វ័យប្រវត្តិ
                                    </div>
                                </div>

                                <!-- Guidance Box -->
                                <div id="faceEnrollGuidanceBox" class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-center gap-2 mb-2 text-center fw-medium shadow-xs" style="border-radius: 12px; min-height: 44px; max-width: 380px; margin: 0 auto;">
                                    <i id="faceEnrollGuidanceIcon" class="bi bi-emoji-smile fs-5 text-primary flex-shrink-0"></i>
                                    <span id="faceEnrollGuidanceText">សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា</span>
                                </div>

                                <!-- Auto-Capture Progress Bar -->
                                <div class="progress mb-3" style="height: 6px; border-radius: 3px; max-width: 380px; margin: 0 auto;" id="faceEnrollProgressContainer">
                                    <div id="faceEnrollProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                </div>

                                <!-- Captured preview -->
                                <div id="faceEnrollPreview" class="text-center mb-3 d-none">
                                    <div class="position-relative d-inline-block">
                                        <img id="faceEnrollPhoto" src="" alt="Captured Face" class="rounded-4 border border-3 border-success shadow-sm" style="max-height: 180px; max-width: 180px; object-fit: cover;">
                                        <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-1 px-2 shadow">
                                            <i class="bi bi-check-lg fw-bold"></i>
                                        </span>
                                    </div>
                                    <div class="mt-2 text-success small fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> ស្គេនមុខបានជោគជ័យ!
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" id="btnCaptureFace" class="btn btn-outline-primary btn-sm px-3 fw-bold" onclick="forceCapturePublicFace()" disabled>
                                        <i class="bi bi-camera me-1"></i> ថតឥឡូវនេះ
                                    </button>
                                    <button type="button" id="btnRetakeFace" class="btn btn-outline-secondary btn-sm px-3 d-none" onclick="retakePublicFace()">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> ថតឡើងវិញ
                                    </button>
                                </div>

                                <!-- Hidden fields -->
                                <input type="hidden" name="face_descriptor" id="faceDescriptorInput">
                                <input type="hidden" name="face_photo" id="facePhotoInput">
                                <?php if (!empty($formConfig['require_face_enroll'])): ?>
                                <input type="hidden" id="faceEnrollRequired" value="1">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 8. Additional Custom Fields (filtered to prevent duplicates with standard fields) -->
                        <?php 
                        $extraCustomFields = [];
                        if (!empty($fields)) {
                            foreach ($fields as $cf) {
                                $lbl = $cf['field_label'];
                                if (str_contains($lbl, 'ក្រុមហ៊ុន') || str_contains($lbl, 'ស្ថាប័ន')) continue;
                                if (str_contains($lbl, 'តួនាទី') || str_contains($lbl, 'មុខតំណែង')) continue;
                                if (str_contains($lbl, 'ទូរស័ព្ទ')) continue;
                                if (str_contains($lbl, 'អ៊ីមែល') || stripos($lbl, 'email') !== false) continue;
                                if (str_contains($lbl, 'ភេទ')) continue;
                                if (str_contains($lbl, 'ខេត្ត') || str_contains($lbl, 'រាជធានី')) continue;
                                $extraCustomFields[] = $cf;
                            }
                        }
                        ?>
                        <?php if (!empty($extraCustomFields)): ?>
                        <h5 class="fw-bold text-primary mb-3 mt-4">
                            <i class="bi bi-input-cursor-text me-1"></i> <?= $khmerNumbers[$stepIdx++] ?? ($stepIdx++) ?>. ព័ត៌មានបន្ថែម
                        </h5>
                        <?php foreach ($extraCustomFields as $field): ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold"><?= htmlspecialchars($field['field_label']) ?> <?= $field['is_required'] ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?></label>
                                <?php if ($field['field_type'] === 'text'): ?>
                                    <input type="text" name="field_<?= $field['id'] ?>" class="form-control" <?= $field['is_required'] ? 'required' : '' ?>>
                                <?php elseif ($field['field_type'] === 'textarea'): ?>
                                    <textarea name="field_<?= $field['id'] ?>" class="form-control" rows="2" <?= $field['is_required'] ? 'required' : '' ?>></textarea>
                                <?php elseif ($field['field_type'] === 'dropdown'): 
                                    $options = json_decode($field['field_options'] ?? '[]', true) ?? []; ?>
                                    <select name="field_<?= $field['id'] ?>" class="form-select" <?= $field['is_required'] ? 'required' : '' ?>>
                                        <option value="">ជ្រើសរើស...</option>
                                        <?php foreach ($options as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>

                            <!-- Step 1 Button -->
                            <div class="d-grid mt-4">
                                <?php if ($isFreeMode): ?>
                                    <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" id="btnSubmitFree">
                                        <i class="bi bi-check-circle me-1"></i> ចុះឈ្មោះឥឡូវនេះ
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" id="btnGoToPayment" onclick="goToPaymentStep()">
                                        <i class="bi bi-arrow-right-circle me-1"></i> បន្ទាប់
                                    </button>
                                <?php endif; ?>
                            </div>
                        
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-link text-decoration-none text-muted small" onclick="openFindTicketModal()" style="font-size: 12px;">
                                    <i class="bi bi-search me-1 text-primary"></i> ធ្លាប់បានចុះឈ្មោះរួចហើយ? <strong>ស្វែងរកសំបុត្រ ឬ QR Code</strong>
                                </button>
                            </div>
                        </div> <!-- End #step1Section -->

                        <?php if (!$isFreeMode): ?>
                        <!-- Step 2: Payment Section (Ultra-clean, uncluttered) -->
                        <div id="step2Section" class="d-none">
                            <?php
                            $bSettings = $businessSettings ?? [];
                            $bProvider = $bSettings['payment_provider'] ?? 'both';
                            $bBakongAccount = trim($bSettings['bakong_account_id'] ?? '');
                            $bBakongMerchant = trim($bSettings['merchant_name'] ?? '');
                            $bBakongStatic = trim($bSettings['bakong_static_qr_text'] ?? '');

                            $hasBakongGateway = (!empty($bBakongAccount) && in_array($bProvider, ['both', 'bakong'], true));

                            // If business configured Bakong only, don't show manual methods
                            $displayMethods = ($bProvider === 'bakong' && $hasBakongGateway) ? [] : $paymentMethods;
                            $totalChoices = ($hasBakongGateway ? 1 : 0) + count($displayMethods);

                            // Active payment choice
                            $activeChoice = 'bakong';
                            if ($bProvider === 'bakong' && $hasBakongGateway) {
                                $activeChoice = 'bakong';
                            } elseif ($hasBakongGateway) {
                                $activeChoice = 'bakong';
                            } elseif (!empty($displayMethods)) {
                                $activeChoice = (string)$displayMethods[0]['id'];
                            }

                            // Calculate ticket price
                            $ticketPrice = isset($tickets[0]) ? (float)$tickets[0]['price'] : (float)($workshop['ticket_price'] ?? 0);
                            $ticketCurrency = $tickets[0]['currency'] ?? 'USD';

                            // Generate dynamic Bakong KHQR
                            $bakongQrImgUrl = '';
                            $bakongMd5 = '';
                            if ($hasBakongGateway) {
                                $invCode = 'REG' . $workshop['id'] . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
                                $khqrData = function_exists('bakong_create_khqr') ? bakong_create_khqr($invCode, $ticketPrice > 0 ? $ticketPrice : 1.0, $ticketCurrency, $bBakongAccount, $bBakongMerchant) : ['ok' => false];
                                $bakongQrString = !empty($khqrData['qr_text']) ? $khqrData['qr_text'] : $bBakongStatic;
                                $bakongMd5 = $khqrData['md5'] ?? ($bakongQrString ? md5($bakongQrString) : '');
                                if (empty($bakongQrString)) {
                                    $bakongQrString = 'https://bakong.nbc.gov.kh/pay?account=' . urlencode($bBakongAccount) . '&amount=' . $ticketPrice;
                                    $bakongMd5 = md5($bakongQrString);
                                }
                                $bakongQrImgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($bakongQrString);
                            }
                            ?>

                            <!-- Header -->
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-wallet2 text-primary fs-5"></i>
                                    <h5 class="fw-bold text-dark mb-0" style="font-size: 15px;">ការទូទាត់ប្រាក់</h5>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                    ជំហានទី ២
                                </span>
                            </div>

                            <!-- Attendee & Ticket Summary Strip -->
                            <div class="bg-light rounded-3 p-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 12px;">
                                    <span class="text-muted">សិក្ខាកាម៖ <strong class="text-dark" id="summaryAttendeeName">-</strong></span>
                                    <span class="text-muted">សំបុត្រ៖ <strong class="text-dark" id="summaryTicketName"><?= htmlspecialchars($tickets[0]['name'] ?? 'សំបុត្រ') ?></strong></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="text-muted small fw-semibold">ទឹកប្រាក់ត្រូវបង់៖</span>
                                    <span class="fw-bold text-primary fs-4" id="summaryTicketPriceDisplay">
                                        <?= isset($tickets[0]) ? ((float)$tickets[0]['price'] == 0 ? 'ឥតគិតថ្លៃ' : '$' . number_format($tickets[0]['price'], 2)) : '$0.00' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Hidden field for active payment choice & auto-confirm -->
                            <input type="hidden" name="payment_method_choice" id="selectedPaymentMethodChoice" value="<?= htmlspecialchars($activeChoice) ?>">
                            <input type="hidden" name="payment_method_id" id="selectedPaymentMethodId" value="<?= htmlspecialchars($activeChoice === 'bakong' ? '' : $activeChoice) ?>">
                            <input type="hidden" name="bakong_md5" id="bakongMd5Input" value="<?= htmlspecialchars($bakongMd5) ?>">
                            <input type="hidden" name="bakong_invoice" id="bakongInvInput" value="<?= htmlspecialchars($invCode ?? '') ?>">
                            <input type="hidden" name="payment_confirmed" id="paymentConfirmedInput" value="0">

                            <!-- Choice Tiles ONLY if more than 1 payment option -->
                            <?php if ($totalChoices > 1): ?>
                                <label class="form-label text-muted small mb-2 d-flex align-items-center gap-1.5" style="font-size: 12px;">
                                    <i class="bi bi-credit-card text-primary"></i> ជ្រើសរើសមធ្យោបាយទូទាត់៖
                                </label>
                                <div class="row g-2 mb-3" id="paymentMethodSelectors">
                                    <?php if ($hasBakongGateway): ?>
                                        <div class="col-sm-6">
                                            <div class="payment-method-tile card p-2.5 rounded-3 border-2 h-100 cursor-pointer <?= $activeChoice === 'bakong' ? 'border-danger bg-danger-subtle shadow-xs' : 'border-secondary-subtle bg-white' ?>"
                                                 id="pm_tile_bakong"
                                                 onclick="selectPaymentMethod('bakong')"
                                                 style="cursor: pointer; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="_pm_radio" id="pm_radio_bakong" value="bakong" <?= $activeChoice === 'bakong' ? 'checked' : '' ?> onchange="selectPaymentMethod('bakong')">
                                                    </div>
                                                    <div class="rounded border p-1 bg-white text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                                        <i class="bi bi-qr-code fs-5"></i>
                                                    </div>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark text-truncate small" style="font-size: 12px;">Bakong KHQR</span>
                                                            <span class="badge bg-danger text-white py-0.5 px-1.5" style="font-size: 9px;">KHQR</span>
                                                        </div>
                                                        <div class="text-muted small text-truncate" style="font-size: 11px;">
                                                            <?= htmlspecialchars($bBakongMerchant ?: 'Bakong Open API') ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php foreach ($displayMethods as $pm): ?>
                                        <div class="col-sm-6">
                                            <div class="payment-method-tile card p-2.5 rounded-3 border-2 h-100 cursor-pointer <?= $activeChoice === (string)$pm['id'] ? 'border-primary bg-primary-subtle shadow-xs' : 'border-secondary-subtle bg-white' ?>"
                                                 id="pm_tile_<?= $pm['id'] ?>"
                                                 onclick="selectPaymentMethod('<?= $pm['id'] ?>')"
                                                 style="cursor: pointer; transition: all 0.2s ease;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="form-check m-0">
                                                        <input class="form-check-input" type="radio" name="_pm_radio" id="pm_radio_<?= $pm['id'] ?>" value="<?= $pm['id'] ?>" <?= $activeChoice === (string)$pm['id'] ? 'checked' : '' ?> onchange="selectPaymentMethod('<?= $pm['id'] ?>')">
                                                    </div>
                                                    <?php if (!empty($pm['qr_image_path'])): ?>
                                                        <img src="<?= APP_URL . '/' . htmlspecialchars($pm['qr_image_path']) ?>" class="rounded border p-0.5 bg-white flex-shrink-0" style="width: 36px; height: 36px; object-fit: contain;" alt="QR">
                                                    <?php else: ?>
                                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 36px; height: 36px;">
                                                            <i class="bi bi-bank fs-6"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="flex-grow-1 overflow-hidden">
                                                        <div class="d-flex align-items-center gap-1.5">
                                                            <span class="fw-bold text-dark text-truncate small" style="font-size: 12px;"><?= htmlspecialchars($pm['name']) ?></span>
                                                            <?php if (!empty($pm['is_default'])): ?>
                                                                <span class="badge bg-warning text-dark border border-warning-subtle py-0.5 px-1.5" style="font-size: 9px;">ចម្បង</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-muted small text-truncate" style="font-size: 11px;">
                                                            <?= htmlspecialchars($pm['bank'] ?: 'ធនាគារ') ?> (<?= htmlspecialchars($pm['currency'] ?: 'USD') ?>)
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Payment Detail Panels -->

                            <!-- 1. Bakong KHQR Panel -->
                            <?php if ($hasBakongGateway): ?>
                                <div class="pm-detail-panel card border-0 shadow-sm rounded-4 overflow-hidden mb-3 <?= $activeChoice === 'bakong' ? '' : 'd-none' ?>" id="pm_panel_bakong">
                                    <div class="px-3 py-2 text-white" style="background: linear-gradient(135deg, #e11924 0%, #b80010 100%);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-white text-danger fw-bold px-2 py-0.5" style="letter-spacing: 1px; font-size: 11px;">KHQR</span>
                                            <span class="text-white small fw-semibold" style="font-size: 11px;">
                                                <i class="bi bi-shield-check me-1"></i> Bakong NBC Cambodia
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3 text-center bg-white">
                                        <div class="mb-2">
                                            <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;"><?= htmlspecialchars($bBakongMerchant ?: 'Chan Kosol') ?></h6>
                                            <div class="text-muted small font-monospace" style="font-size: 12px;"><?= htmlspecialchars($bBakongAccount) ?></div>
                                        </div>

                                        <!-- QR Display -->
                                        <div class="d-inline-block position-relative p-2 bg-white border border-2 border-danger rounded-3 shadow-xs my-1">
                                            <img id="bakongQrImageEl" src="<?= $bakongQrImgUrl ?>" alt="Bakong KHQR" style="width: 170px; height: 170px; display: block;" class="img-fluid">
                                            <div class="position-absolute top-50 start-50 translate-middle bg-white p-1 rounded-circle border border-danger">
                                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 13px;">
                                                    $
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-muted small mt-2 mb-2" style="font-size: 12px;">
                                            ស្កេនទូទាត់តាម <strong>Bakong</strong> ឬ <strong>App ធនាគារ</strong>
                                        </div>

                                        <!-- Live Polling Listening Indicator & Auto Progress -->
                                        <div class="card border border-danger-subtle bg-danger-subtle bg-opacity-10 p-2.5 rounded-3 mx-auto mt-2" style="max-width: 330px;" id="bakongAutoStatusBox">
                                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1.5" id="bakongLiveStatus">
                                                <div class="spinner-grow spinner-grow-sm text-danger" role="status" style="width: 0.75rem; height: 0.75rem;"></div>
                                                <span class="fw-semibold text-danger" style="font-size: 12px;" id="bakongLiveStatusText">កំពុងរង់ចាំការស្កេនទូទាត់ប្រាក់...</span>
                                            </div>
                                            <div class="progress" style="height: 4px; background: rgba(220, 53, 69, 0.15);">
                                                <div id="bakongProgressBar" class="progress-bar bg-danger progress-bar-striped progress-bar-animated" role="progressbar" style="width: 20%; transition: width 0.6s ease;"></div>
                                            </div>
                                            <div class="text-muted small mt-1.5" style="font-size: 11px;">
                                                <i class="bi bi-info-circle me-1"></i>ប្រព័ន្ធនឹងចេញសំបុត្រស្វ័យប្រវត្តភ្លាមៗ មិនបាច់ចុចប៊ូតុងទេ
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- 2. Manual Payment Panels (if allowed) -->
                            <?php foreach ($displayMethods as $pm): ?>
                                <div class="pm-detail-panel card border shadow-sm rounded-4 overflow-hidden mb-3 <?= $activeChoice === (string)$pm['id'] ? '' : 'd-none' ?>" id="pm_panel_<?= $pm['id'] ?>">
                                    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark" style="font-size: 13px;"><?= htmlspecialchars($pm['name']) ?></span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;"><?= htmlspecialchars($pm['bank'] ?: 'ធនាគារ') ?></span>
                                    </div>
                                    <div class="card-body p-3 text-center bg-white">
                                        <?php if (!empty($pm['qr_image_path'])): ?>
                                            <div class="d-inline-block p-1 border rounded-3 bg-light mb-2">
                                                <img src="<?= APP_URL . '/' . htmlspecialchars($pm['qr_image_path']) ?>" alt="QR" style="max-height: 170px; width: 170px; object-fit: contain;">
                                            </div>
                                        <?php endif; ?>

                                        <div class="bg-light p-2.5 rounded-3 border text-start" style="font-size: 12px;">
                                            <?php if (!empty($pm['account_name'])): ?>
                                                <div class="d-flex justify-content-between py-1 border-bottom">
                                                    <span class="text-muted">ឈ្មោះគណនី៖</span>
                                                    <strong class="text-dark"><?= htmlspecialchars($pm['account_name']) ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($pm['account_number'])): ?>
                                                <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                                    <span class="text-muted">លេខគណនី៖</span>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <strong class="text-primary font-monospace"><?= htmlspecialchars($pm['account_number']) ?></strong>
                                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1.5" style="font-size: 10px;" onclick="copyDirectText('<?= htmlspecialchars($pm['account_number']) ?>', this)">
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex justify-content-between py-1">
                                                <span class="text-muted">រូបិយប័ណ្ណ៖</span>
                                                <strong class="text-dark"><?= htmlspecialchars($pm['currency'] ?: 'USD') ?></strong>
                                            </div>
                                        </div>

                                        <?php if (!empty($pm['instructions'])): ?>
                                            <div class="text-muted small mt-2 text-start" style="font-size: 11px;">
                                                <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($pm['instructions']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if ($totalChoices === 0): ?>
                                <div class="alert alert-secondary small text-center p-3 rounded-3 mb-3">
                                    មិនទាន់មានព័ត៌មានគណនីបង់ប្រាក់។ សូមទាក់ទងអ្នករៀបចំកម្មវិធី។
                                </div>
                            <?php endif; ?>

                            <!-- Step 2 Navigation Buttons -->
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-2.5 px-3 fw-bold rounded-pill" onclick="goToStep1()" style="font-size: 13px;">
                                    <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ក្រោយ
                                </button>
                                <button type="button" class="btn btn-primary btn-sm py-2.5 px-4 fw-bold flex-grow-1 rounded-pill shadow-sm <?= $activeChoice === 'bakong' ? 'd-none' : '' ?>" id="btnSubmitManual" onclick="confirmAndSubmitRegistration()" style="font-size: 13.5px;">
                                    <i class="bi bi-check-circle me-1"></i> បញ្ជាក់ការចុះឈ្មោះ
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Find Ticket -->
<div class="modal fade" id="findTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-6">
                    <i class="bi bi-ticket-perforated me-2"></i>ស្វែងរកសំបុត្រ / QR Code ចូលរួម
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    សូមបញ្ចូលលេខទូរស័ព្ទដែលលោកអ្នកបានប្រើពេលចុះឈ្មោះ ប្រព័ន្ធនឹងស្វែងរកសំបុត្រ និង QR Code ជូនលោកអ្នកភ្លាមៗ៖
                </p>
                <form id="findTicketForm" onsubmit="submitFindTicket(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">លេខទូរស័ព្ទចុះឈ្មោះ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                            <input type="tel" id="find_ticket_phone" class="form-control form-control-lg fs-6" placeholder="ឧ. 012 345 678" required autocomplete="tel">
                        </div>
                    </div>
                    <div id="findTicketAlert" class="alert d-none small py-2 mb-3"></div>
                    <div class="d-grid gap-2">
                        <button type="submit" id="btnFindTicket" class="btn btn-primary fw-bold py-2 shadow-xs">
                            <i class="bi bi-search me-1"></i> ស្វែងរកសំបុត្រឥឡូវនេះ
                        </button>
                        <button type="button" class="btn btn-light btn-sm text-muted" data-bs-dismiss="modal">
                            បិទផ្ទាំងនេះ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openFindTicketModal() {
    const modalEl = document.getElementById('findTicketModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const alertBox = document.getElementById('findTicketAlert');
        if (alertBox) alertBox.className = 'alert d-none small py-2 mb-3';
        modal.show();
        setTimeout(() => {
            const phoneInput = document.getElementById('find_ticket_phone');
            if (phoneInput) phoneInput.focus();
        }, 400);
    }
}

async function submitFindTicket(e) {
    e.preventDefault();
    const phoneInput = document.getElementById('find_ticket_phone');
    const alertBox = document.getElementById('findTicketAlert');
    const btn = document.getElementById('btnFindTicket');
    const phone = phoneInput ? phoneInput.value.trim() : '';

    if (!phone) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងស្វែងរក...';
    alertBox.className = 'alert d-none small py-2 mb-3';

    try {
        const res = await fetch('<?= APP_URL ?>/api/ticket/find', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                phone: phone,
                workshop_id: '<?= (int)$workshop['id'] ?>',
                workshop_slug: '<?= htmlspecialchars($workshop['slug'], ENT_QUOTES) ?>'
            })
        });

        const data = await res.json();
        if (data.success && (data.redirect_url || data.ticket_url)) {
            alertBox.className = 'alert alert-success small py-2 mb-3 d-flex align-items-center';
            alertBox.innerHTML = '<i class="bi bi-check-circle-fill me-2 fs-5"></i><div><strong>រកឃើញសំបុត្រហើយ!</strong><div class="small">កំពុងនាំលោកអ្នកទៅកាន់ទំព័រសំបុត្រ...</div></div>';
            setTimeout(() => {
                window.location.href = data.redirect_url || data.ticket_url;
            }, 700);
        } else {
            alertBox.className = 'alert alert-warning small py-2 mb-3 d-flex align-items-center';
            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><div>' + (data.message || 'មិនស្វែងរកឃើញទិន្នន័យចុះឈ្មោះជាមួយលេខទូរស័ព្ទនេះទេ។') + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-search me-1"></i> ស្វែងរកសំបុត្រឥឡូវនេះ';
        }
    } catch (err) {
        alertBox.className = 'alert alert-danger small py-2 mb-3 d-flex align-items-center';
        alertBox.innerHTML = '<i class="bi bi-x-circle-fill me-2 fs-5"></i><div>មានបញ្ហាក្នុងការភ្ជាប់ សូមព្យាយាមម្តងទៀត។</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search me-1"></i> ស្វែងរកសំបុត្រឥឡូវនេះ';
    }
}

// 2-Step Registration & Payment Navigation
function goToPaymentStep() {
    const form = document.getElementById('registrationForm');
    if (!form) return;

    // Check HTML5 validity on required inputs in step 1
    const nameInp = form.querySelector('input[name="name"]');
    const phoneInp = form.querySelector('input[name="phone"]');

    if (nameInp && !nameInp.value.trim()) {
        nameInp.focus();
        nameInp.reportValidity();
        return;
    }
    if (phoneInp && phoneInp.hasAttribute('required') && !phoneInp.value.trim()) {
        phoneInp.focus();
        phoneInp.reportValidity();
        return;
    }

    // Check custom required fields in step 1
    const requiredInputs = document.querySelectorAll('#step1Section [required]');
    for (let inp of requiredInputs) {
        if (!inp.value.trim()) {
            inp.focus();
            inp.reportValidity();
            return;
        }
    }

    // Check Face Scan if required
    const faceReq = document.getElementById('faceEnrollRequired');
    const faceDesc = document.getElementById('faceDescriptorInput');
    if (faceReq && (!faceDesc || !faceDesc.value)) {
        alert('សូមស្គេនផ្ទៃមុខមុនបន្ត (*ចាំបាច់)');
        document.getElementById('faceEnrollSection')?.scrollIntoView({ behavior: 'smooth' });
        return;
    }

    // Populate summary in Step 2
    const summaryName = document.getElementById('summaryAttendeeName');
    if (summaryName && nameInp) summaryName.textContent = nameInp.value.trim();

    // Determine selected ticket
    let ticketName = 'សំបុត្រទូទៅ';
    let ticketPriceText = '$0.00';
    let isFreeTicket = false;

    const checkedRadio = document.querySelector('input[name="ticket_id"]:checked');
    const hiddenInp = document.getElementById('hiddenTicketId');

    if (checkedRadio) {
        const parentLabel = checkedRadio.closest('label');
        ticketName = parentLabel ? parentLabel.querySelector('.fw-bold')?.textContent.trim() : 'សំបុត្រ';
        const badge = parentLabel ? parentLabel.querySelector('.badge')?.textContent.trim() : '';
        ticketPriceText = badge;
        isFreeTicket = badge.includes('ឥតគិតថ្លៃ');
    } else if (hiddenInp) {
        ticketName = hiddenInp.getAttribute('data-name') || 'សំបុត្រ';
        const p = parseFloat(hiddenInp.getAttribute('data-price') || 0);
        ticketPriceText = p === 0 ? 'ឥតគិតថ្លៃ' : '$' + p.toFixed(2);
        isFreeTicket = (p === 0);
    }

    // If ticket is free, submit form directly
    if (isFreeTicket) {
        form.submit();
        return;
    }

    const summaryTicket = document.getElementById('summaryTicketName');
    const summaryPrice = document.getElementById('summaryTicketPriceDisplay');
    if (summaryTicket) summaryTicket.textContent = ticketName;
    if (summaryPrice) summaryPrice.textContent = ticketPriceText;

    // Show step 2
    document.getElementById('step1Section').classList.add('d-none');
    const step2 = document.getElementById('step2Section');
    if (step2) step2.classList.remove('d-none');
    const choice = document.getElementById('selectedPaymentMethodChoice')?.value;
    const submitBtn = document.getElementById('btnSubmitManual');
    if (choice === 'bakong') {
        if (submitBtn) submitBtn.classList.add('d-none');
        startBakongPolling();
    } else {
        if (submitBtn) submitBtn.classList.remove('d-none');
    }
}

function goToStep1() {
    if (typeof bakongPollTimer !== 'undefined' && bakongPollTimer) {
        clearInterval(bakongPollTimer);
        bakongPollTimer = null;
    }
    const step2 = document.getElementById('step2Section');
    if (step2) step2.classList.add('d-none');
    document.getElementById('step1Section').classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function previewSlip(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('slipPreviewImg');
            const wrapper = document.getElementById('slipPreviewWrapper');
            if (preview && wrapper) {
                preview.src = e.target.result;
                wrapper.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function copyDirectText(text, btn) {
    if (!text) return;
    const button = btn || (window.event ? window.event.currentTarget : null);
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text);
    } else {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
    }
    if (button) {
        const orig = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check2"></i> បានចម្លង!';
        setTimeout(() => button.innerHTML = orig, 1200);
    }
}

function selectPaymentMethod(pmId) {
    const choiceInp = document.getElementById('selectedPaymentMethodChoice');
    if (choiceInp) choiceInp.value = pmId;

    const hiddenInp = document.getElementById('selectedPaymentMethodId');
    if (hiddenInp) hiddenInp.value = (pmId === 'bakong' ? '' : pmId);

    // Update radio check
    const radio = document.getElementById('pm_radio_' + pmId);
    if (radio) radio.checked = true;

    // Update tile visual states
    document.querySelectorAll('.payment-method-tile').forEach(tile => {
        tile.classList.remove('border-primary', 'bg-primary-subtle', 'border-danger', 'bg-danger-subtle', 'shadow-xs');
        tile.classList.add('border-secondary-subtle', 'bg-white');
    });
    const activeTile = document.getElementById('pm_tile_' + pmId);
    if (activeTile) {
        activeTile.classList.remove('border-secondary-subtle', 'bg-white');
        if (pmId === 'bakong') {
            activeTile.classList.add('border-danger', 'bg-danger-subtle', 'shadow-xs');
        } else {
            activeTile.classList.add('border-primary', 'bg-primary-subtle', 'shadow-xs');
        }
    }

    // Switch panels
    document.querySelectorAll('.pm-detail-panel').forEach(panel => {
        panel.classList.add('d-none');
    });
    const activePanel = document.getElementById('pm_panel_' + pmId);
    if (activePanel) {
        activePanel.classList.remove('d-none');
    }

    // Toggle submit button: Bakong auto-confirms (no button needed), Manual payment shows submit button
    const submitBtn = document.getElementById('btnSubmitManual');
    if (pmId === 'bakong') {
        if (submitBtn) submitBtn.classList.add('d-none');
        startBakongPolling();
    } else {
        if (submitBtn) submitBtn.classList.remove('d-none');
        if (bakongPollTimer) { clearInterval(bakongPollTimer); bakongPollTimer = null; }
    }
}

let bakongPollTimer = null;
let bakongScanSeconds = 0;

function startBakongPolling() {
    if (bakongPollTimer) clearInterval(bakongPollTimer);
    bakongScanSeconds = 0;

    const md5 = document.getElementById('bakongMd5Input')?.value;
    if (!md5) return;

    const pBar = document.getElementById('bakongProgressBar');
    if (pBar) {
        pBar.className = 'progress-bar bg-danger progress-bar-striped progress-bar-animated';
        pBar.style.width = '20%';
    }

    bakongPollTimer = setInterval(async () => {
        const step2 = document.getElementById('step2Section');
        if (!step2 || step2.classList.contains('d-none')) {
            clearInterval(bakongPollTimer);
            bakongPollTimer = null;
            return;
        }

        const choice = document.getElementById('selectedPaymentMethodChoice')?.value;
        if (choice !== 'bakong') {
            clearInterval(bakongPollTimer);
            bakongPollTimer = null;
            return;
        }

        bakongScanSeconds += 2;
        if (pBar) {
            const pct = Math.min(20 + (bakongScanSeconds * 12), 95);
            pBar.style.width = pct + '%';
        }

        try {
            const res = await fetch('<?= APP_URL ?>/event/<?= htmlspecialchars($workshop['slug'], ENT_QUOTES) ?>/check-bakong', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    md5: md5,
                    scan_seconds: bakongScanSeconds,
                    amount: '<?= (float)$ticketPrice ?>',
                    currency: '<?= htmlspecialchars($ticketCurrency, ENT_QUOTES) ?>'
                })
            });
            const data = await res.json();
            if (data.paid || (data.data && data.data.is_paid)) {
                clearInterval(bakongPollTimer);
                bakongPollTimer = null;

                const statusBox = document.getElementById('bakongAutoStatusBox');
                if (statusBox) {
                    statusBox.className = 'card border border-success bg-success-subtle p-2.5 rounded-3 mx-auto mt-2';
                }
                const liveStatus = document.getElementById('bakongLiveStatus');
                if (liveStatus) {
                    liveStatus.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-5 me-1"></i><span class="fw-bold text-success" style="font-size: 13px;">ទទួលបានការទូទាត់ជោគជ័យ!</span>';
                }
                if (pBar) {
                    pBar.className = 'progress-bar bg-success';
                    pBar.style.width = '100%';
                }

                const pConfirmed = document.getElementById('paymentConfirmedInput');
                if (pConfirmed) pConfirmed.value = '1';

                setTimeout(() => {
                    const form = document.getElementById('registrationForm');
                    if (form) form.submit();
                }, 800);
            }
        } catch (e) {
            // Heartbeat
        }
    }, 2000);
}

function confirmAndSubmitRegistration() {
    const btn = document.getElementById('btnSubmitManual');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបញ្ជាក់ការចុះឈ្មោះ...';
    }
    const form = document.getElementById('registrationForm');
    if (form) form.submit();
}

document.addEventListener('DOMContentLoaded', () => {
    const step2 = document.getElementById('step2Section');
    const choice = document.getElementById('selectedPaymentMethodChoice')?.value;
    if (step2 && !step2.classList.contains('d-none') && choice === 'bakong') {
        startBakongPolling();
    }
});
</script>

<?php if (!empty($formConfig['show_face_enroll'])): ?>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
(function() {
    'use strict';

    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
    let faceStream = null;
    let faceDetectionLoop = null;
    let faceModelsLoaded = false;
    let faceCaptured = false;
    let steadyTicks = 0;
    const REQUIRED_STEADY_TICKS = 3;

    const video       = document.getElementById('faceEnrollVideo');
    const overlay     = document.getElementById('faceEnrollOverlay');
    const reticle     = document.getElementById('faceEnrollReticle');
    const flashEl     = document.getElementById('faceEnrollFlash');
    const badgeEl     = document.getElementById('faceEnrollQualityBadge');
    const bannerEl    = document.getElementById('faceEnrollSteadyBanner');
    const guideBox    = document.getElementById('faceEnrollGuidanceBox');
    const guideIcon   = document.getElementById('faceEnrollGuidanceIcon');
    const guideText   = document.getElementById('faceEnrollGuidanceText');
    const progressBar = document.getElementById('faceEnrollProgressBar');
    const preview     = document.getElementById('faceEnrollPreview');
    const photoImg    = document.getElementById('faceEnrollPhoto');
    const btnCap      = document.getElementById('btnCaptureFace');
    const btnRet      = document.getElementById('btnRetakeFace');
    const descInp     = document.getElementById('faceDescriptorInput');
    const photoInp    = document.getElementById('facePhotoInput');
    const isRequired  = !!document.getElementById('faceEnrollRequired');

    let latestDetection = null;

    function playSuccessChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now);
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.35);
        } catch (e) {}
    }

    function setGuidance(text, iconClass, alertClass) {
        if (!guideText) return;
        guideText.textContent = text;
        if (guideIcon) guideIcon.className = 'bi ' + iconClass + ' fs-5 flex-shrink-0';
        if (guideBox) {
            guideBox.className = 'alert ' + (alertClass || 'alert-info') + ' py-2 px-3 small d-flex align-items-center justify-content-center gap-2 mb-2 text-center fw-medium shadow-xs';
        }
    }

    async function initFaceEnroll() {
        if (!video) return;
        try {
            faceStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            });
            video.srcObject = faceStream;
            await new Promise(r => video.addEventListener('loadedmetadata', r, { once: true }));
            await video.play();
        } catch (e) {
            setGuidance('មិនអាចបើកកាមេរ៉ា៖ ' + e.message, 'bi-camera-video-off-fill text-danger', 'alert-danger');
            return;
        }

        setGuidance('កំពុងផ្ទុកប្រព័ន្ធ AI ស្គេនមុខ...', 'spinner-border spinner-border-sm text-primary', 'alert-secondary');
        try {
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            faceModelsLoaded = true;
        } catch (e) {
            setGuidance('មិនអាចផ្ទុក AI model. ពិនិត្យការភ្ជាប់អ៊ីនធឺណិត។', 'bi-exclamation-triangle-fill text-warning', 'alert-warning');
            return;
        }

        setGuidance('សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា', 'bi-emoji-smile', 'alert-info');
        startDetectionPreview();
    }

    function analyzeQuality(detection, vWidth, vHeight) {
        const box = detection.detection.box;
        const landmarks = detection.landmarks;
        let penalty = 0;
        let advice = null;
        let icon = 'bi-emoji-smile';

        const faceRatio = box.width / vWidth;
        if (faceRatio < 0.25) {
            penalty += 25;
            advice = 'សូមខិតមកជិតកាមេរ៉ាបន្តិច';
            icon = 'bi-zoom-in';
        } else if (faceRatio > 0.68) {
            penalty += 25;
            advice = 'សូមថយក្រោយបន្តិច';
            icon = 'bi-zoom-out';
        }

        const centerX = (box.x + box.width / 2) / vWidth;
        const centerY = (box.y + box.height / 2) / vHeight;
        if (!advice && (centerX < 0.32 || centerX > 0.68 || centerY < 0.28 || centerY > 0.72)) {
            penalty += 15;
            advice = 'សូមរំកិលមុខមកចំកណ្តាល';
            icon = 'bi-arrows-move';
        }

        const leftEye = landmarks.getLeftEye();
        const rightEye = landmarks.getRightEye();
        const avgL = { x: leftEye.reduce((s,p)=>s+p.x,0)/leftEye.length, y: leftEye.reduce((s,p)=>s+p.y,0)/leftEye.length };
        const avgR = { x: rightEye.reduce((s,p)=>s+p.x,0)/rightEye.length, y: rightEye.reduce((s,p)=>s+p.y,0)/rightEye.length };
        const rollDeg = Math.atan2(avgR.y - avgL.y, avgR.x - avgL.x) * (180 / Math.PI);
        if (!advice && Math.abs(rollDeg) > 9) {
            penalty += 20;
            advice = 'សូមតម្រង់ក្បាលឱ្យត្រង់';
            icon = 'bi-arrow-clockwise';
        }

        const noseTip = landmarks.positions[30];
        const distL = Math.abs(noseTip.x - avgL.x);
        const distR = Math.abs(noseTip.x - avgR.x);
        const yawRatio = distL / Math.max(1, distR);
        if (!advice) {
            if (yawRatio < 0.62) {
                penalty += 22;
                advice = 'សូមងាកមុខមកឆ្វេងបន្តិច';
                icon = 'bi-arrow-left-circle';
            } else if (yawRatio > 1.62) {
                penalty += 22;
                advice = 'សូមងាកមុខមកស្តាំបន្តិច';
                icon = 'bi-arrow-right-circle';
            }
        }

        const eyeMidY = (avgL.y + avgR.y) / 2;
        const chin = landmarks.positions[8];
        const upper = noseTip.y - eyeMidY;
        const lower = chin.y - noseTip.y;
        const pitchRatio = upper / Math.max(1, lower);
        if (!advice) {
            if (pitchRatio < 0.38) {
                penalty += 20;
                advice = 'សូមឱនមុខបន្តិច';
                icon = 'bi-arrow-down-circle';
            } else if (pitchRatio > 1.05) {
                penalty += 20;
                advice = 'សូមងើបមុខបន្តិច';
                icon = 'bi-arrow-up-circle';
            }
        }

        const rawScore = Math.round(detection.detection.score * 100);
        const quality = Math.max(10, Math.min(100, rawScore - penalty));

        return {
            quality: quality,
            advice: advice || 'គុណភាពល្អណាស់! សូមរក្សាភាពស្ងៀមស្ងាត់...',
            icon: advice ? icon : 'bi-check-circle-fill'
        };
    }

    function startDetectionPreview() {
        if (faceDetectionLoop) return;
        const ctx = overlay.getContext('2d');
        faceDetectionLoop = setInterval(async () => {
            if (faceCaptured || !faceModelsLoaded || video.paused || video.readyState < 2) return;
            const vw = video.videoWidth  || overlay.offsetWidth || 640;
            const vh = video.videoHeight || overlay.offsetHeight || 480;
            overlay.width  = vw;
            overlay.height = vh;
            ctx.clearRect(0, 0, vw, vh);

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.45 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                latestDetection = detection;

                if (!detection) {
                    steadyTicks = 0;
                    if (badgeEl) badgeEl.textContent = 'គុណភាព៖ 0%';
                    if (reticle) reticle.style.borderColor = 'rgba(255,255,255,0.45)';
                    if (bannerEl) bannerEl.classList.add('d-none');
                    if (progressBar) progressBar.style.width = '0%';
                    if (btnCap) btnCap.disabled = true;
                    setGuidance('សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា', 'bi-emoji-smile', 'alert-info');
                    return;
                }

                const analysis = analyzeQuality(detection, vw, vh);
                const q = analysis.quality;

                if (badgeEl) {
                    badgeEl.textContent = 'គុណភាព៖ ' + q + '%';
                    badgeEl.className = 'badge rounded-pill ' + (q >= 90 ? 'bg-success' : (q >= 70 ? 'bg-primary' : 'bg-warning text-dark')) + ' px-2 py-1 small fw-bold shadow-sm';
                }

                if (btnCap) btnCap.disabled = (q < 65);

                if (q >= 90) {
                    if (reticle) {
                        reticle.style.borderColor = '#10b981';
                        reticle.style.boxShadow = '0 0 0 9999px rgba(11,17,32,0.35), 0 0 25px rgba(16,185,129,0.7)';
                    }
                    if (bannerEl) bannerEl.classList.remove('d-none');
                    setGuidance(analysis.advice, analysis.icon, 'alert-success');

                    steadyTicks++;
                    const pct = Math.min(100, Math.round((steadyTicks / REQUIRED_STEADY_TICKS) * 100));
                    if (progressBar) progressBar.style.width = pct + '%';

                    if (steadyTicks >= REQUIRED_STEADY_TICKS) {
                        executePublicCapture(detection);
                    }
                } else {
                    steadyTicks = 0;
                    if (progressBar) progressBar.style.width = '0%';
                    if (reticle) {
                        reticle.style.borderColor = (q >= 70 ? 'rgba(255,255,255,0.45)' : '#f59e0b');
                        reticle.style.boxShadow = '0 0 0 9999px rgba(11,17,32,0.45)';
                    }
                    if (bannerEl) bannerEl.classList.add('d-none');
                    setGuidance(analysis.advice, analysis.icon, q >= 70 ? 'alert-info' : 'alert-warning');
                }

            } catch (err) {
                console.error('Public face detection loop error:', err);
            }
        }, 180);
    }

    function executePublicCapture(detection) {
        if (faceCaptured || !detection) return;
        faceCaptured = true;

        if (flashEl) {
            flashEl.style.opacity = '0.95';
            setTimeout(() => { flashEl.style.opacity = '0'; }, 250);
        }
        playSuccessChime();

        const vw = video.videoWidth || 640;
        const vh = video.videoHeight || 480;
        const canvas = document.createElement('canvas');
        canvas.width  = vw;
        canvas.height = vh;
        const ctx = canvas.getContext('2d');
        ctx.translate(vw, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, vw, vh);

        const photoDataUrl = canvas.toDataURL('image/jpeg', 0.85);

        descInp.value = JSON.stringify(Array.from(detection.descriptor));
        photoInp.value = photoDataUrl;
        photoImg.src = photoDataUrl;

        clearInterval(faceDetectionLoop);
        faceDetectionLoop = null;
        if (faceStream) {
            faceStream.getTracks().forEach(t => t.stop());
            faceStream = null;
        }

        preview.classList.remove('d-none');
        document.getElementById('faceEnrollBox')?.classList.add('d-none');
        btnCap.classList.add('d-none');
        btnRet.classList.remove('d-none');
        setGuidance('ស្គេនមុខបានជោគជ័យ! អាចបន្តចុះឈ្មោះបាន។', 'bi-check-circle-fill text-success', 'alert-success');
    }

    window.forceCapturePublicFace = function() {
        if (!latestDetection) {
            alert('រកមុខមិនឃើញទេ! សូមតម្រង់មុខឱ្យចំកាមេរ៉ា។');
            return;
        }
        executePublicCapture(latestDetection);
    };

    window.retakePublicFace = function() {
        faceCaptured = false;
        steadyTicks = 0;
        latestDetection = null;
        descInp.value = '';
        photoInp.value = '';
        preview.classList.add('d-none');
        photoImg.src = '';
        document.getElementById('faceEnrollBox')?.classList.remove('d-none');
        btnCap.classList.remove('d-none');
        btnCap.disabled = true;
        btnRet.classList.add('d-none');
        if (progressBar) progressBar.style.width = '0%';
        initFaceEnroll();
    };

    // Form submit validation for required face
    const registerForm = document.querySelector('form[action*="/register"]');
    if (registerForm && isRequired) {
        registerForm.addEventListener('submit', function(e) {
            if (!descInp.value) {
                e.preventDefault();
                setGuidance('ត្រូវតែស្គេនមុខមុនដាក់ស្នើ (*ចាំបាច់)', 'bi-exclamation-triangle-fill text-danger', 'alert-danger');
                document.getElementById('faceEnrollSection').scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    initFaceEnroll();
})();
</script>
<?php endif; ?>
