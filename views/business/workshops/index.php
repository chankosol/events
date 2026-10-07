<?php
// views/business/workshops/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">បញ្ជីសិក្ខាសាលា</h2>
        <p class="text-muted mb-0">គ្រប់គ្រងសិក្ខាសាលា វគ្គបណ្តុះបណ្តាល និងព្រឹត្តិការណ៍ទាំងអស់របស់ស្ថាប័នអ្នក។</p>
    </div>
    <div>
        <a href="<?php echo APP_URL; ?>/workshops/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> បង្កើតសិក្ខាសាលាថ្មី
        </a>
    </div>
</div>

<!-- Filter & Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo APP_URL; ?>/workshops" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះសិក្ខាសាលា ឬវាគ្មិន/គ្រូ..." value="<?php echo e($_GET['q'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="draft" <?php echo (($_GET['status'] ?? '') === 'draft') ? 'selected' : ''; ?>>សេចក្តីព្រាង (Draft)</option>
                    <option value="pending_payment" <?php echo (($_GET['status'] ?? '') === 'pending_payment') ? 'selected' : ''; ?>>រង់ចាំបង់ថ្លៃប្រព័ន្ធ</option>
                    <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>សកម្ម (ត្រៀមរួចរាល់)</option>
                    <option value="registration_open" <?php echo (($_GET['status'] ?? '') === 'registration_open') ? 'selected' : ''; ?>>កំពុងបើកចុះឈ្មោះ</option>
                    <option value="in_progress" <?php echo (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : ''; ?>>កំពុងដំណើរការ</option>
                    <option value="completed" <?php echo (($_GET['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>បានបញ្ចប់</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">ស្វែងរក</button>
            </div>
            <div class="col-md-2 text-end">
                <a href="<?php echo APP_URL; ?>/workshops" class="btn btn-outline-secondary w-100">កំណត់ឡើងវិញ</a>
            </div>
        </form>
    </div>
</div>

<!-- Workshops Table -->
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">សិក្ខាសាលា</th>
                    <th scope="col">កាលបរិច្ឆេទ & ទីកន្លែង</th>
                    <th scope="col">ចំណុះ</th>
                    <th scope="col">ការចុះឈ្មោះ</th>
                    <th scope="col">ថ្លៃប្រព័ន្ធ</th>
                    <th scope="col">ស្ថានភាព</th>
                    <th scope="col" class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workshops)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                            មិនទាន់មានសិក្ខាសាលានៅឡើយទេ។ សូមចុច <strong>បង្កើតសិក្ខាសាលាថ្មី</strong> ដើម្បីចាប់ផ្តើម!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workshops as $w): ?>
                        <tr>
                            <td>
                                <a href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>" class="fw-bold text-dark text-decoration-none">
                                    <?php echo e($w['name']); ?>
                                </a>
                                <div class="text-muted small">
                                    <?php echo e(!empty($w['trainer_title']) ? $w['trainer_title'] : 'គ្រូបណ្តុះបណ្តាល'); ?>: <?php echo e($w['trainer_name'] ?: 'មិនបានបញ្ជាក់'); ?> | 
                                    ទម្រង់: <span class="badge bg-light text-dark border"><?php echo e(ucfirst($w['payment_mode'])); ?></span>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-calendar-event me-1"></i><?php echo formatDate($w['start_date']); ?></div>
                                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?php echo e($w['venue'] ?: 'តាមអនឡាញ / មិនទាន់កំណត់'); ?></div>
                            </td>
                            <td>
                                <span class="fw-bold"><?php echo number_format($w['capacity']); ?></span> នាក់
                            </td>
                            <td>
                                <div><strong><?php echo $w['confirmed_count']; ?></strong> / <?php echo $w['registration_count']; ?></div>
                                <div class="text-muted small">បញ្ជាក់ / សរុប</div>
                            </td>
                            <td>
                                <?php if ($w['billing_status'] === 'paid'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> បានបង់ ($<?php echo number_format($w['platform_fee'], 2); ?>)
                                    </span>
                                <?php elseif ($w['billing_status'] === 'pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-clock me-1"></i> កំពុងពិនិត្យ ($<?php echo number_format($w['platform_fee'], 2); ?>)
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>/activate" class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-none">
                                        <i class="bi bi-exclamation-circle me-1"></i> បង់ថ្លៃ ($<?php echo number_format($w['platform_fee'], 2); ?>)
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo statusBadge($w['status']); ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <?php if ($w['billing_status'] !== 'paid'): ?>
                                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>/activate" class="btn btn-sm btn-danger shadow-sm py-1 px-2" title="បង់ប្រាក់បើកដំណើរការ">
                                            <i class="bi bi-qr-code me-1"></i> បង់ប្រាក់
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>" class="btn btn-sm btn-outline-primary" title="មើល & គ្រប់គ្រង">
                                        <i class="bi bi-gear me-1"></i> គ្រប់គ្រង
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="ជម្រើសបន្ថែម">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border">
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>">
                                                    <i class="bi bi-eye text-primary me-2"></i> មើល & គ្រប់គ្រង
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>/edit">
                                                    <i class="bi bi-pencil text-secondary me-2"></i> កែសម្រួលព័ត៌មាន
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/workshops/<?php echo $w['id']; ?>/activate">
                                                    <i class="bi bi-credit-card text-success me-2"></i> បង់ថ្លៃប្រព័ន្ធ
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/live/<?php echo $w['id']; ?>">
                                                    <i class="bi bi-broadcast text-danger me-2"></i> មជ្ឈមណ្ឌលផ្ទាល់ (Live)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/checkin/<?php echo $w['id']; ?>">
                                                    <i class="bi bi-qr-code-scan text-success me-2"></i> ស្កេនវត្តមាន (Check-in)
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/reports/workshop/<?php echo $w['id']; ?>">
                                                    <i class="bi bi-bar-chart text-info me-2"></i> របាយការណ៍សិក្ខាសាលា
                                                </a>
                                            </li>
                                            <?php if ($w['status'] === 'registration_open'): ?>
                                                <li>
                                                    <a class="dropdown-item py-2" href="<?php echo APP_URL; ?>/event/<?php echo $w['slug']; ?>" target="_blank">
                                                        <i class="bi bi-box-arrow-up-right text-dark me-2"></i> ទំព័រសាធារណៈ
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <span class="text-muted small">បង្ហាញទំព័រទី <?php echo $page; ?> នៃ <?php echo $totalPages; ?></span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $p; ?>&status=<?php echo urlencode($_GET['status'] ?? ''); ?>&q=<?php echo urlencode($_GET['q'] ?? ''); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
