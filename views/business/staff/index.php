<?php
// views/business/staff/index.php
$currentUser = Auth::user();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ក្រុមការងារ & សិទ្ធិប្រើប្រាស់</h2>
        <p class="text-muted mb-0">គ្រប់គ្រងសមាជិកបុគ្គលិក កែប្រែព័ត៌មាន និងកំណត់សិទ្ធិអនុញ្ញាតសម្រាប់សិក្ខាសាលា។</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo APP_URL; ?>/profile" class="btn btn-outline-primary">
            <i class="bi bi-person-gear me-1"></i> កែប្រែព័ត៌មានផ្ទាល់ខ្លួន
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-person-plus me-1"></i> បន្ថែមសមាជិកក្រុម
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>សមាជិក</th>
                    <th>អ៊ីមែល</th>
                    <th>តួនាទី</th>
                    <th>សកម្មភាពចុងក្រោយ</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($staff)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">មិនមានសមាជិកក្រុមនៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($staff as $s): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-shrink-0">
                                        <?php echo userAvatarHtml($s, 38); ?>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block"><?php echo e($s['name']); ?></span>
                                        <?php if (!empty($s['phone'])): ?>
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?php echo e($s['phone']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($s['email']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo e($s['role_name']); ?></span></td>
                            <td><?php echo $s['last_login_at'] ? formatDateTime($s['last_login_at']) : 'មិនធ្លាប់'; ?></td>
                            <td><?php echo statusBadge($s['status']); ?></td>
                            <td class="text-end text-nowrap">
                                <?php if ($s['id'] === Auth::id()): ?>
                                    <span class="badge bg-light text-dark border me-1">អ្នកផ្ទាល់</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMyProfileModal" title="កែប្រែព័ត៌មាន និងរូបថត">
                                        <i class="bi bi-pencil-square me-1"></i> កែប្រែព័ត៌មាន
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#editStaffModal_<?php echo $s['id']; ?>" title="កែប្រែសិទ្ធិ និងព័ត៌មាន">
                                        <i class="bi bi-pencil"></i> កែប្រែ
                                    </button>
                                    <form method="POST" action="<?php echo APP_URL; ?>/staff/<?php echo $s['id']; ?>/remove" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបសមាជិកក្រុមនេះចេញមែនទេ?');">
                                        <?php echo csrfField(); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="លុបចេញ">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Quick Edit My Profile (Kosol Manager) -->
<div class="modal fade" id="editMyProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-circle text-primary me-2"></i> កែប្រែព័ត៌មាន & រូបថតរបស់ខ្ញុំ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo APP_URL; ?>/profile" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="redirect_back" value="<?php echo APP_URL; ?>/staff">
                <div class="modal-body">
                    <!-- Avatar Preview & Upload -->
                    <div class="text-center mb-3">
                        <div class="d-inline-block position-relative mb-2" id="modalMyAvatarPreview">
                            <?php echo userAvatarHtml($currentUser, 80, 'border shadow-sm'); ?>
                        </div>
                        <div>
                            <label for="modalProfilePhotoInput" class="btn btn-sm btn-outline-primary mb-1">
                                <i class="bi bi-camera me-1"></i> ជ្រើសរើសរូបថតថ្មី
                            </label>
                            <input type="file" name="profile_photo" id="modalProfilePhotoInput" class="d-none" accept="image/png, image/jpeg, image/jpg, image/webp">
                        </div>
                        <?php if (!empty($currentUser['profile_photo'])): ?>
                            <div class="form-check d-inline-block mt-1">
                                <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="modalRemovePhotoCheck">
                                <label class="form-check-label text-danger small" for="modalRemovePhotoCheck">
                                    លុបរូបថតចេញ
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($currentUser['name'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល</label>
                        <input type="email" class="form-control bg-light" value="<?php echo e($currentUser['email'] ?? ''); ?>" readonly>
                        <div class="form-text small">អ៊ីមែលគណនីប្រើប្រាស់សម្រាប់ Login។</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e($currentUser['phone'] ?? ''); ?>" placeholder="ឧ. 012 345 678">
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <h6 class="fw-bold mb-2 small text-secondary">
                            <i class="bi bi-key me-1"></i> ប្តូរពាក្យសម្ងាត់ (ទុកទំនេរប្រសិនបើមិនប្តូរ)
                        </h6>
                        <div class="mb-2">
                            <input type="password" name="current_password" class="form-control form-control-sm" placeholder="ពាក្យសម្ងាត់បច្ចុប្បន្ន">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="password" name="new_password" class="form-control form-control-sm" placeholder="ពាក្យសម្ងាត់ថ្មី">
                            </div>
                            <div class="col-6">
                                <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="ផ្ទៀងផ្ទាត់ពាក្យសម្ងាត់ថ្មី">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle me-1"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Staff Member -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-plus text-primary me-2"></i> បន្ថែមសមាជិកក្រុមថ្មី
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo APP_URL; ?>/staff/invite">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="ឧទាហរណ៍៖ សុខ ពិសី" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="staff@company.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                        <input type="text" name="phone" class="form-control" placeholder="012 345 678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">តួនាទី / សិទ្ធិ <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select" required>
                            <option value="">ជ្រើសរើសតួនាទី...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo e($r['name']); ?> &mdash; <?php echo e($r['description']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ពាក្យសម្ងាត់ដំបូង</label>
                        <input type="text" name="password" class="form-control" value="Staff@123456" required>
                        <div class="form-text small">បុគ្គលិកអាចប្តូរពាក្យសម្ងាត់នេះបានពេលចូលប្រើប្រាស់លើកដំបូង។</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">បង្កើតគណនីបុគ្គលិក</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals: Edit Other Staff Members -->
<?php if (!empty($staff)): ?>
    <?php foreach ($staff as $s): ?>
        <?php if ($s['id'] !== Auth::id()): ?>
            <div class="modal fade" id="editStaffModal_<?php echo $s['id']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-pencil-square text-primary me-2"></i> កែប្រែព័ត៌មាន៖ <?php echo e($s['name']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="<?php echo APP_URL; ?>/staff/<?php echo $s['id']; ?>/edit">
                            <?php echo csrfField(); ?>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo e($s['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">អាសយដ្ឋានអ៊ីមែល</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo e($s['email']); ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo e($s['phone'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">តួនាទី / សិទ្ធិ <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-select" required>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?php echo $r['id']; ?>" <?php echo ($r['slug'] === $s['role_slug']) ? 'selected' : ''; ?>>
                                                <?php echo e($r['name']); ?> &mdash; <?php echo e($r['description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ស្ថានភាពគណនី</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?php echo ($s['status'] === 'active') ? 'selected' : ''; ?>>សកម្ម (Active)</option>
                                        <option value="inactive" <?php echo ($s['status'] === 'inactive') ? 'selected' : ''; ?>>អសកម្ម (Inactive)</option>
                                        <option value="suspended" <?php echo ($s['status'] === 'suspended') ? 'selected' : ''; ?>>ផ្អាក (Suspended)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">កំណត់ពាក្យសម្ងាត់ថ្មី (ទុកទំនេរប្រសិនបើមិនផ្លាស់ប្តូរ)</label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                                <button type="submit" class="btn btn-primary">រក្សាទុកការកែប្រែ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalPhotoInput = document.getElementById('modalProfilePhotoInput');
    const modalPreview = document.getElementById('modalMyAvatarPreview');
    if (modalPhotoInput && modalPreview) {
        modalPhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    modalPreview.innerHTML = '<img src="' + evt.target.result + '" class="rounded-circle object-fit-cover border shadow-sm" style="width:80px;height:80px;">';
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>
