<?php
// views/platform/businesses/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">បញ្ជីស្ថាប័ន / ក្រុមហ៊ុន (Tenants)</h2>
        <p class="text-muted mb-0">ក្រុមហ៊ុន និងស្ថាប័នទាំងអស់ដែលបានចុះឈ្មោះលើ Workshop OS។</p>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/platform/businesses" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះក្រុមហ៊ុន អ្នកទំនាក់ទំនង ឬអ៊ីមែល..." value="<?php echo e($_GET['q'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>សកម្ម (Active)</option>
                    <option value="pending" <?php echo (($_GET['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>រង់ចាំ (Pending)</option>
                    <option value="suspended" <?php echo (($_GET['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>ផ្អាក (Suspended)</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">ស្វែងរក</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>អ្នកទំនាក់ទំនង</th>
                    <th>ទីតាំង</th>
                    <th>សិក្ខាសាលា</th>
                    <th>សិក្ខាកាមសរុប</th>
                    <th>ថ្លៃប្រព័ន្ធសរុប</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($businesses)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">មិនទាន់មានស្ថាប័នចុះឈ្មោះនៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($businesses as $b): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3 flex-shrink-0">
                                        <?php echo businessLogoHtml($b, 42); ?>
                                    </div>
                                    <div>
                                        <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $b['id']; ?>" class="fw-bold text-dark text-decoration-none">
                                            <?php echo e($b['name']); ?>
                                        </a>
                                        <div class="text-muted small"><?php echo e($b['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo e($b['contact_person']); ?></div>
                                <div class="text-muted small"><?php echo e($b['phone']); ?></div>
                            </td>
                            <td><?php echo e($b['city'] ?: $b['country'] ?: 'កម្ពុជា'); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $b['workshop_count']; ?></span></td>
                            <td><?php echo number_format($b['participant_count']); ?> នាក់</td>
                            <td class="fw-bold text-success">$<?php echo number_format($b['platform_fee_total'], 2); ?></td>
                            <td><?php echo statusBadge($b['status']); ?></td>
                            <td class="text-end">
                                <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    គ្រប់គ្រង
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
