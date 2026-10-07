<?php 
$reg = $registration ?? $lastReg ?? Session::get('last_registration') ?? []; 
if (!empty($reg['id'])) {
    $db = Database::getInstance();
    $currentReg = $db->queryOne(
        "SELECT r.*, pq.token as qr_token, 
                p.name as participant_name, p.phone as participant_phone, p.email as participant_email,
                w.name as workshop_name, w.start_date, w.end_date, w.start_time, w.end_time, w.venue, w.address,
                t.name as ticket_name, t.price as ticket_price, t.currency as ticket_currency
         FROM registrations r 
         LEFT JOIN participant_qr pq ON pq.registration_id = r.id 
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
$status = $reg['status'] ?? 'confirmed';
$qrToken = $reg['qr_token'] ?? $reg['token'] ?? '';
$regCode = $reg['registration_code'] ?? $reg['code'] ?? 'REG-000';
?>

<style>
.ticket-wrapper {
    max-width: 410px;
    margin: 0 auto;
}
.ticket-card {
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    position: relative;
}
.ticket-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: #ffffff;
    padding: 16px 18px 14px;
}
.ticket-stub-divider {
    position: relative;
    height: 20px;
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ticket-stub-divider .notch {
    width: 22px;
    height: 22px;
    background-color: #f8fafc;
    border-radius: 50%;
    position: absolute;
    top: -1px;
}
.ticket-stub-divider .notch-left {
    left: -12px;
    box-shadow: inset -2px 0 3px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}
.ticket-stub-divider .notch-right {
    right: -12px;
    box-shadow: inset 2px 0 3px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}
.ticket-stub-divider .dashed-line {
    width: calc(100% - 36px);
    border-bottom: 2px dashed #cbd5e1;
}
.ticket-body {
    padding: 12px 18px 16px;
    background: #ffffff;
}
.qr-frame {
    background: #ffffff;
    padding: 8px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    display: inline-block;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.btn-save-gallery {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    color: #ffffff;
    font-weight: 700;
    padding: 12px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.28);
    transition: all 0.2s ease;
}
.btn-save-gallery:hover {
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.38);
}
.btn-save-gallery:active {
    transform: translateY(0);
}
</style>

<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <?php if ($status === 'confirmed'): ?>
                <!-- Short Form E-Ticket Pass (Fits on mobile screen) -->
                <div class="ticket-wrapper">
                    
                    <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> ចុះឈ្មោះជោគជ័យ
                        </span>
                        <small class="text-muted">
                            <?= date('d M Y, H:i') ?>
                        </small>
                    </div>

                    <!-- CAPTURE AREA FOR SAVING TO GALLERY -->
                    <div id="ticketCaptureArea" class="ticket-card mb-3" data-code="<?= htmlspecialchars($regCode) ?>">
                        <!-- Top Header -->
                        <div class="ticket-header">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-white text-primary fw-bold px-2 py-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    សំបុត្រចូលរួម
                                </span>
                                <span class="badge bg-success text-white" style="font-size: 0.7rem;">
                                    <i class="bi bi-patch-check-fill me-1"></i> រួចរាល់
                                </span>
                            </div>
                            <h5 class="fw-bold mb-1 text-white text-truncate" title="<?= htmlspecialchars($reg['workshop_name'] ?? 'សិក្ខាសាលា') ?>">
                                <?= htmlspecialchars($reg['workshop_name'] ?? 'សិក្ខាសាលា') ?>
                            </h5>
                            <div class="small text-white-50 d-flex flex-wrap gap-2 mt-1" style="font-size: 0.78rem;">
                                <span><i class="bi bi-calendar-event me-1"></i> <?= formatDate($reg['start_date'] ?? date('Y-m-d')) ?></span>
                                <?php if (!empty($reg['start_time'])): ?>
                                    <span><i class="bi bi-clock me-1"></i> <?= substr($reg['start_time'], 0, 5) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($reg['venue'])): ?>
                                    <span><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($reg['venue']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Ticket Perforated Divider -->
                        <div class="ticket-stub-divider">
                            <div class="notch notch-left"></div>
                            <div class="dashed-line"></div>
                            <div class="notch notch-right"></div>
                        </div>

                        <!-- Ticket Body & QR Code -->
                        <div class="ticket-body text-center">
                            <!-- Info Grid -->
                            <div class="row g-2 text-start mb-2 pb-2 border-bottom">
                                <div class="col-7">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">ឈ្មោះ</span>
                                    <strong class="text-dark d-block text-truncate" style="font-size: 0.95rem;">
                                        <?= htmlspecialchars($reg['participant_name'] ?? 'សិក្ខាកាម') ?>
                                    </strong>
                                </div>
                                <div class="col-5 text-end">
                                    <span class="text-muted d-block text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">កូដ</span>
                                    <span class="font-monospace fw-bold text-primary" style="font-size: 0.88rem;">
                                        <?= htmlspecialchars($regCode) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Crisp QR Code -->
                            <div class="qr-frame my-1">
                                <div id="qrcode" class="d-flex justify-content-center"></div>
                                <noscript>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($qrToken) ?>" alt="QR Code" width="150" height="150">
                                </noscript>
                            </div>

                            <div class="mt-2">
                                <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-qr-code-scan text-primary me-1"></i> ស្កេនពេលចូលរួម
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS: One Tap Save to Gallery -->
                    <button type="button" id="btnSaveGallery" class="btn btn-save-gallery btn-lg w-100 d-flex align-items-center justify-content-center mb-2">
                        <i class="bi bi-download me-2 fs-5"></i> រក្សាទុកក្នុងទូរស័ព្ទ
                    </button>

                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/participant/login" class="btn btn-outline-primary w-50 py-2 small fw-bold">
                            <i class="bi bi-person-circle me-1"></i> គណនី
                        </a>
                        <a href="<?= APP_URL ?>" class="btn btn-outline-secondary w-50 py-2 small">
                            <i class="bi bi-house me-1"></i> ទំព័រដើម
                        </a>
                    </div>

                </div>

            <?php elseif ($status === 'pending_verification' || ($reg['payment_status'] ?? '') === 'pending'): ?>
                <!-- Pending Payment Verification -->
                <div class="card shadow-sm border-0 p-4 text-center">
                    <div class="bg-warning text-white rounded-circle d-inline-flex p-3 mb-3 mx-auto">
                        <i class="bi bi-clock-history fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-warning mb-2">រង់ចាំការផ្ទៀងផ្ទាត់ការបង់ប្រាក់</h3>
                    <p class="text-muted mb-3">ការចុះឈ្មោះសម្រាប់ <strong><?= htmlspecialchars($reg['workshop_name'] ?? 'សិក្ខាសាលា') ?></strong> កំពុងរង់ចាំការត្រួតពិនិត្យបង្កាន់ដៃផ្ទេរប្រាក់។</p>
                    <div class="alert alert-info py-2 px-3 d-inline-block small mb-3">
                        <strong>ទឹកប្រាក់ត្រូវបង់:</strong> <?= htmlspecialchars($reg['currency'] ?? 'USD') ?> <?= number_format($reg['final_amount'] ?? 0, 2) ?>
                    </div>
                    <div>
                        <a href="<?= APP_URL ?>/participant/login" class="btn btn-primary fw-bold px-4 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i> ចូលគណនីដើម្បីបញ្ចូលបង្កាន់ដៃ
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
        document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
    }
    if (typeof html2canvas === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"><\/script>');
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Generate QR Code on local Canvas/Img (prevents tainted canvas in html2canvas)
    const qrContainer = document.getElementById('qrcode');
    const qrToken = <?= json_encode($qrToken ?: $regCode) ?>;
    
    if (qrContainer && qrToken) {
        try {
            new QRCode(qrContainer, {
                text: qrToken,
                width: 150,
                height: 150,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (e) {
            console.error('QR code generation error:', e);
            qrContainer.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(qrToken) + '" width="150" height="150" alt="QR">';
        }
    }

    // 2. Save to Gallery Handler
    const saveBtn = document.getElementById('btnSaveGallery');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> កំពុងទាញយករូបភាព...';

            try {
                const ticketEl = document.getElementById('ticketCaptureArea');
                if (!ticketEl) return;

                // Render ticket card to high-res canvas (scale 2.5)
                const canvas = await html2canvas(ticketEl, {
                    scale: 2.5,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false
                });

                const regCodeStr = ticketEl.dataset.code || 'REG';
                const fileName = 'Ticket-' + regCodeStr + '.png';
                const dataUrl = canvas.toDataURL('image/png');

                // Try Web Share API (native iOS / Android save to photos / gallery)
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
                        // User cancelled share or not supported; proceed to fallback
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

                // Show modal preview if not shared natively
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
                alert('មានបញ្ហាក្នុងការទាញយក។ សូមថតអេក្រង់សំបុត្រនេះទុកជំនួសវិញ។');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }
});
</script>
