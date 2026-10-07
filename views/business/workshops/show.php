<?php
// views/business/workshops/show.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 50, '…'), 'url' => null, 'icon' => null],
];
?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="h3 fw-bold mb-0"><?php echo e($workshop['name']); ?></h2>
            <?php echo statusBadge($workshop['status']); ?>
        </div>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar3 me-1"></i> <?php echo formatDate($workshop['start_date']); ?> 
            <?php if (!empty($workshop['start_time'])): ?> វេលាម៉ោង <?php echo e($workshop['start_time']); ?><?php endif; ?>
            &bull; <i class="bi bi-geo-alt me-1"></i> <?php echo e($workshop['venue'] ?: 'អនឡាញ / មិនទាន់កំណត់'); ?>
            &bull; <i class="bi bi-person me-1"></i> <?php echo e(!empty($workshop['trainer_title']) ? $workshop['trainer_title'] : 'គ្រូបណ្តុះបណ្តាល'); ?>: <?php echo e($workshop['trainer_name'] ?: 'មិនទាន់កំណត់'); ?>
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/edit" class="btn btn-outline-secondary d-inline-flex align-items-center">
            <i class="bi bi-pencil"></i> <span>កែសម្រួល</span>
        </a>
        <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center" onclick="duplicateWorkshop(<?php echo $workshop['id']; ?>)">
            <i class="bi bi-copy"></i> <span>ចម្លងទម្រង់</span>
        </button>
        <?php if ($billing && $billing['payment_status'] === 'paid'): ?>
            <a href="<?php echo APP_URL; ?>/live/<?php echo $workshop['id']; ?>" class="btn btn-danger d-inline-flex align-items-center">
                <i class="bi bi-broadcast"></i> <span>ផ្សាយផ្ទាល់</span>
            </a>
            <?php if ($workshop['status'] === 'active'): ?>
                <form method="POST" action="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/publish" class="d-inline m-0 p-0">
                    <?php echo csrfField(); ?>
                    <button type="submit" class="btn btn-success d-inline-flex align-items-center">
                        <i class="bi bi-megaphone"></i> <span>បើកចុះឈ្មោះ</span>
                    </button>
                </form>
            <?php elseif ($workshop['status'] === 'registration_open'): ?>
                <a href="<?php echo APP_URL; ?>/event/<?php echo $workshop['slug']; ?>" target="_blank" class="btn btn-primary d-inline-flex align-items-center">
                    <i class="bi bi-box-arrow-up-right"></i> <span>មើលទំព័រសាធារណៈ</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="btn btn-danger d-inline-flex align-items-center" title="បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ">
                <i class="bi bi-broadcast"></i> <span>ផ្សាយផ្ទាល់</span>
            </a>
            <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="btn btn-warning fw-bold d-inline-flex align-items-center">
                <i class="bi bi-credit-card"></i> <span>បង់ថ្លៃប្រព័ន្ធ</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Activation / Billing Status Banner -->
<?php if ($billing): ?>
    <?php if ($billing['payment_status'] !== 'paid'): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center shadow-sm border-warning mb-4">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">តម្រូវឱ្យបង់ថ្លៃដំណើរការប្រព័ន្ធ</h5>
                    <p class="mb-0">
                        ថ្លៃសេវាប្រព័ន្ធ: <strong>$<?php echo number_format($billing['platform_fee'], 2); ?> <?php echo e($billing['currency']); ?></strong> (ចំណុះ: <?php echo number_format($workshop['capacity']); ?> នាក់)។ 
                        ស្ថានភាព: <strong><?php echo ucfirst($billing['payment_status']); ?></strong>.
                    </p>
                </div>
            </div>
            <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="btn btn-warning fw-bold px-3">
                <i class="bi bi-credit-card me-1"></i> បង់ថ្លៃប្រព័ន្ធឥឡូវនេះ
            </a>
        </div>
    <?php else: ?>
        <div class="alert alert-success d-flex justify-content-between align-items-center shadow-sm border-success py-2 mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span>បានបង់ថ្លៃប្រព័ន្ធរួចរាល់ ($<?php echo number_format($billing['platform_fee'], 2); ?>)។ សិក្ខាសាលាដំណើរការសម្រាប់ចំណុះ <?php echo number_format($workshop['capacity']); ?> នាក់។</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#upgradeCapacityModal">
                <i class="bi bi-arrow-up-circle me-1"></i> ដំឡើងចំណុះបន្ថែម
            </button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Quick KPI Stats Cards -->
<div class="row g-3 mb-4 align-items-stretch">
    <!-- Card 1: Capacity -->
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card w-100 shadow-sm border-0 border-start border-primary border-4 rounded-3">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-bold text-truncate">ចំណុះដែលបានបំពេញ</div>
                    <div class="d-flex align-items-baseline gap-1 mt-1">
                        <span class="fs-2 fw-bold text-dark lh-1"><?php echo (int)$stats['confirmed']; ?></span>
                        <span class="text-muted fs-6">/ <?php echo number_format($workshop['capacity']); ?></span>
                    </div>
                </div>
                <div class="pt-2 border-top mt-2">
                    <?php $pct = min(100, round(($stats['confirmed'] / max(1, $workshop['capacity'])) * 100)); ?>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                        <span><?php echo $pct; ?>% ពេញ</span>
                        <span>អតិបរមា: <?php echo number_format($workshop['capacity']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Confirmed & Paid -->
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card w-100 shadow-sm border-0 border-start border-success border-4 rounded-3">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-bold text-truncate">បានបញ្ជាក់ & បង់ប្រាក់</div>
                    <div class="d-flex align-items-baseline gap-1 mt-1">
                        <span class="fs-2 fw-bold text-success lh-1"><?php echo (int)$stats['paid']; ?></span>
                        <span class="text-muted small">នាក់</span>
                    </div>
                </div>
                <div class="pt-2 border-top mt-2">
                    <div class="d-flex align-items-center justify-content-between text-muted" style="min-height: 25px; font-size: 0.75rem;">
                        <span><i class="bi bi-people me-1"></i> ការចុះឈ្មោះសរុប</span>
                        <span class="fw-bold text-dark"><?php echo (int)$stats['total_registrations']; ?> នាក់</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Pending Payment -->
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card w-100 shadow-sm border-0 border-start border-warning border-4 rounded-3">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-bold text-truncate">រង់ចាំការទូទាត់ / ពិនិត្យ</div>
                    <div class="d-flex align-items-baseline gap-1 mt-1">
                        <span class="fs-2 fw-bold text-warning lh-1"><?php echo (int)$stats['pending_payment']; ?></span>
                        <span class="text-muted small">នាក់</span>
                    </div>
                </div>
                <div class="pt-2 border-top mt-2">
                    <div class="d-flex align-items-center justify-content-between" style="min-height: 25px; font-size: 0.75rem;">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/payments" class="text-warning text-decoration-none fw-semibold">
                            <i class="bi bi-receipt me-1"></i> ពិនិត្យបង្កាន់ដៃ &rarr;
                        </a>
                        <span class="text-muted"><?php echo (int)$stats['pending_payment']; ?> រង់ចាំ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Waitlist -->
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="card w-100 shadow-sm border-0 border-start border-info border-4 rounded-3">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-bold text-truncate">បញ្ជីរង់ចាំ</div>
                    <div class="d-flex align-items-baseline gap-1 mt-1">
                        <span class="fs-2 fw-bold text-info lh-1"><?php echo (int)($stats['waitlist'] ?? 0); ?></span>
                        <span class="text-muted small">នាក់</span>
                    </div>
                </div>
                <div class="pt-2 border-top mt-2">
                    <div class="d-flex align-items-center justify-content-between text-muted" style="min-height: 25px; font-size: 0.75rem;">
                        <span><i class="bi bi-tag me-1"></i> ទម្រង់គិតថ្លៃ</span>
                        <span class="badge bg-light text-dark border"><?php echo ucfirst($workshop['payment_mode']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Workshop Navigation Modules -->
<div class="row g-4">
    <!-- Left Column: Module Shortcuts & Sessions -->
    <div class="col-lg-8">
        <!-- Quick Operations Grid -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-grid-fill me-2 text-primary"></i> ម៉ូឌុលប្រតិបត្តិការសិក្ខាសាលា</h5>
            </div>
            <div class="card-body">
                <?php $isUnpaid = (!$billing || $billing['payment_status'] !== 'paid'); ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/registrations" class="card text-decoration-none border h-100 p-3 hover-shadow">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary p-3 rounded-circle"><i class="bi bi-people-fill fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">ការចុះឈ្មោះ</h6>
                                    <div class="text-muted small">គ្រប់គ្រង & នាំចូល</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/payments" class="card text-decoration-none border h-100 p-3 hover-shadow">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success-subtle text-success p-3 rounded-circle"><i class="bi bi-cash-stack fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">ការទូទាត់របស់អ្នករៀបចំ</h6>
                                    <div class="text-muted small">ផ្ទៀងផ្ទាត់បង្កាន់ដៃបង់ប្រាក់</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/checkin/' . $workshop['id']; ?>" class="card text-decoration-none border h-100 p-3 hover-shadow" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info-subtle text-info p-3 rounded-circle"><i class="bi bi-qr-code-scan fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">ស្កេនវត្តមានរហ័ស</h6>
                                    <div class="text-muted small">អេក្រង់ស្កេន QR & វត្តមាន</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/live/' . $workshop['id']; ?>" class="card text-decoration-none border h-100 p-3 hover-shadow" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-danger-subtle text-danger p-3 rounded-circle"><i class="bi bi-chat-quote-fill fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">សួរ-ឆ្លើយ & បោះឆ្នោត</h6>
                                    <div class="text-muted small">អន្តរកម្មផ្ទាល់ជាមួយសិក្ខាកាម</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo APP_URL; ?>/reports/workshop/<?php echo $workshop['id']; ?>" class="card text-decoration-none border h-100 p-3 hover-shadow">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning p-3 rounded-circle"><i class="bi bi-file-earmark-bar-graph fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">របាយការណ៍សរុប</h6>
                                    <div class="text-muted small">សង្ខេបប្រតិបត្តិ & ទាញយកឯកសារ</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/workshops/' . $workshop['id'] . '/delegations'; ?>" class="card text-decoration-none border h-100 p-3 hover-shadow border-primary" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white p-3 rounded-circle"><i class="bi bi-geo-alt-fill fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">កូតា & ប្រតិភូ ២៥ ខេត្ត</h6>
                                    <div class="text-muted small">គ្រប់គ្រងកូតាខេត្ត & ជំនួសសមាជិក</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/workshops/' . $workshop['id'] . '/allowances'; ?>" class="card text-decoration-none border h-100 p-3 hover-shadow border-success" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success text-white p-3 rounded-circle"><i class="bi bi-cash-stack fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">កញ្ចប់ថវិកា & ប្រាក់ឧបត្ថម្ភ</h6>
                                    <div class="text-muted small">តុបើកប្រាក់ & Anti-Double Claim</div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="card text-decoration-none border h-100 p-3 hover-shadow <?php echo $isUnpaid ? 'border-warning bg-warning-subtle' : ''; ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="<?php echo $isUnpaid ? 'bg-warning text-white' : 'bg-secondary-subtle text-secondary'; ?> p-3 rounded-circle"><i class="bi bi-receipt fs-4"></i></div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1">វិក្កយបត្រ & ចំណុះ</h6>
                                    <div class="text-muted small"><?php echo $isUnpaid ? 'បង់ថ្លៃដំណើរការប្រព័ន្ធ' : 'ថ្លៃសេវាប្រព័ន្ធ & ការដំឡើង'; ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sessions List -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> កាលវិភាគ & របៀបវារៈកម្មវិធី</h5>
            </div>
            <div class="card-body">
                <?php if (empty($sessions)): ?>
                    <p class="text-muted mb-0 small">កម្មវិធីពេញមួយថ្ងៃស្តង់ដារ គ្រោងធ្វើនៅថ្ងៃទី <?php echo formatDate($workshop['start_date']); ?>។</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($sessions as $s): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1 fw-bold"><?php echo e($s['name']); ?></h6>
                                    <div class="text-muted small">
                                        <i class="bi bi-calendar me-1"></i><?php echo formatDate($s['session_date']); ?> 
                                        &bull; <i class="bi bi-clock me-1"></i><?php echo e($s['start_time']); ?> - <?php echo e($s['end_time']); ?>
                                        &bull; អ្នកសម្របសម្រួល: <?php echo e($s['facilitator'] ?: $workshop['trainer_name']); ?>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border"><?php echo ucfirst($s['status']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Tickets & Event Links -->
    <div class="col-lg-4">
        <!-- Public Link & QR Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-share-fill me-2 text-primary"></i> តំណភ្ជាប់ព្រឹត្តិការណ៍សាធារណៈ</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">ចែករំលែកតំណភ្ជាប់ចុះឈ្មោះនេះជាមួយសិក្ខាកាម:</p>
                <?php $publicEventUrl = qrCodeUrl('/event/' . $workshop['slug']); ?>
                <div class="input-group mb-3">
                    <input type="text" id="publicUrl" class="form-control form-control-sm" readonly value="<?php echo e($publicEventUrl); ?>">
                    <button class="btn btn-sm btn-outline-primary" type="button" onclick="copyPublicLink('publicUrl', this)"><i class="bi bi-clipboard me-1"></i> ចម្លង</button>
                </div>
                <div class="text-center p-3 bg-light rounded">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?php echo urlencode($publicEventUrl); ?>" class="img-fluid rounded border shadow-sm" alt="Public QR">
                    <div class="small text-muted mt-2"><i class="bi bi-phone me-1"></i> កូដ QR សម្រាប់ស្កេនតាមទូរស័ព្ទ</div>
                </div>
                <?php if (!$billing || $billing['payment_status'] !== 'paid'): ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="btn btn-warning fw-bold w-100 py-2">
                            <i class="bi bi-credit-card me-1"></i> បង់ថ្លៃប្រព័ន្ធ
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ticket Types Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-ticket-detailed-fill me-2 text-primary"></i> សំបុត្រដែលបានកំណត់</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($tickets as $t): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?php echo e($t['name']); ?></div>
                                <div class="text-muted small"><?php echo ucfirst($t['ticket_type']); ?></div>
                            </div>
                            <span class="badge bg-primary fs-6">
                                <?php echo $t['price'] == 0 ? 'ឥតគិតថ្លៃ' : '$' . number_format($t['price'], 2); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Increase Capacity -->
<div class="modal fade" id="upgradeCapacityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">ដំឡើងចំណុះសិក្ខាកាមបន្ថែម</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    អ្នកគ្រាន់តែបង់ប្រាក់បន្ថែមលើចំនួនខុសគ្នារវាងកម្រិតបច្ចុប្បន្ន និងកម្រិតថ្មីប៉ុណ្ណោះ។ ការទូទាត់មុនរបស់អ្នកមិនបាត់បង់ឡើយ។
                </p>
                <div class="mb-3">
                    <label class="form-label">ចំណុះបច្ចុប្បន្ន</label>
                    <input type="text" class="form-control" value="<?php echo number_format($workshop['capacity']); ?> នាក់" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">ចំណុះថ្មីដែលត្រូវការ</label>
                    <input type="number" id="newCapacityInput" class="form-control" min="<?php echo $workshop['capacity'] + 1; ?>" value="<?php echo $workshop['capacity'] + 100; ?>">
                </div>
                <div id="upgradeCalculationBox" class="alert alert-info py-2 small d-none">
                    ថ្លៃសេវាដែលត្រូវបង់បន្ថែម: <strong id="upgradeFeeAmount">$0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="button" class="btn btn-primary" onclick="confirmCapacityUpgrade(<?php echo $workshop['id']; ?>)">បន្តដំឡើងចំណុះ</button>
            </div>
        </div>
    </div>
</div>

<script>
async function duplicateWorkshop(id) {
    if (!confirm('តើអ្នកពិតជាចង់ចម្លងទម្រង់សិក្ខាសាលានេះ (សំបុត្រ, ការរចនា, ប្រអប់ព័ត៌មាន) ដោយមិនចម្លងទិន្នន័យសិក្ខាកាមចាស់មែនទេ?')) return;
    const res = await apiRequest(`<?php echo APP_URL; ?>/workshops/${id}/duplicate`, 'POST');
    if (res.success && res.data.redirect) {
        window.location.href = res.data.redirect;
    } else {
        alert(res.message || 'ការចម្លងមិនបានជោគជ័យ។');
    }
}

async function confirmCapacityUpgrade(id) {
    const newCap = document.getElementById('newCapacityInput').value;
    const form = new FormData();
    form.append('new_capacity', newCap);
    form.append('confirm', '1');
    form.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    const res = await fetch(`<?php echo APP_URL; ?>/workshops/${id}/upgrade-capacity`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: form
    });
    const data = await res.json();
    if (data.success) {
        alert(data.message);
        location.reload();
    } else {
        alert(data.message || 'ការដំឡើងចំណុះមិនបានជោគជ័យ។');
    }
}

function copyPublicLink(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    const textToCopy = input.value;
    const button = btn || (window.event ? window.event.currentTarget : null);

    const doCopy = () => {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(textToCopy);
        } else {
            document.execCommand('copy');
            return Promise.resolve();
        }
    };

    doCopy().then(() => {
        if (button) {
            const origHtml = button.innerHTML;
            button.className = 'btn btn-sm btn-success text-white';
            button.innerHTML = '<i class="bi bi-check2 me-1" style="transform: scale(1.2); transition: transform 0.15s ease;"></i> បានចម្លង!';

            setTimeout(() => {
                button.className = 'btn btn-sm btn-outline-primary';
                button.innerHTML = origHtml;
            }, 1200);
        }
    }).catch(e => console.error('Copy failed:', e));
}
</script>
