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
</style>

<!-- Top Navigation & Stats Bar -->
<div class="stats-bar px-3 py-2 d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= APP_URL ?>/workshops/<?= $workshopId ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ត្រឡប់
        </a>
        <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($workshop['name']) ?></h5>
        <span class="badge bg-primary-subtle text-primary border ms-2">តុស្កេនវត្តមាន</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="text-end">
            <div class="small text-muted mb-0" style="line-height: 1;">បានស្កេនវត្តមាន</div>
            <div class="fw-bold fs-5 text-success" id="stats-checked-in">
                <?= (int)($stats['checked_in'] ?? 0) ?> / <?= (int)($stats['total_confirmed'] ?? 0) ?>
            </div>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="fastModeToggle" checked>
            <label class="form-check-label small text-muted" for="fastModeToggle">ស្កេនលឿន</label>
        </div>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#checkinHelpModal">
            <i class="bi bi-question-circle me-1"></i> របៀបប្រើ
        </button>
    </div>
</div>

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
    <span class="badge bg-light text-secondary border small">សំឡេងប៊ីប: បើក</span>
</div>

<div class="container-fluid pb-5 px-0">
    <div class="row g-4">
        <!-- Left Column: Camera Scanner & Manual Search -->
        <div class="col-lg-6">
            <!-- Camera Scanner Card -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark small">
                            <i class="bi bi-camera-video-fill text-primary me-1"></i>ស្កេនកូដ QR តាមកាមេរ៉ា
                        </span>
                        <span class="badge bg-success-subtle text-success small">ដំណើរការ</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Camera Switcher (Auto-selects Logi 1080P if found) -->
                        <div id="camera-select-wrapper" style="display: none;">
                            <select id="camera-select" class="form-select form-select-sm py-0 px-2" style="font-size: 0.75rem; height: 26px; max-width: 175px;">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="scanner-container">
                        <!-- Floating Zoom & Focus Buttons on Left: 1x, 2x, 3x, 5x, AF -->
                        <div class="zoom-sidebar" id="zoom-sidebar" title="Zoom & Auto Focus">
                            <button type="button" class="btn-zoom active" data-zoom="1" title="ធម្មតា (1x)">1x</button>
                            <button type="button" class="btn-zoom" data-zoom="2" title="Zoom 2x">2x</button>
                            <button type="button" class="btn-zoom" data-zoom="3" title="Zoom 3x">3x</button>
                            <button type="button" class="btn-zoom" data-zoom="5" title="Zoom 5x">5x</button>
                            <button type="button" class="btn-zoom btn-autofocus" id="btn-autofocus" title="Auto Focus (ផ្តោតច្បាស់ស្វ័យប្រវត្តិ)">AF</button>
                        </div>

                        <div id="camera-focus-reticle" class="focus-reticle"></div>
                        <div id="reader"></div>
                        <div id="scan-result" class="scan-result-overlay">
                            <i id="scan-icon" class="bi display-1 mb-2"></i>
                            <h3 id="scan-message" class="text-center px-3"></h3>
                            <div id="scan-participant-name" class="fs-4 fw-bold mt-2"></div>
                        </div>
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
                                        <tr data-reg-id="<?= $ra['registration_id'] ?>" data-reg-code="<?= htmlspecialchars($ra['registration_code']) ?>">
                                            <td class="ps-3 fw-bold text-dark text-nowrap"><?= htmlspecialchars($ra['name']) ?></td>
                                            <td><span class="badge bg-primary-subtle text-primary border"><?= htmlspecialchars($ra['province']) ?></span></td>
                                            <td class="text-muted small text-truncate" style="max-width: 130px;" title="<?= htmlspecialchars($ra['company']) ?>"><?= htmlspecialchars($ra['company']) ?></td>
                                            <td class="text-muted small text-truncate" style="max-width: 100px;" title="<?= htmlspecialchars($ra['position']) ?>"><?= htmlspecialchars($ra['position']) ?></td>
                                            <td class="text-success fw-bold text-nowrap"><?= date('H:i:s', strtotime($ra['checked_in_at'])) ?></td>
                                            <td class="text-end pe-3 text-nowrap">
                                                <button type="button" class="btn btn-sm btn-outline-danger fw-normal" style="font-size: 0.75rem; padding: 3px 7px; line-height: 1.2;" title="លុបវត្តមាន (Reset)" onclick="resetAttendance(<?= $ra['registration_id'] ?>)"><i class="bi bi-arrow-counterclockwise" style="margin-right: 2px;"></i>លុបវត្តមាន</button>
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
                        <h6 class="fw-bold mb-1">៣. បោះពុម្ពប័ណ្ណសម្គាល់ខ្លួន (Badge)</h6>
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

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const WORKSHOP_ID = <?= $workshopId ?>;
    const CSRF_TOKEN = '<?= Session::get('_csrf_token') ?>';
    const APP_URL = '<?= APP_URL ?>';
</script>
<script src="<?= APP_URL ?>/assets/js/checkin.js"></script>
