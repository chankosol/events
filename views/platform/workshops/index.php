<?php
// views/platform/workshops/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">សិក្ខាសាលាទាំងអស់ (Platform Workshops)</h2>
        <p class="text-muted mb-0">តាមដាន និងគ្រប់គ្រងរាល់សិក្ខាសាលាទូទាំងប្រព័ន្ធ Workshop OS។</p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-primary fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-collection me-1"></i> សរុប៖ <?php echo number_format($totalWorkshops ?? 0); ?>
        </span>
        <span class="badge bg-success fs-6 px-3 py-2 d-flex align-items-center">
            <i class="bi bi-play-circle me-1"></i> សកម្ម៖ <?php echo number_format($activeWorkshops ?? 0); ?>
        </span>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/platform/workshops" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះសិក្ខាសាលា ក្រុមហ៊ុន ឬអ្នកទំនាក់ទំនង..." value="<?php echo e($_GET['q'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="business_id" class="form-select">
                    <option value="">គ្រប់ស្ថាប័ន / ក្រុមហ៊ុន</option>
                    <?php if (!empty($businesses)): ?>
                        <?php foreach ($businesses as $biz): ?>
                            <option value="<?php echo $biz['id']; ?>" <?php echo ((int)($_GET['business_id'] ?? 0) === (int)$biz['id']) ? 'selected' : ''; ?>>
                                <?php echo e($biz['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="draft" <?php echo (($_GET['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>ព្រាង (Draft)</option>
                    <option value="pending_payment" <?php echo (($_GET['status'] ?? '') === 'pending_payment') ? 'selected' : ''; ?>>រង់ចាំបង់ថ្លៃ (Pending Fee)</option>
                    <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>សកម្ម (Active)</option>
                    <option value="registration_open" <?php echo (($_GET['status'] ?? '') === 'registration_open') ? 'selected' : ''; ?>>បើកទទួលចុះឈ្មោះ</option>
                    <option value="in_progress" <?php echo (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : ''; ?>>កំពុងដំណើរការ</option>
                    <option value="completed" <?php echo (($_GET['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>បានបញ្ចប់</option>
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

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>សិក្ខាសាលា</th>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>កាលបរិច្ឆេទ</th>
                    <th>ចំណុះ (Capacity)</th>
                    <th>បានចុះឈ្មោះ</th>
                    <th>វត្តមាន</th>
                    <th>ថ្លៃប្រព័ន្ធ</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workshops)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            មិនមានសិក្ខាសាលាត្រូវនឹងលក្ខខណ្ឌស្វែងរកឡើយ។
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workshops as $w): ?>
                        <?php 
                            $canApprove = ($w['billing_status'] !== 'paid' && $w['billing_status'] !== 'waived') || in_array($w['status'], ['draft', 'pending_payment']);
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo e($w['name']); ?></div>
                                <div class="text-muted small">
                                    <i class="bi bi-geo-alt"></i> <?php echo e($w['venue'] ?: 'តាមអនឡាញ'); ?>
                                    <?php if (!empty($w['trainer_name'])): ?>
                                        &bull; <i class="bi bi-person"></i> <?php echo e($w['trainer_name']); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-start">
                                    <div class="me-2 flex-shrink-0 pt-1">
                                        <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $w['business_id']; ?>" title="មើលព័ត៌មានស្ថាប័ន">
                                            <?php echo businessLogoHtml(['name' => $w['business_name'], 'logo_path' => $w['logo_path'] ?? ''], 28); ?>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $w['business_id']; ?>" class="fw-semibold small text-decoration-none text-dark d-block">
                                            <?php echo e($w['business_name']); ?>
                                        </a>
                                        <?php if (!empty($w['contact_person']) || !empty($w['business_phone'])): ?>
                                            <div class="text-muted" style="font-size: 0.78rem; line-height: 1.35;">
                                                <?php if (!empty($w['contact_person'])): ?>
                                                    <span class="d-inline-block text-secondary">
                                                        <i class="bi bi-person me-1"></i><?php echo e($w['contact_person']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($w['business_phone'])): ?>
                                                    <span class="d-inline-block text-nowrap ms-1 text-secondary">
                                                        <i class="bi bi-telephone me-1"></i><a href="tel:<?php echo e($w['business_phone']); ?>" class="text-decoration-none text-muted"><?php echo e($w['business_phone']); ?></a>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?php echo !empty($w['start_date']) ? date('d/m/Y', strtotime($w['start_date'])) : '-'; ?></div>
                                <?php if (!empty($w['start_time'])): ?>
                                    <div class="text-muted small"><?php echo date('H:i', strtotime($w['start_time'])); ?> - <?php echo !empty($w['end_time']) ? date('H:i', strtotime($w['end_time'])) : ''; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo number_format($w['capacity']); ?></span> នាក់
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                                    <?php echo number_format($w['reg_count']); ?> / <?php echo number_format($w['capacity']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">
                                    <?php echo number_format($w['attend_count']); ?> នាក់
                                </span>
                            </td>
                            <td>
                                <?php if (isset($w['platform_fee']) && $w['platform_fee'] !== null): ?>
                                    <div class="fw-bold text-success">$<?php echo number_format($w['platform_fee'], 2); ?></div>
                                    <div class="small">
                                        <?php if ($w['billing_status'] === 'paid'): ?>
                                            <span class="badge bg-success">បានទូទាត់</span>
                                        <?php elseif ($w['billing_status'] === 'waived'): ?>
                                            <span class="badge bg-info text-dark">លើកលែងថ្លៃ</span>
                                        <?php elseif ($w['billing_status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">រង់ចាំត្រួតពិនិត្យ</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">មិនទាន់បង់</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo statusBadge($w['status']); ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php if ($canApprove): ?>
                                    <button type="button" class="btn btn-sm btn-success text-nowrap me-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#approveModal-<?php echo $w['id']; ?>" title="អនុម័ត និងបើកដំណើរការ (លើកលែងថ្លៃប្រព័ន្ធ)">
                                        <i class="bi bi-check-circle me-1"></i> អនុម័ត
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle me-1 py-1 px-2" title="បានអនុម័ត និងដំណើរការរួច">
                                        <i class="bi bi-check2-circle me-1"></i> រួចរាល់
                                    </span>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary me-1" title="មើលផ្ទាំងគ្រប់គ្រងព្រឹត្តិការណ៍">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (!empty($w['slug'])): ?>
                                    <a href="<?php echo APP_URL; ?>/event/<?php echo e($w['slug']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="ទំព័រសាធារណៈ">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php /* Approval Modals */ ?>
<?php if (!empty($workshops)): ?>
    <?php foreach ($workshops as $w): ?>
        <?php 
            $canApprove = ($w['billing_status'] !== 'paid' && $w['billing_status'] !== 'waived') || in_array($w['status'], ['draft', 'pending_payment']);
            if (!$canApprove) continue;
        ?>
        <div class="modal fade text-start" id="approveModal-<?php echo $w['id']; ?>" tabindex="-1" aria-labelledby="approveModalLabel-<?php echo $w['id']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="<?php echo APP_URL; ?>/platform/workshops/<?php echo $w['id']; ?>/approve">
                        <?php echo csrfField(); ?>
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fs-6 fw-bold" id="approveModalLabel-<?php echo $w['id']; ?>">
                                <i class="bi bi-patch-check-fill me-1"></i> អនុម័ត និងបើកដំណើរការសិក្ខាសាលា
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-info py-2 px-3 small mb-3">
                                <i class="bi bi-info-circle me-1"></i> មុខងារនេះសម្រាប់ Super Admin អនុម័តឱ្យម្ចាស់អាជីវកម្មប្រើប្រាស់ប្រព័ន្ធ <strong>ដោយមិនបាច់បង់ប្រាក់</strong> (Waive Fee) ឬបានទូទាត់ក្រៅប្រព័ន្ធរួច។
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold mb-1">ឈ្មោះសិក្ខាសាលា៖</label>
                                <div class="fw-bold fs-6 text-dark"><?php echo e($w['name']); ?></div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold mb-1">ស្ថាប័ន / ក្រុមហ៊ុន៖</label>
                                    <div class="fw-semibold text-dark"><?php echo e($w['business_name']); ?></div>
                                    <?php if (!empty($w['contact_person']) || !empty($w['business_phone'])): ?>
                                        <div class="text-muted small">
                                            <?php echo e($w['contact_person']); ?><?php echo (!empty($w['contact_person']) && !empty($w['business_phone'])) ? ' &bull; ' : ''; ?><?php echo e($w['business_phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-semibold mb-1">តម្លៃប្រព័ន្ធ៖</label>
                                    <div class="fw-bold text-success fs-6">
                                        $<?php echo number_format((float)($w['platform_fee'] ?? 0), 2); ?>
                                        <span class="badge bg-warning text-dark ms-1">នឹងត្រូវលើកលែង/Approved</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="admin_note_<?php echo $w['id']; ?>" class="form-label small fw-semibold">កំណត់ចំណាំរបស់ Admin (ស្រេចចិត្ត)៖</label>
                                <textarea class="form-control form-control-sm" id="admin_note_<?php echo $w['id']; ?>" name="admin_note" rows="2" placeholder="ឧទាហរណ៍៖ អនុម័តតាមសំណើរបស់ម្ចាស់អាជីវកម្ម ឬសហការពិសេស"></textarea>
                            </div>

                            <div class="p-3 bg-light rounded small text-muted">
                                <i class="bi bi-check-circle-fill text-success me-1"></i> ក្រោយពីចុចអនុម័ត៖
                                <ul class="mb-0 mt-1 ps-3">
                                    <li>ស្ថានភាពសិក្ខាសាលានឹងក្លាយជា <strong>សកម្ម (Active)</strong></li>
                                    <li>ស្ថានភាពបង់ប្រាក់នឹងក្លាយជា <strong>បានទូទាត់ (Paid / Waived)</strong></li>
                                    <li>ម្ចាស់អាជីវកម្មអាចកំណត់ទម្រង់ និងបើកទទួលការចុះឈ្មោះភ្លាមៗ</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                            <button type="submit" class="btn btn-sm btn-success px-3 shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> យល់ព្រមអនុម័តដំណើរការ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
