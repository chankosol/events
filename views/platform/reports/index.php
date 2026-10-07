<?php
// views/platform/reports/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">របាយការណ៍សកលប្រព័ន្ធ (Global Analytics & Reports)</h2>
        <p class="text-muted mb-0">ទិន្នន័យសង្ខេបអំពីដំណើរការ ចំណូល និងស្ថិតិសិក្ខាកាមទូទាំងប្រព័ន្ធ Workshop OS។</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo APP_URL; ?>/platform/reports/export" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> ទាញយកជា CSV
        </a>
    </div>
</div>

<!-- 4 KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-success border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ចំណូលប្រព័ន្ធសរុប (Platform Fees)</div>
                        <div class="h3 fw-bold text-success mb-0 mt-1">$<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                    <div class="bg-success-subtle text-success p-3 rounded-circle">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i> ថ្លៃធ្វើឱ្យសកម្ម និងដំឡើង Capacity
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">ស្ថាប័ន / ក្រុមហ៊ុនសរុប (Tenants)</div>
                        <div class="h3 fw-bold text-primary mb-0 mt-1"><?php echo number_format($totalBusinesses); ?></div>
                    </div>
                    <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="text-success fw-bold"><?php echo number_format($activeBusinesses); ?></span> សកម្មក្នុងប្រព័ន្ធ
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-info border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">សិក្ខាសាលាសរុប (Workshops)</div>
                        <div class="h3 fw-bold text-info mb-0 mt-1"><?php echo number_format($totalWorkshops); ?></div>
                    </div>
                    <div class="bg-info-subtle text-info p-3 rounded-circle">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="text-primary fw-bold"><?php echo number_format($activeWorkshops); ?></span> កំពុងដំណើរការ &bull; <span class="text-secondary"><?php echo number_format($completedWorkshops); ?></span> បានបញ្ចប់
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-warning border-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">អត្រាវត្តមានមធ្យម (Attendance)</div>
                        <div class="h3 fw-bold text-warning mb-0 mt-1"><?php echo $attendanceRate; ?>%</div>
                    </div>
                    <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    <span class="fw-bold text-dark"><?php echo number_format($totalAttendance); ?></span> / <?php echo number_format($totalRegistrations); ?> សិក្ខាកាមបានចុះឈ្មោះ
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Businesses Ranking -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-trophy text-warning me-2"></i> ចំណាត់ថ្នាក់ស្ថាប័នឆ្នើម (Top Businesses)</h5>
        <span class="badge bg-light text-dark border">១០ ស្ថាប័នដំបូង</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>អ៊ីមែល</th>
                    <th class="text-center">សិក្ខាសាលាបង្កើត</th>
                    <th class="text-center">សិក្ខាកាមចុះឈ្មោះ</th>
                    <th class="text-end">ថ្លៃប្រព័ន្ធដែលបានបង់</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topBusinesses)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យស្ថាប័ននៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($topBusinesses as $idx => $tb): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge <?php echo $idx === 0 ? 'bg-warning text-dark' : ($idx === 1 ? 'bg-secondary' : 'bg-light text-dark border'); ?> rounded-circle me-2" style="width: 24px; height: 24px; line-height: 18px; text-align: center;">
                                        <?php echo $idx + 1; ?>
                                    </span>
                                    <div class="me-2">
                                        <?php echo businessLogoHtml($tb, 30); ?>
                                    </div>
                                    <span class="fw-bold text-dark"><?php echo e($tb['name']); ?></span>
                                </div>
                            </td>
                            <td><span class="text-muted small"><?php echo e($tb['email']); ?></span></td>
                            <td class="text-center"><span class="badge bg-light text-dark border"><?php echo number_format($tb['workshop_count']); ?></span></td>
                            <td class="text-center"><span class="fw-bold text-primary"><?php echo number_format($tb['registration_count']); ?> នាក់</span></td>
                            <td class="text-end fw-bold text-success">$<?php echo number_format($tb['total_fees'], 2); ?></td>
                            <td><?php echo statusBadge($tb['status']); ?></td>
                            <td class="text-end">
                                <a href="<?php echo APP_URL; ?>/platform/businesses/<?php echo $tb['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    ព័ត៌មានលម្អិត
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Workshops Performance Overview -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up text-primary me-2"></i> ប្រសិទ្ធភាពនៃសិក្ខាសាលា (Workshops Performance)</h5>
        <a href="<?php echo APP_URL; ?>/platform/workshops" class="btn btn-sm btn-outline-secondary">មើលទាំងអស់ &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>សិក្ខាសាលា</th>
                    <th>ស្ថាប័ន</th>
                    <th>កាលបរិច្ឆេទ</th>
                    <th>ចំណុះ (Capacity)</th>
                    <th>ចុះឈ្មោះ</th>
                    <th>វត្តមាន</th>
                    <th>អត្រាវត្តមាន %</th>
                    <th>ថ្លៃប្រព័ន្ធ</th>
                    <th>ស្ថានភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workshops)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យសិក្ខាសាលានៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($workshops as $w): ?>
                        <?php 
                            $wAttRate = ($w['reg_count'] > 0) ? round(($w['attend_count'] / $w['reg_count']) * 100) : 0;
                            $fillRate = ($w['capacity'] > 0) ? round(($w['reg_count'] / $w['capacity']) * 100) : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?php echo e($w['name']); ?></div>
                            </td>
                            <td><span class="text-muted small"><?php echo e($w['business_name']); ?></span></td>
                            <td><span class="small"><?php echo !empty($w['start_date']) ? date('d/m/Y', strtotime($w['start_date'])) : '-'; ?></span></td>
                            <td><?php echo number_format($w['capacity']); ?> នាក់</td>
                            <td>
                                <div><?php echo number_format($w['reg_count']); ?> នាក់</div>
                                <div class="progress mt-1" style="height: 4px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo min(100, $fillRate); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo number_format($w['attend_count']); ?> នាក់</div>
                            </td>
                            <td>
                                <span class="badge <?php echo $wAttRate >= 70 ? 'bg-success' : ($wAttRate >= 40 ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                    <?php echo $wAttRate; ?>%
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">$<?php echo number_format($w['platform_fee'], 2); ?></span>
                            </td>
                            <td><?php echo statusBadge($w['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
