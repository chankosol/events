<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body, html {
            background-color: #f1f5f9;
        }
        body, html, input, button, select, textarea, .btn {
            font-family: 'Kantumruy Pro', system-ui, -apple-system, sans-serif !important;
        }
        .header-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: #fff;
            border-radius: 14px;
        }
        .btn-primary {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #ffffff !important;
        }
        .btn-primary:hover {
            background-color: #0b5ed7 !important;
            border-color: #0a58ca !important;
            color: #ffffff !important;
        }
        .member-row {
            transition: background-color 0.15s ease-in-out;
        }
        .member-row:active {
            background-color: #f8fafc;
        }
        .qr-action-btn {
            min-width: 78px;
            white-space: nowrap;
            border-radius: 8px;
            font-size: 0.8rem;
        }
        @media (max-width: 576px) {
            .form-control, .form-select {
                font-size: 16px; /* Prevents iOS auto zoom on input focus */
            }
        }
    </style>
</head>
<body class="py-2 py-sm-3">
<div class="container px-2 px-sm-3" style="max-width: 780px;">
    <!-- Brand / Header -->
    <div class="card header-card border-0 shadow-sm p-3 p-md-4 mb-3">
        <!-- Workshop Title: Full width to provide maximum space -->
        <h2 class="fw-bold mb-2 fs-4 fs-md-3 text-white text-wrap"><?= htmlspecialchars($delegation['workshop_name']) ?></h2>

        <div class="d-flex justify-content-between align-items-end gap-2">
            <!-- Left: Date/Venue/Map -->
            <div class="min-w-0 opacity-90 small">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <span><i class="bi bi-calendar3 me-1"></i><?= formatDate($delegation['start_date']) ?></span>
                    <?php if (!empty($delegation['venue'])): ?>
                        <span>&bull;</span>
                        <span><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($delegation['venue']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($delegation['venue'])): ?>
                    <div class="mt-1">
                        <a href="<?= !empty($delegation['google_maps_url']) ? htmlspecialchars($delegation['google_maps_url']) : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($delegation['venue']) ?>" target="_blank" class="btn btn-sm btn-light py-0 px-2 text-primary fw-bold text-decoration-none shadow-xs d-inline-flex align-items-center" style="font-size: 0.75rem; border-radius: 6px; height: 24px;">
                            <i class="bi bi-map-fill me-1 text-primary"></i>ផែនទី
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Badges in one column, aligned bottom -->
            <div class="text-end d-flex flex-column align-items-end gap-1 flex-shrink-0 ms-2">
                <span class="badge bg-white text-primary px-3 py-1 rounded-pill shadow-sm fw-bold" style="font-size: 0.95rem;">
                    <i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($delegation['province']) ?>
                </span>
                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-2 py-1 rounded-pill" style="font-size: 0.78rem;">
                    កូតា៖ <strong><?= count($members) ?> / <?= $delegation['quota_seats'] ?></strong> នាក់
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success fade show shadow-sm d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 mb-3" role="alert">
            <div class="d-flex align-items-center me-2 flex-grow-1">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success flex-shrink-0"></i>
                <span class="fw-medium small"><?= htmlspecialchars($successMsg) ?></span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                <?php if (!empty($newlyRegistered)): ?>
                    <button type="button" class="btn btn-sm btn-success text-white fw-bold d-inline-flex align-items-center shadow-sm" data-bs-toggle="modal" data-bs-target="#qrModal_<?= $newlyRegistered['id'] ?>">
                        <i class="bi bi-download me-1"></i> រក្សាទុកកាត
                    </button>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="position: static; padding: 0.4rem;"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger fade show shadow-sm d-flex align-items-center justify-content-between gap-2 p-3 mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger flex-shrink-0"></i>
                <span class="small fw-medium"><?= htmlspecialchars($errorMsg) ?></span>
            </div>
            <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="alert" aria-label="Close" style="position: static; padding: 0.4rem;"></button>
        </div>
    <?php endif; ?>

    <!-- Delegation Members List -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <h6 class="fw-bold text-primary mb-0 d-flex align-items-center" style="font-size: 0.92rem;">
                <i class="bi bi-people-fill me-2 fs-6"></i>បញ្ជីសមាជិកប្រតិភូ (<?= count($members) ?> / <?= $delegation['quota_seats'] ?>)
            </h6>
            <?php if (count($members) < $delegation['quota_seats']): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">នៅសល់ <?= $delegation['quota_seats'] - count($members) ?> កៅអី</span>
            <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1" style="font-size: 0.72rem;">ពេញកូតា</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($members)): ?>
                <div class="text-center py-4 px-3 text-muted small">
                    <i class="bi bi-inbox fs-3 text-secondary d-block mb-1"></i> មិនទាន់មានសមាជិកនៅឡើយទេ
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php $no = 1; foreach ($members as $m): ?>
                        <div class="list-group-item p-3 member-row">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <!-- Member Details Column (Left) -->
                                <div class="flex-grow-1 min-w-0" style="min-width: 0;">
                                    <!-- Row 1: Number, Name, Gender, Position/Role -->
                                    <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                        <span class="badge bg-light text-primary border fw-bold" style="font-size: 0.72rem; padding: 0.2rem 0.4rem;">#<?= $no++ ?></span>
                                        <span class="fw-bold text-dark text-truncate me-1" style="font-size: 0.95rem; line-height: 1.2;"><?= htmlspecialchars($m['name']) ?></span>
                                        <span class="badge <?= $m['gender'] === 'female' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?>" style="font-size: 0.68rem; padding: 0.15rem 0.35rem;">
                                            <?= $m['gender'] === 'female' ? 'ស្រី' : 'ប្រុស' ?>
                                        </span>
                                        <?php 
                                            $orgInfo = array_filter([$m['position'] ?? '', $m['company'] ?? '']);
                                            if (!empty($orgInfo)): 
                                        ?>
                                            <span class="text-muted small">&bull;</span>
                                            <span class="text-secondary small fw-medium" style="font-size: 0.8rem;">
                                                <?= htmlspecialchars(implode(' • ', $orgInfo)) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Row 2: Admin only info (Phone, ID card) -->
                                    <?php if (isLoggedIn() && (!empty($m['phone']) || !empty($m['id_card_number']))): ?>
                                        <div class="text-muted small text-truncate mb-1" style="font-size: 0.75rem;">
                                            <?php if (!empty($m['phone'])): ?>
                                                <span><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($m['phone']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($m['phone']) && !empty($m['id_card_number'])): ?> &bull; <?php endif; ?>
                                            <?php if (!empty($m['id_card_number'])): ?>
                                                <span>អត្តសញ្ញាណប័ណ្ណ៖ <?= htmlspecialchars($m['id_card_number']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Row 4: Code & Check-in Badge -->
                                    <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.76rem;">
                                        <span class="text-muted">កូដ៖ <strong class="font-monospace text-dark"><?= $m['registration_code'] ?></strong></span>
                                        <?php if (!empty($m['checked_in_at'])): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.68rem; padding: 0.18rem 0.38rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>បាន Check-in
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border" style="font-size: 0.68rem; padding: 0.18rem 0.38rem;">
                                                <i class="bi bi-hourglass me-1"></i>មិនទាន់ Check-in
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Column (Right - Vertically Centered & Never Wrapped) -->
                                <?php if (!empty($m['qr_token'])): ?>
                                    <div class="flex-shrink-0 ms-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary fw-medium px-2 py-1 qr-action-btn d-inline-flex align-items-center justify-content-center shadow-xs" data-bs-toggle="modal" data-bs-target="#qrModal_<?= $m['id'] ?>">
                                            <i class="bi bi-qr-code me-1"></i>កាត QR
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($m['qr_token'])): ?>
                                <!-- QR / Ticket Modal -->
                                        <div class="modal fade" id="qrModal_<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" style="max-width: 360px;">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                                                    <div class="modal-header bg-light py-2 px-3 border-0">
                                                        <span class="modal-title small fw-bold text-dark">
                                                            <i class="bi bi-ticket-perforated me-1 text-primary"></i> កាតសមាជិកប្រតិភូ
                                                        </span>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <!-- CAPTURE AREA FOR SAVING TO GALLERY -->
                                                        <div id="ticketCaptureArea_<?= $m['id'] ?>" class="bg-white p-3 border rounded-3 text-center shadow-sm" style="background: #ffffff;" data-code="<?= $m['registration_code'] ?>">
                                                            
                                                            <!-- Header of Ticket -->
                                                            <div class="p-2 mb-2 rounded-2 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                                                                <div class="text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px; opacity: 0.9;">
                                                                    <?= htmlspecialchars($delegation['business_name'] ?? 'Workshop OS') ?>
                                                                </div>
                                                                <h6 class="fw-bold mb-0 text-truncate px-1" style="font-size: 0.92rem;">
                                                                    <?= htmlspecialchars($delegation['workshop_name']) ?>
                                                                </h6>
                                                            </div>

                                                            <!-- Member Info -->
                                                            <div class="d-flex justify-content-between align-items-center px-1 mb-2 pb-2 border-bottom">
                                                                <div class="text-start">
                                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">ឈ្មោះសមាជិក</small>
                                                                    <strong class="text-dark" style="font-size: 0.98rem;"><?= htmlspecialchars($m['name']) ?></strong>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-primary text-white" style="font-size: 0.75rem;">
                                                                        <?= htmlspecialchars($delegation['province']) ?>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Crisp QR Code (Local QRCode / fallback img) -->
                                                            <div class="p-2 bg-light rounded-3 d-inline-block border my-1">
                                                                <div id="qrcode_<?= $m['id'] ?>" class="d-flex justify-content-center" data-token="<?= htmlspecialchars(shareableUrl('/checkin/scan?token=' . $m['qr_token'])) ?>"></div>
                                                                <noscript>
                                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode(shareableUrl('/checkin/scan?token=' . $m['qr_token'])) ?>" alt="QR Code" width="160" height="160">
                                                                </noscript>
                                                            </div>

                                                            <!-- Registration Code -->
                                                            <div class="mt-2">
                                                                <span class="font-monospace fw-bold text-dark px-2 py-1 bg-light rounded border" style="font-size: 0.88rem; letter-spacing: 1px;">
                                                                    <?= $m['registration_code'] ?>
                                                                </span>
                                                            </div>

                                                            <div class="mt-2 text-muted" style="font-size: 0.68rem;">
                                                                <i class="bi bi-qr-code-scan text-primary me-1"></i> បង្ហាញកូដពេលចូលរួម
                                                            </div>
                                                        </div>

                                                        <!-- RENDERED IMAGE: Allows native iOS/Android tap & hold save directly into Photo Gallery -->
                                                        <div id="ticketImageWrap_<?= $m['id'] ?>" class="d-none text-center">
                                                            <img id="ticketImg_<?= $m['id'] ?>" class="img-fluid rounded-3 border shadow-sm" alt="Ticket" style="max-width: 100%; cursor: pointer;">
                                                        </div>

                                                        <div class="mt-2 text-center text-muted" style="font-size: 0.75rem;">
                                                            <i class="bi bi-info-circle text-primary me-1"></i> ចុចសង្កត់លើរូបភាព ដើម្បីរក្សាទុកក្នុងរូបថត
                                                        </div>

                                                        <div id="saveFeedback_<?= $m['id'] ?>" class="d-none mt-2 alert alert-success py-1 px-2 text-center mb-0" style="font-size: 0.8rem;">
                                                            <i class="bi bi-check2-circle me-1"></i> បានរក្សាទុកក្នុងទូរស័ព្ទ!
                                                        </div>

                                                        <!-- ACTION BUTTON: SAVE TO GALLERY -->
                                                        <button id="saveBtn_<?= $m['id'] ?>" type="button" class="btn btn-success fw-bold w-100 py-2 mt-2 d-flex align-items-center justify-content-center shadow-sm" onclick="saveDelegationTicket('<?= $m['id'] ?>', '<?= $m['registration_code'] ?>', this)">
                                                            <i class="bi bi-download me-2"></i> រក្សាទុកកាត
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Registration Form (Only if quota not full) -->
    <?php if (count($members) < $delegation['quota_seats']): ?>
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; overflow: hidden;">
            <div class="card-header bg-white py-2 px-3 border-bottom">
                <h6 class="fw-bold text-success mb-0 d-flex align-items-center" style="font-size: 0.92rem;">
                    <i class="bi bi-person-plus-fill me-2 fs-6"></i>ចាត់តាំងសមាជិកថ្មីចូលប្រតិភូ (បន្ថែមសមាជិក)
                </h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <form method="POST" onsubmit="return handleDelegationSubmit(this);">
                    <div class="row g-2 g-md-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">ឈ្មោះសមាជិក <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control py-2" placeholder="ឧ. លោក សុខ វិសាល" required>
                        </div>
                        <?php if (!empty($delegationFormFields['show_phone'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">លេខទូរស័ព្ទ <?= !empty($delegationFormFields['require_phone']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="phone" class="form-control py-2" placeholder="012 xxx xxx" <?= !empty($delegationFormFields['require_phone']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_email'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">អ៊ីមែល <?= !empty($delegationFormFields['require_email']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="email" name="email" class="form-control py-2" placeholder="name@example.com" <?= !empty($delegationFormFields['require_email']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_gender'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">ភេទ <?= !empty($delegationFormFields['require_gender']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <select name="gender" class="form-select py-2" <?= !empty($delegationFormFields['require_gender']) ? 'required' : '' ?>>
                                    <option value="male">ប្រុស</option>
                                    <option value="female">ស្រី</option>
                                </select>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_position'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">តួនាទី / មុខតំណែង <?= !empty($delegationFormFields['require_position']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="position" class="form-control py-2" placeholder="ឧ. ប្រធានការិយាល័យ" <?= !empty($delegationFormFields['require_position']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_organization'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">អង្គភាព / ស្ថាប័ន <?= !empty($delegationFormFields['require_organization']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="organization" class="form-control py-2" placeholder="ឧ. មន្ទីរអប់រំ យុវជន និងកីឡា" <?= !empty($delegationFormFields['require_organization']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_id_card'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">លេខអត្តសញ្ញាណប័ណ្ណ <?= !empty($delegationFormFields['require_id_card']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="id_card_number" class="form-control py-2" placeholder="ឧ. 010203040" <?= !empty($delegationFormFields['require_id_card']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_bank_name'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">ឈ្មោះធនាគារ <?= !empty($delegationFormFields['require_bank_name']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="bank_name" class="form-control py-2" placeholder="Bakong / ABA / ACLEDA" <?= !empty($delegationFormFields['require_bank_name']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_bank_account'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">លេខគណនីធនាគារ <?= !empty($delegationFormFields['require_bank_account']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="bank_account_number" class="form-control py-2" placeholder="000 123 456" <?= !empty($delegationFormFields['require_bank_account']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <div class="col-12 mt-3 text-center text-md-end">
                            <button type="submit" class="btn btn-primary w-100 w-md-auto px-4 py-2 fw-bold shadow-sm">
                                <i class="bi bi-person-plus me-1"></i> ចុះឈ្មោះសមាជិក
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info shadow-sm text-center py-3 mb-3" style="border-radius: 14px;">
            <i class="bi bi-info-circle-fill me-2"></i> ប្រតិភូរបស់លោកអ្នកបានចាត់តាំងសមាជិកគ្រប់ចំនួនកូតា (<?= $delegation['quota_seats'] ?> នាក់) រួចរាល់ហើយ។
        </div>
    <?php endif; ?>

    <footer class="text-center text-muted small mt-4">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($delegation['business_name']) ?> &bull; ដំណើរការដោយ Workshop OS
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/qrcode.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/html2canvas.min.js"></script>
<script>
if (typeof QRCode === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
}
if (typeof html2canvas === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"><\/script>');
}

window.ticketStore = window.ticketStore || {};

function initQrCode(memberId) {
    const container = document.getElementById('qrcode_' + memberId);
    if (!container || container.dataset.rendered === '1') return;
    const tokenUrl = container.dataset.token;
    if (!tokenUrl) return;

    try {
        new QRCode(container, {
            text: tokenUrl,
            width: 160,
            height: 160,
            colorDark: "#0f172a",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });
        container.dataset.rendered = '1';
    } catch (e) {
        console.error('QR code error:', e);
        container.innerHTML = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(tokenUrl) + '" width="160" height="160" alt="QR">';
        container.dataset.rendered = '1';
    }
}

async function prepareTicketImage(memberId, regCode) {
    if (window.ticketStore[memberId]) {
        return window.ticketStore[memberId];
    }

    initQrCode(memberId);
    const ticketEl = document.getElementById('ticketCaptureArea_' + memberId);
    if (!ticketEl) return null;

    // Small delay for QR render
    await new Promise(r => setTimeout(r, 120));

    try {
        const canvas = await html2canvas(ticketEl, {
            scale: 2.5,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });

        const fileName = 'Ticket-' + (regCode || 'REG') + '.png';
        const dataUrl = canvas.toDataURL('image/png');
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        let file = null;
        if (blob && typeof File !== 'undefined') {
            try {
                file = new File([blob], fileName, { type: 'image/png' });
            } catch (e) {}
        }

        const data = { canvas, dataUrl, blob, file, fileName };
        window.ticketStore[memberId] = data;

        // Display the actual <img> tag in modal so mobile users can long-press and save directly to Photo Gallery
        const imgEl = document.getElementById('ticketImg_' + memberId);
        const wrapEl = document.getElementById('ticketImageWrap_' + memberId);
        if (imgEl && wrapEl) {
            imgEl.src = dataUrl;
            ticketEl.classList.add('d-none');
            wrapEl.classList.remove('d-none');
        }

        return data;
    } catch (err) {
        console.error('Failed to pre-render ticket:', err);
        return null;
    }
}

async function saveDelegationTicket(memberId, regCode, btn) {
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> កំពុងរក្សាទុក...';
    }

    try {
        let ticketData = window.ticketStore[memberId];
        if (!ticketData) {
            ticketData = await prepareTicketImage(memberId, regCode);
        }

        if (!ticketData) {
            throw new Error('Could not generate ticket');
        }

        const fileName = ticketData.fileName;

        // 1. Try Web Share API (native iOS / Android save directly to Photos / Gallery)
        if (ticketData.file && navigator.canShare && navigator.canShare({ files: [ticketData.file] })) {
            try {
                await navigator.share({
                    files: [ticketData.file],
                    title: 'កាតសមាជិកប្រតិភូ',
                    text: 'កាត QR សម្គាល់ខ្លួនចូលរួម'
                });
            } catch (shareErr) {
                console.log('Share dismissed or cancelled:', shareErr);
            }
        }

        // 2. Direct Blob file download (triggers native file download on Android/Desktop to Photo Gallery/Downloads)
        if (ticketData.blob) {
            const blobUrl = URL.createObjectURL(ticketData.blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);
        }

        // 3. Show feedback & highlight the image
        const feedbackEl = document.getElementById('saveFeedback_' + memberId);
        if (feedbackEl) {
            feedbackEl.classList.remove('d-none');
        }

        if (btn) {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-primary');
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> បានរក្សាទុក!';

            setTimeout(() => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                btn.innerHTML = originalHtml;
            }, 2500);
        }

    } catch (err) {
        console.error('Error saving ticket:', err);
        alert('មានបញ្ហាក្នុងការរក្សាទុក។ សូមចុចសង្កត់លើរូបកាត ឬថតអេក្រង់ (Screenshot) ទុកជំនួសវិញ។');
        if (btn) btn.innerHTML = originalHtml;
    } finally {
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // When modal opens, prepare QR and crisp ticket image for photo gallery
    document.querySelectorAll('[id^="qrModal_"]').forEach(function(modalEl) {
        modalEl.addEventListener('shown.bs.modal', function() {
            const id = modalEl.id.replace('qrModal_', '');
            initQrCode(id);
            const regCode = modalEl.querySelector('[data-code]')?.dataset.code || '';
            prepareTicketImage(id, regCode);
        });
    });

    <?php if (!empty($newlyRegistered)): ?>
        // Automatically pop up the QR ticket modal for newly registered member!
        const newModalEl = document.getElementById('qrModal_<?= $newlyRegistered['id'] ?>');
        if (newModalEl && typeof bootstrap !== 'undefined') {
            const m = bootstrap.Modal.getOrCreateInstance(newModalEl);
            m.show();
        }
    <?php endif; ?>
});

let isDelegationSubmitting = false;
function handleDelegationSubmit(form) {
    if (isDelegationSubmitting) return false;
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> កំពុងចុះឈ្មោះ...';
    }
    isDelegationSubmitting = true;
    return true;
}
</script>
</body>
</html>
