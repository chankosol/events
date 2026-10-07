<?php
// views/business/settings/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ការកំណត់ស្ថាប័ន / ក្រុមហ៊ុន</h2>
        <p class="text-muted mb-0">កំណត់ព័ត៌មានស្ថាប័ន ស្លាកសញ្ញា និងវិធីសាស្ត្រទទួលប្រាក់ពីសិក្ខាកាម។</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="list-group list-group-flush" id="settingsTabs" role="tablist">
                <a class="list-group-item list-group-item-action active p-3" data-bs-toggle="list" href="#generalTab">
                    <i class="bi bi-building me-2"></i> ព័ត៌មានស្ថាប័ន
                </a>
                <a class="list-group-item list-group-item-action p-3" data-bs-toggle="list" href="#brandingTab">
                    <i class="bi bi-palette me-2"></i> ស្លាកសញ្ញា & ការរចនា
                </a>
                <a class="list-group-item list-group-item-action p-3" data-bs-toggle="list" href="#paymentsTab">
                    <i class="bi bi-qr-code-scan me-2"></i> កូដ QR ទទួលប្រាក់ពីសិក្ខាកាម
                </a>
                <a class="list-group-item list-group-item-action p-3" data-bs-toggle="list" href="#profileTab">
                    <i class="bi bi-person-gear me-2 text-primary"></i> ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
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
                                    <input type="text" name="name" class="form-control" value="<?php echo e($business['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">អ៊ីមែលក្រុមហ៊ុន</label>
                                    <input type="email" class="form-control" value="<?php echo e($business['email']); ?>" readonly>
                                    <div class="form-text small">អ៊ីមែលគណនីគ្រប់គ្រងដោយម្ចាស់ស្ថាប័ន។</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">លេខទូរស័ព្ទទំនាក់ទំនង</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo e($business['phone']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">គេហទំព័រ</label>
                                    <input type="url" name="website" class="form-control" value="<?php echo e($business['website']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">ប្រទេស</label>
                                    <input type="text" name="country" class="form-control" value="<?php echo e($business['country']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">រាជធានី / ខេត្ត</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo e($business['city']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">តំបន់ម៉ោង</label>
                                    <input type="text" name="timezone" class="form-control" value="<?php echo e($business['timezone']); ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">អាសយដ្ឋាន</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo e($business['address']); ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">រូបិយប័ណ្ណចម្បង</label>
                                    <select name="preferred_currency" class="form-select">
                                        <option value="USD" <?php echo $business['preferred_currency'] === 'USD' ? 'selected' : ''; ?>>ដុល្លារអាមេរិក ($ USD)</option>
                                        <option value="KHR" <?php echo $business['preferred_currency'] === 'KHR' ? 'selected' : ''; ?>>រៀលខ្មែរ (៛ KHR)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">ភាសាចម្បង</label>
                                    <select name="preferred_language" class="form-select">
                                        <option value="km" <?php echo $business['preferred_language'] === 'km' ? 'selected' : ''; ?>>ភាសាខ្មែរ</option>
                                        <option value="en" <?php echo $business['preferred_language'] === 'en' ? 'selected' : ''; ?>>English</option>
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

            <!-- PAYMENT RECEIVING METHODS (SYSTEM B: PARTICIPANT PAYS HOST) -->
            <div class="tab-pane fade" id="paymentsTab">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">កូដ QR ធនាគារសម្រាប់ទទួលប្រាក់ពីសិក្ខាកាម</h5>
                            <span class="badge bg-success-subtle text-success">ប្រព័ន្ធទី ២: សិក្ខាកាមបង់ប្រាក់ថ្លៃសំបុត្រជូនស្ថាប័ន</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info py-2 small mb-4">
                            <i class="bi bi-info-circle-fill me-1"></i> គណនីធនាគារ និងកូដ QR ទាំងនេះជារបស់ស្ថាប័នអ្នកផ្ទាល់សម្រាប់ទទួលប្រាក់ថ្លៃសំបុត្រពីសិក្ខាកាម។ ប្រព័ន្ធ SAAS មិនកាន់កាប់ ឬប៉ះពាល់ប្រាក់នេះឡើយ!
                        </div>

                        <!-- Current Payment Methods -->
                        <div class="row g-3 mb-4">
                            <?php if (empty($paymentMethods)): ?>
                                <div class="col-12 text-center py-4 text-muted border rounded bg-light">
                                    មិនទាន់មានវិធីសាស្ត្រទទួលប្រាក់នៅឡើយទេ។ សូមបន្ថែមខាងក្រោម!
                                </div>
                            <?php else: ?>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <div class="col-md-6">
                                        <div class="card border h-100 p-3 shadow-sm">
                                            <div class="d-flex align-items-start gap-3">
                                                <?php if (!empty($pm['qr_image_path'])): ?>
                                                    <img src="<?php echo APP_URL . '/' . e($pm['qr_image_path']); ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: contain;" alt="QR">
                                                <?php else: ?>
                                                    <div class="bg-light p-3 rounded text-muted"><i class="bi bi-qr-code fs-2"></i></div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1"><?php echo e($pm['name']); ?></h6>
                                                    <div class="text-muted small"><strong>ធនាគារ:</strong> <?php echo e($pm['bank'] ?: 'ធនាគារ'); ?></div>
                                                    <div class="text-muted small"><strong>គណនី:</strong> <?php echo e($pm['account_name'] ?: 'N/A'); ?> (<?php echo e($pm['account_number'] ?: 'N/A'); ?>)</div>
                                                    <div class="text-muted small"><strong>រូបិយប័ណ្ណ:</strong> <?php echo e($pm['currency']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Add New Payment Method Form -->
                        <h6 class="fw-bold mb-3 border-top pt-4">បន្ថែមវិធីសាស្ត្រទទួលប្រាក់តាមធនាគារ</h6>
                        <form method="POST" action="<?php echo APP_URL; ?>/settings/payment-methods" enctype="multipart/form-data">
                            <?php echo csrfField(); ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ឈ្មោះវិធីសាស្ត្រ <span class="text-danger">*</span></label>
                                    <input type="text" name="method_name" class="form-control form-control-sm" placeholder="ឧទាហរណ៍៖ ABA Bank QR" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">ឈ្មោះធនាគារ</label>
                                    <input type="text" name="bank" class="form-control form-control-sm" placeholder="ឧទាហរណ៍៖ ABA / ACLEDA / Bakong">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">រូបិយប័ណ្ណ</label>
                                    <select name="currency" class="form-select form-select-sm">
                                        <option value="USD">ដុល្លារ ($ USD)</option>
                                        <option value="KHR">រៀល (៛ KHR)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">ឈ្មោះម្ចាស់គណនី</label>
                                    <input type="text" name="account_name" class="form-control form-control-sm" placeholder="ឧទាហរណ៍៖ KSH Training Institute">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">លេខគណនី</label>
                                    <input type="text" name="account_number" class="form-control form-control-sm" placeholder="ឧទាហរណ៍៖ 001 987 654">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">ផ្ទុកឡើងរូបភាពកូដ QR ធនាគាររបស់អ្នក <span class="text-danger">*</span></label>
                                    <input type="file" name="qr_image" class="form-control form-control-sm" accept="image/*" required>
                                    <div class="form-text small">នេះជាកូដ QR របស់ស្ថាប័នអ្នក ដែលសិក្ខាកាមនឹងស្កេនដើម្បីបង់ប្រាក់ថ្លៃសំបុត្រ។</div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">សេចក្តីណែនាំសម្រាប់សិក្ខាកាម</label>
                                    <textarea name="instructions" class="form-control form-control-sm" rows="2" placeholder="ឧទាហរណ៍៖ សូមស្កេនកូដ QR នេះ រួចបញ្ចូលលេខកូដចុះឈ្មោះក្នុងចំណាំ និងផ្ទុកឡើងវិក្កយបត្របង់ប្រាក់។"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-success btn-sm px-4">
                                    <i class="bi bi-plus-circle me-1"></i> បន្ថែមវិធីសាស្ត្រទូទាត់
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MY PROFILE TAB -->
            <div class="tab-pane fade" id="profileTab">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-person-circle text-primary me-2"></i> ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត (Profile)
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
                                    <label class="form-label fw-bold mb-1">ប្តូររូបថតគណនី (Profile Photo)</label>
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
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
</script>
