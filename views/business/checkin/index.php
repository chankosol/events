<?php
// views/business/checkin/index.php
?>
<style>
    .scanner-container { position: relative; width: 100%; max-width: 350px; margin: 0 auto; }
    #reader { width: 100%; border: none !important; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); background: #1a1e21; position: relative; }
    #reader video { object-fit: cover !important; border-radius: 12px; }

    /* When scanning is active: move dashboard and Stop button over the bottom gap of the camera */
    #reader.is-scanning #reader__dashboard,
    #reader:has(#html5-qrcode-button-camera-stop:not([style*="display: none"])) #reader__dashboard {
        position: absolute !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 15 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        background: transparent !important;
        pointer-events: none;
    }
    #reader.is-scanning #reader__dashboard *,
    #reader:has(#html5-qrcode-button-camera-stop:not([style*="display: none"])) #reader__dashboard * {
        pointer-events: auto;
    }

    /* Style the Stop Scanning button to have comfortable height with light font and vertically centered icon */
    #html5-qrcode-button-camera-stop {
        position: absolute !important;
        bottom: 8px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        z-index: 20 !important;
        background-color: rgba(13, 110, 253, 0.85) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        padding: 5px 14px !important;
        border-radius: 16px !important;
        font-size: 0.78rem !important;
        font-weight: 400 !important;
        letter-spacing: 0.2px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
        cursor: pointer !important;
        margin: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        line-height: 1.2 !important;
        backdrop-filter: blur(4px);
        transition: all 0.2s ease !important;
        white-space: nowrap !important;
    }
    #html5-qrcode-button-camera-stop i,
    #html5-qrcode-button-camera-stop .bi {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 0.72rem !important;
        line-height: 1 !important;
        vertical-align: 0 !important;
        margin: 0 !important;
    }
    #html5-qrcode-button-camera-stop i::before,
    #html5-qrcode-button-camera-stop .bi::before {
        display: inline-block !important;
        line-height: 1 !important;
        vertical-align: 0 !important;
    }
    #html5-qrcode-button-camera-stop span {
        display: inline-flex !important;
        align-items: center !important;
        line-height: 1 !important;
    }
    #html5-qrcode-button-camera-stop:hover {
        background-color: rgba(220, 53, 69, 0.9) !important;
        border-color: rgba(255, 255, 255, 0.6) !important;
        transform: translateX(-50%) scale(1.03) !important;
    }

    #reader button { background-color: #0d6efd; color: white; border: none; padding: 7px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; margin: 6px; transition: 0.2s; font-size: 0.9rem; }
    #reader button:hover { background-color: #0b5ed7; }
    #reader a { color: #0d6efd; text-decoration: none; font-size: 0.85rem; }

    /* Hide html5-qrcode pause/info messages completely */
    #reader__header_message,
    #reader__status_span,
    #reader > div > span[style*="background"],
    #reader > div > div[style*="background"],
    #reader div[style*="background-color: rgba(0, 0, 0"],
    #reader div[style*="background: rgba(9, 9, 9"],
    #reader div[style*="rgba(9, 9, 9"],
    #reader div[style*="rgba(9,9,9"],
    #reader div[style*="top: 0px"][style*="position: absolute"] {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Modern Segmented Control for QR ⇄ Face Switcher */
    .scan-segmented-control {
        display: inline-flex;
        background: #f1f5f9;
        padding: 3px;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    }
    .scan-seg-btn {
        border: none;
        background: transparent;
        padding: 4px 13px;
        font-size: 0.76rem;
        font-weight: 600;
        color: #64748b;
        border-radius: 999px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        cursor: pointer;
        line-height: 1.3;
        white-space: nowrap;
    }
    .scan-seg-btn:hover {
        color: #1e293b;
    }
    .scan-seg-btn.active {
        background: #ffffff;
        color: #0d6efd;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.15), 0 1px 2px rgba(0,0,0,0.06);
    }
    .scan-seg-btn.active.btn-face-active {
        background: #0d6efd;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.35);
    }

    /* Face AI Mode Overrides: Clean full camera view with sleek Face ID frame */
    .scanner-container.mode-face #reader__scan_region svg,
    .scanner-container.mode-face #reader__scan_region div[style*="border"],
    .scanner-container.mode-face #reader__scan_region div[style*="box-shadow"],
    .scanner-container.mode-face #camera-focus-reticle,
    .scanner-container.mode-face #html5-qrcode-button-camera-stop {
        display: none !important;
        opacity: 0 !important;
    }

    /* Face AI Guide Frame (Apple Face ID style sleek corners) */
    .face-ai-guide {
        position: absolute;
        inset: 12% 16%;
        pointer-events: none;
        z-index: 5;
        border-radius: 24px;
        display: none;
        box-sizing: border-box;
    }
    .scanner-container.mode-face .face-ai-guide {
        display: block;
    }
    .face-ai-guide::before,
    .face-ai-guide::after,
    .face-ai-guide-inner::before,
    .face-ai-guide-inner::after {
        content: '';
        position: absolute;
        width: 26px;
        height: 26px;
        border-color: #10b981;
        border-style: solid;
        filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.7));
    }
    .face-ai-guide::before       { top: 0; left: 0; border-width: 3.5px 0 0 3.5px; border-top-left-radius: 18px; }
    .face-ai-guide::after        { top: 0; right: 0; border-width: 3.5px 3.5px 0 0; border-top-right-radius: 18px; }
    .face-ai-guide-inner::before { bottom: 0; left: 0; border-width: 0 0 3.5px 3.5px; border-bottom-left-radius: 18px; }
    .face-ai-guide-inner::after  { bottom: 0; right: 0; border-width: 0 3.5px 3.5px 0; border-bottom-right-radius: 18px; }

    /* Face Scanning Beam */
    .face-ai-scan-line {
        position: absolute;
        left: 6px;
        right: 6px;
        top: 6px;
        height: 2px;
        background: linear-gradient(90deg, transparent, #10b981, transparent);
        box-shadow: 0 0 10px #10b981;
        animation: faceScanSweep 2.2s ease-in-out infinite alternate;
        opacity: 0.85;
    }
    @keyframes faceScanSweep {
        to { top: calc(100% - 8px); }
    }

    /* Floating subtle hint at bottom of camera */
    .face-bottom-hint {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 7;
        background: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        backdrop-filter: blur(6px);
        padding: 3px 13px;
        border-radius: 20px;
        font-size: 0.72rem;
        border: 1px solid rgba(255, 255, 255, 0.15);
        display: none;
        pointer-events: none;
        white-space: nowrap;
    }
    .scanner-container.mode-face .face-bottom-hint {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    /* Floating Zoom & Focus Sidebar on the left of scanning container */
    .zoom-sidebar {
        position: absolute;
        left: -46px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
        flex-direction: column;
        gap: 7px;
        z-index: 25;
    }
    .btn-zoom {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        background: #ffffff;
        color: #8c959f;
        font-size: 0.72rem;
        font-weight: 350;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 0;
        user-select: none;
    }
    .btn-zoom:hover {
        border-color: #0d6efd;
        color: #0d6efd;
        font-weight: 500;
        transform: scale(1.08);
        box-shadow: 0 3px 8px rgba(13, 110, 253, 0.25);
    }
    .btn-zoom.active {
        background: #0d6efd !important;
        color: #ffffff !important;
        border-color: #0d6efd !important;
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.45) !important;
        transform: scale(1.05);
    }

    /* Auto Focus Button & Reticle */
    .btn-autofocus {
        margin-top: 3px;
        border-color: #ced4da;
        color: #6c757d;
        font-weight: 500;
        letter-spacing: 0.5px;
        background: #ffffff;
    }
    .btn-autofocus:hover {
        border-color: #198754;
        color: #198754;
        font-weight: 600;
    }
    .btn-autofocus.focusing {
        background: #198754 !important;
        color: #ffffff !important;
        border-color: #198754 !important;
        box-shadow: 0 2px 10px rgba(25, 135, 84, 0.5) !important;
        transform: scale(1.08);
    }

    .focus-reticle {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 84px;
        height: 84px;
        transform: translate(-50%, -50%) scale(1.3);
        border: 2px solid #0d6efd;
        border-radius: 12px;
        pointer-events: none;
        z-index: 22;
        opacity: 0;
        transition: opacity 0.15s ease, transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), border-color 0.2s ease;
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.3);
    }
    .focus-reticle.active {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    .focus-reticle.focused {
        border-color: #198754;
        box-shadow: 0 0 12px rgba(25, 135, 84, 0.55), inset 0 0 8px rgba(25, 135, 84, 0.35);
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
    .scan-result-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 30; display: none; flex-direction: column; justify-content: center; align-items: center; border-radius: 12px; background: rgba(255,255,255,0.95); }
    .scan-success { background: rgba(40, 167, 69, 0.95); color: white; }
    .scan-error { background: rgba(220, 53, 69, 0.95); color: white; }
    .scan-warning { background: rgba(255, 193, 7, 0.95); color: #333; }
    .stats-bar { background: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .pulse-scan { animation: pulseBorder 1.5s infinite; }
    @keyframes pulseBorder {
        0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(13, 110, 253, 0); }
        100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
    }
    .helper-pass-card {
        border-color: #e2e8f0 !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        flex-shrink: 0 !important;
    }
    .helper-pass-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.06) !important;
    }
    #helperPassModal .modal-body {
        max-height: calc(85vh - 120px);
        overflow-y: auto;
    }
    #helper-passes-container {
        overflow: visible;
        padding-bottom: 1.5rem;
    }
    .shadow-2xs {
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    /* Modern SweetAlert2 Customization */
    .swal2-popup {
        font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        border-radius: 20px !important;
        padding: 1.75rem !important;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.16) !important;
    }
    .swal2-title {
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        color: #1e293b !important;
        padding-top: 0.5rem !important;
    }
    .swal2-html-container {
        font-size: 0.95rem !important;
        color: #475569 !important;
        line-height: 1.6 !important;
        margin-top: 0.75rem !important;
    }
    .swal2-actions {
        margin-top: 1.5rem !important;
        gap: 12px !important;
    }
    .swal2-confirm, .swal2-cancel {
        border-radius: 50rem !important;
        font-weight: 600 !important;
        font-size: 0.9rem !important;
        padding: 0.6rem 1.6rem !important;
        transition: all 0.2s ease !important;
    }
    .swal2-confirm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.35) !important;
    }
    .swal2-toast {
        border-radius: 14px !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
        padding: 0.85rem 1.15rem !important;
    }
    /* Live Monitor Pulse and Feed Styling */
    .pulse-indicator {
        position: relative;
        width: 14px;
        height: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #dc3545;
        position: relative;
        z-index: 2;
    }
    .pulse-indicator::after {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: rgba(220, 53, 69, 0.4);
        animation: liveRipple 1.6s infinite ease-out;
    }
    @keyframes liveRipple {
        0% { transform: scale(0.6); opacity: 1; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    .stream-item {
        transition: background-color 0.2s ease, transform 0.15s ease;
    }
    .stream-item:hover {
        background-color: #f8fafc !important;
    }
    .stream-item-new {
        animation: highlightNew 2s ease-out;
    }
    @keyframes highlightNew {
        0% { background-color: rgba(25, 135, 84, 0.15); }
        100% { background-color: transparent; }
    }
</style>

<!-- Top Navigation & Stats Bar -->
<div class="stats-bar px-3 py-2 d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ត្រឡប់
        </a>
        <h5 class="fw-bold mb-0 text-dark text-truncate" style="max-width: 250px;"><?= htmlspecialchars($workshop['name']) ?></h5>
        
        <!-- Mode Switcher Pill (Admin Live Monitor vs Camera Scanner) -->
        <div class="btn-group btn-group-sm p-0.5 bg-light rounded-pill border ms-2" role="group">
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-primary text-white shadow-xs" id="btn-mode-dashboard" onclick="switchCheckinMode('dashboard')">
                <i class="bi bi-speedometer2 me-1"></i> ផ្ទាំងបញ្ជា
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary" id="btn-mode-scanner" onclick="switchCheckinMode('scanner')">
                <i class="bi bi-camera-video me-1"></i> ស្កេនកាមេរ៉ា
            </button>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Live Counter in Top Bar with clear container & spacing -->
        <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1.5 bg-light rounded-pill border shadow-2xs">
            <span class="small text-muted mb-0" style="font-size: 0.75rem;">វត្តមាន៖</span>
            <span class="fw-bold text-success" id="stats-checked-in" style="font-size: 0.98rem;">
                <?= $totalCheckedIn ?> / <?= $totalConfirmed ?>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Helper Pass Quick Button -->
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold position-relative shadow-2xs" data-bs-toggle="modal" data-bs-target="#helperPassModal" id="topbar-helper-btn" title="គ្រប់គ្រងតុជំនួយការស្កេន">
                <i class="bi bi-qr-code me-1"></i> តុជំនួយការ
                <span class="badge bg-primary text-white rounded-pill ms-1" id="topbar-helper-badge" style="display: <?= $activeDevicesCount > 0 ? 'inline-block' : 'none' ?>; font-size: 0.68rem;"><?= $activeDevicesCount ?> គ្រឿង</span>
            </button>

            <!-- Fullscreen Button -->
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5" id="btn-toggle-fullscreen" title="បើកពេញអេក្រង់" onclick="toggleFullscreen()">
                <i class="bi bi-arrows-fullscreen"></i>
            </button>

            <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5" data-bs-toggle="modal" data-bs-target="#checkinHelpModal" title="របៀបប្រើ">
                <i class="bi bi-question-circle"></i>
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- VIEW 1: ADMIN LIVE MONITOR DASHBOARD (DEFAULT FOR ADMIN) -->
<!-- ======================================================== -->
<div id="view-dashboard-mode" class="container-fluid pb-5 px-0">
    <!-- Top 4 Live Metrics Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Checked In / Rate -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.76rem;">វត្តមានសរុប</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" id="lm-rate-badge"><?= $checkinRate ?>%</span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="fw-bold mb-0 text-dark" id="lm-checked-in"><?= $totalCheckedIn ?></h2>
                    <span class="text-muted fw-semibold" style="font-size: 0.95rem;">/ <span id="lm-total"><?= $totalConfirmed ?></span> នាក់</span>
                </div>
                <div class="progress" style="height: 6px; border-radius: 10px; background-color: #e2e8f0;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="lm-progress-bar" role="progressbar" style="width: <?= $checkinRate ?>%"></div>
                </div>
                <div class="small text-muted mt-2 d-flex align-items-center justify-content-between" style="font-size: 0.74rem;">
                    <span><i class="bi bi-clock-history me-1 text-primary"></i>បច្ចុប្បន្នភាព</span>
                    <span id="lm-last-updated-text" class="text-secondary">-</span>
                </div>
            </div>
        </div>

        <!-- 2. Pending Attendees -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.76rem;">មិនទាន់មកដល់</span>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-person-dash-fill fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="fw-bold mb-0 text-dark" id="lm-pending"><?= $totalPending ?></h2>
                    <span class="text-muted fw-semibold" style="font-size: 0.95rem;">នាក់</span>
                </div>
                <p class="small text-muted mb-0" style="font-size: 0.76rem;">
                    <i class="bi bi-info-circle me-1 text-secondary"></i>សិក្ខាកាមបានចុះឈ្មោះតែមិនទាន់ស្កេនចូល
                </p>
            </div>
        </div>

        <!-- 3. VIP Attendees -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.76rem;">ភ្ញៀវកិត្តិយស VIP</span>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-star-fill text-warning fs-6"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="fw-bold mb-0 text-dark" id="lm-vip"><?= $vipCheckedIn ?> / <?= $vipTotal ?></h2>
                    <span class="text-muted fw-semibold" style="font-size: 0.95rem;">នាក់</span>
                </div>
                <p class="small text-muted mb-0" style="font-size: 0.76rem;">
                    <i class="bi bi-shield-check me-1 text-success"></i>វត្តមានថ្នាក់ដឹកនាំ និងភ្ញៀវកិត្តិយស VIP
                </p>
            </div>
        </div>

        <!-- 4. Helper Stations -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.76rem;">តុស្កេនជំនួយការ</span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-bold" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#helperPassModal">
                        <i class="bi bi-plus me-0.5"></i> បង្កើតតុ
                    </button>
                </div>
                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <h2 class="fw-bold mb-0 text-primary" id="lm-stations-count"><?= $activePassesCount ?></h2>
                    <span class="text-muted fw-semibold" style="font-size: 0.95rem;">តុ (<span id="lm-devices-count"><?= $activeDevicesCount ?></span> គ្រឿង)</span>
                </div>
                <p class="small text-muted mb-0" style="font-size: 0.76rem;">
                    <i class="bi bi-phone me-1 text-primary"></i>ទូរស័ព្ទជំនួយការកំពុងជួយស្កេននៅច្រកទ្វារ
                </p>
            </div>
        </div>
    </div>

    <!-- Main Grid: Live Activity Feed (Left) & Helper Stations / Tools (Right) -->
    <div class="row g-4">
        <!-- Left: Live Activity Feed (Col 8) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <!-- Header with Live Pulse -->
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="pulse-indicator">
                            <span class="pulse-dot"></span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">ចរន្តស្កេនវត្តមានផ្ទាល់</h6>
                            <small class="text-muted" style="font-size: 0.74rem;">បង្ហាញព័ត៌មានសិក្ខាកាមភ្លាមៗរាល់ពេលស្កេនគ្រប់ច្រក</small>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.76rem;">
                        <i class="bi bi-broadcast me-1 text-danger"></i> ផ្សាយផ្ទាល់
                    </span>
                </div>

                <!-- Admin Inline Quick Search Bar -->
                <div class="p-3 bg-light bg-opacity-75 border-bottom">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="lm-manual-search-input" placeholder="វាយឈ្មោះ, លេខទូរស័ព្ទ, ឬកូដចុះឈ្មោះ ដើម្បីស្វែងរក ឬកត់ត្រាដោយដៃ...">
                        <button class="btn btn-primary fw-bold px-3 shadow-sm" type="button" id="lm-btn-manual-search">
                            <i class="bi bi-person-check me-1"></i> ស្វែងរក
                        </button>
                    </div>
                    <!-- Inline Search Results Popup Box -->
                    <div id="lm-search-results-box" class="mt-2 bg-white rounded-3 border p-2 shadow-sm" style="display: none; max-height: 220px; overflow-y: auto;">
                    </div>
                </div>

                <!-- Live Stream Feed Items -->
                <div class="card-body p-0" style="max-height: 560px; overflow-y: auto;">
                    <div id="lm-stream-container" class="list-group list-group-flush">
                        <div class="text-center py-5 text-muted">
                            <span class="spinner-border spinner-border-sm me-2 text-primary"></span> កំពុងទាញទិន្នន័យផ្សាយផ្ទាល់...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Helper Stations & Tools (Col 4) -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <!-- Helper Stations Monitoring Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-people-fill fs-6"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">តុជំនួយការស្កេន</h6>
                            <small class="text-muted" style="font-size: 0.72rem;">តាមដានការស្កេនតាមតុនីមួយៗ</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light border px-2.5 py-1 rounded-pill text-primary fw-bold small" data-bs-toggle="modal" data-bs-target="#helperPassModal">
                        គ្រប់គ្រង <i class="bi bi-arrow-right-short"></i>
                    </button>
                </div>
                <div class="card-body p-3" style="max-height: 320px; overflow-y: auto;">
                    <div id="lm-stations-list-container" class="d-flex flex-column gap-2">
                        <div class="text-center py-4 text-muted small">
                            <span class="spinner-border spinner-border-sm me-1 text-primary"></span> កំពុងផ្ទុកទិន្នន័យតុ...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Organizer Action Center -->
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <h6 class="fw-bold text-dark mb-2.5 small text-uppercase text-secondary">
                    <i class="bi bi-tools me-1 text-primary"></i> ឧបករណ៍ និងសកម្មភាពរហ័ស
                </h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>/registrations/export" class="btn btn-light border d-flex align-items-center justify-content-between p-2.5 rounded-3 text-dark text-decoration-none">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                <i class="bi bi-file-earmark-spreadsheet-fill fs-5"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold small">ទាញយកបញ្ជីវត្តមាន Excel</div>
                                <div class="text-muted" style="font-size: 0.72rem;">ទាញយកបញ្ជីអ្នកបានស្កេនជាក់ស្តែង</div>
                            </div>
                        </div>
                        <i class="bi bi-download text-muted"></i>
                    </a>

                    <button type="button" class="btn btn-light border d-flex align-items-center justify-content-between p-2.5 rounded-3 text-dark text-start" data-bs-toggle="modal" data-bs-target="#helperPassModal">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                <i class="bi bi-qr-code fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold small">បង្កើតលីងតុជំនួយការថ្មី</div>
                                <div class="text-muted" style="font-size: 0.72rem;">ឱ្យក្រុមការងារជួយស្កេនតាមទូរស័ព្ទ</div>
                            </div>
                        </div>
                        <i class="bi bi-plus-circle-fill text-primary"></i>
                    </button>

                    <button type="button" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2 p-2 rounded-3 fw-bold small mt-1 shadow-2xs" onclick="switchCheckinMode('scanner')">
                        <i class="bi bi-camera-video"></i> ប្តូរទៅរបៀបកាមេរ៉ាស្កេនផ្ទាល់ដៃ
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- VIEW 2: CAMERA SCANNER MODE (FOR DIRECT SCANNING)       -->
<!-- ======================================================== -->
<div id="view-scanner-mode" class="container-fluid pb-5 px-0" style="display: none;">
    <!-- Quick Guidance Banner -->
    <div class="alert alert-light border shadow-sm mb-4 py-2 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
            <span class="small text-dark">
                <strong>វិធីស្កេនវត្តមាន៖</strong> 
                (១) <strong>ស្កេនកូដ QR</strong> តាមកាមេរ៉ា ឬ 
                (២) <strong>វាយឈ្មោះ / លេខទូរស័ព្ទ / កូដចុះឈ្មោះ</strong> ក្នុងប្រអប់ស្វែងរក រួចចុច <strong>កត់ត្រា</strong>។
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="fastModeToggle" checked>
                <label class="form-check-label small text-muted" for="fastModeToggle">ស្កេនលឿន</label>
            </div>
            <span class="badge bg-light text-secondary border small">សំឡេងប៊ីប: បើក</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Camera Scanner & Manual Search -->
        <div class="col-lg-6">
            <!-- Camera Scanner Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-1.5" id="scanner-title-label">
                            <i class="bi <?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'face' ? 'bi-person-bounding-box' : 'bi-camera-video-fill' ?> text-primary" id="scanner-title-icon" style="font-size: 0.95rem;"></i>
                            <span id="scanner-title-text"><?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'face' ? 'AI ស្កេនមុខ' : 'ស្កេន QR' ?></span>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" id="scanner-status-badge" style="font-size: 0.68rem; padding: 2.5px 8px;">ដំណើរការ</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small <?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'face' ? '' : 'd-none' ?>" id="face-count-header-badge" style="font-size: 0.68rem; padding: 2.5px 8px;">
                            <i class="bi bi-person-check-fill me-1"></i><span id="face-count-text">0 មុខបានចុះ</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Modern Apple-style Segmented Switcher (QR vs Face AI) -->
                        <div class="scan-segmented-control" id="hostScanModeGroup" title="ប្តូរម៉ូដស្កេនសម្រាប់គ្រប់ស្ថានីយ">
                            <button type="button" class="scan-seg-btn <?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'qr' ? 'active' : '' ?>" id="btn-mode-qr" onclick="switchScanMode('qr')">
                                <i class="bi bi-qr-code"></i>
                                <span>ស្កេន QR</span>
                            </button>
                            <button type="button" class="scan-seg-btn <?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'face' ? 'active btn-face-active' : '' ?>" id="btn-mode-face" onclick="switchScanMode('face')">
                                <i class="bi bi-person-bounding-box"></i>
                                <span>AI ស្កេនមុខ</span>
                            </button>
                        </div>
                        <!-- Camera Switcher (Auto-selects Logi 1080P if found) -->
                        <div id="camera-select-wrapper" style="display: none;">
                            <select id="camera-select" class="form-select form-select-sm py-0 px-2" style="font-size: 0.72rem; height: 28px; max-width: 155px; border-radius: 8px;">
                            </select>
                        </div>
                        <!-- Helper Pass Button (Create temporary scan link for helpers) -->
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center gap-1 shadow-xs" style="font-size: 0.74rem; padding: 3px 10px; height: 28px;" data-bs-toggle="modal" data-bs-target="#helperPassModal" id="btn-open-helper-modal" title="បង្កើតលីងជំនួយការស្កេនបណ្តោះអាសន្ន">
                            <i class="bi bi-qr-code"></i>
                            <span class="d-none d-sm-inline">លីងជំនួយការ</span>
                            <span class="d-inline d-sm-none">ជំនួយការ</span>
                            <span class="badge bg-primary text-white rounded-pill ms-1" id="active-helper-count-badge" style="display: none; font-size: 0.65rem;">0</span>
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <?php 
                    $dailyScans = (int)($workshop['daily_scans_required'] ?? 1);
                    if ($dailyScans >= 2): 
                        if ($dailyScans == 2) {
                            $slotsList = [
                                1 => ['label' => 'ពេលព្រឹក', 'en' => 'Morning', 'icon' => 'bi-sun-fill text-warning'],
                                2 => ['label' => 'ពេលរសៀល', 'en' => 'Afternoon', 'icon' => 'bi-cloud-sun-fill text-primary']
                            ];
                        } elseif ($dailyScans == 3) {
                            $slotsList = [
                                1 => ['label' => 'ពេលព្រឹក', 'en' => 'Morning', 'icon' => 'bi-sun-fill text-warning'],
                                2 => ['label' => 'ពេលរសៀល', 'en' => 'Afternoon', 'icon' => 'bi-cloud-sun-fill text-primary'],
                                3 => ['label' => 'ពេលល្ងាច', 'en' => 'Closing', 'icon' => 'bi-moon-stars-fill text-indigo']
                            ];
                        } else {
                            $slotsList = [
                                1 => ['label' => 'ព្រឹកចូល', 'en' => 'In', 'icon' => 'bi-box-arrow-in-right text-success'],
                                2 => ['label' => 'ព្រឹកចេញ', 'en' => 'Out', 'icon' => 'bi-box-arrow-right text-warning'],
                                3 => ['label' => 'រសៀលចូល', 'en' => 'In', 'icon' => 'bi-box-arrow-in-right text-info'],
                                4 => ['label' => 'រសៀលចេញ', 'en' => 'Out', 'icon' => 'bi-box-arrow-right text-danger']
                            ];
                        }
                    ?>
                    <!-- Daily Scan Slot Selector Pills -->
                    <div class="mb-3 bg-light p-2 rounded-3 border">
                        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                            <span class="small fw-bold text-dark" style="font-size: 0.76rem;">
                                <i class="bi bi-calendar2-range text-primary me-1"></i> ជ្រើសរើសវេនស្កេន (<?= $dailyScans ?> វេន/ថ្ងៃ)
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem;" id="slot-auto-hint">
                                <i class="bi bi-clock me-1"></i>ស្វ័យប្រវត្តិតាមម៉ោង
                            </span>
                        </div>
                        <div class="btn-group w-100 p-0.5 rounded-pill bg-white border shadow-2xs" role="group" id="scan-slot-pills">
                            <?php foreach ($slotsList as $sNum => $sInfo): ?>
                                <button type="button" class="btn btn-sm rounded-pill py-1 px-2 fw-semibold slot-pill-btn btn-light text-muted" data-slot="<?= $sNum ?>" onclick="setScanSlot(<?= $sNum ?>)" style="font-size: 0.75rem; transition: all 0.2s;">
                                    <i class="bi <?= $sInfo['icon'] ?> me-1"></i><?= $sInfo['label'] ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="scanner-container position-relative <?= ($workshop['checkin_scan_mode'] ?? 'qr') === 'face' ? 'mode-face' : '' ?>" id="scanner-main-wrap">
                        <!-- Floating Zoom & Focus Buttons on Left: 1x, 2x, 3x, 5x, AF -->
                        <div class="zoom-sidebar" id="zoom-sidebar" title="Zoom & Auto Focus">
                            <button type="button" class="btn-zoom active" data-zoom="1" title="ធម្មតា (1x)">1x</button>
                            <button type="button" class="btn-zoom" data-zoom="2" title="Zoom 2x">2x</button>
                            <button type="button" class="btn-zoom" data-zoom="3" title="Zoom 3x">3x</button>
                            <button type="button" class="btn-zoom" data-zoom="5" title="Zoom 5x">5x</button>
                            <button type="button" class="btn-zoom btn-autofocus" id="btn-autofocus" title="Auto Focus (ផ្តោតច្បាស់ស្វ័យប្រវត្តិ)">AF</button>
                        </div>

                        <!-- Face AI Guide Frame (Apple Face ID style sleek corners) -->
                        <div class="face-ai-guide" id="face-ai-guide">
                            <div class="face-ai-guide-inner"></div>
                            <div class="face-ai-scan-line"></div>
                        </div>

                        <!-- Floating subtle hint at bottom of camera in Face Mode -->
                        <div class="face-bottom-hint" id="face-bottom-hint">
                            <i class="bi bi-person-bounding-box text-success"></i>
                            <span>សូមដាក់មុខឱ្យចំកណ្តាលកាមេរ៉ា</span>
                        </div>

                        <div id="camera-focus-reticle" class="focus-reticle"></div>
                        <div id="reader"></div>
                        <canvas id="face-hud-canvas" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 6; pointer-events: none; display: none;"></canvas>
                        <div id="scan-result" class="scan-result-overlay">
                            <i id="scan-icon" class="bi display-1 mb-2"></i>
                            <h3 id="scan-message" class="text-center px-3"></h3>
                            <div id="scan-participant-name" class="fs-4 fw-bold mt-2"></div>
                        </div>
                    </div>

                    <!-- Clean alert if no faces enrolled yet -->
                    <div id="no-face-alert" class="alert alert-warning border border-warning-subtle py-2 px-3 mt-2 rounded-3 small d-none align-items-center justify-content-between mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill text-warning fs-5"></i>
                            <span>មិនទាន់មានទិន្នន័យផ្ទៃមុខសម្រាប់ស្កេននៅឡើយទេ។</span>
                        </div>
                        <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>/registrations" class="btn btn-sm btn-warning text-dark fw-bold py-1 px-3 rounded-pill text-nowrap" style="font-size: 0.75rem;">
                            <i class="bi bi-person-plus-fill me-1"></i> បញ្ជីសិក្ខាកាម
                        </a>
                    </div>
                </div>
            </div>

            <!-- Manual Search / Check-in Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2">
                    <span class="fw-bold text-dark small">
                        <i class="bi bi-search text-success me-2"></i>ស្វែងរក ឬកត់ត្រាវត្តមានដោយដៃ
                    </span>
                </div>
                <div class="card-body p-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="manual-input" class="form-control" placeholder="វាយឈ្មោះ, លេខទូរស័ព្ទ, ឬកូដចុះឈ្មោះ...">
                        <button class="btn btn-primary fw-semibold px-3" type="button" id="btn-manual-search">
                            ស្វែងរក
                        </button>
                    </div>
                    <div class="form-text small text-muted mt-1">
                        សម្រាប់សិក្ខាកាមដែលភ្លេចកូដ QR ឬទូរស័ព្ទអស់ថ្ម។
                    </div>

                    <!-- Search Results Box -->
                    <div id="manual-results" class="mt-3" style="display: none; max-height: 280px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>

        <!-- Right Column: Participant Details & Recent Check-ins -->
        <div class="col-lg-6">
            <!-- Scanned Participant Info Card -->
            <div class="card shadow-sm border-0 mb-3" id="participant-info-card" style="display: none;">
                <div class="card-body text-center p-4">
                    <div class="mb-3 position-relative d-inline-block">
                        <div id="pi-photo-box">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow" style="width: 90px; height: 90px; font-size: 2rem;" id="pi-initials"></div>
                            <img id="pi-photo-img" src="" alt="Profile Photo" class="rounded-circle shadow border border-3 border-success" style="width: 90px; height: 90px; object-fit: cover; display: none;">
                        </div>
                        <span class="badge bg-success position-absolute bottom-0 start-50 translate-middle-x" id="pi-photo-badge" style="display: none;">
                            <i class="bi bi-shield-check"></i> ផ្ទៀងផ្ទាត់
                        </span>
                    </div>

                    <h3 id="pi-name" class="mb-1 fw-bold text-dark"></h3>
                    <div class="mb-2">
                        <span id="pi-province" class="badge bg-primary px-3 py-1 me-1"></span>
                        <span id="pi-ticket" class="badge bg-secondary"></span>
                        <span id="pi-vip" class="badge bg-warning text-dark" style="display: none;"><i class="bi bi-star-fill"></i> VIP</span>
                    </div>
                    <p id="pi-company" class="text-muted mb-3 small"></p>

                    <div class="p-3 bg-light rounded mb-3 text-start border">
                        <div class="row g-2">
                            <div class="col-6">
                                <small class="text-muted d-block">លេខកូដចុះឈ្មោះ</small>
                                <strong id="pi-code" class="font-monospace text-primary"></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">លេខទូរស័ព្ទ</small>
                                <strong id="pi-phone"></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">អត្តសញ្ញាណប័ណ្ណ</small>
                                <strong id="pi-idcard"></strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">ស្ថានភាពបង់ប្រាក់</small>
                                <strong id="pi-payment"></strong>
                            </div>
                        </div>
                    </div>

                    <div id="already-in-msg" class="alert alert-info py-2 mb-3" style="display: none;"></div>

                    <div class="row g-2" id="pi-action-buttons">
                        <div class="col" id="col-print-badge">
                            <button type="button" class="btn btn-outline-primary w-100 fw-bold py-2 small text-nowrap" id="btn-print-badge">
                                <i class="bi bi-printer me-1"></i> បោះពុម្ពប័ណ្ណ
                            </button>
                        </div>
                        <div class="col" id="col-substitute">
                            <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>/delegations" class="btn btn-outline-warning text-dark w-100 fw-bold py-2 small text-nowrap">
                                <i class="bi bi-arrow-left-right me-1"></i> ជំនួសសមាជិក
                            </a>
                        </div>
                        <div class="col" id="col-reset-attendance" style="display: none;">
                            <button type="button" class="btn btn-outline-danger w-100 fw-bold py-2 small text-nowrap" id="btn-reset-attendance">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> លុបវត្តមាន
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Waiting State Card -->
            <div id="waiting-state" class="card border-0 shadow-sm p-5 text-center text-muted mb-3" style="min-height: 260px;">
                <div class="my-auto">
                    <i class="bi bi-qr-code-scan display-3 text-primary mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">ត្រៀមរួចរាល់សម្រាប់ស្កេន</h5>
                    <p class="small text-muted mb-0">សូមតម្រង់កាមេរ៉ាទៅកាន់កូដ QR របស់សិក្ខាកាម ឬវាយឈ្មោះស្វែងរក</p>
                </div>
            </div>

            <!-- Recent Check-ins Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold small text-dark">
                        <i class="bi bi-clock-history text-primary me-1"></i> វត្តមានដែលទើបស្កេនថ្មីៗ
                    </span>
                    <span class="badge bg-light text-muted border" id="recent-count"><?= count($recentAttendance ?? []) ?> នាក់</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">ឈ្មោះសិក្ខាកាម</th>
                                    <th>ខេត្ត</th>
                                    <th>ស្ថាប័ន</th>
                                    <th>តួនាទី</th>
                                    <th>ម៉ោងស្កេន</th>
                                    <th class="text-end pe-3" style="width: 125px;">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody id="recent-attendance-tbody">
                                <?php if (!empty($recentAttendance)): ?>
                                    <?php foreach ($recentAttendance as $ra): ?>
                                        <tr data-reg-id="<?= $ra['registration_id'] ?>" data-reg-code="<?= htmlspecialchars($ra['registration_code']) ?>" data-slot-number="<?= $ra['slot_number'] ?? '' ?>" data-slot-id="<?= $ra['slot_id'] ?? '' ?>">
                                            <td class="ps-3 fw-bold text-dark text-nowrap"><?= htmlspecialchars($ra['name']) ?></td>
                                            <td><span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($ra['province']) ?></span></td>
                                            <td class="text-muted small text-truncate" style="max-width: 130px;" title="<?= htmlspecialchars($ra['company']) ?>"><?= htmlspecialchars($ra['company']) ?></td>
                                            <td class="text-muted small text-truncate" style="max-width: 100px;" title="<?= htmlspecialchars($ra['position']) ?>"><?= htmlspecialchars($ra['position']) ?></td>
                                            <td class="text-success fw-bold text-nowrap">
                                                <?php if (!empty($ra['slot_name'])): ?>
                                                    <span class="badge bg-light text-secondary border me-1 fw-normal" style="font-size: 0.72rem;"><?= htmlspecialchars(preg_replace('/^ពេល/', 'វេន', $ra['slot_name'])) ?></span>
                                                <?php endif; ?>
                                                <?= date('H:i:s', strtotime($ra['checked_in_at'])) ?>
                                            </td>
                                            <td class="text-end pe-3 text-nowrap">
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-normal" style="font-size: 0.75rem; padding: 3px 7px; line-height: 1.2;" title="លុបវត្តមានដើម្បីស្កេនឡើងវិញ" onclick="resetAttendance(<?= $ra['registration_id'] ?>, <?= (int)($ra['slot_number'] ?? 0) ?>, <?= (int)($ra['slot_id'] ?? 0) ?>, this)"><i class="bi bi-arrow-counterclockwise" style="margin-right: 2px;"></i>លុបវត្តមាន</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="no-recent-row"><td colspan="6" class="text-center py-4 text-muted small">មិនទាន់មានការស្កេនវត្តមាននៅឡើយទេ។</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Checkin Instructions / Help -->
<div class="modal fade" id="checkinHelpModal" tabindex="-1" aria-labelledby="checkinHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="checkinHelpModalLabel">
                    <i class="bi bi-question-circle me-2"></i> របៀបប្រើប្រាស់តុស្កេនវត្តមាន
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-3 mb-3">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-camera-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">១. ស្កេនតាមកាមេរ៉ា</h6>
                        <p class="text-muted small mb-0">ចុចប៊ូតុង <strong>«អនុញ្ញាតបើកកាមេរ៉ាស្កេន»</strong> នៅខាងឆ្វេង រួចយកកូដ QR របស់សិក្ខាកាម (លើសំបុត្រ ឬទូរស័ព្ទ) មកបង្ហាញមុខកាមេរ៉ា។</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <div class="bg-success-subtle text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-search fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">២. ស្វែងរក និងកត់ត្រាដោយដៃ</h6>
                        <p class="text-muted small mb-0">ប្រសិនបើសិក្ខាកាមភ្លេចកូដ QR គ្រាន់តែវាយ <strong>ឈ្មោះ</strong>, <strong>លេខទូរស័ព្ទ</strong>, ឬ <strong>កូដចុះឈ្មោះ</strong> ក្នុងប្រអប់ស្វែងរក រួចចុច <strong>«កត់ត្រា»</strong>។</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div class="bg-info-subtle text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px;">
                        <i class="bi bi-printer-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">៣. បោះពុម្ពកាតសម្គាល់ខ្លួន</h6>
                        <p class="text-muted small mb-0">ពេលស្កេនជោគជ័យ ព័ត៌មានសិក្ខាកាមនឹងបង្ហាញនៅខាងស្តាំ លោកអ្នកអាចចុចប៊ូតុង <strong>«បោះពុម្ពប័ណ្ណ»</strong> ដើម្បីព្រីនប័ណ្ណភ្លាមៗ។</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-primary fw-bold px-4" data-bs-dismiss="modal">យល់ព្រម</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Printable Badge -->
<div class="modal fade" id="badgeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center" id="printable-badge-area">
                <div class="border border-2 border-dark rounded-3 p-3 bg-white" style="width: 250px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                    <div class="fw-bold text-uppercase text-truncate mb-1" style="font-size: 11px;"><?= htmlspecialchars($workshop['name']) ?></div>
                    <div class="border-top border-2 border-dark mb-3"></div>
                    
                    <div class="mb-2">
                        <img id="badge-photo-img" src="" alt="Photo" class="rounded-circle border border-2 border-dark" style="width: 75px; height: 75px; object-fit: cover; display: none;">
                        <div id="badge-photo-placeholder" class="rounded-circle bg-light border border-2 border-dark d-inline-flex align-items-center justify-content-center" style="width: 75px; height: 75px; font-size: 2rem;">
                            <i class="bi bi-person"></i>
                        </div>
                    </div>

                    <h4 class="fw-bold text-dark mb-1" id="badge-name"></h4>
                    <span class="badge bg-dark fs-6 px-3 py-1 mb-2" id="badge-province"></span>
                    <div class="text-muted small text-truncate mb-3" id="badge-company"></div>

                    <div class="p-2 bg-light rounded border mb-2 d-inline-block">
                        <img id="badge-qr" src="" alt="QR" style="width: 100px; height: 100px;">
                    </div>
                    <div class="fw-bold text-muted font-monospace" style="font-size: 11px;" id="badge-code"></div>
                </div>
            </div>
            <div class="modal-footer p-2 justify-content-center bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="printBadge()">
                    <i class="bi bi-printer me-1"></i> បោះពុម្ពឥឡូវនេះ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Helper Pass (Temporary Scan Link) -->
<div class="modal fade" id="helperPassModal" tabindex="-1" aria-labelledby="helperPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-qr-code text-white fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="helperPassModalLabel">គ្រប់គ្រងលីងជំនួយការស្កេន</h5>
                        <small class="text-white text-opacity-75" style="font-size: 0.75rem;">បង្កើត និងតាមដានតុស្កេនជំនួយការបណ្តោះអាសន្ន</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-fill mb-4 bg-light p-1.5 rounded-3 border" id="helperPassTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-2.5 rounded-2 d-flex align-items-center justify-content-center gap-1.5" id="create-pass-tab" data-bs-toggle="tab" data-bs-target="#tab-create-pass" type="button" role="tab">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>បង្កើតលីងថ្មី</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-2.5 rounded-2 d-flex align-items-center justify-content-center gap-1.5" id="list-pass-tab" data-bs-toggle="tab" data-bs-target="#tab-list-pass" type="button" role="tab">
                            <i class="bi bi-list-stars"></i>
                            <span>បញ្ជីតុជំនួយការ</span>
                            <span class="badge bg-primary text-white rounded-pill ms-1 px-2 py-0.5" id="helper-passes-count">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="helperPassTabsContent">
                    <!-- Tab 1: Create Pass -->
                    <div class="tab-pane fade show active" id="tab-create-pass" role="tabpanel">
                        <form id="form-create-helper-pass">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-dark small">ឈ្មោះសម្គាល់តុ ឬអ្នកជួយស្កេន</label>
                                    <input type="text" class="form-control" id="hp-label" name="label" placeholder="ឧ. តុទី១ (ច្រកមុខ), ក្រុមការងារស្ម័គ្រចិត្ត A..." value="តុស្កេនជំនួយការ">
                                    <div class="form-text small">ដាក់ឈ្មោះដើម្បីងាយស្រួលចំណាំថាតើនរណា ឬតុណាជាអ្នកស្កេន។</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small" title="កំណត់ចំនួនឧបករណ៍ដែលអាចចូលប្រើបាន">ចំនួនឧបករណ៍</label>
                                    <select class="form-select" id="hp-max-devices" name="max_devices">
                                        <option value="1" selected>១ គ្រឿង (ណែនាំ - សម្រាប់ ១ ទូរស័ព្ទ)</option>
                                        <option value="2">២ គ្រឿង</option>
                                        <option value="3">៣ គ្រឿង</option>
                                        <option value="5">៥ គ្រឿង</option>
                                        <option value="10">១០ គ្រឿង</option>
                                    </select>
                                    <div class="form-text small text-danger"><i class="bi bi-shield-lock me-1"></i>បិទមិនឱ្យឧបករណ៍លើសពីនេះចូលប្រើបានឡើយ។</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark small">សុពលភាព / រយៈពេលផុតកំណត់</label>
                                    <select class="form-select" id="hp-duration" name="duration_hours">
                                        <option value="2">២ ម៉ោង</option>
                                        <option value="4" selected>៤ ម៉ោង (ណែនាំ)</option>
                                        <option value="8">៨ ម៉ោង</option>
                                        <option value="12">១២ ម៉ោង</option>
                                        <option value="24">២៤ ម៉ោង (១ ថ្ងៃ)</option>
                                    </select>
                                    <div class="form-text small">ផុតម៉ោងនេះ លីងនឹងខូចដោយស្វ័យប្រវត្តិ។</div>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary fw-bold px-4 py-2 rounded-3 shadow-sm" id="btn-submit-create-pass">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> បង្កើតលីងជំនួយការ
                                </button>
                            </div>
                        </form>

                        <!-- Newly Created Pass Display Box -->
                        <div id="new-pass-result" class="mt-4 p-3 bg-light rounded-4 border border-primary border-opacity-25" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>បង្កើតជោគជ័យ</span>
                                <small class="text-muted" id="np-expiry-text"></small>
                            </div>
                            <div class="row align-items-center g-3">
                                <div class="col-sm-5 text-center">
                                    <div id="np-qrcode" class="d-inline-block p-2 bg-white rounded-3 shadow-sm border"></div>
                                    <div class="small text-muted mt-2">
                                        <i class="bi bi-camera me-1"></i>ឱ្យជំនួយការយកទូរស័ព្ទ<strong>បាញ់ QR នេះ</strong>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <label class="small fw-bold text-dark mb-1" title="តំណភ្ជាប់សម្រាប់ជំនួយការស្កេនផ្ទាល់">តំណភ្ជាប់ស្កេន</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control form-control-sm font-monospace" id="np-url-input" readonly>
                                        <button class="btn btn-outline-primary btn-sm fw-bold" type="button" id="btn-copy-np-url">
                                            <i class="bi bi-clipboard me-1"></i> ចម្លង
                                        </button>
                                    </div>
                                    <div class="p-2 rounded bg-white border small text-muted">
                                        <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                        ជំនួយការគ្រាន់តែចុច Link នេះតាម Telegram ឬ Browser គឺអាចស្កេនបានភ្លាមៗ <strong>ដោយមិនបាច់មានគណនី ឬចាំ Password ឡើយ</strong>!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Passes List (Active vs Closed) -->
                    <div class="tab-pane fade" id="tab-list-pass" role="tabpanel">
                        <!-- Filter Bar for Active vs Closed Passes -->
                        <div class="d-flex align-items-center justify-content-between mb-3 bg-light p-1.5 rounded-pill border">
                            <div class="d-flex gap-1" id="pass-filter-buttons">
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-primary text-white shadow-sm" id="btn-filter-active" onclick="filterPassList('active')">
                                    <i class="bi bi-check-circle-fill me-1"></i> កំពុងសកម្ម (<span id="filter-count-active">0</span>)
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary" id="btn-filter-closed" onclick="filterPassList('closed')">
                                    <i class="bi bi-x-circle-fill me-1"></i> បានបិទ / ផុតកំណត់ (<span id="filter-count-closed">0</span>)
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-light text-secondary" id="btn-filter-all" onclick="filterPassList('all')">
                                    ទាំងអស់ (<span id="filter-count-all">0</span>)
                                </button>
                            </div>
                        </div>

                        <div id="helper-passes-container" class="d-flex flex-column gap-3">
                            <div class="text-center py-5 text-muted">
                                <span class="spinner-border spinner-border-sm me-2 text-primary"></span> កំពុងទាញទិន្នន័យ...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3 rounded-pill" data-bs-dismiss="modal">បិទ</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Show Large QR for Pass -->
<div class="modal fade" id="viewPassQrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4 border-0 shadow-lg rounded-4">
            <h6 class="fw-bold mb-1" id="vqr-label"></h6>
            <p class="small text-muted mb-3" id="vqr-expiry"></p>
            <div id="vqr-qrcode-box" class="d-flex justify-content-center mb-3"></div>
            <div class="input-group input-group-sm mb-3">
                <input type="text" class="form-control font-monospace" id="vqr-url" readonly>
                <button class="btn btn-outline-primary" type="button" id="btn-vqr-copy"><i class="bi bi-clipboard"></i></button>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
    const WORKSHOP_ID = <?= $workshopId ?>;
    const CSRF_TOKEN = '<?= Session::get('_csrf_token') ?>';
    const APP_URL = '<?= APP_URL ?>';
    const INITIAL_SCAN_MODE = '<?= $workshop['checkin_scan_mode'] ?? 'qr' ?>';
    const DAILY_SCANS_REQUIRED = <?= (int)($workshop['daily_scans_required'] ?? 1) ?>;
</script>
<script src="<?= APP_URL ?>/assets/js/sweetalert2.all.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/checkin.js?v=<?= filemtime(ROOT_PATH . '/assets/js/checkin.js') ?>"></script>
