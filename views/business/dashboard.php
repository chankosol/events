<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1 text-dark">ផ្ទាំងគ្រប់គ្រងទូទៅ</h1>
        <p class="text-muted small mb-0">ទិដ្ឋភាពទូទៅនៃសិក្ខាសាលា សិក្ខាកាម និងចំណូលរបស់ក្រុមហ៊ុន</p>
    </div>
    <?php if (Permission::has('workshop.create') || Auth::isBusinessOwner()): ?>
    <a href="<?php echo APP_URL; ?>/workshops/create" class="btn btn-primary px-3 fw-bold shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> បង្កើតសិក្ខាសាលាថ្មី
    </a>
    <?php endif; ?>
</div>

<!-- KPI Cards in Khmer -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-primary border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold">សិក្ខាសាលាសកម្ម</div>
                    <div class="display-6 fw-bold text-dark mt-1"><?php echo number_format($stats['upcoming_workshops'] ?? 0); ?></div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-calendar-event fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-success border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold">សិក្ខាកាមសរុប</div>
                    <div class="display-6 fw-bold text-success mt-1"><?php echo number_format($stats['total_participants'] ?? 0); ?></div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-people fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-info border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold">អ្នកចុះឈ្មោះបញ្ជាក់ផ្លូវការ</div>
                    <div class="display-6 fw-bold text-info mt-1"><?php echo number_format($stats['active_registrations'] ?? 0); ?></div>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-card-checklist fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 border-start border-warning border-4 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-bold">រង់ចាំការផ្ទៀងផ្ទាត់បង់ប្រាក់</div>
                    <div class="display-6 fw-bold text-warning mt-1"><?php echo number_format($stats['pending_payments'] ?? 0); ?></div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Workshops in Khmer -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="m-0 fw-bold text-dark"><i class="bi bi-collection-play me-2 text-primary"></i> សិក្ខាសាលាថ្មីៗ</h6>
                <?php if (Permission::has('workshop.view')): ?>
                <a href="<?php echo APP_URL; ?>/workshops" class="btn btn-sm btn-outline-primary fw-bold">មើលទាំងអស់</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">សិក្ខាសាលា</th>
                                <th>កាលបរិច្ឆេទ</th>
                                <th>ស្ថានភាព</th>
                                <th class="text-end pe-3">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentWorkshops)): ?>
                                <?php foreach ($recentWorkshops as $ws): ?>
                                <tr>
                                    <td class="ps-3">
                                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $ws['id']; ?>" class="fw-bold text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($ws['name'] ?? $ws['title'] ?? ''); ?>
                                        </a>
                                        <div class="small text-muted"><?php echo htmlspecialchars($ws['workshop_type'] ?? $ws['type'] ?? 'in-person'); ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><i class="bi bi-calendar me-1"></i><?php echo formatDate($ws['start_date']); ?></div>
                                    </td>
                                    <td>
                                        <?php echo statusBadge($ws['status']); ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if (Permission::has('checkin.use')): ?>
                                        <a href="<?php echo APP_URL; ?>/checkin/<?php echo $ws['id']; ?>" class="btn btn-sm btn-outline-success me-1">
                                            <i class="bi bi-qr-code-scan me-1"></i> ស្កេនវត្តមាន
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $ws['id']; ?>" class="btn btn-sm btn-light border">
                                            <i class="bi bi-eye me-1"></i> គ្រប់គ្រង
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">មិនទាន់មានសិក្ខាសាលានៅឡើយទេ។</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Queue in Khmer -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="m-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-warning"></i> បញ្ជីរង់ចាំការត្រួតពិនិត្យ</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($recentRegistrations)): ?>
                        <?php foreach ($recentRegistrations as $reg): ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($reg['participant_name']); ?></h6>
                                    <small class="text-muted d-block mb-1"><?php echo htmlspecialchars($reg['workshop_name'] ?? $reg['workshop_title'] ?? ''); ?></small>
                                    <span class="badge bg-warning text-dark">រង់ចាំត្រួតពិនិត្យ</span>
                                </div>
                                <div>
                                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $reg['workshop_id'] ?? 1; ?>/payments" class="btn btn-sm btn-outline-primary">
                                        ពិនិត្យ
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-check2-all fs-2 d-block mb-1 text-success"></i>
                            មិនមានការចុះឈ្មោះរង់ចាំការត្រួតពិនិត្យឡើយ។
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
