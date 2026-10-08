<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>តុស្កេនជំនួយការ - <?= htmlspecialchars($pass['workshop_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
        }
        body {
            font-family: 'Kantumruy Pro', -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }
        .main-container {
            max-width: 1240px;
            margin: 0 auto;
        }
        .stats-bar {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            padding: 16px 24px;
        }
        .fast-mode-pill {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 50rem;
            padding: 6px 14px;
            transition: all 0.2s ease;
        }
        .fast-mode-pill:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }
        .fast-mode-pill .form-check-input {
            width: 2.2rem;
            height: 1.25rem;
            margin: 0 !important;
            float: none !important;
            cursor: pointer;
        }
        .scanner-container {
            position: relative;
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }
        #reader {
            width: 100%;
            border: none !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            background: #1a1e21;
            position: relative;
        }
        #reader video {
            object-fit: cover !important;
            border-radius: 12px;
        }

        /* Overlay after scan */
        .scan-result-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 30;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(4px);
            padding: 20px;
            text-align: center;
        }
        .scan-success { background: rgba(25, 135, 84, 0.95); color: #ffffff; }
        .scan-warning { background: rgba(245, 158, 11, 0.95); color: #ffffff; }
        .scan-error { background: rgba(220, 53, 69, 0.95); color: #ffffff; }

        /* Floating Zoom & AF Controls */
        .zoom-sidebar {
            position: absolute;
            left: -44px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            flex-direction: column;
            gap: 6px;
            z-index: 25;
        }
        .btn-zoom {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #dee2e6;
            background: #ffffff;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: all 0.2s;
        }
        .btn-zoom:hover {
            border-color: #0d6efd;
            color: #0d6efd;
            transform: scale(1.05);
        }
        .btn-zoom.active {
            background: #0d6efd !important;
            color: #ffffff !important;
            border-color: #0d6efd !important;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.4);
        }

        @media (max-width: 520px) {
            .zoom-sidebar {
                left: 8px;
                background: rgba(0, 0, 0, 0.35);
                padding: 4px;
                border-radius: 20px;
                backdrop-filter: blur(4px);
            }
            .btn-zoom {
                width: 30px;
                height: 30px;
                font-size: 0.68rem;
            }
        }

        /* HTML5 QR Code Scanner native button restyling */
        #html5-qrcode-button-camera-permission {
            background-color: #0d6efd !important;
            color: #ffffff !important;
            border: none !important;
            padding: 9px 18px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            margin: 15px auto !important;
            display: inline-block !important;
            font-size: 0.85rem !important;
        }
        #html5-qrcode-button-camera-start {
            background-color: #198754 !important;
            color: #ffffff !important;
            border: none !important;
            padding: 8px 18px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            margin: 10px auto !important;
            font-size: 0.85rem !important;
        }
        #html5-qrcode-button-camera-stop {
            position: absolute !important;
            bottom: 8px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 20 !important;
            background-color: rgba(220, 53, 69, 0.9) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255,255,255,0.4) !important;
            padding: 5px 14px !important;
            border-radius: 16px !important;
            font-size: 0.78rem !important;
            cursor: pointer !important;
            white-space: nowrap !important;
        }

        /* Pulse scan animation */
        .pulse-scan { animation: pulseBorder 1.5s infinite; }
        @keyframes pulseBorder {
            0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(13, 110, 253, 0); }
            100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
        }
    </style>
</head>
<body>

    <div class="main-container py-3 px-3">
        <!-- Top Stats Bar (Spacious, Clean, Well-aligned) -->
        <div class="stats-bar d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
            <!-- Left Info Block -->
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 46px; height: 46px;">
                    <i class="bi bi-qr-code-scan fs-5"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($pass['workshop_name']) ?></h5>
                        <span class="badge bg-primary-subtle text-primary border px-2.5 py-1 rounded-pill"><?= htmlspecialchars($pass['label']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="badge bg-warning-subtle text-dark border px-3 py-1.5 rounded-pill" id="countdown-badge" title="សុពលភាពដែលនៅសល់">
                            <i class="bi bi-clock-history me-1 text-danger"></i>នៅសល់ <strong id="countdown-timer">...</strong>
                        </span>
                        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
                            <i class="bi bi-shield-check text-success me-1"></i>តុជំនួយការស្កេន
                        </span>
                        <?php if (!empty($device['helper_name'])): ?>
                            <span class="badge bg-primary text-white border px-3 py-1.5 rounded-pill shadow-sm" title="អ្នកស្កេនបច្ចុប្បន្ន">
                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($device['helper_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Controls Block -->
            <div class="d-flex align-items-center gap-3 gap-md-4 ms-auto ms-sm-0 flex-wrap">
                <!-- Attendance Counter with Separator -->
                <div class="text-end pe-3 border-end">
                    <div class="small text-muted mb-1" style="line-height: 1.2; font-size: 0.78rem;">បានស្កេនវត្តមាន</div>
                    <div class="fw-bold fs-5 text-success" id="stats-checked-in">
                        <?= (int)($stats['checked_in'] ?? 0) ?> / <?= (int)($stats['total_confirmed'] ?? 0) ?>
                    </div>
                </div>

                <!-- Fast Mode Switch Pill -->
                <div class="fast-mode-pill d-flex align-items-center gap-2">
                    <input class="form-check-input" type="checkbox" id="fastModeToggle" checked>
                    <label class="form-check-label small text-secondary fw-medium text-nowrap user-select-none mb-0 ps-1" for="fastModeToggle" style="cursor: pointer; font-size: 0.82rem;">
                        ស្កេនលឿន
                    </label>
                </div>

                <!-- Sound Feedback Badge -->
                <span class="badge bg-light text-secondary border py-2 px-3 d-none d-md-inline-flex align-items-center gap-2 rounded-pill" title="សំឡេងប៊ីប: បើក">
                    <i class="bi bi-volume-up-fill text-primary"></i> ប៊ីប
                </span>
            </div>
        </div>

        <!-- Quick Guidance Banner -->
        <div class="alert alert-light border shadow-sm mb-4 py-2 px-3 d-flex justify-content-between align-items-center rounded-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                <span class="small text-dark">
                    <strong>វិធីស្កេនវត្តមាន៖</strong> 
                    (១) <strong>ស្កេនកូដ QR</strong> តាមកាមេរ៉ា ឬ 
                    (២) <strong>វាយឈ្មោះ / លេខទូរស័ព្ទ / កូដ</strong> ក្នុងប្រអប់ស្វែងរក រួចចុច <strong>កត់ត្រា</strong>។
                </span>
            </div>
            <span class="badge bg-success-subtle text-success border small">ដំណើរការ</span>
        </div>

        <!-- Main Workspace: Symmetrical 2 Columns -->
        <div class="row g-4">
            <!-- Left Column: Camera Scanner & Manual Input -->
            <div class="col-lg-6">
                <!-- Camera Scanner Card -->
                <div class="card shadow-sm border-0 mb-3 rounded-3">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="fw-bold small text-dark">
                            <i class="bi bi-camera-video-fill text-primary me-1"></i> ស្កេនកូដ QR តាមកាមេរ៉ា
                        </span>
                        <div id="camera-select-wrapper" style="display: none;">
                            <select id="camera-select" class="form-select form-select-sm py-0 px-2" style="font-size: 0.75rem; height: 26px; max-width: 175px;"></select>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="scanner-container">
                            <div class="zoom-sidebar" id="zoom-sidebar" title="Zoom">
                                <button type="button" class="btn-zoom active" data-zoom="1" title="1x">1x</button>
                                <button type="button" class="btn-zoom" data-zoom="2" title="2x">2x</button>
                                <button type="button" class="btn-zoom" data-zoom="3" title="3x">3x</button>
                            </div>
                            <div id="reader"></div>
                            <div id="scan-result" class="scan-result-overlay">
                                <i id="scan-icon" class="bi display-1 mb-2"></i>
                                <h4 id="scan-message" class="fw-bold px-2 mb-1"></h4>
                                <div id="scan-participant-name" class="fs-5 fw-bold text-truncate"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual Search Card -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white py-2">
                        <span class="fw-bold small text-dark">
                            <i class="bi bi-search text-success me-2"></i> ស្វែងរក ឬកត់ត្រាវត្តមានដោយដៃ
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="manual-input" class="form-control" placeholder="វាយឈ្មោះ, លេខទូរស័ព្ទ, ឬកូដចុះឈ្មោះ..." autocomplete="off">
                            <button class="btn btn-primary fw-semibold px-3" type="button" id="btn-manual-search">
                                ស្វែងរក
                            </button>
                        </div>
                        <div class="form-text small text-muted mt-1">
                            សម្រាប់សិក្ខាកាមដែលភ្លេចកូដ QR ឬទូរស័ព្ទអស់ថ្ម។
                        </div>
                        <div id="manual-results" class="mt-2" style="display: none; max-height: 250px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Scanned Participant Details & Recent Check-ins -->
            <div class="col-lg-6">
                <!-- Scanned Participant Info Card -->
                <div class="card shadow-sm border-0 rounded-3 mb-3" id="participant-info-card" style="display: none;">
                    <div class="card-body text-center p-4">
                        <div class="mb-3 position-relative d-inline-block">
                            <div id="pi-photo-box">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow" style="width: 85px; height: 85px; font-size: 1.9rem;" id="pi-initials"></div>
                                <img id="pi-photo-img" src="" alt="Profile" class="rounded-circle shadow border border-3 border-success" style="width: 85px; height: 85px; object-fit: cover; display: none;">
                            </div>
                        </div>

                        <h4 id="pi-name" class="fw-bold text-dark mb-1"></h4>
                        <div class="mb-2">
                            <span id="pi-province" class="badge bg-primary px-3 py-1 me-1"></span>
                            <span id="pi-ticket" class="badge bg-secondary"></span>
                            <span id="pi-vip" class="badge bg-warning text-dark" style="display: none;"><i class="bi bi-star-fill"></i> VIP</span>
                        </div>
                        <p id="pi-company" class="text-muted small mb-3"></p>

                        <div class="p-3 bg-light rounded-3 text-start border small">
                            <div class="row g-2">
                                <div class="col-6">
                                    <span class="text-muted d-block">កូដចុះឈ្មោះ</span>
                                    <strong id="pi-code" class="text-primary font-monospace"></strong>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block">លេខទូរស័ព្ទ</span>
                                    <strong id="pi-phone"></strong>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block">ម៉ោងស្កេនវត្តមាន</span>
                                    <strong id="pi-time" class="text-success"></strong>
                                </div>
                            </div>
                        </div>

                        <div id="already-in-msg" class="alert alert-warning py-2 mt-3 mb-0 small" style="display: none;"></div>
                    </div>
                </div>

                <!-- Ready State Card -->
                <div id="waiting-state" class="card border-0 shadow-sm rounded-3 p-4 text-center text-muted mb-3" style="min-height: 230px;">
                    <div class="my-auto py-3">
                        <i class="bi bi-qr-code-scan display-4 text-primary mb-2 d-block"></i>
                        <h5 class="fw-bold text-dark">តុជំនួយការត្រៀមរួចរាល់</h5>
                        <p class="small text-muted mb-0">សូមតម្រង់កាមេរ៉ាទៅកាន់កូដ QR របស់សិក្ខាកាម ឬវាយឈ្មោះស្វែងរក</p>
                    </div>
                </div>

                <!-- Recent Check-ins Card -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark">
                            <i class="bi bi-clock-history text-primary me-1"></i> វត្តមានដែលទើបស្កេនថ្មីៗ
                        </span>
                        <span class="badge bg-light text-muted border" id="recent-count"><?= count($recentAttendance ?? []) ?> នាក់</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ឈ្មោះសិក្ខាកាម</th>
                                        <th>ខេត្ត / ស្ថាប័ន</th>
                                        <th class="text-end pe-3">ម៉ោងស្កេន</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-attendance-tbody">
                                    <?php if (!empty($recentAttendance)): ?>
                                        <?php foreach ($recentAttendance as $ra): ?>
                                            <tr>
                                                <td class="ps-3 fw-bold text-dark text-nowrap"><?= htmlspecialchars($ra['name']) ?></td>
                                                <td class="text-muted small text-truncate" style="max-width: 150px;">
                                                    <span class="badge bg-primary-subtle text-primary border me-1"><?= htmlspecialchars($ra['province']) ?></span>
                                                    <?= htmlspecialchars($ra['company']) ?>
                                                </td>
                                                <td class="text-end pe-3 text-success fw-bold text-nowrap"><?= date('H:i:s', strtotime($ra['checked_in_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="no-recent-row"><td colspan="3" class="text-center py-4 text-muted small">មិនទាន់មានការស្កេននៅឡើយទេ។</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiration Modal -->
    <div class="modal fade" id="expiredModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-4 border-0 shadow-lg rounded-4">
                <i class="bi bi-clock-history text-danger display-3 mb-2"></i>
                <h5 class="fw-bold mb-2">លីងបានផុតសុពលភាព</h5>
                <p class="small text-muted mb-3">សុពលភាពនៃតុស្កេនជំនួយការនេះបានបញ្ចប់ហើយ។ សូមទាក់ទងអ្នកគ្រប់គ្រងប្រសិនបើត្រូវការបន្ត។</p>
                <a href="<?= APP_URL ?>" class="btn btn-primary fw-semibold">យល់ព្រម</a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const PASS_TOKEN = '<?= $pass['token'] ?>';
        const EXPIRES_AT = new Date('<?= date('c', strtotime($pass['expires_at'])) ?>').getTime();
        const APP_URL = '<?= APP_URL ?>';
        const DEVICE_TOKEN = '<?= htmlspecialchars($devToken ?? '') ?>';

        let html5QrcodeScanner = null;
        let isScanning = true;
        let scanTimeout = null;

        // Beep Feedback Audio
        function playBeep(type = 'success') {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                if (type === 'success') {
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.2);
                } else if (type === 'warning') {
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(440, ctx.currentTime);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                } else {
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, ctx.currentTime);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                }
            } catch (e) {}
        }

        // Expiration Countdown Timer
        function updateCountdown() {
            const now = new Date().getTime();
            const diff = EXPIRES_AT - now;
            const timerEl = document.getElementById('countdown-timer');
            if (diff <= 0) {
                if (timerEl) timerEl.innerText = 'ផុតកំណត់';
                const modal = new bootstrap.Modal(document.getElementById('expiredModal'));
                modal.show();
                return;
            }
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            if (timerEl) {
                if (hours > 0) {
                    timerEl.innerText = hours + ' ម៉ោង ' + minutes + ' នាទី';
                } else {
                    timerEl.innerText = minutes + ' នាទី ' + seconds + ' វិ.';
                }
            }
        }
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Scanner Initialization
        function initScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                {
                    fps: 20,
                    qrbox: function(w, h) {
                        const edge = Math.max(180, Math.floor(Math.min(w, h) * 0.72));
                        return { width: edge, height: edge };
                    },
                    aspectRatio: 1.0,
                    videoConstraints: {
                        facingMode: "environment"
                    },
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true
                    }
                },
                false
            );
            html5QrcodeScanner.render(onScanSuccess, () => {});

            // Auto-translate default HTML5 QR buttons
            const observer = new MutationObserver(() => {
                const permBtn = document.getElementById('html5-qrcode-button-camera-permission');
                if (permBtn && !permBtn.dataset.kh) {
                    permBtn.dataset.kh = "1";
                    permBtn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> អនុញ្ញាតបើកកាមេរ៉ាស្កេន';
                }
                const stopBtn = document.getElementById('html5-qrcode-button-camera-stop');
                if (stopBtn && !stopBtn.dataset.kh) {
                    stopBtn.dataset.kh = "1";
                    stopBtn.innerHTML = '<i class="bi bi-stop-circle me-1"></i> បិទស្កេន';
                }
                const startBtn = document.getElementById('html5-qrcode-button-camera-start');
                if (startBtn && !startBtn.dataset.kh) {
                    startBtn.dataset.kh = "1";
                    startBtn.innerHTML = '<i class="bi bi-camera-video me-1"></i> ចាប់ផ្តើមកាមេរ៉ា';
                }
            });
            observer.observe(document.getElementById('reader'), { childList: true, subtree: true });

            // Camera selector setup
            if (typeof Html5Qrcode !== 'undefined' && Html5Qrcode.getCameras) {
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length > 1) {
                        const sel = document.getElementById('camera-select');
                        const wrap = document.getElementById('camera-select-wrapper');
                        wrap.style.display = 'block';
                        sel.innerHTML = '';
                        devices.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d.id;
                            opt.textContent = d.label || ('កាមេរ៉ា ' + d.id.substring(0,6));
                            sel.appendChild(opt);
                        });
                        sel.addEventListener('change', function() {
                            localStorage.setItem('helper_cam_id', this.value);
                            location.reload();
                        });
                    }
                }).catch(() => {});
            }
        }

        function onScanSuccess(decodedText) {
            if (!isScanning) return;
            isScanning = false;
            if (html5QrcodeScanner) {
                try { html5QrcodeScanner.pause(); } catch(e){}
            }
            processScan(decodedText);
        }

        function processScan(qrToken) {
            const fd = new FormData();
            fd.append('pass_token', PASS_TOKEN);
            fd.append('qr_token', qrToken);
            fd.append('device_token', DEVICE_TOKEN);

            fetch(APP_URL + '/api/checkin/helper-scan', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                if (data.revoked) {
                    alert(data.message || 'ការអនុញ្ញាតរបស់អ្នកត្រូវបានបិទដោយអ្នកគ្រប់គ្រង។');
                    location.reload();
                    return;
                }
                showResult(data);
                if (data.participant) {
                    updateParticipantCard(data);
                }
                if (data.success && !data.already_in && data.participant) {
                    addRecent(data.participant);
                }
            })
            .catch(err => {
                showResult({ success: false, message: 'បញ្ហាភ្ជាប់បណ្តាញ ឬប្រព័ន្ធមិនឆ្លើយតប។' });
            });
        }

        function showResult(data) {
            const overlay = document.getElementById('scan-result');
            const icon = document.getElementById('scan-icon');
            const msg = document.getElementById('scan-message');
            const nameEl = document.getElementById('scan-participant-name');

            overlay.className = 'scan-result-overlay';
            overlay.style.display = 'flex';

            if (data.success && !data.already_in) {
                playBeep('success');
                overlay.classList.add('scan-success');
                icon.className = 'bi bi-check-circle-fill display-1 mb-2';
                // update stats
                const st = document.getElementById('stats-checked-in');
                if (st) {
                    const parts = st.innerText.split('/');
                    if (parts.length === 2) {
                        st.innerText = (parseInt(parts[0].trim()) + 1) + ' / ' + parts[1].trim();
                    }
                }
            } else if (data.success && data.already_in) {
                playBeep('warning');
                overlay.classList.add('scan-warning');
                icon.className = 'bi bi-exclamation-triangle-fill display-1 mb-2';
            } else {
                playBeep('error');
                overlay.classList.add('scan-error');
                icon.className = 'bi bi-x-circle-fill display-1 mb-2';
            }

            msg.innerText = data.message || 'កំហុស';
            nameEl.innerText = data.participant ? data.participant.name : '';

            const fastMode = document.getElementById('fastModeToggle').checked;
            if (scanTimeout) clearTimeout(scanTimeout);

            if (fastMode) {
                scanTimeout = setTimeout(resetScanner, 1800);
            } else {
                overlay.onclick = resetScanner;
            }
        }

        function resetScanner() {
            const overlay = document.getElementById('scan-result');
            overlay.style.display = 'none';
            overlay.onclick = null;
            isScanning = true;
            if (html5QrcodeScanner) {
                try { html5QrcodeScanner.resume(); } catch(e){}
            }
        }

        function updateParticipantCard(data) {
            const p = data.participant;
            document.getElementById('waiting-state').style.display = 'none';
            document.getElementById('participant-info-card').style.display = 'block';

            document.getElementById('pi-name').innerText = p.name || '';
            document.getElementById('pi-company').innerText = p.company || '';
            document.getElementById('pi-province').innerText = p.province || 'ទូទៅ';
            document.getElementById('pi-ticket').innerText = p.ticket || 'ស្តង់ដារ';
            document.getElementById('pi-code').innerText = p.reg_code || '';
            document.getElementById('pi-phone').innerText = p.phone || '-';
            document.getElementById('pi-time').innerText = p.checked_in || 'ទើបស្កេន';

            const photoImg = document.getElementById('pi-photo-img');
            const initialsEl = document.getElementById('pi-initials');
            if (p.photo_url) {
                photoImg.src = p.photo_url;
                photoImg.style.display = 'inline-block';
                initialsEl.style.display = 'none';
            } else {
                photoImg.style.display = 'none';
                initialsEl.style.display = 'inline-flex';
                initialsEl.innerText = p.name ? p.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : '?';
            }

            const vipEl = document.getElementById('pi-vip');
            if (vipEl) vipEl.style.display = p.is_vip ? 'inline-block' : 'none';

            const warnMsg = document.getElementById('already-in-msg');
            if (data.already_in) {
                warnMsg.style.display = 'block';
                warnMsg.innerText = data.message;
            } else {
                warnMsg.style.display = 'none';
            }
        }

        function addRecent(p) {
            const tbody = document.getElementById('recent-attendance-tbody');
            const noRow = document.getElementById('no-recent-row');
            if (noRow) noRow.remove();

            const tr = document.createElement('tr');
            const timeStr = new Date().toTimeString().split(' ')[0];
            tr.innerHTML = `
                <td class="ps-3 fw-bold text-dark text-nowrap">${escapeHtml(p.name)}</td>
                <td class="text-muted small text-truncate" style="max-width: 150px;">
                    <span class="badge bg-primary-subtle text-primary border me-1">${escapeHtml(p.province)}</span>
                    ${escapeHtml(p.company)}
                </td>
                <td class="text-end pe-3 text-success fw-bold text-nowrap">${timeStr}</td>
            `;
            tbody.insertBefore(tr, tbody.firstChild);

            const countEl = document.getElementById('recent-count');
            if (countEl) {
                const cur = parseInt(countEl.innerText) || 0;
                countEl.innerText = (cur + 1) + ' នាក់';
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        // Manual Search
        const manualInput = document.getElementById('manual-input');
        const btnManualSearch = document.getElementById('btn-manual-search');
        const manualResults = document.getElementById('manual-results');

        function doManualSearch() {
            const q = manualInput.value.trim();
            if (!q) {
                manualResults.style.display = 'none';
                return;
            }

            btnManualSearch.disabled = true;
            btnManualSearch.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            const fd = new FormData();
            fd.append('pass_token', PASS_TOKEN);
            fd.append('query', q);

            fetch(APP_URL + '/api/checkin/helper-search', {
                method: 'POST',
                body: fd
            })
            .then(res => res.json())
            .then(res => {
                btnManualSearch.disabled = false;
                btnManualSearch.innerText = 'ស្វែងរក';
                if (!res.data || res.data.length === 0) {
                    manualResults.innerHTML = '<div class="alert alert-light border small text-muted text-center py-2 mb-0">រកមិនឃើញសិក្ខាកាមដែលមានទិន្នន័យនេះឡើយ។</div>';
                    manualResults.style.display = 'block';
                    return;
                }

                let html = '<div class="list-group list-group-flush border rounded-3">';
                res.data.forEach(item => {
                    const isAttended = item.checked_in_at !== null;
                    html += `
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3">
                            <div>
                                <div class="fw-bold text-dark small">${escapeHtml(item.name)}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">${escapeHtml(item.registration_code)} | ${escapeHtml(item.phone || '-')} | ${escapeHtml(item.company)}</div>
                            </div>
                            <div>
                                ${isAttended ? '<span class="badge bg-success-subtle text-success border small">បានស្កេនរួច</span>' :
                                `<button class="btn btn-sm btn-primary py-1 px-2 fw-semibold" style="font-size: 0.75rem;" onclick="processScan('${item.token || item.registration_code}')">កត់ត្រា</button>`}
                            </div>
                        </div>
                    `;
                });
                html += '</div>';
                manualResults.innerHTML = html;
                manualResults.style.display = 'block';
            })
            .catch(() => {
                btnManualSearch.disabled = false;
                btnManualSearch.innerText = 'ស្វែងរក';
            });
        }

        btnManualSearch.addEventListener('click', doManualSearch);
        manualInput.addEventListener('keydown', e => { if (e.key === 'Enter') doManualSearch(); });

        // Start Scanner
        document.addEventListener('DOMContentLoaded', initScanner);
    </script>
</body>
</html>
