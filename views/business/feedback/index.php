<?php
// views/business/feedback/index.php
?>
<div class="container-fluid py-4">
    <!-- Breadcrumb & Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> ត្រឡប់ទៅសិក្ខាសាលា
            </a>
            <h3 class="fw-bold mb-0">
                <i class="bi bi-star-fill text-warning me-2"></i>មតិកែលម្អ & ការវាយតម្លៃ (Feedback & Survey)
            </h3>
            <div class="text-muted small"><?= htmlspecialchars($workshop['name']) ?></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/live/<?= $workshop['id'] ?>/feedback" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="bi bi-box-arrow-up-right me-1"></i> បើកទំព័រស្ទង់មតិ
            </a>
            <a href="<?= APP_URL ?>/live/<?= $workshop['id'] ?>/screen" target="_blank" class="btn btn-info btn-sm fw-bold text-dark">
                <i class="bi bi-display me-1"></i> បើកលើ Projector
            </a>
        </div>
    </div>

    <!-- Rating Summary Row -->
    <div class="row g-4 mb-4">
        <!-- Average Score Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-muted text-uppercase small fw-bold mb-2">ពិន្ទុវាយតម្លៃជាមធ្យម</div>
                    <div class="display-3 fw-bold text-warning mb-1">
                        <?= $stats['avg_rating'] > 0 ? $stats['avg_rating'] : '0.0' ?>
                    </div>
                    <div class="fs-4 text-warning mb-2">
                        <?php 
                        $stars = round($stats['avg_rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $stars ? '<i class="bi bi-star-fill"></i> ' : '<i class="bi bi-star text-secondary opacity-50"></i> ';
                        }
                        ?>
                    </div>
                    <div class="text-muted">ផ្អែកលើការវាយតម្លៃសរុប <strong><?= (int)$stats['total_responses'] ?></strong> នាក់</div>
                </div>
            </div>
        </div>

        <!-- Rating Breakdown Card -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100 p-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">ការបែងចែកកម្រិតផ្កាយ</h6>
                    <?php 
                    $total = max(1, (int)$stats['total_responses']);
                    for ($s = 5; $s >= 1; $s--): 
                        $count = $ratingCounts[$s] ?? 0;
                        $pct = round(($count / $total) * 100);
                    ?>
                    <div class="d-flex align-items-center mb-2 small">
                        <span style="width: 60px;" class="fw-bold"><?= $s ?> ផ្កាយ</span>
                        <div class="progress flex-grow-1 mx-2" style="height: 10px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pct ?>%;"></div>
                        </div>
                        <span style="width: 45px;" class="text-muted text-end"><?= $count ?> នាក់</span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Share / QR Box -->
        <div class="col-lg-3">
            <div class="card shadow-sm border-0 h-100 p-3 text-center">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h6 class="fw-bold mb-2">QR Code ផ្ដល់មតិកែលម្អ</h6>
                    <?php $fbLink = APP_URL . '/live/' . $workshop['id'] . '/feedback'; ?>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($fbLink) ?>" 
                         alt="QR Feedback" class="img-fluid rounded border p-1 mb-2" style="width: 120px;">
                    <a href="<?= $fbLink ?>" target="_blank" class="small text-truncate w-100 d-block font-monospace"><?= $fbLink ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Comments / Responses Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-chat-heart-fill text-danger me-2"></i>មតិយោបល់ និងចម្លើយពីសិក្ខាកាម (Responses)
            </h5>
            <span class="badge bg-secondary"><?= count($responses) ?> នាក់</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 180px;">កាលបរិច្ឆេទ & ម៉ោង</th>
                            <th style="width: 150px;">សិក្ខាកាម</th>
                            <th style="width: 140px;">ពិន្ទុសរុប</th>
                            <th>មតិកែលម្អ និងការផ្ដល់យោបល់</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $r): ?>
                            <?php 
                            $ans = json_decode($r['answers'] ?? '{}', true) ?: [];
                            $comment = $ans['comments'] ?? ($ans['suggestions'] ?? '-');
                            $speakerScore = $ans['speaker_rating'] ?? null;
                            $contentScore = $ans['content_rating'] ?? null;
                            ?>
                            <tr>
                                <td class="ps-4 text-muted small">
                                    <i class="bi bi-calendar-event me-1"></i><?= date('d/m/Y H:i', strtotime($r['submitted_at'])) ?>
                                </td>
                                <td>
                                    <strong class="text-dark">
                                        <?= $r['is_anonymous'] ? 'មិនបញ្ចេញឈ្មោះ' : htmlspecialchars($r['participant_name']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark fs-6 px-2 py-1">
                                        ⭐ <?= number_format($r['overall_rating'], 1) ?> / 5
                                    </span>
                                </td>
                                <td>
                                    <div class="text-secondary"><?= htmlspecialchars($comment) ?></div>
                                    <?php if ($speakerScore || $contentScore): ?>
                                    <div class="small text-muted mt-1">
                                        <?php if ($speakerScore): ?>
                                            <span class="badge bg-light text-dark border me-1">វាគ្មិន: <?= $speakerScore ?>/5</span>
                                        <?php endif; ?>
                                        <?php if ($contentScore): ?>
                                            <span class="badge bg-light text-dark border">ខ្លឹមសារ: <?= $contentScore ?>/5</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($responses)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 mb-2 d-block opacity-50"></i>
                                    មិនទាន់មានសិក្ខាកាមផ្ដល់មតិកែលម្អនៅឡើយទេ។
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
