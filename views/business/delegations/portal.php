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
        .face-scan-box {
            position: relative;
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            background: #0b1120;
            aspect-ratio: 4 / 3;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .face-scan-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .face-scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            transform: scaleX(-1);
        }
        .face-reticle-oval {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 190px;
            height: 250px;
            border-radius: 50%;
            border: 3px dashed rgba(255, 255, 255, 0.45);
            box-shadow: 0 0 0 9999px rgba(11, 17, 32, 0.45);
            pointer-events: none;
            transition: all 0.25s ease;
        }
        .face-reticle-oval.good {
            border: 3.5px solid #10b981;
            box-shadow: 0 0 0 9999px rgba(11, 17, 32, 0.35), 0 0 25px rgba(16, 185, 129, 0.7);
        }
        .face-reticle-oval.warning {
            border-color: #f59e0b;
        }
        .face-capture-flash {
            position: absolute;
            inset: 0;
            background: #ffffff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s ease-out;
            z-index: 10;
        }
        .face-capture-flash.active {
            opacity: 0.95;
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
                                                                    <small class="text-muted d-block fw-semibold" style="font-size: 0.7rem; text-transform: uppercase;">ឈ្មោះពេញ / Full Name</small>
                                                                    <strong class="text-dark" style="font-size: 1.05rem; line-height: 1.3;"><?= htmlspecialchars($m['name']) ?></strong>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-primary text-white" style="font-size: 0.75rem;">
                                                                        <?= htmlspecialchars($delegation['province']) ?>
                                                                    </span>
                                                                </div>
                                                            </div>

                                                            <!-- Crisp QR Code (Direct instant render with local fallback) -->
                                                            <div class="p-2 bg-white rounded-3 d-inline-block border my-1 shadow-2xs text-center" style="min-width: 172px; min-height: 172px;">
                                                                <img id="qrImg_<?= $m['id'] ?>" 
                                                                     src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=<?= urlencode($m['qr_token'] ?: $m['registration_code']) ?>" 
                                                                     alt="QR Code" 
                                                                     width="160" 
                                                                     height="160" 
                                                                     class="img-fluid d-block mx-auto" 
                                                                     crossorigin="anonymous" 
                                                                     loading="eager"
                                                                     onerror="renderLocalQr('<?= $m['id'] ?>', '<?= htmlspecialchars($m['qr_token'] ?: $m['registration_code']) ?>')">
                                                                <div id="qrcode_<?= $m['id'] ?>" class="d-none"></div>
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

                                                        <div id="saveFeedback_<?= $m['id'] ?>" class="d-none mt-2 alert alert-success py-1 px-2 text-center mb-0" style="font-size: 0.8rem;">
                                                            <i class="bi bi-check2-circle me-1"></i> បានរក្សាទុកក្នុងទូរស័ព្ទ!
                                                        </div>

                                                        <!-- Action Buttons -->
                                                        <div class="d-flex flex-column gap-2 mt-3">
                                                            <?php 
                                                                $isTgActive = function_exists('telegram_is_bot_active') && telegram_is_bot_active();
                                                                $tgLink = $isTgActive 
                                                                    ? telegram_get_ticket_bot_url($m['registration_code']) 
                                                                    : ('https://t.me/share/url?url=' . urlencode(APP_URL . '/ticket/' . $m['registration_code']) . '&text=' . urlencode('សំបុត្រ និង QR Code: ' . APP_URL . '/ticket/' . $m['registration_code']));
                                                            ?>
                                                            <a href="<?= $tgLink ?>" target="_blank" class="btn btn-primary fw-bold w-100 py-2 d-flex align-items-center justify-content-center shadow-xs">
                                                                <i class="bi bi-telegram me-2"></i> ផ្ញើទៅកាន់ Telegram
                                                            </a>

                                                            <button id="saveBtn_<?= $m['id'] ?>" type="button" class="btn btn-outline-success fw-bold w-100 py-2 d-flex align-items-center justify-content-center shadow-sm" onclick="saveDelegationTicket('<?= $m['id'] ?>', '<?= $m['registration_code'] ?>', this)">
                                                                <i class="bi bi-download me-2"></i> រក្សាទុកកាត (រូបភាព)
                                                            </button>
                                                        </div>
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
                <form method="POST" id="delegationRegisterForm" onsubmit="return handleDelegationSubmit(event, this);">
                    <div class="row g-2 g-md-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control py-2" placeholder="ឧ. ចាន់ កុសល ឬ Chan Kosol" required>
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
                                <label class="form-label fw-bold small text-dark mb-1">តួនាទី <?= !empty($delegationFormFields['require_position']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="position" class="form-control py-2" placeholder="ឧ. ប្រធានការិយាល័យ" <?= !empty($delegationFormFields['require_position']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_organization'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">អង្គភាព <?= !empty($delegationFormFields['require_organization']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="organization" class="form-control py-2" placeholder="ឧ. មន្ទីរអប់រំ យុវជន និងកីឡា" <?= !empty($delegationFormFields['require_organization']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_id_card'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">អត្តសញ្ញាណប័ណ្ណ <?= !empty($delegationFormFields['require_id_card']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="id_card_number" class="form-control py-2" placeholder="ឧ. 010203040" <?= !empty($delegationFormFields['require_id_card']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_bank_name'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">ធនាគារ <?= !empty($delegationFormFields['require_bank_name']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="bank_name" class="form-control py-2" placeholder="Bakong / ABA / ACLEDA" <?= !empty($delegationFormFields['require_bank_name']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($delegationFormFields['show_bank_account'])): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">លេខគណនី <?= !empty($delegationFormFields['require_bank_account']) ? '<span class="text-danger">*</span>' : '<span class="text-muted small fw-normal">(ជម្រើស)</span>' ?></label>
                                <input type="text" name="bank_account_number" class="form-control py-2" placeholder="000 123 456" <?= !empty($delegationFormFields['require_bank_account']) ? 'required' : '' ?>>
                            </div>
                        <?php endif; ?>

                        <!-- Face Pre-registration Hidden & Status Fields -->
                        <input type="hidden" name="face_descriptor" id="faceDescriptorInput">
                        <input type="hidden" name="face_photo" id="facePhotoInput">
                        <?php if (!empty($delegationFormFields['require_face_enroll'])): ?>
                            <input type="hidden" id="faceEnrollRequired" value="1">
                        <?php endif; ?>

                        <!-- Success Alert when Face is Enrolled -->
                        <div id="faceCapturedAlert" class="col-12 mt-2 d-none">
                            <div class="alert alert-success py-2 px-3 small d-flex align-items-center justify-content-between mb-0 rounded-3 shadow-xs">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-person-check-fill fs-5 text-success"></i>
                                    <div>
                                        <div class="fw-bold text-success">ស្គេនមុខបានជោគជ័យ!</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">ទិន្នន័យផ្ទៃមុខត្រូវបានរក្សាទុកសម្រាប់ Check-in</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 fw-medium" onclick="openFaceScanModal()">
                                    <i class="bi bi-camera me-1"></i> ស្គេនឡើងវិញ
                                </button>
                            </div>
                        </div>

                        <div class="col-12 mt-3 text-center text-md-end">
                            <button type="button" class="btn btn-primary w-100 w-md-auto px-4 py-2 fw-bold shadow-sm" id="btnSubmitDelegation" onclick="triggerDelegationRegister()">
                                <i class="bi bi-person-check me-1"></i> ចុះឈ្មោះចូលរួម
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

    <?php if (!empty($delegationFormFields['show_face_enroll'])): ?>
    <!-- Modal: AI Face Scan -->
    <div class="modal fade" id="faceScanModal" tabindex="-1" aria-labelledby="faceScanModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header border-0 bg-light py-2 px-3">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center mb-0" id="faceScanModalLabel">
                        <i class="bi bi-person-bounding-box text-primary me-2 fs-5"></i> ស្គេនមុខ
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="onCloseFaceModal()"></button>
                </div>
                <div class="modal-body p-3 text-center">
                    <p class="text-muted small mb-3">
                        សូមស្គេនមុខរបស់អ្នក ដើម្បីប្រើជំនួសសំបុត្រនៅថ្ងៃចូលរួម។
                    </p>

                    <!-- Camera Container -->
                    <div class="face-scan-box mb-3 position-relative" id="faceScanBox">
                        <video id="faceModalVideo" class="face-scan-video" autoplay playsinline muted></video>
                        <canvas id="faceModalOverlay" class="face-scan-overlay"></canvas>
                        <div id="faceReticle" class="face-reticle-oval"></div>
                        <div id="faceFlash" class="face-capture-flash"></div>

                        <!-- Quality Badge floating top right -->
                        <div class="position-absolute top-0 end-0 m-2" style="z-index: 5;">
                            <span id="faceQualityBadge" class="badge rounded-pill bg-dark bg-opacity-75 text-white px-2 py-1 small fw-bold" style="font-size: 0.75rem; backdrop-filter: blur(4px);">
                                គុណភាព៖ 0%
                            </span>
                        </div>

                        <!-- Steady Banner floating bottom when quality >= 90% -->
                        <div id="steadyBanner" class="position-absolute bottom-0 start-0 end-0 p-2 text-white small fw-bold bg-success bg-opacity-90 d-none" style="z-index: 5; backdrop-filter: blur(2px);">
                            <i class="bi bi-camera-fill me-1"></i> ស្ងៀម... កំពុងថតស្វ័យប្រវត្តិ
                        </div>
                    </div>

                    <!-- Guidance Box -->
                    <div id="faceGuidanceBox" class="alert alert-info py-2 px-3 small d-flex align-items-center justify-content-center gap-2 mb-2 text-center fw-medium shadow-xs" style="border-radius: 12px; min-height: 44px;">
                        <i id="faceGuidanceIcon" class="bi bi-emoji-smile fs-5 text-primary flex-shrink-0"></i>
                        <span id="faceGuidanceText">សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា</span>
                    </div>

                    <!-- Auto-Capture Progress Bar (Holds steady >= 90%) -->
                    <div class="progress mb-3" style="height: 6px; border-radius: 3px;" id="faceProgressContainer">
                        <div id="faceProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                    </div>

                    <!-- Captured Result Preview (Hidden until capture) -->
                    <div id="faceCapturedView" class="d-none text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <img id="faceCapturedImg" src="" alt="Captured Face" class="rounded-4 border border-3 border-success shadow" style="max-height: 180px; max-width: 180px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-success text-white rounded-circle p-1 px-2 shadow">
                                <i class="bi bi-check-lg fw-bold"></i>
                            </span>
                        </div>
                        <div class="mt-2 text-success fw-bold small">
                            <i class="bi bi-check-circle-fill me-1"></i> ស្គេនមុខបានជោគជ័យ!
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center mt-2" id="faceModalControls">
                        <button type="button" id="btnManualSnap" class="btn btn-outline-primary btn-sm px-3 fw-bold" onclick="forceCaptureFace()" disabled>
                            <i class="bi bi-camera me-1"></i> ថតឥឡូវនេះ
                        </button>
                        <button type="button" id="btnRetakeSnap" class="btn btn-outline-secondary btn-sm px-3 d-none" onclick="retakeFaceScan()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> ថតឡើងវិញ
                        </button>
                        <?php if (empty($delegationFormFields['require_face_enroll'])): ?>
                            <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" onclick="skipFaceScan()">
                                រំលងជំហាននេះ
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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

function renderLocalQr(memberId, token) {
    const img = document.getElementById('qrImg_' + memberId);
    const container = document.getElementById('qrcode_' + memberId);
    if (!container) return;
    if (img) img.style.display = 'none';
    container.classList.remove('d-none');
    try {
        if (typeof QRCode !== 'undefined') {
            container.innerHTML = '';
            new QRCode(container, {
                text: token,
                width: 160,
                height: 160,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    } catch (e) {
        console.error('Local QR render error:', e);
    }
}

async function saveDelegationTicket(memberId, regCode, btn) {
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> កំពុងរក្សាទុក...';
    }

    try {
        const ticketEl = document.getElementById('ticketCaptureArea_' + memberId);
        if (!ticketEl) throw new Error('Ticket element not found');

        // Ensure images inside ticket are loaded before capture
        const imgs = ticketEl.querySelectorAll('img');
        await Promise.all(Array.from(imgs).map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(res => {
                img.onload = img.onerror = res;
            });
        }));

        const canvas = await html2canvas(ticketEl, {
            scale: 2.5,
            useCORS: true,
            allowTaint: false,
            backgroundColor: '#ffffff',
            logging: false
        });

        const fileName = 'Ticket-' + (regCode || 'REG') + '.png';
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
        let file = null;
        if (blob && typeof File !== 'undefined') {
            try {
                file = new File([blob], fileName, { type: 'image/png' });
            } catch (e) {}
        }

        // 1. Try Web Share API (native iOS / Android save directly to Photos / Gallery)
        let shared = false;
        if (file && navigator.canShare && navigator.canShare({ files: [file] })) {
            try {
                await navigator.share({
                    files: [file],
                    title: 'កាតសមាជិកប្រតិភូ',
                    text: 'កាត QR សម្គាល់ខ្លួនចូលរួម'
                });
                shared = true;
            } catch (shareErr) {
                // Share dismissed
            }
        }

        // 2. Direct Blob file download
        if (!shared && blob) {
            const blobUrl = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = fileName;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);
        }

        // 3. Show feedback
        const feedbackEl = document.getElementById('saveFeedback_' + memberId);
        if (feedbackEl) {
            feedbackEl.classList.remove('d-none');
        }

        if (btn) {
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> បានរក្សាទុក!';

            setTimeout(() => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
                btn.innerHTML = originalHtml;
            }, 2500);
        }

    } catch (err) {
        console.error('Error saving ticket:', err);
        alert('មានបញ្ហាក្នុងការរក្សាទុក។ សូមថតអេក្រង់កាតនេះទុកជំនួសវិញ។');
        if (btn) btn.innerHTML = originalHtml;
    } finally {
        if (btn) btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
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

function submitDelegationForm() {
    if (isDelegationSubmitting) return;
    isDelegationSubmitting = true;
    const form = document.getElementById('delegationRegisterForm');
    const btn = document.getElementById('btnSubmitDelegation');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> កំពុងចុះឈ្មោះ...';
    }
    form.submit();
}

function handleDelegationSubmit(e, form) {
    if (isDelegationSubmitting) return true;
    if (e && e.preventDefault) e.preventDefault();
    triggerDelegationRegister();
    return false;
}

function triggerDelegationRegister() {
    const form = document.getElementById('delegationRegisterForm');
    if (!form.reportValidity()) return;

    const faceEnrollEnabled = <?= !empty($delegationFormFields['show_face_enroll']) ? 'true' : 'false' ?>;
    const descInp = document.getElementById('faceDescriptorInput');
    const hasFace = descInp && descInp.value && descInp.value.trim().length > 10;

    if (faceEnrollEnabled && !hasFace) {
        openFaceScanModal();
        return;
    }

    submitDelegationForm();
}

function openFaceScanModal() {
    const modalEl = document.getElementById('faceScanModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const m = bootstrap.Modal.getOrCreateInstance(modalEl);
        m.show();
    }
}

function skipFaceScan() {
    const modalEl = document.getElementById('faceScanModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const m = bootstrap.Modal.getInstance(modalEl);
        if (m) m.hide();
    }
    submitDelegationForm();
}

function onCloseFaceModal() {
    // Modal closed by user
}
</script>

<?php if (!empty($delegationFormFields['show_face_enroll'])): ?>
<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
(function() {
    'use strict';

    const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
    let faceStream = null;
    let faceDetectionLoop = null;
    let faceModelsLoaded = false;
    let faceCaptured = false;
    let steadyTicks = 0;
    const REQUIRED_STEADY_TICKS = 3; // ~600ms of sustained >=90% quality

    const modalEl     = document.getElementById('faceScanModal');
    const video       = document.getElementById('faceModalVideo');
    const overlay     = document.getElementById('faceModalOverlay');
    const reticle     = document.getElementById('faceReticle');
    const flashEl     = document.getElementById('faceFlash');
    const badgeEl     = document.getElementById('faceQualityBadge');
    const bannerEl    = document.getElementById('steadyBanner');
    const guideBox    = document.getElementById('faceGuidanceBox');
    const guideIcon   = document.getElementById('faceGuidanceIcon');
    const guideText   = document.getElementById('faceGuidanceText');
    const progressBar = document.getElementById('faceProgressBar');
    const capView     = document.getElementById('faceCapturedView');
    const capImg      = document.getElementById('faceCapturedImg');
    const btnManual   = document.getElementById('btnManualSnap');
    const btnRetake   = document.getElementById('btnRetakeSnap');
    const descInp     = document.getElementById('faceDescriptorInput');
    const photoInp    = document.getElementById('facePhotoInput');
    const alertEnrolled = document.getElementById('faceCapturedAlert');

    let latestDetection = null;

    function playSuccessChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now); // D5
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5
            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.35);
        } catch (e) {}
    }

    function setGuidance(text, iconClass, alertClass) {
        if (!guideText) return;
        guideText.textContent = text;
        if (guideIcon) guideIcon.className = 'bi ' + iconClass + ' fs-5 flex-shrink-0';
        if (guideBox) {
            guideBox.className = 'alert ' + (alertClass || 'alert-info') + ' py-2 px-3 small d-flex align-items-center justify-content-center gap-2 mb-2 text-center fw-medium shadow-xs';
        }
    }

    async function loadModels() {
        if (faceModelsLoaded) return true;
        try {
            setGuidance('កំពុងផ្ទុកប្រព័ន្ធ AI ស្គេនមុខ...', 'spinner-border spinner-border-sm text-primary', 'alert-secondary');
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
            ]);
            faceModelsLoaded = true;
            return true;
        } catch (e) {
            console.error('Face models load error:', e);
            setGuidance('មិនអាចផ្ទុក AI model បានទេ។ ពិនិត្យការភ្ជាប់អ៊ីនធឺណិត។', 'bi-exclamation-triangle-fill text-warning', 'alert-warning');
            return false;
        }
    }

    async function startCamera() {
        if (faceStream) return;
        try {
            faceStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });
            video.srcObject = faceStream;
            await new Promise(r => video.addEventListener('loadedmetadata', r, { once: true }));
            await video.play();
        } catch (err) {
            console.error('Camera error:', err);
            setGuidance('មិនអាចបើកកាមេរ៉ាបានទេ៖ ' + (err.message || 'សូមផ្តល់សិទ្ធិប្រើកាមេរ៉ា'), 'bi-camera-video-off-fill text-danger', 'alert-danger');
        }
    }

    function stopCamera() {
        if (faceDetectionLoop) {
            clearInterval(faceDetectionLoop);
            faceDetectionLoop = null;
        }
        if (faceStream) {
            faceStream.getTracks().forEach(t => t.stop());
            faceStream = null;
        }
        if (video) video.srcObject = null;
    }

    // Geometry & Quality Analysis (Distance, Center, Roll, Yaw, Pitch)
    function analyzeQuality(detection, vWidth, vHeight) {
        const box = detection.detection.box;
        const landmarks = detection.landmarks;
        let penalty = 0;
        let advice = null;
        let icon = 'bi-emoji-smile';

        // 1. Distance / Size check
        const faceRatio = box.width / vWidth;
        if (faceRatio < 0.25) {
            penalty += 25;
            advice = 'សូមខិតមកជិតកាមេរ៉ាបន្តិច';
            icon = 'bi-zoom-in';
        } else if (faceRatio > 0.68) {
            penalty += 25;
            advice = 'សូមថយក្រោយបន្តិច';
            icon = 'bi-zoom-out';
        }

        // 2. Centering check (in mirrored video)
        const centerX = (box.x + box.width / 2) / vWidth;
        const centerY = (box.y + box.height / 2) / vHeight;
        if (!advice && (centerX < 0.32 || centerX > 0.68 || centerY < 0.28 || centerY > 0.72)) {
            penalty += 15;
            advice = 'សូមរំកិលមុខមកចំកណ្តាល';
            icon = 'bi-arrows-move';
        }

        // 3. Roll angle (eye tilt)
        const leftEye = landmarks.getLeftEye();
        const rightEye = landmarks.getRightEye();
        const avgL = { x: leftEye.reduce((s,p)=>s+p.x,0)/leftEye.length, y: leftEye.reduce((s,p)=>s+p.y,0)/leftEye.length };
        const avgR = { x: rightEye.reduce((s,p)=>s+p.x,0)/rightEye.length, y: rightEye.reduce((s,p)=>s+p.y,0)/rightEye.length };
        const rollDeg = Math.atan2(avgR.y - avgL.y, avgR.x - avgL.x) * (180 / Math.PI);
        if (!advice && Math.abs(rollDeg) > 9) {
            penalty += 20;
            advice = 'សូមតម្រង់ក្បាលឱ្យត្រង់';
            icon = 'bi-arrow-clockwise';
        }

        // 4. Yaw (Head turn left/right)
        const noseTip = landmarks.positions[30];
        const distL = Math.abs(noseTip.x - avgL.x);
        const distR = Math.abs(noseTip.x - avgR.x);
        const yawRatio = distL / Math.max(1, distR);
        if (!advice) {
            if (yawRatio < 0.62) {
                penalty += 22;
                advice = 'សូមងាកមុខមកឆ្វេងបន្តិច';
                icon = 'bi-arrow-left-circle';
            } else if (yawRatio > 1.62) {
                penalty += 22;
                advice = 'សូមងាកមុខមកស្តាំបន្តិច';
                icon = 'bi-arrow-right-circle';
            }
        }

        // 5. Pitch (Head tilt up/down)
        const eyeMidY = (avgL.y + avgR.y) / 2;
        const chin = landmarks.positions[8];
        const upper = noseTip.y - eyeMidY;
        const lower = chin.y - noseTip.y;
        const pitchRatio = upper / Math.max(1, lower);
        if (!advice) {
            if (pitchRatio < 0.38) {
                penalty += 20;
                advice = 'សូមឱនមុខបន្តិច';
                icon = 'bi-arrow-down-circle';
            } else if (pitchRatio > 1.05) {
                penalty += 20;
                advice = 'សូមងើបមុខបន្តិច';
                icon = 'bi-arrow-up-circle';
            }
        }

        const rawScore = Math.round(detection.detection.score * 100);
        const quality = Math.max(10, Math.min(100, rawScore - penalty));

        return {
            quality: quality,
            advice: advice || 'គុណភាពល្អណាស់! សូមរក្សាភាពស្ងៀមស្ងាត់...',
            icon: advice ? icon : 'bi-check-circle-fill'
        };
    }

    function startDetection() {
        if (faceDetectionLoop) return;
        const ctx = overlay.getContext('2d');

        faceDetectionLoop = setInterval(async () => {
            if (faceCaptured || !faceModelsLoaded || !video || video.paused || video.readyState < 2) return;

            const vw = video.videoWidth || overlay.offsetWidth || 640;
            const vh = video.videoHeight || overlay.offsetHeight || 480;
            overlay.width = vw;
            overlay.height = vh;
            ctx.clearRect(0, 0, vw, vh);

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.45 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                latestDetection = detection;

                if (!detection) {
                    steadyTicks = 0;
                    if (badgeEl) {
                        badgeEl.textContent = 'គុណភាព៖ 0%';
                        badgeEl.className = 'badge rounded-pill bg-dark bg-opacity-75 text-white px-2 py-1 small fw-bold';
                    }
                    if (reticle) reticle.className = 'face-reticle-oval';
                    if (bannerEl) bannerEl.classList.add('d-none');
                    if (progressBar) progressBar.style.width = '0%';
                    if (btnManual) btnManual.disabled = true;
                    setGuidance('សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា', 'bi-emoji-smile', 'alert-info');
                    return;
                }

                const analysis = analyzeQuality(detection, vw, vh);
                const q = analysis.quality;

                if (badgeEl) {
                    badgeEl.textContent = 'គុណភាព៖ ' + q + '%';
                    badgeEl.className = 'badge rounded-pill ' + (q >= 90 ? 'bg-success' : (q >= 70 ? 'bg-primary' : 'bg-warning text-dark')) + ' px-2 py-1 small fw-bold shadow-sm';
                }

                if (btnManual) btnManual.disabled = (q < 65);

                if (q >= 90) {
                    if (reticle) reticle.className = 'face-reticle-oval good';
                    if (bannerEl) bannerEl.classList.remove('d-none');
                    setGuidance(analysis.advice, analysis.icon, 'alert-success');

                    steadyTicks++;
                    const pct = Math.min(100, Math.round((steadyTicks / REQUIRED_STEADY_TICKS) * 100));
                    if (progressBar) progressBar.style.width = pct + '%';

                    if (steadyTicks >= REQUIRED_STEADY_TICKS) {
                        triggerCapture(detection);
                    }
                } else {
                    steadyTicks = 0;
                    if (progressBar) progressBar.style.width = '0%';
                    if (reticle) reticle.className = 'face-reticle-oval ' + (q >= 70 ? '' : 'warning');
                    if (bannerEl) bannerEl.classList.add('d-none');
                    setGuidance(analysis.advice, analysis.icon, q >= 70 ? 'alert-info' : 'alert-warning');
                }

            } catch (err) {
                console.error('Detection loop error:', err);
            }
        }, 180);
    }

    function triggerCapture(detection) {
        if (faceCaptured || !detection) return;
        faceCaptured = true;

        // Visual flash & Audio chime
        if (flashEl) {
            flashEl.classList.add('active');
            setTimeout(() => flashEl.classList.remove('active'), 250);
        }
        playSuccessChime();

        // Capture frame to 640x480 mirrored canvas
        const vw = video.videoWidth || 640;
        const vh = video.videoHeight || 480;
        const canvas = document.createElement('canvas');
        canvas.width = vw;
        canvas.height = vh;
        const ctx = canvas.getContext('2d');
        ctx.translate(vw, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, vw, vh);

        const photoDataUrl = canvas.toDataURL('image/jpeg', 0.85);

        // Save descriptor & photo
        if (descInp) descInp.value = JSON.stringify(Array.from(detection.descriptor));
        if (photoInp) photoInp.value = photoDataUrl;

        // Stop camera & detection
        stopCamera();

        // Update UI
        if (capImg) capImg.src = photoDataUrl;
        if (capView) capView.classList.remove('d-none');
        document.getElementById('faceScanBox')?.classList.add('d-none');
        if (btnManual) btnManual.classList.add('d-none');
        if (btnRetake) btnRetake.classList.remove('d-none');
        setGuidance('ស្គេនមុខបានជោគជ័យ! កំពុងចុះឈ្មោះចូលរួម...', 'bi-check-circle-fill text-success', 'alert-success');

        if (alertEnrolled) alertEnrolled.classList.remove('d-none');

        // Automatically submit form after short delay
        setTimeout(() => {
            const m = bootstrap.Modal.getInstance(modalEl);
            if (m) m.hide();
            submitDelegationForm();
        }, 700);
    }

    window.forceCaptureFace = function() {
        if (!latestDetection) {
            alert('រកមុខមិនឃើញទេ! សូមតម្រង់មុខឱ្យចំកាមេរ៉ា។');
            return;
        }
        triggerCapture(latestDetection);
    };

    window.retakeFaceScan = function() {
        faceCaptured = false;
        steadyTicks = 0;
        latestDetection = null;
        if (descInp) descInp.value = '';
        if (photoInp) photoInp.value = '';
        if (alertEnrolled) alertEnrolled.classList.add('d-none');
        if (capView) capView.classList.add('d-none');
        document.getElementById('faceScanBox')?.classList.remove('d-none');
        if (btnManual) {
            btnManual.classList.remove('d-none');
            btnManual.disabled = true;
        }
        if (btnRetake) btnRetake.classList.add('d-none');
        if (progressBar) progressBar.style.width = '0%';
        if (reticle) reticle.className = 'face-reticle-oval';
        startCamera().then(() => startDetection());
    };

    // Modal Events
    if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', async function() {
            faceCaptured = false;
            steadyTicks = 0;
            latestDetection = null;
            if (capView) capView.classList.add('d-none');
            document.getElementById('faceScanBox')?.classList.remove('d-none');
            if (btnManual) {
                btnManual.classList.remove('d-none');
                btnManual.disabled = true;
            }
            if (btnRetake) btnRetake.classList.add('d-none');
            if (progressBar) progressBar.style.width = '0%';

            await startCamera();
            const loaded = await loadModels();
            if (loaded) {
                setGuidance('សូមដាក់ផ្ទៃមុខរបស់អ្នកឱ្យចំកាមេរ៉ា', 'bi-emoji-smile', 'alert-info');
                startDetection();
            }
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            stopCamera();
        });
    }

})();
</script>
<?php endif; ?>
</body>
</html>
