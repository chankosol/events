<?php
// views/business/allowances/index.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',        'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា',      'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'ថវិកា / ប្រាក់ឧបត្ថម្ភ', 'url' => null,                              'icon' => 'cash-stack'],
];
?>
<div class="container-fluid py-4">


    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-gray-800 mb-1">
                <i class="bi bi-cash-stack text-success me-2"></i>ការគ្រប់គ្រងកញ្ចប់ថវិកា និងប្រាក់ឧបត្ថម្ភ
            </h1>
            <p class="text-muted mb-0">សិក្ខាសាលា៖ <strong><?= htmlspecialchars($workshop['name']) ?></strong></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/allowances/desk" class="btn btn-success fw-bold">
                <i class="bi bi-upc-scan me-1"></i> តុស្កេនបើកប្រាក់ (Payout Desk)
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#configModal">
                <i class="bi bi-gear me-1"></i> កំណត់លក្ខខណ្ឌបើកប្រាក់
            </button>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/allowances/sheet" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> បោះពុម្ពបញ្ជីសវនកម្ម (Audit Sheet)
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($success = Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-primary mb-1">ថវិកាសរុបដែលបានត្រៀម</div>
                    <div class="h4 fw-bold mb-0 text-gray-800">$<?= number_format((float)$stats['total_budget'], 2) ?></div>
                    <small class="text-muted">សម្រាប់សិក្ខាកាមមានសិទ្ធិ <?= $stats['total_eligible'] ?> នាក់</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-success mb-1">ថវិកាបានបើកផ្តល់ជាក់ស្តែង</div>
                    <div class="h4 fw-bold mb-0 text-success">$<?= number_format((float)$stats['total_disbursed_amount'], 2) ?></div>
                    <small class="text-success fw-medium"><i class="bi bi-check-all"></i> បានបើកជូន <?= $stats['total_disbursed'] ?> នាក់</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-warning mb-1">ថវិកានៅសល់មិនទាន់បើក</div>
                    <div class="h4 fw-bold mb-0 text-warning">$<?= number_format((float)$stats['remaining_balance'], 2) ?></div>
                    <small class="text-muted">នៅសល់ <?= $stats['total_pending'] ?> នាក់</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-info mb-1">អត្រាបើកប្រាក់ (Disbursement Rate)</div>
                    <div class="h4 fw-bold mb-0 text-gray-800">
                        <?= $stats['total_eligible'] > 0 ? round(($stats['total_disbursed'] / $stats['total_eligible']) * 100) : 0 ?>%
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= $stats['total_eligible'] > 0 ? round(($stats['total_disbursed'] / $stats['total_eligible']) * 100) : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendees & Disbursement Status Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold text-primary mb-0">
                <i class="bi bi-people-fill me-2"></i>បញ្ជីសិក្ខាកាម និងស្ថានភាពបើកថវិកា
            </h5>
            <div class="text-muted small">
                ប្រាក់ឧបត្ថម្ភ៖ <strong>$<?= number_format((float)$allowance['default_amount'], 2) ?> / នាក់</strong> 
                (តម្រូវការវត្តមាន៖ <strong><?= $allowance['min_attendance_percent'] ?>%</strong>)
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>ឈ្មោះសិក្ខាកាម</th>
                            <th>រាជធានី-ខេត្ត / ប្រតិភូ</th>
                            <th>អត្តសញ្ញាណប័ណ្ណ / គណនីធនាគារ</th>
                            <th class="text-center">អត្រាវត្តមាន</th>
                            <th class="text-center">សិទ្ធិបើកថវិកា</th>
                            <th class="text-center">ស្ថានភាពបើកប្រាក់</th>
                            <th>ព័ត៌មានប័ណ្ណបើកប្រាក់ (Voucher)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendees)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    មិនទាន់មានសិក្ខាកាមបានចុះឈ្មោះក្នុងសិក្ខាសាលានេះនៅឡើយទេ។
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attendees as $att): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2 flex-shrink-0">
                                                <?= userAvatarHtml(['name' => $att['name'], 'profile_photo' => $att['photo']], 34) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($att['name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($att['phone'] ?: 'គ្មានលេខ') ?> &bull; កូដ: <?= $att['registration_code'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= htmlspecialchars($att['delegation_province'] ?: $att['province'] ?: 'ទូទៅ') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($att['delegation_org'] ?: 'សមាជិក') ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($att['id_card_number'])): ?>
                                            <div><i class="bi bi-card-text me-1"></i><?= htmlspecialchars($att['id_card_number']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($att['bank_account_number'])): ?>
                                            <small class="text-primary"><i class="bi bi-bank me-1"></i><?= htmlspecialchars($att['bank_name'] ?: 'ធនាគារ') ?>: <?= htmlspecialchars($att['bank_account_number']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted">សាច់ប្រាក់សុទ្ធ (Cash)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $att['attendance_rate'] >= 80 ? 'bg-success' : 'bg-warning text-dark' ?> fs-6">
                                            <?= $att['attendance_rate'] ?>%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($att['is_eligible']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                <i class="bi bi-check-circle me-1"></i>គ្រប់លក្ខខណ្ឌ
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                                <i class="bi bi-x-circle me-1"></i>ខ្វះវត្តមាន
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($att['disbursement_id'])): ?>
                                            <span class="badge bg-success fs-6 py-2 px-3">
                                                <i class="bi bi-check2-circle me-1"></i>បានបើករួច
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark fs-6 py-2 px-3">
                                                មិនទាន់បើក
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($att['disbursement_id'])): ?>
                                            <div class="fw-bold font-monospace text-primary"><?= $att['receipt_voucher_no'] ?></div>
                                            <small class="text-muted d-block">
                                                ទឹកប្រាក់៖ <strong>$<?= number_format((float)$att['disbursed_amount'], 2) ?></strong>
                                                &bull; <?= date('H:i, d/m', strtotime($att['disbursed_at'])) ?>
                                            </small>
                                            <small class="text-secondary">បើកដោយ៖ <?= htmlspecialchars($att['disbursed_by_name'] ?? 'Staff') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Config Allowance Rules -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/allowances/config">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-gear me-2"></i>កំណត់លក្ខខណ្ឌបើកប្រាក់ឧបត្ថម្ភ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះប្រភេទប្រាក់ឧបត្ថម្ភ</label>
                        <input type="text" name="allowance_name" class="form-control" value="<?= htmlspecialchars($allowance['allowance_name']) ?>" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">ចំនួនទឹកប្រាក់ក្នុង ១ នាក់</label>
                            <input type="number" step="0.01" name="default_amount" class="form-control" value="<?= $allowance['default_amount'] ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">រូបិយប័ណ្ណ</label>
                            <select name="currency" class="form-select">
                                <option value="USD" <?= ($allowance['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                <option value="KHR" <?= ($allowance['currency'] ?? '') === 'KHR' ? 'selected' : '' ?>>KHR (៛)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">អត្រាវត្តមានអប្បបរមាដើម្បីមានសិទ្ធិបើក (%)</label>
                        <div class="input-group">
                            <input type="number" name="min_attendance_percent" class="form-control" value="<?= $allowance['min_attendance_percent'] ?>" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">សិក្ខាកាមត្រូវតែមានវត្តមានស្មើ ឬលើសពីភាគរយនេះ ទើបប្រព័ន្ធអនុញ្ញាតឱ្យបើកប្រាក់។</small>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="allow_delegation_head_claim" id="allowHeadClaim" value="1" <?= $allowance['allow_delegation_head_claim'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="allowHeadClaim">អនុញ្ញាតឱ្យប្រធានប្រតិភូខេត្តបើកជំនួសសមាជិក (Group Claim)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុកការកំណត់</button>
                </div>
            </form>
        </div>
    </div>
</div>
