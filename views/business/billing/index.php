<?php
// views/business/billing/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ការទូទាត់ថ្លៃប្រព័ន្ធ & វិក្កយបត្រ</h2>
        <p class="text-muted mb-0">ទិដ្ឋភាពទូទៅនៃថ្លៃដំណើរការសិក្ខាសាលាតែមួយលើកគត់ និងវិក្កយបត្រប្រព័ន្ធ។</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">ថ្លៃប្រព័ន្ធសរុបដែលបានបង់</div>
                <div class="fs-2 fw-bold text-success mt-1">$<?php echo number_format($totalPaid, 2); ?> USD</div>
                <div class="text-muted small">ថ្លៃសេវាដំណើរការសិក្ខាសាលាតែមួយលើកគត់</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">ចំនួនទឹកប្រាក់រង់ចាំដំណើរការ</div>
                <div class="fs-2 fw-bold text-warning mt-1">$<?php echo number_format($totalPending, 2); ?> USD</div>
                <div class="text-muted small">កំពុងរង់ចាំការទូទាត់ ឬការផ្ទៀងផ្ទាត់</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> វិក្កយបត្រសិក្ខាសាលា</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>លេខវិក្កយបត្រ</th>
                    <th>សិក្ខាសាលា</th>
                    <th>ចំណុះ</th>
                    <th>កម្រិតតម្លៃ</th>
                    <th>ថ្លៃប្រព័ន្ធ</th>
                    <th>កាលបរិច្ឆេទកំណត់</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($billings)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">មិនមានវិក្កយបត្រនោះទេ។</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($billings as $b): ?>
                        <tr>
                            <td><span class="font-monospace fw-bold"><?php echo e($b['invoice_number']); ?></span></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>/workshops/<?php echo $b['workshop_id']; ?>" class="fw-bold text-dark text-decoration-none">
                                    <?php echo e($b['workshop_name']); ?>
                                </a>
                            </td>
                            <td><?php echo number_format($b['capacity']); ?> នាក់</td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($b['pricing_tier'] ?? 'ស្តង់ដារ'); ?></span></td>
                            <td class="fw-bold">$<?php echo number_format($b['platform_fee'], 2); ?> <?php echo e($b['currency']); ?></td>
                            <td><?php echo formatDate($b['due_date']); ?></td>
                            <td><?php echo statusBadge($b['payment_status']); ?></td>
                            <td class="text-end">
                                <?php if ($b['payment_status'] !== 'paid'): ?>
                                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $b['workshop_id']; ?>/activate" class="btn btn-sm btn-primary">
                                        បង់ប្រាក់ & ដំណើរការ
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> បានបង់ & ផ្ទៀងផ្ទាត់រួច
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
