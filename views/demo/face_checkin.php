<?php
// views/demo/face_checkin.php
// Real AI Face Check-in & Enrollment System
$db = Database::getInstance();
$allWorkshops = $db->query("SELECT id, name FROM workshops WHERE deleted_at IS NULL ORDER BY id DESC");
$defaultWsId = !empty($allWorkshops) ? (int)$allWorkshops[0]['id'] : 0;
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Face Recognition Check-in System - Real Mode</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Inter & Kantumruy Pro for Khmer -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- face-api.js (Vladmandic optimized build) -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #10b981;
            --success-dark: #059669;
            --dark-bg: #0f172a;
        }
        body {
            font-family: 'Kantumruy Pro', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        /* Top Nav */
        .demo-nav {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 4px 20px -2px rgba(0,0,0,0.15);
        }

        /* Camera Frame & Kiosk Elements */
        .camera-viewport {
            position: relative;
            width: 100%;
            max-width: 540px;
            margin: 0 auto;
            border-radius: 24px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 12px 30px -5px rgba(15, 23, 42, 0.25);
            aspect-ratio: 4 / 3;
        }
        video.camera-stream {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        canvas.overlay-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
        }

        /* Oval Target Face Guide for Registration */
        .face-oval-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 210px;
            height: 270px;
            border: 3px dashed rgba(255, 255, 255, 0.55);
            border-radius: 50%;
            pointer-events: none;
            transition: all 0.25s ease;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
        }
        .face-oval-guide.aligned {
            border-color: #10b981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.3), 0 0 25px rgba(16, 185, 129, 0.5);
        }

        /* Scanning Laser Beam in Kiosk Mode */
        .scan-laser-line {
            position: absolute;
            left: 5%;
            width: 90%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #2563eb, transparent);
            box-shadow: 0 0 15px #38bdf8;
            top: 15%;
            animation: scanAnim 2.4s ease-in-out infinite alternate;
            pointer-events: none;
            border-radius: 50%;
        }
        @keyframes scanAnim {
            0% { top: 12%; opacity: 0.2; }
            50% { opacity: 1; }
            100% { top: 88%; opacity: 0.2; }
        }

        /* Kiosk Mode Banner Overlay */
        .kiosk-badge-float {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        .kiosk-status-pill {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            color: #fff;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.82rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        /* Welcome Modal / Toast for Checkin Success */
        .kiosk-success-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(16, 185, 129, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            z-index: 20;
            padding: 24px;
            text-align: center;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .tab-btn.active {
            background: #2563eb !important;
            color: #fff !important;
            border-color: #2563eb !important;
        }

        .avatar-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- Top Header -->
    <header class="demo-nav text-white py-3 px-3 px-md-4">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-2.5 py-1.5 rounded-3">
                    <i class="bi bi-person-bounding-box fs-5"></i>
                </span>
                <div>
                    <h5 class="fw-bold mb-0">AI Face Recognition Check-in System (Real System)</h5>
                    <small class="text-white-50">ភ្ជាប់ទៅកាន់ Database និងកត់ត្រាវត្តមានផ្ទាល់ &bull; Workshop OS</small>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Select Workshop Dropdown -->
                <div class="d-flex align-items-center gap-1 bg-dark bg-opacity-50 px-2 py-1 rounded-pill border border-secondary border-opacity-50">
                    <span class="small text-white-50 me-1"><i class="bi bi-calendar-event me-1"></i> សិក្ខាសាលា:</span>
                    <select id="global-workshop-select" class="form-select form-select-sm bg-transparent text-white border-0 py-0 ps-1 pe-3" style="font-size: 0.82rem; cursor: pointer;" onchange="onWorkshopChange()">
                        <?php foreach ($allWorkshops as $ws): ?>
                            <option value="<?= $ws['id'] ?>" class="text-dark" <?= ($ws['id'] == $defaultWsId) ? 'selected' : '' ?>>
                                #<?= $ws['id'] ?> - <?= htmlspecialchars($ws['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <span id="model-status-badge" class="badge bg-warning text-dark px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> កំពុងទាញយក AI Models...
                </span>

                <a href="<?= defined('APP_URL') ? APP_URL : '/' ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-house-door me-1"></i> ទំព័រដើម
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container-fluid my-4 px-3 px-lg-4">
        
        <!-- Tab Navigation Switcher -->
        <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
            <div class="nav nav-pills nav-fill gap-2" id="demoTabs" role="tablist">
                <button class="nav-link tab-btn active rounded-3 fw-bold py-2.5" id="tab-enroll" onclick="switchTab('enroll')">
                    <i class="bi bi-camera me-1"></i> ១. ចុះឈ្មោះផ្ទៃមុខ (Enroll to Database)
                </button>
                <button class="nav-link tab-btn rounded-3 fw-bold py-2.5" id="tab-kiosk" onclick="switchTab('kiosk')">
                    <i class="bi bi-display me-1"></i> ២. តុស្កេន Kiosk (Real Database Check-in)
                </button>
                <button class="nav-link tab-btn rounded-3 fw-bold py-2.5" id="tab-database" onclick="switchTab('database')">
                    <i class="bi bi-database me-1"></i> ៣. បញ្ជីមុខដែលបានចុះឈ្មោះក្នុង DB (<span id="attendee-count-badge">0</span>)
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 1: PRE-REGISTRATION / ENROLLMENT                           -->
        <!-- ============================================================== -->
        <div id="view-enroll" class="demo-view">
            <div class="row g-4 align-items-start">
                
                <!-- Left: Form inputs -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                                <i class="bi bi-person-plus-fill fs-5"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0">ទម្រង់ចុះឈ្មោះស្កេនផ្ទៃមុខ</h5>
                                <small class="text-muted">រក្សាទុក 128-d Vector ចូល MySQL ផ្ទាល់</small>
                            </div>
                        </div>

                        <form id="enrollForm" onsubmit="event.preventDefault();">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">លេខកូដសំបុត្រ (Registration Code) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted font-monospace"><i class="bi bi-ticket-perforated"></i></span>
                                    <input type="text" id="reg-code" class="form-control font-monospace fw-bold fs-6" placeholder="ឧ. REG92FE1637" value="REG92FE1637" required>
                                </div>
                                <div class="form-text small text-muted">វាយកូដសំបុត្ររបស់សិក្ខាកាមដើម្បីភ្ជាប់ជាមួយគណនីក្នុងប្រព័ន្ធ។</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">ឈ្មោះសិក្ខាកាម (Optional)</label>
                                <input type="text" id="reg-name" class="form-control" placeholder="ឧ. Chan Kosol" value="Chan Kosol">
                            </div>

                            <div class="alert alert-success py-2 px-3 small rounded-3 d-flex align-items-center mb-3">
                                <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                <div>
                                    <strong>Real Database Mode៖</strong> រូបភាព និង Vector នឹងត្រូវរក្សាទុកក្នុងតារាង `participant_faces` ពិតប្រាកដ។
                                </div>
                            </div>

                            <button type="button" id="btn-enroll-capture" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-xs" onclick="captureAndEnrollFace()" disabled>
                                <i class="bi bi-camera-fill me-1"></i> ថតស្កេន & រក្សាទុកផ្ទៃមុខ (Save to Database)
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Camera Viewport with Oval Guide -->
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-webcam text-primary me-1"></i> កាមេរ៉ាស្កេនផ្ទៃមុខ
                            </h6>
                            <span id="enroll-guide-status" class="badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill">
                                កំពុងបើកកាមេរ៉ា...
                            </span>
                        </div>

                        <!-- Viewport -->
                        <div class="camera-viewport">
                            <video id="video-enroll" class="camera-stream" autoplay muted playsinline></video>
                            <canvas id="canvas-enroll" class="overlay-canvas"></canvas>
                            <!-- Face Guide Oval -->
                            <div id="face-guide-oval" class="face-oval-guide"></div>
                        </div>

                        <div class="mt-3 text-muted small">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            សូមដាក់ផ្ទៃមុខឱ្យចំកណ្តាលរង្វង់ ពេលរង្វង់ប្រែពណ៌ **បៃតង** ចុចប៊ូតុងខាងឆ្វេងដើម្បីរក្សាទុកចូល Database។
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 2: VENUE ENTRANCE KIOSK (LIVE RECOGNITION & CHECK-IN)      -->
        <!-- ============================================================== -->
        <div id="view-kiosk" class="demo-view d-none">
            <div class="row g-4 align-items-start">
                
                <!-- Camera Kiosk Area -->
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-start">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="bi bi-door-open-fill text-success me-1"></i> តុស្កេន Check-in ដោយផ្ទៃមុខ (Real AI Kiosk)
                                </h5>
                                <small class="text-muted">កត់ត្រាវត្តមានផ្ទាល់ចូលក្នុងតារាង `attendance` នៃ MySQL</small>
                            </div>

                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                                <i class="bi bi-broadcast me-1 text-danger"></i> កំពុងដំណើរការស្កេន
                            </span>
                        </div>

                        <!-- Camera Viewport with Laser Beam -->
                        <div class="camera-viewport position-relative">
                            <video id="video-kiosk" class="camera-stream" autoplay muted playsinline></video>
                            <canvas id="canvas-kiosk" class="overlay-canvas"></canvas>
                            <div class="scan-laser-line"></div>

                            <!-- Floating Pill in Kiosk View -->
                            <div class="kiosk-badge-float">
                                <span class="kiosk-status-pill">
                                    <i class="bi bi-clock me-1 text-primary"></i> <span id="kiosk-clock">--:--:--</span>
                                </span>
                                <span class="kiosk-status-pill">
                                    <i class="bi bi-people-fill me-1 text-success"></i> ស្គាល់ក្នុង DB: <strong id="kiosk-enrolled-count">0</strong>
                                </span>
                            </div>

                            <!-- Success Welcome Overlay Popup -->
                            <div id="kiosk-success-popup" class="kiosk-success-overlay d-none">
                                <div class="bg-white text-success rounded-circle p-3 d-inline-flex mb-3 shadow">
                                    <i class="bi bi-check-lg display-5 fw-bold"></i>
                                </div>
                                <h3 class="fw-bold mb-1">សូមស្វាគមន៍!</h3>
                                <h2 class="fw-extrabold mb-2" id="pop-name">Chan Kosol</h2>
                                <div class="d-flex gap-2 justify-content-center mb-3">
                                    <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill font-monospace fs-6" id="pop-code">REG92FE1637</span>
                                    <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-6" id="pop-ticket">VIP Pass</span>
                                </div>
                                <p class="mb-0 small text-white-50">
                                    <i class="bi bi-calendar-check me-1"></i> វត្តមានត្រូវបានកត់ត្រាចូលក្នុងប្រព័ន្ធ Database ជោគជ័យ
                                </p>
                            </div>

                            <!-- Already Checked In Warning Overlay -->
                            <div id="kiosk-warning-popup" class="kiosk-success-overlay d-none" style="background: rgba(245, 158, 11, 0.95);">
                                <div class="bg-white text-warning rounded-circle p-3 d-inline-flex mb-3 shadow">
                                    <i class="bi bi-exclamation-triangle-fill display-5 fw-bold"></i>
                                </div>
                                <h3 class="fw-bold mb-1">បាន Check-in រួចរាល់ហើយ!</h3>
                                <h4 class="fw-bold mb-2" id="warn-name">Chan Kosol</h4>
                                <p class="mb-0 small" id="warn-desc">
                                    សិក្ខាកាមនេះបានកត់ត្រាវត្តមានរួចហើយនៅមុននេះបន្តិច។
                                </p>
                            </div>
                        </div>

                        <!-- Fallback & Controls -->
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted" id="kiosk-fps-meter">
                                ស្ថានភាព៖ Live AI Inference (WebGL Active)
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="refreshDatabaseAttendees()">
                                <i class="bi bi-arrow-repeat me-1"></i> ទាញទិន្នន័យពី DB ឡើងវិញ (Reload DB)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Live Attendance Stream -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-card-checklist text-primary me-1"></i> បញ្ជីវត្តមានជាក់ស្តែង (Live Stream)
                            </h6>
                            <span class="badge bg-success px-2.5 py-1 rounded-pill" id="live-checkin-count">0 នាក់</span>
                        </div>

                        <div class="list-group list-group-flush overflow-auto pe-1" id="kiosk-attendance-feed" style="max-height: 420px;">
                            <div class="text-center py-5 text-muted" id="empty-attendance-msg">
                                <i class="bi bi-person-x display-6 text-muted mb-2 d-block"></i>
                                មិនទាន់មានវត្តមាន Check-in ថ្មីៗនៅឡើយទេ...
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 3: ATTENDEE DATABASE & DESCRIPTOR INSPECTOR                -->
        <!-- ============================================================== -->
        <div id="view-database" class="demo-view d-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-database-fill-check text-success me-1"></i> បញ្ជីអ្នកចុះឈ្មោះផ្ទៃមុខពិតប្រាកដក្នុង MySQL
                        </h5>
                        <p class="text-muted small mb-0">ទិន្នន័យបានមកពីតារាង `participant_faces` នៃសិក្ខាសាលាដែលបានជ្រើសរើស</p>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="refreshDatabaseAttendees()">
                            <i class="bi bi-arrow-clockwise me-1"></i> ទាញទិន្នន័យថ្មី (Refresh DB)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="small text-muted">
                                <th>រូបថត</th>
                                <th>ឈ្មោះសិក្ខាកាម</th>
                                <th>កូដសំបុត្រ</th>
                                <th>ប្រភេទសំបុត្រ</th>
                                <th>128-d Vector Descriptor (Sample)</th>
                                <th>ស្ថានភាពវត្តមានក្នុង DB</th>
                            </tr>
                        </thead>
                        <tbody id="attendee-table-body">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Main Logic Script -->
    <script>
        const APP_URL = <?= json_encode(APP_URL) ?>;
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
        
        let selectedWorkshopId = <?= $defaultWsId ?>;
        let isModelLoaded = false;
        let localStream = null;
        let activeTab = 'enroll';
        let isProcessingScan = false;
        let lastDetectedCode = null;
        let lastDetectedTime = 0;

        let dbAttendees = [];
        let faceMatcher = null;

        // Initialize Web Audio API for Chimes
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function playChime(isSuccess = true) {
            try {
                const now = audioCtx.currentTime;
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                
                if (isSuccess) {
                    osc.frequency.setValueAtTime(880, now);
                    osc.frequency.setValueAtTime(1320, now + 0.12);
                    gain.gain.setValueAtTime(0.3, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                    osc.start(now);
                    osc.stop(now + 0.6);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, now);
                    osc.frequency.setValueAtTime(160, now + 0.15);
                    gain.gain.setValueAtTime(0.2, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                    osc.start(now);
                    osc.stop(now + 0.35);
                }
            } catch(e) {}
        }

        // ==============================================================
        // 1. MODEL LOADING & INITIALIZATION
        // ==============================================================
        async function loadFaceModels() {
            const statusBadge = document.getElementById('model-status-badge');
            try {
                statusBadge.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងទាញយក TinyFace & Recognition Models...';
                
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                isModelLoaded = true;
                statusBadge.className = 'badge bg-success px-3 py-1.5 rounded-pill';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> AI Models រួចរាល់ (30 FPS)';

                document.getElementById('btn-enroll-capture').disabled = false;
                
                // Fetch real attendees from DB
                await refreshDatabaseAttendees();

                // Start default enroll camera
                startCamera('video-enroll');
            } catch (err) {
                console.error('Error loading face-api models:', err);
                statusBadge.className = 'badge bg-danger px-3 py-1.5 rounded-pill';
                statusBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> កំហុស Model';
                document.getElementById('btn-enroll-capture').disabled = false;
            }
        }

        async function onWorkshopChange() {
            const sel = document.getElementById('global-workshop-select');
            selectedWorkshopId = parseInt(sel.value) || 0;
            await refreshDatabaseAttendees();
        }

        async function refreshDatabaseAttendees() {
            try {
                const res = await fetch(`${APP_URL}/api/face/descriptors?workshop_id=${selectedWorkshopId}`);
                const data = await res.json();

                if (data.success && Array.isArray(data.attendees)) {
                    dbAttendees = data.attendees;
                    document.getElementById('attendee-count-badge').innerText = dbAttendees.length;
                    document.getElementById('kiosk-enrolled-count').innerText = dbAttendees.length;

                    // Rebuild matcher
                    let labeledDescriptors = [];
                    dbAttendees.forEach(att => {
                        if (Array.isArray(att.descriptor) && att.descriptor.length === 128) {
                            labeledDescriptors.push(
                                new faceapi.LabeledFaceDescriptors(att.registration_code, [new Float32Array(att.descriptor)])
                            );
                        }
                    });

                    if (labeledDescriptors.length > 0) {
                        faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.52);
                    } else {
                        faceMatcher = null;
                    }

                    renderTable();
                }
            } catch (err) {
                console.error('Failed to load DB descriptors:', err);
            }
        }

        // ==============================================================
        // 2. CAMERA MANAGEMENT
        // ==============================================================
        async function startCamera(videoId) {
            const video = document.getElementById(videoId);
            if (!video) return;

            try {
                if (localStream) {
                    localStream.getTracks().forEach(t => t.stop());
                }

                localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
                    audio: false
                });

                video.srcObject = localStream;
                await video.play();

                if (videoId === 'video-enroll') {
                    monitorEnrollmentVideo();
                } else if (videoId === 'video-kiosk') {
                    startKioskLiveRecognition();
                }
            } catch (err) {
                console.warn('Camera access denied:', err);
                const guideStatus = document.getElementById('enroll-guide-status');
                if (guideStatus) {
                    guideStatus.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill';
                    guideStatus.innerHTML = '<i class="bi bi-camera-video-off me-1"></i> សូមអនុញ្ញាត Camera Permission';
                }
            }
        }

        // Realtime Enrollment Face Guide Feedback
        let currentEnrollDescriptor = null;
        let currentEnrollThumbnail = null;

        async function monitorEnrollmentVideo() {
            const video = document.getElementById('video-enroll');
            const canvas = document.getElementById('canvas-enroll');
            const oval = document.getElementById('face-guide-oval');
            const guideStatus = document.getElementById('enroll-guide-status');

            if (!video || !canvas || activeTab !== 'enroll') return;

            const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
            faceapi.matchDimensions(canvas, displaySize);

            const interval = setInterval(async () => {
                if (activeTab !== 'enroll' || video.paused || video.ended) {
                    clearInterval(interval);
                    return;
                }

                if (!isModelLoaded) return;

                const detection = await faceapi.detectSingleFace(
                    video, 
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptor();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detection) {
                    const resized = faceapi.resizeResults(detection, displaySize);
                    const box = resized.detection.box;
                    const isCentered = (box.x > displaySize.width * 0.15 && (box.x + box.width) < displaySize.width * 0.85);

                    if (isCentered) {
                        oval.classList.add('aligned');
                        guideStatus.className = 'badge bg-success px-3 py-1.5 rounded-pill';
                        guideStatus.innerHTML = '<i class="bi bi-check-circle me-1"></i> ទីតាំងល្អឥតខ្ចោះ! អាចចុចរក្សាទុក';

                        currentEnrollDescriptor = Array.from(detection.descriptor);

                        // Capture small cropped thumbnail
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = 160;
                        tempCanvas.height = 160;
                        const tctx = tempCanvas.getContext('2d');
                        tctx.drawImage(video, 0, 0, 160, 160);
                        currentEnrollThumbnail = tempCanvas.toDataURL('image/jpeg', 0.85);
                    } else {
                        oval.classList.remove('aligned');
                        guideStatus.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill';
                        guideStatus.innerHTML = 'សូមរំកិលមុខមកកណ្តាលរង្វង់';
                    }
                } else {
                    oval.classList.remove('aligned');
                    guideStatus.className = 'badge bg-secondary px-3 py-1.5 rounded-pill';
                    guideStatus.innerHTML = 'សូមបែរមុខមករកកាមេរ៉ា...';
                }
            }, 180);
        }

        // ==============================================================
        // 3. CAPTURE & SAVE TO REAL DATABASE (api/face/enroll)
        // ==============================================================
        async function captureAndEnrollFace() {
            const codeInput = document.getElementById('reg-code');
            const code = codeInput.value.trim();
            const btn = document.getElementById('btn-enroll-capture');

            if (!code) {
                Swal.fire({ icon: 'warning', title: 'សូមបញ្ចូលលេខកូដសំបុត្រ!', text: 'លេខកូដចុះឈ្មោះ (Registration Code) មិនអាចទទេបានឡើយ។' });
                return;
            }

            if (!currentEnrollDescriptor) {
                Swal.fire({ icon: 'warning', title: 'មិនទាន់ចាប់បានផ្ទៃមុខ!', text: 'សូមដាក់ផ្ទៃមុខរបស់អ្នកចំកណ្តាលរង្វង់រហូតដល់ចេញពណ៌បៃតង។' });
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងរក្សាទុកក្នុង MySQL...';

            try {
                const res = await fetch(`${APP_URL}/api/face/enroll`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        registration_code: code,
                        descriptor: currentEnrollDescriptor,
                        photo: currentEnrollThumbnail
                    })
                });

                const data = await res.json();
                if (data.success) {
                    playChime(true);

                    Swal.fire({
                        icon: 'success',
                        title: 'បានរក្សាទុកក្នុង MySQL ជោគជ័យ!',
                        html: `<strong>${data.participant.name}</strong> (${data.participant.registration_code})<br>` +
                              `កម្មវិធី៖ ${data.participant.workshop_name}<br><br>` +
                              `<span class="badge bg-success p-2">ឥឡូវនេះអាចចូល Tab «តុស្កេន Kiosk» ដើម្បី Check-in បានភ្លាមៗ!</span>`,
                        timer: 4000,
                        showConfirmButton: true
                    });

                    await refreshDatabaseAttendees();
                } else {
                    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'កំហុសភ្ជាប់ប្រព័ន្ធ', text: err.message });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> ថតស្កេន & រក្សាទុកផ្ទៃមុខ (Save to Database)';
            }
        }

        // ==============================================================
        // 4. KIOSK LIVE RECOGNITION (REAL ATTENDANCE RECORDING)
        // ==============================================================
        let kioskInterval = null;

        function startKioskLiveRecognition() {
            const video = document.getElementById('video-kiosk');
            const canvas = document.getElementById('canvas-kiosk');
            if (!video || !canvas) return;

            const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
            faceapi.matchDimensions(canvas, displaySize);

            if (kioskInterval) clearInterval(kioskInterval);

            kioskInterval = setInterval(async () => {
                if (activeTab !== 'kiosk' || video.paused || video.ended || isProcessingScan) {
                    return;
                }

                if (!isModelLoaded) return;

                const detections = await faceapi.detectAllFaces(
                    video, 
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptors();

                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections && detections.length > 0) {
                    const resizedDetections = faceapi.resizeResults(detections, displaySize);

                    resizedDetections.forEach(d => {
                        const box = d.detection.box;

                        if (faceMatcher) {
                            const bestMatch = faceMatcher.findBestMatch(d.descriptor);
                            
                            if (bestMatch.label !== 'unknown' && bestMatch.distance < 0.52) {
                                // Match found!
                                const attendee = dbAttendees.find(a => a.registration_code === bestMatch.label);
                                
                                ctx.strokeStyle = '#10b981';
                                ctx.lineWidth = 3;
                                ctx.strokeRect(box.x, box.y, box.width, box.height);

                                ctx.fillStyle = '#10b981';
                                ctx.fillRect(box.x, box.y - 25, box.width, 25);
                                ctx.fillStyle = '#ffffff';
                                ctx.font = 'bold 13px Inter, sans-serif';
                                ctx.fillText((attendee ? attendee.name : bestMatch.label), box.x + 6, box.y - 8);

                                // Real Check-in
                                triggerRealCheckin(attendee || { registration_code: bestMatch.label });
                            } else {
                                // Unknown Person
                                ctx.strokeStyle = '#f59e0b';
                                ctx.lineWidth = 2;
                                ctx.strokeRect(box.x, box.y, box.width, box.height);
                            }
                        }
                    });
                }
            }, 120);
        }

        async function triggerRealCheckin(attendee) {
            const now = Date.now();
            const regCode = attendee.registration_code;

            if (lastDetectedCode === regCode && (now - lastDetectedTime < 6000)) {
                return;
            }

            lastDetectedCode = regCode;
            lastDetectedTime = now;
            isProcessingScan = true;

            const fd = new FormData();
            fd.append('token', regCode);
            fd.append('workshop_id', selectedWorkshopId);
            fd.append('scan_type', 'face');

            try {
                const res = await fetch(`${APP_URL}/api/checkin/scan`, {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                const successPopup = document.getElementById('kiosk-success-popup');
                const warnPopup = document.getElementById('kiosk-warning-popup');

                if (data.already_in) {
                    playChime(false);
                    document.getElementById('warn-name').innerText = attendee.name || data.participant?.name || regCode;
                    document.getElementById('warn-desc').innerText = data.message || 'បានកត់ត្រាវត្តមានរួចរាល់ហើយកាលពីមុននេះបន្តិច។';

                    warnPopup.classList.remove('d-none');
                    setTimeout(() => { warnPopup.classList.add('d-none'); }, 2600);
                } else if (data.success) {
                    playChime(true);
                    const p = data.participant || attendee;

                    document.getElementById('pop-name').innerText = p.name;
                    document.getElementById('pop-code').innerText = p.reg_code || p.registration_code;
                    document.getElementById('pop-ticket').innerText = p.ticket || p.ticket_name || 'ទូទៅ';

                    successPopup.classList.remove('d-none');
                    setTimeout(() => { successPopup.classList.add('d-none'); }, 2600);

                    // Add to live feed
                    addRecentItemToFeed(p);
                    await refreshDatabaseAttendees();
                }
            } catch (err) {
                console.error('Scan error:', err);
            } finally {
                setTimeout(() => { isProcessingScan = false; }, 2200);
            }
        }

        function addRecentItemToFeed(p) {
            const feed = document.getElementById('kiosk-attendance-feed');
            const emptyMsg = document.getElementById('empty-attendance-msg');
            if (emptyMsg) emptyMsg.remove();

            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const photoUrl = p.photo_url || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(p.name));

            const html = `
                <div class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2 shadow-2xs" style="animation: popIn 0.3s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${photoUrl}" class="avatar-box" alt="Face">
                        <div>
                            <div class="fw-bold text-dark">${p.name}</div>
                            <small class="text-muted font-monospace">${p.reg_code || p.registration_code} &bull; <span class="badge bg-primary-subtle text-primary border">${p.ticket || p.ticket_name || 'ទូទៅ'}</span></small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success px-2.5 py-1 mb-1 d-inline-block">
                            <i class="bi bi-check2"></i> ${timeStr}
                        </span>
                        <div>
                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">AI ស្កេនមុខ</span>
                        </div>
                    </div>
                </div>`;
            feed.insertAdjacentHTML('afterbegin', html);

            const countEl = document.getElementById('live-checkin-count');
            if (countEl) {
                const cur = parseInt(countEl.innerText) || 0;
                countEl.innerText = (cur + 1) + ' នាក់';
            }
        }

        // ==============================================================
        // 5. TAB SWITCHER & DATABASE RENDER
        // ==============================================================
        function switchTab(tab) {
            activeTab = tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.demo-view').forEach(v => v.classList.add('d-none'));

            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('view-' + tab).classList.remove('d-none');

            if (tab === 'enroll') {
                startCamera('video-enroll');
            } else if (tab === 'kiosk') {
                startCamera('video-kiosk');
            } else if (tab === 'database') {
                if (localStream) localStream.getTracks().forEach(t => t.stop());
                renderTable();
            }
        }

        function renderTable() {
            const tbody = document.getElementById('attendee-table-body');
            if (dbAttendees.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យមុខដែលបានចុះឈ្មោះសម្រាប់សិក្ខាសាលានេះក្នុង Database ឡើយ។ សូមចុះឈ្មោះក្នុង Tab ១។</td></tr>`;
                return;
            }

            tbody.innerHTML = dbAttendees.map(a => `
                <tr>
                    <td><img src="${a.photo_url}" class="avatar-box" alt="Face"></td>
                    <td>
                        <strong class="text-dark">${a.name}</strong>
                        <div class="small text-muted">${a.company || '-'}</div>
                    </td>
                    <td><span class="badge bg-light text-primary font-monospace border">${a.registration_code}</span></td>
                    <td><span class="badge bg-secondary-subtle text-secondary">${a.ticket_name}</span></td>
                    <td>
                        <span class="font-monospace small text-muted">
                            [${(a.descriptor || []).slice(0, 4).map(n => n.toFixed(3)).join(', ')}, ...] (128 floats)
                        </span>
                    </td>
                    <td>
                        ${a.checked_in 
                            ? `<span class="badge bg-success px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Checked-in (${a.checked_in_at})</span>` 
                            : `<span class="badge bg-light text-muted border px-2.5 py-1"><i class="bi bi-hourglass me-1"></i> មិនទាន់ Check-in</span>`}
                    </td>
                </tr>
            `).join('');
        }

        // Live Clock
        setInterval(() => {
            const clockEl = document.getElementById('kiosk-clock');
            if (clockEl) clockEl.innerText = new Date().toLocaleTimeString();
        }, 1000);

        window.addEventListener('DOMContentLoaded', loadFaceModels);
    </script>
</body>
</html>
