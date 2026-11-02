<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AI Face Check-in Kiosk') ?></title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Kantumruy Pro & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- face-api.js (Vladmandic WebGL optimized) -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>

    <style>
        :root {
            --primary: #2563eb;
            --success: #10b981;
            --dark-bg: #0f172a;
        }
        body {
            font-family: 'Kantumruy Pro', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
        }

        /* Top Kiosk Bar */
        .kiosk-navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Camera Viewport */
        .kiosk-viewport-container {
            position: relative;
            width: 100%;
            max-width: 680px;
            margin: 0 auto;
            border-radius: 28px;
            overflow: hidden;
            background: #020617;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            aspect-ratio: 4 / 3;
        }
        video.kiosk-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        canvas.kiosk-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
        }

        /* Laser Scan Line */
        .kiosk-laser {
            position: absolute;
            left: 5%;
            width: 90%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #2563eb, transparent);
            box-shadow: 0 0 18px #38bdf8;
            top: 15%;
            animation: laserAnim 2.2s ease-in-out infinite alternate;
            pointer-events: none;
            border-radius: 50%;
        }
        @keyframes laserAnim {
            0% { top: 10%; opacity: 0.2; }
            50% { opacity: 1; }
            100% { top: 90%; opacity: 0.2; }
        }

        /* Fullscreen Success Welcome Overlay */
        .kiosk-welcome-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.96) 0%, rgba(5, 150, 105, 0.98) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            z-index: 25;
            padding: 30px;
            text-align: center;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .kiosk-warning-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.96) 0%, rgba(217, 119, 6, 0.98) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            z-index: 25;
            padding: 30px;
            text-align: center;
            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Oval guide for enrollment modal */
        .enroll-oval {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 250px;
            border: 3px dashed rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
        }
        .enroll-oval.ready {
            border-color: #10b981;
            border-style: solid;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.3), 0 0 25px rgba(16, 185, 129, 0.5);
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
        }
        .glass-list-item {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            color: #f8fafc;
        }

        .avatar-kiosk {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

    <!-- Kiosk Top Navigation Bar -->
    <header class="kiosk-navbar py-2.5 px-3 px-md-4 sticky-top">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            
            <div class="d-flex align-items-center gap-3">
                <a href="<?= APP_URL ?>/checkin/<?= $workshop['id'] ?>" class="btn btn-sm btn-outline-light rounded-pill px-3" title="ត្រឡប់ទៅផ្ទាំង Check-in ធម្មតា">
                    <i class="bi bi-arrow-left me-1"></i> ចាកចេញពី Kiosk
                </a>
                <div class="border-start border-secondary ps-3 d-none d-sm-block">
                    <h5 class="fw-bold mb-0 text-white text-truncate" style="max-width: 320px;">
                        <?= htmlspecialchars($workshop['name']) ?>
                    </h5>
                    <small class="text-white-50" style="font-size: 0.75rem;">
                        <i class="bi bi-calendar3 me-1"></i> <?= formatDate($workshop['start_date']) ?> &bull; ច្រកទ្វារ AI Face Check-in
                    </small>
                </div>
            </div>

            <!-- Central Live Status Pills -->
            <div class="d-flex align-items-center gap-2">
                <span id="kiosk-model-badge" class="badge bg-warning text-dark px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                    <span class="spinner-border spinner-border-sm me-1"></span> កំពុងទាញយក AI Models...
                </span>
                
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                    <i class="bi bi-person-bounding-box me-1"></i> ស្គាល់ក្នុងបញ្ជី: <strong id="kiosk-enrolled-count"><?= $enrolledCount ?></strong>
                </span>

                <span class="badge bg-success px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                    <i class="bi bi-people-fill me-1"></i> វត្តមាន: <strong id="kiosk-checked-count"><?= $totalCheckedIn ?></strong> / <?= $totalConfirmed ?>
                </span>

                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold ms-1" data-bs-toggle="modal" data-bs-target="#enrollModal" title="ចុះឈ្មោះមុខសិក្ខាកាមថ្មី">
                    <i class="bi bi-person-plus-fill me-1"></i> ចុះឈ្មោះមុខថ្មី
                </button>
            </div>

        </div>
    </header>

    <!-- Main Content Area -->
    <main class="container-fluid py-4 px-3 px-lg-5">
        <div class="row g-4 justify-content-center">

            <!-- Left: Kiosk Camera Viewport -->
            <div class="col-12 col-xl-7 col-xxl-6">
                <div class="glass-card p-3 p-md-4 text-center">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                        <div class="text-start">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small fw-bold">
                                <i class="bi bi-broadcast me-1 text-danger"></i> កាមេរ៉ាស្កេន AI សកម្ម
                            </span>
                        </div>
                        <div class="text-end">
                            <span class="text-white-50 small font-monospace" id="kiosk-clock">--:--:--</span>
                        </div>
                    </div>

                    <!-- Camera Box -->
                    <div class="kiosk-viewport-container">
                        <video id="kioskVideo" class="kiosk-video" autoplay muted playsinline></video>
                        <canvas id="kioskCanvas" class="kiosk-canvas"></canvas>
                        <div class="kiosk-laser"></div>

                        <!-- Success Popup Modal -->
                        <div id="kioskSuccessPopup" class="kiosk-welcome-overlay d-none">
                            <div class="bg-white text-success rounded-circle p-3 d-inline-flex mb-3 shadow-lg">
                                <i class="bi bi-check-lg display-4 fw-bold"></i>
                            </div>
                            <h3 class="fw-bold mb-1">សូមស្វាគមន៍!</h3>
                            <h1 class="fw-extrabold mb-2 display-6" id="successAttendeeName">--</h1>
                            <div class="d-flex gap-2 justify-content-center mb-3">
                                <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill font-monospace fs-6" id="successAttendeeCode">--</span>
                                <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-6" id="successAttendeeTicket">--</span>
                            </div>
                            <h6 class="text-white-50 mb-0" id="successAttendeeCompany">--</h6>
                            <div class="mt-3 text-white small">
                                <i class="bi bi-patch-check-fill me-1"></i> វត្តមានត្រូវបានកត់ត្រាចូលប្រព័ន្ធជោគជ័យ
                            </div>
                        </div>

                        <!-- Warning Popup Modal (Already Checked In) -->
                        <div id="kioskWarningPopup" class="kiosk-warning-overlay d-none">
                            <div class="bg-white text-warning rounded-circle p-3 d-inline-flex mb-3 shadow-lg">
                                <i class="bi bi-exclamation-triangle-fill display-4 fw-bold"></i>
                            </div>
                            <h3 class="fw-bold mb-1">បាន Check-in រួចរាល់ហើយ!</h3>
                            <h2 class="fw-bold mb-2" id="warnAttendeeName">--</h2>
                            <p class="mb-0 text-white-50" id="warnAttendeeTime">
                                សិក្ខាកាមនេះបានកត់ត្រាវត្តមានរួចហើយនៅមុននេះបន្តិច។
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center px-1">
                        <small class="text-white-50">
                            <i class="bi bi-info-circle me-1"></i> សូមឈរនៅចម្ងាយ 0.5m - 1.5m ពីមុខកាមេរ៉ា
                        </small>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-white-50" onclick="restartCamera()">
                            <i class="bi bi-arrow-repeat me-1"></i> Refresh Camera
                        </button>
                    </div>

                </div>
            </div>

            <!-- Right: Real-time Attendance Stream -->
            <div class="col-12 col-xl-5 col-xxl-5">
                <div class="glass-card p-3 p-md-4 h-100 d-flex flex-column">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-check-fill text-success fs-5"></i>
                            <h6 class="fw-bold mb-0 text-white">វត្តមានទើបស្កេនថ្មីៗ (Live Stream)</h6>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill font-monospace" id="streamCounter">
                            <?= count($recentAttendance) ?> នាក់
                        </span>
                    </div>

                    <!-- Attendance List -->
                    <div class="flex-grow-1 overflow-auto pe-1" id="attendanceFeedContainer" style="max-height: 520px;">
                        <?php if (empty($recentAttendance)): ?>
                            <div class="text-center py-5 text-white-50" id="emptyAttendanceMessage">
                                <i class="bi bi-person-badge display-6 d-block mb-2 text-secondary"></i>
                                មិនទាន់មានវត្តមាន Check-in ថ្មីៗនៅឡើយទេ...
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentAttendance as $item): ?>
                                <div class="glass-list-item p-3 mb-2 d-flex align-items-center justify-content-between shadow-2xs">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= !empty($item['photo']) ? (APP_URL . '/' . ltrim($item['photo'], '/\\')) : ('https://api.dicebear.com/7.x/avataaars/svg?seed=' . urlencode($item['name'])) ?>" class="avatar-kiosk" alt="Face">
                                        <div>
                                            <div class="fw-bold text-white"><?= htmlspecialchars($item['name']) ?></div>
                                            <small class="text-white-50">
                                                <span class="font-monospace text-primary"><?= htmlspecialchars($item['registration_code']) ?></span> &bull; 
                                                <?= htmlspecialchars($item['ticket_name'] ?? 'ទូទៅ') ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success px-2.5 py-1 rounded-pill mb-1 d-inline-block">
                                            <i class="bi bi-check2"></i> <?= date('H:i:s', strtotime($item['checked_in_at'])) ?>
                                        </span>
                                        <div>
                                            <span class="badge bg-secondary-subtle text-white-50" style="font-size: 0.65rem;">
                                                <?= ($item['check_in_method'] === 'face') ? 'AI ស្កេនមុខ' : 'QR Scan' ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <!-- Modal: Quick Enroll Participant Face -->
    <div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 glass-card text-white">
                <div class="modal-header border-bottom border-secondary border-opacity-25 py-3">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>ចុះឈ្មោះផ្ទៃមុខសិក្ខាកាម (Face ID)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label small fw-bold text-white">ជ្រើសរើសសិក្ខាកាម <span class="text-danger">*</span></label>
                        <select id="modalAttendeeSelect" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- ជ្រើសរើសសិក្ខាកាមក្នុងសិក្ខាសាលានេះ --</option>
                            <?php foreach ($unregisteredParticipants as $p): ?>
                                <option value="<?= htmlspecialchars($p['registration_code']) ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['registration_code']) ?> - <?= htmlspecialchars($p['company'] ?: $p['ticket_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-white-50 small">ឬវាយកូដសំបុត្រផ្ទាល់ខាងក្រោម៖</div>
                        <input type="text" id="modalCustomCodeInput" class="form-control form-control-sm bg-dark text-white border-secondary mt-1 font-monospace" placeholder="ឧ. REG92FE1637">
                    </div>

                    <!-- Mini Enrollment Camera Viewport -->
                    <div class="position-relative mx-auto rounded-4 overflow-hidden mb-3 bg-black shadow" style="width: 100%; max-width: 380px; aspect-ratio: 4/3;">
                        <video id="modalEnrollVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                        <canvas id="modalEnrollCanvas" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; transform: scaleX(-1);"></canvas>
                        <div id="modalEnrollOval" class="enroll-oval"></div>
                    </div>

                    <div id="modalEnrollStatus" class="badge bg-secondary-subtle text-white px-3 py-1.5 rounded-pill mb-3">
                        សូមរំកិលផ្ទៃមុខមកចំកណ្តាលរង្វង់...
                    </div>

                    <button type="button" id="btnModalCaptureFace" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm" onclick="submitFaceEnrollment()" disabled>
                        <i class="bi bi-camera-fill me-1"></i> ថត & រក្សាទុកផ្ទៃមុខ (Save Face)
                    </button>

                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const WORKSHOP_ID = <?= (int)$workshop['id'] ?>;
        const APP_URL = <?= json_encode(APP_URL) ?>;
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';

        let isModelLoaded = false;
        let kioskStream = null;
        let enrollStream = null;
        let enrolledAttendees = [];
        let faceMatcher = null;
        let lastScannedCode = null;
        let lastScannedTime = 0;
        let isProcessingScan = false;

        // Web Audio Synthesizer Chime
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
                    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    osc.start(now);
                    osc.stop(now + 0.4);
                }
            } catch(e) {}
        }

        // ==============================================================
        // 1. INITIALIZE & FETCH REAL DESCRIPTORS FROM DATABASE
        // ==============================================================
        async function initKiosk() {
            const statusBadge = document.getElementById('kiosk-model-badge');

            try {
                // 1. Load AI Models
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);

                isModelLoaded = true;
                statusBadge.className = 'badge bg-success px-3 py-1.5 rounded-pill';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> AI Models រួចរាល់';

                // 2. Load Descriptors from Database
                await loadDescriptorsFromDatabase();

                // 3. Start Camera
                await startKioskCamera();

            } catch (err) {
                console.error('Initialization error:', err);
                statusBadge.className = 'badge bg-danger px-3 py-1.5 rounded-pill';
                statusBadge.innerHTML = 'កំហុស Model (សូម Refresh)';
            }
        }

        async function loadDescriptorsFromDatabase() {
            try {
                const res = await fetch(`${APP_URL}/api/face/descriptors?workshop_id=${WORKSHOP_ID}`);
                const data = await res.json();

                if (data.success && Array.isArray(data.attendees)) {
                    enrolledAttendees = data.attendees;
                    document.getElementById('kiosk-enrolled-count').innerText = enrolledAttendees.length;

                    // Build FaceMatcher
                    let labeledDescriptors = [];
                    enrolledAttendees.forEach(att => {
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
                }
            } catch (e) {
                console.error('Failed to load descriptors from database:', e);
            }
        }

        // ==============================================================
        // 2. KIOSK WEBCAM & CONTINUOUS AI MATCHING
        // ==============================================================
        async function startKioskCamera() {
            const video = document.getElementById('kioskVideo');
            if (!video) return;

            try {
                if (kioskStream) {
                    kioskStream.getTracks().forEach(t => t.stop());
                }

                kioskStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
                    audio: false
                });

                video.srcObject = kioskStream;
                await video.play();

                runKioskDetectorLoop();
            } catch (err) {
                console.warn('Camera access denied:', err);
                Swal.fire({
                    icon: 'warning',
                    title: 'មិនអាចបើកកាមេរ៉ា!',
                    text: 'សូមអនុញ្ញាត Camera Permission នៅក្នុង Browser របស់លោកអ្នក។',
                    background: '#1e293b',
                    color: '#fff'
                });
            }
        }

        function restartCamera() {
            startKioskCamera();
        }

        let kioskLoopInterval = null;

        function runKioskDetectorLoop() {
            const video = document.getElementById('kioskVideo');
            const canvas = document.getElementById('kioskCanvas');
            if (!video || !canvas) return;

            const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
            faceapi.matchDimensions(canvas, displaySize);

            if (kioskLoopInterval) clearInterval(kioskLoopInterval);

            kioskLoopInterval = setInterval(async () => {
                if (video.paused || video.ended || !isModelLoaded || isProcessingScan) return;

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

                            // Strict biometric check: distance < 0.38
                            if (bestMatch.label !== 'unknown' && bestMatch.distance < 0.38) {
                                // Match found!
                                const attendee = enrolledAttendees.find(a => a.registration_code === bestMatch.label);
                                
                                // Draw Green Box
                                ctx.strokeStyle = '#10b981';
                                ctx.lineWidth = 3;
                                ctx.strokeRect(box.x, box.y, box.width, box.height);

                                ctx.fillStyle = '#10b981';
                                ctx.fillRect(box.x, box.y - 26, box.width, 26);
                                ctx.fillStyle = '#ffffff';
                                ctx.font = 'bold 13px Inter, sans-serif';
                                ctx.fillText((attendee ? attendee.name : bestMatch.label), box.x + 8, box.y - 8);

                                // Perform Real Check-in
                                triggerRealCheckin(attendee || { registration_code: bestMatch.label });
                            } else {
                                // Unknown Face
                                ctx.strokeStyle = '#f59e0b';
                                ctx.lineWidth = 2;
                                ctx.strokeRect(box.x, box.y, box.width, box.height);
                            }
                        }
                    });
                }
            }, 130);
        }

        // ==============================================================
        // 3. REAL BACKEND CHECK-IN CALL
        // ==============================================================
        async function triggerRealCheckin(attendee) {
            const now = Date.now();
            const regCode = attendee.registration_code;

            // Debounce: don't trigger the same code within 6 seconds
            if (lastScannedCode === regCode && (now - lastScannedTime < 6000)) {
                return;
            }

            lastScannedCode = regCode;
            lastScannedTime = now;
            isProcessingScan = true;

            const fd = new FormData();
            fd.append('token', regCode);
            fd.append('workshop_id', WORKSHOP_ID);
            fd.append('scan_type', 'face');

            try {
                const res = await fetch(`${APP_URL}/api/checkin/scan`, {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();

                if (data.already_in) {
                    // Already Checked In Warning
                    playChime(false);
                    showWarningPopup(attendee.name || data.participant?.name || regCode, data.message);
                } else if (data.success) {
                    // Check-in Success!
                    playChime(true);
                    showSuccessPopup(data.participant || attendee);
                    addRecentAttendanceItem(data.participant || attendee);
                    incrementCheckedCount();
                } else {
                    console.warn('Scan rejected:', data.message);
                }
            } catch (err) {
                console.error('Scan error:', err);
            } finally {
                setTimeout(() => {
                    isProcessingScan = false;
                }, 2200);
            }
        }

        function showSuccessPopup(p) {
            const popup = document.getElementById('kioskSuccessPopup');
            document.getElementById('successAttendeeName').innerText = p.name || 'សិក្ខាកាម';
            document.getElementById('successAttendeeCode').innerText = p.reg_code || p.registration_code || '';
            document.getElementById('successAttendeeTicket').innerText = p.ticket || p.ticket_name || 'ទូទៅ';
            document.getElementById('successAttendeeCompany').innerText = p.company || p.org_name || '';

            popup.classList.remove('d-none');
            setTimeout(() => {
                popup.classList.add('d-none');
            }, 2600);
        }

        function showWarningPopup(name, msg) {
            const popup = document.getElementById('kioskWarningPopup');
            document.getElementById('warnAttendeeName').innerText = name;
            document.getElementById('warnAttendeeTime').innerText = msg || 'បាន Check-in រួចរាល់ហើយកាលពីមុននេះបន្តិច';

            popup.classList.remove('d-none');
            setTimeout(() => {
                popup.classList.add('d-none');
            }, 2400);
        }

        function incrementCheckedCount() {
            const countEl = document.getElementById('kiosk-checked-count');
            if (countEl) {
                const cur = parseInt(countEl.innerText) || 0;
                countEl.innerText = cur + 1;
            }
        }

        function addRecentAttendanceItem(p) {
            const container = document.getElementById('attendanceFeedContainer');
            const emptyMsg = document.getElementById('emptyAttendanceMessage');
            if (emptyMsg) emptyMsg.remove();

            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const photoUrl = p.photo_url || ('https://api.dicebear.com/7.x/avataaars/svg?seed=' + encodeURIComponent(p.name));

            const html = `
                <div class="glass-list-item p-3 mb-2 d-flex align-items-center justify-content-between shadow-2xs" style="animation: popIn 0.3s ease;">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${photoUrl}" class="avatar-kiosk" alt="Face">
                        <div>
                            <div class="fw-bold text-white">${p.name}</div>
                            <small class="text-white-50">
                                <span class="font-monospace text-primary">${p.reg_code || p.registration_code}</span> &bull; 
                                ${p.ticket || p.ticket_name || 'ទូទៅ'}
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success px-2.5 py-1 rounded-pill mb-1 d-inline-block">
                            <i class="bi bi-check2"></i> ${timeStr}
                        </span>
                        <div>
                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">
                                AI ស្កេនមុខ
                            </span>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('afterbegin', html);

            const streamCount = document.getElementById('streamCounter');
            if (streamCount) {
                const c = container.querySelectorAll('.glass-list-item').length;
                streamCount.innerText = c + ' នាក់';
            }
        }

        // ==============================================================
        // 4. QUICK FACE ENROLLMENT MODAL
        // ==============================================================
        let modalEnrollDescriptor = null;
        let modalEnrollPhotoData = null;

        const enrollModalEl = document.getElementById('enrollModal');
        if (enrollModalEl) {
            enrollModalEl.addEventListener('shown.bs.modal', startModalEnrollCamera);
            enrollModalEl.addEventListener('hidden.bs.modal', stopModalEnrollCamera);
        }

        document.getElementById('modalAttendeeSelect').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('modalCustomCodeInput').value = this.value;
            }
        });

        async function startModalEnrollCamera() {
            const video = document.getElementById('modalEnrollVideo');
            const canvas = document.getElementById('modalEnrollCanvas');
            const oval = document.getElementById('modalEnrollOval');
            const statusBadge = document.getElementById('modalEnrollStatus');
            const btn = document.getElementById('btnModalCaptureFace');

            try {
                enrollStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
                    audio: false
                });
                video.srcObject = enrollStream;
                await video.play();

                const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
                faceapi.matchDimensions(canvas, displaySize);

                const enrollInterval = setInterval(async () => {
                    if (video.paused || video.ended || !enrollStream) {
                        clearInterval(enrollInterval);
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
                        const box = detection.detection.box;
                        const isCentered = (box.x > displaySize.width * 0.15 && (box.x + box.width) < displaySize.width * 0.85);

                        if (isCentered) {
                            oval.classList.add('ready');
                            statusBadge.className = 'badge bg-success px-3 py-1.5 rounded-pill mb-3';
                            statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> ទីតាំងល្អ! ចុចប៊ូតុងខាងក្រោមដើម្បីថត';
                            btn.disabled = false;

                            modalEnrollDescriptor = Array.from(detection.descriptor);

                            // Capture snapshot
                            const tempC = document.createElement('canvas');
                            tempC.width = 160;
                            tempC.height = 160;
                            const tctx = tempC.getContext('2d');
                            tctx.drawImage(video, 0, 0, 160, 160);
                            modalEnrollPhotoData = tempC.toDataURL('image/jpeg', 0.85);
                        } else {
                            oval.classList.remove('ready');
                            statusBadge.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill mb-3';
                            statusBadge.innerHTML = 'សូមរំកិលមុខមកកណ្តាលរង្វង់';
                            btn.disabled = true;
                        }
                    } else {
                        oval.classList.remove('ready');
                        statusBadge.className = 'badge bg-secondary-subtle text-white px-3 py-1.5 rounded-pill mb-3';
                        statusBadge.innerHTML = 'សូមបែរមុខមករកកាមេរ៉ា...';
                        btn.disabled = true;
                    }
                }, 160);
            } catch (err) {
                console.warn('Enroll camera error:', err);
                statusBadge.className = 'badge bg-danger px-3 py-1.5 rounded-pill mb-3';
                statusBadge.innerHTML = 'មិនអាចបើកកាមេរ៉ា';
            }
        }

        function stopModalEnrollCamera() {
            if (enrollStream) {
                enrollStream.getTracks().forEach(t => t.stop());
                enrollStream = null;
            }
        }

        async function submitFaceEnrollment() {
            const code = document.getElementById('modalCustomCodeInput').value.trim();
            const btn = document.getElementById('btnModalCaptureFace');

            if (!code) {
                Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើសសិក្ខាកាម!', text: 'ត្រូវមានលេខកូដសំបុត្រ (Registration Code)' });
                return;
            }
            if (!modalEnrollDescriptor) {
                Swal.fire({ icon: 'warning', title: 'មិនទាន់ចាប់បានផ្ទៃមុខ!', text: 'សូមដាក់ផ្ទៃមុខក្នុងរង្វង់' });
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងរក្សាទុក...';

            try {
                const res = await fetch(`${APP_URL}/api/face/enroll`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        registration_code: code,
                        descriptor: modalEnrollDescriptor,
                        photo: modalEnrollPhotoData
                    })
                });

                const data = await res.json();
                if (data.success) {
                    playChime(true);
                    bootstrap.Modal.getInstance(document.getElementById('enrollModal')).hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'ចុះឈ្មោះផ្ទៃមុខជោគជ័យ!',
                        text: `${data.participant.name} អាចចូល Check-in តាមកាមេរ៉ា Kiosk នេះបានភ្លាមៗ!`,
                        timer: 2600,
                        showConfirmButton: false
                    });

                    // Reload Descriptors from DB so Kiosk recognizes them immediately!
                    await loadDescriptorsFromDatabase();
                } else {
                    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: data.message });
                }
            } catch (err) {
                Swal.fire({ icon: 'error', title: 'កំហុសភ្ជាប់ប្រព័ន្ធ', text: err.message });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> ថត & រក្សាទុកផ្ទៃមុខ (Save Face)';
            }
        }

        // Live Clock
        setInterval(() => {
            const clockEl = document.getElementById('kiosk-clock');
            if (clockEl) clockEl.innerText = new Date().toLocaleTimeString();
        }, 1000);

        // Start everything on page load
        window.addEventListener('DOMContentLoaded', initKiosk);
    </script>
</body>
</html>
