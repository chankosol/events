<?php
// views/business/reports/workshop.php
// Comprehensive Workshop Event Report
$w = $report['workshop'] ?? [];
$workshopId = (int)($w['id'] ?? 0);

// Registration & Capacity stats
$totalReg       = (int)($report['registrations']['total'] ?? 0);
$confirmedCount = (int)($report['registrations']['confirmed'] ?? 0);
$waitlistedCount= (int)($report['registrations']['waitlisted'] ?? 0);
$cancelledCount = (int)($report['registrations']['cancelled'] ?? 0);
$capacity       = (int)($w['capacity'] ?? 0);
$capPct         = $capacity > 0 ? min(100, round(($confirmedCount / $capacity) * 100, 1)) : 0;

// Attendance stats
$checkedInCount = (int)($report['attendance']['checked_in'] ?? 0);
$attendanceRate = $confirmedCount > 0 ? min(100, round(($checkedInCount / $confirmedCount) * 100, 1)) : ($totalReg > 0 ? round(($checkedInCount / $totalReg) * 100, 1) : 0);

// Financial stats
$expected       = (float)($report['payments']['expected'] ?? 0);
$collected      = (float)($report['payments']['collected'] ?? 0);
$pending        = (float)($report['payments']['pending'] ?? 0);
$outstanding    = (float)($report['payments']['outstanding'] ?? 0);
$paymentRate    = $expected > 0 ? min(100, round(($collected / $expected) * 100, 1)) : 100;

// Checkin methods
$methods        = $report['checkin_methods'] ?? ['face' => 0, 'qr' => 0, 'helper_station' => 0, 'manual' => 0, 'total' => 0];
$faceCount      = (int)($methods['face'] ?? 0);
$qrCount        = (int)($methods['qr'] ?? 0);
$helperCount    = (int)($methods['helper_station'] ?? 0);
$manualCount    = (int)($methods['manual'] ?? 0);
$methodTotal    = max(1, $checkedInCount);
$facePct        = round(($faceCount / $methodTotal) * 100);
$qrPct          = round(($qrCount / $methodTotal) * 100);
$helperPct      = round(($helperCount / $methodTotal) * 100);
$manualPct      = round(($manualCount / $methodTotal) * 100);

// Feedback stats
$avgRating      = (float)($report['feedback']['avg_rating'] ?? 0);
$feedbackCount  = (int)($report['feedback']['responses'] ?? 0);

// Certificates & Gifts
$certIssued     = (int)($report['certificates']['issued'] ?? 0);
$giftDistributed= (int)($report['gifts']['distributed'] ?? 0);

// Gender stats
$genderStats    = $report['gender_stats'] ?? ['male' => 0, 'female' => 0, 'other' => 0, 'unspecified' => 0, 'total' => 0];
$genderTotal    = max(1, (int)($genderStats['total'] ?? 0));
$maleCount      = (int)($genderStats['male'] ?? 0);
$femaleCount    = (int)($genderStats['female'] ?? 0);
$otherCount     = (int)($genderStats['other'] ?? 0);
$unspecCount    = (int)($genderStats['unspecified'] ?? 0);

// Delegations & Allowances
$delegations    = $report['delegations'] ?? [];
$hasDelegations = !empty($delegations);
$allowances     = $report['allowances'] ?? ['count' => 0, 'total_disbursed' => 0];

// Attendees
$attendees      = $report['attendees'] ?? [];

// Helper for status badge
$statusLabels = [
    'draft'               => ['label' => 'ព្រាង', 'class' => 'bg-secondary'],
    'published'           => ['label' => 'បានផ្សាយ', 'class' => 'bg-info text-dark'],
    'registration_open'   => ['label' => 'បើកចុះឈ្មោះ', 'class' => 'bg-success'],
    'registration_closed' => ['label' => 'បិទចុះឈ្មោះ', 'class' => 'bg-warning text-dark'],
    'in_progress'         => ['label' => 'កំពុងដំណើរការ', 'class' => 'bg-primary'],
    'completed'           => ['label' => 'បានបញ្ចប់', 'class' => 'bg-dark'],
    'cancelled'           => ['label' => 'បានលុបចោល', 'class' => 'bg-danger'],
];
$wStatus = $statusLabels[$w['status'] ?? 'draft'] ?? ['label' => $w['status'] ?? '-', 'class' => 'bg-secondary'];
?>

<div class="container-fluid py-4 px-md-4 report-container">
    
    <!-- Action Top Bar (Hidden in Print) -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= APP_URL ?>/reports" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> បញ្ជីរបាយការណ៍
            </a>
            <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="bi bi-speedometer2 me-1"></i> ផ្ទាំងគ្រប់គ្រងសិក្ខាសាលា
            </a>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($w['telegram_chat_id'])): ?>
            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-sm" id="btnSendTelegramReport" onclick="sendTelegramSummaryReport()">
                <i class="bi bi-telegram me-1 text-primary"></i> ផ្ញើរបាយការណ៍ទៅ Telegram
            </button>
            <?php endif; ?>
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" onclick="exportAttendeesCSV()">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> ទាញយក CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> បោះពុម្ព / ទាញយក PDF
            </button>
        </div>
    </div>

    <!-- Official Report Header Banner -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden report-header-card">
        <div class="card-body p-4 p-md-5">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge <?= $wStatus['class'] ?> px-3 py-1 rounded-pill small"><?= $wStatus['label'] ?></span>
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small">
                            <i class="bi bi-tag me-1 text-primary"></i><?= htmlspecialchars($w['category'] ?? 'ទូទៅ') ?>
                        </span>
                        <span class="badge bg-light text-dark border px-3 py-1 rounded-pill small">
                            <i class="bi bi-geo-alt me-1 text-danger"></i><?= ($w['workshop_type'] ?? '') === 'online' ? 'អនឡាញ (Online)' : (($w['workshop_type'] ?? '') === 'hybrid' ? 'កូនកាត់ (Hybrid)' : 'ផ្ទាល់ (In-Person)') ?>
                        </span>
                    </div>
                    <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars($w['name'] ?? 'របាយការណ៍សិក្ខាសាលា') ?></h1>
                    <p class="text-muted mb-3 small">
                        <?= !empty($w['short_description']) ? htmlspecialchars($w['short_description']) : 'របាយការណ៍បូកសរុបលទ្ធផលទូទៅ ប្រតិបត្តិការ វត្តមាន និងហិរញ្ញវត្ថុនៃកម្មវិធីទាំងស្រុង' ?>
                    </p>

                    <!-- Event Metadata Grid -->
                    <div class="row g-2 text-secondary small pt-2 border-top">
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-building me-1 text-primary"></i> <strong>អ្នករៀបចំ៖</strong> <?= htmlspecialchars($w['organizer'] ?: ($w['business_name'] ?: 'មិនបានបញ្ជាក់')) ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-calendar-event me-1 text-primary"></i> <strong>កាលបរិច្ឆេទ៖</strong> 
                            <?= date('d/m/Y', strtotime($w['start_date'] ?? 'now')) ?>
                            <?= (!empty($w['end_date']) && $w['end_date'] !== $w['start_date']) ? ' ដល់ ' . date('d/m/Y', strtotime($w['end_date'])) : '' ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-clock me-1 text-primary"></i> <strong>ម៉ោង៖</strong> 
                            <?= substr($w['start_time'] ?? '08:00', 0, 5) ?> - <?= substr($w['end_time'] ?? '17:00', 0, 5) ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i> <strong>ទីតាំង៖</strong> <?= htmlspecialchars($w['venue'] ?: ($w['address'] ?: 'មិនបានបញ្ជាក់')) ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-person-video3 me-1 text-success"></i> <strong>វាគ្មិន/គ្រូ៖</strong> <?= htmlspecialchars(($w['trainer_title'] ? $w['trainer_title'] . ' ' : '') . ($w['trainer_name'] ?: 'មិនបានបញ្ជាក់')) ?>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="bi bi-printer-fill me-1 text-secondary"></i> <strong>ចេញរបាយការណ៍៖</strong> <?= date('d/m/Y H:i') ?>
                        </div>
                    </div>
                </div>

                <!-- Capacity Ring / Progress Block -->
                <div class="col-lg-4 text-lg-end">
                    <div class="bg-light p-3 rounded-4 border d-inline-block text-start w-100" style="max-width: 340px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-bold">ចំណុះកៅអីសាល</span>
                            <span class="fw-bold text-primary"><?= $confirmedCount ?> / <?= $capacity ?: '∞' ?> នាក់</span>
                        </div>
                        <div class="progress mb-2" style="height: 10px; border-radius: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $capPct ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>ពេញ៖ <?= $capPct ?>%</span>
                            <span>នៅសល់៖ <?= max(0, $capacity - $confirmedCount) ?> កៅអី</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Core Executive KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #fff;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-white-50 small fw-bold">ចំណូលប្រមូលបានជាក់ស្តែង</span>
                        <div class="stat-icon-badge bg-white bg-opacity-20 text-white rounded-circle p-2">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1 text-white">$<?= number_format($collected, 2) ?></h2>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>រំពឹងទុក៖ $<?= number_format($expected, 2) ?></span>
                            <span>អត្រា៖ <?= $paymentRate ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Rate -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: #fff;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-white-50 small fw-bold">អត្រាវត្តមាន Check-in</span>
                        <div class="stat-icon-badge bg-white bg-opacity-20 text-white rounded-circle p-2">
                            <i class="bi bi-person-check-fill fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1 text-white"><?= $attendanceRate ?>%</h2>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>មកជាក់ស្តែង៖ <?= $checkedInCount ?> នាក់</span>
                            <span>បញ្ជាក់៖ <?= $confirmedCount ?> នាក់</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Rating -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #fff;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-white-50 small fw-bold">ពិន្ទុវាយតម្លៃមធ្យម</span>
                        <div class="stat-icon-badge bg-white bg-opacity-20 text-white rounded-circle p-2">
                            <i class="bi bi-star-fill fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1 text-white"><?= number_format($avgRating, 1) ?> <span class="fs-6 fw-normal">/ 5.0</span></h2>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>ឆ្លើយតប៖ <?= $feedbackCount ?> ចម្លើយ</span>
                            <span>
                                <?php for($s = 1; $s <= 5; $s++): ?>
                                    <i class="bi <?= $s <= round($avgRating) ? 'bi-star-fill' : 'bi-star' ?> text-white small"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificates & Souvenirs -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 stat-card" style="background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%); color: #fff;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-white-50 small fw-bold">វិញ្ញាបនបត្រ & កាដូបានចែក</span>
                        <div class="stat-icon-badge bg-white bg-opacity-20 text-white rounded-circle p-2">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="fw-bold mb-1 text-white"><?= $certIssued ?> <span class="fs-6 fw-normal">ច្បាប់</span></h2>
                        <div class="d-flex justify-content-between text-white-50 small">
                            <span>កាដូអនុស្សាវរីយ៍៖ <?= $giftDistributed ?> នាក់</span>
                            <span>ជោគជ័យ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Technology Breakdown (AI Face vs QR vs Helper) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-person-bounding-box text-primary me-2"></i>បច្ចេកវិទ្យាវត្តមាន Check-in (Check-in Method Analytics)
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">វត្តមានសរុប៖ <?= $checkedInCount ?> នាក់</span>
        </div>
        <div class="card-body pt-0">
            <!-- 4 Checkin Cards -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 border bg-light text-center h-100">
                        <div class="text-primary mb-1"><i class="bi bi-person-bounding-box fs-3"></i></div>
                        <div class="text-muted small fw-bold">ស្កេនផ្ទៃមុខ AI (Face)</div>
                        <div class="h4 fw-bold text-dark mb-0"><?= $faceCount ?> <small class="text-muted fs-6">(<?= $facePct ?>%)</small></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 border bg-light text-center h-100">
                        <div class="text-success mb-1"><i class="bi bi-qr-code-scan fs-3"></i></div>
                        <div class="text-muted small fw-bold">ស្កេនកូដ QR</div>
                        <div class="h4 fw-bold text-dark mb-0"><?= $qrCount ?> <small class="text-muted fs-6">(<?= $qrPct ?>%)</small></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 border bg-light text-center h-100">
                        <div class="text-info mb-1"><i class="bi bi-display fs-3"></i></div>
                        <div class="text-muted small fw-bold">តុជំនួយការ (Helper)</div>
                        <div class="h4 fw-bold text-dark mb-0"><?= $helperCount ?> <small class="text-muted fs-6">(<?= $helperPct ?>%)</small></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 border bg-light text-center h-100">
                        <div class="text-secondary mb-1"><i class="bi bi-pencil-square fs-3"></i></div>
                        <div class="text-muted small fw-bold">ស្កេនដោយដៃ (Manual)</div>
                        <div class="h4 fw-bold text-dark mb-0"><?= $manualCount ?> <small class="text-muted fs-6">(<?= $manualPct ?>%)</small></div>
                    </div>
                </div>
            </div>

            <!-- Proportional Bar -->
            <?php if ($checkedInCount > 0): ?>
            <div class="progress rounded-pill overflow-hidden" style="height: 14px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $facePct ?>%" title="Face: <?= $faceCount ?>"></div>
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $qrPct ?>%" title="QR: <?= $qrCount ?>"></div>
                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $helperPct ?>%" title="Helper: <?= $helperCount ?>"></div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: <?= $manualPct ?>%" title="Manual: <?= $manualCount ?>"></div>
            </div>
            <div class="d-flex flex-wrap gap-3 justify-content-between small text-muted mt-2">
                <span><i class="bi bi-circle-fill text-primary small me-1"></i> AI Face Recognition (<?= $facePct ?>%)</span>
                <span><i class="bi bi-circle-fill text-success small me-1"></i> QR Code (<?= $qrPct ?>%)</span>
                <span><i class="bi bi-circle-fill text-info small me-1"></i> Helper Desk (<?= $helperPct ?>%)</span>
                <span><i class="bi bi-circle-fill text-secondary small me-1"></i> Manual (<?= $manualPct ?>%)</span>
            </div>
            <?php else: ?>
            <div class="alert alert-light text-center small text-muted mb-0">មិនទាន់មានទិន្នន័យ Check-in នៅឡើយទេ។</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Row: Financial Breakdown & Registration Funnel -->
    <div class="row g-4 mb-4">
        <!-- Financial Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-success me-2"></i>សង្ខេបហិរញ្ញវត្ថុសិក្ខាសាលា</h6>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush border-0">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div>
                                <span class="fw-bold text-dark">ចំណូលរំពឹងទុក (Expected Revenue)</span>
                                <div class="text-muted small">ផ្អែកលើការចុះឈ្មោះទាំងអស់ដែលបានបញ្ជាក់</div>
                            </div>
                            <span class="fs-6 fw-bold text-dark">$<?= number_format($expected, 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div>
                                <span class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> ប្រមូលបានជាក់ស្តែង (Collected)</span>
                                <div class="text-muted small">ទូទាត់តាម KHQR, ធនាគារ ឬសាច់ប្រាក់រួច</div>
                            </div>
                            <span class="fs-6 fw-bold text-success">$<?= number_format($collected, 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div>
                                <span class="fw-bold text-warning"><i class="bi bi-hourglass-split me-1"></i> រង់ចាំការទូទាត់ (Pending)</span>
                                <div class="text-muted small">កំពុងរង់ចាំការផ្ទៀងផ្ទាត់ ឬការផ្ទេរប្រាក់</div>
                            </div>
                            <span class="fs-6 fw-bold text-warning">$<?= number_format($pending, 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                            <div>
                                <span class="fw-bold text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> នៅសល់មិនទាន់បង់ (Outstanding)</span>
                                <div class="text-muted small">សំបុត្រដែលមិនទាន់ទូទាត់</div>
                            </div>
                            <span class="fs-6 fw-bold text-danger">$<?= number_format($outstanding, 2) ?></span>
                        </li>
                    </ul>

                    <div class="mt-3 p-3 bg-light rounded-3">
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>អត្រាប្រមូលប្រាក់ជោគជ័យ</span>
                            <span class="text-success"><?= $paymentRate ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $paymentRate ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration & Attendance Funnel -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-filter-circle-fill text-primary me-2"></i>ដំណើរការចុះឈ្មោះ (Registration Funnel)</h6>
                </div>
                <div class="card-body pt-0">
                    <?php 
                    $confPct = $totalReg > 0 ? round(($confirmedCount / $totalReg) * 100) : 0;
                    $attPct  = $totalReg > 0 ? round(($checkedInCount / $totalReg) * 100) : 0;
                    $waitPct = $totalReg > 0 ? round(($waitlistedCount / $totalReg) * 100) : 0;
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold text-dark">១. ចុះឈ្មោះសរុប (Total Registered)</span>
                            <span class="fw-bold"><?= $totalReg ?> នាក់ (100%)</span>
                        </div>
                        <div class="progress" style="height: 24px; border-radius: 8px;">
                            <div class="progress-bar bg-primary text-start px-3" style="width: 100%">
                                ចុះឈ្មោះសរុប (<?= $totalReg ?>)
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold text-dark">២. បានបញ្ជាក់ផ្លូវការ (Confirmed / Approved)</span>
                            <span class="fw-bold text-success"><?= $confirmedCount ?> នាក់ (<?= $confPct ?>%)</span>
                        </div>
                        <div class="progress" style="height: 24px; border-radius: 8px;">
                            <div class="progress-bar bg-success text-start px-3" style="width: <?= max(5, $confPct) ?>%">
                                បានបញ្ជាក់ (<?= $confirmedCount ?>)
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold text-dark">៣. វត្តមានជាក់ស្តែង (Attended / Checked-in)</span>
                            <span class="fw-bold text-info"><?= $checkedInCount ?> នាក់ (<?= $attPct ?>%)</span>
                        </div>
                        <div class="progress" style="height: 24px; border-radius: 8px;">
                            <div class="progress-bar bg-info text-dark text-start px-3" style="width: <?= max(5, $attPct) ?>%">
                                វត្តមានជាក់ស្តែង (<?= $checkedInCount ?>)
                            </div>
                        </div>
                    </div>

                    <?php if ($waitlistedCount > 0): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-bold text-dark">៤. បញ្ជីរង់ចាំ (Waitlisted)</span>
                            <span class="fw-bold text-warning"><?= $waitlistedCount ?> នាក់ (<?= $waitPct ?>%)</span>
                        </div>
                        <div class="progress" style="height: 20px; border-radius: 8px;">
                            <div class="progress-bar bg-warning text-dark text-start px-3" style="width: <?= max(5, $waitPct) ?>%">
                                បញ្ជីរង់ចាំ (<?= $waitlistedCount ?>)
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between text-muted small pt-2 border-top mt-3">
                        <span><i class="bi bi-info-circle me-1"></i> អត្រាវត្តមានធៀបនឹងបញ្ជាក់៖ <strong><?= $attendanceRate ?>%</strong></span>
                        <span>លុបចោល៖ <strong><?= $cancelledCount ?></strong> នាក់</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row: Tickets Breakdown & Workshop Sessions -->
    <div class="row g-4 mb-4">
        <!-- Tickets Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-ticket-perforated-fill text-warning me-2"></i>ស្ថិតិប្រភេទសំបុត្រ (Ticket Breakdown)</h6>
                    <span class="badge bg-light text-muted border"><?= count($report['tickets'] ?? []) ?> ប្រភេទ</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">ប្រភេទសំបុត្រ</th>
                                    <th>តម្លៃ</th>
                                    <th class="text-center">លក់បាន</th>
                                    <th class="text-center">ចំណូល</th>
                                    <th class="text-center pe-4">វត្តមាន</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report['tickets'])): ?>
                                    <?php foreach ($report['tickets'] as $t): ?>
                                    <?php 
                                        $tSold = (int)($t['sold_count'] ?? 0);
                                        $tAtt  = (int)($t['attended_count'] ?? 0);
                                        $tRate = $tSold > 0 ? round(($tAtt / $tSold) * 100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($t['name'] ?? '-') ?></div>
                                            <div class="text-muted small"><?= ucfirst($t['ticket_type'] ?? 'standard') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                $<?= number_format((float)($t['price'] ?? 0), 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold"><?= $tSold ?></td>
                                        <td class="text-center text-success fw-bold">$<?= number_format((float)($t['total_revenue'] ?? 0), 2) ?></td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <?= $tAtt ?> នាក់ (<?= $tRate ?>%)
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">មិនមានព័ត៌មានសំបុត្រ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-clock-history text-info me-2"></i>កាលវិភាគវគ្គសិក្ខាសាលា (Workshop Sessions)</h6>
                    <span class="badge bg-light text-muted border"><?= count($report['sessions'] ?? []) ?> វគ្គ</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-4">ឈ្មោះវគ្គ</th>
                                    <th>កាលបរិច្ឆេទ & ម៉ោង</th>
                                    <th>ទីតាំង / វាគ្មិន</th>
                                    <th class="text-center pe-4">វត្តមាន</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($report['sessions'])): ?>
                                    <?php foreach ($report['sessions'] as $s): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['name'] ?? '-') ?></div>
                                            <span class="badge bg-light text-muted border small"><?= $s['status'] ?? 'scheduled' ?></span>
                                        </td>
                                        <td class="small">
                                            <div><i class="bi bi-calendar3 me-1 text-muted"></i><?= date('d/m/Y', strtotime($s['session_date'] ?? 'now')) ?></div>
                                            <div class="text-muted"><i class="bi bi-clock me-1 text-muted"></i><?= substr($s['start_time'] ?? '', 0, 5) ?> - <?= substr($s['end_time'] ?? '', 0, 5) ?></div>
                                        </td>
                                        <td class="small text-muted">
                                            <div><?= htmlspecialchars($s['location'] ?: 'បន្ទប់មេ') ?></div>
                                            <small><?= htmlspecialchars($s['facilitator'] ?: '-') ?></small>
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <?= (int)($s['present_count'] ?? 0) ?> នាក់
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">មិនមានវគ្គរងបំបែកក្នុងសិក្ខាសាលានេះទេ</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row: Demographics & Geography -->
    <div class="row g-4 mb-4">
        <!-- Gender Breakdown -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-gender-ambiguous text-primary me-2"></i>សមាមាត្រយេនឌ័រ / ភេទ</h6>
                </div>
                <div class="card-body pt-0">
                    <?php 
                    $mPct = round(($maleCount / $genderTotal) * 100);
                    $fPct = round(($femaleCount / $genderTotal) * 100);
                    $oPct = round(($otherCount / $genderTotal) * 100);
                    $uPct = round(($unspecCount / $genderTotal) * 100);
                    ?>
                    <div class="progress rounded-pill overflow-hidden mb-3" style="height: 14px;">
                        <div class="progress-bar" style="width: <?= $mPct ?>%; background-color: #3b82f6;" title="ប្រុស: <?= $maleCount ?>"></div>
                        <div class="progress-bar" style="width: <?= $fPct ?>%; background-color: #ec4899;" title="ស្រី: <?= $femaleCount ?>"></div>
                        <div class="progress-bar" style="width: <?= $oPct ?>%; background-color: #8b5cf6;" title="ផ្សេងៗ: <?= $otherCount ?>"></div>
                        <div class="progress-bar" style="width: <?= $uPct ?>%; background-color: #94a3b8;" title="មិនបញ្ជាក់: <?= $unspecCount ?>"></div>
                    </div>

                    <ul class="list-group list-group-flush border-0 small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span><i class="bi bi-circle-fill me-2" style="color: #3b82f6;"></i>ប្រុស (Male)</span>
                            <span class="fw-bold"><?= $maleCount ?> នាក់ (<?= $mPct ?>%)</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span><i class="bi bi-circle-fill me-2" style="color: #ec4899;"></i>ស្រី (Female)</span>
                            <span class="fw-bold"><?= $femaleCount ?> នាក់ (<?= $fPct ?>%)</span>
                        </li>
                        <?php if ($otherCount > 0): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span><i class="bi bi-circle-fill me-2" style="color: #8b5cf6;"></i>ផ្សេងៗ (Other)</span>
                            <span class="fw-bold"><?= $otherCount ?> នាក់ (<?= $oPct ?>%)</span>
                        </li>
                        <?php endif; ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span><i class="bi bi-circle-fill me-2" style="color: #94a3b8;"></i>មិនបានបញ្ជាក់</span>
                            <span class="fw-bold"><?= $unspecCount ?> នាក់ (<?= $uPct ?>%)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Top Provinces -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-2"></i>ខេត្ត/រាជធានីចូលរួមច្រើនជាងគេ</h6>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($report['provinces'])): ?>
                        <?php foreach (array_slice($report['provinces'], 0, 5) as $prov): ?>
                        <?php 
                            $provCount = (int)($prov['count'] ?? 0);
                            $provPct   = $totalReg > 0 ? round(($provCount / $totalReg) * 100) : 0;
                        ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($prov['province'] ?? 'ទូទៅ') ?></span>
                                <span class="text-muted"><?= $provCount ?> នាក់ (<?= $provPct ?>%)</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-danger" style="width: <?= $provPct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">មិនទាន់មានទិន្នន័យខេត្ត/រាជធានី</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Organizations -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-building text-info me-2"></i>ស្ថាប័ន / ក្រុមហ៊ុនចូលរួមច្រើនជាងគេ</h6>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($report['organizations'])): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (array_slice($report['organizations'], 0, 6) as $org): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light small">
                                <span class="text-truncate me-2 fw-medium text-dark" style="max-width: 220px;">
                                    <i class="bi bi-buildings me-1 text-muted"></i><?= htmlspecialchars($org['company_name'] ?? 'ទូទៅ') ?>
                                </span>
                                <span class="badge bg-primary rounded-pill"><?= (int)($org['count'] ?? 0) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">មិនទាន់មានទិន្នន័យស្ថាប័ន</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delegations & Allowances Section (If Any) -->
    <?php if ($hasDelegations): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="bi bi-people-fill text-primary me-2"></i>ប្រតិភូ និងការបើកប្រាក់ឧបត្ថម្ភ (Delegations & Allowances)
            </h6>
            <div class="d-flex gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                    <?= count($delegations) ?> គណៈប្រតិភូ
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    ប្រាក់ឧបត្ថម្ភ៖ $<?= number_format((float)($allowances['total_disbursed'] ?? 0), 2) ?> (<?= (int)($allowances['count'] ?? 0) ?> នាក់)
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4">ឈ្មោះស្ថាប័ន / ខេត្ត</th>
                            <th>ប្រធានប្រតិភូ & ទំនាក់ទំនង</th>
                            <th class="text-center">កូតាសរុប</th>
                            <th class="text-center">បានចុះឈ្មោះ</th>
                            <th class="text-center">វត្តមានជាក់ស្តែង</th>
                            <th class="text-center pe-4">ប្រាក់ឧបត្ថម្ភ/នាក់</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($delegations, 0, 8) as $d): ?>
                        <?php 
                            $dQuota = (int)($d['quota_seats'] ?? 0);
                            $dReg   = (int)($d['registered_count'] ?? 0);
                            $dAtt   = (int)($d['checked_in_count'] ?? 0);
                            $dRate  = $dReg > 0 ? round(($dAtt / $dReg) * 100) : 0;
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($d['organization'] ?? '-') ?></div>
                                <div class="text-muted small"><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($d['province'] ?? '-') ?></div>
                            </td>
                            <td class="small">
                                <div class="fw-medium text-dark"><?= htmlspecialchars($d['head_name'] ?: '-') ?></div>
                                <div class="text-muted"><?= htmlspecialchars($d['head_phone'] ?: '-') ?></div>
                            </td>
                            <td class="text-center fw-bold"><?= $dQuota ?></td>
                            <td class="text-center"><?= $dReg ?></td>
                            <td class="text-center">
                                <span class="badge <?= $dAtt > 0 ? 'bg-success-subtle text-success' : 'bg-light text-muted' ?> border">
                                    <?= $dAtt ?> (<?= $dRate ?>%)
                                </span>
                            </td>
                            <td class="text-center pe-4 text-success fw-bold">
                                $<?= number_format((float)($d['allowance_rate'] ?? 0), 2) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (count($delegations) > 8): ?>
                <div class="p-2 text-center bg-light small text-muted">
                    ... និងមានគណៈប្រតិភូចំនួន <?= count($delegations) - 8 ?> ផ្សេងទៀត។
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Feedback & Live Q&A Section -->
    <div class="row g-4 mb-4">
        <!-- Feedback Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-chat-heart-fill text-danger me-2"></i>មតិកែលម្អ & ការវាយតម្លៃ (Evaluation)</h6>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><?= number_format($avgRating, 1) ?> / 5.0 ★</span>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($report['feedback_recent'])): ?>
                        <!-- Recent comments -->
                        <div class="d-flex flex-column gap-2 mb-3">
                            <?php foreach ($report['feedback_recent'] as $fb): ?>
                            <?php 
                                $ans = is_string($fb['answers']) ? json_decode($fb['answers'], true) : $fb['answers'];
                                $comment = $ans['comments'] ?? ($ans['comment'] ?? ($ans['feedback'] ?? ''));
                                $rStar = (int)($fb['overall_rating'] ?? 5);
                            ?>
                            <div class="p-3 rounded-3 bg-light border small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark">
                                        <i class="bi bi-person-circle me-1 text-primary"></i>
                                        <?= !empty($fb['is_anonymous']) ? 'អនាមិក (Anonymous)' : htmlspecialchars($fb['participant_name'] ?? 'សិក្ខាកាម') ?>
                                    </span>
                                    <span class="text-warning">
                                        <?php for($k = 1; $k <= 5; $k++): ?>
                                            <i class="bi <?= $k <= $rStar ? 'bi-star-fill' : 'bi-star' ?> small"></i>
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <p class="text-muted mb-0"><?= !empty($comment) ? htmlspecialchars($comment) : 'បានផ្តល់ការវាយតម្លៃ ' . $rStar . ' ផ្កាយ' ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-chat-square-dots fs-3 d-block text-secondary mb-2"></i>
                            មិនទាន់មានមតិកែលម្អដែលបានបញ្ជូននៅឡើយទេ
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Live Q&A Section -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-question-circle-fill text-info me-2"></i>សំណួរពេញនិយមក្នុងកម្មវិធី (Live Q&A)</h6>
                    <span class="badge bg-light text-muted border"><?= count($report['top_questions'] ?? []) ?> សំណួរ</span>
                </div>
                <div class="card-body pt-0">
                    <?php if (!empty($report['top_questions'])): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($report['top_questions'] as $q): ?>
                            <div class="p-3 rounded-3 bg-light border small">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="fw-bold text-dark">
                                        <i class="bi bi-chat-left-text me-1 text-info"></i>
                                        <?= htmlspecialchars($q['question_text'] ?? '') ?>
                                    </span>
                                    <span class="badge bg-info-subtle text-info rounded-pill ms-2">
                                        <i class="bi bi-hand-thumbs-up me-1"></i><?= (int)($q['upvotes'] ?? 0) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mt-2">
                                    <span>ដោយ៖ <?= htmlspecialchars($q['author_name'] ?: ($q['author_real_name'] ?: 'អនាមិក')) ?></span>
                                    <span>
                                        <?php if (!empty($q['answer_text'])): ?>
                                            <span class="text-success"><i class="bi bi-check2-all me-1"></i>បានឆ្លើយ</span>
                                        <?php else: ?>
                                            <span class="text-warning"><i class="bi bi-clock me-1"></i>រង់ចាំការឆ្លើយ</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if (!empty($q['answer_text'])): ?>
                                <div class="mt-2 p-2 rounded bg-white border text-secondary small">
                                    <strong>ចម្លើយ៖</strong> <?= htmlspecialchars($q['answer_text']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-patch-question fs-3 d-block text-secondary mb-2"></i>
                            មិនទាន់មានសំណួរចោទសួរក្នុងកម្មវិធីនៅឡើយទេ
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Attendee Registry Table (with Live Filter & Search) -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 attendee-table-card">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h6 class="m-0 fw-bold text-dark">
                    <i class="bi bi-people-fill text-primary me-2"></i>បញ្ជីរាយនាមសិក្ខាកាមលម្អិត (Attendee Registry)
                </h6>
                <small class="text-muted" id="attendeeFilterCount">បង្ហាញ <?= count($attendees) ?> នាក់ នៃ <?= count($attendees) ?> នាក់សរុប</small>
            </div>
            
            <!-- Table Search & Filter Controls (Hidden in Print) -->
            <div class="d-flex flex-wrap gap-2 align-items-center no-print">
                <div class="input-group input-group-sm" style="width: 260px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="attendeeSearchInput" class="form-control border-start-0 ps-0" placeholder="ស្វែងរក ឈ្មោះ, លេខទូរស័ព្ទ, កូដ..." onkeyup="filterAttendeeTable()">
                </div>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" onclick="setAttendeeFilter('all', this)">ទាំងអស់</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setAttendeeFilter('attended', this)">បាន Check-in</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setAttendeeFilter('absent', this)">មិនទាន់ Check-in</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="setAttendeeFilter('face', this)">AI Face</button>
                </div>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attendeeTable">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>កូដចុះឈ្មោះ</th>
                            <th>ឈ្មោះសិក្ខាកាម</th>
                            <th>ទំនាក់ទំនង</th>
                            <th>ស្ថាប័ន & ខេត្ត</th>
                            <th>សំបុត្រ</th>
                            <th class="text-center">ការទូទាត់</th>
                            <th class="text-center">វត្តមាន</th>
                            <th class="text-center pe-4">វិធី & ម៉ោង Check-in</th>
                        </tr>
                    </thead>
                    <tbody id="attendeeTableBody">
                        <?php if (!empty($attendees)): ?>
                            <?php $idx = 1; foreach ($attendees as $att): ?>
                            <?php 
                                $isAttended = !empty($att['checked_in_at']);
                                $mType      = $att['check_in_method'] ?? 'qr';
                                $pStatus    = $att['payment_status'] ?? 'pending';
                                $isVip      = !empty($att['is_vip']);
                                $methodKh   = 'QR Code';
                                if ($mType === 'face') $methodKh = 'AI Face';
                                elseif ($mType === 'helper_station') $methodKh = 'Helper';
                                elseif ($mType === 'manual') $methodKh = 'Manual';
                            ?>
                            <tr class="attendee-row" 
                                data-status="<?= $isAttended ? 'attended' : 'absent' ?>"
                                data-method="<?= htmlspecialchars($mType) ?>"
                                data-vip="<?= $isVip ? 'vip' : 'normal' ?>">
                                <td class="ps-4 text-muted small"><?= $idx++ ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        <?= htmlspecialchars($att['registration_code'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?= htmlspecialchars($att['name'] ?? '-') ?>
                                        <?php if ($isVip): ?>
                                            <span class="badge bg-warning text-dark small ms-1"><i class="bi bi-star-fill me-1"></i>VIP</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small"><?= !empty($att['gender']) ? (in_array(strtolower($att['gender']), ['m', 'male']) ? 'ប្រុស' : 'ស្រី') : '' ?></div>
                                </td>
                                <td class="small">
                                    <div><i class="bi bi-telephone me-1 text-muted"></i><?= htmlspecialchars($att['phone'] ?: '-') ?></div>
                                    <?php if (!empty($att['email'])): ?>
                                    <div class="text-muted"><i class="bi bi-envelope me-1 text-muted"></i><?= htmlspecialchars($att['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small">
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($att['organization'] ?: '-') ?></div>
                                    <div class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars($att['province'] ?: '-') ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        <?= htmlspecialchars($att['ticket_name'] ?: 'ទូទៅ') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if (in_array($pStatus, ['paid', 'paid_cash'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle small">បានបង់</span>
                                    <?php elseif ($pStatus === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">រង់ចាំ</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">មិនគិតថ្លៃ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($att['slots'])): ?>
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            <?php foreach ($att['slots'] as $sl): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle small px-2 py-0.5" style="font-size: 0.69rem;" title="<?= date('H:i d/m/Y', strtotime($sl['checked_in_at'])) ?>">
                                                    <i class="bi bi-check2 me-1"></i><?= htmlspecialchars($sl['slot_name'] ?: 'Slot ' . $sl['slot_number']) ?>
                                                    <span class="text-muted ms-1" style="font-size: 0.64rem;"><?= date('H:i', strtotime($sl['checked_in_at'])) ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($isAttended): ?>
                                        <span class="badge bg-success text-white small"><i class="bi bi-check-circle-fill me-1"></i>មានវត្តមាន</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border small">អវត្តមាន</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4 small">
                                    <?php if ($isAttended): ?>
                                        <span class="badge <?= $mType === 'face' ? 'bg-primary' : ($mType === 'qr' ? 'bg-success' : 'bg-info') ?> small">
                                            <?= $methodKh ?>
                                        </span>
                                        <div class="text-muted small mt-1"><?= date('H:i, d/m', strtotime($att['checked_in_at'])) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small">
                                    <i class="bi bi-person-x fs-2 d-block text-secondary mb-2"></i>
                                    មិនទាន់មានអ្នកចុះឈ្មោះក្នុងសិក្ខាសាលានេះនៅឡើយទេ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Official Signature Block (For Printing / Reports) -->
    <div class="card border-0 shadow-sm rounded-4 mt-5 p-4 print-signature-block">
        <div class="row text-center g-4">
            <div class="col-4">
                <div class="fw-bold text-dark mb-1">រៀបចំដោយ</div>
                <div class="text-muted small mb-5">មន្ត្រីសម្របសម្រួលរបាយការណ៍</div>
                <div class="border-top pt-2 text-muted small">ហត្ថលេខា និងឈ្មោះ</div>
            </div>
            <div class="col-4">
                <div class="fw-bold text-dark mb-1">ត្រួតពិនិត្យដោយ</div>
                <div class="text-muted small mb-5">ប្រធានផ្នែករៀបចំកម្មវិធី</div>
                <div class="border-top pt-2 text-muted small">ហត្ថលេខា និងឈ្មោះ</div>
            </div>
            <div class="col-4">
                <div class="fw-bold text-dark mb-1">បានឃើញ និងអនុម័តដោយ</div>
                <div class="text-muted small mb-5">ថ្នាក់ដឹកនាំស្ថាប័ន / Host Organizer</div>
                <div class="border-top pt-2 text-muted small">ហត្ថលេខា និងត្រា</div>
            </div>
        </div>
    </div>

</div>

<!-- Styles: Print and Enhancements -->
<style>
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.stat-icon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
}
.report-header-card {
    border-left: 5px solid #2563eb !important;
}

@media print {
    /* Hide layout chrome */
    .sidebar, .topbar, .btn, .no-print, nav, header, footer, #sidebar, .breadcrumb {
        display: none !important;
    }
    .layout-with-sidebar {
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .page-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    body, html {
        background-color: #fff !important;
        color: #000 !important;
        font-size: 11pt !important;
    }
    .report-container {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
    .card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
    .stat-card {
        color: #000 !important;
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
    }
    .stat-card h2, .stat-card span, .stat-card div {
        color: #000 !important;
    }
    .progress {
        border: 1px solid #cbd5e1 !important;
    }
    .print-signature-block {
        display: block !important;
        margin-top: 3rem !important;
        page-break-inside: avoid;
    }
    table {
        page-break-inside: auto;
    }
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
}
</style>

<!-- JavaScript for Filter, Export, and Telegram Reporting -->
<script>
let currentFilter = 'all';

function filterAttendeeTable() {
    const searchVal = (document.getElementById('attendeeSearchInput')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('#attendeeTableBody .attendee-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const matchesSearch = text.includes(searchVal);
        
        const status = row.getAttribute('data-status');
        const method = row.getAttribute('data-method');
        const isVip  = row.getAttribute('data-vip') === 'vip';

        let matchesFilter = true;
        if (currentFilter === 'attended') matchesFilter = (status === 'attended');
        else if (currentFilter === 'absent') matchesFilter = (status === 'absent');
        else if (currentFilter === 'face') matchesFilter = (method === 'face');
        else if (currentFilter === 'vip') matchesFilter = isVip;

        if (matchesSearch && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countEl = document.getElementById('attendeeFilterCount');
    if (countEl) {
        countEl.textContent = `បង្ហាញ ${visibleCount} នាក់ នៃ ${rows.length} នាក់សរុប`;
    }
}

function setAttendeeFilter(filterName, btn) {
    currentFilter = filterName;
    if (btn && btn.parentElement) {
        btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    filterAttendeeTable();
}

function exportAttendeesCSV() {
    const rows = document.querySelectorAll('#attendeeTable tr');
    let csv = [];
    rows.forEach(row => {
        // Skip hidden rows
        if (row.style.display === 'none') return;
        let rowData = [];
        row.querySelectorAll('th, td').forEach(cell => {
            let text = cell.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/"/g, '""').trim();
            rowData.push('"' + text + '"');
        });
        csv.push(rowData.join(','));
    });

    const csvContent = "\uFEFF" + csv.join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `Workshop_Report_Attendees_<?= $workshopId ?>_<?= date('Ymd_His') ?>.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function sendTelegramSummaryReport() {
    const btn = document.getElementById('btnSendTelegramReport');
    if (!btn) return;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ផ្ញើរបាយការណ៍ទៅ Telegram?',
            text: 'តើអ្នកពិតជាចង់បញ្ជូនរបាយការណ៍បច្ចុប្បន្នភាពនេះ ទៅកាន់ក្រុម Telegram ដែលបានភ្ជាប់មែនទេ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'បាទ/ចាស ផ្ញើឥឡូវនេះ',
            cancelButtonText: 'បោះបង់',
            confirmButtonColor: '#2563eb'
        }).then((result) => {
            if (result.isConfirmed) {
                executeTelegramReportSend(btn);
            }
        });
    } else {
        if (confirm('តើអ្នកពិតជាចង់ផ្ញើរបាយការណ៍បច្ចុប្បន្នភាពនេះ ទៅកាន់ក្រុម Telegram មែនទេ?')) {
            executeTelegramReportSend(btn);
        }
    }
}

function executeTelegramReportSend(btn) {
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងផ្ញើ...';

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    fetch('<?= APP_URL ?>/workshops/<?= $workshopId ?>/telegram-report', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'csrf_token=' + encodeURIComponent(csrfToken)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'ជោគជ័យ!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert(data.message);
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'បរាជ័យ',
                    text: data.message || 'មិនអាចផ្ញើសារបានទេ។'
                });
            } else {
                alert(data.message || 'មិនអាចផ្ញើសារបានទេ។');
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'កំហុសប្រព័ន្ធ',
                text: 'មានបញ្ហាតភ្ជាប់៖ ' + err.message
            });
        } else {
            alert('មានបញ្ហាតភ្ជាប់៖ ' + err.message);
        }
    });
}
</script>
