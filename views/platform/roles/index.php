<?php
// views/platform/roles/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">តួនាទី & សិទ្ធិអនុញ្ញាត (Roles & Permissions / RBAC)</h2>
        <p class="text-muted mb-0">គ្រប់គ្រង និងកែប្រែសិទ្ធិតាមតួនាទី (Role-Based Access Control) កម្រិតម៉ត់ចត់ខ្ពស់ (Granular Permissions)។</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-shield-lock me-1"></i> <?php echo $totalRoles; ?> តួនាទីផ្លូវការ
        </span>
        <span class="badge bg-success fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-key-fill me-1"></i> <?php echo $totalPermissions; ?> សិទ្ធិអនុញ្ញាតម៉ត់ចត់
        </span>
    </div>
</div>

<ul class="nav nav-tabs mb-4" id="roleTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link fw-bold" id="roles-cards-tab" data-bs-toggle="tab" data-bs-target="#rolesCards" type="button">
            <i class="bi bi-person-badge me-1"></i> បញ្ជីតួនាទី & សិទ្ធិលម្អិត (Roles Directory)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link active fw-bold" id="matrix-tab" data-bs-toggle="tab" data-bs-target="#matrixView" type="button">
            <i class="bi bi-grid-3x3-gap-fill me-1"></i> តារាងម៉ាទ្រីសសិទ្ធិ (RBAC Permission Matrix & Editor)
        </button>
    </li>
</ul>

<div class="tab-content" id="roleTabContent">
    <!-- TAB 1: Roles Directory Cards -->
    <div class="tab-pane fade" id="rolesCards">
        <!-- Platform Scope Roles -->
        <div class="d-flex align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-shield-fill-check me-2"></i> តួនាទីកម្រិតប្រព័ន្ធវេទិកា (Platform Scope Roles)</h5>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2">៤ តួនាទី</span>
        </div>

        <div class="row g-3 mb-5">
            <?php foreach ($roles as $r): ?>
                <?php if ($r['scope'] !== 'platform') continue; ?>
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100 shadow-sm border-0 border-top border-danger border-3">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0 text-dark"><?php echo e($r['name']); ?></h5>
                                <span class="badge bg-danger"><?php echo e($r['slug']); ?></span>
                            </div>
                            <p class="text-muted small mb-3 flex-grow-1" style="min-height: 38px;"><?php echo e($r['description']); ?></p>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mb-3">
                                <div>
                                    <span class="small text-muted">សិទ្ធិអនុញ្ញាត៖</span>
                                    <span class="fw-bold text-success" id="role-count-card-<?php echo $r['id']; ?>"><?php echo $r['perm_count']; ?></span>
                                </div>
                                <div>
                                    <span class="small text-muted">អ្នកប្រើប្រាស់៖</span>
                                    <span class="badge bg-light text-dark border"><?php echo $r['user_count']; ?></span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-danger w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#editPermModal<?php echo $r['id']; ?>">
                                <i class="bi bi-pencil-square me-1"></i> កែសម្រួលសិទ្ធិតួនាទីនេះ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Business Scope Roles -->
        <div class="d-flex align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-building me-2"></i> តួនាទីកម្រិតស្ថាប័ន / ក្រុមហ៊ុន (Business Scope Roles)</h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2">១១ តួនាទី</span>
        </div>

        <div class="row g-3 mb-4">
            <?php foreach ($roles as $r): ?>
                <?php if ($r['scope'] !== 'business') continue; ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-primary border-3">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 text-dark fs-6"><?php echo e($r['name']); ?></h6>
                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.75rem;"><?php echo e($r['slug']); ?></span>
                            </div>
                            <p class="text-muted small mb-3 flex-grow-1" style="min-height: 38px;"><?php echo e($r['description']); ?></p>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top mb-3">
                                <div>
                                    <span class="small text-muted">សិទ្ធិអនុញ្ញាត៖</span>
                                    <span class="fw-bold text-primary" id="role-count-card-<?php echo $r['id']; ?>"><?php echo $r['perm_count']; ?></span>
                                </div>
                                <div>
                                    <span class="small text-muted">អ្នកប្រើប្រាស់៖</span>
                                    <span class="badge bg-light text-dark border"><?php echo $r['user_count']; ?></span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-primary w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#editPermModal<?php echo $r['id']; ?>">
                                <i class="bi bi-pencil-square me-1"></i> កែសម្រួលសិទ្ធិតួនាទីនេះ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- TAB 2: RBAC Matrix Table & One-Click Editor -->
    <div class="tab-pane fade show active" id="matrixView">
        <div class="alert alert-primary py-2 px-3 small d-flex align-items-center justify-content-between mb-3 border-0 shadow-sm">
            <div>
                <i class="bi bi-hand-index-thumb-fill me-2 fs-5"></i>
                <strong>មុខងារកែប្រែសិទ្ធិដោយផ្ទាល់ (One-Click Toggle Editor)៖</strong>
                ចុចលើរូបសញ្ញាក្នុងតារាងខាងក្រោម ដើម្បីបើក <i class="bi bi-check-circle-fill text-success"></i> ឬបិទ <i class="bi bi-dash-circle text-muted"></i> សិទ្ធិនីមួយៗភ្លាមៗដោយស្វ័យប្រវត្តិ (Real-Time Auto-Save with Audit Logging)។
            </div>
            <span class="badge bg-light text-dark border ms-2">ចុចលើ Cell ដើម្បីកែប្រែ</span>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i> ម៉ាទ្រីសសិទ្ធិអនុញ្ញាត & កែប្រែផ្ទាល់ (Detailed RBAC Matrix & Toggle Editor)</h5>
                <div class="small text-muted">
                    <span class="me-3"><i class="bi bi-check-circle-fill text-success me-1"></i> មានសិទ្ធិ (Granted)</span>
                    <span><i class="bi bi-dash-circle text-muted me-1"></i> គ្មានសិទ្ធិ (Revoked)</span>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 720px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0 text-center" style="font-size: 0.82rem;">
                    <thead class="table-light sticky-top" style="z-index: 2;">
                        <tr>
                            <th class="text-start" style="min-width: 240px; background: #f8f9fa;">មុខងារ & សិទ្ធិអនុញ្ញាត (Permission)</th>
                            <?php foreach ($roles as $r): ?>
                                <th style="min-width: 95px; background: #f8f9fa;" class="<?php echo $r['scope'] === 'platform' ? 'text-danger' : 'text-primary'; ?>">
                                    <div class="small fw-bold"><?php echo e($r['name']); ?></div>
                                    <span class="badge <?php echo $r['scope'] === 'platform' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary'; ?>" style="font-size: 0.65rem;">
                                        <?php echo $r['scope'] === 'platform' ? 'Platform' : 'Tenant'; ?>
                                    </span>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $groupLabels = [
                            'workshop'     => 'ការគ្រប់គ្រងសិក្ខាសាលា (Workshop Management)',
                            'registration' => 'ការចុះឈ្មោះ (Registrations)',
                            'payment'      => 'ការទូទាត់ប្រាក់ (Payments)',
                            'checkin'      => 'វត្តមាន & ស្កេន QR (Attendance & Check-in)',
                            'qa'           => 'សំណួរផ្ទាល់ Live Q&A',
                            'polls'        => 'ការស្ទង់មតិផ្ទាល់ (Live Polls)',
                            'requests'     => 'សំណើពីសិក្ខាកាម (Requests)',
                            'gifts'        => 'កាដូ & រង្វាន់ (Gifts & Inventory)',
                            'certificates' => 'វិញ្ញាបនបត្រឌីជីថល (Certificates)',
                            'tests'        => 'ការធ្វើតេស្ត & កម្រងសំណួរ (Tests)',
                            'feedback'     => 'មតិកែលម្អ (Feedback)',
                            'reports'      => 'របាយការណ៍ & ស្ថិតិ (Reports)',
                            'staff'        => 'គ្រប់គ្រងបុគ្គលិក (Staff)',
                            'business'     => 'ការកំណត់ស្ថាប័ន (Business Settings)',
                            'billing'      => 'ថ្លៃសេវាប្រព័ន្ធ (Platform Billing)',
                            'platform'     => 'ស្នូលអ្នកគ្រប់គ្រងប្រព័ន្ធ (Super Admin Platform Core)',
                            'participant'  => 'ទម្រង់សិក្ខាកាម (Participant Profiles)',
                        ];
                        ?>
                        <?php foreach ($permGroups as $group => $groupPerms): ?>
                            <tr class="table-secondary">
                                <td colspan="<?php echo count($roles) + 1; ?>" class="text-start fw-bold py-2 ps-3">
                                    <i class="bi bi-folder-fill me-1 text-primary"></i>
                                    <?php echo $groupLabels[$group] ?? strtoupper($group); ?>
                                    <span class="badge bg-light text-dark border ms-2"><?php echo count($groupPerms); ?> សិទ្ធិ</span>
                                </td>
                            </tr>
                            <?php foreach ($groupPerms as $p): ?>
                                <tr>
                                    <td class="text-start ps-4">
                                        <div class="fw-semibold text-dark"><?php echo e($p['name']); ?></div>
                                        <div class="text-muted small font-monospace" style="font-size: 0.72rem;"><?php echo e($p['slug']); ?></div>
                                    </td>
                                    <?php foreach ($roles as $r): ?>
                                        <?php $hasPerm = !empty($rolePermMatrix[$r['id']][$p['slug']]); ?>
                                        <td class="p-0">
                                            <button type="button" 
                                                    class="btn w-100 h-100 py-2 border-0 perm-toggle-btn"
                                                    data-role-id="<?php echo $r['id']; ?>"
                                                    data-perm-id="<?php echo $p['id']; ?>"
                                                    data-role-name="<?php echo e($r['name']); ?>"
                                                    data-perm-name="<?php echo e($p['name']); ?>"
                                                    data-granted="<?php echo $hasPerm ? '1' : '0'; ?>"
                                                    title="ចុចដើម្បី <?php echo $hasPerm ? 'ដកសិទ្ធិ' : 'បន្ថែមសិទ្ធិ'; ?> «<?php echo e($p['name']); ?>» សម្រាប់ <?php echo e($r['name']); ?>"
                                                    style="cursor: pointer; background: transparent;">
                                                <?php if ($hasPerm): ?>
                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-dash-circle text-muted" style="opacity: 0.35; font-size: 1.05rem;"></i>
                                                <?php endif; ?>
                                            </button>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Batch Editing Permissions for each Role -->
<?php foreach ($roles as $r): ?>
    <div class="modal fade" id="editPermModal<?php echo $r['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-shield-lock text-primary me-2"></i> កែសម្រួលសិទ្ធិ៖ <?php echo e($r['name']); ?>
                        </h5>
                        <div class="text-muted small">ដែនកំណត់៖ <span class="badge <?php echo $r['scope'] === 'platform' ? 'bg-danger' : 'bg-primary'; ?>"><?php echo strtoupper($r['scope']); ?></span> &bull; Slug: <code><?php echo e($r['slug']); ?></code></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo APP_URL; ?>/platform/roles/<?php echo $r['id']; ?>/permissions">
                    <?php echo csrfField(); ?>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="bi bi-info-circle me-1"></i> ជ្រើសរើសសញ្ញាធីកលើសិទ្ធិដែលអ្នកចង់ផ្តល់ឱ្យតួនាទីនេះ រួចចុចប៊ូតុង <strong>រក្សាទុកសិទ្ធិ</strong> នៅខាងក្រោម។
                        </div>

                        <?php foreach ($permGroups as $group => $groupPerms): ?>
                            <div class="card mb-3 border">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                    <strong class="small text-dark">
                                        <i class="bi bi-folder2-open me-1 text-primary"></i> <?php echo $groupLabels[$group] ?? strtoupper($group); ?>
                                    </strong>
                                    <div>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;" onclick="toggleGroupCheckboxes(this, true)">ទាំងអស់</button>
                                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;" onclick="toggleGroupCheckboxes(this, false)">ដោះចេញ</button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <?php foreach ($groupPerms as $p): ?>
                                            <?php $isAssigned = !empty($rolePermMatrix[$r['id']][$p['slug']]); ?>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $p['id']; ?>" id="perm_<?php echo $r['id']; ?>_<?php echo $p['id']; ?>" <?php echo $isAssigned ? 'checked' : ''; ?>>
                                                    <label class="form-check-label small" for="perm_<?php echo $r['id']; ?>_<?php echo $p['id']; ?>">
                                                        <span class="fw-semibold text-dark"><?php echo e($p['name']); ?></span>
                                                        <div class="text-muted font-monospace" style="font-size: 0.7rem;"><?php echo e($p['slug']); ?></div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="bi bi-save me-1"></i> រក្សាទុកសិទ្ធិទាំងអស់
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- Toast Notification Container for AJAX Toggle -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="permToast" class="toast align-items-center text-white border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2" id="toastIcon"></i>
                <span id="toastMsg" class="small"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('permToast');
    const toastMsg = document.getElementById('toastMsg');
    const toastIcon = document.getElementById('toastIcon');
    
    toastMsg.textContent = message;
    if (isSuccess) {
        toastEl.className = 'toast align-items-center text-white bg-success border-0 shadow';
        toastIcon.className = 'bi bi-check-circle-fill fs-5 me-2';
    } else {
        toastEl.className = 'toast align-items-center text-white bg-danger border-0 shadow';
        toastIcon.className = 'bi bi-exclamation-triangle-fill fs-5 me-2';
    }
    
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

function toggleGroupCheckboxes(btn, checkAll) {
    const card = btn.closest('.card');
    if (!card) return;
    const checkboxes = card.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = checkAll);
}

// Interactive Matrix One-Click Toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.perm-toggle-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const roleId = this.getAttribute('data-role-id');
            const permId = this.getAttribute('data-perm-id');
            const roleName = this.getAttribute('data-role-name');
            const permName = this.getAttribute('data-perm-name');
            const currentGranted = this.getAttribute('data-granted') === '1';

            this.style.opacity = '0.5';

            fetch('<?php echo APP_URL; ?>/platform/roles/toggle-permission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    role_id: parseInt(roleId),
                    permission_id: parseInt(permId),
                    _csrf_token: csrfToken
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.style.opacity = '1';
                if (data.success) {
                    if (data.granted) {
                        btn.setAttribute('data-granted', '1');
                        btn.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-5"></i>';
                        btn.setAttribute('title', 'ចុចដើម្បី ដកសិទ្ធិ «' + permName + '» សម្រាប់ ' + roleName);
                        showToast(data.message || 'បានបន្ថែមសិទ្ធិដោយជោគជ័យ!', true);
                    } else {
                        btn.setAttribute('data-granted', '0');
                        btn.innerHTML = '<i class="bi bi-dash-circle text-muted" style="opacity: 0.35; font-size: 1.05rem;"></i>';
                        btn.setAttribute('title', 'ចុចដើម្បី បន្ថែមសិទ្ធិ «' + permName + '» សម្រាប់ ' + roleName);
                        showToast(data.message || 'បានដកសិទ្ធិដោយជោគជ័យ!', false);
                    }

                    // Also update corresponding checkbox in modal
                    const modalCb = document.getElementById('perm_' + roleId + '_' + permId);
                    if (modalCb) {
                        modalCb.checked = data.granted;
                    }
                } else {
                    showToast(data.message || 'មិនអាចផ្លាស់ប្តូរសិទ្ធិបានទេ!', false);
                }
            })
            .catch(err => {
                btn.style.opacity = '1';
                showToast('មានបញ្ហាក្នុងការតភ្ជាប់ សូមព្យាយាមម្តងទៀត។', false);
            });
        });
    });
});
</script>
