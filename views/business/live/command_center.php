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
        body, html, input, button, select, textarea, .btn, span, div, p, label, #adminTimerClock {
            font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        }
        body { background-color: #121212; color: #fff; overflow-x: hidden; }
        .command-card { background-color: #1e1e1e; border: 1px solid #333; height: 100%; border-radius: 8px; }
        .stat-value { font-size: 2.5rem; font-weight: bold; }
        .top-bar { background-color: #000; border-bottom: 1px solid #333; }
        .fullscreen-btn { cursor: pointer; }
        
        .question-item {
            transition: all 0.25s ease;
            background-color: #262626 !important;
        }
        .question-item.pinned {
            border: 2px solid #ffc107 !important;
            background: linear-gradient(145deg, #2c2512 0%, #1e1e1e 100%) !important;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.15);
        }
        .question-item.answered {
            border-left: 4px solid #198754 !important;
            opacity: 0.85;
        }
        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #0d6efd;
            border-radius: 50%;
            margin-right: 5px;
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar p-3 d-flex justify-content-between align-items-center">
        <div>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ</a>
            <span class="fs-4 fw-bold text-uppercase"><?= htmlspecialchars($workshop['name']) ?></span>
            <span class="ms-3 badge bg-danger">ផ្សាយផ្ទាល់ (LIVE)</span>
        </div>
        <div class="d-flex align-items-center">
            <a href="<?= APP_URL ?>/live/<?= $workshop['id'] ?>/screen" target="_blank" class="btn btn-info btn-sm fw-bold me-2 shadow-sm text-dark">
                <i class="bi bi-display me-1"></i> បើកផ្ទាំង Projector
            </a>
            <button class="btn btn-warning btn-sm fw-bold me-3 shadow-sm" onclick="openQrModal()">
                <i class="bi bi-qr-code-scan me-1"></i> QR Code សួរសំណួរ
            </button>
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
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">សំណួរកំពុងសួរ</div><div class="fs-4 fw-bold text-primary" id="stat-questions"><?= $openQuestions ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">សំណើជំនួយ</div><div class="fs-4 fw-bold text-warning" id="stat-requests"><?= $openRequests ?></div></div></div>
            <div class="col-md-2 col-4"><div class="command-card p-2"><div class="text-muted text-uppercase small">កាដូ</div><div class="fs-4 fw-bold text-info" id="stat-gifts"><?= $giftCount ?></div></div></div>
        </div>

        <div class="row g-4">
            <!-- Questions Panel (Enhanced with Tabs, Pin, Done, Real-time) -->
            <div class="col-lg-7">
                <div class="command-card d-flex flex-column" style="height: 540px;">
                    <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 text-uppercase"><i class="bi bi-question-circle text-primary me-2"></i> សួរ-ឆ្លើយផ្ទាល់ (Live Q&A)</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-warning btn-sm active text-dark fw-light" id="tabBtnPending" onclick="switchQaTab('pending')">
                                    រង់ចាំ (<span id="qaPendingCount"><?= $pendingCount ?? 0 ?></span>)
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm fw-light" id="tabBtnApproved" onclick="switchQaTab('approved')">
                                    កំពុងបង្ហាញ (<span id="qaApprovedCount"><?= $approvedCount ?? 0 ?></span>)
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm fw-light" id="tabBtnAnswered" onclick="switchQaTab('answered')">
                                    ឆ្លើយរួច (<span id="qaAnsweredCount"><?= $answeredCount ?? 0 ?></span>)
                                </button>
                            </div>
                            <button class="btn btn-sm btn-outline-warning" title="បង្ហាញ QR Code សម្រាប់អ្នកចូលរួមស្កេន" onclick="openQrModal()">
                                <i class="bi bi-qr-code"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Live Q&A Session Timer & Status Control Bar -->
                    <div class="px-3 py-2 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: rgba(15, 23, 42, 0.65);">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-success fw-light" id="adminQaStatusBadge">
                                បើកទទួលសំណួរ
                            </span>
                            <span class="fs-5 fw-light text-success" id="adminTimerClock">បើកគ្មានកំណត់</span>
                            <span class="text-white-50 small d-none" id="adminTimerSub"></span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            <span class="text-white-50 small me-1">ថែមម៉ោង:</span>
                            <button class="btn btn-outline-warning btn-sm py-0 px-2 fw-light" onclick="addTimerMinutes(5)" title="បន្ថែម ៥ នាទី">
                                +5 នាទី
                            </button>
                            <button class="btn btn-outline-warning btn-sm py-0 px-2 fw-light" onclick="addTimerMinutes(10)" title="បន្ថែម ១០ នាទី">
                                +10 នាទី
                            </button>
                            <button class="btn btn-outline-warning btn-sm py-0 px-2 fw-light" onclick="addTimerMinutes(15)" title="បន្ថែម ១៥ នាទី">
                                +15 នាទី
                            </button>
                            <button class="btn btn-outline-info btn-sm py-0 px-2 fw-light" onclick="promptSetTimer()" title="កំណត់នាទីផ្ទាល់ខ្លួន">
                                កំណត់
                            </button>
                            <button class="btn btn-sm btn-danger py-0 px-2 fw-light" id="btnAdminToggleQa" onclick="toggleAdminQaStatus()">
                                បិទសួរ
                            </button>
                        </div>
                    </div>

                    <!-- Moderation Toolbar (Master Checkbox & Bulk Actions) -->
                    <div class="px-3 py-2 border-bottom border-secondary d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03);">
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check m-0 d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" id="masterSelectAll" onchange="toggleSelectAll(this.checked)" style="width: 1.15rem; height: 1.15rem; cursor: pointer;">
                                <label for="masterSelectAll" class="form-check-label small text-light fw-light user-select-none" style="cursor: pointer;">
                                    ជ្រើសទាំងអស់ (<span id="selectedCountText">0</span>)
                                </label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 d-none" id="bulkActionsGroup">
                            <button class="btn btn-sm btn-success py-1 px-2 fw-light" onclick="executeBulkAction('approve')">
                                បង្ហាញ
                            </button>
                            <button class="btn btn-sm btn-outline-light py-1 px-2 fw-light" onclick="executeBulkAction('hide')">
                                លាក់
                            </button>
                            <button class="btn btn-sm btn-outline-danger py-1 px-2 fw-light" onclick="executeBulkAction('reject')">
                                បដិសេធ
                            </button>
                        </div>
                    </div>

                    <!-- Questions List Container -->
                    <div class="p-3 flex-grow-1 overflow-auto" id="qaListContainer">
                        <!-- Loaded dynamically or rendered initially -->
                        <div class="text-center text-muted my-5">
                            <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                            <div>កំពុងដំណើរការទិន្នន័យ...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests & Sessions Panel -->
            <div class="col-lg-5">
                <!-- Requests Panel -->
                <div class="command-card mb-4" style="height: 260px;">
                    <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-uppercase"><i class="bi bi-bell text-warning me-2"></i> សំណើជំនួយពីសិក្ខាកាម (Requests)</h5>
                        <span class="badge bg-warning text-dark rounded-pill" id="requestHeaderCount"><?= count($requests) ?></span>
                    </div>
                    <div class="p-3 overflow-auto" style="height: 195px;" id="requestsListContainer">
                        <!-- Rendered via JS -->
                    </div>
                </div>

                <!-- Sessions / Agenda Panel -->
                <div class="command-card" style="height: 255px;">
                    <div class="p-3 border-bottom border-secondary">
                        <h5 class="mb-0 text-uppercase"><i class="bi bi-calendar-event text-info me-2"></i> កាលវិភាគថ្ងៃនេះ</h5>
                    </div>
                    <div class="p-3 overflow-auto" style="height: 190px;">
                        <ul class="list-group list-group-flush" style="background: transparent;">
                            <?php foreach ($sessions as $s): ?>
                            <li class="list-group-item bg-transparent text-white border-secondary px-0 py-2">
                                <span class="badge bg-secondary me-2"><?= date('H:i', strtotime($s['start_time'])) ?> - <?= date('H:i', strtotime($s['end_time'])) ?></span>
                                <strong><?= htmlspecialchars($s['name']) ?></strong>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($sessions)): ?>
                            <li class="list-group-item bg-transparent text-muted px-0">មិនទាន់មានកាលវិភាគលម្អិតនៅឡើយទេ។</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Projector Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-secondary shadow-lg">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold" id="qrModalLabel">
                        <i class="bi bi-qr-code text-warning me-2"></i> ស្កេនដើម្បីសួរសំណួរ & ស្នើសុំជំនួយ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="bg-white p-3 rounded-4 d-inline-block shadow mb-3">
                        <?php 
                        $joinUrl = APP_URL . '/live/' . $workshop['id'] . '/interactive';
                        ?>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=<?= urlencode($joinUrl) ?>" 
                             alt="QR Code Q&A" class="img-fluid" style="width: 250px; height: 250px;">
                    </div>
                    <h6 class="text-warning fw-bold mb-1">បើកកាមេរ៉ាទូរស័ព្ទដៃ ដើម្បីស្កេនចូលរួម</h6>
                    <p class="text-light small mb-3">អ្នកចូលរួមអាចសួរសំណួរទៅកាន់វាគ្មិន ឬស្នើសុំជំនួយបានភ្លាមៗពីទូរស័ព្ទដៃ</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control bg-black text-light border-secondary" id="joinUrlInput" value="<?= $joinUrl ?>" readonly>
                        <button class="btn btn-primary" onclick="copyJoinUrl()"><i class="bi bi-clipboard"></i> ចម្លងតំណ</button>
                        <a href="<?= $joinUrl ?>" target="_blank" class="btn btn-outline-light"><i class="bi bi-box-arrow-up-right"></i> បើក</a>
                    </div>
                </div>
                <div class="modal-footer border-secondary justify-content-center">
                    <small class="text-muted">អាចចាក់បញ្ចាំងផ្ទាំងនេះលើ Projector ដើម្បីឱ្យអ្នកចូលរួមទាំងអស់ក្នុងសាលមើលឃើញ</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const WORKSHOP_ID = <?= (int)$workshop['id'] ?>;
        const APP_URL = "<?= APP_URL ?>";
        let qaListPending = [];
        let qaListApproved = [];
        let qaListAnswered = [];
        let currentQaTab = 'pending'; // 'pending' or 'approved' or 'answered'
        let selectedQuestionIds = new Set();
        let requestsList = [];
        let qrModalInstance = null;

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

        function openQrModal() {
            if (!qrModalInstance) {
                qrModalInstance = new bootstrap.Modal(document.getElementById('qrModal'));
            }
            qrModalInstance.show();
        }

        function copyJoinUrl() {
            const copyText = document.getElementById("joinUrlInput");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("បានចម្លងតំណភ្ជាប់ជោគជ័យ: " + copyText.value);
        }

        function switchQaTab(tab) {
            currentQaTab = tab;
            selectedQuestionIds.clear();
            updateSelectedCount();

            const btnPending = document.getElementById('tabBtnPending');
            const btnApproved = document.getElementById('tabBtnApproved');
            const btnAnswered = document.getElementById('tabBtnAnswered');

            btnPending.className = `btn btn-sm ${tab === 'pending' ? 'btn-warning active text-dark fw-light' : 'btn-outline-warning fw-light'}`;
            btnApproved.className = `btn btn-sm ${tab === 'approved' ? 'btn-success active fw-light' : 'btn-outline-success fw-light'}`;
            btnAnswered.className = `btn btn-sm ${tab === 'answered' ? 'btn-secondary active fw-light' : 'btn-outline-secondary fw-light'}`;

            renderQuestions();
        }

        function getQuestionsForCurrentTab() {
            if (currentQaTab === 'pending') return qaListPending;
            if (currentQaTab === 'approved') return qaListApproved;
            if (currentQaTab === 'answered') return qaListAnswered;
            return [];
        }

        function toggleSelectAll(isChecked) {
            const list = getQuestionsForCurrentTab();
            if (isChecked) {
                list.forEach(q => selectedQuestionIds.add(parseInt(q.id)));
            } else {
                selectedQuestionIds.clear();
            }
            updateSelectedCount();
            document.querySelectorAll('.q-select-box').forEach(cb => {
                cb.checked = isChecked;
            });
        }

        function onItemSelectionChange(qId, isChecked) {
            if (isChecked) {
                selectedQuestionIds.add(parseInt(qId));
            } else {
                selectedQuestionIds.delete(parseInt(qId));
            }
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = selectedQuestionIds.size;
            const countEl = document.getElementById('selectedCountText');
            if (countEl) countEl.innerText = count;

            const bulkGroup = document.getElementById('bulkActionsGroup');
            if (bulkGroup) {
                if (count > 0) {
                    bulkGroup.classList.remove('d-none');
                } else {
                    bulkGroup.classList.add('d-none');
                }
            }

            const masterCb = document.getElementById('masterSelectAll');
            const currentList = getQuestionsForCurrentTab();
            if (masterCb && currentList.length > 0) {
                masterCb.checked = (selectedQuestionIds.size === currentList.length);
            } else if (masterCb) {
                masterCb.checked = false;
            }
        }

        function executeBulkAction(action) {
            if (selectedQuestionIds.size === 0) return;

            const ids = Array.from(selectedQuestionIds);
            const formData = new FormData();
            formData.append('action', action);
            formData.append('ids', JSON.stringify(ids));

            fetch(`${APP_URL}/api/questions/bulk-moderate`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    selectedQuestionIds.clear();
                    updateSelectedCount();
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => {
                console.error(e);
                alert('មានបញ្ហាក្នុងការតភ្ជាប់');
            });
        }

        function toggleQuestionVisibility(qId, shouldShow) {
            const action = shouldShow ? 'approve' : 'hide';
            const formData = new FormData();
            formData.append('action', action);

            fetch(`${APP_URL}/api/questions/${qId}/moderate`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                    fetchQuestions();
                }
            })
            .catch(e => {
                console.error(e);
                fetchQuestions();
            });
        }

        let adminRemainingSeconds = null;
        let adminQaStatus = 'open';

        function updateAdminTimerUI(qaControl) {
            if (!qaControl) return;
            adminQaStatus = qaControl.status || 'open';
            adminRemainingSeconds = qaControl.remaining_seconds !== undefined ? qaControl.remaining_seconds : null;
            renderAdminTimerUI();
        }

        function renderAdminTimerUI() {
            const badge = document.getElementById('adminQaStatusBadge');
            const clock = document.getElementById('adminTimerClock');
            const sub = document.getElementById('adminTimerSub');
            const btnToggle = document.getElementById('btnAdminToggleQa');

            if (adminQaStatus === 'closed') {
                badge.className = 'badge rounded-pill bg-danger fw-light';
                badge.innerText = 'បិទទទួលសំណួរ';
                clock.className = 'fs-5 fw-light text-danger';
                clock.innerText = 'បិទ';
                if (sub) sub.innerText = '';
                btnToggle.className = 'btn btn-sm btn-success py-0 px-2 fw-light';
                btnToggle.innerText = 'បើកសួរ';
                return;
            }

            btnToggle.className = 'btn btn-sm btn-danger py-0 px-2 fw-light';
            btnToggle.innerText = 'បិទសួរ';
            badge.className = 'badge rounded-pill bg-success fw-light';
            badge.innerText = 'បើកទទួលសំណួរ';

            if (adminRemainingSeconds !== null && adminRemainingSeconds >= 0) {
                const mins = Math.floor(adminRemainingSeconds / 60);
                const secs = adminRemainingSeconds % 60;
                const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                
                if (adminRemainingSeconds <= 120 && adminRemainingSeconds > 0) {
                    clock.className = 'fs-5 font-monospace fw-bold text-danger';
                    clock.innerText = formatted;
                    if (sub) sub.innerText = '';
                } else if (adminRemainingSeconds === 0) {
                    clock.className = 'fs-5 font-monospace fw-bold text-danger';
                    clock.innerText = '00:00';
                    if (sub) sub.innerText = '';
                } else {
                    clock.className = 'fs-5 font-monospace fw-bold text-warning';
                    clock.innerText = formatted;
                    if (sub) sub.innerText = '';
                }
            } else {
                clock.className = 'fs-5 fw-light text-success';
                clock.innerText = 'បើកគ្មានកំណត់';
                if (sub) sub.innerText = '';
            }
        }

        // 1-second interval tick for admin
        setInterval(() => {
            if (adminRemainingSeconds !== null && adminRemainingSeconds > 0 && adminQaStatus === 'open') {
                adminRemainingSeconds--;
                renderAdminTimerUI();
            }
        }, 1000);

        function addTimerMinutes(mins) {
            const formData = new FormData();
            formData.append('action', 'add_time');
            formData.append('minutes', mins);

            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/qa-timer`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    updateAdminTimerUI(res.qa_control);
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => console.error(e));
        }

        function promptSetTimer() {
            const val = prompt("បញ្ចូលចំនួននាទីសម្រាប់វេទិកាសួរសំណួរ (ឧទាហរណ៍៖ 15):", "15");
            if (!val) return;
            const mins = parseInt(val);
            if (isNaN(mins) || mins <= 0) {
                alert("សូមបញ្ចូលចំនួននាទីឱ្យបានត្រឹមត្រូវ");
                return;
            }

            const formData = new FormData();
            formData.append('action', 'set_timer');
            formData.append('minutes', mins);

            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/qa-timer`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    updateAdminTimerUI(res.qa_control);
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => console.error(e));
        }

        function toggleAdminQaStatus() {
            const newAction = (adminQaStatus === 'open') ? 'close' : 'open';
            const confirmMsg = (newAction === 'close') 
                ? "តើអ្នកប្រាកដជាចង់បិទការទទួលសំណួរទាំងអស់មែនទេ? (អ្នកចូលរួមនឹងមិនអាចសួរបានទៀតឡើយ)"
                : "តើអ្នកចង់បើកទទួលសំណួរឡើងវិញមែនទេ?";
            
            if (!confirm(confirmMsg)) return;

            const formData = new FormData();
            formData.append('action', newAction);

            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/qa-timer`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    updateAdminTimerUI(res.qa_control);
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => console.error(e));
        }

        // Fetch Live Questions
        function fetchQuestions() {
            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/questions?admin=1`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        qaListPending = res.pending || [];
                        qaListApproved = res.approved || [];
                        qaListAnswered = res.answered || [];

                        document.getElementById('qaPendingCount').innerText = qaListPending.length;
                        document.getElementById('qaApprovedCount').innerText = qaListApproved.length;
                        document.getElementById('qaAnsweredCount').innerText = qaListAnswered.length;
                        document.getElementById('stat-questions').innerText = (qaListPending.length + qaListApproved.length);

                        if (res.qa_control) {
                            updateAdminTimerUI(res.qa_control);
                        }

                        renderQuestions();
                        updateSelectedCount();
                    }
                })
                .catch(e => console.error("Error fetching questions", e));
        }

        // Render Questions according to selected tab
        function renderQuestions() {
            const container = document.getElementById('qaListContainer');
            const list = getQuestionsForCurrentTab();

            if (list.length === 0) {
                let emptyMsg = '';
                if (currentQaTab === 'pending') {
                    emptyMsg = 'គ្មានសំណួរដែលកំពុងរង់ចាំត្រួតពិនិត្យទេ។';
                } else if (currentQaTab === 'approved') {
                    emptyMsg = 'គ្មានសំណួរដែលកំពុងបង្ហាញលើផ្ទាំងធំនៅឡើយទេ។';
                } else {
                    emptyMsg = 'មិនទាន់មានសំណួរដែលបានឆ្លើយរួចនៅឡើយទេ។';
                }

                container.innerHTML = `
                    <div class="text-center text-muted my-5">
                        <i class="bi bi-chat-square-dots fs-1 mb-2 opacity-50"></i>
                        <div class="fs-6">${emptyMsg}</div>
                        ${currentQaTab === 'pending' ? '<button class="btn btn-sm btn-outline-warning mt-2" onclick="openQrModal()"><i class="bi bi-qr-code"></i> បង្ហាញ QR ឱ្យអ្នកចូលរួមស្កេន</button>' : ''}
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach(q => {
                const isPinned = q.is_pinned == 1;
                const isApproved = q.status === 'approved';
                const isPending = q.status === 'pending';
                const isAnswered = q.status === 'answered';
                const author = q.is_anonymous == 1 ? 'មិនបញ្ចេញឈ្មោះ' : (q.display_name || 'អ្នកចូលរួម');
                const isSelected = selectedQuestionIds.has(parseInt(q.id));

                html += `
                    <div class="card question-item border-secondary mb-3 shadow-sm ${isPinned ? 'pinned' : ''} ${isAnswered ? 'answered' : ''}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <input class="form-check-input q-select-box m-0" type="checkbox" value="${q.id}" 
                                           ${isSelected ? 'checked' : ''} onchange="onItemSelectionChange(${q.id}, this.checked)"
                                           style="width: 1.15rem; height: 1.15rem; cursor: pointer;">
                                    <span class="fw-bold text-info">${escapeHtml(author)}</span>
                                    ${isPinned ? '<span class="badge bg-warning text-dark fw-light">ខ្ទាស់</span>' : ''}
                                    ${isPending ? '<span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-light">រង់ចាំ</span>' : ''}
                                    ${isApproved ? '<span class="badge bg-success bg-opacity-25 text-success border border-success fw-light">កំពុងបង្ហាញ</span>' : ''}
                                    ${isAnswered ? '<span class="badge bg-info bg-opacity-25 text-info border border-info fw-light">ឆ្លើយរួច</span>' : ''}
                                    ${q.category && q.category !== 'General' ? `<span class="badge bg-dark border border-secondary text-secondary fw-light">${escapeHtml(q.category)}</span>` : ''}
                                    <span class="text-muted small">${formatTime(q.created_at)}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-secondary fs-6 fw-light"><i class="bi bi-hand-thumbs-up me-1"></i>${q.upvotes || 0}</span>
                                    
                                    <!-- Live Display Toggle Switch / Checkbox -->
                                    ${!isAnswered ? `
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-1 p-0" title="ធីកដើម្បីបង្ហាញលើផ្ទាំងធំ និងទូរស័ព្ទអ្នកចូលរួម">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" id="q-vis-${q.id}" 
                                               ${isApproved ? 'checked' : ''} onchange="toggleQuestionVisibility(${q.id}, this.checked)"
                                               style="width: 2.3rem; height: 1.25rem; cursor: pointer;">
                                        <label class="form-check-label small fw-light user-select-none ${isApproved ? 'text-success' : 'text-warning'}" 
                                               for="q-vis-${q.id}" style="cursor: pointer; min-width: 55px;">
                                            ${isApproved ? 'បង្ហាញ' : 'លាក់'}
                                        </label>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>

                            <p class="mb-3 fs-5 text-light" style="white-space: pre-line;">${escapeHtml(q.question_text)}</p>

                            <!-- Actions Toolbar -->
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                ${!isAnswered ? `
                                    <!-- Pin / Unpin Button -->
                                    ${!isPinned ? `
                                    <button class="btn btn-sm btn-outline-warning fw-light" onclick="moderateQuestion(${q.id}, 'pin')">
                                        ខ្ទាស់
                                    </button>
                                    ` : `
                                    <button class="btn btn-sm btn-warning text-dark fw-light" onclick="moderateQuestion(${q.id}, 'unpin')">
                                        ដោះខ្ទាស់
                                    </button>
                                    `}

                                    <!-- Answered Button (Mark Done removed, icon removed) -->
                                    <button class="btn btn-sm btn-outline-info fw-light" onclick="moderateQuestion(${q.id}, 'answer')">
                                        ឆ្លើយរួច
                                    </button>

                                    <!-- Reject Button (Icon removed) -->
                                    <button class="btn btn-sm btn-outline-danger fw-light" onclick="moderateQuestion(${q.id}, 'reject')">
                                        បដិសេធ
                                    </button>
                                ` : `
                                    <!-- Answered Tab Action: Restore to Queue -->
                                    <button class="btn btn-sm btn-outline-secondary fw-light" onclick="moderateQuestion(${q.id}, 'restore')">
                                        យកមកវិញ
                                    </button>
                                    ${q.answered_at ? `<small class="text-success ms-2 fw-light">ឆ្លើយនៅម៉ោង ${formatTime(q.answered_at)}</small>` : ''}
                                `}
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Moderate Question Action (Pin, Unpin, Answer/Done, Restore, Reject, Hide)
        function moderateQuestion(qId, action) {
            const formData = new FormData();
            formData.append('action', action);

            fetch(`${APP_URL}/api/questions/${qId}/moderate`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    fetchQuestions();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => {
                console.error(e);
                alert('មានបញ្ហាក្នុងការតភ្ជាប់');
            });
        }

        // Fetch Live Requests
        function fetchRequests() {
            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/requests`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        requestsList = res.requests || [];
                        document.getElementById('stat-requests').innerText = requestsList.length;
                        document.getElementById('requestHeaderCount').innerText = requestsList.length;
                        renderRequests();
                    }
                })
                .catch(e => console.error("Error fetching requests", e));
        }

        // Render Requests
        function renderRequests() {
            const container = document.getElementById('requestsListContainer');
            if (requestsList.length === 0) {
                container.innerHTML = `<div class="text-center text-muted my-4"><i class="bi bi-check2-all fs-2"></i><div>មិនទាន់មានសំណើជំនួយពីសិក្ខាកាមទេ។</div></div>`;
                return;
            }

            let html = '';
            requestsList.forEach(r => {
                const badgeClass = r.priority === 'urgent' ? 'bg-danger' : (r.priority === 'high' ? 'bg-warning text-dark' : 'bg-secondary');
                const requester = r.display_name || 'សិក្ខាកាម';

                html += `
                    <div class="alert alert-dark border-secondary mb-2 p-2 d-flex justify-content-between align-items-center">
                        <div class="me-2 text-truncate">
                            <span class="badge ${badgeClass} me-2">${r.priority ? r.priority.toUpperCase() : 'NORMAL'}</span>
                            <strong class="text-info">${escapeHtml(requester)}:</strong>
                            <span class="text-light ms-1">${escapeHtml(r.request_type)}</span>
                            <div class="small text-muted mt-1 text-truncate">${escapeHtml(r.description || '')}</div>
                        </div>
                        <button class="btn btn-sm btn-success flex-shrink-0" onclick="resolveRequest(${r.id})">
                            <i class="bi bi-check-lg"></i> ដោះស្រាយរួច
                        </button>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Resolve Request Action
        function resolveRequest(rId) {
            const formData = new FormData();
            formData.append('status', 'resolved');

            fetch(`${APP_URL}/api/requests/${rId}/resolve`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    fetchRequests();
                } else {
                    alert(res.message || 'ប្រតិបត្តិការមិនជោគជ័យ');
                }
            })
            .catch(e => console.error(e));
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
        }

        function formatTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr.replace(/-/g, '/'));
            return isNaN(d) ? dateStr : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        // KPI Stats Refresh
        function fetchKpiStats() {
            fetch(`${APP_URL}/api/workshops/stats?id=${WORKSHOP_ID}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const d = res.data;
                        document.getElementById('stat-registered').innerText = d.total || 0;
                        document.getElementById('stat-confirmed').innerText = d.confirmed || 0;
                        document.getElementById('stat-checkedin').innerText = d.checked_in || 0;
                        document.getElementById('stat-paid').innerText = d.paid || 0;
                        
                        let pct = d.confirmed > 0 ? Math.round((d.checked_in / d.confirmed) * 100) : 0;
                        document.getElementById('stat-attendance').innerText = pct + '%';
                    }
                })
                .catch(e => console.error(e));
        }

        // Initial Data Fetch
        fetchQuestions();
        fetchRequests();
        fetchKpiStats();

        // Polling interval every 4 seconds for real-time responsiveness
        setInterval(() => {
            fetchQuestions();
            fetchRequests();
            fetchKpiStats();
        }, 4000);
    </script>
</body>
</html>
