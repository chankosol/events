<!DOCTYPE html>
<html lang="km" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'មជ្ឈមណ្ឌលបញ្ជាផ្ទាល់') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html, input, button, select, textarea, .btn {
            font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        }
        body { background-color: #121212; color: #fff; overflow-x: hidden; }
        .command-card { background-color: #1e1e1e; border: 1px solid #333; height: 100%; border-radius: 8px; }
        .stat-value { font-size: 2.5rem; font-weight: bold; }
        .top-bar { background-color: #000; border-bottom: 1px solid #333; }
        .fullscreen-btn { cursor: pointer; }
    </style>
</head>
<body>
    <div class="top-bar p-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ</a>
            <span class="fs-4 fw-bold text-uppercase"><?= htmlspecialchars($workshop['name']) ?></span>
            <span class="ms-3 badge bg-danger">ផ្សាយផ្ទាល់ (LIVE)</span>
        </div>
        <div>
            <span id="currentTime" class="fs-5 fw-bold font-monospace me-4"></span>
            <button class="btn btn-outline-light btn-sm fullscreen-btn" onclick="toggleFullScreen()"><i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់</button>
        </div>
    </div>

    <div class="container-fluid p-4">
        <!-- KPI Grid -->
        <div class="row g-3 mb-4 text-center">
            <div class="col-md-3 col-6"><div class="command-card p-3"><div class="text-muted text-uppercase small">បានចុះឈ្មោះ</div><div class="stat-value text-primary" id="stat-registered"><?= $stats['total_registered'] ?></div></div></div>
            <div class="col-md-3 col-6"><div class="command-card p-3"><div class="text-muted text-uppercase small">បានបញ្ជាក់</div><div class="stat-value text-success" id="stat-confirmed"><?= $stats['confirmed'] ?? 0 ?></div></div></div>
            <div class="col-md-3 col-6"><div class="command-card p-3"><div class="text-muted text-uppercase small">បានស្កេនវត្តមាន</div><div class="stat-value text-info" id="stat-checkedin"><?= $stats['checked_in'] ?? 0 ?></div></div></div>
            <div class="col-md-3 col-6"><div class="command-card p-3"><div class="text-muted text-uppercase small">អត្រាវត្តមាន</div><div class="stat-value text-warning" id="stat-attendance"><?= ($stats['confirmed']>0) ? round(($stats['checked_in']/$stats['confirmed'])*100) : 0 ?>%</div></div></div>
            
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">បានបង់ប្រាក់</div><div class="fs-4 fw-bold text-success" id="stat-paid"><?= $stats['paid'] ?? 0 ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">រង់ចាំបង់</div><div class="fs-4 fw-bold text-danger" id="stat-pending"><?= $stats['pending_payment'] ?? 0 ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">VIP</div><div class="fs-4 fw-bold text-warning" id="stat-vip"><?= $stats['vip_count'] ?? 0 ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">សំណួរ</div><div class="fs-4 fw-bold text-primary" id="stat-questions"><?= $openQuestions ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">សំណើជំនួយ</div><div class="fs-4 fw-bold text-warning" id="stat-requests"><?= $openRequests ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">កាដូ</div><div class="fs-4 fw-bold text-info" id="stat-gifts"><?= $giftCount ?></div></div></div>
        </div>

        <div class="row g-4">
            <!-- Questions Panel -->
            <div class="col-lg-6">
                <div class="command-card d-flex flex-column" style="height: 500px;">
                    <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-uppercase"><i class="bi bi-question-circle text-primary me-2"></i> សួរ-ឆ្លើយផ្ទាល់ (Live Q&A)</h5>
                        <span class="badge bg-primary rounded-pill"><?= count($questions) ?></span>
                    </div>
                    <div class="p-3 flex-grow-1 overflow-auto">
                        <?php foreach ($questions as $q): ?>
                        <div class="card bg-dark border-secondary mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-info"><?= htmlspecialchars($q['participant_name'] ?: 'មិនបញ្ចេញឈ្មោះ') ?></span>
                                    <span class="badge bg-secondary"><i class="bi bi-arrow-up"></i> <?= $q['upvotes'] ?></span>
                                </div>
                                <p class="mb-3 fs-5"><?= htmlspecialchars($q['question_text']) ?></p>
                                <div class="d-flex gap-2">
                                    <?php if($q['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success">អនុម័ត</button>
                                    <button class="btn btn-sm btn-danger">បដិសេធ</button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary">ឆ្លើយរួច</button>
                                    <?php if(!$q['is_pinned']): ?>
                                    <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pin-angle"></i> ខ្ទាស់</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($questions)): ?>
                        <div class="text-center text-muted mt-5">មិនទាន់មានសំណួរនៅឡើយទេ។</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Requests & Sessions Panel -->
            <div class="col-lg-6">
                <div class="command-card mb-4" style="height: 240px;">
                    <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-uppercase"><i class="bi bi-bell text-warning me-2"></i> សំណើជំនួយពីសិក្ខាកាម (Requests)</h5>
                    </div>
                    <div class="p-3 overflow-auto" style="height: 180px;">
                        <?php foreach ($requests as $r): ?>
                        <div class="alert alert-dark border-secondary mb-2 p-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-<?= $r['priority']=='high'?'danger':'secondary' ?> me-2"><?= strtoupper($r['priority']) ?></span>
                                <span class="fw-bold"><?= htmlspecialchars($r['participant_name']) ?>:</span> 
                                <?= htmlspecialchars($r['request_type']) ?>
                            </div>
                            <button class="btn btn-sm btn-success">ដោះស្រាយរួច</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="command-card" style="height: 240px;">
                    <div class="p-3 border-bottom border-secondary">
                        <h5 class="mb-0 text-uppercase"><i class="bi bi-calendar-event text-info me-2"></i> កាលវិភាគថ្ងៃនេះ</h5>
                    </div>
                    <div class="p-3 overflow-auto" style="height: 180px;">
                        <ul class="list-group list-group-flush" style="background: transparent;">
                            <?php foreach ($sessions as $s): ?>
                            <li class="list-group-item bg-transparent text-white border-secondary px-0">
                                <strong><?= date('H:i', strtotime($s['start_time'])) ?></strong> - <?= htmlspecialchars($s['name']) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').innerText = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        setInterval(() => {
            fetch(`<?= APP_URL ?>/api/workshops/stats?id=<?= $workshop['id'] ?>`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const d = res.data;
                        document.getElementById('stat-registered').innerText = d.total;
                        document.getElementById('stat-confirmed').innerText = d.confirmed || 0;
                        document.getElementById('stat-checkedin').innerText = d.checked_in || 0;
                        document.getElementById('stat-paid').innerText = d.paid || 0;
                        document.getElementById('stat-questions').innerText = d.open_questions || 0;
                        document.getElementById('stat-requests').innerText = d.open_requests || 0;
                        
                        let pct = d.confirmed > 0 ? Math.round((d.checked_in / d.confirmed) * 100) : 0;
                        document.getElementById('stat-attendance').innerText = pct + '%';
                    }
                });
        }, 10000);
    </script>
</body>
</html>
