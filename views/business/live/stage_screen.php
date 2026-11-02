<!DOCTYPE html>
<html lang="km" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body, html, input, button, select, textarea, .btn {
            font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        }
        body {
            background-color: #0d1117;
            color: #f0f6fc;
            overflow: hidden;
            height: 100vh;
        }
        .stage-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .top-stage-bar {
            background: #161b22;
            border-bottom: 1px solid #30363d;
            padding: 12px 24px;
        }
        .main-stage-content {
            flex: 1;
            display: flex;
            overflow: hidden;
            padding: 20px 24px;
            gap: 24px;
        }
        .stage-left-panel {
            width: 36%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .stage-right-panel {
            width: 64%;
            display: flex;
            flex-direction: column;
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .qr-card-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            display: inline-block;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .question-feed {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        .stage-q-card {
            background: #21262d;
            border: 1px solid #30363d;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        .stage-q-card.pinned {
            background: linear-gradient(135deg, #332709 0%, #21262d 100%);
            border: 2px solid #e3b341 !important;
            box-shadow: 0 0 20px rgba(227, 179, 65, 0.2);
        }
        .pulse-live {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #ef4444;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1.6s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.6; }
        }
        /* Custom scrollbar for stage */
        .question-feed::-webkit-scrollbar {
            width: 6px;
        }
        .question-feed::-webkit-scrollbar-thumb {
            background: #30363d;
            border-radius: 4px;
        }

        @keyframes pulseTimer {
            0% { transform: scale(1); text-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }
            50% { transform: scale(1.05); text-shadow: 0 0 25px rgba(239, 68, 68, 0.95); }
            100% { transform: scale(1); text-shadow: 0 0 10px rgba(239, 68, 68, 0.4); }
        }
        .timer-pulse-danger {
            color: #ef4444 !important;
            animation: pulseTimer 1s infinite ease-in-out;
        }
        .stage-timer-card {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="stage-container">
        <!-- Top Stage Bar -->
        <div class="top-stage-bar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                    <span class="pulse-live"></span>ផ្សាយផ្ទាល់ (LIVE)
                </span>
                <span class="fs-4 fw-bold text-light"><?= htmlspecialchars($workshop['name']) ?></span>
                <?php if (!empty($workshop['trainer_name'])): ?>
                <span class="text-white-50 fs-6">| វាគ្មិន: <strong class="text-info"><?= htmlspecialchars($workshop['trainer_name']) ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span id="stageClock" class="fs-5 font-monospace fw-bold text-white-50"></span>
                <button class="btn btn-outline-light btn-sm" onclick="toggleFullScreen()">
                    <i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់
                </button>
            </div>
        </div>

        <!-- Main Stage Content -->
        <div class="main-stage-content">
            
            <!-- LEFT PANEL: QR Code & Instructions -->
            <div class="stage-left-panel text-center">
                <div>
                    <h5 class="text-warning fw-bold mb-1" id="qrTitle">
                        <i class="bi bi-qr-code-scan me-2"></i>ស្កេនដើម្បីសួរសំណួរ
                    </h5>
                    <p class="text-white-50 small mb-3" id="qrSubtitle">
                        បើកកាមេរ៉ាទូរស័ព្ទដៃ ដើម្បីចូលរួមសួរផ្ទាល់ និងគាំទ្រសំណួរ
                    </p>

                    <!-- QR Display -->
                    <?php 
                    $qaUrl = APP_URL . '/live/' . $workshop['id'] . '/interactive';
                    $feedbackUrl = APP_URL . '/live/' . $workshop['id'] . '/feedback';
                    ?>
                    <div class="qr-card-box my-2">
                        <img id="stageQrImage" 
                             src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=<?= urlencode($qaUrl) ?>" 
                             alt="Scan QR Code" style="width: 230px; height: 230px; display: block;">
                    </div>

                    <!-- URL Box with Copy Button -->
                    <div class="mt-3 d-flex justify-content-center">
                        <div class="d-inline-flex align-items-center bg-dark border border-secondary rounded-pill p-1 ps-3 pe-1 shadow-sm" style="max-width: 95%;">
                            <span class="text-info font-monospace text-truncate me-2 small" id="qrUrlDisplay" style="max-width: 230px;">
                                <?= $qaUrl ?>
                            </span>
                            <button class="btn btn-sm btn-outline-info rounded-pill py-1 px-3 fw-bold" onclick="copyStageUrl()" id="btnCopyStageUrl" title="ចម្លងតំណភ្ជាប់">
                                <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Countdown Timer / Status Box (Under URL) -->
                    <div class="mt-3 mx-auto px-4 py-2 rounded-4 border text-center stage-timer-card shadow" id="stageCountdownBox" style="background: rgba(15, 23, 42, 0.75); border-color: rgba(59, 130, 246, 0.35) !important; max-width: 280px; display: none;">
                        <div id="stageTimerContent"></div>
                    </div>
                </div>

                <!-- Stage Controls / Switch to Feedback Mode -->
                <div class="pt-3 border-top border-secondary">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning active" id="btnModeQa" onclick="setStageMode('qa')">
                            <i class="bi bi-chat-dots-fill me-1"></i> សួរ-ឆ្លើយ (Q&A)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btnModeFeedback" onclick="setStageMode('feedback')">
                            <i class="bi bi-star-fill me-1"></i> មតិកែលម្អ (Feedback)
                        </button>
                    </div>
                    <small class="text-white-50 d-block mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i>ប្តូរទៅរបៀប Feedback នៅពេលកម្មវិធីជិតបញ្ចប់
                    </small>
                </div>
            </div>

            <!-- RIGHT PANEL: Live Questions Board -->
            <div class="stage-right-panel">
                <div class="p-3 border-bottom border-secondary d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-0 fw-bold text-light">
                            <i class="bi bi-chat-square-quote-fill text-primary me-2"></i>សំណួរផ្ទាល់ពីសិក្ខាកាម (Live Q&A)
                        </h4>
                    </div>
                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                        សំណួរកំពុងរង់ចាំ: <span id="stageQuestionCount">0</span>
                    </span>
                </div>

                <!-- Real-time Questions Feed -->
                <div class="question-feed" id="stageFeed">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary mb-3"></div>
                        <div class="fs-5">កំពុងទាញយកសំណួរផ្ទាល់...</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const WORKSHOP_ID = <?= (int)$workshop['id'] ?>;
        const APP_URL = "<?= APP_URL ?>";
        const QA_URL = "<?= $qaUrl ?>";
        const FEEDBACK_URL = "<?= $feedbackUrl ?>";
        let currentMode = 'qa'; // 'qa' or 'feedback'

        function updateClock() {
            const now = new Date();
            document.getElementById('stageClock').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        function setStageMode(mode) {
            currentMode = mode;
            const titleEl = document.getElementById('qrTitle');
            const subEl = document.getElementById('qrSubtitle');
            const imgEl = document.getElementById('stageQrImage');
            const urlEl = document.getElementById('qrUrlDisplay');
            const btnQa = document.getElementById('btnModeQa');
            const btnFb = document.getElementById('btnModeFeedback');

            if (mode === 'feedback') {
                titleEl.innerHTML = `<i class="bi bi-star-fill text-warning me-2"></i>ស្កេនផ្ដល់មតិកែលម្អ`;
                subEl.innerText = `សូមជួយផ្ដល់ការវាយតម្លៃ និងមតិកែលម្អលើកម្មវិធីនេះ`;
                imgEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=${encodeURIComponent(FEEDBACK_URL)}`;
                urlEl.innerText = FEEDBACK_URL;
                btnFb.className = 'btn btn-sm btn-info fw-bold active';
                btnQa.className = 'btn btn-sm btn-outline-warning';
            } else {
                titleEl.innerHTML = `<i class="bi bi-qr-code-scan me-2"></i>ស្កេនដើម្បីសួរសំណួរ`;
                subEl.innerText = `បើកកាមេរ៉ាទូរស័ព្ទដៃ ដើម្បីចូលរួមសួរផ្ទាល់ និងគាំទ្រសំណួរ`;
                imgEl.src = `https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=${encodeURIComponent(QA_URL)}`;
                urlEl.innerText = QA_URL;
                btnQa.className = 'btn btn-sm btn-warning fw-bold active';
                btnFb.className = 'btn btn-sm btn-outline-info';
            }
        }

        function copyStageUrl() {
            const text = document.getElementById('qrUrlDisplay').innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.getElementById('btnCopyStageUrl');
                const orig = btn.innerHTML;
                btn.innerHTML = `<i class="bi bi-check2"></i> Copied!`;
                btn.className = 'btn btn-sm btn-success rounded-pill py-1 px-3 fw-bold';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.className = 'btn btn-sm btn-outline-info rounded-pill py-1 px-3 fw-bold';
                }, 2000);
            }).catch(e => {
                prompt("ចម្លងតំណភ្ជាប់:", text);
            });
        }

        let stageRemainingSeconds = null;
        let stageQaStatus = 'open';

        function updateStageTimerWidget(qaControl) {
            if (!qaControl) return;
            stageQaStatus = qaControl.status || 'open';
            stageRemainingSeconds = qaControl.remaining_seconds !== undefined ? qaControl.remaining_seconds : null;
            renderStageTimerUI();
        }

        function renderStageTimerUI() {
            const box = document.getElementById('stageCountdownBox');
            const content = document.getElementById('stageTimerContent');
            if (!box || !content) return;

            if (stageQaStatus === 'closed') {
                box.style.display = 'block';
                box.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                box.style.background = 'rgba(239, 68, 68, 0.12)';
                content.innerHTML = `<div class="fs-2 fw-bold text-danger py-1" style="letter-spacing: 1px;">បិទបញ្ចប់</div>`;
                return;
            }

            if (stageRemainingSeconds !== null && stageRemainingSeconds >= 0) {
                const mins = Math.floor(stageRemainingSeconds / 60);
                const secs = stageRemainingSeconds % 60;
                const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

                box.style.display = 'block';
                if (stageRemainingSeconds <= 120 && stageRemainingSeconds > 0) {
                    box.style.borderColor = 'rgba(239, 68, 68, 0.55)';
                    box.style.background = 'rgba(15, 23, 42, 0.85)';
                    content.innerHTML = `<div class="fs-1 font-monospace fw-bold my-0 timer-pulse-danger" style="letter-spacing: 2px;">${formatted}</div>`;
                } else if (stageRemainingSeconds === 0) {
                    box.style.borderColor = 'rgba(239, 68, 68, 0.45)';
                    box.style.background = 'rgba(239, 68, 68, 0.12)';
                    content.innerHTML = `<div class="fs-2 fw-bold text-danger py-1" style="letter-spacing: 1px;">បិទបញ្ចប់</div>`;
                } else {
                    box.style.borderColor = 'rgba(245, 158, 11, 0.4)';
                    box.style.background = 'rgba(15, 23, 42, 0.85)';
                    content.innerHTML = `<div class="fs-1 font-monospace fw-bold text-warning my-0 timer-digits" style="letter-spacing: 2px; text-shadow: 0 0 16px rgba(245, 158, 11, 0.3);">${formatted}</div>`;
                }
            } else {
                box.style.display = 'none';
                content.innerHTML = '';
            }
        }

        // Local 1-second countdown tick
        setInterval(() => {
            if (stageRemainingSeconds !== null && stageRemainingSeconds > 0 && stageQaStatus === 'open') {
                stageRemainingSeconds--;
                renderStageTimerUI();
            }
        }, 1000);

        function fetchStageQuestions() {
            fetch(`${APP_URL}/api/workshop/${WORKSHOP_ID}/questions`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const questions = res.active || [];
                        document.getElementById('stageQuestionCount').innerText = questions.length;
                        renderStageFeed(questions);
                        if (res.qa_control) {
                            updateStageTimerWidget(res.qa_control);
                        }
                    }
                })
                .catch(e => console.error("Stage poll error", e));
        }

        function renderStageFeed(list) {
            const feed = document.getElementById('stageFeed');
            if (list.length === 0) {
                feed.innerHTML = `
                    <div class="text-center py-5 text-muted my-5">
                        <i class="bi bi-chat-left-dots fs-1 mb-3 opacity-50"></i>
                        <h4 class="text-light">មិនទាន់មានសំណួរនៅឡើយទេ</h4>
                        <p class="text-white-50 fs-5">សូមស្កេន QR Code នៅផ្នែកខាងឆ្វេង ដើម្បីសួរសំណួរទៅកាន់វាគ្មិន!</p>
                    </div>
                `;
                return;
            }

            let html = '';
            list.forEach(q => {
                const isPinned = q.is_pinned == 1;
                const author = q.is_anonymous == 1 ? 'មិនបញ្ចេញឈ្មោះ' : (q.display_name || 'អ្នកចូលរួម');

                html += `
                    <div class="stage-q-card ${isPinned ? 'pinned' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-info fs-5"><i class="bi bi-person-fill me-1"></i>${escapeHtml(author)}</span>
                                ${isPinned ? '<span class="badge bg-warning text-dark fs-6 fw-bold"><i class="bi bi-pin-angle-fill me-1"></i>សំណួរអាទិភាព (PINNED)</span>' : ''}
                                ${q.category && q.category !== 'General' ? `<span class="badge bg-dark border border-secondary text-secondary">${escapeHtml(q.category)}</span>` : ''}
                            </div>
                            <div>
                                <span class="badge bg-secondary fs-5 px-3 py-1">
                                    <i class="bi bi-hand-thumbs-up-fill text-primary me-1"></i>${q.upvotes || 0}
                                </span>
                            </div>
                        </div>
                        <div class="fs-4 text-light fw-medium" style="white-space: pre-line; line-height: 1.5;">${escapeHtml(q.question_text)}</div>
                    </div>
                `;
            });

            feed.innerHTML = html;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
        }

        // Initial Load and poll every 4 seconds
        fetchStageQuestions();
        setInterval(fetchStageQuestions, 4000);
    </script>
</body>
</html>
