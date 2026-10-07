<?php
// views/profile/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត</h2>
        <p class="text-muted mb-0">គ្រប់គ្រងឈ្មោះ រូបភាពគណនី លេខទូរស័ព្ទ និងពាក្យសម្ងាត់របស់អ្នក។</p>
    </div>
    <div>
        <?php if (!empty($user['business_id'])): ?>
            <a href="<?php echo APP_URL; ?>/staff" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅក្រុមការងារ
            </a>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>/platform" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅផ្ទាំងគ្រប់គ្រង
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <!-- Profile Summary Card -->
        <div class="card shadow-sm border-0 text-center p-4">
            <div class="d-flex justify-content-center mb-3 position-relative">
                <div id="avatarPreviewContainer">
                    <?php echo userAvatarHtml($user, 110, 'border shadow'); ?>
                </div>
            </div>
            <h5 class="fw-bold mb-1"><?php echo e($user['name']); ?></h5>
            <p class="text-muted small mb-2"><?php echo e($user['email']); ?></p>
            <div class="mb-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                    <i class="bi bi-shield-check me-1"></i> <?php echo e(userRoleTitle($user)); ?>
                </span>
            </div>
            <?php if (!empty($user['phone'])): ?>
                <div class="small text-secondary mb-1">
                    <i class="bi bi-telephone me-1"></i> <?php echo e($user['phone']); ?>
                </div>
            <?php endif; ?>
            <div class="small text-muted border-top pt-3 mt-2">
                ចុះឈ្មោះតាំងពី៖ <?php echo formatDate($user['created_at']); ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Edit Profile Form -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-pencil-square text-primary me-2"></i> កែប្រែព័ត៌មានគណនី
                </h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?php echo APP_URL; ?>/profile" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>

                    <!-- Profile Picture Upload -->
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label fw-bold d-block">រូបថតគណនី (Profile Picture)</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="file" name="profile_photo" id="profilePhotoInput" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <div class="form-check text-nowrap">
                                    <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                                    <label class="form-check-label text-danger small" for="removePhotoCheck">
                                        លុបរូបថតចេញ
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text small">គាំទ្រប្រភេទឯកសារ JPG, PNG, WEBP (ទំហំអតិបរមា 2MB)។ ប្រព័ន្ធនឹងរៀបចំជារាងមូលស្វ័យប្រវត្តិ។</div>
                    </div>

                    <!-- Basic Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ឈ្មោះពេញ (Full Name) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($user['name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល (Email)</label>
                            <input type="email" class="form-control bg-light" value="<?php echo e($user['email']); ?>" readonly>
                            <div class="form-text small">អ៊ីមែលគណនីមិនអាចកែប្រែដោយផ្ទាល់បានទេ។</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">លេខទូរស័ព្ទ (Phone)</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone'] ?? ''); ?>" placeholder="ឧទាហរណ៍៖ 012 345 678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">តួនាទី (Role)</label>
                            <input type="text" class="form-control bg-light" value="<?php echo e(userRoleTitle($user)); ?>" readonly>
                        </div>
                    </div>

                    <!-- Change Password Accordion / Section -->
                    <div class="mb-4 p-3 bg-light rounded border">
                        <h6 class="fw-bold mb-2">
                            <i class="bi bi-shield-lock text-warning me-1"></i> ប្តូរពាក្យសម្ងាត់ (ទុកទំនេរប្រសិនបើមិនចង់ផ្លាស់ប្តូរ)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">ពាក្យសម្ងាត់បច្ចុប្បន្ន</label>
                                <input type="password" name="current_password" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">ពាក្យសម្ងាត់ថ្មី</label>
                                <input type="password" name="new_password" class="form-control" placeholder="យ៉ាងតិច ៦ ខ្ទង់">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">បញ្ជាក់ពាក្យសម្ងាត់ថ្មី</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> រក្សាទុកការផ្លាស់ប្តូរ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('profilePhotoInput');
    const previewContainer = document.getElementById('avatarPreviewContainer');
    
    if (photoInput && previewContainer) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewContainer.innerHTML = '<img src="' + evt.target.result + '" class="rounded-circle object-fit-cover border shadow" style="width:110px;height:110px;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
