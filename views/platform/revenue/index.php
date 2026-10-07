<?php
// views/platform/revenue/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">របាយការណ៍ប្រាក់ចំណូលប្រព័ន្ធ</h2>
        <p class="text-muted mb-0">ប្រាក់ចំណូលរបស់ប្រព័ន្ធពីថ្លៃដំណើរការសិក្ខាសាលា និងការដំឡើងចំណុះ។</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase fw-bold">ប្រាក់ចំណូលសរុបរបស់ប្រព័ន្ធ (Gross Revenue)</div>
                <div class="display-5 fw-bold text-success mt-2">$<?php echo number_format($grossRevenue, 2); ?></div>
                <div class="text-muted small mt-2">ទទួលបានពីថ្លៃសេវាទូទាត់តាមសិក្ខាសាលា (Pay-per-workshop)</div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">ការបែងចែកចំណូលតាមខែ</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>ខែ</th>
                            <th>សិក្ខាសាលាដែលបានដំណើរការ</th>
                            <th class="text-end">ប្រាក់ចំណូល</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthlyRevenue)): ?>
                            <tr><td colspan="3" class="text-center py-3 text-muted">មិនទាន់មានទិន្នន័យចំណូលប្រចាំខែនៅឡើយទេ។</td></tr>
                        <?php else: ?>
                            <?php foreach ($monthlyRevenue as $mr): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo e($mr['month_name']); ?></td>
                                    <td><?php echo $mr['total_workshops']; ?> សិក្ខាសាលា</td>
                                    <td class="text-end fw-bold text-success">$<?php echo number_format($mr['revenue'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0">ស្ថាប័នដែលបានប្រើប្រាស់សេវាច្រើនជាងគេ</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>ចំនួនសិក្ខាសាលាដែលបានបង់</th>
                    <th class="text-end">ថ្លៃប្រព័ន្ធសរុប</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($businessRevenue)): ?>
                    <tr><td colspan="3" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យនៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($businessRevenue as $br): ?>
                        <tr>
                            <td class="fw-bold"><?php echo e($br['name']); ?></td>
                            <td><?php echo $br['paid_workshops']; ?> សិក្ខាសាលា</td>
                            <td class="text-end fw-bold text-success">$<?php echo number_format($br['total_spent'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
