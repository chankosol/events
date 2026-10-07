<?php
// views/business/delegations/index.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard',               'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops',               'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'ប្រតិភូ / កូតា', 'url' => null,                             'icon' => 'geo-alt-fill'],
];
?>
<div class="container-fluid py-4">

    <!-- Page Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-gray-800 mb-1">
                <i class="bi bi-geo-alt-fill text-primary me-2"></i>គ្រប់គ្រងកូតា និងប្រតិភូ ២៥ រាជធានី-ខេត្ត
            </h1>
            <p class="text-muted mb-0">សិក្ខាសាលា៖ <strong><?= htmlspecialchars($workshop['name']) ?></strong></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#delegationFormFieldsModal">
                <i class="bi bi-sliders me-1"></i> កំណត់ទម្រង់ប្រតិភូ
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#autoGenerateModal">
                <i class="bi bi-magic me-1"></i> បង្កើតកូតា ២៥ ខេត្តស្វ័យប្រវត្តិ
            </button>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addDelegationModal">
                <i class="bi bi-plus-circle me-1"></i> បន្ថែមប្រតិភូ/ស្ថាប័នថ្មី
            </button>
            <button type="button" class="btn btn-warning text-dark fw-medium" data-bs-toggle="modal" data-bs-target="#substituteModal">
                <i class="bi bi-arrow-left-right me-1"></i> ជំនួសសមាជិក (Substitute)
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($success = Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error = Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-primary mb-1">ចំនួនប្រតិភូ/ខេត្ត</div>
                    <div class="h4 fw-bold mb-0 text-gray-800"><?= number_format($stats['total_delegations']) ?> ខេត្ត</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-info mb-1">កូតាកៅអីសរុប</div>
                    <div class="h4 fw-bold mb-0 text-gray-800"><?= number_format($stats['total_quota']) ?> កៅអី</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-success mb-1">សមាជិកបានចុះឈ្មោះ</div>
                    <div class="h4 fw-bold mb-0 text-gray-800">
                        <?= number_format($stats['total_registered']) ?> / <?= number_format($stats['total_quota']) ?>
                        <small class="text-muted fs-6">(<?= $stats['total_quota'] > 0 ? round(($stats['total_registered'] / $stats['total_quota']) * 100) : 0 ?>%)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-warning mb-1">វត្តមានជាក់ស្តែង</div>
                    <div class="h4 fw-bold mb-0 text-gray-800"><?= number_format($stats['total_attended']) ?> នាក់</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="text-xs text-uppercase fw-bold text-danger mb-1">ថវិកាបានបើកផ្តល់</div>
                    <div class="h4 fw-bold mb-0 text-gray-800">$<?= number_format((float)$stats['disbursed_amount'], 2) ?> <small class="text-muted fs-6">(<?= $stats['total_disbursed'] ?> នាក់)</small></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delegations Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-primary mb-0">
                <i class="bi bi-list-check me-2"></i>បញ្ជីប្រតិភូ និងកូតាខេត្តនីមួយៗ
            </h5>
            <span class="badge bg-light text-dark border">សរុប <?= count($delegations) ?> ប្រតិភូ</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>រាជធានី-ខេត្ត / ស្ថាប័ន</th>
                            <th>ប្រធានប្រតិភូ / ទំនាក់ទំនង</th>
                            <th class="text-center">កូតា</th>
                            <th class="text-center">បានចុះឈ្មោះ</th>
                            <th class="text-center">វត្តមាន</th>
                            <th class="text-center">បើកថវិកា</th>
                            <th>តំណភ្ជាប់ស្វ័យសេវា (Portal Link)</th>
                            <th class="text-end">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($delegations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                    មិនទាន់មានកូតាប្រតិភូនៅឡើយទេ។ សូមចុចប៊ូតុង <strong>«បង្កើតកូតា ២៥ ខេត្តស្វ័យប្រវត្តិ»</strong> ខាងលើដើម្បីចាប់ផ្តើម!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($delegations as $d): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($d['province']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($d['organization'] ?: 'ប្រតិភូ ' . $d['province']) ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['head_name'])): ?>
                                            <div class="fw-medium text-dark"><i class="bi bi-person me-1"></i><?= htmlspecialchars($d['head_name']) ?></div>
                                            <small class="text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($d['head_phone'] ?: '-') ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border">មិនទាន់ចាត់តាំង</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6 px-3 py-2"><?= $d['quota_seats'] ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $regPct = $d['quota_seats'] > 0 ? round(($d['registered_count'] / $d['quota_seats']) * 100) : 0;
                                            $regBadge = $d['registered_count'] >= $d['quota_seats'] ? 'bg-success' : 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $regBadge ?> fs-6"><?= $d['registered_count'] ?> / <?= $d['quota_seats'] ?></span>
                                        <div class="progress mt-1" style="height: 4px; width: 60px; margin: 0 auto;">
                                            <div class="progress-bar <?= $d['registered_count'] >= $d['quota_seats'] ? 'bg-success' : 'bg-info' ?>" style="width: <?= min(100, $regPct) ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fs-6"><?= (int)$d['attended_count'] ?> នាក់</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fs-6"><?= (int)$d['disbursed_count'] ?> នាក់</span>
                                    </td>
                                    <td>
                                        <?php $portalUrl = shareableUrl('/delegation/' . $d['delegation_token']); ?>
                                        <div class="input-group input-group-sm" style="max-width: 250px;">
                                            <input type="text" class="form-control" value="<?= $portalUrl ?>" id="portal_link_<?= $d['id'] ?>" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyPortalLink('portal_link_<?= $d['id'] ?>', this)" title="ចម្លងតំណភ្ជាប់">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <a href="<?= $portalUrl ?>" target="_blank" class="btn btn-outline-primary" title="បើកមើល">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                ជម្រើស
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <a class="dropdown-item" href="<?= APP_URL ?>/delegation/<?= $d['delegation_token'] ?>" target="_blank">
                                                        <i class="bi bi-eye me-2 text-primary"></i>មើលទំព័រប្រតិភូ
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal_<?= $d['id'] ?>">
                                                        <i class="bi bi-pencil me-2 text-info"></i>កែសម្រួលព័ត៌មាន
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/<?= $d['id'] ?>/delete" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបប្រតិភូនេះមែនទេ?')">
                                                        <?= csrfField() ?>
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i>លុបចេញ
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade text-start" id="editModal_<?= $d['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/<?= $d['id'] ?>/update">
                                                        <?= csrfField() ?>
                                                        <div class="modal-header">
                                                            <h5 class="modal-title fw-bold">កែសម្រួលប្រតិភូ៖ <?= htmlspecialchars($d['province']) ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">ឈ្មោះអង្គភាព/មន្ទីរ</label>
                                                                <input type="text" name="organization" class="form-control" value="<?= htmlspecialchars($d['organization']) ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">ឈ្មោះប្រធានប្រតិភូ</label>
                                                                <input type="text" name="head_name" class="form-control" value="<?= htmlspecialchars($d['head_name'] ?? '') ?>" placeholder="ឧ. លោក សុខ ចិន្តា">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">លេខទូរស័ព្ទប្រធាន</label>
                                                                <input type="text" name="head_phone" class="form-control" value="<?= htmlspecialchars($d['head_phone'] ?? '') ?>" placeholder="012 xxx xxx">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">អ៊ីមែលប្រធាន</label>
                                                                <input type="email" name="head_email" class="form-control" value="<?= htmlspecialchars($d['head_email'] ?? '') ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">កូតាកៅអីអនុញ្ញាត (Seats)</label>
                                                                <input type="number" name="quota_seats" class="form-control" value="<?= $d['quota_seats'] ?>" min="1" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                                                            <button type="submit" class="btn btn-primary">រក្សាទុកការកែប្រែ</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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

<!-- Modal: Auto-Generate 25 Provinces -->
<div class="modal fade" id="autoGenerateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/auto-generate">
                <?= csrfField() ?>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-magic me-2"></i>បង្កើតកូតា ២៥ រាជធានី-ខេត្តស្វ័យប្រវត្តិ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        ប្រព័ន្ធនឹងបង្កើតបញ្ជីប្រតិភូសម្រាប់រាជធានី-ខេត្តទាំង ២៥ នៃព្រះរាជាណាចក្រកម្ពុជាដោយស្វ័យប្រវត្តិ ព្រមទាំងផ្តល់នូវតំណភ្ជាប់ (Self-service Portal Link) សម្ងាត់រៀងៗខ្លួនសម្រាប់ប្រធានខេត្តបំពេញឈ្មោះសមាជិក។
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">កំណត់ចំនួនកៅអី (Quota) ក្នុង ១ ខេត្ត៖</label>
                        <div class="input-group">
                            <input type="number" name="quota_per_province" class="form-control" value="5" min="1" max="100" required>
                            <span class="input-group-text">កៅអី / ខេត្ត</span>
                        </div>
                        <small class="text-muted">ឧ. ២៥ ខេត្ត x ៥ កៅអី = សរុប ១២៥ នាក់</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-check-circle me-1"></i> បង្កើតទាំង ២៥ ខេត្តភ្លាមៗ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Single Custom Delegation -->
<div class="modal fade" id="addDelegationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/store">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>បន្ថែមប្រតិភូ/ស្ថាប័នថ្មី</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ជ្រើសរើស ឬបញ្ចូលរាជធានី-ខេត្ត <span class="text-danger">*</span></label>
                        <select name="province" class="form-select" required>
                            <option value="">-- ជ្រើសរើសខេត្ត --</option>
                            <?php foreach ($provinces as $p): ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះអង្គភាព/មន្ទីរ</label>
                        <input type="text" name="organization" class="form-control" placeholder="ឧ. មន្ទីរអប់រំ យុវជន និងកីឡា">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះប្រធានប្រតិភូ</label>
                        <input type="text" name="head_name" class="form-control" placeholder="ឧ. លោក សុខ ចិន្តា">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                        <input type="text" name="head_phone" class="form-control" placeholder="012 xxx xxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">កូតាកៅអីអនុញ្ញាត</label>
                        <input type="number" name="quota_seats" class="form-control" value="5" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">បង្កើតប្រតិភូ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Quick On-Site Member Substitution -->
<div class="modal fade" id="substituteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/substitute">
                <?= csrfField() ?>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-arrow-left-right me-2"></i>ជំនួសសមាជិកនៅថ្ងៃកម្មវិធី (On-Site Member Substitution)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i> ប្រើប្រាស់មុខងារនេះនៅពេលសមាជិកចាស់ក្នុងបញ្ជីអវត្តមាន ហើយមានសមាជិកថ្មីមកចូលរួមជំនួស។ ប្រព័ន្ធនឹងលុបចោលសមាជិកចាស់ និងបង្កើត QR Code ថ្មីជូនសមាជិកថ្មីភ្លាមៗក្នុងរយៈពេលតែ ១៥ វិនាទី!
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ជ្រើសរើសសមាជិកចាស់ដែលអវត្តមាន <span class="text-danger">*</span></label>
                        <select name="old_registration_id" class="form-select select2" required>
                            <option value="">-- ជ្រើសរើសឈ្មោះសមាជិកអវត្តមាន --</option>
                            <?php foreach ($members as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['delegation_province'] ?: $m['province'] ?: 'ទូទៅ') ?>) - កូដ: <?= $m['registration_code'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-2 mt-4">ព័ត៌មានសមាជិកថ្មីដែលមកជំនួស</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ឈ្មោះសមាជិកថ្មី <span class="text-danger">*</span></label>
                            <input type="text" name="new_name" class="form-control" placeholder="ឧ. លោក សេង វណ្ណា" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                            <input type="text" name="new_phone" class="form-control" placeholder="012 xxx xxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ភេទ</label>
                            <select name="new_gender" class="form-select">
                                <option value="male">ប្រុស</option>
                                <option value="female">ស្រី</option>
                                <option value="other">ផ្សេងទៀត</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">លេខអត្តសញ្ញាណប័ណ្ណ (បើមាន សម្រាប់បើកថវិកា)</label>
                            <input type="text" name="new_id_card" class="form-control" placeholder="ឧ. 010203040">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ឈ្មោះធនាគារ (បើមាន)</label>
                            <input type="text" name="new_bank_name" class="form-control" placeholder="Bakong / ABA / ACLEDA">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">លេខគណនីធនាគារ (បើមាន)</label>
                            <input type="text" name="new_bank_account" class="form-control" placeholder="000 123 456">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">មូលហេតុនៃការជំនួស</label>
                            <input type="text" name="reason" class="form-control" value="មកជំនួសនៅថ្ងៃកម្មវិធីផ្ទាល់ (On-site delegation replacement)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-check-circle me-1"></i> បញ្ជាក់ការផ្លាស់ប្តូរ & បង្កើត QR ថ្មី
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Customize Delegation Form Fields -->
<div class="modal fade" id="delegationFormFieldsModal" tabindex="-1" aria-labelledby="delegationFormFieldsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/delegations/form-fields" method="POST">
                <?= csrfField() ?>
                <div class="modal-header bg-light py-3">
                    <h5 class="modal-title fw-bold text-dark mb-0" id="delegationFormFieldsModalLabel">
                        <i class="bi bi-sliders text-primary me-2"></i> កំណត់ទម្រង់ប្រតិភូ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="alert alert-light border py-2 px-3 small mb-3 text-secondary rounded-3">
                        💡 គន្លឹះ៖ អាចកំណត់ព័ត៌មានដែលត្រូវបង្ហាញ ឬចាំបាច់បំពេញលើទម្រង់ចាត់តាំងសមាជិកប្រតិភូ (តំណភ្ជាប់ Portal)។
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
                                        <span class="fw-bold text-dark"><i class="bi bi-person me-2 text-primary"></i> ឈ្មោះសមាជិក</span>
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
                                            <input class="form-check-input" type="checkbox" name="show_phone" id="del_show_phone" value="1" <?= !empty($delegationFormFields['show_phone']) ? 'checked' : '' ?> onchange="toggleDelReq('phone')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_phone" id="del_req_phone" value="1" <?= !empty($delegationFormFields['require_phone']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_phone']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_phone">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 3. Gender -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-gender-ambiguous me-2 text-warning"></i> ភេទ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_gender" id="del_show_gender" value="1" <?= !empty($delegationFormFields['show_gender']) ? 'checked' : '' ?> onchange="toggleDelReq('gender')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_gender" id="del_req_gender" value="1" <?= !empty($delegationFormFields['require_gender']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_gender']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_gender">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 4. Position -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-briefcase me-2 text-secondary"></i> តួនាទី / មុខតំណែង</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_position" id="del_show_position" value="1" <?= !empty($delegationFormFields['show_position']) ? 'checked' : '' ?> onchange="toggleDelReq('position')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_position" id="del_req_position" value="1" <?= !empty($delegationFormFields['require_position']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_position']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_position">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 5. Organization -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i> អង្គភាព / ស្ថាប័ន</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_organization" id="del_show_organization" value="1" <?= !empty($delegationFormFields['show_organization']) ? 'checked' : '' ?> onchange="toggleDelReq('organization')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_organization" id="del_req_organization" value="1" <?= !empty($delegationFormFields['require_organization']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_organization']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_organization">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 6. ID Card -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-card-heading me-2 text-danger"></i> លេខអត្តសញ្ញាណប័ណ្ណ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_id_card" id="del_show_id_card" value="1" <?= !empty($delegationFormFields['show_id_card']) ? 'checked' : '' ?> onchange="toggleDelReq('id_card')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_id_card" id="del_req_id_card" value="1" <?= !empty($delegationFormFields['require_id_card']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_id_card']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_id_card">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 7. Bank Name -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-bank me-2 text-info"></i> ឈ្មោះធនាគារ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_bank_name" id="del_show_bank_name" value="1" <?= !empty($delegationFormFields['show_bank_name']) ? 'checked' : '' ?> onchange="toggleDelReq('bank_name')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_bank_name" id="del_req_bank_name" value="1" <?= !empty($delegationFormFields['require_bank_name']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_bank_name']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_bank_name">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 8. Bank Account -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-credit-card me-2 text-success"></i> លេខគណនីធនាគារ</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_bank_account" id="del_show_bank_account" value="1" <?= !empty($delegationFormFields['show_bank_account']) ? 'checked' : '' ?> onchange="toggleDelReq('bank_account')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_bank_account" id="del_req_bank_account" value="1" <?= !empty($delegationFormFields['require_bank_account']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_bank_account']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_bank_account">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 9. Email -->
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="bi bi-envelope me-2 text-info"></i> អ៊ីមែល</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="show_email" id="del_show_email" value="1" <?= !empty($delegationFormFields['show_email']) ? 'checked' : '' ?> onchange="toggleDelReq('email')">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="require_email" id="del_req_email" value="1" <?= !empty($delegationFormFields['require_email']) ? 'checked' : '' ?> <?= empty($delegationFormFields['show_email']) ? 'disabled' : '' ?>>
                                            <label class="form-check-label small" for="del_req_email">ចាំបាច់</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> រក្សាទុកការកំណត់
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDelReq(field) {
    const showChk = document.getElementById('del_show_' + field);
    const reqChk = document.getElementById('del_req_' + field);
    if (!showChk || !reqChk) return;
    if (!showChk.checked) {
        reqChk.checked = false;
        reqChk.disabled = true;
    } else {
        reqChk.disabled = false;
    }
}

function copyPortalLink(elementId, btn) {
    const input = document.getElementById(elementId);
    if (!input) return;
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    const textToCopy = input.value;
    const button = btn || (window.event ? window.event.currentTarget : null) || document.activeElement;
    
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
            const originalIconClass = icon ? icon.className : 'bi bi-clipboard';
            const originalBtnClass = button.className;
            
            // Visual feedback: green button & checkmark icon animation
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success', 'text-white');
            if (icon) {
                icon.className = 'bi bi-check2 text-white';
                icon.style.transform = 'scale(1.3)';
                icon.style.transition = 'transform 0.15s ease-in-out';
            }
            
            // Revert after 1.2 seconds
            setTimeout(() => {
                if (icon) {
                    icon.className = originalIconClass;
                    icon.style.transform = 'scale(1)';
                }
                button.className = originalBtnClass;
            }, 1200);
        }
    }).catch(err => {
        console.error('Copy failed:', err);
    });
}
</script>
