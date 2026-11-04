<?php 
// Resolve registration data from available context variables
$reg = !empty($reg) ? $reg : ($registration ?? $lastReg ?? Session::get('last_registration') ?? []); 

// If the record only contains partial info (like after public registration redirect), hydrate it from DB
if (!empty($reg['id']) && (empty($reg['participant_name']) || empty($reg['workshop_name']))) {
    $db = Database::getInstance();
    $currentReg = $db->queryOne(
        "SELECT r.*, pq.token as qr_token, 
                p.name as participant_name, p.phone as participant_phone, p.email as participant_email,
                w.name as workshop_name, w.start_date, w.end_date, w.start_time, w.end_time, w.venue, w.address,
                t.name as ticket_name, t.price as ticket_price, t.currency as ticket_currency
         FROM registrations r 
         LEFT JOIN participant_qr pq ON pq.registration_id = r.id AND pq.is_active = 1
         LEFT JOIN participants p ON p.id = r.participant_id
         LEFT JOIN workshops w ON w.id = r.workshop_id
         LEFT JOIN tickets t ON t.id = r.ticket_id
         WHERE r.id = ?", 
        [$reg['id']]
    );
    if ($currentReg) {
        $reg = array_merge($reg, $currentReg);
        $status = $currentReg['status'];
    }
}
$status = $status ?? $reg['status'] ?? 'confirmed';
$regCode = !empty($regCode) ? $regCode : ($reg['registration_code'] ?? $reg['code'] ?? 'REG-000');
$qrToken = !empty($qrToken) ? $qrToken : ($reg['qr_token'] ?? $reg['token'] ?? $regCode);
if (empty($qrToken)) {
    $qrToken = $regCode;
}
$currentTicketUrl = APP_URL . '/ticket/' . urlencode($regCode);
$tgShareText = "សំបុត្រ និង QR Code សម្រាប់ចូលរួម " . ($reg['workshop_name'] ?? 'សិក្ខាសាលា') . ":\n" . $currentTicketUrl;

// Check if Official Telegram Bot is enabled in Platform Settings
$isTgBotActive = function_exists('telegram_is_bot_active') && telegram_is_bot_active();
if ($isTgBotActive) {
    $tgTargetUrl = telegram_get_ticket_bot_url($regCode);
} else {
    $tgTargetUrl = "https://t.me/share/url?url=" . urlencode($currentTicketUrl) . "&text=" . urlencode($tgShareText);
}

// Check if face is enrolled in participant_faces table
$hasEnrolledFace = false;
$latestPayment = null;
if (!empty($reg['id'])) {
    $db = Database::getInstance();
    $faceRecord = $db->queryOne("SELECT id, enrolled_at FROM participant_faces WHERE registration_id = ? AND is_active = 1", [$reg['id']]);
    if ($faceRecord) {
        $hasEnrolledFace = true;
    }
    $latestPayment = $db->queryOne("SELECT * FROM payments WHERE registration_id = ? ORDER BY id DESC LIMIT 1", [$reg['id']]);
}
$isPaymentSuccessful = (!empty($reg['payment_status']) && $reg['payment_status'] === 'paid') 
    || (!empty($latestPayment['status']) && $latestPayment['status'] === 'paid')
    || isset($_GET['paid'])
    || !empty($reg['just_paid']);

// Retrieve Organization / Institution branding logo (Priority: Business Branding Logo -> Workshop Branding Logo)
$orgLogo = null;
$workshopId = $reg['workshop_id'] ?? null;
$businessId = $reg['business_id'] ?? null;
$db = Database::getInstance();

if (empty($businessId) && !empty($workshopId)) {
    $businessId = $db->queryValue("SELECT business_id FROM workshops WHERE id = ?", [$workshopId]);
}

// 1. Primary: Business / Institution Branding Logo (set in Settings -> Branding)
if (!empty($businessId)) {
    $bb = $db->queryOne("SELECT logo_path FROM business_branding WHERE business_id = ?", [$businessId]);
    if (!empty($bb['logo_path'])) {
        $orgLogo = $bb['logo_path'];
    }
}
// 2. Fallback: Workshop-specific logo
if (empty($orgLogo) && !empty($workshopId)) {
    $wb = $db->queryOne("SELECT logo FROM workshop_branding WHERE workshop_id = ?", [$workshopId]);
    if (!empty($wb['logo'])) {
        $orgLogo = $wb['logo'];
    }
}
?>

<style>
/* Page container & background */
body {
    background-color: #f8fafc;
}
.ticket-page-wrapper {
    max-width: 400px;
    margin: 0 auto;
}

/* Premium E-Ticket Pass Card */
.ticket-card-premium {
    background: #ffffff;
    border-radius: 22px;
    box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    position: relative;
}

/* Ticket Header */
.ticket-header-premium {
    background: linear-gradient(145deg, #1e3a8a 0%, #1e40af 55%, #2563eb 100%);
    color: #ffffff;
    padding: 20px 20px 16px;
    position: relative;
}
.ticket-glass-pill {
    background: rgba(255, 255, 255, 0.18);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 30px;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
}
.ticket-status-pill {
    background: #10b981;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 30px;
    display: inline-flex;
    align-items: center;
}
.ticket-workshop-name {
    font-weight: 800;
    font-size: 1.15rem;
    line-height: 1.45;
    color: #ffffff;
    margin: 10px 0 10px;
    word-break: break-word;
}
.ticket-meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.ticket-meta-chip {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.16);
    color: rgba(255, 255, 255, 0.95);
    font-size: 0.74rem;
    padding: 3px 9px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
}

/* Perforated Stub Tear Divider */
.ticket-tear-divider {
    position: relative;
    height: 22px;
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.tear-notch {
    width: 24px;
    height: 24px;
    background-color: #f8fafc;
    border-radius: 50%;
    position: absolute;
    top: -1px;
}
.tear-notch-left {
    left: -12px;
    box-shadow: inset -2px 0 3px rgba(0, 0, 0, 0.05);
}
.tear-notch-right {
    right: -12px;
    box-shadow: inset 2px 0 3px rgba(0, 0, 0, 0.05);
}
.tear-dashed-line {
    width: calc(100% - 40px);
    border-bottom: 2px dashed #cbd5e1;
}

/* Ticket Body & QR */
.ticket-body-premium {
    padding: 16px 20px 16px;
    background: #ffffff;
    text-align: center;
}
.ticket-attendee-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 10px 14px;
    margin-bottom: 14px;
    text-align: left;
}
.ticket-micro-label {
    font-size: 0.65rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.ticket-attendee-name {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
}
.ticket-code-badge {
    font-family: monospace;
    font-weight: 800;
    font-size: 0.95rem;
    color: #2563eb;
    letter-spacing: 0.5px;
}

/* QR Container */
.qr-premium-frame {
    background: transparent;
    padding: 10px;
    border-radius: 16px;
    border: 1.5px solid #e2e8f0;
    display: inline-block;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
}
.qr-instruction-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.74rem;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    padding: 4px 12px;
    border-radius: 20px;
    margin-top: 10px;
}

/* Decorative barcode strip at bottom */
.ticket-barcode-wrap {
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px dashed #e2e8f0;
}
.ticket-barcode-bars-wrap {
    margin: 4px auto 3px;
    display: flex;
    justify-content: center;
}
.ticket-barcode-svg {
    width: 170px;
    height: 22px;
    display: block;
    opacity: 0.7;
}
.ticket-barcode-text {
    font-family: monospace;
    font-size: 0.65rem;
    color: #94a3b8;
    letter-spacing: 1.5px;
}

/* ======================================================== */
/* ACTION BUTTONS (Modeled after Fintech Circular Bar)      */
/* ======================================================== */
.action-card-panel {
    background: #ffffff;
    border-radius: 20px;
    padding: 16px 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    margin-top: 14px;
}
.action-buttons-flex {
    display: flex;
    align-items: flex-start;
    justify-content: space-around;
    gap: 6px;
}
.action-btn-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    min-width: 64px;
    transition: transform 0.15s ease;
    outline: none;
    color: inherit;
}
.action-btn-item:hover {
    transform: translateY(-2px);
    color: inherit;
}
.action-btn-item:active {
    transform: scale(0.94);
}
.action-circle-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    transition: all 0.2s ease;
    background-color: #f1f5f9;
    color: #1e293b;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.action-btn-text {
    margin-top: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
    letter-spacing: 0.2px;
    text-align: center;
    white-space: nowrap;
}

/* Telegram Button (PRIORITY) - Eye-catching Telegram Brand Blue */
.circle-telegram {
    background: linear-gradient(135deg, #2AABEE 0%, #229ED9 100%);
    color: #ffffff;
    box-shadow: 0 6px 16px rgba(34, 158, 217, 0.35);
}
.action-priority:hover .circle-telegram {
    box-shadow: 0 8px 22px rgba(34, 158, 217, 0.48);
}
.text-telegram {
    color: #0284c7;
    font-weight: 700;
}

/* Hover effects for soft circles */
.action-btn-item:hover .circle-save {
    background: #e2e8f0;
    color: #059669;
}
.action-btn-item:hover .circle-share {
    background: #e2e8f0;
    color: #2563eb;
}
.action-btn-item:hover .circle-home {
    background: #e2e8f0;
    color: #0f172a;
}

/* Toast alert popup */
.toast-popup {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #0f172a;
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 9999;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    animation: fadeInToast 0.25s ease;
}
@keyframes fadeInToast {
    from { opacity: 0; transform: translate(-50%, 15px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}

/* Success Animation Popup Overlay */
.success-popup-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 999999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
    margin: 0 !important;
    opacity: 1;
    transition: opacity 0.35s ease, visibility 0.35s ease;
}
.success-popup-overlay.fade-out {
    opacity: 0 !important;
    visibility: hidden !important;
    pointer-events: none !important;
}
.confetti-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}
.success-popup-card {
    background: #ffffff;
    border-radius: 28px;
    padding: 32px 24px 22px;
    max-width: 360px;
    width: 90%;
    margin: auto !important;
    text-align: center;
    box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 2;
    animation: successCardPop 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
@keyframes successCardPop {
    0% { transform: scale(0.65) translateY(20px); opacity: 0; }
    100% { transform: scale(1) translateY(0); opacity: 1; }
}

@keyframes bounceBadge {
    0% { transform: scale(0.35); opacity: 0; }
    60% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

/* Auto-close progress bar */
.success-autoclose-bar {
    width: 100%;
    height: 3px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
    margin-bottom: 4px;
}
.success-autoclose-fill {
    height: 100%;
    background: #10b981;
    width: 100%;
    animation: autoCloseTimer 2.2s linear forwards;
}
@keyframes autoCloseTimer {
    0% { width: 100%; }
    100% { width: 0%; }
}

/* Refined VIP / Official Event Ticket Badge */
.ticket-entry-badge-wrap {
    text-align: center;
    padding: 10px 0 6px;
    background: #ffffff;
    position: relative;
}
.ticket-entry-badge {
    display: inline-block;
    background: #eff6ff;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1.5px solid #bfdbfe;
    padding: 5px 16px 5px 10px;
    border-radius: 50px;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.12);
    transition: all 0.2s ease;
    text-align: center;
    letter-spacing: normal;
}
.ticket-entry-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.18);
}
.ticket-entry-badge-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    background-color: #2563eb !important;
    color: #ffffff !important;
    border-radius: 50%;
    font-size: 11px;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);
    vertical-align: middle;
    margin-right: 6px;
}
.ticket-entry-badge-text {
    display: inline-block;
    vertical-align: middle;
    color: #1d4ed8 !important;
    font-size: 0.88rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: 0;
}

/* Face check-in button anchored at bottom-right corner of the ticket */
.ticket-corner-face-btn-wrap {
    position: absolute;
    bottom: 10px;
    right: 12px;
    z-index: 10;
}
.ticket-corner-face-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: #ffffff;
    border: none;
    border-radius: 50px;
    padding: 4px 11px 4px 8px;
    font-size: 0.72rem;
    font-weight: 500;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.35);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-decoration: none;
    line-height: 1.2;
}
.ticket-corner-face-btn:hover {
    transform: translateY(-1.5px) scale(1.03);
    box-shadow: 0 5px 14px rgba(37, 99, 235, 0.4), 0 0 0 1.5px rgba(255, 255, 255, 0.5);
    color: #ffffff;
}
.ticket-corner-face-btn.face-enrolled {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.35);
}
.floating-face-icon-box {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}
.floating-face-label {
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.1px;
}
.floating-pulse-ring {
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.7);
    animation: fabPulse 1.8s infinite cubic-bezier(0, 0.2, 0.8, 1);
}
@keyframes fabPulse {
    0% { transform: scale(0.85); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}
.floating-check-badge {
    position: absolute;
    bottom: -3px;
    right: -5px;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #ffffff;
    color: #059669;
    font-size: 8.5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>

<?php 
$showSuccessPopup = isset($_GET['already_registered']) 
    || isset($_GET['paid']) 
    || !empty($reg['just_paid']) 
    || Session::has('info') 
    || !empty($_GET['success'])
    || !empty($_GET['new']);

if ($showSuccessPopup): 
    $isPaidPopup = (isset($_GET['paid']) || !empty($reg['just_paid']));
    $isAlreadyReg = (isset($_GET['already_registered']) || Session::has('info'));

    if ($isPaidPopup) {
        $popupTitle = 'ការទូទាត់ប្រាក់ជោគជ័យ!';
        $popupSub = 'ប្រព័ន្ធបានផ្ទៀងផ្ទាត់ការទូទាត់ និងចេញសំបុត្រផ្លូវការជូនរួចរាល់';
    } elseif ($isAlreadyReg) {
        $popupTitle = 'បានចុះឈ្មោះរួចរាល់!';
        $popupSub = 'លោកអ្នកបានចុះឈ្មោះរួចហើយ នេះជាសំបុត្រ និងកូដ QR របស់អ្នក';
    } else {
        $popupTitle = 'ការចុះឈ្មោះជោគជ័យ!';
        $popupSub = 'ប្រព័ន្ធបានចេញសំបុត្រផ្លូវការ និង QR Code ជូនលោកអ្នករួចរាល់';
    }
?>
<!-- Success Celebration Popup Modal (Centered, Auto-close, No button) -->
<div id="successAnimationOverlay" class="success-popup-overlay" onclick="closeSuccessPopup()">
    <canvas id="confettiCanvas" class="confetti-canvas"></canvas>

    <div class="success-popup-card" onclick="event.stopPropagation()">
        <!-- Verified Badge Icon with gentle pop animation -->
        <div class="mb-3 d-flex justify-content-center">
            <img src="<?= APP_URL ?>/assets/images/verified_badge.png" alt="Verified" style="width: 76px; height: 76px; object-fit: contain; filter: drop-shadow(0 8px 18px rgba(16, 185, 129, 0.35)); animation: bounceBadge 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        </div>

        <h4 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">
            <?= htmlspecialchars($popupTitle) ?>
        </h4>
        <p class="text-muted small px-2 mb-3" style="font-size: 0.85rem; line-height: 1.5;">
            <?= htmlspecialchars($popupSub) ?>
        </p>

        <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1 bg-light rounded-pill border mb-3">
            <i class="bi bi-ticket-perforated text-primary"></i>
            <span class="font-monospace fw-bold text-dark small"><?= htmlspecialchars($regCode) ?></span>
        </div>

        <!-- Auto-close countdown progress bar -->
        <div class="success-autoclose-bar">
            <div class="success-autoclose-fill"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <?php if ($status === 'confirmed' || $status === 'attended' || $status === 'completed' || $isPaymentSuccessful): ?>
                <div class="ticket-page-wrapper">

                    <!-- TICKET CARD CAPTURE AREA -->
                    <div id="ticketCaptureArea" class="ticket-card-premium" data-code="<?= htmlspecialchars($regCode) ?>">
                        
                        <!-- Header -->
                        <div class="ticket-header-premium position-relative">
                            <!-- Top-Right Verified Badge (No text, enlarged) -->
                            <div class="position-absolute" style="top: 14px; right: 16px; z-index: 2;">
                                <img src="<?= APP_URL ?>/assets/images/verified_badge.png" alt="Verified" style="width: 36px; height: 36px; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.25));" title="បានផ្ទៀងផ្ទាត់រួចរាល់">
                            </div>

                            <!-- Organization Logo + Workshop Title + Meta badges -->
                            <div class="d-flex align-items-center gap-3 pe-5 pt-1">
                                <?php if (!empty($orgLogo)): ?>
                                    <img src="<?= APP_URL . '/' . htmlspecialchars(ltrim($orgLogo, '/')) ?>" alt="Logo" class="rounded-3 flex-shrink-0" style="width: 54px; height: 54px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.25); box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);">
                                <?php else: ?>
                                    <div class="rounded-3 text-white d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 54px; height: 54px; font-size: 24px; background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25);">
                                        <i class="bi bi-buildings"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-grow-1 overflow-hidden">
                                    <h4 class="ticket-workshop-name m-0" style="font-size: 1.18rem; line-height: 1.35; color: #ffffff;">
                                        <?= htmlspecialchars($reg['workshop_name'] ?? 'សិក្ខាសាលា') ?>
                                    </h4>

                                    <div class="ticket-meta-badges mt-2">
                                        <span class="ticket-meta-chip">
                                            <i class="bi bi-calendar3 me-1"></i> <?= formatDate($reg['start_date'] ?? date('Y-m-d')) ?>
                                        </span>
                                        <?php if (!empty($reg['start_time'])): ?>
                                            <span class="ticket-meta-chip">
                                                <i class="bi bi-clock me-1"></i> <?= substr($reg['start_time'], 0, 5) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($reg['venue'])): ?>
                                            <span class="ticket-meta-chip">
                                                <i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($reg['venue']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Perforated Tear Divider -->
                        <div class="ticket-tear-divider">
                            <div class="tear-notch tear-notch-left"></div>
                            <div class="tear-dashed-line"></div>
                            <div class="tear-notch tear-notch-right"></div>
                        </div>

                        <!-- Relocated & Refined: សំបុត្រចូលរួម Badge -->
                        <div class="ticket-entry-badge-wrap">
                            <div class="ticket-entry-badge">
                                <span class="ticket-entry-badge-icon">
                                    <i class="bi bi-ticket-perforated-fill"></i>
                                </span>
                                <span class="ticket-entry-badge-text">សំបុត្រចូលរួម</span>
                            </div>
                        </div>

                        <!-- Ticket Body -->
                        <div class="ticket-body-premium position-relative">
                            
                            <!-- Attendee Info Card -->
                            <div class="ticket-attendee-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="ticket-micro-label">ឈ្មោះសិក្ខាកាម</div>
                                        <div class="ticket-attendee-name"><?= htmlspecialchars($reg['participant_name'] ?? 'សិក្ខាកាម') ?></div>
                                    </div>
                                    <div class="text-end">
                                        <div class="ticket-micro-label">កូដសំបុត្រ</div>
                                        <div class="ticket-code-badge"><?= htmlspecialchars($regCode) ?></div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light-subtle">
                                    <div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 fw-bold" style="font-size: 0.72rem;">
                                            <?= htmlspecialchars($reg['ticket_name'] ?: 'ទូទៅ') ?>
                                        </span>
                                    </div>
                                    <div class="text-success small fw-bold" style="font-size: 0.75rem;">
                                        <i class="bi bi-shield-check me-1"></i> សុពលភាព
                                    </div>
                                </div>
                            </div>

                            <!-- QR Code Holder -->
                            <div class="qr-premium-frame">
                                <div id="qrcode" class="d-flex justify-content-center"></div>
                                <noscript>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=165x165&data=<?= urlencode($qrToken) ?>" alt="QR" width="165" height="165">
                                </noscript>
                            </div>

                            <div>
                                <span class="qr-instruction-chip">
                                    <i class="bi bi-qr-code-scan"></i> ស្កេនពេលចូលរួម
                                </span>
                            </div>

                            <!-- Decorative barcode & serial -->
                            <div class="ticket-barcode-wrap position-relative">
                                <div class="ticket-barcode-bars-wrap">
                                    <svg class="ticket-barcode-svg" viewBox="0 0 170 22" fill="#1e293b" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="0" y="0" width="2" height="22"/>
                                        <rect x="4" y="0" width="1" height="22"/>
                                        <rect x="7" y="0" width="3" height="22"/>
                                        <rect x="12" y="0" width="1" height="22"/>
                                        <rect x="15" y="0" width="2" height="22"/>
                                        <rect x="19" y="0" width="4" height="22"/>
                                        <rect x="25" y="0" width="1" height="22"/>
                                        <rect x="28" y="0" width="3" height="22"/>
                                        <rect x="33" y="0" width="2" height="22"/>
                                        <rect x="37" y="0" width="1" height="22"/>
                                        <rect x="40" y="0" width="3" height="22"/>
                                        <rect x="45" y="0" width="2" height="22"/>
                                        <rect x="49" y="0" width="4" height="22"/>
                                        <rect x="55" y="0" width="1" height="22"/>
                                        <rect x="58" y="0" width="2" height="22"/>
                                        <rect x="62" y="0" width="3" height="22"/>
                                        <rect x="67" y="0" width="1" height="22"/>
                                        <rect x="70" y="0" width="4" height="22"/>
                                        <rect x="76" y="0" width="2" height="22"/>
                                        <rect x="80" y="0" width="1" height="22"/>
                                        <rect x="83" y="0" width="3" height="22"/>
                                        <rect x="88" y="0" width="2" height="22"/>
                                        <rect x="92" y="0" width="4" height="22"/>
                                        <rect x="98" y="0" width="1" height="22"/>
                                        <rect x="101" y="0" width="3" height="22"/>
                                        <rect x="106" y="0" width="2" height="22"/>
                                        <rect x="110" y="0" width="1" height="22"/>
                                        <rect x="113" y="0" width="3" height="22"/>
                                        <rect x="118" y="0" width="2" height="22"/>
                                        <rect x="122" y="0" width="4" height="22"/>
                                        <rect x="128" y="0" width="1" height="22"/>
                                        <rect x="131" y="0" width="2" height="22"/>
                                        <rect x="135" y="0" width="3" height="22"/>
                                        <rect x="140" y="0" width="1" height="22"/>
                                        <rect x="143" y="0" width="4" height="22"/>
                                        <rect x="149" y="0" width="2" height="22"/>
                                        <rect x="153" y="0" width="1" height="22"/>
                                        <rect x="156" y="0" width="3" height="22"/>
                                        <rect x="161" y="0" width="2" height="22"/>
                                        <rect x="165" y="0" width="2" height="22"/>
                                        <rect x="168" y="0" width="2" height="22"/>
                                    </svg>
                                </div>
                                <div class="ticket-barcode-text"><?= htmlspecialchars($regCode) ?> &bull; PASS-<?= strtoupper(substr(md5($regCode), 0, 6)) ?></div>
                            </div>

                            <!-- Face check-in button anchored at bottom-right corner of the ticket -->
                            <div class="ticket-corner-face-btn-wrap" data-html2canvas-ignore="true" id="floatingFaceScanBtn">
                                <button type="button" class="btn ticket-corner-face-btn <?= $hasEnrolledFace ? 'face-enrolled' : '' ?>" onclick="openPublicFaceEnrollModal()" title="<?= $hasEnrolledFace ? 'បានស្កេនផ្ទៃមុខរួច (ចុចដើម្បីថតឡើងវិញ)' : 'ស្កេនផ្ទៃមុខទុកមុន សម្រាប់ចូលកម្មវិធីលឿន' ?>">
                                    <div class="floating-face-icon-box">
                                        <i class="bi bi-person-bounding-box"></i>
                                        <?php if (!$hasEnrolledFace): ?>
                                            <span class="floating-pulse-ring"></span>
                                        <?php else: ?>
                                            <span class="floating-check-badge"><i class="bi bi-check-lg"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="floating-face-label"><?= $hasEnrolledFace ? 'បានស្កេនមុខ' : 'ស្កេនមុខ' ?></span>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- ACTION BUTTONS PANEL (Fintech Style with Priority Telegram) -->
                    <div class="action-card-panel">
                        <div class="action-buttons-flex">
                            
                            <!-- 1. Telegram (PRIORITY) -->
                            <a href="<?= htmlspecialchars($tgTargetUrl) ?>" 
                               target="_blank" 
                               class="action-btn-item action-priority" 
                               title="Telegram">
                                <div class="action-circle-icon circle-telegram">
                                    <i class="bi bi-telegram"></i>
                                </div>
                                <span class="action-btn-text text-telegram">Telegram</span>
                            </a>

                            <!-- 2. Save Image -->
                            <button type="button" 
                                    id="btnSaveGallery" 
                                    class="action-btn-item" 
                                    title="រក្សាទុក">
                                <div class="action-circle-icon circle-save">
                                    <i class="bi bi-download"></i>
                                </div>
                                <span class="action-btn-text">រក្សាទុក</span>
                            </button>

                            <!-- 3. Share -->
                            <button type="button" 
                                    id="btnShareTicket" 
                                    onclick="shareTicketUrl()" 
                                    class="action-btn-item" 
                                    title="ចែករំលែក">
                                <div class="action-circle-icon circle-share">
                                    <i class="bi bi-box-arrow-up"></i>
                                </div>
                                <span class="action-btn-text">ចែករំលែក</span>
                            </button>

                            <!-- 4. Home -->
                            <a href="<?= APP_URL ?>" 
                               class="action-btn-item" 
                               title="ទំព័រដើម">
                                <div class="action-circle-icon circle-home">
                                    <i class="bi bi-house-door"></i>
                                </div>
                                <span class="action-btn-text">ទំព័រដើម</span>
                            </a>

                        </div>
                    </div>

                    <?php if ($isPaymentSuccessful || !empty($latestPayment)): ?>
                    <!-- Payment Receipt Card (បង្កាន់ដៃ / វិក្កយបត្របង់ប្រាក់) -->
                    <div class="card border-0 rounded-4 shadow-sm p-3 mt-3 bg-white" id="paymentReceiptCard">
                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-receipt text-primary fs-5"></i>
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">បង្កាន់ដៃទូទាត់ប្រាក់ (Receipt)</h6>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5" style="font-size: 10.5px;">
                                <i class="bi bi-check-circle-fill me-1"></i> បានបង់រួច
                            </span>
                        </div>

                        <div class="bg-light rounded-3 p-2.5 border mb-2 text-start" style="font-size: 12px;">
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">កូដសំបុត្រ៖</span>
                                <strong class="text-primary font-monospace"><?= htmlspecialchars($regCode) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">អ្នកបង់ប្រាក់៖</span>
                                <strong class="text-dark"><?= htmlspecialchars($reg['participant_name'] ?? 'សិក្ខាកាម') ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">ប្រភេទសំបុត្រ៖</span>
                                <span class="text-dark fw-medium"><?= htmlspecialchars($reg['ticket_name'] ?? 'សំបុត្រទូទៅ') ?></span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">មធ្យោបាយទូទាត់៖</span>
                                <span class="text-dark fw-semibold">
                                    <span class="badge bg-danger text-white me-1 py-0 px-1" style="font-size:9px;">KHQR</span>
                                    <?= htmlspecialchars($reg['payment_method'] ?? $latestPayment['notes'] ?? 'Bakong KHQR') ?>
                                </span>
                            </div>
                            <?php 
                            $trxRefShow = $reg['transaction_reference'] ?? $latestPayment['transaction_reference'] ?? '';
                            if (!empty($trxRefShow)): ?>
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">លេខយោងប្រតិបត្តិការ (Ref)៖</span>
                                <span class="font-monospace text-muted small"><?= htmlspecialchars($trxRefShow) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between py-1 border-bottom border-light-subtle">
                                <span class="text-muted">កាលបរិច្ឆេទ៖</span>
                                <span class="text-muted small"><?= htmlspecialchars($reg['paid_at'] ?? $latestPayment['created_at'] ?? date('Y-m-d H:i')) ?></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2">
                                <span class="fw-bold text-dark">ទឹកប្រាក់បានទូទាត់៖</span>
                                <span class="fw-bold text-success fs-5">
                                    $<?= number_format((float)($reg['final_amount'] ?? $latestPayment['amount'] ?? 0), 2) ?> USD
                                </span>
                            </div>
                        </div>

                        <div class="text-muted text-center" style="font-size: 11px;">
                            <i class="bi bi-shield-check text-success me-1"></i>
                            វិក្កយបត្រនេះត្រូវបានផ្ទៀងផ្ទាត់ដោយប្រព័ន្ធស្វ័យប្រវត្តិតាម Bakong Network
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            <?php elseif ($status === 'pending_verification' || ($reg['payment_status'] ?? '') === 'pending'): ?>
                <!-- Pending Payment Verification -->
                <div class="card shadow-sm border-0 p-4 text-center">
                    <div class="bg-warning text-white rounded-circle d-inline-flex p-3 mb-3 mx-auto">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-warning mb-2">រង់ចាំការផ្ទៀងផ្ទាត់ការបង់ប្រាក់</h4>
                    <p class="text-muted mb-3">ការចុះឈ្មោះសម្រាប់ <strong><?= htmlspecialchars($reg['workshop_name'] ?? 'សិក្ខាសាលា') ?></strong> កំពុងរង់ចាំការត្រួតពិនិត្យការទូទាត់ប្រាក់។</p>
                    <div class="alert alert-info py-2 px-3 d-inline-block small mb-3">
                        <strong>ទឹកប្រាក់ត្រូវបង់:</strong> <?= htmlspecialchars($reg['currency'] ?? 'USD') ?> <?= number_format($reg['final_amount'] ?? 0, 2) ?>
                    </div>
                    
                    <div class="mb-3">
                        <span class="text-muted small d-block">លេខកូដចុះឈ្មោះរបស់អ្នក៖</span>
                        <strong class="font-monospace fs-5 text-primary"><?= htmlspecialchars($regCode) ?></strong>
                    </div>

                    <?php if (!empty($reg['has_proof'])): ?>
                        <div class="alert alert-success py-2 px-3 small mb-3">
                            <i class="bi bi-check-circle-fill me-1"></i> បានទទួលបង្កាន់ដៃបង់ប្រាក់រួចរាល់! ក្រុមការងារនឹងផ្ទៀងផ្ទាត់ និងបញ្ជាក់សំបុត្រជូនក្នុងពេលឆាប់ៗ។
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="<?= APP_URL ?>/ticket/<?= urlencode($regCode) ?>" class="btn btn-outline-primary fw-bold px-3 py-2">
                            <i class="bi bi-ticket-perforated me-1"></i> មើលសំបុត្រ & ស្ថានភាព
                        </a>
                        <a href="<?= APP_URL ?>/event/<?= htmlspecialchars($reg['workshop_slug'] ?? $slug ?? '') ?>" class="btn btn-light px-3 py-2">
                            ត្រឡប់ទៅកម្មវិធី
                        </a>
                    </div>
                </div>

            <?php elseif ($status === 'waitlisted'): ?>
                <!-- Waitlisted -->
                <div class="card shadow-sm border-0 p-4 text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex p-3 mb-3 mx-auto">
                        <i class="bi bi-list-ol fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-info mb-2">ក្នុងបញ្ជីរង់ចាំ</h3>
                    <p class="text-muted mb-3">សិក្ខាសាលានេះបានពេញកៅអីហើយ។</p>
                    <?php if (!empty($reg['participant_email'])): ?>
                        <p class="small text-muted">ប្រព័ន្ធនឹងជូនដំណឹងតាមអ៊ីមែល <?= htmlspecialchars($reg['participant_email']) ?> ភ្លាមៗប្រសិនបើមានកៅអីទំនេរ។</p>
                    <?php else: ?>
                        <p class="small text-muted">ប្រព័ន្ធនឹងជូនដំណឹងតាមរយៈលេខទូរស័ព្ទរបស់អ្នកភ្លាមៗប្រសិនបើមានកៅអីទំនេរ។</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Generic Status -->
                <div class="card shadow-sm border-0 p-4 text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex p-3 mb-3 mx-auto">
                        <i class="bi bi-info-circle fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-2">ទទួលបានទិន្នន័យចុះឈ្មោះរួចរាល់</h3>
                    <p class="lead mb-3">ស្ថានភាព: <?= statusBadge($status) ?></p>
                    <a href="<?= APP_URL ?>" class="btn btn-outline-secondary px-4 py-2">&larr; ត្រឡប់ទៅកាន់ទំព័រដើមវិញ</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Toast Popup Notification -->
<div id="toastNotification" class="toast-popup d-none">
    <i class="bi bi-check-circle-fill me-2 text-success"></i>
    <span id="toastMessage">បានចម្លងលីងសំបុត្រ!</span>
</div>

<!-- Modal: Saved Image Preview (Helpful for iOS Safari tap & hold save) -->
<div class="modal fade" id="imageSavedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 shadow-lg text-center p-3">
            <div class="modal-body p-2">
                <div class="text-success mb-2">
                    <i class="bi bi-check-circle-fill fs-1"></i>
                </div>
                <h6 class="fw-bold mb-1">បានបង្កើតសំបុត្ររួចរាល់!</h6>
                <p class="text-muted small mb-2">ប្រសិនបើមិនទាន់បានរក្សាទុកទេ សូមចុចសង្កត់លើរូបភាពខាងក្រោម ហើយជ្រើសរើស <strong>«រក្សាទុករូបភាព»</strong>៖</p>
                <div class="border rounded p-1 mb-3 bg-light shadow-xs">
                    <img id="savedImagePreview" src="" class="img-fluid rounded" alt="Ticket Preview">
                </div>
                <button type="button" class="btn btn-secondary w-100 py-2 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-check2 me-1"></i> បិទ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Client-side QRCode and html2canvas Libraries -->
<script src="<?= APP_URL ?>/assets/js/qrcode.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/html2canvas.min.js"></script>
<script>
    if (typeof QRCode === 'undefined') {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        document.head.appendChild(s);
    }
    if (typeof html2canvas === 'undefined') {
        const s2 = document.createElement('script');
        s2.src = 'https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js';
        document.head.appendChild(s2);
    }
</script>

<script>
// Toast function
function showToast(msg) {
    const toast = document.getElementById('toastNotification');
    const msgEl = document.getElementById('toastMessage');
    if (toast && msgEl) {
        msgEl.textContent = msg;
        toast.classList.remove('d-none');
        setTimeout(() => {
            toast.classList.add('d-none');
        }, 2400);
    }
}

// Share function
async function shareTicketUrl() {
    const url = <?= json_encode($currentTicketUrl) ?>;
    const title = <?= json_encode($reg['workshop_name'] ?? 'សំបុត្រចូលរួម') ?>;
    const text = 'សំបុត្រ និង QR Code សម្រាប់ចូលរួម ' + title;

    if (navigator.share) {
        try {
            await navigator.share({
                title: title,
                text: text,
                url: url
            });
            return;
        } catch (err) {
            // Cancelled or fallback to copy
        }
    }

    try {
        await navigator.clipboard.writeText(url);
        showToast('បានចម្លងលីងសំបុត្ររួចរាល់!');
    } catch (e) {
        prompt('ចម្លងលីងសំបុត្រនេះ៖', url);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Generate QR Code
    const qrContainer = document.getElementById('qrcode');
    const qrToken = <?= json_encode($qrToken ?: $regCode) ?>;
    
    if (qrContainer && qrToken) {
        try {
            new QRCode(qrContainer, {
                text: qrToken,
                width: 165,
                height: 165,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.error('QR code generation error:', e);
            qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=165x165&data=' + encodeURIComponent(qrToken) + '" width="165" height="165" alt="QR">';
        }
    }

    // 2. Save to Gallery Handler
    const saveBtn = document.getElementById('btnSaveGallery');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<div class="action-circle-icon circle-save"><span class="spinner-border spinner-border-sm text-primary" role="status"></span></div><span class="action-btn-text">កំពុងរក្សា...</span>';

            try {
                const ticketEl = document.getElementById('ticketCaptureArea');
                if (!ticketEl) return;

                // Ensure all custom web fonts (like Kantumruy Pro) are fully loaded
                if (document.fonts && document.fonts.ready) {
                    await document.fonts.ready;
                }

                const canvas = await html2canvas(ticketEl, {
                    scale: 2.5,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                const regCodeStr = ticketEl.dataset.code || 'REG';
                const fileName = 'Ticket-' + regCodeStr + '.png';
                const dataUrl = canvas.toDataURL('image/png');

                let sharedViaNative = false;
                if (navigator.canShare) {
                    try {
                        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                        if (blob) {
                            const file = new File([blob], fileName, { type: 'image/png' });
                            if (navigator.canShare({ files: [file] })) {
                                await navigator.share({
                                    files: [file],
                                    title: 'សំបុត្រចូលរួមសិក្ខាសាលា',
                                    text: 'សំបុត្រ និងកូដ QR សម្រាប់ចូលរួម'
                                });
                                sharedViaNative = true;
                            }
                        }
                    } catch (shareErr) {
                        console.log('Native share skipped or cancelled:', shareErr);
                    }
                }

                // Automatic direct file download fallback
                const downloadLink = document.createElement('a');
                downloadLink.download = fileName;
                downloadLink.href = dataUrl;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                showToast('បានរក្សាទុករូបភាពសំបុត្រ!');

                if (!sharedViaNative) {
                    const previewImg = document.getElementById('savedImagePreview');
                    if (previewImg) {
                        previewImg.src = dataUrl;
                    }
                    const modalEl = document.getElementById('imageSavedModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.show();
                    }
                }

            } catch (err) {
                console.error('Error generating ticket image:', err);
                showToast('មានបញ្ហាក្នុងការទាញយក។');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
});
</script>

<!-- Modal: Public Face Enrollment (Opt-In Face ID) -->
<div class="modal fade" id="publicFaceEnrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 rounded-4 shadow-lg text-center p-3">
            <div class="modal-header border-0 pb-1">
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-1.5 rounded-circle">
                        <i class="bi bi-person-bounding-box"></i>
                    </span>
                    ស្កេនមុខ
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2 text-center">
                <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                    សូមដាក់ផ្ទៃមុខឱ្យចំកណ្តាលរង្វង់ពងក្រពើ
                </p>

                <!-- Camera Viewport with Oval Guide -->
                <div class="position-relative mx-auto rounded-4 overflow-hidden mb-2.5 bg-black shadow-xs" style="width: 100%; max-width: 320px; aspect-ratio: 4/3;">
                    <video id="pubFaceVideo" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                    <canvas id="pubFaceCanvas" style="position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; transform: scaleX(-1);"></canvas>
                    <img id="pubFacePreviewImg" style="display:none; position: absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;" alt="Face Preview">
                    <div id="pubFaceOval" class="position-absolute" style="top:50%; left:50%; transform:translate(-50%, -50%); width: 160px; height: 200px; border: 2.5px dashed rgba(255,255,255,0.6); border-radius: 50%; pointer-events:none; transition: all 0.25s ease;"></div>
                </div>

                <div id="pubFaceStatusBadge" class="badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill mb-3" style="font-size: 0.76rem;">
                    កំពុងបើកកាមេរ៉ា...
                </div>

                <!-- Single Clean Button: ថតមុខ -->
                <button type="button" id="btnPubCaptureFace" class="btn btn-primary w-100 py-2.5 fw-bold rounded-pill shadow-xs" onclick="handlePubFaceAction()">
                    <i class="bi bi-camera-fill me-1"></i> ថតមុខ
                </button>

                <!-- Native direct camera capture (capture="user") fallback when WebRTC stream is restricted -->
                <input type="file" id="pubFaceDirectCamera" accept="image/*" capture="user" class="d-none" onchange="handlePubFaceDirectPhoto(event)">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
let pubFaceModelLoaded = false;
let pubFaceStream = null;
let pubFaceDescriptor = null;
let pubFacePhoto = null;
let pubFaceCheckInterval = null;
const PUB_FACE_REG_CODE = <?= json_encode($regCode) ?>;

function openPublicFaceEnrollModal() {
    const modalEl = document.getElementById('publicFaceEnrollModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

const pubModalEl = document.getElementById('publicFaceEnrollModal');
if (pubModalEl) {
    pubModalEl.addEventListener('shown.bs.modal', startPubFaceCamera);
    pubModalEl.addEventListener('hidden.bs.modal', stopPubFaceCamera);
}

async function loadPubFaceModels() {
    if (pubFaceModelLoaded) return true;
    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
            faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
            faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/')
        ]);
        pubFaceModelLoaded = true;
        return true;
    } catch (e) {
        console.error('Model load error:', e);
        return false;
    }
}

async function startPubFaceCamera() {
    const video = document.getElementById('pubFaceVideo');
    const canvas = document.getElementById('pubFaceCanvas');
    const previewImg = document.getElementById('pubFacePreviewImg');
    const oval = document.getElementById('pubFaceOval');
    const statusBadge = document.getElementById('pubFaceStatusBadge');
    const btn = document.getElementById('btnPubCaptureFace');

    if (previewImg) previewImg.style.display = 'none';
    if (video) video.style.display = 'block';
    if (canvas) canvas.style.display = 'block';

    pubFaceDescriptor = null;
    pubFacePhoto = null;

    statusBadge.className = 'badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill mb-3';
    statusBadge.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងបើកកាមេរ៉ា...';

    // 1. Ensure models are loaded
    if (!pubFaceModelLoaded) {
        const loaded = await loadPubFaceModels();
        if (!loaded) {
            statusBadge.className = 'badge bg-danger text-white px-3 py-1.5 rounded-pill mb-3';
            statusBadge.innerText = 'កំហុស Model (សូម Refresh)';
            return;
        }
    }

    // 2. Try WebRTC camera stream (works on localhost & HTTPS)
    const hasMedia = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    if (!hasMedia) {
        statusBadge.className = 'badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill mb-3';
        statusBadge.innerText = 'សូមចុចប៊ូតុង «ថតមុខ» ខាងក្រោម';
        return;
    }

    try {
        if (pubFaceStream) {
            pubFaceStream.getTracks().forEach(t => t.stop());
            pubFaceStream = null;
        }
        pubFaceStream = await navigator.mediaDevices.getUserMedia({
            video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' },
            audio: false
        });
        video.srcObject = pubFaceStream;
        await video.play();

        const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
        faceapi.matchDimensions(canvas, displaySize);

        if (pubFaceCheckInterval) clearInterval(pubFaceCheckInterval);
        pubFaceCheckInterval = setInterval(async () => {
            if (video.paused || video.ended || !pubFaceStream) {
                clearInterval(pubFaceCheckInterval);
                return;
            }

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
                    oval.style.borderColor = '#10b981';
                    oval.style.borderStyle = 'solid';
                    oval.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.5)';
                    statusBadge.className = 'badge bg-success px-3 py-1.5 rounded-pill mb-3';
                    statusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i> ទីតាំងល្អ! អាចថតបាន';

                    pubFaceDescriptor = Array.from(detection.descriptor);

                    const tempC = document.createElement('canvas');
                    tempC.width = 160;
                    tempC.height = 160;
                    const tctx = tempC.getContext('2d');
                    tctx.drawImage(video, 0, 0, 160, 160);
                    pubFacePhoto = tempC.toDataURL('image/jpeg', 0.85);
                } else {
                    oval.style.borderColor = 'rgba(255,255,255,0.6)';
                    oval.style.borderStyle = 'dashed';
                    oval.style.boxShadow = 'none';
                    statusBadge.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill mb-3';
                    statusBadge.innerText = 'សូមរំកិលមុខមកកណ្តាលរង្វង់';
                }
            } else {
                oval.style.borderColor = 'rgba(255,255,255,0.6)';
                oval.style.borderStyle = 'dashed';
                oval.style.boxShadow = 'none';
                statusBadge.className = 'badge bg-secondary-subtle text-secondary px-3 py-1.5 rounded-pill mb-3';
                statusBadge.innerText = 'សូមបែរមុខមករកកាមេរ៉ា...';
            }
        }, 160);
    } catch (err) {
        console.warn('Live stream camera not available or blocked:', err);
        statusBadge.className = 'badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill mb-3';
        statusBadge.innerText = 'សូមចុចប៊ូតុង «ថតមុខ» ខាងក្រោម';
    }
}

// Single Action Button handler
async function handlePubFaceAction() {
    const btn = document.getElementById('btnPubCaptureFace');

    // Case 1: Live stream active and face detected in circle
    if (pubFaceStream && pubFaceDescriptor) {
        await submitPublicFaceEnroll();
        return;
    }

    // Case 2: Live stream active but face not yet centered
    if (pubFaceStream && !pubFaceDescriptor) {
        const statusBadge = document.getElementById('pubFaceStatusBadge');
        if (statusBadge) {
            statusBadge.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill mb-3';
            statusBadge.innerText = 'សូមដាក់ផ្ទៃមុខឱ្យចំកណ្តាលរង្វង់';
        }
        return;
    }

    // Case 3: Live stream was not available (HTTP or blocked) -> open direct device camera immediately
    const cameraInput = document.getElementById('pubFaceDirectCamera');
    if (cameraInput) {
        cameraInput.click();
    }
}

// Direct native camera photo handler
async function handlePubFaceDirectPhoto(e) {
    const file = e.target.files && e.target.files[0];
    if (!file) return;

    const video = document.getElementById('pubFaceVideo');
    const canvas = document.getElementById('pubFaceCanvas');
    const previewImg = document.getElementById('pubFacePreviewImg');
    const oval = document.getElementById('pubFaceOval');
    const statusBadge = document.getElementById('pubFaceStatusBadge');
    const btn = document.getElementById('btnPubCaptureFace');

    stopPubFaceCamera();

    statusBadge.className = 'badge bg-primary px-3 py-1.5 rounded-pill mb-3';
    statusBadge.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងពិនិត្យផ្ទៃមុខ...';

    if (!pubFaceModelLoaded) {
        await loadPubFaceModels();
    }

    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = async function() {
            if (video) video.style.display = 'none';
            if (canvas) canvas.style.display = 'none';
            if (previewImg) {
                previewImg.src = img.src;
                previewImg.style.display = 'block';
            }

            const detection = await faceapi.detectSingleFace(
                img,
                new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.4 })
            ).withFaceLandmarks().withFaceDescriptor();

            if (detection) {
                pubFaceDescriptor = Array.from(detection.descriptor);

                const tempC = document.createElement('canvas');
                tempC.width = 160;
                tempC.height = 160;
                const tctx = tempC.getContext('2d');
                tctx.drawImage(img, 0, 0, 160, 160);
                pubFacePhoto = tempC.toDataURL('image/jpeg', 0.85);

                oval.style.borderColor = '#10b981';
                oval.style.borderStyle = 'solid';
                oval.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.5)';
                statusBadge.className = 'badge bg-success px-3 py-1.5 rounded-pill mb-3';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ផ្ទៃមុខត្រឹមត្រូវ! កំពុងចុះឈ្មោះ...';

                // Automatically submit enrollment right away
                await submitPublicFaceEnroll();
            } else {
                oval.style.borderColor = '#ef4444';
                oval.style.boxShadow = 'none';
                statusBadge.className = 'badge bg-danger text-white px-3 py-1.5 rounded-pill mb-3';
                statusBadge.innerText = 'រកមិនឃើញមុខទេ សូមចុច «ថតមុខ» ម្ដងទៀត';
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> ថតមុខ';
            }
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function stopPubFaceCamera() {
    if (pubFaceCheckInterval) {
        clearInterval(pubFaceCheckInterval);
        pubFaceCheckInterval = null;
    }
    if (pubFaceStream) {
        pubFaceStream.getTracks().forEach(t => t.stop());
        pubFaceStream = null;
    }
}

async function submitPublicFaceEnroll() {
    const btn = document.getElementById('btnPubCaptureFace');
    if (!pubFaceDescriptor) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងចុះឈ្មោះ...';

    try {
        const res = await fetch('<?= APP_URL ?>/api/face/enroll', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                registration_code: PUB_FACE_REG_CODE,
                descriptor: pubFaceDescriptor,
                photo: pubFacePhoto
            })
        });

        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('publicFaceEnrollModal')).hide();
            showToast('ចុះឈ្មោះផ្ទៃមុខជោគជ័យ!');
            
            const badge = document.getElementById('ticketFaceStatusBadge');
            if (badge) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill';
                badge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> បានចុះឈ្មោះរួច';
            }

            const fab = document.getElementById('floatingFaceScanBtn');
            if (fab) {
                const fbtn = fab.querySelector('.ticket-corner-face-btn, .floating-face-btn') || fab;
                if (fbtn) fbtn.classList.add('face-enrolled');
                const flabel = fab.querySelector('.floating-face-label');
                if (flabel) flabel.innerText = 'បានស្កេនមុខ';
                const pulse = fab.querySelector('.floating-pulse-ring');
                if (pulse) pulse.remove();
            }
        } else {
            alert(data.message || 'បរាជ័យក្នុងការចុះឈ្មោះ');
        }
    } catch (err) {
        alert('កំហុសភ្ជាប់ប្រព័ន្ធ សូមព្យាយាមម្តងទៀត');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-camera-fill me-1"></i> ថតមុខ';
    }
}

// Success Animation Popup Logic
function closeSuccessPopup() {
    const overlay = document.getElementById('successAnimationOverlay');
    if (overlay && !overlay.classList.contains('fade-out')) {
        overlay.classList.add('fade-out');
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 360);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('successAnimationOverlay');
    if (!overlay) return;

    // Auto-close smoothly after 2.2 seconds
    setTimeout(function() {
        closeSuccessPopup();
    }, 2200);

    // Celebratory Confetti Cannon
    try {
        const canvas = document.getElementById('confettiCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        window.addEventListener('resize', () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        });

        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#e11d48'];
        const particles = [];
        const count = Math.min(75, Math.floor(window.innerWidth / 12));

        for (let i = 0; i < count; i++) {
            particles.push({
                x: canvas.width / 2 + (Math.random() - 0.5) * 80,
                y: canvas.height / 2 - 30,
                vx: (Math.random() - 0.5) * 14,
                vy: (Math.random() - 1.1) * 12 - 2,
                size: Math.random() * 8 + 4,
                color: colors[Math.floor(Math.random() * colors.length)],
                rotation: Math.random() * 360,
                rotSpeed: (Math.random() - 0.5) * 12,
                opacity: 1
            });
        }

        function renderConfetti() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let alive = false;
            particles.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.32; // gravity
                p.vx *= 0.99; // drag
                p.rotation += p.rotSpeed;
                p.opacity -= 0.009;
                if (p.opacity > 0) {
                    alive = true;
                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate((p.rotation * Math.PI) / 180);
                    ctx.fillStyle = p.color;
                    ctx.globalAlpha = Math.max(0, p.opacity);
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.65);
                    ctx.restore();
                }
            });
            if (alive) {
                requestAnimationFrame(renderConfetti);
            }
        }
        requestAnimationFrame(renderConfetti);
    } catch (e) {
        // Fallback gracefully
    }
});
</script>
