<?php
// views/platform/users/index.php
$activeTab = $_GET['tab'] ?? 'staff';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">គ្រប់គ្រងអ្នកប្រើប្រាស់ (Users Management)</h2>
        <p class="text-muted mb-0">គ្រប់គ្រងគណនីបុគ្គលិក ម្ចាស់ស្ថាប័ន និងសិក្ខាកាមទូទាំងប្រព័ន្ធ Workshop OS។</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-person-badge me-1"></i> បុគ្គលិក & អ្នកគ្រប់គ្រង៖ <?php echo number_format($totalStaff); ?>
        </span>
        <span class="badge bg-secondary fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-people me-1"></i> សិក្ខាកាម៖ <?php echo number_format($totalParticipants); ?>
        </span>
    </div>
</div>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link fw-bold <?php echo ($activeTab !== 'participants') ? 'active text-primary' : 'text-muted'; ?>" href="<?php echo APP_URL; ?>/platform/users?tab=staff">
            <i class="bi bi-person-gear me-1"></i> អ្នកគ្រប់គ្រង & បុគ្គលិកស្ថាប័ន (<?php echo count($users); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold <?php echo ($activeTab === 'participants') ? 'active text-primary' : 'text-muted'; ?>" href="<?php echo APP_URL; ?>/platform/users?tab=participants">
            <i class="bi bi-person-heart me-1"></i> សិក្ខាកាមទាំងអស់ (<?php echo count($participants); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link fw-bold text-muted" href="<?php echo APP_URL; ?>/platform/roles">
            <i class="bi bi-shield-check me-1"></i> តួនាទី & សិទ្ធិ (RBAC Roles & Matrix)
        </a>
    </li>
</ul>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/platform/users" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="<?php echo e($activeTab); ?>">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះ អ៊ីមែល លេខទូរស័ព្ទ..." value="<?php echo e($_GET['q'] ?? ''); ?>">
            </div>
            <?php if ($activeTab !== 'participants'): ?>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">គ្រប់តួនាទី (All Roles)</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo e($r['slug']); ?>" <?php echo (($_GET['role'] ?? '') === $r['slug']) ? 'selected' : ''; ?>>
                                <?php echo e($r['name']); ?> (<?php echo $r['scope'] === 'platform' ? 'Platform' : 'Business'; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <div class="col-md-3">
                    <input type="text" class="form-control" disabled placeholder="គណនីសិក្ខាកាម (Global Profile)">
                </div>
            <?php endif; ?>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>សកម្ម (Active)</option>
                    <option value="inactive" <?php echo (($_GET['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>អសកម្ម (Inactive)</option>
                    <option value="suspended" <?php echo (($_GET['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>ផ្អាក (Suspended)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> ស្វែងរក
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($activeTab !== 'participants'): ?>
    <!-- Staff / Admins Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ឈ្មោះ & អ៊ីមែល</th>
                        <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                        <th>តួនាទី</th>
                        <th>លេខទូរស័ព្ទ</th>
                        <th>ចូលប្រព័ន្ធចុងក្រោយ</th>
                        <th>ស្ថានភាព</th>
                        <th class="text-end">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-2"></i>
                                មិនមានអ្នកប្រើប្រាស់ត្រូវនឹងលក្ខខណ្ឌស្វែងរកឡើយ។
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            <?php echo strtoupper(mb_substr($u['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($u['name']); ?></div>
                                            <div class="text-muted small"><?php echo e($u['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($u['business_name'])): ?>
                                        <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $u['business_id']; ?>" class="d-inline-flex align-items-center text-decoration-none text-dark">
                                            <div class="me-2">
                                                <?php echo businessLogoHtml(['name' => $u['business_name']], 24); ?>
                                            </div>
                                            <span class="small fw-semibold"><?php echo e($u['business_name']); ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-shield-fill-check me-1"></i> អ្នកគ្រប់គ្រងប្រព័ន្ធ (Platform)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">
                                        <?php echo e($u['role_names'] ?: 'គ្មានតួនាទី'); ?>
                                    </span>
                                </td>
                                <td><?php echo e($u['phone'] ?: '-'); ?></td>
                                <td>
                                    <div class="small"><?php echo !empty($u['last_login_at']) ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : 'មិនទាន់ចូល'; ?></div>
                                    <?php if (!empty($u['last_login_ip'])): ?>
                                        <div class="text-muted small" style="font-size: 0.75rem;"><?php echo e($u['last_login_ip']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo statusBadge($u['status']); ?></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#roleModal<?php echo $u['id']; ?>" title="កំណត់តួនាទី">
                                        <i class="bi bi-shield-lock me-1"></i> តួនាទី
                                    </button>
                                    <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
                                        <form method="POST" action="<?php echo APP_URL; ?>/platform/users/<?php echo $u['id']; ?>/toggle" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់ផ្លាស់ប្តូរស្ថានភាពអ្នកប្រើប្រាស់នេះមែនទេ?');">
                                            <?php echo csrfField(); ?>
                                            <button type="submit" class="btn btn-sm <?php echo $u['status'] === 'active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>">
                                                <i class="bi <?php echo $u['status'] === 'active' ? 'bi-lock' : 'bi-unlock'; ?> me-1"></i>
                                                <?php echo $u['status'] === 'active' ? 'ផ្អាកដំណើរការ' : 'បើកដំណើរការ'; ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">គណនីបច្ចុប្បន្ន</span>
                                    <?php endif; ?>

                                    <!-- Modal: Change Role -->
                                    <div class="modal fade text-start" id="roleModal<?php echo $u['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i> កំណត់តួនាទីអ្នកប្រើប្រាស់</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="<?php echo APP_URL; ?>/platform/users/<?php echo $u['id']; ?>/role">
                                                    <?php echo csrfField(); ?>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label text-muted small">អ្នកប្រើប្រាស់</label>
                                                            <div class="fw-bold fs-6"><?php echo e($u['name']); ?> (<?php echo e($u['email']); ?>)</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold">ជ្រើសរើសតួនាទីថ្មី</label>
                                                            <select name="role_id" class="form-select" required>
                                                                <?php foreach ($roles as $r): ?>
                                                                    <option value="<?php echo $r['id']; ?>" <?php echo in_array($r['slug'], explode(',', $u['role_slugs'] ?? ''), true) ? 'selected' : ''; ?>>
                                                                        <?php echo e($r['name']); ?> (<?php echo $r['scope'] === 'platform' ? 'Platform' : 'Tenant'; ?>) &mdash; <?php echo e($r['description']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                                                        <button type="submit" class="btn btn-primary fw-bold">រក្សាទុកតួនាទី</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- Participants Table -->
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ឈ្មោះ & អ៊ីមែល</th>
                        <th>ក្រុមហ៊ុន / ស្ថាប័ន</th>
                        <th>លេខទូរស័ព្ទ</th>
                        <th>ទីក្រុង / ខេត្ត</th>
                        <th>ចំនួនសិក្ខាសាលាបានចុះឈ្មោះ</th>
                        <th>ស្ថានភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2"></i>
                                មិនមានសិក្ខាកាមត្រូវនឹងលក្ខខណ្ឌស្វែងរកឡើយ។
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($participants as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                            <?php echo strtoupper(mb_substr($p['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($p['name']); ?></div>
                                            <div class="text-muted small"><?php echo e($p['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo e($p['company'] ?: '-'); ?></div>
                                    <?php if (!empty($p['position'])): ?>
                                        <div class="text-muted small"><?php echo e($p['position']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($p['phone'] ?: '-'); ?></td>
                                <td><?php echo e($p['province'] ?: $p['city'] ?: 'កម្ពុជា'); ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <i class="bi bi-journal-check me-1"></i> <?php echo number_format($p['reg_count']); ?> វគ្គ
                                    </span>
                                </td>
                                <td><?php echo statusBadge($p['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
