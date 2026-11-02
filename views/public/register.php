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

                    <!-- Quick Ticket Retrieval for existing attendees -->
                    <div class="alert alert-primary-subtle border border-primary-subtle d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-xs">
                        <div class="d-flex align-items-center me-2 mb-2 mb-sm-0">
                            <i class="bi bi-ticket-perforated-fill text-primary fs-3 me-3"></i>
                            <div>
                                <span class="fw-bold d-block text-dark">ធ្លាប់បានចុះឈ្មោះរួចហើយ?</span>
                                <small class="text-muted">ស្វែងរកសំបុត្រ ឬទាញយក QR Code របស់អ្នកឡើងវិញដោយប្រើលេខទូរស័ព្ទ</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-bold" onclick="openFindTicketModal()">
                            <i class="bi bi-search me-1"></i> រកសំបុត្ររបស់ខ្ញុំ
                        </button>
                    </div>

                    <?php
                    $isFreeMode = ($workshop['payment_mode'] === 'free');
                    // In Free Mode with single ticket, hide ticket selection completely
                    $showTicketSelection = !$isFreeMode || count($tickets) > 1;
                    $stepIdx = 1;
                    $khmerNumbers = [1 => '១', 2 => '២', 3 => '៣', 4 => '៤', 5 => '៥'];
                    ?>

                    <form action="<?= APP_URL ?>/event/<?= htmlspecialchars($workshop['slug']) ?>/register" method="POST">
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

                        <?php if ($showTicketSelection): ?>
                            <h5 class="fw-bold text-primary mb-3">
                                <i class="bi bi-ticket-perforated me-1"></i> <?= $khmerNumbers[$stepIdx++] ?>. ប្រភេទសំបុត្រ
                            </h5>
                            <div class="list-group mb-4">
                                <?php foreach ($tickets as $ticket): ?>
                                <label class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <input class="form-check-input me-2" type="radio" name="ticket_id" value="<?= $ticket['id'] ?>" <?= (($old['ticket_id'] ?? '') == $ticket['id'] || count($tickets) === 1) ? 'checked' : '' ?> required>
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
                                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($tickets[0]['id']) ?>">
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

                        <?php if (!$isFreeMode): ?>
                        <div class="mb-4 mt-3">
                            <label class="form-label small fw-bold">កូដបញ្ចុះតម្លៃ <span class="text-muted fw-normal small">(ជម្រើស)</span></label>
                            <input type="text" name="promo_code" class="form-control" placeholder="ឧ. PROMO20" value="<?= htmlspecialchars($old['promo_code'] ?? '') ?>">
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($paymentMethods) && !$isFreeMode): ?>
                        <div class="mt-4 p-3 bg-light rounded border mb-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-wallet2 text-primary me-1"></i> គណនីបង់ប្រាក់</h6>
                            <p class="text-muted small mb-3">សូមផ្ទេរប្រាក់ទៅកាន់គណនីខាងក្រោម ហើយបញ្ចូលបង្កាន់ដៃនៅជំហានបន្ទាប់៖</p>
                            <div class="row g-3">
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 p-2 shadow-sm border">
                                            <div class="card-body p-2 small">
                                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($pm['name']) ?></h6>
                                                <?php if ($pm['bank']): ?><div><strong>ធនាគារ:</strong> <?= htmlspecialchars($pm['bank']) ?></div><?php endif; ?>
                                                <?php if ($pm['account_name']): ?><div><strong>ឈ្មោះគណនី:</strong> <?= htmlspecialchars($pm['account_name']) ?></div><?php endif; ?>
                                                <?php if ($pm['account_number']): ?><div><strong>លេខគណនី:</strong> <?= htmlspecialchars($pm['account_number']) ?></div><?php endif; ?>
                                                <?php if (!empty($pm['qr_image_path'])): ?>
                                                    <div class="text-center mt-2">
                                                        <img src="<?= APP_URL . '/' . htmlspecialchars($pm['qr_image_path']) ?>" alt="QR" class="img-thumbnail" style="max-height: 120px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3">
                                <i class="bi bi-check-circle me-1"></i> ចុះឈ្មោះឥឡូវនេះ
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-link text-decoration-none text-muted small" onclick="openFindTicketModal()">
                                <i class="bi bi-search me-1 text-primary"></i> ធ្លាប់បានចុះឈ្មោះរួចហើយ? <strong>ស្វែងរកសំបុត្រ ឬ QR Code ឡើងវិញ</strong>
                            </button>
                        </div>
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
