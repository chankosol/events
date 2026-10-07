<?php
// views/public/workshop.php
?>
<?php if (!empty($isPreview)): ?>
    <div class="alert alert-warning border-0 rounded-0 text-center py-2 mb-0 shadow-sm sticky-top">
        <i class="bi bi-eye-fill me-1"></i> <strong>របៀបមើលសាកល្បង (Preview Mode)</strong>៖ សិក្ខាសាលានេះមិនទាន់បើកទទួលការចុះឈ្មោះជាផ្លូវការនៅឡើយទេ។ អ្នកកំពុងមើលជាអ្នកគ្រប់គ្រង។
    </div>
<?php endif; ?>
<div class="workshop-public-page">
    <!-- Hero / Cover Banner -->
    <div class="bg-dark text-white position-relative py-5" style="<?php echo !empty($workshop['cover_image']) ? 'background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.85)), url(' . APP_URL . '/' . e($workshop['cover_image']) . ') center/cover no-repeat;' : 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);'; ?>">
        <div class="container py-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-primary mb-3 px-3 py-2 fs-6 text-uppercase tracking-wider">
                        <?php echo e($workshop['category'] ?: 'សិក្ខាសាលា'); ?> &bull; <?php echo $workshop['workshop_type'] === 'in-person' ? 'ជួបផ្ទាល់' : ($workshop['workshop_type'] === 'online' ? 'អនឡាញ' : 'រួមគ្នា'); ?>
                    </span>
                    <h1 class="display-4 fw-bold mb-3"><?php echo e($workshop['name']); ?></h1>
                    <?php if (!empty($workshop['short_description'])): ?>
                        <p class="lead mb-4 text-light opacity-90"><?php echo e($workshop['short_description']); ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-wrap gap-4 text-light small">
                        <div><i class="bi bi-calendar3 me-2 fs-5 text-warning"></i><strong><?php echo formatDate($workshop['start_date']); ?></strong> <?php if (!empty($workshop['start_time'])): ?>ម៉ោង <?php echo e($workshop['start_time']); ?><?php endif; ?></div>
                        <div><i class="bi bi-geo-alt me-2 fs-5 text-warning"></i><strong><?php echo e($workshop['venue'] ?: 'ទីតាំងរៀបចំ'); ?></strong></div>
                        <div><i class="bi bi-person me-2 fs-5 text-warning"></i><?php echo e(!empty($workshop['trainer_title']) ? $workshop['trainer_title'] : 'វាគ្មិន'); ?>: <strong><?php echo e($workshop['trainer_name'] ?: 'អ្នកជំនាញ'); ?></strong></div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <div class="card shadow-lg border-0 text-dark p-3 text-start d-inline-block w-100" style="max-width: 360px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">ស្ថានភាពកៅអី</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    នៅសល់ <?php echo $remainingSeats; ?> កៅអី
                                </span>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <?php $seatPct = min(100, round((($workshop['capacity'] - $remainingSeats) / max(1, $workshop['capacity'])) * 100)); ?>
                                <div class="progress-bar bg-success" style="width: <?php echo $seatPct; ?>%;"></div>
                            </div>
                            <div class="mb-3">
                                <?php 
                                $minPrice = null;
                                foreach ($tickets as $t) {
                                    if ($minPrice === null || (float)$t['price'] < $minPrice) $minPrice = (float)$t['price'];
                                }
                                $isFreeEvent = ($workshop['payment_mode'] === 'free') || ($minPrice === null || $minPrice == 0.0);
                                ?>
                                <div class="text-muted small"><?= $isFreeEvent ? 'តម្លៃចូលរួម' : 'តម្លៃចាប់ផ្តើមពី' ?></div>
                                <div class="fs-3 fw-bold <?= $isFreeEvent ? 'text-success' : 'text-primary' ?>">
                                    <?= $isFreeEvent ? '<i class="bi bi-gift me-1"></i> ឥតគិតថ្លៃ (Free)' : '$' . number_format($minPrice, 2) ?>
                                </div>
                            </div>
                            <?php if ($workshop['status'] === 'registration_open'): ?>
                                <a href="<?php echo APP_URL; ?>/event/<?php echo e($workshop['slug']); ?>/register" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                    <i class="bi bi-pencil-square me-1"></i> ចុះឈ្មោះចូលរួមឥឡូវនេះ
                                </a>
                            <?php elseif ($workshop['status'] === 'active'): ?>
                                <button class="btn btn-warning btn-lg w-100 fw-bold shadow-sm" disabled>
                                    <i class="bi bi-clock-history me-1"></i> នឹងបើកទទួលការចុះឈ្មោះឆាប់ៗនេះ
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg w-100 fw-bold shadow-sm" disabled>
                                    <i class="bi bi-lock me-1"></i> ការចុះឈ្មោះត្រូវបានបិទ
                                </button>
                            <?php endif; ?>
                            <div class="text-center text-muted small mt-2">
                                <i class="bi bi-shield-check text-success me-1"></i> បញ្ជាក់ភ្លាមៗ & ទទួលបានកាត QR
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row g-5">
            <!-- Left Column: Details, Agenda, Trainer -->
            <div class="col-lg-8">
                <!-- Description -->
                <div class="mb-5">
                    <h3 class="fw-bold mb-3"><i class="bi bi-file-text me-2 text-primary"></i> អំពីសិក្ខាសាលានេះ</h3>
                    <div class="lead-text lh-lg text-secondary">
                        <?php echo nl2br(e($workshop['description'] ?: 'មិនទាន់មានការពិពណ៌នាលម្អិតនៅឡើយទេ។')); ?>
                    </div>
                </div>

                <!-- Agenda Sessions -->
                <?php if (!empty($sessions)): ?>
                    <div class="mb-5">
                        <h3 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i> កាលវិភាគ និងរបៀបវារៈ</h3>
                        <div class="list-group shadow-sm border-0">
                            <?php foreach ($sessions as $s): ?>
                                <div class="list-group-item p-3 border mb-2 rounded">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h5 class="fw-bold mb-0 text-dark"><?php echo e($s['name']); ?></h5>
                                        <span class="badge bg-light text-primary border">
                                            <i class="bi bi-clock me-1"></i><?php echo e($s['start_time']); ?> &ndash; <?php echo e($s['end_time']); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($s['description'])): ?>
                                        <p class="text-muted small mb-2"><?php echo e($s['description']); ?></p>
                                    <?php endif; ?>
                                    <div class="small text-muted">
                                        <i class="bi bi-person me-1"></i>សម្របសម្រួលដោយ: <?php echo e($s['facilitator'] ?: $workshop['trainer_name']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Trainer Bio -->
                <?php if (!empty($workshop['trainer_name'])): ?>
                    <div class="card shadow-sm border-0 bg-light p-4 mb-5">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="bi bi-person-fill fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1"><?php echo e($workshop['trainer_name']); ?></h4>
                                <div class="text-muted mb-2"><?php echo e(!empty($workshop['trainer_title']) ? $workshop['trainer_title'] : 'គ្រូបណ្តុះបណ្តាល / វាគ្មិនកិត្តិយស'); ?></div>
                                <?php if (!empty($workshop['trainer_bio'])): ?>
                                    <p class="mb-0 text-secondary small"><?php echo nl2br(e($workshop['trainer_bio'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Available Tickets -->
                <!-- Available Tickets (Show only if paid or multiple tiers exist) -->
                <?php if ($workshop['payment_mode'] !== 'free' || count($tickets) > 1): ?>
                <div class="mb-5">
                    <h3 class="fw-bold mb-3"><i class="bi bi-ticket-perforated me-2 text-primary"></i> ប្រភេទសំបុត្រ</h3>
                    <div class="row g-3">
                        <?php foreach ($tickets as $t): ?>
                            <div class="col-md-6">
                                <div class="card h-100 border shadow-sm p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="fw-bold text-dark mb-0"><?php echo e($t['name']); ?></h5>
                                        <span class="fs-4 fw-bold <?php echo (float)$t['price'] == 0 ? 'text-success' : 'text-primary'; ?>">
                                            <?php echo (float)$t['price'] == 0 ? 'ឥតគិតថ្លៃ' : '$' . number_format($t['price'], 2); ?>
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-3 flex-grow-1">
                                        <?php echo e($t['description'] ?: 'សំបុត្រចូលរួមពេញលេញ រួមទាំងឯកសារ និងសិទ្ធិទទួលបានវិញ្ញាបនបត្រឌីជីថល។'); ?>
                                    </p>
                                    <a href="<?php echo APP_URL; ?>/event/<?php echo e($workshop['slug']); ?>/register?ticket=<?php echo $t['id']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold">
                                        ជ្រើសរើសសំបុត្រនេះ
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Venue, Organizer, Share -->
            <div class="col-lg-4">
                <!-- Location & Map -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill text-danger me-2"></i> ទីតាំងរៀបចំ</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><?php echo e($workshop['venue'] ?: 'ទីតាំងរៀបចំ'); ?></h6>
                        <?php if (!empty($workshop['address'])): ?>
                            <p class="text-muted small mb-3"><?php echo e($workshop['address']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($workshop['google_maps_url'])): ?>
                            <a href="<?php echo e($workshop['google_maps_url']); ?>" target="_blank" class="btn btn-outline-danger btn-sm w-100 mb-2 fw-bold">
                                <i class="bi bi-map me-1"></i> បើកមើលក្នុង Google Maps
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Host Company -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-building text-primary me-2"></i> ស្ថាប័នរៀបចំ</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1"><?php echo e($workshop['business_name']); ?></h6>
                        <?php if (!empty($workshop['organizer'])): ?>
                            <div class="text-muted small mb-2"><?php echo e($workshop['organizer']); ?></div>
                        <?php endif; ?>
                        <div class="text-muted small">
                            <i class="bi bi-envelope me-1"></i> <?php echo e($workshop['business_email']); ?>
                        </div>
                    </div>
                </div>

                <!-- Share QR -->
                <div class="card shadow-sm border-0 text-center p-4">
                    <h6 class="fw-bold mb-2">ស្កេនដើម្បីមើល និងចែករំលែក</h6>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode(qrCodeUrl('/event/' . $workshop['slug'])); ?>" class="img-fluid rounded border p-2 mb-2" alt="Workshop QR">
                    <div class="text-muted small">កូដ QR សម្រាប់ផ្សព្វផ្សាយសិក្ខាសាលា</div>
                </div>
            </div>
        </div>
    </div>
</div>
