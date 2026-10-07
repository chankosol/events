<div class="container-fluid py-4">
    <h1 class="h3 mb-4 text-gray-800 fw-bold">ទិដ្ឋភាពទូទៅនៃរបាយការណ៍ស្ថាប័ន</h1>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 border-start border-primary border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">សិក្ខាសាលាសរុប</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['total_workshops'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 border-start border-success border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ចំណូលសរុប</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">$<?= number_format((float)($stats['total_revenue'] ?? 0), 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 border-start border-info border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">អត្រាវត្តមាន</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= (float)($stats['attendance_rate'] ?? 0) ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 border-start border-warning border-4 shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">សិក្ខាកាមសរុប</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800"><?= $stats['total_participants'] ?> នាក់</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workshop Performance Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">ប្រសិទ្ធភាពតាមសិក្ខាសាលា</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ឈ្មោះសិក្ខាសាលា</th>
                            <th>កាលបរិច្ឆេទ</th>
                            <th>ការចុះឈ្មោះ</th>
                            <th>វត្តមាន</th>
                            <th>ចំណូល</th>
                            <th>ការវាយតម្លៃ</th>
                            <th>សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workshops as $w): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($w['name']) ?></td>
                            <td><?= date('Y-m-d', strtotime($w['start_date'])) ?></td>
                            <td><?= (int)($w['confirmed'] ?? 0) ?> / <?= (int)($w['capacity'] ?? 0) ?></td>
                            <td><?= ((int)($w['confirmed'] ?? 0) > 0) ? round(((int)($w['attended'] ?? 0) / (int)($w['confirmed'] ?? 1)) * 100) : 0 ?>%</td>
                            <td class="text-success fw-bold">$<?= number_format((float)($w['revenue'] ?? 0), 2) ?></td>
                            <td><?= number_format((float)($w['avg_feedback'] ?? 0), 1) ?>/5.0</td>
                            <td>
                                <a href="<?= APP_URL ?>/reports/workshop/<?= $w['id'] ?>" class="btn btn-sm btn-info text-white">របាយការណ៍លម្អិត</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($workshops)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">មិនមានសិក្ខាសាលានៅឡើយទេ។</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
