<?php
// views/platform/invoices/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">វិក្កយបត្រប្រព័ន្ធ (Platform Invoices)</h2>
        <p class="text-muted mb-0">កំណត់ត្រាវិក្កយបត្រថ្លៃដំណើរការសិក្ខាសាលា និងការដំឡើងចំណុះទាំងអស់។</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">ទឹកប្រាក់វិក្កយបត្រសរុប</div>
                <div class="fs-2 fw-bold text-dark mt-1">$<?php echo number_format($totalInvoiced, 2); ?> USD</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">ចំណូលប្រមូលបានជាក់ស្តែង</div>
                <div class="fs-2 fw-bold text-success mt-1">$<?php echo number_format($totalPaid, 2); ?> USD</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>លេខវិក្កយបត្រ</th>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>សិក្ខាសាលា</th>
                    <th>ប្រភេទ</th>
                    <th>ទឹកប្រាក់</th>
                    <th>កាលបរិច្ឆេទចេញ</th>
                    <th>ស្ថានភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">មិនមានវិក្កយបត្រនោះទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td><span class="font-monospace fw-bold"><?php echo e($inv['invoice_number']); ?></span></td>
                            <td class="fw-bold"><?php echo e($inv['business_name']); ?></td>
                            <td><?php echo e($inv['workshop_name'] ?: 'N/A'); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo e($inv['invoice_type'] === 'workshop_activation' ? 'ដំណើរការសិក្ខាសាលា' : 'ដំឡើងចំណុះ'); ?></span></td>
                            <td class="fw-bold text-success">$<?php echo number_format($inv['amount'], 2); ?> <?php echo e($inv['currency']); ?></td>
                            <td><?php echo formatDate($inv['issue_date']); ?></td>
                            <td><?php echo statusBadge($inv['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
