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
        body {
            font-family: 'Kantumruy Pro', -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }
        .main-container { max-width: 1200px; margin: 0 auto; }

        /* Stats Bar */
        .stats-bar {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            padding: 12px 18px;
        }
        .header-avatar { width: 42px; height: 42px; flex-shrink: 0; }
        .stat-counter-pill {
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 50rem; padding: 4px 14px;
        }
        .fast-mode-pill {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 50rem; padding: 4px 12px;
        }
        .fast-mode-pill .form-check-input {
            width: 1.9rem; height: 1.1rem;
            margin: 0 !important; float: none !important;
        }

        /* ─── Camera Scanner ─── */
        .scanner-card-body { padding: 0; }
        .camera-view-wrap {
            position: relative;
            background: #0f172a;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
            /* Fixed 4:3 aspect-ratio so no jump on mobile */
            aspect-ratio: 4 / 3;
        }
        #qr-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Laser corner reticle */
        .laser-reticle {
            position: absolute; inset: 0;
            pointer-events: none; z-index: 10;
            display: flex; align-items: center; justify-content: center;
        }
        .reticle-box {
            position: relative;
            width: 64%; max-width: 220px;
            aspect-ratio: 1;
        }
        .reticle-box::before, .reticle-box::after,
        .reticle-inner::before, .reticle-inner::after {
            content: ''; position: absolute;
            width: 20px; height: 20px;
            border-color: #22d3ee; border-style: solid;
        }
        .reticle-box::before  { top:-2px; left:-2px;   border-width:3px 0 0 3px;   border-top-left-radius:10px; }
        .reticle-box::after   { top:-2px; right:-2px;  border-width:3px 3px 0 0;   border-top-right-radius:10px; }
        .reticle-inner::before{ bottom:-2px; left:-2px; border-width:0 0 3px 3px;  border-bottom-left-radius:10px; }
        .reticle-inner::after { bottom:-2px; right:-2px;border-width:0 3px 3px 0;  border-bottom-right-radius:10px; }
        .laser-line {
            position: absolute; left:5px; right:5px; top:5px; height:2px;
            background: linear-gradient(90deg,transparent,#22d3ee,transparent);
            box-shadow: 0 0 8px #22d3ee;
            animation: laserSweep 1.8s ease-in-out infinite alternate;
        }
        @keyframes laserSweep {
            to { top: calc(100% - 7px); }
        }
        .reticle-hint {
            position: absolute; bottom: -36px; left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            color: #fff;
            font-size: 0.7rem;
            background: rgba(15,23,42,0.7);
            padding: 2px 12px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.12);
        }

        /* Tap-focus ring */
        .focus-ring {
            position: absolute; width: 52px; height: 52px;
            border: 2px solid #facc15;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(250,204,21,0.7);
            pointer-events: none; z-index: 20;
            transform: translate(-50%,-50%) scale(1.3);
            opacity: 0; transition: transform .22s cubic-bezier(.175,.885,.32,1.275), opacity .25s;
        }
        .focus-ring.show { transform: translate(-50%,-50%) scale(1); opacity:1; }

        /* Scan result overlay */
        .scan-overlay {
            position: absolute; inset: 0; z-index: 30;
            display: none; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; padding: 20px;
            cursor: pointer;
        }
        .scan-overlay.success { background: rgba(21,128,61,.93); color:#fff; }
        .scan-overlay.warning { background: rgba(180,83,9,.93); color:#fff; }
        .scan-overlay.error   { background: rgba(185,28,28,.93); color:#fff; }

        /* ─── Camera Toolbar ─── */
        .cam-toolbar {
            background: #1e293b;
            border-radius: 0 0 10px 10px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            overflow-x: auto;
        }
        .cam-toolbar::-webkit-scrollbar { display: none; }

        .cam-toolbar select {
            flex: 1; min-width: 0;
            background: #334155; color: #e2e8f0;
            border: 1px solid #475569;
            border-radius: 8px;
            font-size: 0.73rem; height: 32px;
            padding: 0 8px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            padding-right: 24px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        .cam-btn {
            flex-shrink: 0;
            display: inline-flex; align-items: center; gap: 5px;
            height: 32px; padding: 0 12px;
            border-radius: 8px; border: none;
            font-size: 0.73rem; font-weight: 600;
            cursor: pointer; white-space: nowrap;
            transition: opacity .15s;
        }
        .cam-btn:active { opacity: .75; }
        .cam-btn-start  { background: #166534; color: #fff; }
        .cam-btn-stop   { background: #991b1b; color: #fff; }
        .cam-btn-torch  { background: #334155; color: #e2e8f0; }
        .cam-btn-torch.on { background: #d97706; color: #fff; }
        .cam-btn-zoom   { background: #334155; color: #e2e8f0; min-width: 46px; justify-content: center; }
        .cam-btn-zoom.on{ background: #0d6efd; color: #fff; }
        .cam-btn-focus  { background: #334155; color: #94a3b8; }
        #cam-status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            background: #475569;
            transition: background .3s;
        }
        #cam-status-dot.active { background: #22c55e; box-shadow: 0 0 6px #22c55e; }

        /* No-camera placeholder */
        .cam-placeholder {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #94a3b8; gap: 10px;
        }

        /* Result overlay */
        .scan-overlay { border-radius: 10px 10px 0 0; }

        /* Mobile */
        @media (max-width: 576px) {
            .stats-bar { padding: 10px 12px; }
            .header-avatar { width: 36px !important; height: 36px !important; }
            .cam-toolbar { padding: 7px 10px; gap: 6px; }
            .cam-btn { height: 30px; padding: 0 10px; font-size: 0.7rem; }
            .cam-toolbar select { font-size: 0.7rem; height: 30px; }
            .table th, .table td { padding: 5px 7px !important; font-size: 0.77rem !important; }
            #waiting-state { min-height: auto !important; padding: 14px !important; }
        }
    </style>
</head>
<body>
<div class="main-container py-3 px-3">

    <!-- ── Top Stats Bar ── -->
    <div class="stats-bar mb-3">
        <div class="row align-items-center g-2">
            <!-- Left: Info -->
            <div class="col-12 col-md-auto me-md-auto">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm header-avatar">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size:.95rem;"><?= htmlspecialchars($pass['workshop_name']) ?></h6>
                            <span class="badge bg-primary-subtle text-primary border rounded-pill" style="font-size:.68rem;"><?= htmlspecialchars($pass['label']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-warning-subtle text-dark border rounded-pill" id="countdown-badge" style="font-size:.68rem;">
                                <i class="bi bi-clock-history text-danger me-1"></i>នៅសល់&nbsp;<strong id="countdown-timer">...</strong>
                            </span>
                            <?php if (!empty($device['helper_name'])): ?>
                            <span class="badge bg-primary text-white border rounded-pill" style="font-size:.68rem;">
                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($device['helper_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right: Counter + Toggle -->
            <div class="col-12 col-md-auto">
                <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2 pt-2 pt-md-0 border-top border-md-0">
                    <div class="stat-counter-pill d-flex align-items-center gap-1">
                        <span class="text-secondary" style="font-size:.76rem;">បានស្កេន៖</span>
                        <strong class="text-success" style="font-size:1rem;" id="stats-checked-in"><?= (int)($stats['checked_in']??0) ?>&nbsp;/&nbsp;<?= (int)($stats['total_confirmed']??0) ?></strong>
                    </div>
                    <div class="fast-mode-pill d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="fastModeToggle" checked>
                        <label class="form-check-label user-select-none mb-0" for="fastModeToggle" style="font-size:.78rem;cursor:pointer;">ស្កេនលឿន</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Guidance Banner ── -->
    <div class="alert alert-light border shadow-sm mb-3 py-2 px-3 rounded-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="small text-dark" style="font-size:.78rem;">
                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                <strong>ណែនាំ៖</strong> ស្កេន QR ឬវាយឈ្មោះ/លេខទូរស័ព្ទ រួចចុច <strong>កត់ត្រា</strong>
            </span>
            <span class="badge bg-success-subtle text-success border flex-shrink-0" style="font-size:.68rem;">ដំណើរការ</span>
        </div>
    </div>

    <!-- ── Main 2-Column Layout ── -->
    <div class="row g-3 g-md-4">

        <!-- Left: Camera + Manual -->
        <div class="col-lg-6">
            <!-- Camera Card -->
            <div class="card shadow-sm border-0 mb-3 rounded-3 overflow-hidden">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
                    <span class="fw-bold small d-flex align-items-center gap-1">
                        <i class="bi bi-camera-video-fill text-primary"></i> ស្កេនកូដ QR
                    </span>
                    <span class="d-flex align-items-center gap-1 small text-muted">
                        <span id="cam-status-dot"></span>
                        <span id="cam-status-text" style="font-size:.7rem;">រង់ចាំ…</span>
                    </span>
                </div>

                <!-- Camera Viewport -->
                <div class="camera-view-wrap" id="camera-view-wrap">
                    <video id="qr-video" autoplay muted playsinline></video>

                    <!-- Placeholder before camera starts -->
                    <div class="cam-placeholder" id="cam-placeholder">
                        <i class="bi bi-camera-video-off fs-1 text-muted"></i>
                        <div style="font-size:.8rem;">ចុច <strong class="text-success">ចាប់ផ្តើម</strong> ដើម្បីបើកកាមេរ៉ា</div>
                    </div>

                    <!-- Laser Reticle (visible while scanning) -->
                    <div class="laser-reticle" id="laser-reticle" style="display:none;">
                        <div class="reticle-box">
                            <div class="reticle-inner"></div>
                            <div class="laser-line"></div>
                            <div class="reticle-hint"><i class="bi bi-crosshair me-1"></i>ចុចលើអេក្រង់ Focus</div>
                        </div>
                    </div>

                    <!-- Tap-Focus Ring -->
                    <div class="focus-ring" id="focus-ring"></div>

                    <!-- Scan Result Overlay (click to dismiss) -->
                    <div class="scan-overlay" id="scan-overlay" onclick="resumeAfterScan()">
                        <i id="scan-icon" class="bi display-1 mb-2"></i>
                        <h4 id="scan-message" class="fw-bold mb-1 px-2"></h4>
                        <div id="scan-name" class="fw-bold" style="font-size:1.1rem;"></div>
                        <div class="mt-2 text-white-50" style="font-size:.72rem;">ចុចអេក្រង់ ឬ រង់ចាំដើម្បីស្កេនបន្ត</div>
                    </div>
                </div>

                <!-- ── Camera Toolbar ── -->
                <div class="cam-toolbar" id="cam-toolbar">
                    <!-- Camera Selector -->
                    <select id="cam-select" style="display:none;" title="ជ្រើសរើសកាមេរ៉ា"></select>

                    <!-- Start / Stop -->
                    <button class="cam-btn cam-btn-start" id="btn-cam-start" onclick="startCamera()">
                        <i class="bi bi-play-fill"></i> ចាប់ផ្តើម
                    </button>
                    <button class="cam-btn cam-btn-stop" id="btn-cam-stop" onclick="stopCamera()" style="display:none;">
                        <i class="bi bi-stop-fill"></i> បិទ
                    </button>

                    <!-- Torch (shown only if supported) -->
                    <button class="cam-btn cam-btn-torch" id="btn-torch" onclick="toggleTorch()" style="display:none;" title="ពិល">
                        <i class="bi bi-lightbulb"></i>
                    </button>

                    <!-- Zoom (shown only if supported) -->
                    <button class="cam-btn cam-btn-zoom" id="btn-zoom" onclick="toggleZoom()" style="display:none;" title="Zoom">
                        <i class="bi bi-zoom-in"></i>&nbsp;1x
                    </button>

                    <!-- Focus -->
                    <button class="cam-btn cam-btn-focus" id="btn-focus" onclick="triggerFocus()" style="display:none;" title="Focus">
                        <i class="bi bi-crosshair"></i>
                    </button>
                </div>
            </div>

            <!-- Manual Search Card -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-2 px-3">
                    <span class="fw-bold small"><i class="bi bi-search text-success me-1"></i> ស្វែងរក ឬកត់ត្រាដោយដៃ</span>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white d-none d-sm-flex"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="manual-input" class="form-control" placeholder="វាយឈ្មោះ / លេខទូរស័ព្ទ / កូដ..." autocomplete="off">
                        <button class="btn btn-primary fw-semibold px-3 text-nowrap" id="btn-manual-search" style="font-size:.85rem;">ស្វែងរក</button>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.73rem;">សម្រាប់សិក្ខាកាមដែលភ្លេចកូដ QR ឬទូរស័ព្ទអស់ថ្ម។</div>
                    <div id="manual-results" class="mt-2" style="display:none;max-height:250px;overflow-y:auto;"></div>
                </div>
            </div>
        </div>

        <!-- Right: Participant Info + Recent List -->
        <div class="col-lg-6">
            <!-- Participant Card (hidden until scan) -->
            <div class="card shadow-sm border-0 rounded-3 mb-3" id="participant-info-card" style="display:none;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <div id="pi-initials" class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow" style="width:72px;height:72px;font-size:1.6rem;"></div>
                        <img id="pi-photo-img" src="" alt="Profile" class="rounded-circle shadow border border-3 border-success" style="width:72px;height:72px;object-fit:cover;display:none;">
                    </div>
                    <h5 id="pi-name" class="fw-bold text-dark mb-1"></h5>
                    <div class="mb-2">
                        <span id="pi-province" class="badge bg-primary me-1" style="font-size:.72rem;"></span>
                        <span id="pi-ticket" class="badge bg-secondary" style="font-size:.72rem;"></span>
                        <span id="pi-vip" class="badge bg-warning text-dark" style="display:none;font-size:.72rem;"><i class="bi bi-star-fill"></i> VIP</span>
                    </div>
                    <p id="pi-company" class="text-muted mb-2" style="font-size:.78rem;"></p>
                    <div class="bg-light rounded-3 border p-2 text-start">
                        <div class="row g-1">
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.68rem;">កូដចុះឈ្មោះ</div>
                                <strong id="pi-code" class="text-primary font-monospace small"></strong>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.68rem;">លេខទូរស័ព្ទ</div>
                                <strong id="pi-phone" class="small"></strong>
                            </div>
                            <div class="col-12">
                                <div class="text-muted" style="font-size:.68rem;">ម៉ោងស្កេន</div>
                                <strong id="pi-time" class="text-success small"></strong>
                            </div>
                        </div>
                    </div>
                    <div id="already-in-msg" class="alert alert-warning py-1 mt-2 mb-0 small" style="display:none;"></div>
                </div>
            </div>

            <!-- Ready State -->
            <div id="waiting-state" class="card border-0 shadow-sm rounded-3 p-3 text-center text-muted mb-3" style="min-height:200px;">
                <div class="m-auto py-2">
                    <i class="bi bi-qr-code-scan text-primary mb-2 d-block" style="font-size:2.4rem;"></i>
                    <h6 class="fw-bold text-dark">តុជំនួយការត្រៀមរួចរាល់</h6>
                    <p class="small mb-0" style="font-size:.78rem;">ចុច <strong>ចាប់ផ្តើម</strong> ចំណុចខាងក្រោម ហើយតម្រង់កូដ QR ទៅកាន់កាមេរ៉ា</p>
                </div>
            </div>

            <!-- Recent Check-ins -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small"><i class="bi bi-clock-history text-primary me-1"></i> វត្តមានដែលទើបស្កេន</span>
                    <span class="badge bg-light text-muted border" id="recent-count" style="font-size:.7rem;"><?= count($recentAttendance??[]) ?> នាក់</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size:.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">ឈ្មោះ</th>
                                    <th>ខេត្ត</th>
                                    <th class="text-end pe-3">ម៉ោង</th>
                                </tr>
                            </thead>
                            <tbody id="recent-tbody">
                                <?php if (!empty($recentAttendance)): ?>
                                    <?php foreach ($recentAttendance as $ra): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark text-nowrap"><?= htmlspecialchars($ra['name']) ?></td>
                                        <td class="text-muted small text-truncate" style="max-width:120px;">
                                            <span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($ra['province']) ?></span>
                                        </td>
                                        <td class="text-end pe-3 text-success fw-bold text-nowrap"><?= date('H:i:s', strtotime($ra['checked_in_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="no-recent-row"><td colspan="3" class="text-center py-4 text-muted small">មិនទាន់មានការស្កេននៅឡើយ</td></tr>
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
            <p class="small text-muted mb-3">សូមទាក់ទងអ្នកគ្រប់គ្រង ដើម្បីបន្ត។</p>
            <a href="<?= APP_URL ?>" class="btn btn-primary fw-semibold">យល់ព្រម</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Use Html5Qrcode LIBRARY (not Scanner wrapper) for full UI control -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const PASS_TOKEN   = '<?= $pass['token'] ?>';
const EXPIRES_AT   = new Date('<?= date('c', strtotime($pass['expires_at'])) ?>').getTime();
const APP_URL      = '<?= APP_URL ?>';
const DEVICE_TOKEN = '<?= htmlspecialchars($devToken ?? '') ?>';

// ─── State ───────────────────────────────────────────
let html5Qrcode   = null;
let videoTrack    = null;
let isActive      = false;   // camera running?
let isPaused      = false;   // result overlay shown?
let torchOn       = false;
let zoomLevel     = 1;
let focusTimer    = null;
let scanTimeout   = null;
let afInterval    = null;
let cameraList    = [];
let activeCamId   = null;

// ─── Beep ─────────────────────────────────────────────
function playBeep(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = ctx.createOscillator(), gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        if (type === 'ok') {
            osc.type = 'sine';
            osc.frequency.setValueAtTime(820, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + .14);
            gain.gain.setValueAtTime(.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .18);
            osc.start(); osc.stop(ctx.currentTime + .2);
        } else if (type === 'warn') {
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(460, ctx.currentTime);
            gain.gain.setValueAtTime(.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .28);
            osc.start(); osc.stop(ctx.currentTime + .3);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(200, ctx.currentTime);
            gain.gain.setValueAtTime(.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(.001, ctx.currentTime + .28);
            osc.start(); osc.stop(ctx.currentTime + .3);
        }
    } catch(e) {}
}

// ─── Countdown ────────────────────────────────────────
function updateCountdown() {
    const diff = EXPIRES_AT - Date.now();
    const el = document.getElementById('countdown-timer');
    if (!el) return;
    if (diff <= 0) {
        el.innerText = 'ផុតកំណត់';
        new bootstrap.Modal(document.getElementById('expiredModal')).show();
        return;
    }
    const h = Math.floor(diff / 3600000), m = Math.floor((diff % 3600000) / 60000), s = Math.floor((diff % 60000) / 1000);
    el.innerText = h > 0 ? `${h} ម៉ោង ${m} នាទី` : `${m}:${String(s).padStart(2,'0')}`;
}
setInterval(updateCountdown, 1000); updateCountdown();

// ─── Camera Start / Stop ─────────────────────────────
async function loadCameraList() {
    try {
        const devices = await Html5Qrcode.getCameras();
        cameraList = devices || [];
        const sel = document.getElementById('cam-select');
        sel.innerHTML = '';
        cameraList.forEach((d, i) => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.label || `កាមេរ៉ា ${i+1}`;
            sel.appendChild(opt);
        });
        // Prefer rear camera
        const rear = cameraList.find(d => /back|rear|environment/i.test(d.label));
        if (rear) { sel.value = rear.id; activeCamId = rear.id; }
        else if (cameraList.length) activeCamId = cameraList[0].id;

        if (cameraList.length > 1) sel.style.display = 'block';

        sel.addEventListener('change', async function() {
            activeCamId = this.value;
            if (isActive) { await stopCamera(); await startCamera(); }
        });
    } catch(e) { console.warn('getCameras:', e); }
}

async function startCamera() {
    setStatus('loading', 'ចាប់ផ្តើម…');
    document.getElementById('btn-cam-start').style.display = 'none';
    document.getElementById('btn-cam-stop').style.display  = 'inline-flex';
    document.getElementById('cam-placeholder').style.display = 'none';

    if (!html5Qrcode) {
        html5Qrcode = new Html5Qrcode("qr-reader-hidden");
    }

    const config = {
        fps: 30,
        // NO qrbox → scans the ENTIRE frame — fastest possible detection
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
        videoConstraints: {
            deviceId: activeCamId ? { exact: activeCamId } : undefined,
            facingMode: activeCamId ? undefined : { ideal: 'environment' },
            width:  { min: 640, ideal: 1280 },
            height: { min: 480, ideal: 720 },
        }
    };

    try {
        await html5Qrcode.start(
            activeCamId || { facingMode: 'environment' },
            config,
            onScanSuccess,
            () => {}    // ignore non-QR-found errors
        );

        // Hijack the video element html5-qrcode creates
        // and move it into our styled viewport
        const origVideo = document.querySelector('#qr-reader-hidden video');
        if (origVideo) {
            origVideo.id = 'qr-video';
            origVideo.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
            document.getElementById('camera-view-wrap').prepend(origVideo);
        }

        isActive = true; isPaused = false;
        setStatus('active', 'ដំណើរការ');
        document.getElementById('laser-reticle').style.display = 'flex';
        document.getElementById('btn-focus').style.display = 'inline-flex';

        // Detect hardware capabilities after short delay
        setTimeout(setupHardwareCaps, 800);

        // Auto-refocus every 5s
        if (afInterval) clearInterval(afInterval);
        afInterval = setInterval(() => { if (isActive && !isPaused) applyFocus(); }, 5000);

    } catch(err) {
        console.error('Camera start error:', err);
        setStatus('off', 'បរាជ័យ — ផ្ដល់ Permission');
        document.getElementById('btn-cam-start').style.display = 'inline-flex';
        document.getElementById('btn-cam-stop').style.display  = 'none';
        document.getElementById('cam-placeholder').style.display = 'flex';
    }
}

async function stopCamera() {
    if (afInterval) { clearInterval(afInterval); afInterval = null; }
    if (html5Qrcode && isActive) {
        try { await html5Qrcode.stop(); } catch(e) {}
    }
    isActive = false; isPaused = false;
    torchOn = false; zoomLevel = 1;
    document.getElementById('btn-cam-start').style.display = 'inline-flex';
    document.getElementById('btn-cam-stop').style.display  = 'none';
    document.getElementById('btn-torch').style.display     = 'none';
    document.getElementById('btn-zoom').style.display      = 'none';
    document.getElementById('btn-focus').style.display     = 'none';
    document.getElementById('laser-reticle').style.display = 'none';
    document.getElementById('cam-placeholder').style.display = 'flex';
    setStatus('off', 'បានបិទ');
}

// ─── Hardware Capabilities ────────────────────────────
function getTrack() {
    try {
        const v = document.querySelector('#camera-view-wrap video');
        if (v && v.srcObject) return v.srcObject.getVideoTracks()[0] || null;
    } catch(e) {}
    return null;
}

function setupHardwareCaps() {
    const track = getTrack();
    if (!track) return;
    videoTrack = track;

    try {
        const caps = track.getCapabilities ? track.getCapabilities() : {};

        // Continuous autofocus + autoexposure
        const adv = [];
        if (caps.focusMode?.includes('continuous'))   adv.push({ focusMode: 'continuous' });
        if (caps.exposureMode?.includes('continuous')) adv.push({ exposureMode: 'continuous' });
        if (adv.length) track.applyConstraints({ advanced: adv }).catch(()=>{});

        // Torch
        if (caps.torch) document.getElementById('btn-torch').style.display = 'inline-flex';

        // Zoom
        if (caps.zoom && caps.zoom.max > 1.5) {
            const z = document.getElementById('btn-zoom');
            z.style.display = 'inline-flex';
            z.dataset.max = caps.zoom.max;
        }
    } catch(e) {}
}

function applyFocus() {
    const track = getTrack();
    if (!track) return;
    try {
        track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }).catch(() => {
            track.applyConstraints({ advanced: [{ focusMode: 'auto' }] }).catch(()=>{});
        });
    } catch(e) {}
}

function triggerFocus() {
    applyFocus();
    const wrap = document.getElementById('camera-view-wrap');
    if (wrap) {
        const r = wrap.getBoundingClientRect();
        showFocusRing(r.width / 2, r.height / 2);
    }
}

function toggleTorch() {
    const track = getTrack();
    if (!track) return;
    torchOn = !torchOn;
    track.applyConstraints({ advanced: [{ torch: torchOn }] }).then(() => {
        const b = document.getElementById('btn-torch');
        b.classList.toggle('on', torchOn);
        b.innerHTML = torchOn
            ? '<i class="bi bi-lightbulb-fill"></i>'
            : '<i class="bi bi-lightbulb"></i>';
    }).catch(() => { torchOn = !torchOn; });
}

function toggleZoom() {
    const track = getTrack();
    if (!track) return;
    const b   = document.getElementById('btn-zoom');
    const max = parseFloat(b.dataset.max || 2);
    zoomLevel = (zoomLevel === 1) ? Math.min(2, max) : 1;
    track.applyConstraints({ advanced: [{ zoom: zoomLevel }] }).then(() => {
        b.innerHTML = `<i class="bi bi-zoom-in"></i>&nbsp;${zoomLevel}x`;
        b.classList.toggle('on', zoomLevel > 1);
    }).catch(()=>{});
}

// ─── Tap-to-Focus ─────────────────────────────────────
function showFocusRing(x, y) {
    const ring = document.getElementById('focus-ring');
    ring.style.left = x + 'px'; ring.style.top = y + 'px';
    ring.classList.add('show');
    if (focusTimer) clearTimeout(focusTimer);
    focusTimer = setTimeout(() => ring.classList.remove('show'), 900);
    if (navigator.vibrate) try { navigator.vibrate(20); } catch(e){}
}

document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('camera-view-wrap');
    if (wrap) {
        wrap.addEventListener('click', e => {
            if (!isActive || isPaused) return;
            if (e.target.closest('button,select,a')) return;
            const r = wrap.getBoundingClientRect();
            const x = e.clientX - r.left, y = e.clientY - r.top;
            showFocusRing(x, y);
            applyFocus();
        });
    }
});

// ─── Status dot + text ────────────────────────────────
function setStatus(state, text) {
    const dot = document.getElementById('cam-status-dot');
    const txt = document.getElementById('cam-status-text');
    dot.className = state === 'active' ? 'active' : '';
    if (txt) txt.textContent = text;
}

// ─── Scan Success ────────────────────────────────────
function onScanSuccess(decoded) {
    if (isPaused) return;
    isPaused = true;
    if (navigator.vibrate) try { navigator.vibrate(35); } catch(e){}
    document.getElementById('laser-reticle').style.display = 'none';
    processScan(decoded);
}

function processScan(token) {
    const fd = new FormData();
    fd.append('pass_token',  PASS_TOKEN);
    fd.append('qr_token',    token);
    fd.append('device_token', DEVICE_TOKEN);

    fetch(APP_URL + '/api/checkin/helper-scan', { method:'POST', body:fd })
        .then(r => r.json())
        .then(data => {
            if (data.revoked) { alert(data.message); location.reload(); return; }
            showOverlay(data);
            if (data.participant) updateParticipantCard(data);
            if (data.success && !data.already_in && data.participant) addRecent(data.participant);
        })
        .catch(() => showOverlay({ success:false, message:'បញ្ហាភ្ជាប់បណ្តាញ' }));
}

function showOverlay(data) {
    const ov  = document.getElementById('scan-overlay');
    const ico = document.getElementById('scan-icon');
    const msg = document.getElementById('scan-message');
    const nm  = document.getElementById('scan-name');

    ov.className = 'scan-overlay';
    if (data.success && !data.already_in) {
        playBeep('ok');
        if (navigator.vibrate) try { navigator.vibrate(45); } catch(e){}
        ov.classList.add('success');
        ico.className = 'bi bi-check-circle-fill display-1 mb-2';
        // increment counter
        const st = document.getElementById('stats-checked-in');
        if (st) { const p = st.innerText.split('/'); if (p.length===2) st.innerText = (parseInt(p[0])+1) + ' / ' + p[1]; }
    } else if (data.success && data.already_in) {
        playBeep('warn');
        if (navigator.vibrate) try { navigator.vibrate([50,40,50]); } catch(e){}
        ov.classList.add('warning');
        ico.className = 'bi bi-exclamation-triangle-fill display-1 mb-2';
    } else {
        playBeep('err');
        if (navigator.vibrate) try { navigator.vibrate(160); } catch(e){}
        ov.classList.add('error');
        ico.className = 'bi bi-x-circle-fill display-1 mb-2';
    }
    msg.innerText = data.message || 'កំហុស';
    nm.innerText  = data.participant ? data.participant.name : '';
    ov.style.display = 'flex';

    if (scanTimeout) clearTimeout(scanTimeout);
    const fast = document.getElementById('fastModeToggle').checked;
    if (fast) scanTimeout = setTimeout(resumeAfterScan, 1500);
}

function resumeAfterScan() {
    document.getElementById('scan-overlay').style.display = 'none';
    isPaused = false;
    document.getElementById('laser-reticle').style.display = isActive ? 'flex' : 'none';
    applyFocus();
}

// ─── Participant Card ──────────────────────────────────
function updateParticipantCard(data) {
    const p = data.participant;
    document.getElementById('waiting-state').style.display = 'none';
    document.getElementById('participant-info-card').style.display = 'block';
    document.getElementById('pi-name').innerText     = p.name    || '';
    document.getElementById('pi-company').innerText  = p.company || '';
    document.getElementById('pi-province').innerText = p.province|| 'ទូទៅ';
    document.getElementById('pi-ticket').innerText   = p.ticket  || 'ស្តង់ដារ';
    document.getElementById('pi-code').innerText     = p.reg_code|| '';
    document.getElementById('pi-phone').innerText    = p.phone   || '-';
    document.getElementById('pi-time').innerText     = p.checked_in|| 'ទើបស្កេន';

    const img = document.getElementById('pi-photo-img');
    const ini = document.getElementById('pi-initials');
    if (p.photo_url) { img.src=p.photo_url; img.style.display='inline-block'; ini.style.display='none'; }
    else { img.style.display='none'; ini.style.display='inline-flex'; ini.innerText=p.name?p.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase():'?'; }

    const vip  = document.getElementById('pi-vip');
    if (vip) vip.style.display = p.is_vip ? 'inline-block' : 'none';
    const warn = document.getElementById('already-in-msg');
    if (data.already_in) { warn.style.display='block'; warn.innerText=data.message; }
    else warn.style.display='none';
}

function addRecent(p) {
    const tbody = document.getElementById('recent-tbody');
    document.getElementById('no-recent-row')?.remove();
    const tr  = document.createElement('tr');
    const t   = new Date().toTimeString().slice(0,8);
    tr.innerHTML = `
        <td class="ps-3 fw-bold text-dark text-nowrap">${esc(p.name)}</td>
        <td class="text-muted small text-truncate" style="max-width:120px;"><span class="badge bg-primary-subtle text-primary border">${esc(p.province)}</span></td>
        <td class="text-end pe-3 text-success fw-bold text-nowrap">${t}</td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);
    const c = document.getElementById('recent-count');
    if (c) c.innerText = (parseInt(c.innerText)||0) + 1 + ' នាក់';
}

function esc(s) { if (!s) return ''; const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

// ─── Manual Search ────────────────────────────────────
const minput = document.getElementById('manual-input');
const mbtn   = document.getElementById('btn-manual-search');
const mres   = document.getElementById('manual-results');

function doSearch() {
    const q = minput.value.trim(); if (!q) { mres.style.display='none'; return; }
    mbtn.disabled=true; mbtn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
    const fd=new FormData(); fd.append('pass_token',PASS_TOKEN); fd.append('query',q);
    fetch(APP_URL+'/api/checkin/helper-search',{method:'POST',body:fd})
        .then(r=>r.json()).then(res=>{
            mbtn.disabled=false; mbtn.innerText='ស្វែងរក';
            if (!res.data?.length) {
                mres.innerHTML='<div class="alert alert-light border small text-muted text-center py-2 mb-0">រកមិនឃើញ</div>';
            } else {
                let h='<div class="list-group list-group-flush border rounded-3">';
                res.data.forEach(it=>{
                    const done = it.checked_in_at !== null;
                    h+=`<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 py-2 px-2">
                        <div class="min-w-0">
                            <div class="fw-bold small text-truncate">${esc(it.name)}</div>
                            <div class="text-muted text-truncate" style="font-size:.7rem;">${esc(it.registration_code)} | ${esc(it.phone||'-')}</div>
                        </div>
                        <div class="flex-shrink-0">${done?'<span class="badge bg-success-subtle text-success border" style="font-size:.7rem;">បានស្កេន</span>':`<button class="btn btn-sm btn-primary py-0 px-2" style="font-size:.73rem;" onclick="processScan('${esc(it.token||it.registration_code)}')">កត់ត្រា</button>`}</div>
                    </div>`;
                });
                mres.innerHTML = h+'</div>';
            }
            mres.style.display='block';
        }).catch(()=>{ mbtn.disabled=false; mbtn.innerText='ស្វែងរក'; });
}

mbtn.addEventListener('click', doSearch);
minput.addEventListener('keydown', e => { if(e.key==='Enter') doSearch(); });

// ─── Init ──────────────────────────────────────────────
// Hidden container for html5-qrcode internal logic (outside viewport)
const hiddenDiv = document.createElement('div');
hiddenDiv.id = 'qr-reader-hidden';
hiddenDiv.style.cssText = 'position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;pointer-events:none;';
document.body.appendChild(hiddenDiv);

document.addEventListener('DOMContentLoaded', async () => {
    await loadCameraList();
    // Auto-start camera
    await startCamera();
});
</script>
</body>
</html>
