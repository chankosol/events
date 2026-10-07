<?php $breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'បញ្ជីចុះឈ្មោះ', 'url' => null,                                  'icon' => 'person-check'],
]; ?>
<style>
.btn-action-approve {
    background-color: #10b981;
    border: 1px solid #10b981;
    color: #ffffff;
    font-size: 0.8125rem;
    font-weight: 500;
    height: 31px;
    padding: 0 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
    transition: all 0.15s ease-in-out;
    text-decoration: none;
    cursor: pointer;
}
.btn-action-approve:hover {
    background-color: #059669;
    border-color: #059669;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(16, 185, 129, 0.3);
}

.btn-action-reject {
    background-color: #ffffff;
    border: 1px solid #fecaca;
    color: #ef4444;
    width: 31px;
    height: 31px;
    padding: 0;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8125rem;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}
.btn-action-reject:hover {
    background-color: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(239, 68, 68, 0.25);
}

.btn-action-view {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    width: 31px;
    height: 31px;
    padding: 0;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}
.btn-action-view:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
    transform: translateY(-1px);
}
</style>

<div class="container-fluid py-4">

    <?php $isUnpaid = (!$billing || $billing['payment_status'] !== 'paid'); ?>

    <?php if ($isUnpaid): ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4 shadow-sm border-warning">
            <div>
                <strong>សិក្ខាសាលាមិនទាន់បង់ថ្លៃដំណើរការប្រព័ន្ធ ($<?= number_format($billing['platform_fee'] ?? 10, 2) ?>)</strong><br>
                <span class="small text-muted">សូមបង់ថ្លៃសេវាប្រព័ន្ធដើម្បីបន្ថែម ឬនាំចូលសិក្ខាកាម។</span>
            </div>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/activate" class="btn btn-warning fw-bold px-3">
                <i class="bi bi-credit-card me-1"></i> បង់ថ្លៃប្រព័ន្ធឥឡូវនេះ
            </a>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">បញ្ជីចុះឈ្មោះ - <?= htmlspecialchars($workshop['name']) ?></h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#formFieldsModal" onclick="openFormFieldsModal()" title="កំណត់ទម្រង់ចុះឈ្មោះ">
                <i class="bi bi-sliders me-1"></i> កំណត់ទម្រង់ចុះឈ្មោះ
            </button>
            <button type="button" class="btn btn-outline-info text-dark d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#shareLinksModal">
                <i class="bi bi-qr-code-scan me-1 text-primary"></i> តំណភ្ជាប់ & QR
            </button>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/export" class="btn btn-outline-success d-inline-flex align-items-center fw-medium" title="ទាញយកជា Excel (.xlsx)">
                <i class="bi bi-file-earmark-excel-fill me-1 text-success"></i> ទាញយក Excel
            </a>
            <?php if ($isUnpaid): ?>
                <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/activate" class="btn btn-outline-primary d-inline-flex align-items-center" title="បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> នាំចូល
                </a>
                <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/activate" class="btn btn-primary d-inline-flex align-items-center" title="បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ">
                    <i class="bi bi-plus-lg me-1"></i> ចុះឈ្មោះ
                </a>
            <?php else: ?>
                <button class="btn btn-outline-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> នាំចូល
                </button>
                <button class="btn btn-primary d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addRegistrationModal">
                    <i class="bi bi-plus-lg me-1"></i> ចុះឈ្មោះ
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs" id="statusTabs">
                <li class="nav-item"><a class="nav-link active" href="#" data-status="all">ទាំងអស់</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="pending">រង់ចាំពិនិត្យ / អនុម័ត</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="confirmed">បានបញ្ជាក់</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="waitlisted">បញ្ជីរង់ចាំ</a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="cancelled">បានលុបចោល / បដិសេធ</a></li>
            </ul>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="ស្វែងរកតាមឈ្មោះ អ៊ីមែល ទូរស័ព្ទ ឬកូដ...">
                    </div>
                </div>
                <div class="col-md-7 text-md-end mt-2 mt-md-0" id="bulkActionsBar" style="display: none;">
                    <span class="text-muted small me-2"><span id="selectedCount" class="fw-bold text-primary">0</span> បានជ្រើសរើស:</span>
                    <button type="button" class="btn btn-sm btn-success fw-bold" onclick="bulkApprove()">
                        <i class="bi bi-check-all me-1"></i> អនុម័តទាំងអស់ដែលបានជ្រើសរើស
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="registrationsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAll"></th>
                            <th>ឈ្មោះសិក្ខាកាម & លេខទូរស័ព្ទ</th>
                            <th>រាជធានី-ខេត្ត / ស្ថាប័ន</th>
                            <th>តួនាទី</th>
                            <th>ស្ថានភាព</th>
                            <th class="text-end pe-3" style="min-width: 140px;">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $reg): ?>
                        <tr class="reg-row" data-status="<?= htmlspecialchars($reg['status']) ?>" data-search="<?= strtolower(htmlspecialchars($reg['participant_name'].' '.($reg['email'] ?? '').' '.$reg['phone'].' '.$reg['registration_code'].' '.($reg['company'] ?? '').' '.($reg['position'] ?? '').' '.($reg['province'] ?? ''))) ?>">
                            <td><input type="checkbox" class="reg-select" value="<?= $reg['id'] ?>" onchange="updateBulkBar()"></td>
                            <td>
                                <div class="d-flex align-items-center flex-wrap gap-1">
                                    <strong class="text-dark" role="button" data-bs-toggle="tooltip" data-bs-placement="top" title="កូដចុះឈ្មោះ: <?= htmlspecialchars($reg['registration_code']) ?>">
                                        <?= htmlspecialchars($reg['participant_name']) ?>
                                    </strong>
                                    <?php if (($reg['gender'] ?? '') === 'female' || ($reg['gender'] ?? '') === 'ស្រី'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.68rem; padding: 2px 6px;">ស្រី</span>
                                    <?php elseif (($reg['gender'] ?? '') === 'male' || ($reg['gender'] ?? '') === 'ប្រុស'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem; padding: 2px 6px;">ប្រុស</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-muted small mt-1 font-monospace">
                                    <i class="bi bi-telephone text-secondary me-1"></i><?= htmlspecialchars($reg['phone'] ?: '-') ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">
                                    <i class="bi bi-geo-alt text-danger me-1 small"></i><?= !empty($reg['province']) ? htmlspecialchars($reg['province']) : '<span class="text-muted small">-</span>' ?>
                                </div>
                                <?php if (!empty($reg['company'])): ?>
                                    <div class="text-secondary small mt-1 text-truncate" style="max-width: 240px;" title="<?= htmlspecialchars($reg['company']) ?>">
                                        <i class="bi bi-building text-muted me-1 small"></i><?= htmlspecialchars($reg['company']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($reg['position']) ? htmlspecialchars($reg['position']) : '<span class="text-muted small">-</span>' ?>
                            </td>
                            <td>
                                <?php if (!empty($reg['created_at'])): ?>
                                    <div class="text-muted small mb-1" style="font-size: 0.75rem; white-space: nowrap;" title="<?= formatDateTime($reg['created_at'], 'd M Y, H:i:s') ?>">
                                        <i class="bi bi-clock me-1 text-secondary"></i><?= formatDateTime($reg['created_at'], 'd/m/Y H:i') ?>
                                    </div>
                                <?php endif; ?>
                                <?= statusBadge($reg['status']) ?>
                            </td>
                            <td class="text-end pe-3" style="white-space: nowrap;">
                                <?php if (in_array($reg['status'], ['pending_approval', 'pending_verification', 'pending', 'waitlisted'])): ?>
                                    <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                        <button type="button" class="btn-action-approve" onclick="approveReg(<?= $reg['id'] ?>)" title="អនុម័តការចុះឈ្មោះ">
                                            <i class="bi bi-check-lg"></i>
                                            <span>អនុម័ត</span>
                                        </button>
                                        <button type="button" class="btn-action-reject" onclick="rejectReg(<?= $reg['id'] ?>)" title="បដិសេធ">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button type="button" class="btn-action-view" onclick="showRegModal(<?= htmlspecialchars(json_encode($reg), ENT_QUOTES, 'UTF-8') ?>)" title="មើលព័ត៌មានលម្អិត">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                        <button type="button" class="btn-action-view" onclick="showRegModal(<?= htmlspecialchars(json_encode($reg), ENT_QUOTES, 'UTF-8') ?>)" title="មើលព័ត៌មានលម្អិត">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($reg['status'] === 'confirmed'): ?>
                                            <button type="button" class="btn-action-reject" onclick="rejectReg(<?= $reg['id'] ?>)" title="លុបចោល / បដិសេធ">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($registrations)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">មិនមានការចុះឈ្មោះនៅឡើយទេ។</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Share & Invite Links -->
<div class="modal fade" id="shareLinksModal" tabindex="-1" aria-labelledby="shareLinksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-light py-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="shareLinksModalLabel">
                    <i class="bi bi-link-45deg text-primary me-2"></i>តំណភ្ជាប់ចុះឈ្មោះសិក្ខាសាលា
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Direct Invite Link (Auto QR / No approval needed) -->
                <div class="card bg-success-subtle border border-success-subtle mb-4">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <strong class="text-success"><i class="bi bi-star-fill text-warning me-1"></i> តំណភ្ជាប់ពិសេស (Direct Invite - ចេញ QR ភ្លាមៗ)</strong>
                            <span class="badge bg-success">Auto-Approve</span>
                        </div>
                        <p class="small text-muted mb-2">
                            សិក្ខាកាមដែលចុះឈ្មោះតាមតំណភ្ជាប់នេះ នឹងទទួលបានសំបុត្រ និង QR កូដភ្លាមៗដោយស្វ័យប្រវត្តិ ដោយមិនចាំបាច់បុគ្គលិកចុចអនុម័តឡើយ។
                        </p>
                        <div class="input-group">
                            <input type="text" id="directInviteLinkInput" class="form-control form-control-sm bg-white" readonly value="<?= APP_URL ?>/event/<?= htmlspecialchars($workshop['slug']) ?>/register?invite=direct">
                            <button class="btn btn-sm btn-success fw-bold px-3" type="button" onclick="copyLinkWithFeedback('directInviteLinkInput', this)">
                                <i class="bi bi-clipboard me-1"></i> ចម្លង
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Public Link -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small mb-1">
                        <i class="bi bi-globe me-1 text-primary"></i> តំណភ្ជាប់សាធារណៈ (Public Registration Link)
                    </label>
                    <div class="input-group">
                        <input type="text" id="publicLinkInput" class="form-control form-control-sm bg-light" readonly value="<?= APP_URL ?>/event/<?= htmlspecialchars($workshop['slug']) ?>/register">
                        <button class="btn btn-sm btn-outline-secondary px-3" type="button" onclick="copyLinkWithFeedback('publicLinkInput', this)">
                            <i class="bi bi-clipboard me-1"></i> ចម្លង
                        </button>
                    </div>
                </div>

                <!-- Workshop Settings Status -->
                <div class="p-3 bg-light rounded border small">
                    <div class="fw-bold mb-1 text-secondary">ស្ថានភាពការកំណត់បច្ចុប្បន្ន:</div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>របៀបគិតប្រាក់:</span>
                        <strong class="text-primary"><?= $workshop['payment_mode'] === 'free' ? 'ឥតគិតថ្លៃ (Free Mode)' : 'មានបង់ប្រាក់ (Paid)' ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span>ការចេញសំបុត្រ/QR ស្វ័យប្រវត្តិ:</span>
                        <strong class="<?= !empty($workshop['auto_confirm']) && empty($workshop['requires_approval']) ? 'text-success' : 'text-warning' ?>">
                            <?= !empty($workshop['auto_confirm']) && empty($workshop['requires_approval']) ? '✓ បើក (Auto-Approved គ្រប់គ្នា)' : 'រង់ចាំអនុម័តដោយដៃ' ?>
                        </strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 border-top">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ</button>
                <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/edit" class="btn btn-primary btn-sm">
                    <i class="bi bi-gear me-1"></i> កែប្រែការកំណត់សិក្ខាសាលា
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Registration -->
<div class="modal fade" id="addRegistrationModal" tabindex="-1" aria-labelledby="addRegistrationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addRegistrationModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i>បន្ថែមការចុះឈ្មោះសិក្ខាកាមថ្មី
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations">
                <?= csrfField() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="ឧ. សុខ ចិន្តា" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">ភេទ</label>
                            <select name="gender" class="form-select">
                                <option value="male" selected>ប្រុស (Male)</option>
                                <option value="female">ស្រី (Female)</option>
                                <option value="other">ផ្សេងៗ (Other)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">លេខទូរស័ព្ទ <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="012 345 678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">អ៊ីមែល (ជម្រើស)</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">រាជធានី-ខេត្ត</label>
                            <select name="province" class="form-select">
                                <option value="">-- ជ្រើសរើសរាជធានី-ខេត្ត --</option>
                                <?php foreach ($provinces ?? [] as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ស្ថាប័ន / អង្គភាព</label>
                            <input type="text" name="company" class="form-control" placeholder="ឧ. មន្ទីរអប់រំ ឬ ក្រុមហ៊ុន...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">តួនាទី / មុខតំណែង</label>
                            <input type="text" name="position" class="form-control" placeholder="ឧ. ប្រធានការិយាល័យ">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ប្រភេទសំបុត្រ</label>
                            <select name="ticket_id" class="form-select">
                                <option value="">-- សំបុត្រទូទៅ (Standard) --</option>
                                <?php foreach ($tickets ?? [] as $t): ?>
                                    <option value="<?= $t['id'] ?>">
                                        <?= htmlspecialchars($t['name']) ?> ($<?= number_format((float)$t['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">ស្ថានភាពចុះឈ្មោះ</label>
                            <select name="status" class="form-select">
                                <option value="confirmed" selected>បានបញ្ជាក់ (Confirmed)</option>
                                <option value="pending">រង់ចាំពិនិត្យ (Pending)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ស្ថានភាពទូទាត់</label>
                            <select name="payment_status" class="form-select">
                                <option value="paid_cash" selected>បានបង់ជាសាច់ប្រាក់ (Paid Cash)</option>
                                <option value="paid">បានបង់តាមធនាគារ (Paid Bank)</option>
                                <option value="complimentary">ឥតគិតថ្លៃ (Complimentary / VIP)</option>
                                <option value="unpaid">មិនទាន់បង់ (Unpaid)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">ប្រភេទអ្នកចូលរួម</label>
                            <select name="attendee_type" class="form-select">
                                <option value="general" selected>អ្នកចូលរួមទូទៅ (General Attendee)</option>
                                <option value="delegate">សមាជិកប្រតិភូ (Delegate)</option>
                                <option value="vip">ភ្ញៀវកិត្តិយស (VIP)</option>
                                <option value="assistant">ជំនួយការ / អ្នកបើកបរ (Driver/Assistant)</option>
                                <option value="media">អ្នកសារព័ត៌មាន / ជាងថត (Media / Photographer)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_allowance_eligible" id="allowanceCheck" value="1" checked>
                                <label class="form-check-label fw-semibold" for="allowanceCheck">
                                    <i class="bi bi-cash-coin text-success me-1"></i> មានសិទ្ធិបើកប្រាក់ឧបត្ថម្ភ
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">កំណត់សម្គាល់បន្ថែម (ជម្រើស)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="ព័ត៌មានលម្អិតបន្ថែម..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check-circle me-1"></i> រក្សាទុកការចុះឈ្មោះ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Import Registrations / Participants -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i> នាំចូលបញ្ជីសិក្ខាកាម
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/import" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-body p-4">
                    <!-- Instruction & Sample Download -->
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="fw-bold"><i class="bi bi-info-circle-fill me-1"></i> ទម្រង់ឯកសារនាំចូល</div>
                            <div class="small text-muted">គាំទ្រឯកសារ CSV ឬ Excel (.csv) ដែលមានក្បាលជួរជាភាសាខ្មែរ ឬអង់គ្លេស។</div>
                        </div>
                        <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/sample-csv" class="btn btn-sm btn-light border fw-semibold text-primary">
                            <i class="bi bi-download me-1"></i> ទាញយកគំរូ
                        </a>
                    </div>

                    <!-- File Input -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">ជ្រើសរើសឯកសារ CSV <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control form-control-lg" accept=".csv,.txt" required>
                        <div class="form-text text-muted">
                            សូមរក្សាទុកតារាង Excel ជាប្រភេទ <strong>CSV UTF-8</strong> ឬ <strong>CSV (.csv)</strong>។
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ប្រភេទសំបុត្រ</label>
                            <select name="default_ticket_id" class="form-select">
                                <?php foreach ($tickets as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> ($<?= number_format($t['price'], 2) ?>)</option>
                                <?php endforeach; ?>
                                <?php if (empty($tickets)): ?>
                                    <option value="">ទូទៅ</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text small">ជ្រើសរើសប្រភេទសំបុត្រលំនាំដើម។</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ស្ថានភាពចុះឈ្មោះ</label>
                            <select name="default_status" class="form-select">
                                <option value="confirmed" selected>បានបញ្ជាក់</option>
                                <option value="pending">រង់ចាំពិនិត្យ</option>
                                <option value="waitlisted">បញ្ជីរង់ចាំ</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ការបង់ប្រាក់</label>
                            <select name="default_payment_status" class="form-select">
                                <option value="paid_cash" selected>បង់ផ្ទាល់</option>
                                <option value="paid">បានបង់រួច</option>
                                <option value="unpaid">មិនទាន់បង់</option>
                                <option value="complimentary">ឥតគិតថ្លៃ</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">ប្រភេទអ្នកចូលរួម</label>
                            <select name="default_attendee_type" class="form-select">
                                <option value="general" selected>ទូទៅ</option>
                                <option value="vip">VIP</option>
                                <option value="speaker">វាគ្មិន</option>
                                <option value="staff">បុគ្គលិក</option>
                            </select>
                        </div>
                    </div>

                    <!-- Column Guide Preview -->
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-bold small text-muted text-uppercase mb-2"><i class="bi bi-table me-1"></i> ទម្រង់ជួរឈរគំរូ</div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ឈ្មោះ <span class="text-danger">*</span></th>
                                        <th>លេខទូរស័ព្ទ</th>
                                        <th>អ៊ីមែល</th>
                                        <th>ភេទ</th>
                                        <th>អង្គភាព</th>
                                        <th>តួនាទី</th>
                                        <th>រាជធានី-ខេត្ត</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>សុខ ចាន់ដារ៉ា</td>
                                        <td>012345678</td>
                                        <td>sokh@example.com</td>
                                        <td>ប្រុស</td>
                                        <td>ក្រុមហ៊ុន ក</td>
                                        <td>ប្រធានផ្នែក</td>
                                        <td>ភ្នំពេញ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> ចាប់ផ្តើមនាំចូល
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View Registration Details -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="viewDetailsModalLabel">
                    <i class="bi bi-person-lines-fill me-2"></i> ព័ត៌មានលម្អិតសិក្ខាកាម
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <div class="text-muted small">លេខកូដចុះឈ្មោះ:</div>
                        <span class="fs-5 font-monospace fw-bold text-primary" id="mRegCode"></span>
                    </div>
                    <div id="mStatusBadge"></div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="text-muted small">ឈ្មោះសិក្ខាកាម:</div>
                        <div class="fw-bold" id="mName"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">លេខទូរស័ព្ទ:</div>
                        <div class="fw-bold" id="mPhone"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">អ៊ីមែល:</div>
                        <div class="fw-bold text-break" id="mEmail"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">អង្គភាព / ស្ថាប័ន:</div>
                        <div class="fw-bold" id="mCompany">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">តួនាទី / មុខតំណែង:</div>
                        <div class="fw-bold" id="mPosition">-</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">ប្រភេទសំបុត្រ:</div>
                        <div class="fw-bold" id="mTicket"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">ស្ថានភាពទូទាត់:</div>
                        <div class="fw-bold" id="mPayment"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small">កាលបរិច្ឆេទចុះឈ្មោះ:</div>
                        <div class="fw-bold" id="mDate"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បិទ</button>
                    <div class="d-none gap-2" id="modalActionsGroup">
                        <button type="button" class="btn btn-outline-danger" id="modalRejectBtn"><i class="bi bi-x-circle me-1"></i> បដិសេធ</button>
                        <button type="button" class="btn btn-success fw-bold" id="modalApproveBtn"><i class="bi bi-check-circle me-1"></i> អនុម័តការចុះឈ្មោះ</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Customize Registration Form Fields -->
<div class="modal fade" id="formFieldsModal" tabindex="-1" aria-labelledby="formFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/form-fields" method="POST">
                <?= csrfField() ?>
                <div class="modal-header bg-light py-3">
                    <h5 class="modal-title fw-bold text-dark mb-0" id="formFieldsModalLabel">
                        <i class="bi bi-sliders text-primary me-2"></i> កំណត់ទម្រង់ចុះឈ្មោះ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="alert alert-light border py-2 px-3 small mb-3 text-secondary rounded-3">
                        💡 គន្លឹះ៖ អាចជ្រើសរើសត្រឹមតែ <strong>ឈ្មោះ</strong> និង <strong>លេខទូរស័ព្ទ</strong> សម្រាប់ទម្រង់ខ្លីរហ័ស។
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ព័ត៌មាន</th>
                                    <th class="text-center" style="width: 100px;">បង្ហាញ</th>
                                    <th class="text-center" style="width: 110px;">ចាំបាច់</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- 1. Full Name -->
                                <tr class="bg-light-subtle">
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i> ឈ្មោះពេញ</span>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" checked disabled>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">ចាំបាច់</span>
                                    </td>
                                </tr>

                                <!-- 2. Phone Number -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-telephone me-2 text-success"></i> លេខទូរស័ព្ទ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_phone" id="chk_show_phone" value="1" <?= !empty($formConfig['show_phone']) ? 'checked' : '' ?> onchange="toggleReq('phone')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_phone" id="chk_req_phone" value="1" <?= !empty($formConfig['require_phone']) ? 'checked' : '' ?> <?= empty($formConfig['show_phone']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_phone">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 3. Email -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-envelope me-2 text-info"></i> អ៊ីមែល</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_email" id="chk_show_email" value="1" <?= !empty($formConfig['show_email']) ? 'checked' : '' ?> onchange="toggleReq('email')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_email" id="chk_req_email" value="1" <?= !empty($formConfig['require_email']) ? 'checked' : '' ?> <?= empty($formConfig['show_email']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_email">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 4. Gender -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-gender-ambiguous me-2 text-warning"></i> ភេទ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_gender" id="chk_show_gender" value="1" <?= !empty($formConfig['show_gender']) ? 'checked' : '' ?> onchange="toggleReq('gender')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_gender" id="chk_req_gender" value="1" <?= !empty($formConfig['require_gender']) ? 'checked' : '' ?> <?= empty($formConfig['show_gender']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_gender">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 5. Province -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i> រាជធានី-ខេត្ត</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_province" id="chk_show_province" value="1" <?= !empty($formConfig['show_province']) ? 'checked' : '' ?> onchange="toggleReq('province')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_province" id="chk_req_province" value="1" <?= !empty($formConfig['require_province']) ? 'checked' : '' ?> <?= empty($formConfig['show_province']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_province">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 6. Company -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i> ក្រុមហ៊ុន / ស្ថាប័ន</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_company" id="chk_show_company" value="1" <?= !empty($formConfig['show_company']) ? 'checked' : '' ?> onchange="toggleReq('company')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_company" id="chk_req_company" value="1" <?= !empty($formConfig['require_company']) ? 'checked' : '' ?> <?= empty($formConfig['show_company']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_company">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 7. Position -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-briefcase me-2 text-secondary"></i> តួនាទី / មុខតំណែង</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_position" id="chk_show_position" value="1" <?= !empty($formConfig['show_position']) ? 'checked' : '' ?> onchange="toggleReq('position')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_position" id="chk_req_position" value="1" <?= !empty($formConfig['require_position']) ? 'checked' : '' ?> <?= empty($formConfig['show_position']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="chk_req_position">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($customFields)): ?>
                        <?php 
                        $extraFields = [];
                        foreach ($customFields as $cf) {
                            $l = $cf['field_label'];
                            if (str_contains($l, 'ក្រុមហ៊ុន') || str_contains($l, 'ស្ថាប័ន')) continue;
                            if (str_contains($l, 'តួនាទី') || str_contains($l, 'មុខតំណែង')) continue;
                            if (str_contains($l, 'ទូរស័ព្ទ') || str_contains($l, 'អ៊ីមែល')) continue;
                            $extraFields[] = $cf;
                        }
                        ?>
                        <?php if (!empty($extraFields)): ?>
                        <div class="mt-3">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-question-circle me-1 text-primary"></i> សំណួរបន្ថែម</h6>
                            <div class="list-group">
                                <?php foreach ($extraFields as $ef): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <span class="fw-bold small"><?= htmlspecialchars($ef['field_label']) ?></span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="custom_fields[<?= $ef['id'] ?>]" value="active" <?= $ef['status'] === 'active' ? 'checked' : '' ?>>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">
                        <i class="bi bi-check2 me-1"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Filtering and Searching
    const rows = document.querySelectorAll('.reg-row');
    const tabs = document.querySelectorAll('#statusTabs .nav-link');
    const search = document.getElementById('searchInput');
    const selectAll = document.getElementById('selectAll');
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    let currentStatus = 'all';

    function filterRows() {
        const query = search ? search.value.toLowerCase().trim() : '';
        rows.forEach(row => {
            const rowStatus = (row.dataset.status || '').toLowerCase();
            let matchesStatus = false;
            if (currentStatus === 'all') {
                matchesStatus = true;
            } else if (currentStatus === 'pending') {
                matchesStatus = rowStatus.startsWith('pending') || rowStatus === 'pending';
            } else if (currentStatus === 'cancelled') {
                matchesStatus = rowStatus === 'cancelled' || rowStatus === 'rejected';
            } else {
                matchesStatus = (rowStatus === currentStatus);
            }
            const matchesSearch = !query || (row.dataset.search || '').includes(query);
            row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
        });
    }

    if (tabs) {
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentStatus = tab.dataset.status;
                filterRows();
            });
        });
    }

    if (search) {
        search.addEventListener('input', filterRows);
    }

    // Bulk selection
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.reg-select').forEach(cb => {
                if (cb.closest('tr').style.display !== 'none') {
                    cb.checked = selectAll.checked;
                }
            });
            updateBulkBar();
        });
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.reg-select:checked');
        if (selectedCount) selectedCount.innerText = checked.length;
        if (bulkBar) bulkBar.style.display = checked.length > 0 ? '' : 'none';
    }

    // Single Actions
    function approveReg(id) {
        if (!confirm('តើលោកអ្នកពិតជាចង់អនុម័តការចុះឈ្មោះនេះមែនទេ?')) return;
        const fd = new FormData();
        fd.append('_csrf_token', '<?= csrfToken() ?>');
        fetch(`<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/${id}/approve`, {method: 'POST', body: fd})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'មានបញ្ហាក្នុងការអនុម័ត');
                }
            })
            .catch(() => alert('មានបញ្ហាក្នុងការតភ្ជាប់ប្រព័ន្ធ'));
    }

    function rejectReg(id) {
        const reason = prompt('សូមបញ្ចូលមូលហេតុនៃការបដិសេធ (ប្រសិនបើមាន):');
        if (reason === null) return;
        const fd = new FormData();
        fd.append('_csrf_token', '<?= csrfToken() ?>');
        fd.append('reason', reason);
        fetch(`<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/${id}/reject`, {method: 'POST', body: fd})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'មានបញ្ហាក្នុងការបដិសេធ');
                }
            })
            .catch(() => alert('មានបញ្ហាក្នុងការតភ្ជាប់ប្រព័ន្ធ'));
    }

    function bulkApprove() {
        const checked = Array.from(document.querySelectorAll('.reg-select:checked')).map(cb => cb.value);
        if (checked.length === 0) return;
        if (!confirm(`តើលោកអ្នកពិតជាចង់អនុម័តការចុះឈ្មោះទាំង ${checked.length} នាក់នេះមែនទេ?`)) return;

        const fd = new FormData();
        fd.append('_csrf_token', '<?= csrfToken() ?>');
        fd.append('ids', JSON.stringify(checked));
        fetch(`<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/registrations/bulk-approve`, {method: 'POST', body: fd})
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message);
                }
            })
            .catch(() => alert('មានបញ្ហាក្នុងការតភ្ជាប់ប្រព័ន្ធ'));
    }

    let detailModalInstance = null;
    const workshopPaymentMode = <?= json_encode($workshop['payment_mode'] ?? 'free') ?>;

    function showRegModal(reg) {
        document.getElementById('mRegCode').innerText = reg.registration_code || '-';
        document.getElementById('mName').innerText = reg.participant_name || '-';
        document.getElementById('mPhone').innerText = reg.phone || '-';
        document.getElementById('mEmail').innerText = reg.email || '-';
        document.getElementById('mCompany').innerText = reg.company || '-';
        document.getElementById('mPosition').innerText = reg.position || '-';

        // 1. Status Badge at top right
        const statusEl = document.getElementById('mStatusBadge');
        if (statusEl) {
            const regStatusMap = {
                'confirmed': '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> បានបញ្ជាក់រួចរាល់</span>',
                'attended': '<span class="badge bg-primary px-2 py-1"><i class="bi bi-person-check me-1"></i> បានចូលរួម</span>',
                'completed': '<span class="badge bg-info px-2 py-1">បានបញ្ចប់</span>',
                'pending': '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i> រង់ចាំអនុម័ត</span>',
                'pending_approval': '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i> រង់ចាំអនុម័ត</span>',
                'pending_verification': '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-clock me-1"></i> រង់ចាំផ្ទៀងផ្ទាត់</span>',
                'waitlisted': '<span class="badge bg-info text-dark px-2 py-1">ក្នុងបញ្ជីរង់ចាំ</span>',
                'cancelled': '<span class="badge bg-danger px-2 py-1">បានលុបចោល</span>',
                'rejected': '<span class="badge bg-danger px-2 py-1">បានបដិសេធ</span>'
            };
            statusEl.innerHTML = regStatusMap[reg.status] || `<span class="badge bg-secondary">${reg.status || '-'}</span>`;
        }

        // 2. Free Detection
        const isFree = (workshopPaymentMode === 'free') || 
                       (reg.workshop_payment_mode === 'free') ||
                       (parseFloat(reg.final_amount || 0) === 0 && parseFloat(reg.ticket_price || 0) === 0) ||
                       (reg.is_complimentary == 1) || 
                       (reg.payment_status === 'complimentary');

        // 3. Ticket name
        const ticketBaseName = reg.ticket_name || 'សំបុត្រទូទៅ';
        document.getElementById('mTicket').innerHTML = isFree
            ? `${ticketBaseName} <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">ឥតគិតថ្លៃ</span>`
            : ticketBaseName;

        // 4. Payment status
        if (isFree) {
            document.getElementById('mPayment').innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-gift me-1"></i> ឥតគិតថ្លៃ</span>';
        } else {
            const payStatusMap = {
                'paid': '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> បានបង់ប្រាក់</span>',
                'paid_cash': '<span class="badge bg-success"><i class="bi bi-cash me-1"></i> បង់ប្រាក់សុទ្ធ</span>',
                'pending': '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> រង់ចាំទូទាត់</span>',
                'unpaid': '<span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i> មិនទាន់បង់</span>',
                'refunded': '<span class="badge bg-secondary">បានសងប្រាក់វិញ</span>',
                'waived': '<span class="badge bg-info text-dark">មិនគិតប្រាក់</span>',
                'complimentary': '<span class="badge bg-success-subtle text-success border">ឥតគិតថ្លៃ</span>',
                'rejected': '<span class="badge bg-danger">បដិសេធ</span>'
            };
            const pBadge = payStatusMap[reg.payment_status] || `<span class="badge bg-secondary">${reg.payment_status || 'មិនទាន់បង់'}</span>`;
            const amt = parseFloat(reg.final_amount || 0);
            const amountStr = amt > 0 ? ` ($${amt.toFixed(2)})` : '';
            document.getElementById('mPayment').innerHTML = pBadge + amountStr;
        }

        document.getElementById('mDate').innerText = reg.created_at || '-';

        // 5. Action Buttons:
        // Only show Approve/Reject when the registration actually needs approval (pending/waitlisted)!
        const isPending = ['pending', 'pending_approval', 'pending_verification', 'waitlisted'].includes(reg.status);
        const actionsGroup = document.getElementById('modalActionsGroup');
        if (isPending) {
            actionsGroup.classList.remove('d-none');
            actionsGroup.classList.add('d-flex');
            document.getElementById('modalApproveBtn').onclick = () => {
                detailModalInstance.hide();
                approveReg(reg.id);
            };
            document.getElementById('modalRejectBtn').onclick = () => {
                detailModalInstance.hide();
                rejectReg(reg.id);
            };
        } else {
            // Already confirmed, attended, cancelled, etc. -> In this condition, HIDE approval buttons!
            actionsGroup.classList.remove('d-flex');
            actionsGroup.classList.add('d-none');
        }

        const modalEl = document.getElementById('viewDetailsModal');
        detailModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        detailModalInstance.show();
    }

    // Toggle required checkbox based on show checkbox
    function toggleReq(fieldName) {
        const showChk = document.getElementById('chk_show_' + fieldName);
        const reqChk = document.getElementById('chk_req_' + fieldName);
        if (!showChk || !reqChk) return;
        if (!showChk.checked) {
            reqChk.checked = false;
            reqChk.disabled = true;
        } else {
            reqChk.disabled = false;
        }
    }

    // Programmatically open form fields modal
    function openFormFieldsModal() {
        const modalEl = document.getElementById('formFieldsModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        }
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Copy link helper with smooth animation feedback
    function copyLinkWithFeedback(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        const textToCopy = input.value;
        const button = btn || (window.event ? window.event.currentTarget : null);

        const doCopy = () => {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(textToCopy);
            } else {
                document.execCommand('copy');
                return Promise.resolve();
            }
        };

        doCopy().then(() => {
            if (button) {
                const icon = button.querySelector('i');
                const origIconClass = icon ? icon.className : 'bi bi-clipboard me-1';
                const origHtml = button.innerHTML;
                
                button.classList.add('btn-success');
                button.innerHTML = '<i class="bi bi-check2 me-1" style="transform: scale(1.2); transition: transform 0.15s ease;"></i> បានចម្លង!';

                setTimeout(() => {
                    button.innerHTML = origHtml;
                    button.classList.remove('btn-success');
                }, 1200);
            }
        }).catch(e => console.error('Copy failed:', e));
    }
</script>
