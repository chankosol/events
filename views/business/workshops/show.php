<?php
// views/business/workshops/show.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 50, '…'), 'url' => null, 'icon' => null],
];
?>


<!-- Top Header Card with Integrated Compact Stats Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <!-- Header Content: Title + Meta + Action Buttons -->
    <div class="p-4 text-white">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-start align-items-xl-center gap-3">
            <div>
                <h1 class="h4 fw-bold mb-2 text-white"><?php echo e($workshop['name']); ?></h1>
                <div class="d-flex flex-wrap gap-3 text-light opacity-75 small mb-2.5">
                    <span><i class="bi bi-calendar-event me-1.5 text-info"></i><?php echo formatDate($workshop['start_date']); ?><?php if (!empty($workshop['start_time'])): ?> (ម៉ោង <?php echo date('h:i A', strtotime($workshop['start_time'])); ?>)<?php endif; ?></span>
                    <span><i class="bi bi-geo-alt me-1.5 text-danger"></i><?php echo e($workshop['venue'] ?: 'អនឡាញ / មិនទាន់កំណត់'); ?></span>
                    <span><i class="bi bi-person-badge me-1.5 text-warning"></i><?php echo e(!empty($workshop['trainer_title']) ? $workshop['trainer_title'] : 'គ្រូ'); ?>: <strong><?php echo e($workshop['trainer_name'] ?: 'មិនទាន់កំណត់'); ?></strong></span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php echo statusBadge($workshop['status']); ?>
                    <?php if (!empty($workshop['payment_mode'])): ?>
                        <span class="badge bg-secondary bg-opacity-50 text-white border border-light border-opacity-25 px-2.5 py-1 small">
                            <i class="bi bi-tag me-1"></i><?php echo ucfirst($workshop['payment_mode']); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($billing && $billing['payment_status'] === 'paid'): ?>
                        <span class="badge bg-success bg-opacity-25 text-success-light border border-success border-opacity-50 px-2.5 py-1 small" style="color: #86efac !important;">
                            <i class="bi bi-patch-check-fill me-1"></i>ប្រព័ន្ធដំណើរការ ($<?php echo number_format($billing['platform_fee'], 2); ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-2.5 py-1 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>រង់ចាំការបង់ថ្លៃប្រព័ន្ធ
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons on Header (Pure Khmer labels, generous 12px gap) -->
            <div class="d-flex align-items-center flex-wrap flex-shrink-0" style="gap: 12px;">
                <?php if ($billing && $billing['payment_status'] === 'paid'): ?>
                    <?php if ($workshop['status'] === 'active'): ?>
                        <form method="POST" action="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/publish" class="d-inline m-0 p-0">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="btn btn-sm btn-success fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-megaphone-fill"></i> <span>បើកចុះឈ្មោះ</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/live/<?php echo $workshop['id']; ?>" class="btn btn-sm btn-danger fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-broadcast"></i> <span>ផ្សាយផ្ទាល់</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="btn btn-sm btn-warning fw-bold px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-credit-card"></i> <span>បង់ថ្លៃប្រព័ន្ធ</span>
                    </a>
                <?php endif; ?>

                <!-- 1. Public Event Link & QR Modal Trigger -->
                <button type="button" class="btn btn-sm btn-outline-light px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#qrPublicModal" title="តំណភ្ជាប់សាធារណៈ & QR">
                    <i class="bi bi-qr-code"></i> <span>QR & តំណភ្ជាប់</span>
                </button>

                <!-- 2. Daily Scans Configuration Modal Trigger -->
                <button type="button" class="btn btn-sm btn-outline-light px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#dailyScansModal" title="កំណត់ចំនួនដងស្កេនវត្តមាន">
                    <i class="bi bi-clock-history"></i> <span id="headerDailyScansBadge">ស្កេន <?php echo (int)($workshop['daily_scans_required'] ?? 1); ?>ដង</span>
                </button>

                <!-- 3. Ticket Types Modal Trigger -->
                <button type="button" class="btn btn-sm btn-outline-light px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#ticketsModal" title="ប្រភេទសំបុត្រ">
                    <i class="bi bi-ticket-perforated"></i> <span>សំបុត្រ (<?php echo count($tickets); ?>)</span>
                </button>

                <!-- 4. Telegram Group Modal Trigger -->
                <button type="button" class="btn btn-sm btn-outline-info text-white border-info px-2.5 d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#telegramReportModal" title="Telegram Group">
                    <i class="bi bi-telegram"></i> <span>Telegram</span>
                </button>

                <!-- 5. Edit Workshop Details -->
                <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/edit" class="btn btn-sm btn-outline-light px-2.5 d-inline-flex align-items-center gap-1" title="កែសម្រួលព័ត៌មានសិក្ខាសាលា">
                    <i class="bi bi-pencil"></i> <span>កែសម្រួល</span>
                </a>

                <!-- 6. Duplicate Workshop -->
                <button type="button" class="btn btn-sm btn-outline-light px-2" onclick="duplicateWorkshop(<?php echo $workshop['id']; ?>)" title="ចម្លងទម្រង់សិក្ខាសាលា">
                    <i class="bi bi-copy"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Integrated Compact Stats Bar (Pure Khmer labels, no English text) -->
    <div class="py-3 px-4 border-top" style="background: rgba(0, 0, 0, 0.25); border-color: rgba(255, 255, 255, 0.1) !important;">
        <div class="row align-items-center g-3 text-white">
            <!-- 1. Capacity -->
            <div class="col-6 col-md-3 border-end border-white border-opacity-10 py-1">
                <div class="text-white-50" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;">ចំណុះសិក្ខាកាម</div>
                <div class="fw-bold fs-5 lh-1 mt-1.5 text-white">
                    <?php echo (int)$stats['confirmed']; ?> <span class="fs-6 fw-normal text-white-50">/ <?php echo number_format($workshop['capacity']); ?></span>
                    <?php $pct = min(100, round(($stats['confirmed'] / max(1, $workshop['capacity'])) * 100)); ?>
                    <span class="badge rounded-pill ms-1 fw-normal" style="font-size: 0.68rem; background: rgba(59, 130, 246, 0.35); color: #93c5fd;"><?php echo $pct; ?>%</span>
                </div>
            </div>

            <!-- 2. Paid -->
            <div class="col-6 col-md-3 border-end border-white border-opacity-10 py-1">
                <div class="text-white-50" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;">បានបញ្ជាក់ & បង់ប្រាក់</div>
                <div class="fw-bold fs-5 lh-1 mt-1.5" style="color: #86efac;">
                    <?php echo (int)$stats['paid']; ?> <span class="fs-6 fw-normal text-white-50">នាក់</span>
                    <span class="text-white-50 ms-1 fw-normal" style="font-size: 0.72rem;">(ចុះសរុប <?php echo (int)$stats['total_registrations']; ?>)</span>
                </div>
            </div>

            <!-- 3. Pending -->
            <div class="col-6 col-md-3 border-end border-white border-opacity-10 py-1">
                <div class="text-white-50" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;">រង់ចាំការទូទាត់</div>
                <div class="fw-bold fs-5 lh-1 mt-1.5" style="color: <?php echo (int)($stats['pending_payment'] ?? 0) > 0 ? '#fde047' : '#94a3b8'; ?>;">
                    <?php echo (int)($stats['pending_payment'] ?? 0); ?> <span class="fs-6 fw-normal text-white-50">នាក់</span>
                    <?php if ((int)($stats['pending_payment'] ?? 0) > 0): ?>
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/payments" class="badge bg-warning text-dark text-decoration-none ms-1" style="font-size: 0.68rem;">ពិនិត្យ</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4. Waitlist (No English text) -->
            <div class="col-6 col-md-3 py-1">
                <div class="text-white-50" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;">បញ្ជីរង់ចាំ</div>
                <div class="fw-bold fs-5 lh-1 mt-1.5" style="color: #67e8f9;">
                    <?php echo (int)($stats['waitlist'] ?? 0); ?> <span class="fs-6 fw-normal text-white-50">នាក់</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     CORE OPERATIONS HUB (Bigger Gap, Pure Khmer, High-Contrast)
     ========================================== -->
<style>
/* Section Container Panels to differentiate from background */
.section-panel-card {
    background: #f8fafc !important;
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 20px !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05) !important;
}
.section-panel-header {
    background: #ffffff !important;
    border-bottom: 1.5px solid #e2e8f0 !important;
    border-top-left-radius: 19px !important;
    border-top-right-radius: 19px !important;
}

/* App tile cards with distinct colored borders & soft shadows */
.app-tile-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.15rem 1.25rem;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    height: 100%;
    min-height: 88px;
    position: relative;
}
.app-tile-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px -4px rgba(37, 99, 235, 0.16);
}

/* 9 Specific Module Accent Borders & Hover Tints */
.app-tile-blue { border-color: #bfdbfe !important; }
.app-tile-blue:hover { border-color: #3b82f6 !important; background: #f0f7ff !important; }

.app-tile-green { border-color: #bbf7d0 !important; }
.app-tile-green:hover { border-color: #10b981 !important; background: #f0fdf4 !important; }

.app-tile-purple { border-color: #e9d5ff !important; }
.app-tile-purple:hover { border-color: #8b5cf6 !important; background: #faf5ff !important; }

.app-tile-cyan { border-color: #a5f3fc !important; }
.app-tile-cyan:hover { border-color: #06b6d4 !important; background: #ecfeff !important; }

.app-tile-red { border-color: #fecaca !important; }
.app-tile-red:hover { border-color: #ef4444 !important; background: #fef2f2 !important; }

.app-tile-teal { border-color: #99f6e4 !important; }
.app-tile-teal:hover { border-color: #059669 !important; background: #f0fdfa !important; }

.app-tile-amber { border-color: #fde68a !important; }
.app-tile-amber:hover { border-color: #f59e0b !important; background: #fffbeb !important; }

.app-tile-sky { border-color: #bae6fd !important; }
.app-tile-sky:hover { border-color: #0284c7 !important; background: #f0f9ff !important; }

.app-tile-slate { border-color: #cbd5e1 !important; }
.app-tile-slate:hover { border-color: #64748b !important; background: #f8fafc !important; }

.app-tile-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.app-tile-card-title {
    font-size: 0.94rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
}
.app-tile-card-sub {
    font-size: 0.74rem;
    color: #64748b;
}
.app-tile-card-badge {
    font-size: 0.73rem;
    font-weight: 600;
    padding: 2px 7.5px;
    border-radius: 50rem;
    display: inline-block;
}
.app-tile-arrow {
    font-size: 0.9rem;
    opacity: 0.6;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.app-tile-card:hover .app-tile-arrow {
    opacity: 1;
    transform: translate(2px, -2px);
}
.op-clean-filter {
    background: #f1f5f9;
    border-radius: 50rem;
    padding: 3px;
    display: inline-flex;
    gap: 3px;
}
.op-clean-btn {
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.76rem;
    padding: 4px 10px;
    border-radius: 50rem;
    transition: all 0.15s ease;
}
.op-clean-btn.active {
    background: #0f172a;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
</style>

<!-- Main Dashboard Grid: Left Operations Modules (col-lg-8) + Right Agenda (col-lg-4) -->
<div class="row g-4 mb-4">
    <!-- LEFT COLUMN: Operations Modules (col-lg-8) -->
    <div class="col-lg-8">
        <div class="card section-panel-card h-100 overflow-hidden">
            <!-- Section Header Bar with clean title & filter -->
            <div class="card-header section-panel-header py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-grid-fill"></i>
                    </span>
                    <span>ម៉ូឌុលប្រតិបត្តិការ</span>
                </h5>
                <div class="op-clean-filter">
                    <button type="button" class="op-clean-btn active" onclick="filterOperations('all', this)">ទាំងអស់ (៩)</button>
                    <button type="button" class="op-clean-btn" onclick="filterOperations('p1', this)">👥 សិក្ខាកាម</button>
                    <button type="button" class="op-clean-btn" onclick="filterOperations('p2', this)">⚡ ថ្ងៃកម្មវិធី</button>
                    <button type="button" class="op-clean-btn" onclick="filterOperations('p3', this)">📊 របាយការណ៍</button>
                </div>
            </div>

            <!-- Card Body with the 9 colored cards -->
            <div class="card-body p-3.5 p-xl-4">
                <?php $isUnpaid = (!$billing || $billing['payment_status'] !== 'paid'); ?>
                <div class="row g-3.5 gy-3.5" id="operationsGrid" style="--bs-gutter-x: 1.15rem; --bs-gutter-y: 1.15rem;">

                    <!-- 1. Registrations (Blue Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p1">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/registrations" class="app-tile-card app-tile-blue">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">ការចុះឈ្មោះ</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-primary"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <?php echo (int)$stats['total_registrations']; ?> នាក់
                                    </span>
                                    <span class="app-tile-card-sub">បញ្ជី & សំបុត្រ</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 2. Payments (Green Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p1">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/payments" class="app-tile-card app-tile-green">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">ការទូទាត់</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-success"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-success-subtle text-success border border-success-subtle">
                                        <?php echo (int)$stats['paid']; ?> ជោគជ័យ
                                    </span>
                                    <span class="app-tile-card-sub">ចំណូល & ធនាគារ</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 3. Delegations (Purple Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p1">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/workshops/' . $workshop['id'] . '/delegations'; ?>" class="app-tile-card app-tile-purple" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">កូតា & ប្រតិភូ</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-purple"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge" style="background: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe;">
                                        ២៥ ខេត្ត-ក្រុង
                                    </span>
                                    <span class="app-tile-card-sub">តំណាងស្ថាប័ន</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 4. Checkin Station (Cyan Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p2">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/checkin/' . $workshop['id']; ?>" class="app-tile-card app-tile-cyan" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">ស្កេនវត្តមាន</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-info"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-info-subtle text-info border border-info-subtle">
                                        QR & ផ្ទៃមុខ
                                    </span>
                                    <span class="app-tile-card-sub">ច្រកចូលកម្មវិធី</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 5. Live Stage & Q&A (Red Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p2">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/live/' . $workshop['id']; ?>" class="app-tile-card app-tile-red" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">ផ្សាយផ្ទាល់</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-danger"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-danger-subtle text-danger border border-danger-subtle">
                                        វេទិកា Live
                                    </span>
                                    <span class="app-tile-card-sub">សំណួរ & បោះឆ្នោត</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 6. Allowances Desk (Teal Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p2">
                        <a href="<?php echo $isUnpaid ? APP_URL . '/workshops/' . $workshop['id'] . '/activate' : APP_URL . '/workshops/' . $workshop['id'] . '/allowances'; ?>" class="app-tile-card app-tile-teal" title="<?php echo $isUnpaid ? 'បង់ថ្លៃប្រព័ន្ធដើម្បីដំណើរការ' : ''; ?>">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                                <i class="bi bi-wallet2"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">ប្រាក់ឧបត្ថម្ភ</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-success"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-success-subtle text-success border border-success-subtle">
                                        តុទូទាត់
                                    </span>
                                    <span class="app-tile-card-sub">តារាងចុះហត្ថលេខា</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 7. Comprehensive Report (Amber Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p3">
                        <a href="<?php echo APP_URL; ?>/reports/workshop/<?php echo $workshop['id']; ?>" class="app-tile-card app-tile-amber">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">របាយការណ៍សរុប</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-warning"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-warning-subtle text-warning border border-warning-subtle">
                                        តារាងវិភាគ
                                    </span>
                                    <span class="app-tile-card-sub">ទាញយក Excel / PDF</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 8. Feedback & Survey (Sky Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p3">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/feedback" class="app-tile-card app-tile-sky">
                            <div class="app-tile-card-icon" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">មតិកែលម្អ</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-info"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge bg-info-subtle text-info border border-info-subtle">
                                        ការវាយតម្លៃ
                                    </span>
                                    <span class="app-tile-card-sub">កម្រិតការពេញចិត្ត</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- 9. Billing & Platform Tier (Slate Accent) -->
                    <div class="col-12 col-sm-6 col-md-4 op-card-item" data-category="p3">
                        <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/activate" class="app-tile-card app-tile-slate <?php echo $isUnpaid ? 'border-warning bg-warning-subtle' : ''; ?>">
                            <div class="app-tile-card-icon" style="background: <?php echo $isUnpaid ? 'linear-gradient(135deg, #f59e0b 0%, #b45309 100%)' : 'linear-gradient(135deg, #475569 0%, #334155 100%)'; ?>;">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="app-tile-card-title">វិក្កយបត្រ & ចំណុះ</span>
                                    <i class="bi bi-arrow-up-right app-tile-arrow text-secondary"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <span class="app-tile-card-badge <?php echo $isUnpaid ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary border'; ?>">
                                        <?php echo $isUnpaid ? 'បង់ថ្លៃ' : '$' . number_format($billing['platform_fee'] ?? 0, 2); ?>
                                    </span>
                                    <span class="app-tile-card-sub">អតិបរមា <?php echo number_format($workshop['capacity']); ?> នាក់</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: របៀបវារៈកម្មវិធី (col-lg-4) -->
    <div class="col-lg-4">
        <div class="card section-panel-card h-100 overflow-hidden">
            <div class="card-header section-panel-header py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-3 p-1.5 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-calendar3-event"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">របៀបវារៈកម្មវិធី</h6>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <?php if (!empty($sessions)): ?>
                        <span class="badge bg-primary text-white rounded-pill px-2 py-1 small"><?php echo count($sessions); ?> វគ្គ</span>
                    <?php endif; ?>
                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/agenda/print" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2 d-inline-flex align-items-center justify-content-center gap-1.5" style="height: 32px; padding: 0 10px;" title="ទម្រង់បោះពុម្ព A4">
                        <i class="bi bi-printer"></i> <span>បោះពុម្ព</span>
                    </a>
                    <button type="button" class="btn btn-primary btn-sm rounded-2 d-inline-flex align-items-center justify-content-center gap-1.5" style="height: 32px; padding: 0 12px;" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                        <i class="bi bi-plus-lg"></i> <span>បន្ថែម</span>
                    </button>
                </div>
            </div>
            <div class="card-body p-3.5 p-xl-4 d-flex flex-column justify-content-between">
                <!-- Sessions List or Empty State -->
                <div class="flex-grow-1 d-flex flex-column">
                    <?php if (empty($sessions)): ?>
                        <div class="text-center py-4 px-3 bg-white rounded-3 border border-dashed h-100 d-flex flex-column justify-content-center align-items-center shadow-sm">
                            <div class="rounded-circle p-2.5 bg-primary bg-opacity-10 text-primary mb-2.5 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-calendar2-range fs-4"></i>
                            </div>
                            <div class="fw-bold text-dark small mb-3">មិនទាន់មានវគ្គកម្មវិធីនៅឡើយទេ</div>
                            <div class="d-flex flex-column w-100" style="max-width: 220px; gap: 8px;">
                                <button type="button" class="btn btn-primary btn-sm py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                    <i class="bi bi-plus-circle me-1"></i> បង្កើតវគ្គថ្មី
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1.5" onclick="autoGenerateAgenda(<?php echo $workshop['id']; ?>)">
                                    <i class="bi bi-lightning-charge me-1 text-warning"></i> បង្កើតគំរូស្តង់ដារ ១ ថ្ងៃ
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Sessions List -->
                        <div class="d-flex flex-column gap-2" style="max-height: 250px; overflow-y: auto; padding-right: 2px;">
                            <?php foreach ($sessions as $idx => $s): 
                                $isBreak = stripos($s['name'], 'Break') !== false || 
                                           stripos($s['name'], 'Lunch') !== false || 
                                           stripos($s['name'], 'សម្រាក') !== false || 
                                           stripos($s['name'], 'កាហ្វេ') !== false;
                            ?>
                                <div class="p-2 bg-white rounded-3 border shadow-sm d-flex justify-content-between align-items-center gap-2 hover-shadow-sm transition-all <?php echo $isBreak ? 'border-warning-subtle bg-warning-subtle bg-opacity-10' : ''; ?>">
                                    <div class="d-flex align-items-start gap-2 min-w-0">
                                        <span class="badge <?php echo $isBreak ? 'bg-warning text-dark' : 'bg-primary text-white'; ?> rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 20px; height: 20px; font-size: 0.68rem;">
                                            <?php echo $idx + 1; ?>
                                        </span>
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark small text-truncate" style="font-size: 0.82rem;">
                                                <?php if ($isBreak): ?><i class="bi bi-cup-hot text-warning me-1"></i><?php endif; ?>
                                                <?php echo e($s['name']); ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap text-muted mt-0.5" style="font-size: 0.72rem;">
                                                <?php if (!empty($s['start_time'])): ?>
                                                    <span><i class="bi bi-clock me-1 text-primary"></i><?php echo date('H:i', strtotime($s['start_time'])); ?><?php echo !empty($s['end_time']) ? ' - ' . date('H:i', strtotime($s['end_time'])) : ''; ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($s['facilitator'])): ?>
                                                    <span class="text-truncate" style="max-width: 100px;"><i class="bi bi-person me-0.5 text-secondary"></i><?php echo e($s['facilitator']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        <button type="button" class="btn btn-light btn-sm p-1 text-secondary rounded-2" title="កែប្រែ" onclick='openEditSessionModal(<?php echo json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>
                                            <i class="bi bi-pencil" style="font-size: 0.78rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm p-1 text-danger rounded-2" title="លុប" onclick="deleteSession(<?php echo $workshop['id']; ?>, <?php echo $s['id']; ?>)">
                                            <i class="bi bi-trash" style="font-size: 0.78rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Bottom Agenda Actions -->
                        <div class="mt-2.5 pt-2 border-top d-flex justify-content-between align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 rounded-2" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                <i class="bi bi-plus-lg me-1"></i> បន្ថែមវគ្គ
                            </button>
                            <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/agenda/print" target="_blank" class="btn btn-primary btn-sm py-1 px-2.5 rounded-2 text-decoration-none">
                                <i class="bi bi-printer me-1"></i> ទម្រង់បោះពុម្ព A4
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ==========================================
     MODALS FOR SECONDARY SETTINGS (Clean Pure Khmer, Bigger Gap)
     ========================================== -->

<!-- 1. Modal: Public Event Link & QR Code -->
<div class="modal fade" id="qrPublicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code text-primary"></i> តំណភ្ជាប់សាធារណៈ & QR
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small mb-3 text-start">
                    ចែករំលែកតំណភ្ជាប់នេះ ឬដាក់បង្ហាញ QR Code ឱ្យសិក្ខាកាមស្កេនដើម្បីចុះឈ្មោះចូលរួម៖
                </p>
                <?php $publicEventUrl = qrCodeUrl('/event/' . $workshop['slug']); ?>
                <div class="input-group input-group-sm mb-4">
                    <input type="text" id="publicUrl" class="form-control font-monospace" readonly value="<?php echo e($publicEventUrl); ?>">
                    <button class="btn btn-primary px-3" type="button" onclick="copyPublicLink('publicUrl', this)">
                        <i class="bi bi-clipboard me-1"></i> ចម្លង
                    </button>
                </div>
                <div class="p-3 bg-light rounded-4 d-inline-block border shadow-sm mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($publicEventUrl); ?>" class="img-fluid rounded" alt="QR Code" style="max-width: 180px;">
                    <div class="small text-muted mt-2 fw-semibold">
                        <i class="bi bi-phone me-1"></i> ស្កេនតាមទូរស័ព្ទដៃ
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="<?php echo e($publicEventUrl); ?>" target="_blank" class="btn btn-sm btn-outline-primary px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> បើកមើលទំព័រសាធារណៈ
                    </a>
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=<?php echo urlencode($publicEventUrl); ?>" download="qr_<?php echo e($workshop['slug']); ?>.png" target="_blank" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="bi bi-download me-1"></i> ទាញយក QR
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal: Daily Check-in Slots Configuration (Pure Khmer, No (Single) / (Twice Daily), Bigger Gap) -->
<div class="modal fade" id="dailyScansModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    <h5 class="modal-title fw-bold text-dark mb-0">ចំនួនដងស្កេនវត្តមានក្នុង ១ ថ្ងៃ</h5>
                </div>
                <span class="badge bg-primary rounded-pill px-2.5 py-1" id="currentDailyScansBadge">
                    <?php echo (int)($workshop['daily_scans_required'] ?? 1); ?> ដង/ថ្ងៃ
                </span>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">
                    ជ្រើសរើសចំនួនដងដែលត្រូវស្កេនវត្តមានសិក្ខាកាមក្នុង ១ ថ្ងៃ សម្រាប់សិក្ខាសាលានេះ៖
                </p>
                <?php $curScans = (int)($workshop['daily_scans_required'] ?? 1); ?>
                <div class="d-flex flex-column gap-3">
                    <label class="form-check p-3 rounded-3 border d-flex align-items-center cursor-pointer m-0 <?php echo $curScans == 1 ? 'bg-primary-subtle border-primary' : 'bg-light'; ?>" id="label_scan_1" style="cursor: pointer;">
                        <input class="form-check-input ms-1 me-3 mt-0" type="radio" name="daily_scans_radio" value="1" <?php echo $curScans == 1 ? 'checked' : ''; ?> onchange="changeDailyScans(1)">
                        <div>
                            <span class="fw-bold text-dark d-block">ស្កេន ១ ដង</span>
                            <span class="text-muted small">សម្រាប់កម្មវិធី ១ ព្រឹក ឬ ១ រសៀល</span>
                        </div>
                    </label>

                    <label class="form-check p-3 rounded-3 border d-flex align-items-center cursor-pointer m-0 <?php echo $curScans == 2 ? 'bg-primary-subtle border-primary' : 'bg-light'; ?>" id="label_scan_2" style="cursor: pointer;">
                        <input class="form-check-input ms-1 me-3 mt-0" type="radio" name="daily_scans_radio" value="2" <?php echo $curScans == 2 ? 'checked' : ''; ?> onchange="changeDailyScans(2)">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark">ស្កេន ២ ដង</span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle small py-0.5">ពេញនិយម</span>
                            </div>
                            <span class="text-muted small">ព្រឹក + រសៀល (កម្មវិធីពេញ ១ ថ្ងៃ ឬ ច្រើនថ្ងៃ)</span>
                        </div>
                    </label>

                    <label class="form-check p-3 rounded-3 border d-flex align-items-center cursor-pointer m-0 <?php echo $curScans == 3 ? 'bg-primary-subtle border-primary' : 'bg-light'; ?>" id="label_scan_3" style="cursor: pointer;">
                        <input class="form-check-input ms-1 me-3 mt-0" type="radio" name="daily_scans_radio" value="3" <?php echo $curScans == 3 ? 'checked' : ''; ?> onchange="changeDailyScans(3)">
                        <div>
                            <span class="fw-bold text-dark d-block">ស្កេន ៣ ដង</span>
                            <span class="text-muted small">ព្រឹក + រសៀល + ល្ងាច (ចេញ ឬទទួលវិញ្ញាបនបត្រ)</span>
                        </div>
                    </label>

                    <label class="form-check p-3 rounded-3 border d-flex align-items-center cursor-pointer m-0 <?php echo $curScans == 4 ? 'bg-primary-subtle border-primary' : 'bg-light'; ?>" id="label_scan_4" style="cursor: pointer;">
                        <input class="form-check-input ms-1 me-3 mt-0" type="radio" name="daily_scans_radio" value="4" <?php echo $curScans == 4 ? 'checked' : ''; ?> onchange="changeDailyScans(4)">
                        <div>
                            <span class="fw-bold text-dark d-block">ស្កេន ៤ ដង</span>
                            <span class="text-muted small">ព្រឹកចូល + ព្រឹកចេញ + រសៀលចូល + រសៀលចេញ</span>
                        </div>
                    </label>
                </div>
                <div id="dailyScansStatus" class="small mt-3 d-none"></div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">រួចរាល់</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Ticket Types (Pure Khmer, No (Tickets)) -->
<div class="modal fade" id="ticketsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-ticket-perforated-fill text-primary"></i> ប្រភេទសំបុត្រ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($tickets as $t): ?>
                        <li class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><?php echo e($t['name']); ?></div>
                                <div class="text-muted small"><?php echo ucfirst($t['ticket_type']); ?></div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 fs-6">
                                <?php echo $t['price'] == 0 ? 'ឥតគិតថ្លៃ' : '$' . number_format($t['price'], 2); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="text-center pt-2 border-top">
                    <a href="<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/edit" class="btn btn-outline-primary btn-sm px-3">
                        <i class="bi bi-pencil me-1"></i> កែសម្រួលព័ត៌មានសំបុត្រ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Smart calculation for next session start and end time
$lastSession = !empty($sessions) ? end($sessions) : null;
$nextStartTime = '08:30';
$nextEndTime = '09:00';

if ($lastSession && !empty($lastSession['end_time'])) {
    $nextStartTime = date('H:i', strtotime($lastSession['end_time']));
    $nextEndTime = date('H:i', strtotime($lastSession['end_time'] . ' +30 minutes'));
} elseif (!empty($workshop['start_time'])) {
    $nextStartTime = date('H:i', strtotime($workshop['start_time']));
    $nextEndTime = date('H:i', strtotime($workshop['start_time'] . ' +30 minutes'));
}
?>

<!-- Modal: Add Agenda Session -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-plus text-primary"></i> បន្ថែមវគ្គកម្មវិធី (របៀបវារៈ)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSessionForm" onsubmit="event.preventDefault(); saveSession(<?php echo $workshop['id']; ?>);">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            ឈ្មោះវគ្គ ឬ សកម្មភាពកម្មវិធី <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="sessionNameInput" class="form-control" placeholder="ឧ. ពិធីបើកកម្មវិធី & មតិស្វាគមន៍" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ម៉ោងចាប់ផ្តើម</label>
                            <input type="time" id="sessionStartTimeInput" class="form-control" value="<?php echo htmlspecialchars($nextStartTime); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ម៉ោងបញ្ចប់</label>
                            <input type="time" id="sessionEndTimeInput" class="form-control" value="<?php echo htmlspecialchars($nextEndTime); ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">វាគ្មិន / អ្នកសម្របសម្រួល</label>
                            <input type="text" id="sessionFacilitatorInput" class="form-control" placeholder="ឧ. លោកគ្រូ... / គណៈអធិបតី" value="<?php echo htmlspecialchars($workshop['trainer_name'] ?? ''); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ទីតាំង / បន្ទប់</label>
                            <input type="text" id="sessionLocationInput" class="form-control" placeholder="ឧ. សាលប្រជុំធំ / Room A" value="<?php echo htmlspecialchars($workshop['venue'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">កាលបរិច្ឆេទ</label>
                        <input type="date" id="sessionDateInput" class="form-control" value="<?php echo htmlspecialchars($workshop['start_date'] ?? date('Y-m-d')); ?>">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">ការពិពណ៌នាសង្ខេប (បើមាន)</label>
                        <textarea id="sessionDescriptionInput" class="form-control" rows="2" placeholder="ខ្លឹមសារលម្អិត ឬឯកសារពាក់ព័ន្ធ..."></textarea>
                    </div>

                    <div id="addSessionAlert" class="alert py-2 small d-none mt-3"></div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" id="btnSaveSession" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-check2 me-1"></i> រក្សាទុក
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Agenda Session -->
<div class="modal fade" id="editSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary"></i> កែសម្រួលវគ្គកម្មវិធី
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSessionForm" onsubmit="event.preventDefault(); updateSession(<?php echo $workshop['id']; ?>);">
                <input type="hidden" id="editSessionId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            ឈ្មោះវគ្គ ឬ សកម្មភាពកម្មវិធី <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="editSessionName" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ម៉ោងចាប់ផ្តើម</label>
                            <input type="time" id="editSessionStartTime" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ម៉ោងបញ្ចប់</label>
                            <input type="time" id="editSessionEndTime" class="form-control">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">វាគ្មិន / អ្នកសម្របសម្រួល</label>
                            <input type="text" id="editSessionFacilitator" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-dark small mb-1">ទីតាំង / បន្ទប់</label>
                            <input type="text" id="editSessionLocation" class="form-control">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small mb-1">ការពិពណ៌នាសង្ខេប (បើមាន)</label>
                        <textarea id="editSessionDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div id="editSessionAlert" class="alert py-2 small d-none mt-3"></div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" id="btnUpdateSession" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-check2 me-1"></i> រក្សាទុកការកែប្រែ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterOperations(category, btn) {
    document.querySelectorAll('.op-clean-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.op-card-item').forEach(card => {
        if (category === 'all' || card.getAttribute('data-category') === category) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>


<!-- Modal: Increase Capacity -->
<div class="modal fade" id="upgradeCapacityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">ដំឡើងចំណុះសិក្ខាកាមបន្ថែម</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    អ្នកគ្រាន់តែបង់ប្រាក់បន្ថែមលើចំនួនខុសគ្នារវាងកម្រិតបច្ចុប្បន្ន និងកម្រិតថ្មីប៉ុណ្ណោះ។ ការទូទាត់មុនរបស់អ្នកមិនបាត់បង់ឡើយ។
                </p>
                <div class="mb-3">
                    <label class="form-label">ចំណុះបច្ចុប្បន្ន</label>
                    <input type="text" class="form-control" value="<?php echo number_format($workshop['capacity']); ?> នាក់" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">ចំណុះថ្មីដែលត្រូវការ</label>
                    <input type="number" id="newCapacityInput" class="form-control" min="<?php echo $workshop['capacity'] + 1; ?>" value="<?php echo $workshop['capacity'] + 100; ?>">
                </div>
                <div id="upgradeCalculationBox" class="alert alert-info py-2 small d-none">
                    ថ្លៃសេវាដែលត្រូវបង់បន្ថែម: <strong id="upgradeFeeAmount">$0.00</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                <button type="button" class="btn btn-primary" onclick="confirmCapacityUpgrade(<?php echo $workshop['id']; ?>)">បន្តដំឡើងចំណុះ</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Telegram Group Report & Settings -->
<div class="modal fade" id="telegramReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-telegram me-2"></i> របាយការណ៍ & ដំណឹង Telegram Group
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Group ID setting -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-1">
                        Telegram Group Chat ID <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-hash"></i></span>
                        <input type="text" id="tgChatIdInput" class="form-control" placeholder="ឧ. -100234567890" value="<?php echo htmlspecialchars($workshop['telegram_chat_id'] ?? ''); ?>">
                        <button class="btn btn-outline-primary d-inline-flex align-items-center" type="button" id="btnScanGroups" onclick="scanTelegramGroups(<?php echo $workshop['id']; ?>)" title="ស្កេនរក Group ID ដោយស្វ័យប្រវត្តិ">
                            <i class="bi bi-radar me-1"></i> <span>ស្កេន</span>
                        </button>
                        <button class="btn btn-primary d-inline-flex align-items-center" type="button" onclick="saveTelegramSettings(<?php echo $workshop['id']; ?>)">
                            <i class="bi bi-check2 me-1"></i> <span>រក្សាទុក</span>
                        </button>
                    </div>

                    <!-- Container for Scanned Groups Results -->
                    <div id="tgScannedGroupsBox" class="mt-2 d-none"></div>

                    <div class="form-text small mt-2 text-muted">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> <b>ងាយស្រួលបំផុត៖</b> Add Bot ចូលក្នុង Telegram Group រួចវាយពាក្យ <b>«hi»</b> ក្នុងក្រុមនោះ ហើយចុចប៊ូតុង <b>«ស្កេន»</b> នោះវានឹងចាប់យក ID ដោយស្វ័យប្រវត្តិតែម្តង។
                    </div>
                </div>

                <!-- Auto Notify Toggle -->
                <div class="form-check form-switch p-3 bg-light rounded-3 mb-3">
                    <input class="form-check-input ms-0 me-2" type="checkbox" id="tgAutoNotifyInput" <?php echo !empty($workshop['telegram_notify_registration']) ? 'checked' : ''; ?> onchange="saveTelegramSettings(<?php echo $workshop['id']; ?>)">
                    <label class="form-check-label fw-semibold" for="tgAutoNotifyInput">
                        ជូនដំណឹងស្វ័យប្រវត្តិកាលណាមានអ្នកចុះឈ្មោះថ្មី
                    </label>
                    <div class="text-muted small ps-4 mt-1">
                        Bot នឹងបាញ់សារឈ្មោះ, លេខទូរស័ព្ទ, ស្ថាប័ន, និងចំនួនសរុប (ឧ. សិក្ខាកាមទី #15) ចូលក្នុងក្រុមភ្លាមៗ។
                    </div>
                </div>

                <div id="tgSettingsAlert" class="alert alert-success py-2 small d-none"></div>

                <hr class="my-4">

                <!-- Instant Report Button -->
                <div>
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-send-fill text-primary me-1"></i> បញ្ជូនរបាយការណ៍បច្ចុប្បន្នភាពឥឡូវនេះ
                    </h6>
                    <p class="text-muted small mb-3">
                        ចុចប៊ូតុងខាងក្រោមដើម្បីឱ្យ Bot ប្រមូលទិន្នន័យជាក់ស្តែង (ចំនួនចុះឈ្មោះ, បានបញ្ជាក់, វត្តមាន Check-in, ភាគរយពេញ) រួចផ្ញើជា Card របាយការណ៍ស្អាតចូលក្នុង Telegram Group។
                    </p>
                    <button type="button" id="btnSendTgReport" class="btn btn-primary w-100 py-2 fw-bold" onclick="sendTelegramReportNow(<?php echo $workshop['id']; ?>)">
                        <i class="bi bi-telegram me-2"></i> ផ្ញើរបាយការណ៍ទៅ Telegram ឥឡូវនេះ
                    </button>
                    <div id="tgReportAlert" class="alert mt-3 py-2 small d-none"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">បិទ</button>
            </div>
        </div>
    </div>
</div>

<script>
async function saveTelegramSettings(workshopId, silent = false) {
    const chatId = document.getElementById('tgChatIdInput').value.trim();
    const autoNotify = document.getElementById('tgAutoNotifyInput').checked ? 1 : 0;
    const alertEl = document.getElementById('tgSettingsAlert');

    const fd = new FormData();
    fd.append('telegram_chat_id', chatId);
    fd.append('telegram_notify_registration', autoNotify);
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/telegram-settings`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (!silent) {
            alertEl.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger') + ' py-2 small';
            alertEl.innerHTML = (data.success ? '<i class="bi bi-check-circle-fill me-1"></i> ' : '<i class="bi bi-exclamation-triangle-fill me-1"></i> ') + data.message;
            alertEl.classList.remove('d-none');
            setTimeout(() => alertEl.classList.add('d-none'), 3500);
        }
    } catch (e) {
        if (!silent) alert('បញ្ហាភ្ជាប់ទៅកាន់ប្រព័ន្ធ');
    }
}

async function scanTelegramGroups(workshopId) {
    const btn = document.getElementById('btnScanGroups');
    const box = document.getElementById('tgScannedGroupsBox');
    const input = document.getElementById('tgChatIdInput');

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> ស្កេន...';
    box.className = 'mt-2 alert alert-secondary py-2 small d-flex align-items-center gap-2';
    box.innerHTML = '<span class="spinner-border spinner-border-sm"></span> កំពុងស្កេនរកក្រុម Telegram...';
    box.classList.remove('d-none');

    const fd = new FormData();
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/scan-telegram-groups`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success && data.groups && data.groups.length > 0) {
            // Auto-fill input with most recent group
            const topGroup = data.groups[0];
            input.value = topGroup.chat_id;

            // Auto-save setting
            saveTelegramSettings(workshopId, true);

            let html = `<div class="p-2 bg-success-subtle border border-success rounded-2 text-success small mb-2">`;
            html += `<i class="bi bi-check-circle-fill me-1"></i> <b>បានចាប់យកជោគជ័យ៖</b> «${escapeHtml(topGroup.title)}» (<code>${topGroup.chat_id}</code>)`;
            html += `</div>`;

            if (data.groups.length > 1) {
                html += `<div class="text-muted small mb-1">ក្រុមផ្សេងទៀតដែលបានរកឃើញ (ចុចដើម្បីជ្រើសរើស)៖</div>`;
                html += `<div class="d-flex flex-wrap gap-1">`;
                data.groups.forEach(g => {
                    html += `<button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="selectTgGroup('${g.chat_id}', '${escapeHtml(g.title)}')">`;
                    html += `📋 ${escapeHtml(g.title)} <span class="text-muted">(${g.chat_id})</span>`;
                    html += `</button>`;
                });
                html += `</div>`;
            }

            box.className = 'mt-2';
            box.innerHTML = html;
        } else {
            box.className = 'mt-2 alert alert-warning py-2 small';
            box.innerHTML = `<div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}</div>`;
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        box.className = 'mt-2 alert alert-danger py-2 small';
        box.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> កំហុសភ្ជាប់ប្រព័ន្ធ សូមព្យាយាមម្តងទៀត។';
    }
}

function selectTgGroup(chatId, title) {
    const input = document.getElementById('tgChatIdInput');
    input.value = chatId;
    saveTelegramSettings(<?php echo $workshop['id']; ?>);
    const box = document.getElementById('tgScannedGroupsBox');
    box.innerHTML = `<div class="p-2 bg-success-subtle border border-success rounded-2 text-success small"><i class="bi bi-check-circle-fill me-1"></i> <b>បានជ្រើសរើស៖</b> «${escapeHtml(title)}» (<code>${chatId}</code>)</div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

async function sendTelegramReportNow(workshopId) {
    const btn = document.getElementById('btnSendTgReport');
    const alertEl = document.getElementById('tgReportAlert');
    const chatId = document.getElementById('tgChatIdInput').value.trim();

    if (!chatId) {
        alertEl.className = 'alert alert-warning py-2 small';
        alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> សូមបញ្ចូល Telegram Group Chat ID រួចចុច "រក្សាទុក" ជាមុនសិន។';
        alertEl.classList.remove('d-none');
        document.getElementById('tgChatIdInput').focus();
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងបញ្ជូនរបាយការណ៍...';
    alertEl.classList.add('d-none');

    const fd = new FormData();
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/telegram-report`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        btn.disabled = false;
        btn.innerHTML = origHtml;

        alertEl.className = 'alert ' + (data.success ? 'alert-success' : 'alert-danger') + ' py-2 small';
        alertEl.innerHTML = (data.success ? '<i class="bi bi-check-circle-fill me-1"></i> ' : '<i class="bi bi-exclamation-triangle-fill me-1"></i> ') + data.message;
        alertEl.classList.remove('d-none');
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alertEl.className = 'alert alert-danger py-2 small';
        alertEl.innerHTML = 'កំហុសភ្ជាប់ប្រព័ន្ធ សូមព្យាយាមម្តងទៀត។';
        alertEl.classList.remove('d-none');
    }
}
async function duplicateWorkshop(id) {
    if (!confirm('តើអ្នកពិតជាចង់ចម្លងទម្រង់សិក្ខាសាលានេះ (សំបុត្រ, ការរចនា, ប្រអប់ព័ត៌មាន) ដោយមិនចម្លងទិន្នន័យសិក្ខាកាមចាស់មែនទេ?')) return;
    const res = await apiRequest(`<?php echo APP_URL; ?>/workshops/${id}/duplicate`, 'POST');
    if (res.success && res.data.redirect) {
        window.location.href = res.data.redirect;
    } else {
        alert(res.message || 'ការចម្លងមិនបានជោគជ័យ។');
    }
}

async function confirmCapacityUpgrade(id) {
    const newCap = document.getElementById('newCapacityInput').value;
    const form = new FormData();
    form.append('new_capacity', newCap);
    form.append('confirm', '1');
    form.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    const res = await fetch(`<?php echo APP_URL; ?>/workshops/${id}/upgrade-capacity`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: form
    });
    const data = await res.json();
    if (data.success) {
        alert(data.message);
        location.reload();
    } else {
        alert(data.message || 'ការដំឡើងចំណុះមិនបានជោគជ័យ។');
    }
}

function copyPublicLink(inputId, btn) {
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
            const origHtml = button.innerHTML;
            button.className = 'btn btn-sm btn-success text-white';
            button.innerHTML = '<i class="bi bi-check2 me-1" style="transform: scale(1.2); transition: transform 0.15s ease;"></i> បានចម្លង!';

            setTimeout(() => {
                button.className = 'btn btn-sm btn-outline-primary';
                button.innerHTML = origHtml;
            }, 1200);
        }
    }).catch(e => console.error('Copy failed:', e));
}

async function changeDailyScans(count) {
    const statusEl = document.getElementById('dailyScansStatus');
    const badgeEl = document.getElementById('currentDailyScansBadge');
    
    // Update visual radio active highlight
    [1, 2, 3, 4].forEach(i => {
        const lbl = document.getElementById('label_scan_' + i);
        if (lbl) {
            if (i === count) {
                lbl.classList.add('bg-primary-subtle', 'border-primary');
                lbl.classList.remove('bg-light');
            } else {
                lbl.classList.remove('bg-primary-subtle', 'border-primary');
                lbl.classList.add('bg-light');
            }
        }
    });

    if (badgeEl) badgeEl.textContent = count + ' ដង/ថ្ងៃ';
    const headerBadge = document.getElementById('headerDailyScansBadge');
    if (headerBadge) headerBadge.textContent = 'ស្កេន ' + count + 'ដង';
    if (statusEl) {
        statusEl.className = 'small text-primary d-block mt-2';
        statusEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងរក្សាទុក...';
    }

    const fd = new FormData();
    fd.append('daily_scans_required', count);
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/<?php echo $workshop['id']; ?>/daily-scans`, {
            method: 'POST',
            body: fd
        });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (je) {
            console.error('Non-JSON response:', text);
            throw new Error('ម៉ាស៊ីនបម្រើមិនបានឆ្លើយតបជា JSON ត្រឹមត្រូវ។ សូមប្រាកដថាបាន Run Database Update SQL រួចរាល់។');
        }

        if (statusEl) {
            if (data.success) {
                statusEl.className = 'small text-success d-block mt-2';
                statusEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
                setTimeout(() => { statusEl.classList.add('d-none'); }, 3000);
            } else {
                statusEl.className = 'small text-danger d-block mt-2';
                statusEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'បរាជ័យ');
            }
        }
    } catch (e) {
        if (statusEl) {
            statusEl.className = 'small text-danger d-block mt-2';
            statusEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + e.message;
        }
    }
}

// ==========================================
// AGENDA SESSIONS MANAGEMENT
// ==========================================
async function saveSession(workshopId) {
    const name = document.getElementById('sessionNameInput').value.trim();
    const startTime = document.getElementById('sessionStartTimeInput').value;
    const endTime = document.getElementById('sessionEndTimeInput').value;
    const facilitator = document.getElementById('sessionFacilitatorInput').value.trim();
    const location = document.getElementById('sessionLocationInput').value.trim();
    const sessionDate = document.getElementById('sessionDateInput').value;
    const description = document.getElementById('sessionDescriptionInput').value.trim();
    const alertEl = document.getElementById('addSessionAlert');
    const btn = document.getElementById('btnSaveSession');

    if (!name) {
        alert('សូមបញ្ចូលឈ្មោះវគ្គ ឬ កម្មវិធី');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងរក្សាទុក...';

    const fd = new FormData();
    fd.append('name', name);
    fd.append('start_time', startTime);
    fd.append('end_time', endTime);
    fd.append('facilitator', facilitator);
    fd.append('location', location);
    fd.append('session_date', sessionDate);
    fd.append('description', description);
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/sessions`, {
            method: 'POST',
            body: fd
        });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (je) {
            console.error('Non-JSON response:', text);
            throw new Error('ការឆ្លើយតបពីម៉ាស៊ីនបម្រើមិនត្រឹមត្រូវ');
        }

        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success) {
            window.location.reload();
        } else {
            alertEl.className = 'alert alert-danger py-2 small mt-3';
            alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'បរាជ័យ');
            alertEl.classList.remove('d-none');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alertEl.className = 'alert alert-danger py-2 small mt-3';
        alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + e.message;
        alertEl.classList.remove('d-none');
    }
}

function openEditSessionModal(session) {
    document.getElementById('editSessionId').value = session.id;
    document.getElementById('editSessionName').value = session.name || '';
    
    // Format times to HH:mm for time input
    const formatTime = (t) => {
        if (!t) return '';
        const parts = t.split(':');
        return parts.length >= 2 ? `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}` : t;
    };
    document.getElementById('editSessionStartTime').value = formatTime(session.start_time);
    document.getElementById('editSessionEndTime').value = formatTime(session.end_time);
    document.getElementById('editSessionFacilitator').value = session.facilitator || '';
    document.getElementById('editSessionLocation').value = session.location || '';
    document.getElementById('editSessionDescription').value = session.description || '';
    
    const alertEl = document.getElementById('editSessionAlert');
    if (alertEl) alertEl.classList.add('d-none');

    const modal = new bootstrap.Modal(document.getElementById('editSessionModal'));
    modal.show();
}

async function updateSession(workshopId) {
    const sessionId = document.getElementById('editSessionId').value;
    const name = document.getElementById('editSessionName').value.trim();
    const startTime = document.getElementById('editSessionStartTime').value;
    const endTime = document.getElementById('editSessionEndTime').value;
    const facilitator = document.getElementById('editSessionFacilitator').value.trim();
    const location = document.getElementById('editSessionLocation').value.trim();
    const description = document.getElementById('editSessionDescription').value.trim();
    const alertEl = document.getElementById('editSessionAlert');
    const btn = document.getElementById('btnUpdateSession');

    if (!name) {
        alert('សូមបញ្ចូលឈ្មោះវគ្គ ឬ កម្មវិធី');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> កំពុងកែប្រែ...';

    const fd = new FormData();
    fd.append('name', name);
    fd.append('start_time', startTime);
    fd.append('end_time', endTime);
    fd.append('facilitator', facilitator);
    fd.append('location', location);
    fd.append('description', description);
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/sessions/${sessionId}/update`, {
            method: 'POST',
            body: fd
        });
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (je) {
            console.error('Non-JSON response:', text);
            throw new Error('ការឆ្លើយតបពីម៉ាស៊ីនបម្រើមិនត្រឹមត្រូវ');
        }

        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success) {
            window.location.reload();
        } else {
            alertEl.className = 'alert alert-danger py-2 small mt-3';
            alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'បរាជ័យ');
            alertEl.classList.remove('d-none');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alertEl.className = 'alert alert-danger py-2 small mt-3';
        alertEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + e.message;
        alertEl.classList.remove('d-none');
    }
}

async function deleteSession(workshopId, sessionId) {
    if (!confirm('តើអ្នកពិតជាចង់លុបវគ្គកម្មវិធីនេះមែនទេ?')) {
        return;
    }

    const fd = new FormData();
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/sessions/${sessionId}/delete`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'ការលុបមិនបានជោគជ័យ');
        }
    } catch (e) {
        alert('កំហុសក្នុងការភ្ជាប់ទៅកាន់ម៉ាស៊ីនបម្រើ');
    }
}

async function autoGenerateAgenda(workshopId) {
    if (!confirm('តើអ្នកចង់បង្កើតរបៀបវារៈស្តង់ដារពេញមួយថ្ងៃ (១០ វគ្គ) ដោយស្វ័យប្រវត្តិនេះមែនទេ?')) {
        return;
    }

    const fd = new FormData();
    fd.append('_csrf_token', '<?php echo generateCsrfToken(); ?>');

    try {
        const res = await fetch(`<?php echo APP_URL; ?>/workshops/${workshopId}/sessions/auto-generate`, {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'បរាជ័យក្នុងការបង្កើតរបៀបវារៈ');
        }
    } catch (e) {
        alert('កំហុសក្នុងការភ្ជាប់ទៅកាន់ម៉ាស៊ីនបម្រើ');
    }
}

// Smart time arithmetic helper
function addMinutesToTime(timeStr, minutesToAdd = 30) {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    if (parts.length < 2) return '';
    let hours = parseInt(parts[0], 10);
    let minutes = parseInt(parts[1], 10);
    
    minutes += minutesToAdd;
    hours += Math.floor(minutes / 60);
    minutes = minutes % 60;
    hours = hours % 24;
    
    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
}

// Setup smart time listeners when modal opens and when start time changes
document.addEventListener('DOMContentLoaded', function() {
    const addSessionModal = document.getElementById('addSessionModal');
    if (addSessionModal) {
        addSessionModal.addEventListener('show.bs.modal', function() {
            // Reset input fields
            const nameInput = document.getElementById('sessionNameInput');
            if (nameInput) {
                nameInput.value = '';
                setTimeout(() => nameInput.focus(), 300);
            }
            const descInput = document.getElementById('sessionDescriptionInput');
            if (descInput) descInput.value = '';
            const alertEl = document.getElementById('addSessionAlert');
            if (alertEl) alertEl.classList.add('d-none');

            // Pick end time of latest session if available
            const existingSessions = <?php echo json_encode(array_values($sessions), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            let smartStart = '<?php echo $nextStartTime; ?>';
            let smartEnd = '<?php echo $nextEndTime; ?>';

            if (existingSessions && existingSessions.length > 0) {
                const last = existingSessions[existingSessions.length - 1];
                if (last && last.end_time) {
                    const parts = last.end_time.split(':');
                    smartStart = parts[0].padStart(2, '0') + ':' + parts[1].padStart(2, '0');
                    smartEnd = addMinutesToTime(smartStart, 30);
                }
            }

            const startInp = document.getElementById('sessionStartTimeInput');
            const endInp = document.getElementById('sessionEndTimeInput');
            if (startInp) startInp.value = smartStart;
            if (endInp) endInp.value = smartEnd;
        });
    }

    // Auto-advance End Time when Start Time is changed in Add Modal
    const startTimeInp = document.getElementById('sessionStartTimeInput');
    if (startTimeInp) {
        startTimeInp.addEventListener('change', function() {
            if (this.value) {
                document.getElementById('sessionEndTimeInput').value = addMinutesToTime(this.value, 30);
            }
        });
    }

    // Auto-advance End Time in Edit Modal if earlier or equal to Start Time
    const editStartTimeInp = document.getElementById('editSessionStartTime');
    if (editStartTimeInp) {
        editStartTimeInp.addEventListener('change', function() {
            const endInp = document.getElementById('editSessionEndTime');
            if (this.value && (!endInp.value || endInp.value <= this.value)) {
                endInp.value = addMinutesToTime(this.value, 30);
            }
        });
    }
});
</script>

