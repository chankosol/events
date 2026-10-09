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
            background: #f8fafc; color: #1e293b; min-height: 100vh;
        }
        .main-container { max-width: 1200px; margin: 0 auto; }

        /* ── Stats Bar ── */
        .stats-bar {
            background: #fff; border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.04);
            border: 1px solid #e2e8f0; padding: 12px 18px;
        }
        .header-avatar { width: 42px; height: 42px; flex-shrink: 0; }
        .stat-pill {
            background: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 50rem; padding: 4px 14px;
        }
        .fast-pill {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 50rem; padding: 4px 12px;
        }
        .fast-pill .form-check-input {
            width:1.9rem; height:1.1rem; margin:0!important; float:none!important;
        }

        /* ─── SQUARE scanner viewport ───────────────────────────
           Strategy: .scanner-wrap holds aspect-ratio:1/1 + overflow:hidden.
           #qr-reader is left as NORMAL FLOW (not absolutely positioned)
           so html5-qrcode can size its internal canvas correctly.
           The tall video overflows the square wrapper and is clipped
           visually by overflow:hidden — the decode canvas is unaffected.
        ──────────────────────────────────────────────────────── */
        .scanner-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1 / 1;   /* square clip window */
            overflow: hidden;       /* clips tall video to square visually */
            border-radius: 10px 10px 0 0;
            background: #0f172a;
        }

        /* Let html5-qrcode manage #qr-reader's own sizing — don't override! */
        #qr-reader {
            width: 100%;
            background: #0f172a;
        }

        /* Hide html5-qrcode native UI (buttons, select, text) — NOT the canvas */
        #qr-reader__dashboard,
        #qr-reader__header_message,
        #qr-reader__status_span,
        #qr-reader__dashboard_section_swaplink,
        #html5-qrcode-anchor-scan-type-change,
        #qr-reader__filescan_input,
        #qr-reader__dashboard_section_filesel { display: none !important; }

        /* Video: fill width, object-fit cover — height stays auto (tall, clipped by wrapper) */
        #qr-reader video {
            width: 100% !important;
            max-width: 100% !important;
            object-fit: cover !important;
            display: block !important;
            border-radius: 0 !important;
        }
        /* The canvas inside scan_region is used for QR decoding — do NOT hide or override it */

        /* ── Our custom laser reticle overlaid on top of video ── */
        .laser-reticle {
            position: absolute; inset: 0; pointer-events: none; z-index: 10;
            display: none; align-items: center; justify-content: center;
        }
        .reticle-box {
            position: relative; width: 62%; max-width: 210px; aspect-ratio: 1;
        }
        .reticle-box::before, .reticle-box::after,
        .reticle-inner::before, .reticle-inner::after {
            content:''; position:absolute; width:20px; height:20px;
            border-color:#22d3ee; border-style:solid;
        }
        .reticle-box::before  { top:-2px;left:-2px;     border-width:3px 0 0 3px;   border-top-left-radius:10px; }
        .reticle-box::after   { top:-2px;right:-2px;    border-width:3px 3px 0 0;   border-top-right-radius:10px; }
        .reticle-inner::before{ bottom:-2px;left:-2px;  border-width:0 0 3px 3px;   border-bottom-left-radius:10px; }
        .reticle-inner::after { bottom:-2px;right:-2px; border-width:0 3px 3px 0;   border-bottom-right-radius:10px; }
        .laser-line {
            position:absolute; left:5px; right:5px; top:5px; height:2px;
            background:linear-gradient(90deg,transparent,#22d3ee,transparent);
            box-shadow:0 0 8px #22d3ee;
            animation:laserSweep 1.6s ease-in-out infinite alternate;
        }
        @keyframes laserSweep { to { top: calc(100% - 7px); } }
        .reticle-hint {
            position:absolute; bottom:-34px; left:50%; transform:translateX(-50%);
            white-space:nowrap; color:#fff; font-size:.68rem;
            background:rgba(15,23,42,.72); padding:2px 12px;
            border-radius:20px; border:1px solid rgba(255,255,255,.12);
        }

        /* Tap focus ring */
        .focus-ring {
            position:absolute; width:50px; height:50px;
            border:2px solid #facc15; border-radius:8px;
            box-shadow:0 0 10px rgba(250,204,21,.7);
            pointer-events:none; z-index:20;
            transform:translate(-50%,-50%) scale(1.3);
            opacity:0; transition:transform .2s cubic-bezier(.175,.885,.32,1.275), opacity .22s;
        }
        .focus-ring.show { transform:translate(-50%,-50%) scale(1); opacity:1; }

        /* Scan result overlay */
        .scan-overlay {
            position:absolute; inset:0; z-index:30;
            display:none; flex-direction:column;
            align-items:center; justify-content:center;
            text-align:center; padding:20px; cursor:pointer;
        }
        .scan-overlay.success { background:rgba(21,128,61,.93); color:#fff; }
        .scan-overlay.warning { background:rgba(180,83,9,.93); color:#fff; }
        .scan-overlay.error   { background:rgba(185,28,28,.93); color:#fff; }

        /* ── Camera Toolbar ── */
        .cam-toolbar {
            background:#1e293b;
            border-radius:0 0 10px 10px;
            padding:8px 12px;
            display:flex; align-items:center; gap:8px;
            flex-wrap:nowrap; overflow-x:auto;
        }
        .cam-toolbar::-webkit-scrollbar { display:none; }

        #cam-select {
            flex:1; min-width:0;
            background:#334155; color:#e2e8f0;
            border:1px solid #475569; border-radius:8px;
            font-size:.72rem; height:32px; padding:0 24px 0 8px;
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394a3b8'/%3E%3C/svg%3E");
            background-repeat:no-repeat; background-position:right 8px center;
            text-overflow:ellipsis;
        }
        .cam-btn {
            flex-shrink:0; display:inline-flex; align-items:center; gap:4px;
            height:32px; padding:0 12px; border-radius:8px; border:none;
            font-size:.72rem; font-weight:600; cursor:pointer; white-space:nowrap;
        }
        .cam-btn-start  { background:#166534; color:#fff; }
        .cam-btn-stop   { background:#991b1b; color:#fff; }
        .cam-btn-torch  { background:#334155; color:#e2e8f0; min-width:36px; justify-content:center; }
        .cam-btn-torch.on { background:#d97706; color:#fff; }
        .cam-btn-zoom   { background:#334155; color:#e2e8f0; min-width:46px; justify-content:center; }
        .cam-btn-zoom.on  { background:#0d6efd; color:#fff; }
        .cam-btn-focus  { background:#334155; color:#64748b; min-width:36px; justify-content:center; }
        .cam-btn-fs     { background:#0284c7; color:#fff; }
        .cam-btn-fs:hover { background:#0369a1; }
        .cam-btn-fs.active { background:#dc2626 !important; }

        /* Status dot */
        #cam-status-dot {
            width:8px; height:8px; border-radius:50%; flex-shrink:0;
            background:#475569; transition:background .3s;
        }
        #cam-status-dot.live { background:#22c55e; box-shadow:0 0 5px #22c55e; }

        /* ── Fullscreen Kiosk Mode (Locks Scroll & Expands Scanner) ── */
        body.fullscreen-scan-lock {
            overflow: hidden !important;
            touch-action: none !important;
            position: fixed !important;
            width: 100vw !important;
            height: 100vh !important;
        }

        .scanner-card.fullscreen-mode {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100dvh !important;
            z-index: 999999 !important;
            border-radius: 0 !important;
            margin: 0 !important;
            background: #090d16 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            touch-action: none !important;
            overflow: hidden !important;
        }

        .fullscreen-hud-bar {
            position: absolute;
            top: 12px;
            left: 12px;
            right: 12px;
            z-index: 45;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            padding: 8px 14px;
            border-radius: 50rem;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .scanner-card.fullscreen-mode .card-header {
            display: none !important;
        }

        .scanner-card.fullscreen-mode .scanner-wrap {
            flex: 1 !important;
            max-width: min(88vw, 68vh, 440px) !important;
            max-height: min(88vw, 68vh, 440px) !important;
            margin: auto !important;
            border-radius: 16px !important;
            box-shadow: 0 0 30px rgba(34, 211, 238, 0.25) !important;
        }

        .scanner-card.fullscreen-mode .cam-toolbar {
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(8px) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 0 !important;
            padding: 10px 16px !important;
            z-index: 50 !important;
            justify-content: center !important;
        }

        /* Placeholder */
        .cam-placeholder {
            position:absolute; inset:0;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center;
            color:#94a3b8; gap:10px; z-index:5;
        }

        /* Mobile */
        @media (max-width:576px) {
            .stats-bar { padding:10px 12px; }
            .header-avatar { width:36px!important; height:36px!important; }
            .cam-toolbar { padding:7px 10px; gap:6px; }
            .cam-btn { height:30px; padding:0 10px; font-size:.68rem; }
            #cam-select { font-size:.68rem; height:30px; }
            .table th, .table td { padding:5px 7px!important; font-size:.76rem!important; }
        }
    </style>
</head>
<body>
<div class="main-container py-3 px-3">

    <!-- ── Stats Bar ── -->
    <div class="stats-bar mb-3">
        <div class="row align-items-center g-2">
            <div class="col-12 col-md-auto me-md-auto">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm header-avatar">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size:.93rem;"><?= htmlspecialchars($pass['workshop_name']) ?></h6>
                            <span class="badge bg-primary-subtle text-primary border rounded-pill" style="font-size:.67rem;"><?= htmlspecialchars($pass['label']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="badge bg-warning-subtle text-dark border rounded-pill" style="font-size:.67rem;">
                                <i class="bi bi-clock-history text-danger me-1"></i>នៅសល់&nbsp;<strong id="countdown-timer">...</strong>
                            </span>
                            <?php if (!empty($device['helper_name'])): ?>
                            <span class="badge bg-primary text-white border rounded-pill" style="font-size:.67rem;">
                                <i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($device['helper_name']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-auto">
                <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-2 pt-2 pt-md-0 border-top border-md-0">
                    <div class="stat-pill d-flex align-items-center gap-1">
                        <span class="text-secondary" style="font-size:.74rem;">បានស្កេន៖</span>
                        <strong class="text-success" style="font-size:.98rem;" id="stats-checked-in"><?= (int)($stats['checked_in']??0) ?>&nbsp;/&nbsp;<?= (int)($stats['total_confirmed']??0) ?></strong>
                    </div>
                    <div class="fast-pill d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox" id="fastModeToggle" checked>
                        <label class="form-check-label user-select-none mb-0" for="fastModeToggle" style="font-size:.77rem;cursor:pointer;">ស្កេនលឿន</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Guidance Banner ── -->
    <div class="alert alert-light border shadow-sm mb-3 py-2 px-3 rounded-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="small text-dark" style="font-size:.77rem;">
                <i class="bi bi-info-circle-fill text-primary me-1"></i>
                <strong>ណែនាំ៖</strong> ស្កេន QR ឬវាយឈ្មោះ/លេខទូរស័ព្ទ រួចចុច <strong>កត់ត្រា</strong>
            </span>
            <span class="badge bg-success-subtle text-success border flex-shrink-0" style="font-size:.67rem;">ដំណើរការ</span>
        </div>
    </div>

    <!-- ── Main Layout ── -->
    <div class="row g-3 g-md-4">

        <!-- Left: Camera + Manual -->
        <div class="col-lg-6">

            <!-- Camera Card -->
            <div class="card shadow-sm border-0 mb-3 rounded-3 overflow-hidden scanner-card" id="scanner-card">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
                    <span class="fw-bold small d-flex align-items-center gap-1">
                        <i class="bi bi-camera-video-fill text-primary"></i> ស្គេនកូដ QR
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1" id="btn-fullscreen-toggle" onclick="toggleFullscreen()" style="font-size: 0.74rem; height: 26px;">
                            <i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់
                        </button>
                        <span class="d-flex align-items-center gap-1 small text-muted">
                            <span id="cam-status-dot"></span>
                            <span id="cam-status-text" style="font-size:.7rem;">រង់ចាំ…</span>
                        </span>
                    </div>
                </div>

                <!-- ── Scanner viewport (html5-qrcode renders INSIDE #qr-reader) ── -->
                <div class="scanner-wrap" id="scanner-wrap">
                    <!-- Floating Fullscreen HUD (active only in fullscreen mode) -->
                    <div class="fullscreen-hud-bar" id="fullscreen-hud-bar" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white border px-2 py-1 rounded-pill" style="font-size: 0.72rem;">
                                <i class="bi bi-lock-fill me-1"></i>ជាប់សោរ
                            </span>
                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                                បានស្កេន៖ <strong class="text-success fs-6" id="fs-stats-checked-in"><?= (int)($stats['checked_in']??0) ?> / <?= (int)($stats['total_confirmed']??0) ?></strong>
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger py-1 px-3 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-1" onclick="toggleFullscreen()" style="font-size: 0.76rem;">
                            <i class="bi bi-fullscreen-exit"></i> ចាកចេញ
                        </button>
                    </div>

                    <!-- Placeholder shown before camera starts -->
                    <div class="cam-placeholder" id="cam-placeholder">
                        <i class="bi bi-camera-video-off" style="font-size:2.2rem;"></i>
                        <div style="font-size:.78rem;">ចុច <strong class="text-success">ចាប់ផ្តើម</strong> ខាងក្រោម</div>
                    </div>

                    <!-- html5-qrcode renders its video HERE — native UI hidden by CSS above -->
                    <div id="qr-reader"></div>

                    <!-- Laser reticle overlay (on top of video) -->
                    <div class="laser-reticle" id="laser-reticle">
                        <div class="reticle-box">
                            <div class="reticle-inner"></div>
                            <div class="laser-line"></div>
                            <div class="reticle-hint"><i class="bi bi-crosshair me-1"></i>ចុចលើអេក្រង់ Focus</div>
                        </div>
                    </div>

                    <!-- Tap-focus ring -->
                    <div class="focus-ring" id="focus-ring"></div>

                    <!-- Scan result overlay -->
                    <div class="scan-overlay" id="scan-overlay" onclick="resumeAfterScan()">
                        <i id="scan-icon" class="bi display-1 mb-2"></i>
                        <h4 id="scan-message" class="fw-bold mb-1 px-2"></h4>
                        <div id="scan-name" class="fw-bold" style="font-size:1.05rem;"></div>
                        <div class="mt-2 text-white-50" style="font-size:.7rem;">ចុចអេក្រង់ ឬ រង់ចាំ…</div>
                    </div>
                </div>

                <!-- ── Camera Toolbar (our custom, not html5-qrcode's) ── -->
                <div class="cam-toolbar">
                    <select id="cam-select" style="display:none;"></select>

                    <button class="cam-btn cam-btn-start" id="btn-start" onclick="startScanning()">
                        <i class="bi bi-play-fill"></i> ចាប់ផ្តើម
                    </button>
                    <button class="cam-btn cam-btn-stop" id="btn-stop" onclick="stopScanning()" style="display:none;">
                        <i class="bi bi-stop-fill"></i> បិទ
                    </button>

                    <button class="cam-btn cam-btn-torch" id="btn-torch" onclick="toggleTorch()" style="display:none;" title="ភ្លើង">
                        <i class="bi bi-lightbulb"></i>
                    </button>
                    <button class="cam-btn cam-btn-zoom" id="btn-zoom" onclick="toggleZoom()" style="display:none;" title="Zoom">
                        1x
                    </button>
                    <button class="cam-btn cam-btn-focus" id="btn-focus" onclick="triggerFocus()" style="display:none;" title="Focus">
                        <i class="bi bi-crosshair"></i>
                    </button>
                    <button class="cam-btn cam-btn-fs" id="btn-cam-fs" onclick="toggleFullscreen()" title="ពេញអេក្រង់">
                        <i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់
                    </button>
                </div>
            </div>

            <!-- Manual Search -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-2 px-3">
                    <span class="fw-bold small"><i class="bi bi-search text-success me-1"></i> ស្វែងរក ឬកត់ត្រាដោយដៃ</span>
                </div>
                <div class="card-body p-2 p-sm-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white d-none d-sm-flex"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="manual-input" class="form-control" placeholder="វាយឈ្មោះ / លេខទូរស័ព្ទ / កូដ..." autocomplete="off">
                        <button class="btn btn-primary fw-semibold px-3 text-nowrap" id="btn-manual-search" style="font-size:.84rem;">ស្វែងរក</button>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.72rem;">សម្រាប់សិក្ខាកាមដែលភ្លេចកូដ QR ឬទូរស័ព្ទអស់ថ្ម។</div>
                    <div id="manual-results" class="mt-2" style="display:none;max-height:260px;overflow-y:auto;"></div>
                </div>
            </div>
        </div>

        <!-- Right: Participant + Recent -->
        <div class="col-lg-6">
            <!-- Participant Card -->
            <div class="card shadow-sm border-0 rounded-3 mb-3" id="participant-info-card" style="display:none;">
                <div class="card-body text-center p-3">
                    <div class="mb-2">
                        <div id="pi-initials" class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow" style="width:70px;height:70px;font-size:1.6rem;"></div>
                        <img id="pi-photo-img" src="" alt="" class="rounded-circle shadow border border-3 border-success" style="width:70px;height:70px;object-fit:cover;display:none;">
                    </div>
                    <h5 id="pi-name" class="fw-bold text-dark mb-1" style="font-size:1.05rem;"></h5>
                    <div class="mb-2">
                        <span id="pi-province" class="badge bg-primary me-1" style="font-size:.7rem;"></span>
                        <span id="pi-ticket"   class="badge bg-secondary" style="font-size:.7rem;"></span>
                        <span id="pi-vip" class="badge bg-warning text-dark" style="display:none;font-size:.7rem;"><i class="bi bi-star-fill"></i> VIP</span>
                    </div>
                    <p id="pi-company" class="text-muted mb-2" style="font-size:.77rem;"></p>
                    <div class="bg-light rounded-3 border p-2 text-start">
                        <div class="row g-1">
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.67rem;">កូដចុះឈ្មោះ</div>
                                <strong id="pi-code" class="text-primary font-monospace" style="font-size:.82rem;"></strong>
                            </div>
                            <div class="col-6">
                                <div class="text-muted" style="font-size:.67rem;">លេខទូរស័ព្ទ</div>
                                <strong id="pi-phone" style="font-size:.82rem;"></strong>
                            </div>
                            <div class="col-12">
                                <div class="text-muted" style="font-size:.67rem;">ម៉ោងស្កេន</div>
                                <strong id="pi-time" class="text-success" style="font-size:.82rem;"></strong>
                            </div>
                        </div>
                    </div>
                    <div id="already-in-msg" class="alert alert-warning py-1 mt-2 mb-0 small" style="display:none;"></div>
                </div>
            </div>

            <!-- Ready State -->
            <div id="waiting-state" class="card border-0 shadow-sm rounded-3 p-3 text-center text-muted mb-3" style="min-height:200px;">
                <div class="m-auto py-2">
                    <i class="bi bi-qr-code-scan text-primary d-block mb-2" style="font-size:2.5rem;"></i>
                    <h6 class="fw-bold text-dark">តុជំនួយការត្រៀមរួចរាល់</h6>
                    <p class="small mb-0" style="font-size:.77rem;">ចុច <strong class="text-success">ចាប់ផ្តើម</strong> រួចតម្រង់កូដ QR ទៅកាន់កាមេរ៉ា</p>
                </div>
            </div>

            <!-- Recent Check-ins -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small"><i class="bi bi-clock-history text-primary me-1"></i> វត្តមានដែលទើបស្កេន</span>
                    <span class="badge bg-light text-muted border" id="recent-count" style="font-size:.69rem;"><?= count($recentAttendance??[]) ?> នាក់</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size:.79rem;">
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
                                        <td class="text-muted small">
                                            <span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($ra['province']) ?></span>
                                        </td>
                                        <td class="text-end pe-3 text-success fw-bold text-nowrap"><?= date('H:i:s', strtotime($ra['checked_in_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="no-recent-row"><td colspan="3" class="text-center py-4 text-muted small">មិនទាន់មានការស្កេន</td></tr>
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
<!-- html5-qrcode library — renders into #qr-reader but we hide its native UI via CSS -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const PASS_TOKEN   = '<?= $pass['token'] ?>';
const EXPIRES_AT   = new Date('<?= date('c', strtotime($pass['expires_at'])) ?>').getTime();
const APP_URL      = '<?= APP_URL ?>';
const DEVICE_TOKEN = '<?= htmlspecialchars($devToken ?? '') ?>';

// ── State ──────────────────────────────────────────────
let html5QrCode  = null;   // Html5Qrcode instance (NOT Scanner)
let scanning     = false;  // camera running
let paused       = false;  // result overlay showing
let torchOn      = false;
let zoomLevel    = 1;
let focusTimer   = null;
let scanTimeout  = null;
let afTimer      = null;
let cameras      = [];
let activeCamId  = null;

// ── Countdown ──────────────────────────────────────────
function tick() {
    const diff = EXPIRES_AT - Date.now();
    const el = document.getElementById('countdown-timer');
    if (!el) return;
    if (diff <= 0) {
        el.innerText = 'ផុតកំណត់';
        new bootstrap.Modal(document.getElementById('expiredModal')).show();
        return;
    }
    const h = Math.floor(diff/3600000), m = Math.floor((diff%3600000)/60000), s = Math.floor((diff%60000)/1000);
    el.innerText = h > 0 ? `${h}ម ${m}ន` : `${m}:${String(s).padStart(2,'0')}`;
}
setInterval(tick, 1000); tick();

// ── Camera list ────────────────────────────────────────
async function loadCameras() {
    try {
        cameras = await Html5Qrcode.getCameras();
    } catch(e) { cameras = []; }

    const sel = document.getElementById('cam-select');
    sel.innerHTML = '';
    cameras.forEach((d, i) => {
        const o = document.createElement('option');
        o.value = d.id;
        o.textContent = d.label || `កាមេរ៉ា ${i+1}`;
        sel.appendChild(o);
    });

    // Prefer back/rear camera
    const rear = cameras.find(d => /back|rear|environment/i.test(d.label || ''));
    if (rear) { sel.value = rear.id; activeCamId = rear.id; }
    else if (cameras.length) activeCamId = cameras[0].id;

    if (cameras.length > 1) sel.style.display = 'block';

    sel.addEventListener('change', async function() {
        activeCamId = this.value;
        if (scanning) { await stopScanning(); await startScanning(); }
    });
}

// ── Start / Stop ───────────────────────────────────────
async function startScanning() {
    setStatus('loading', 'ចាប់ផ្តើម…');
    document.getElementById('btn-start').style.display = 'none';
    document.getElementById('btn-stop').style.display  = 'inline-flex';
    document.getElementById('cam-placeholder').style.display = 'none';

    // Create fresh instance each time
    html5QrCode = new Html5Qrcode("qr-reader", {
        formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
        verbose: false
    });

    const config = {
        fps: 30,
        // NO qrbox at all → decode full camera frame → fastest
        videoConstraints: {
            deviceId: activeCamId ? { exact: activeCamId } : undefined,
            facingMode: activeCamId ? undefined : { ideal: 'environment' },
            width:  { min: 640, ideal: 1280 },
            height: { min: 480, ideal: 720 },
            focusMode: { ideal: 'continuous' },
            advanced: [
                { focusMode: 'continuous' },
                { exposureMode: 'continuous' }
            ]
        }
    };

    try {
        await html5QrCode.start(
            activeCamId || { facingMode: 'environment' },
            config,
            onScanSuccess,
            _err => {}   // silent errors during scanning (frame with no QR)
        );

        scanning = true; paused = false;
        setStatus('live', 'ដំណើរការ');
        document.getElementById('laser-reticle').style.display = 'flex';
        document.getElementById('btn-focus').style.display = 'inline-flex';

        // Detect hardware features after a short settle delay
        setTimeout(detectHardwareCaps, 900);

        // Auto-refocus every 4 s to prevent blur drift
        if (afTimer) clearInterval(afTimer);
        afTimer = setInterval(() => { if (scanning && !paused) applyFocus(); }, 4000);

    } catch(err) {
        console.error('Scanner start error:', err);
        setStatus('off', 'Permission ចាំបាច់');
        document.getElementById('btn-start').style.display = 'inline-flex';
        document.getElementById('btn-stop').style.display  = 'none';
        document.getElementById('cam-placeholder').style.display = 'flex';
    }
}

async function stopScanning() {
    if (afTimer) { clearInterval(afTimer); afTimer = null; }
    if (html5QrCode && scanning) {
        try { await html5QrCode.stop(); } catch(e){}
    }
    scanning = false; paused = false;
    torchOn = false; zoomLevel = 1;
    document.getElementById('btn-start').style.display = 'inline-flex';
    document.getElementById('btn-stop').style.display  = 'none';
    document.getElementById('btn-torch').style.display = 'none';
    document.getElementById('btn-zoom').style.display  = 'none';
    document.getElementById('btn-focus').style.display = 'none';
    document.getElementById('laser-reticle').style.display = 'none';
    document.getElementById('cam-placeholder').style.display = 'flex';
    setStatus('off', 'បានបិទ');
}

function setStatus(state, text) {
    const dot = document.getElementById('cam-status-dot');
    dot.className = state === 'live' ? 'live' : '';
    document.getElementById('cam-status-text').textContent = text;
}

// ── Hardware Capabilities ──────────────────────────────
function getTrack() {
    try {
        const v = document.querySelector('#qr-reader video');
        if (v && v.srcObject) return v.srcObject.getVideoTracks()[0] || null;
    } catch(e) {}
    return null;
}

function detectHardwareCaps() {
    const track = getTrack();
    if (!track) return;
    try {
        const caps = track.getCapabilities ? track.getCapabilities() : {};

        // Apply continuous focus + exposure
        const adv = [];
        if (caps.focusMode?.includes('continuous'))   adv.push({ focusMode: 'continuous' });
        if (caps.exposureMode?.includes('continuous')) adv.push({ exposureMode: 'continuous' });
        if (adv.length) track.applyConstraints({ advanced: adv }).catch(()=>{});

        if (caps.torch) document.getElementById('btn-torch').style.display = 'inline-flex';
        if (caps.zoom && caps.zoom.max > 1.5) {
            const b = document.getElementById('btn-zoom');
            b.style.display = 'inline-flex';
            b.dataset.max = caps.zoom.max;
        }
    } catch(e) {}
}

function applyFocus() {
    const t = getTrack(); if (!t) return;
    try {
        t.applyConstraints({ advanced: [{ focusMode: 'continuous' }] })
         .catch(() => t.applyConstraints({ advanced: [{ focusMode: 'auto' }] }).catch(()=>{}));
    } catch(e) {}
}

function triggerFocus() {
    applyFocus();
    const w = document.getElementById('scanner-wrap');
    if (w) { const r = w.getBoundingClientRect(); showFocusRing(r.width/2, r.height/2); }
}

function toggleTorch() {
    const t = getTrack(); if (!t) return;
    torchOn = !torchOn;
    t.applyConstraints({ advanced: [{ torch: torchOn }] }).then(() => {
        const b = document.getElementById('btn-torch');
        b.classList.toggle('on', torchOn);
        b.innerHTML = torchOn ? '<i class="bi bi-lightbulb-fill"></i>' : '<i class="bi bi-lightbulb"></i>';
    }).catch(() => { torchOn = !torchOn; });
}

function toggleZoom() {
    const t = getTrack(); if (!t) return;
    const b = document.getElementById('btn-zoom');
    const max = parseFloat(b.dataset.max || 2);
    zoomLevel = zoomLevel === 1 ? Math.min(2, max) : 1;
    t.applyConstraints({ advanced: [{ zoom: zoomLevel }] }).then(() => {
        b.textContent = zoomLevel + 'x';
        b.classList.toggle('on', zoomLevel > 1);
    }).catch(()=>{});
}

// ── Fullscreen Mode (Lock Screen Scroll & Center Scanner) ──
let isFullscreenMode = false;

function toggleFullscreen() {
    isFullscreenMode = !isFullscreenMode;
    const card = document.getElementById('scanner-card');
    const hud = document.getElementById('fullscreen-hud-bar');
    const btnFs = document.getElementById('btn-fullscreen-toggle');
    const btnCamFs = document.getElementById('btn-cam-fs');

    if (isFullscreenMode) {
        document.body.classList.add('fullscreen-scan-lock');
        if (card) card.classList.add('fullscreen-mode');
        if (hud) hud.style.display = 'flex';
        if (btnCamFs) {
            btnCamFs.classList.add('active');
            btnCamFs.innerHTML = '<i class="bi bi-fullscreen-exit"></i> ចាកចេញ';
        }
        if (btnFs) {
            btnFs.classList.replace('btn-outline-primary', 'btn-danger');
            btnFs.innerHTML = '<i class="bi bi-fullscreen-exit"></i> ចាកចេញ';
        }

        // Sync counter
        const origSt = document.getElementById('stats-checked-in');
        const fsSt = document.getElementById('fs-stats-checked-in');
        if (origSt && fsSt) fsSt.innerText = origSt.innerText;

        // Try native browser fullscreen API if supported (Android / Desktop)
        try {
            if (card && card.requestFullscreen) {
                card.requestFullscreen().catch(()=>{});
            } else if (card && card.webkitRequestFullscreen) {
                card.webkitRequestFullscreen();
            }
        } catch(e) {}
    } else {
        document.body.classList.remove('fullscreen-scan-lock');
        if (card) card.classList.remove('fullscreen-mode');
        if (hud) hud.style.display = 'none';
        if (btnCamFs) {
            btnCamFs.classList.remove('active');
            btnCamFs.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់';
        }
        if (btnFs) {
            btnFs.classList.replace('btn-danger', 'btn-outline-primary');
            btnFs.innerHTML = '<i class="bi bi-arrows-fullscreen"></i> ពេញអេក្រង់';
        }

        // Exit native fullscreen if active
        try {
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                if (document.exitFullscreen) document.exitFullscreen().catch(()=>{});
                else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
            }
        } catch(e) {}
    }

    setTimeout(() => { applyFocus(); }, 300);
}

// Exit fullscreen when user presses ESC or exits browser fullscreen
document.addEventListener('fullscreenchange', () => {
    if (!document.fullscreenElement && isFullscreenMode) toggleFullscreen();
});
document.addEventListener('webkitfullscreenchange', () => {
    if (!document.webkitFullscreenElement && isFullscreenMode) toggleFullscreen();
});

// ── Tap-to-Focus ───────────────────────────────────────
function showFocusRing(x, y) {
    const r = document.getElementById('focus-ring');
    r.style.left = x + 'px'; r.style.top = y + 'px';
    r.classList.add('show');
    if (focusTimer) clearTimeout(focusTimer);
    focusTimer = setTimeout(() => r.classList.remove('show'), 900);
    if (navigator.vibrate) try { navigator.vibrate(18); } catch(e){}
}

// ── Scan Success ───────────────────────────────────────
function onScanSuccess(decoded) {
    if (paused) return;
    paused = true;
    if (navigator.vibrate) try { navigator.vibrate(35); } catch(e){}
    document.getElementById('laser-reticle').style.display = 'none';
    submitScan(decoded);
}

function submitScan(token) {
    const fd = new FormData();
    fd.append('pass_token', PASS_TOKEN);
    fd.append('qr_token', token);
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
    const ov = document.getElementById('scan-overlay');
    ov.className = 'scan-overlay';
    const ico = document.getElementById('scan-icon');
    if (data.success && !data.already_in) {
        beep('ok'); vibrate(45);
        ov.classList.add('success');
        ico.className = 'bi bi-check-circle-fill display-1 mb-2';
        const st = document.getElementById('stats-checked-in');
        if (st) {
            const p = st.innerText.split('/');
            if (p.length===2) {
                const updated = (parseInt(p[0].trim())+1)+' / '+p[1].trim();
                st.innerText = updated;
                const fsSt = document.getElementById('fs-stats-checked-in');
                if (fsSt) fsSt.innerText = updated;
            }
        }
    } else if (data.success && data.already_in) {
        beep('warn'); vibrate([50,40,50]);
        ov.classList.add('warning');
        ico.className = 'bi bi-exclamation-triangle-fill display-1 mb-2';
    } else {
        beep('err'); vibrate(120);
        ov.classList.add('error');
        ico.className = 'bi bi-x-circle-fill display-1 mb-2';
    }
    document.getElementById('scan-message').innerText = data.message || 'កំហុស';
    document.getElementById('scan-name').innerText = data.participant ? data.participant.name : '';
    ov.style.display = 'flex';

    if (scanTimeout) clearTimeout(scanTimeout);
    if (document.getElementById('fastModeToggle').checked)
        scanTimeout = setTimeout(resumeAfterScan, 1500);
}

function resumeAfterScan() {
    document.getElementById('scan-overlay').style.display = 'none';
    paused = false;
    if (scanning) document.getElementById('laser-reticle').style.display = 'flex';
    applyFocus();
}

// ── Participant Card ───────────────────────────────────
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
    document.getElementById('pi-time').innerText     = p.checked_in || 'ទើបស្កេន';
    const img = document.getElementById('pi-photo-img');
    const ini = document.getElementById('pi-initials');
    if (p.photo_url) { img.src=p.photo_url; img.style.display='inline-block'; ini.style.display='none'; }
    else { img.style.display='none'; ini.style.display='inline-flex'; ini.innerText=p.name?p.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase():'?'; }
    const vip = document.getElementById('pi-vip');
    if (vip) vip.style.display = p.is_vip ? 'inline-block' : 'none';
    const warn = document.getElementById('already-in-msg');
    if (data.already_in) { warn.style.display='block'; warn.innerText=data.message; } else warn.style.display='none';
}

function addRecent(p) {
    document.getElementById('no-recent-row')?.remove();
    const tbody = document.getElementById('recent-tbody');
    const tr = document.createElement('tr');
    const t = new Date().toTimeString().slice(0,8);
    tr.innerHTML=`<td class="ps-3 fw-bold text-dark text-nowrap">${esc(p.name)}</td><td class="text-muted small"><span class="badge bg-primary-subtle text-primary border">${esc(p.province)}</span></td><td class="text-end pe-3 text-success fw-bold text-nowrap">${t}</td>`;
    tbody.insertBefore(tr, tbody.firstChild);
    const c = document.getElementById('recent-count');
    if (c) c.innerText = (parseInt(c.innerText)||0)+1+' នាក់';
}

// ── Manual Search ──────────────────────────────────────
function doSearch() {
    const q = document.getElementById('manual-input').value.trim();
    const mres = document.getElementById('manual-results');
    const mbtn = document.getElementById('btn-manual-search');
    if (!q) { mres.style.display='none'; return; }
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
                    const done=it.checked_in_at!==null;
                    h+=`<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-2 py-2 px-2">
                        <div class="min-w-0"><div class="fw-bold small text-truncate">${esc(it.name)}</div>
                        <div class="text-muted text-truncate" style="font-size:.69rem;">${esc(it.registration_code)} | ${esc(it.phone||'-')}</div></div>
                        <div class="flex-shrink-0">${done?'<span class="badge bg-success-subtle text-success border" style="font-size:.69rem;">បានស្កេន</span>':`<button class="btn btn-sm btn-primary py-0 px-2" style="font-size:.72rem;" onclick="submitScan('${esc(it.token||it.registration_code)}')">កត់ត្រា</button>`}</div></div>`;
                });
                mres.innerHTML=h+'</div>';
            }
            mres.style.display='block';
        }).catch(()=>{ mbtn.disabled=false; mbtn.innerText='ស្វែងរក'; });
}
document.getElementById('btn-manual-search').addEventListener('click', doSearch);
document.getElementById('manual-input').addEventListener('keydown', e=>{ if(e.key==='Enter') doSearch(); });

// ── Audio + Haptic ─────────────────────────────────────
function beep(type) {
    try {
        const ctx=new(window.AudioContext||window.webkitAudioContext)();
        const osc=ctx.createOscillator(), g=ctx.createGain();
        osc.connect(g); g.connect(ctx.destination);
        if(type==='ok')  { osc.type='sine';     osc.frequency.setValueAtTime(820,ctx.currentTime); osc.frequency.exponentialRampToValueAtTime(1200,ctx.currentTime+.13); g.gain.setValueAtTime(.3,ctx.currentTime); g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+.17); osc.start(); osc.stop(ctx.currentTime+.2); }
        else if(type==='warn'){ osc.type='triangle'; osc.frequency.setValueAtTime(460,ctx.currentTime); g.gain.setValueAtTime(.3,ctx.currentTime); g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+.28); osc.start(); osc.stop(ctx.currentTime+.3); }
        else { osc.type='sawtooth'; osc.frequency.setValueAtTime(200,ctx.currentTime); g.gain.setValueAtTime(.3,ctx.currentTime); g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+.28); osc.start(); osc.stop(ctx.currentTime+.3); }
    } catch(e){}
}
function vibrate(p) { if(navigator.vibrate) try { navigator.vibrate(p); } catch(e){} }
function esc(s) { if(!s) return ''; const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }

// ── Tap-to-Focus listener ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('scanner-wrap');
    if (wrap) {
        wrap.addEventListener('click', e => {
            if (!scanning || paused) return;
            if (e.target.closest('button,select,a')) return;
            const r = wrap.getBoundingClientRect();
            showFocusRing(e.clientX - r.left, e.clientY - r.top);
            applyFocus();
        });
    }
});

// ── Init ───────────────────────────────────────────────
(async function init() {
    await loadCameras();
    await startScanning();   // auto-start immediately on page load
})();
</script>
</body>
</html>
