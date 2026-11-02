<!DOCTYPE html>
<html lang="km" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
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
            background-color: #f1f5f9;
            color: #0f172a;
            padding-bottom: 90px;
            margin: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* Modern App Header */
        .app-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.25);
        }
        .header-live-pill {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
        }
        .pulse-live {
            width: 7px;
            height: 7px;
            background-color: #ef4444;
            border-radius: 50%;
            margin-right: 5px;
            animation: pulse-ring 1.6s infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.3); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Modern Segmented Navigation Tabs */
        .segmented-nav {
            background: #e2e8f0;
            border-radius: 14px;
            padding: 4px;
            display: flex;
            margin: 14px 0 16px;
        }
        .segmented-nav .nav-item {
            flex: 1;
        }
        .segmented-nav .nav-link {
            width: 100%;
            border-radius: 11px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #64748b;
            padding: 8px 10px;
            border: none;
            background: transparent;
            text-align: center;
            transition: all 0.2s ease;
        }
        .segmented-nav .nav-link.active {
            background: #ffffff;
            color: #1e3a8a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        /* Quick Ask Prompt Bar */
        .ask-prompt-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 14px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .ask-prompt-card:active {
            transform: scale(0.99);
        }
        .ask-avatar-icon {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* Expanded Ask Form Card */
        .ask-form-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #cbd5e1;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 16px;
            animation: slideDown 0.25s ease-out;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-anon-toggle {
            border-radius: 10px;
            font-size: 0.82rem;
            padding: 6px 12px;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .btn-anon-toggle.active {
            background-color: #334155;
            color: #fff;
            border-color: #334155;
        }

        .btn-submit-glow {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #fff;
            border-radius: 12px;
            font-weight: 700;
            padding: 10px;
            box-shadow: 0 3px 12px rgba(37, 99, 235, 0.35);
        }
        .btn-submit-glow:active {
            transform: scale(0.98);
        }

        /* Filter Pills */
        .filter-pill-btn {
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 5px 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #64748b;
        }
        .filter-pill-btn.active {
            background: #1e3a8a;
            color: #ffffff;
            border-color: #1e3a8a;
        }

        /* Question Cards in Feed */
        .feed-q-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 14px 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .feed-q-card.pinned {
            border: 1.5px solid #f59e0b;
            background: #fffdf7;
            box-shadow: 0 2px 10px rgba(245, 158, 11, 0.12);
        }
        .feed-q-card.answered {
            background: #f8fafc;
            border-left: 4px solid #10b981;
            opacity: 0.88;
        }

        /* Upvote Button Pill */
        .upvote-pill {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s ease;
        }
        .upvote-pill:active {
            transform: scale(0.95);
        }
        .upvote-pill.voted {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        /* Floating Action Button (FAB) */
        .fab-ask {
            position: fixed;
            bottom: 24px;
            right: 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(37, 99, 235, 0.45);
            z-index: 1030;
            transition: all 0.2s ease;
        }
        .fab-ask:active {
            transform: scale(0.96);
        }
    </style>
</head>
<body>

    <!-- Ultra-Clean Mobile Header -->
    <header class="app-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                <span class="header-live-pill">
                    <span class="pulse-live"></span>LIVE
                </span>
                <span class="fw-bold text-white text-truncate fs-6"><?= htmlspecialchars($workshop['name']) ?></span>
            </div>
            <div class="flex-shrink-0 d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark fw-bold d-none" id="mobileHeaderTimerBadge" style="font-size: 0.78rem;">
                    <i class="bi bi-stopwatch me-1"></i><span id="mobileTimerDigits">--:--</span>
                </span>
                <span class="text-white-50 small"><i class="bi bi-clock me-1"></i><span id="liveClock"></span></span>
            </div>
        </div>
        <div class="text-white-50 small text-truncate mt-1" style="font-size: 0.78rem;">
            <?= htmlspecialchars($workshop['business_name']) ?>
            <?php if (!empty($workshop['trainer_name'])): ?>
                &bull; វាគ្មិន: <span class="text-info"><?= htmlspecialchars($workshop['trainer_name']) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <div class="container px-3" style="max-width: 580px;">
        
        <!-- Segmented Tab Navigation -->
        <div class="segmented-nav" id="mainTab" role="tablist">
            <div class="nav-item">
                <button class="nav-link active" id="qa-tab" data-bs-toggle="pill" data-bs-target="#qa-pane" type="button" role="tab">
                    <i class="bi bi-chat-quote-fill me-1"></i> សួរ-ឆ្លើយ (<span id="tabQaCount">0</span>)
                </button>
            </div>
            <div class="nav-item">
                <button class="nav-link" id="request-tab" data-bs-toggle="pill" data-bs-target="#request-pane" type="button" role="tab">
                    <i class="bi bi-life-preserver me-1"></i> ស្នើសុំជំនួយ
                </button>
            </div>
        </div>

        <!-- Tab Panes -->
        <div class="tab-content">
            
            <!-- TAB 1: Live Q&A -->
            <div class="tab-pane fade show active" id="qa-pane" role="tabpanel">

                <!-- QA Closed Notice Banner -->
                <div class="alert alert-dark border border-secondary rounded-4 shadow-sm mb-3 text-center d-none" id="qaClosedAlert" style="background: rgba(30, 41, 59, 0.9);">
                    <div class="text-warning fw-bold mb-1 fs-6">
                        <i class="bi bi-lock-fill me-1"></i> វេទិកាសួរសំណួរត្រូវបានបិទបញ្ចប់
                    </div>
                    <div class="small text-white-50">
                        អ្នករៀបចំបានបិទការទទួលសំណួរថ្មី។ លោកអ្នកនៅតែអាចទស្សនាសំណួរ និងចម្លើយខាងក្រោមបាន។
                    </div>
                </div>

                <!-- Quick Ask Trigger Bar (Compact State) -->
                <div class="ask-prompt-card mb-3" id="askPromptBar" onclick="expandAskForm()">
                    <div class="d-flex align-items-center gap-2">
                        <div class="ask-avatar-icon">
                            <i class="bi bi-patch-question-fill"></i>
                        </div>
                        <div class="flex-grow-1 text-muted small ps-1">
                            សរសេរសំណួរទៅកាន់វាគ្មិន...
                        </div>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-pencil-fill me-1"></i>សួរ
                        </button>
                    </div>
                </div>

                <!-- Expanded Ask Form Card (Collapsible) -->
                <div class="ask-form-card mb-3 d-none" id="askFormCard">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-chat-dots-fill text-primary me-1"></i>សួរសំណួរថ្មី
                        </h6>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0 text-decoration-none" onclick="collapseAskForm()">
                            <i class="bi bi-x-lg me-1"></i>បង្រួម
                        </button>
                    </div>

                    <form id="askForm" onsubmit="submitQuestion(event)">
                        <!-- Author Name & Anonymous Pill -->
                        <div class="mb-2">
                            <div class="d-flex gap-2 align-items-center">
                                <div class="input-group input-group-sm flex-grow-1">
                                    <span class="input-group-text bg-light"><i class="bi bi-person-fill text-secondary"></i></span>
                                    <input type="text" id="author_name" class="form-control" 
                                           placeholder="ឈ្មោះរបស់អ្នក" 
                                           value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-anon-toggle flex-shrink-0" 
                                        id="btnAnonToggle" onclick="toggleAnonymous()">
                                    <i class="bi bi-incognito me-1"></i>មិនបញ្ចេញឈ្មោះ
                                </button>
                                <input type="checkbox" id="is_anonymous" class="d-none">
                            </div>
                        </div>

                        <!-- Optional Category / Session -->
                        <?php if (!empty($sessions)): ?>
                        <div class="mb-2">
                            <select id="category" class="form-select form-select-sm">
                                <option value="General">-- ជ្រើសរើសប្រធានបទ/វគ្គ (ស្រេចចិត្ត) --</option>
                                <?php foreach ($sessions as $s): ?>
                                <option value="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <!-- Question Textarea -->
                        <div class="mb-2">
                            <textarea id="question_text" class="form-control" rows="3" 
                                      placeholder="សូមសរសេរសំណួររបស់អ្នកនៅទីនេះ..." required maxlength="1000"
                                      style="border-radius: 12px; font-size: 0.92rem;"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="btnSubmitQuestion" class="btn btn-submit-glow w-100">
                            <i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណួរ
                        </button>
                    </form>
                </div>

                <!-- Sub-filter Pills (Active vs Answered) -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="filter-pill-btn active" id="btnFilterActive" onclick="filterQuestions('active')">
                            កំពុងសួរ (<span id="countActive">0</span>)
                        </button>
                        <button type="button" class="filter-pill-btn" id="btnFilterAnswered" onclick="filterQuestions('answered')">
                            ឆ្លើយរួច ✓ (<span id="countAnswered">0</span>)
                        </button>
                    </div>
                    <button class="btn btn-sm btn-link text-decoration-none text-muted p-0" onclick="loadQuestions(true)">
                        <i class="bi bi-arrow-repeat me-1"></i>Refresh
                    </button>
                </div>

                <!-- Questions Feed Container -->
                <div id="questionsContainer">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div class="small">កំពុងទាញយកទិន្នន័យសំណួរ...</div>
                    </div>
                </div>

            </div>

            <!-- TAB 2: Requests / Help -->
            <div class="tab-pane fade" id="request-pane" role="tabpanel">
                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-4 mb-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning bg-opacity-20 text-warning rounded-circle p-2 me-2">
                            <i class="bi bi-life-preserver fs-5 text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">ស្នើសុំជំនួយពីក្រុមការងារក្នុងសាល</h6>
                            <small class="text-muted">ក្រុមការងារនឹងមកសម្របសម្រួលដល់ទីតាំងអ្នកផ្ទាល់</small>
                        </div>
                    </div>

                    <form id="requestForm" onsubmit="submitRequest(event)">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">ឈ្មោះរបស់អ្នក</label>
                                <input type="text" id="req_name" class="form-control form-control-sm" 
                                       placeholder="ឈ្មោះ" 
                                       value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">លេខកៅអី / តុ</label>
                                <input type="text" id="req_seat" class="form-control form-control-sm" 
                                       placeholder="ឧ. តុ A-2, កៅអី 15" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-7">
                                <label class="form-label small fw-bold mb-1">ប្រភេទជំនួយ</label>
                                <select id="req_type" class="form-select form-select-sm" required>
                                    <option value="បច្ចេកទេស/ឧបករណ៍">💻 បញ្ហាបច្ចេកទេស</option>
                                    <option value="សំឡេង/មីក្រូហ្វូន">🎤 សំឡេង/មីក្រូហ្វូន</option>
                                    <option value="ស្លាយ/ឯកសារ">📄 ស្លាយ/ឯកសារ</option>
                                    <option value="ម៉ាស៊ីនត្រជាក់/ទីតាំង">❄️ ម៉ាស៊ីនត្រជាក់</option>
                                    <option value="ផ្សេងៗ">❓ ជំនួយផ្សេងៗ</option>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="form-label small fw-bold mb-1">កម្រិតបន្ទាន់</label>
                                <select id="req_priority" class="form-select form-select-sm">
                                    <option value="normal">ធម្មតា</option>
                                    <option value="high">បន្ទាន់</option>
                                    <option value="urgent">បន្ទាន់ខ្លាំង</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold mb-1">ការពិពណ៌នាលម្អិត</label>
                            <textarea id="req_desc" class="form-control form-control-sm" rows="3" 
                                      placeholder="សូមបញ្ជាក់ព័ត៌មានបន្ថែម..." required style="border-radius: 10px;"></textarea>
                        </div>

                        <button type="submit" id="btnSubmitRequest" class="btn btn-warning w-100 fw-bold py-2 rounded-3 text-dark shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណើជំនួយ
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Floating Action Button (FAB) for Asking -->
    <button type="button" class="fab-ask" id="fabAskButton" onclick="expandAskForm()">
        <i class="bi bi-chat-quote-fill"></i>
        <span>សួរសំណួរ</span>
    </button>

    <!-- Alert Toast Container -->
    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
        <div id="actionToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">បានរក្សាទុក!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const WORKSHOP_ID = <?= (int)$workshop['id'] ?>;
        const APP_URL = "<?= APP_URL ?>";
        let activeQuestions = [];
        let answeredQuestions = [];
        let myPendingQuestions = [];
        let currentFilter = 'active'; // 'active' or 'answered'
        let isAnonymousState = false;
        let votedQuestionIds = new Set(JSON.parse(localStorage.getItem('voted_q_' + WORKSHOP_ID) || '[]'));
        let mySubmittedQuestionIds = new Set(JSON.parse(localStorage.getItem('my_questions_' + WORKSHOP_ID) || '[]'));

        function showToast(msg, bg = 'bg-dark') {
            const toastEl = document.getElementById('actionToast');
            toastEl.className = `toast align-items-center text-white ${bg} border-0 shadow-lg`;
            document.getElementById('toastMessage').innerText = msg;
            const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
        }

        function expandAskForm() {
            document.getElementById('askPromptBar').classList.add('d-none');
            document.getElementById('askFormCard').classList.remove('d-none');
            document.getElementById('fabAskButton').classList.add('d-none');
            document.getElementById('question_text').focus();
        }

        function collapseAskForm() {
            document.getElementById('askFormCard').classList.add('d-none');
            document.getElementById('askPromptBar').classList.remove('d-none');
            document.getElementById('fabAskButton').classList.remove('d-none');
        }

        function toggleAnonymous() {
            isAnonymousState = !isAnonymousState;
            const btn = document.getElementById('btnAnonToggle');
            const nameInput = document.getElementById('author_name');
            const chk = document.getElementById('is_anonymous');
            
            chk.checked = isAnonymousState;
            if (isAnonymousState) {
                btn.classList.add('active');
                btn.innerHTML = `<i class="bi bi-check2 me-1"></i>អនាមិក`;
                nameInput.dataset.previous = nameInput.value;
                nameInput.value = 'មិនបញ្ចេញឈ្មោះ';
                nameInput.disabled = true;
            } else {
                btn.classList.remove('active');
                btn.innerHTML = `<i class="bi bi-incognito me-1"></i>មិនបញ្ចេញឈ្មោះ`;
                nameInput.disabled = false;
                nameInput.value = nameInput.dataset.previous || '';
            }
        }

        function filterQuestions(type) {
            currentFilter = type;
            document.getElementById('btnFilterActive').classList.toggle('active', type === 'active');
            document.getElementById('btnFilterAnswered').classList.toggle('active', type === 'answered');
            renderQuestions();
        }

        let mobileQaStatus = 'open';
        let mobileRemainingSeconds = null;

        function updateMobileTimerUI(qaControl) {
            if (!qaControl) return;
            mobileQaStatus = qaControl.status || 'open';
            mobileRemainingSeconds = qaControl.remaining_seconds !== undefined ? qaControl.remaining_seconds : null;
            renderMobileTimerUI();
        }

        function renderMobileTimerUI() {
            const closedAlert = document.getElementById('qaClosedAlert');
            const askPrompt = document.getElementById('askPromptBar');
            const askForm = document.getElementById('askFormCard');
            const fab = document.getElementById('fabAskButton');
            const timerBadge = document.getElementById('mobileHeaderTimerBadge');
            const timerClock = document.getElementById('mobileTimerDigits');

            if (mobileQaStatus === 'closed') {
                if (closedAlert) closedAlert.classList.remove('d-none');
                if (askPrompt) askPrompt.classList.add('d-none');
                if (askForm) askForm.classList.add('d-none');
                if (fab) fab.classList.add('d-none');
                if (timerBadge) {
                    timerBadge.classList.remove('d-none');
                    timerBadge.className = 'badge bg-danger text-white fw-bold';
                    timerBadge.innerHTML = '<i class="bi bi-lock-fill me-1"></i>បិទ';
                }
                return;
            }

            // Status is open
            if (closedAlert) closedAlert.classList.add('d-none');
            if (askPrompt && askForm && askForm.classList.contains('d-none')) {
                askPrompt.classList.remove('d-none');
            }
            if (fab && askForm && askForm.classList.contains('d-none')) {
                fab.classList.remove('d-none');
            }

            if (mobileRemainingSeconds !== null && mobileRemainingSeconds >= 0) {
                const mins = Math.floor(mobileRemainingSeconds / 60);
                const secs = mobileRemainingSeconds % 60;
                const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                
                if (timerBadge) {
                    timerBadge.classList.remove('d-none');
                    if (mobileRemainingSeconds <= 120 && mobileRemainingSeconds > 0) {
                        timerBadge.className = 'badge bg-danger text-white fw-bold';
                    } else {
                        timerBadge.className = 'badge bg-warning text-dark fw-bold';
                    }
                    if (timerClock) timerClock.innerText = formatted;
                }
            } else {
                if (timerBadge) timerBadge.classList.add('d-none');
            }
        }

        setInterval(() => {
            if (mobileRemainingSeconds !== null && mobileRemainingSeconds > 0 && mobileQaStatus === 'open') {
                mobileRemainingSeconds--;
                renderMobileTimerUI();
            }
        }, 1000);

        function loadQuestions(showFeedback = false) {
            const myIdsParam = [...mySubmittedQuestionIds].join(',');
            const url = `${APP_URL}/api/workshop/${WORKSHOP_ID}/questions` + (myIdsParam ? `?my_ids=${myIdsParam}` : '');

            fetch(url)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        activeQuestions = res.active || [];
                        answeredQuestions = res.answered || [];
                        myPendingQuestions = res.my_pending || [];
                        
                        document.getElementById('countActive').innerText = activeQuestions.length;
                        document.getElementById('countAnswered').innerText = answeredQuestions.length;
                        document.getElementById('tabQaCount').innerText = activeQuestions.length;

                        if (res.qa_control) {
                            updateMobileTimerUI(res.qa_control);
                        }

                        renderQuestions();
                        if (showFeedback) showToast('បានធ្វើបច្ចុប្បន្នភាពសំណួរ!', 'bg-success');
                    }
                })
                .catch(err => console.error(err));
        }

        function renderQuestions() {
            const container = document.getElementById('questionsContainer');
            const list = currentFilter === 'active' ? activeQuestions : answeredQuestions;

            if (list.length === 0 && (currentFilter !== 'active' || myPendingQuestions.length === 0)) {
                container.innerHTML = `
                    <div class="card border-0 shadow-sm p-4 text-center rounded-4 text-muted my-2" style="background: #fff;">
                        <i class="bi bi-chat-left-dots fs-1 mb-2 text-secondary opacity-50"></i>
                        <h6 class="fw-bold">${currentFilter === 'active' ? 'មិនទាន់មានសំណួរនៅឡើយទេ' : 'មិនទាន់មានសំណួរដែលបានឆ្លើយនៅឡើយទេ'}</h6>
                        <small class="text-muted">${currentFilter === 'active' ? 'ចុចប៊ូតុង "សួរសំណួរ" ខាងលើ ដើម្បីសួរវាគ្មិនដំបូងគេ!' : ''}</small>
                    </div>
                `;
                return;
            }

            let html = '';

            // Render current user's pending questions on top of active feed
            if (currentFilter === 'active' && myPendingQuestions.length > 0) {
                myPendingQuestions.forEach(pq => {
                    html += `
                        <div class="feed-q-card mb-3 shadow-sm" style="background: #fffdf5; border: 1.5px dashed #f59e0b;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.74rem;">
                                    <i class="bi bi-hourglass-split me-1"></i>សំណួររបស់អ្នក (រង់ចាំការត្រួតពិនិត្យ)
                                </span>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>${formatTime(pq.created_at)}</small>
                            </div>
                            <div class="text-dark mb-2 fw-medium" style="white-space: pre-line; font-size: 0.95rem;">
                                ${escapeHtml(pq.question_text)}
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-shield-check me-1 text-warning"></i>សំណួររបស់អ្នកនឹងបង្ហាញជាសាធារណៈក្រោយពេលអ្នកសម្របសម្រួលអនុញ្ញាត។
                            </div>
                        </div>
                    `;
                });
            }

            list.forEach(q => {
                const isPinned = q.is_pinned == 1;
                const isAnswered = q.status === 'answered';
                const hasVoted = votedQuestionIds.has(parseInt(q.id));
                const author = q.is_anonymous == 1 ? 'មិនបញ្ចេញឈ្មោះ' : (q.display_name || 'អ្នកចូលរួម');

                html += `
                    <div class="feed-q-card ${isPinned ? 'pinned' : ''} ${isAnswered ? 'answered' : ''}" id="q-card-${q.id}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                    <i class="bi bi-person-circle text-primary me-1"></i>${escapeHtml(author)}
                                </span>
                                ${isPinned ? '<span class="badge bg-warning text-dark fw-bold" style="font-size: 0.72rem;"><i class="bi bi-pin-angle-fill me-1"></i>សំណួរអាទិភាព</span>' : ''}
                                ${isAnswered ? '<span class="badge bg-success" style="font-size: 0.72rem;"><i class="bi bi-check2-circle me-1"></i>ឆ្លើយរួច</span>' : ''}
                                ${q.category && q.category !== 'General' ? `<span class="badge bg-light text-secondary border" style="font-size: 0.72rem;">${escapeHtml(q.category)}</span>` : ''}
                            </div>
                            <div>
                                ${!isAnswered ? `
                                <button type="button" class="upvote-pill ${hasVoted ? 'voted' : ''}" 
                                        onclick="upvoteQuestion(${q.id})" ${hasVoted ? 'disabled' : ''}>
                                    <i class="bi bi-hand-thumbs-up-fill"></i>
                                    <span id="up-count-${q.id}">${q.upvotes || 0}</span>
                                </button>
                                ` : ''}
                            </div>
                        </div>

                        <div class="text-dark mb-2" style="white-space: pre-line; font-size: 0.95rem; line-height: 1.55;">
                            ${escapeHtml(q.question_text)}
                        </div>

                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.78rem;">
                            <span><i class="bi bi-clock me-1"></i>${formatTime(q.created_at)}</span>
                            ${isAnswered && q.answered_at ? `<span class="text-success"><i class="bi bi-check-all me-1"></i>ឆ្លើយនៅម៉ោង ${formatTime(q.answered_at)}</span>` : ''}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
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

        function submitQuestion(e) {
            e.preventDefault();
            const qText = document.getElementById('question_text').value.trim();
            if (!qText) return;

            const isAnon = isAnonymousState ? 1 : 0;
            const author = isAnon ? 'មិនបញ្ចេញឈ្មោះ' : document.getElementById('author_name').value.trim();
            const catEl = document.getElementById('category');
            const category = catEl ? catEl.value : 'General';

            const btn = document.getElementById('btnSubmitQuestion');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបញ្ជូន...`;

            const formData = new FormData();
            formData.append('workshop_id', WORKSHOP_ID);
            formData.append('question', qText);
            formData.append('author_name', author);
            formData.append('is_anonymous', isAnon);
            formData.append('category', category);

            fetch(`${APP_URL}/api/questions/submit`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណួរ`;
                if (res.success) {
                    if (res.question_id) {
                        mySubmittedQuestionIds.add(parseInt(res.question_id));
                        localStorage.setItem('my_questions_' + WORKSHOP_ID, JSON.stringify([...mySubmittedQuestionIds]));
                    }
                    document.getElementById('question_text').value = '';
                    collapseAskForm();
                    showToast(res.message || 'សំណួររបស់អ្នកត្រូវបានបញ្ជូនរួចរាល់ និងកំពុងរង់ចាំការត្រួតពិនិត្យ!', 'bg-success');
                    loadQuestions();
                } else {
                    showToast(res.message || 'បរាជ័យក្នុងការបញ្ជូន', 'bg-danger');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណួរ`;
                showToast('មានបញ្ហាបណ្តាញ អ៊ីនធឺណិត', 'bg-danger');
            });
        }

        function upvoteQuestion(qId) {
            if (votedQuestionIds.has(qId)) return;

            fetch(`${APP_URL}/api/questions/${qId}/upvote`, {
                method: 'POST'
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    votedQuestionIds.add(qId);
                    localStorage.setItem('voted_q_' + WORKSHOP_ID, JSON.stringify([...votedQuestionIds]));
                    const countEl = document.getElementById(`up-count-${qId}`);
                    if (countEl) countEl.innerText = res.upvotes;
                    showToast('បានគាំទ្រសំណួរនេះ!', 'bg-primary');
                    loadQuestions();
                } else {
                    showToast(res.message, 'bg-warning');
                }
            });
        }

        function submitRequest(e) {
            e.preventDefault();
            const desc = document.getElementById('req_desc').value.trim();
            if (!desc) return;

            const name = document.getElementById('req_name').value.trim();
            const seat = document.getElementById('req_seat').value.trim();
            const type = document.getElementById('req_type').value;
            const priority = document.getElementById('req_priority').value;

            const btn = document.getElementById('btnSubmitRequest');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបញ្ជូន...`;

            const formData = new FormData();
            formData.append('workshop_id', WORKSHOP_ID);
            formData.append('requester_name', name);
            formData.append('seat_number', seat);
            formData.append('request_type', type);
            formData.append('priority', priority);
            formData.append('description', `[ទីតាំង: ${seat}] ${desc}`);

            fetch(`${APP_URL}/api/requests/submit`, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណើជំនួយ`;
                if (res.success) {
                    document.getElementById('req_desc').value = '';
                    showToast('សំណើជំនួយត្រូវបានបញ្ជូនទៅក្រុមការងាររួចរាល់!', 'bg-success');
                } else {
                    showToast(res.message || 'បរាជ័យក្នុងការបញ្ជូន', 'bg-danger');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-send-fill me-1"></i> បញ្ជូនសំណើជំនួយ`;
                showToast('មានបញ្ហាបណ្តាញ អ៊ីនធឺណិត', 'bg-danger');
            });
        }

        // Live Clock
        setInterval(() => {
            const now = new Date();
            document.getElementById('liveClock').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }, 1000);
        document.getElementById('liveClock').innerText = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Initial Load and Auto-Refresh polling every 4 seconds
        loadQuestions();
        setInterval(() => {
            loadQuestions();
        }, 4000);
    </script>
</body>
</html>
