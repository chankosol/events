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
                                ក្រុមហ៊ុន / ស្ថាប័ន 
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
                                តួនាទី / មុខតំណែង 
                                <?= !empty($formConfig['require_position']) ? '<span class="text-danger">*</span>' : '<span class="text-muted fw-normal small">(ជម្រើស)</span>' ?>
                            </label>
                            <input type="text" name="position" class="form-control" placeholder="ឧ. ប្រធានផ្នែក, អ្នកគ្រប់គ្រង" value="<?= htmlspecialchars($old['position'] ?? '') ?>" <?= !empty($formConfig['require_position']) ? 'required' : '' ?>>
                            <?php if (isset($errors['position'])): ?><div class="text-danger small mt-1"><?= $errors['position'] ?></div><?php endif; ?>
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
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
