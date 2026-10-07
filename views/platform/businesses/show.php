<?php
// views/platform/businesses/show.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3 mb-1">
            <?php echo businessLogoHtml($business, 56); ?>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h3 fw-bold mb-0"><?php echo e($business['name']); ?></h2>
                    <?php echo statusBadge($business['status']); ?>
                </div>
                <p class="text-muted mb-0 small">កូដសម្គាល់ស្ថាប័ន: #<?php echo $business['id']; ?> &bull; អ្នកទំនាក់ទំនង: <?php echo e($business['contact_person']); ?> (<?php echo e($business['email']); ?>)</p>
            </div>
        </div>
    </div>
    <a href="<?php echo APP_URL; ?>/platform/businesses" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅបញ្ជីស្ថាប័ន
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">ព័ត៌មានលម្អិតស្ថាប័ន</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th class="text-muted w-40">លេខទូរស័ព្ទ:</th><td><?php echo e($business['phone']); ?></td></tr>
                    <tr><th class="text-muted">ប្រទេស:</th><td><?php echo e($business['country']); ?></td></tr>
                    <tr><th class="text-muted">រាជធានី/ខេត្ត:</th><td><?php echo e($business['city']); ?></td></tr>
                    <tr><th class="text-muted">គេហទំព័រ:</th><td><a href="<?php echo e($business['website']); ?>" target="_blank"><?php echo e($business['website']); ?></a></td></tr>
                    <tr><th class="text-muted">កាលបរិច្ឆេទចុះឈ្មោះ:</th><td><?php echo formatDate($business['created_at']); ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">សិក្ខាសាលាដែលបានរៀបចំ (<?php echo count($workshops); ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>សិក្ខាសាលា</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ចំណុះ</th>
                            <th>ស្ថានភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($workshops)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">មិនទាន់មានសិក្ខាសាលានៅឡើយទេ។</td></tr>
                        <?php else: ?>
                            <?php foreach ($workshops as $w): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo e($w['name']); ?></td>
                                    <td><?php echo formatDate($w['start_date']); ?></td>
                                    <td><?php echo number_format($w['capacity']); ?> នាក់</td>
                                    <td><?php echo statusBadge($w['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
