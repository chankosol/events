<?php
// views/business/workshops/create.php
$breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'],
    ['label' => !empty($workshop) ? ('កែសម្រួល: ' . mb_strimwidth($workshop['name'], 0, 35, '…')) : 'បង្កើតសិក្ខាសាលាថ្មី', 'url' => null, 'icon' => !empty($workshop) ? 'pencil' : 'plus-circle'],
];
?>
<style>
/* Sticky floating wizard navigation bar */
.sticky-wizard-card {
    position: -webkit-sticky;
    position: sticky;
    top: 60px; /* Aligns right beneath topbar (60px) */
    z-index: 950;
    background: #ffffff !important;
    border: 1px solid rgba(203, 213, 225, 0.8) !important;
    box-shadow: 0 6px 22px rgba(15, 23, 42, 0.09) !important;
    border-radius: 14px !important;
    transition: all 0.2s ease;
}

/* Wizard steps layout & clickable links */
.wizard-steps {
    display: flex;
    justify-content: space-around;
    align-items: center;
    position: relative;
    margin-bottom: 0 !important;
}

.wizard-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
    text-decoration: none !important;
    color: #475569 !important;
    cursor: pointer;
    transition: transform 0.15s ease, opacity 0.15s ease;
    padding: 6px 4px;
}

.wizard-step::before {
    content: '';
    position: absolute;
    top: 23px;
    left: -50%;
    right: 50%;
    height: 3px;
    background: #e2e8f0;
    z-index: 1;
    transition: background 0.25s ease;
}

.wizard-step:first-child::before {
    display: none !important;
}

.wizard-step.completed::before {
    background: #198754 !important;
}

.wizard-step.active::before {
    background: #0d6efd !important;
}

/* Step Circle */
.wizard-step .step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8fafc;
    color: #64748b;
    border: 2px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    z-index: 2;
    position: relative;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

/* Step Label */
.wizard-step .step-label {
    font-size: 0.82rem;
    margin-top: 0.45rem;
    color: #64748b;
    text-align: center;
    font-weight: 500;
    z-index: 2;
    position: relative;
    transition: color 0.2s ease, font-weight 0.2s ease;
}

/* Active State */
.wizard-step.active .step-circle {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25) !important;
    transform: scale(1.05);
}

.wizard-step.active .step-label {
    color: #0d6efd !important;
    font-weight: 700 !important;
}

/* Completed State */
.wizard-step.completed .step-circle {
    background: #198754 !important;
    border-color: #198754 !important;
    color: #ffffff !important;
    box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.18) !important;
}

.wizard-step.completed .step-label {
    color: #198754 !important;
    font-weight: 600;
}

/* Hover effects for clickable steps */
a.wizard-step:hover .step-circle {
    transform: translateY(-2px) scale(1.1);
    box-shadow: 0 4px 14px rgba(13, 110, 253, 0.3) !important;
    border-color: #0d6efd !important;
}

a.wizard-step:hover .step-label {
    color: #0d6efd !important;
    font-weight: 600;
}

/* Disabled State */
.wizard-step.disabled {
    cursor: not-allowed !important;
    opacity: 0.55;
    pointer-events: none;
}

/* All form label titles in blue */
.form-label, 
label.form-label,
label:not(.form-check-label),
.ticket-row label.form-label,
.field-row label.form-label,
.table-label-blue {
    color: #0d6efd !important;
    font-weight: 600 !important;
    font-size: 0.88rem !important;
    margin-bottom: 0.35rem !important;
}

.form-label .text-danger,
label .text-danger {
    color: #dc3545 !important;
    font-weight: 700 !important;
}

.form-check-label {
    color: #334155 !important;
    font-weight: 500 !important;
}

/* Ensure all button text and icons have a clear, spacious gap and are perfectly centered */
.btn,
a.btn,
button.btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    vertical-align: middle !important;
    line-height: 1.5 !important;
    gap: 0.65rem !important; /* Spacious gap between icon and text */
}

.btn i,
a.btn i,
button.btn i {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    font-size: 1.05rem;
}

@media (max-width: 576px) {
    .wizard-step .step-label {
        font-size: 0.7rem;
    }
    .wizard-step .step-circle {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }
    .wizard-step::before {
        top: 19px;
    }
    .sticky-wizard-card {
        top: 60px;
    }
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-1"><?php echo $workshop ? 'កែសម្រួលសិក្ខាសាលា: ' . e($workshop['name']) : 'បង្កើតសិក្ខាសាលាថ្មី'; ?></h2>
                <p class="text-muted mb-0">បំពេញ ៥ ជំហានដើម្បីកំណត់រចនាសម្ព័ន្ធ និងដំណើរការសិក្ខាសាលារបស់អ្នក។</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-3 py-2 shadow-sm" id="topSaveBtn" title="រក្សាទុកការកែប្រែ">
                    <i class="bi bi-floppy"></i> <span>រក្សាទុក</span>
                </button>
                <a href="<?php echo APP_URL; ?>/workshops" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-3 py-2">
                    <i class="bi bi-arrow-left"></i> <span>ត្រឡប់ទៅបញ្ជីសិក្ខាសាលា</span>
                </a>
            </div>
        </div>

        <!-- Wizard Navigation Steps (Clickable & Floating Sticky at Top) -->
        <div class="card shadow-sm border-0 mb-4 sticky-wizard-card">
            <div class="card-body p-3">
                <div class="wizard-steps d-flex justify-content-around">
                    <?php
                    $stepsConfig = [
                        1 => ['title' => 'ព័ត៌មានទូទៅ'],
                        2 => ['title' => 'ការរចនា & រូបភាព'],
                        3 => ['title' => 'ទម្រង់ចុះឈ្មោះ'],
                        4 => ['title' => 'ប្រភេទសំបុត្រ'],
                        5 => ['title' => 'ពិនិត្យ & បង់ថ្លៃប្រព័ន្ធ'],
                    ];
                    
                    foreach ($stepsConfig as $sNum => $sInfo):
                        $isActive = ($step === $sNum);
                        // Can click if workshop exists or if it's the current step 1
                        $canClick = !empty($workshop) || ($sNum === 1);
                        
                        // Determine completed state
                        $isCompleted = false;
                        if (!empty($workshop)) {
                            if ($sNum === 1) {
                                $isCompleted = true;
                            } elseif ($sNum === 2) {
                                $isCompleted = !empty($branding);
                            } elseif ($sNum === 3) {
                                $isCompleted = !empty($customFields);
                            } elseif ($sNum === 4) {
                                $isCompleted = !empty($tickets);
                            } elseif ($sNum === 5) {
                                $isCompleted = (!empty($billing) && ($billing['payment_status'] ?? '') === 'paid');
                            }
                        }
                        
                        $stepClass = $isActive ? 'active' : ($isCompleted ? 'completed' : '');
                        if (!$canClick) {
                            $stepClass .= ' disabled';
                        }
                        
                        $stepUrl = $canClick 
                            ? (APP_URL . '/workshops/create?step=' . $sNum . (!empty($workshop) ? '&workshop_id=' . $workshop['id'] : '')) 
                            : 'javascript:void(0)';
                    ?>
                        <?php if ($canClick): ?>
                            <a href="<?php echo $stepUrl; ?>" class="wizard-step <?php echo $stepClass; ?>" title="ចុចដើម្បីទៅកាន់: <?php echo e($sInfo['title']); ?>">
                                <div class="step-circle"><?php echo $sNum; ?></div>
                                <span class="step-label"><?php echo e($sInfo['title']); ?></span>
                            </a>
                        <?php else: ?>
                            <div class="wizard-step <?php echo $stepClass; ?>" title="សូមបំពេញជំហានទី ១ ជាមុនសិន">
                                <div class="step-circle"><?php echo $sNum; ?></div>
                                <span class="step-label"><?php echo e($sInfo['title']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Step Forms -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if ($step === 1): ?>
                    <!-- STEP 1: BASIC INFORMATION -->
                    <h4 class="card-title fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i> ជំហានទី ១: ព័ត៌មានទូទៅនៃសិក្ខាសាលា</h4>
                    <form method="POST" action="<?php echo APP_URL; ?>/workshops/create" id="workshopStepForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="step" value="1">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop['id'] ?? ''; ?>">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">ឈ្មោះសិក្ខាសាលា / វគ្គបណ្តុះបណ្តាល <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="ឧទាហរណ៍៖ វគ្គបណ្តុះបណ្តាលភាពជាអ្នកដឹកនាំ និងការគ្រប់គ្រងក្រុមការងារ ២០២៦" value="<?php echo e($workshop['name'] ?? $old['name'] ?? ''); ?>">
                                <?php if (!empty($errors['name'])): ?><div class="text-danger small"><?php echo $errors['name']; ?></div><?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ផ្នែក / ប្រភេទជំនាញ</label>
                                <input type="text" name="category" class="form-control" placeholder="ឧទាហរណ៍៖ ភាពជាអ្នកដឹកនាំ, ព័ត៌មានវិទ្យា, ទីផ្សារ, ធនធានមនុស្ស" value="<?php echo e($workshop['category'] ?? $old['category'] ?? 'ភាពជាអ្នកដឹកនាំ'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ទម្រង់នៃការរៀបចំ</label>
                                <select name="workshop_type" class="form-select">
                                    <option value="in-person" <?php echo (($workshop['workshop_type'] ?? '') === 'in-person') ? 'selected' : ''; ?>>ផ្ទាល់នៅទីតាំង (In-Person)</option>
                                    <option value="online" <?php echo (($workshop['workshop_type'] ?? '') === 'online') ? 'selected' : ''; ?>>អនឡាញ (Online - Zoom / Teams / Meet)</option>
                                    <option value="hybrid" <?php echo (($workshop['workshop_type'] ?? '') === 'hybrid') ? 'selected' : ''; ?>>រួមបញ្ចូលគ្នា (Hybrid - ផ្ទាល់ & អនឡាញ)</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">សេចក្តីសង្ខេបខ្លី (Tagline)</label>
                                <input type="text" name="short_description" class="form-control" placeholder="១-២ ប្រយោគសង្ខេបអំពីគោលបំណងនៃសិក្ខាសាលា" value="<?php echo e($workshop['short_description'] ?? $old['short_description'] ?? ''); ?>">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">ការពិពណ៌នាលម្អិតអំពីកម្មវិធី</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="ពិពណ៌នាលម្អិតអំពីមាតិកា អត្ថប្រយោជន៍ លក្ខខណ្ឌសិក្សា និងលទ្ធផលរំពឹងទុក..."><?php echo e($workshop['description'] ?? $old['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">ងារ / តួនាទី</label>
                                <?php 
                                $currentTitle = $workshop['trainer_title'] ?? $old['trainer_title'] ?? 'គ្រូបណ្តុះបណ្តាល'; 
                                $presetTitles = ['គ្រូបណ្តុះបណ្តាល', 'វាគ្មិនកិត្តិយស', 'វាគ្មិន', 'អ្នកសម្របសម្រួល', 'ប្រធានបទដឹកនាំ'];
                                $isPreset = in_array($currentTitle, $presetTitles);
                                ?>
                                <select id="trainerTitleSelect" class="form-select">
                                    <option value="គ្រូបណ្តុះបណ្តាល" <?php echo $currentTitle === 'គ្រូបណ្តុះបណ្តាល' ? 'selected' : ''; ?>>គ្រូបណ្តុះបណ្តាល</option>
                                    <option value="វាគ្មិនកិត្តិយស" <?php echo $currentTitle === 'វាគ្មិនកិត្តិយស' ? 'selected' : ''; ?>>វាគ្មិនកិត្តិយស</option>
                                    <option value="វាគ្មិន" <?php echo $currentTitle === 'វាគ្មិន' ? 'selected' : ''; ?>>វាគ្មិន</option>
                                    <option value="អ្នកសម្របសម្រួល" <?php echo $currentTitle === 'អ្នកសម្របសម្រួល' ? 'selected' : ''; ?>>អ្នកសម្របសម្រួល</option>
                                    <option value="custom" <?php echo !$isPreset ? 'selected' : ''; ?>>ផ្សេងៗ (កំណត់ដោយខ្លួនឯង)...</option>
                                </select>
                                <input type="text" name="trainer_title" id="trainerTitleInput" class="form-control mt-2 <?php echo $isPreset ? 'd-none' : ''; ?>" placeholder="វាយបញ្ចូលងារ..." value="<?php echo e($currentTitle); ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ឈ្មោះវាគ្មិន / គ្រូបណ្តុះបណ្តាល</label>
                                <input type="text" name="trainer_name" class="form-control" placeholder="ឧទាហរណ៍៖ ឯកឧត្តម គីម ដល្លា..." value="<?php echo e($workshop['trainer_name'] ?? $old['trainer_name'] ?? ''); ?>">
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">អ្នករៀបចំ / ផ្នែកទទួលបន្ទុក</label>
                                <input type="text" name="organizer" class="form-control" placeholder="ឧទាហរណ៍៖ វិទ្យាស្ថានបណ្តុះបណ្តាល KSH" value="<?php echo e($workshop['organizer'] ?? $old['organizer'] ?? ''); ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">កាលបរិច្ឆេទចាប់ផ្តើម <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required value="<?php echo e($workshop['start_date'] ?? $old['start_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">កាលបរិច្ឆេទបញ្ចប់</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo e($workshop['end_date'] ?? $old['end_date'] ?? date('Y-m-d', strtotime('+7 days'))); ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">ម៉ោងចាប់ផ្តើម</label>
                                <input type="time" name="start_time" class="form-control" value="<?php echo e($workshop['start_time'] ?? $old['start_time'] ?? '09:00'); ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">ម៉ោងបញ្ចប់</label>
                                <input type="time" name="end_time" class="form-control" value="<?php echo e($workshop['end_time'] ?? $old['end_time'] ?? '17:00'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ឈ្មោះទីកន្លែងរៀបចំ</label>
                                <input type="text" name="venue" class="form-control" placeholder="ឧទាហរណ៍៖ សាលសន្និសីទធំ សណ្ឋាគារសុខា ភ្នំពេញ" value="<?php echo e($workshop['venue'] ?? $old['venue'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">ចំណុះសិក្ខាកាមអតិបរមា (Capacity) <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" id="capacityInput" class="form-control" min="1" max="10000" required value="<?php echo e($workshop['capacity'] ?? $old['capacity'] ?? '100'); ?>">
                                <div class="form-text text-primary" id="platformFeeNotice"><i class="bi bi-tag-fill me-1"></i> ថ្លៃសេវាប្រព័ន្ធគណនាដោយស្វ័យប្រវត្តិតាមចំនួនចំណុះ។</div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">អាសយដ្ឋានពេញលេញ</label>
                                <input type="text" name="address" class="form-control" placeholder="អាសយដ្ឋានទីតាំងរៀបចំកម្មវិធី" value="<?php echo e($workshop['address'] ?? $old['address'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">តំណភ្ជាប់ Google Maps</label>
                                <input type="url" name="google_maps_url" class="form-control" placeholder="https://maps.google.com/..." value="<?php echo e($workshop['google_maps_url'] ?? $old['google_maps_url'] ?? ''); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">តំណភ្ជាប់ប្រជុំអនឡាញ (Zoom / Meet)</label>
                                <input type="url" name="online_meeting_url" class="form-control" placeholder="https://zoom.us/j/..." value="<?php echo e($workshop['online_meeting_url'] ?? $old['online_meeting_url'] ?? ''); ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">របៀបទូទាត់សម្រាប់សិក្ខាកាម</label>
                                <select name="payment_mode" class="form-select">
                                    <option value="free" <?php echo (($workshop['payment_mode'] ?? '') === 'free') ? 'selected' : ''; ?>>ចុះឈ្មោះឥតគិតថ្លៃ ($0)</option>
                                    <option value="paid" <?php echo (($workshop['payment_mode'] ?? '') === 'paid') ? 'selected' : ''; ?>>បង់ប្រាក់ថ្លៃសំបុត្រ</option>
                                    <option value="freemium" <?php echo (($workshop['payment_mode'] ?? '') === 'freemium') ? 'selected' : ''; ?>>ឥតគិតថ្លៃ + ជម្រើសបង់ប្រាក់បន្ថែម</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ភាពមើលឃើញ</label>
                                <select name="visibility" class="form-select">
                                    <option value="public" <?php echo (($workshop['visibility'] ?? '') === 'public') ? 'selected' : ''; ?>>សាធារណៈ (បង្ហាញលើគេហទំព័រ)</option>
                                    <option value="unlisted" <?php echo (($workshop['visibility'] ?? '') === 'unlisted') ? 'selected' : ''; ?>>មិនបង្ហាញ (តាមរយៈតំណភ្ជាប់ផ្ទាល់)</option>
                                    <option value="private" <?php echo (($workshop['visibility'] ?? '') === 'private') ? 'selected' : ''; ?>>ផ្ទៃក្នុងស្ថាប័ន (Internal Only)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">ភាសាសិក្ខាសាលា</label>
                                <input type="text" name="language" class="form-control" value="<?php echo e($workshop['language'] ?? 'ភាសាខ្មែរ'); ?>">
                            </div>

                            <div class="col-md-12 pt-2">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="allow_waitlist" id="allow_waitlist" value="1" <?php echo (!isset($workshop['allow_waitlist']) || $workshop['allow_waitlist']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="allow_waitlist">អនុញ្ញាតឱ្យចុះឈ្មោះក្នុងបញ្ជីរង់ចាំ (Waitlist) ពេលពេញចំណុះ</label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="auto_confirm" id="auto_confirm" value="1" <?php echo (!empty($workshop['auto_confirm'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_confirm">បញ្ជាក់ការចុះឈ្មោះដោយស្វ័យប្រវត្តិ (ចេញ QR ភ្លាមៗចំពោះសំបុត្រឥតគិតថ្លៃ)</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="requires_approval" id="requires_approval" value="1" <?php echo (!empty($workshop['requires_approval'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="requires_approval">តម្រូវឱ្យអ្នករៀបចំអនុម័តដោយដៃមុនពេលបញ្ជាក់ការចុះឈ្មោះ</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <span>បន្ទាប់: ជំហានទី ២ (ការរចនា)</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                <?php elseif ($step === 2): ?>
                    <!-- STEP 2: BRANDING -->
                    <h4 class="card-title fw-bold mb-3"><i class="bi bi-palette me-2 text-primary"></i> ជំហានទី ២: ការរចនា និងស្លាកសញ្ញាសិក្ខាសាលា</h4>
                    <form method="POST" action="<?php echo APP_URL; ?>/workshops/create" enctype="multipart/form-data" id="workshopStepForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="step" value="2">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop['id']; ?>">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">រូបភាពគម្របសិក្ខាសាលា (Cover Image)</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <div class="form-text">ទំហំដែលណែនាំ: 1200x630px JPG ឬ PNG (អតិបរមា 5MB)។</div>
                                <?php if (!empty($branding['cover_image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo APP_URL . '/' . e($branding['cover_image']); ?>" class="img-thumbnail" style="max-height: 120px;" alt="Cover">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ឡូហ្គោសិក្ខាសាលា (Logo)</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <div class="form-text">ឡូហ្គោការ៉េ: 400x400px (អតិបរមា 2MB)។</div>
                                <?php if (!empty($branding['logo'])): ?>
                                    <div class="mt-2">
                                        <img src="<?php echo APP_URL . '/' . e($branding['logo']); ?>" class="img-thumbnail" style="max-height: 80px;" alt="Logo">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ពណ៌ចម្បងនៃប្រធានបទ (Theme Color)</label>
                                <div class="input-group">
                                    <input type="color" name="theme_color" class="form-control form-control-color" value="<?php echo e($branding['theme_color'] ?? '#0d6efd'); ?>" title="ជ្រើសរើសពណ៌">
                                    <input type="text" class="form-control" value="<?php echo e($branding['theme_color'] ?? '#0d6efd'); ?>" readonly>
                                </div>
                                <div class="form-text">ពណ៌នេះនឹងត្រូវប្រើលើប៊ូតុង ស្លាកសញ្ញា និងផ្នែកសំខាន់ៗនៃទំព័រសាធារណៈ។</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">ផ្ទាំងបដាព្រឹត្តិការណ៍ (Banner - ជម្រើស)</label>
                                <input type="file" name="banner" class="form-control" accept="image/*">
                                <div class="form-text">ផ្ទាំងបដាទទឹងវែងសម្រាប់ផ្នែកខាងលើនៃទំព័រ (អតិបរមា 5MB)។</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="<?php echo APP_URL; ?>/workshops/create?step=1&workshop_id=<?php echo $workshop['id']; ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <i class="bi bi-arrow-left"></i> <span>ថយក្រោយ: ជំហានទី ១</span>
                            </a>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <span>បន្ទាប់: ជំហានទី ៣ (ទម្រង់ចុះឈ្មោះ)</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                <?php elseif ($step === 3): ?>
                    <!-- STEP 3: REGISTRATION FIELDS -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title fw-bold mb-0"><i class="bi bi-ui-checks me-2 text-primary"></i> ជំហានទី ៣: កំណត់ទម្រង់ព័ត៌មានចុះឈ្មោះ</h4>
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" id="addFieldBtn">
                            <i class="bi bi-plus-lg"></i> <span>បន្ថែមប្រអប់ព័ត៌មានថ្មី</span>
                        </button>
                    </div>
                    <p class="text-muted small">ប្រព័ន្ធប្រមូលឈ្មោះពេញ អ៊ីមែល និងលេខទូរស័ព្ទជាស្វ័យប្រវត្តិរួចហើយ។ អ្នកអាចបន្ថែមប្រអប់ព័ត៌មានផ្សេងៗទៀតខាងក្រោម:</p>

                    <form method="POST" action="<?php echo APP_URL; ?>/workshops/create" id="workshopStepForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="step" value="3">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop['id']; ?>">

                        <div id="fieldsContainer">
                            <?php if (empty($customFields)): ?>
                                <!-- Default suggested fields -->
                                <div class="card bg-light border p-3 mb-3 field-row">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">ឈ្មោះប្រអប់ (Field Label)</label>
                                            <input type="text" name="fields[0][label]" class="form-control" value="ក្រុមហ៊ុន / ស្ថាប័ន" placeholder="ឈ្មោះប្រអប់ (Field Label)" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">ប្រភេទប្រអប់</label>
                                            <select name="fields[0][type]" class="form-select">
                                                <option value="text" selected>អក្សរខ្លី (Text Input)</option>
                                                <option value="dropdown">ជម្រើសទម្លាក់ចុះ (Dropdown Select)</option>
                                                <option value="textarea">អត្ថបទវែង (Multi-line Text)</option>
                                                <option value="number">លេខ (Number)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pt-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="fields[0][required]" value="1" checked>
                                                <label class="form-check-label">តម្រូវឱ្យបំពេញ (Required)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-end pt-md-4">
                                            <button type="button" class="btn btn-sm btn-danger remove-field-btn"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-light border p-3 mb-3 field-row">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">ឈ្មោះប្រអប់ (Field Label)</label>
                                            <input type="text" name="fields[1][label]" class="form-control" value="តួនាទី / មុខតំណែង" placeholder="ឈ្មោះប្រអប់ (Field Label)" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">ប្រភេទប្រអប់</label>
                                            <select name="fields[1][type]" class="form-select">
                                                <option value="text" selected>អក្សរខ្លី (Text Input)</option>
                                                <option value="dropdown">ជម្រើសទម្លាក់ចុះ (Dropdown Select)</option>
                                                <option value="textarea">អត្ថបទវែង (Multi-line Text)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 pt-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="fields[1][required]" value="1">
                                                <label class="form-check-label">តម្រូវឱ្យបំពេញ (Required)</label>
                                            </div>
                                        </div>
                                        <div class="col-md-2 text-end pt-md-4">
                                            <button type="button" class="btn btn-sm btn-danger remove-field-btn"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($customFields as $idx => $f): ?>
                                    <div class="card bg-light border p-3 mb-3 field-row">
                                        <input type="hidden" name="fields[<?php echo $idx; ?>][id]" value="<?php echo $f['id']; ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">ឈ្មោះប្រអប់ (Field Label)</label>
                                                <input type="text" name="fields[<?php echo $idx; ?>][label]" class="form-control" value="<?php echo e($f['field_label']); ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">ប្រភេទប្រអប់</label>
                                                <select name="fields[<?php echo $idx; ?>][type]" class="form-select">
                                                    <option value="text" <?php echo $f['field_type'] === 'text' ? 'selected' : ''; ?>>អក្សរខ្លី (Text)</option>
                                                    <option value="dropdown" <?php echo $f['field_type'] === 'dropdown' ? 'selected' : ''; ?>>ជម្រើសទម្លាក់ចុះ (Dropdown)</option>
                                                    <option value="textarea" <?php echo $f['field_type'] === 'textarea' ? 'selected' : ''; ?>>អត្ថបទវែង (Textarea)</option>
                                                    <option value="number" <?php echo $f['field_type'] === 'number' ? 'selected' : ''; ?>>លេខ (Number)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 pt-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="fields[<?php echo $idx; ?>][required]" value="1" <?php echo $f['is_required'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label">តម្រូវឱ្យបំពេញ</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2 text-end pt-md-4">
                                                <button type="button" class="btn btn-sm btn-danger remove-field-btn"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="<?php echo APP_URL; ?>/workshops/create?step=2&workshop_id=<?php echo $workshop['id']; ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <i class="bi bi-arrow-left"></i> <span>ថយក្រោយ: ជំហានទី ២</span>
                            </a>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <span>បន្ទាប់: ជំហានទី ៤ (ប្រភេទសំបុត្រ)</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                <?php elseif ($step === 4): ?>
                    <!-- STEP 4: TICKETS -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title fw-bold mb-0"><i class="bi bi-ticket-perforated me-2 text-primary"></i> ជំហានទី ៤: ប្រភេទសំបុត្រ និងតម្លៃ</h4>
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center" id="addTicketBtn">
                            <i class="bi bi-plus-lg"></i> <span>បន្ថែមប្រភេទសំបុត្រថ្មី</span>
                        </button>
                    </div>

                    <form method="POST" action="<?php echo APP_URL; ?>/workshops/create" id="workshopStepForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="step" value="4">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop['id']; ?>">

                        <div id="ticketsContainer">
                            <?php if (empty($tickets)): ?>
                                <div class="card bg-light border p-3 mb-3 ticket-row">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">ឈ្មោះសំបុត្រ</label>
                                            <input type="text" name="tickets[0][name]" class="form-control" value="សំបុត្រទូទៅ (Standard Ticket)" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">ប្រភេទសំបុត្រ</label>
                                            <select name="tickets[0][type]" class="form-select">
                                                <option value="standard" selected>ទូទៅ (Standard)</option>
                                                <option value="vip">ពិសេស (VIP)</option>
                                                <option value="early_bird">ចុះឈ្មោះមុន (Early Bird)</option>
                                                <option value="student">សិស្ស/និស្សិត (Student)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">តម្លៃ ($ ដុល្លារ)</label>
                                            <input type="number" step="0.01" name="tickets[0][price]" class="form-control" value="<?php echo ($workshop['payment_mode'] === 'free') ? '0.00' : '25.00'; ?>" required>
                                        </div>
                                        <div class="col-md-2 text-end pt-3">
                                            <button type="button" class="btn btn-sm btn-danger remove-ticket-btn"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($tickets as $idx => $t): ?>
                                    <div class="card bg-light border p-3 mb-3 ticket-row">
                                        <input type="hidden" name="tickets[<?php echo $idx; ?>][id]" value="<?php echo $t['id']; ?>">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">ឈ្មោះសំបុត្រ</label>
                                                <input type="text" name="tickets[<?php echo $idx; ?>][name]" class="form-control" value="<?php echo e($t['name']); ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">ប្រភេទសំបុត្រ</label>
                                                <select name="tickets[<?php echo $idx; ?>][type]" class="form-select">
                                                    <option value="standard" <?php echo $t['ticket_type'] === 'standard' ? 'selected' : ''; ?>>ទូទៅ (Standard)</option>
                                                    <option value="vip" <?php echo $t['ticket_type'] === 'vip' ? 'selected' : ''; ?>>ពិសេស (VIP)</option>
                                                    <option value="early_bird" <?php echo $t['ticket_type'] === 'early_bird' ? 'selected' : ''; ?>>ចុះឈ្មោះមុន (Early Bird)</option>
                                                    <option value="student" <?php echo $t['ticket_type'] === 'student' ? 'selected' : ''; ?>>សិស្ស/និស្សិត (Student)</option>
                                                    <option value="complimentary" <?php echo $t['ticket_type'] === 'complimentary' ? 'selected' : ''; ?>>អញ្ជើញពិសេស (Complimentary)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">តម្លៃ ($ ដុល្លារ)</label>
                                                <input type="number" step="0.01" name="tickets[<?php echo $idx; ?>][price]" class="form-control" value="<?php echo number_format($t['price'], 2, '.', ''); ?>" required>
                                            </div>
                                            <div class="col-md-2 text-end pt-3">
                                                <button type="button" class="btn btn-sm btn-danger remove-ticket-btn"><i class="bi bi-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <a href="<?php echo APP_URL; ?>/workshops/create?step=3&workshop_id=<?php echo $workshop['id']; ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <i class="bi bi-arrow-left"></i> <span>ថយក្រោយ: ជំហានទី ៣</span>
                            </a>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <span>បន្ទាប់: ជំហានទី ៥ (ពិនិត្យ & បង់ថ្លៃប្រព័ន្ធ)</span> <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>

                <?php elseif ($step === 5): ?>
                    <!-- STEP 5: REVIEW & ACTIVATE -->
                    <h4 class="card-title fw-bold mb-3"><i class="bi bi-check2-circle me-2 text-success"></i> ជំហានទី ៥: ពិនិត្យឡើងវិញ និងថ្លៃដំណើរការប្រព័ន្ធ (Platform Activation Fee)</h4>

                    <div class="row g-4 mb-4">
                        <div class="col-md-7">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-file-earmark-text me-2"></i>ព័ត៌មានសង្ខេបនៃសិក្ខាសាលា</h5>
                                    <table class="table table-sm table-borderless">
                                        <tr><th class="table-label-blue text-primary w-40">ឈ្មោះសិក្ខាសាលា:</th><td class="fw-bold"><?php echo e($workshop['name']); ?></td></tr>
                                        <tr><th class="table-label-blue text-primary">ចំណុះសិក្ខាកាម:</th><td><span class="badge bg-primary fs-6"><?php echo number_format($workshop['capacity']); ?> នាក់</span></td></tr>
                                        <tr><th class="table-label-blue text-primary">កាលបរិច្ឆេទ:</th><td><?php echo formatDate($workshop['start_date']); ?></td></tr>
                                        <tr><th class="table-label-blue text-primary">ទីកន្លែង:</th><td><?php echo e($workshop['venue'] ?: 'អនឡាញ / មិនទាន់កំណត់'); ?></td></tr>
                                        <tr><th class="table-label-blue text-primary"><?php echo e(!empty($workshop['trainer_title']) ? $workshop['trainer_title'] : 'គ្រូបណ្តុះបណ្តាល'); ?>:</th><td><?php echo e($workshop['trainer_name'] ?: 'មិនបានបញ្ជាក់'); ?></td></tr>
                                        <tr><th class="table-label-blue text-primary">ទម្រង់ទូទាត់:</th><td><?php echo ucfirst($workshop['payment_mode']); ?></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card border border-primary bg-primary-subtle h-100">
                                <div class="card-body text-center p-4">
                                    <h6 class="text-uppercase fw-bold text-primary mb-2">ថ្លៃដំណើរការប្រព័ន្ធតែម្តងគត់ (One-Time Platform Fee)</h6>
                                    <div class="display-4 fw-bold text-dark mb-2">
                                        $<?php echo number_format($billing['platform_fee'] ?? 10.00, 2); ?>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        កម្រិត: <strong><?php echo e($billing['pricing_tier'] ?? 'Standard'); ?></strong><br>
                                        គណនាផ្អែកលើចំណុះសិក្ខាកាម <?php echo number_format($workshop['capacity']); ?> នាក់។
                                    </p>
                                    <div class="alert alert-info py-2 small mb-0 text-start">
                                        <i class="bi bi-info-circle me-1"></i> ប្រាក់ចំណូលពីសំបុត្រសិក្ខាកាមទាំងអស់ជារបស់ស្ថាប័នអ្នក ១០០% ដោយផ្ទាល់!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo APP_URL; ?>/workshops/create" id="workshopStepForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="step" value="5">
                        <input type="hidden" name="workshop_id" value="<?php echo $workshop['id']; ?>">

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-4">
                            <a href="<?php echo APP_URL; ?>/workshops/create?step=4&workshop_id=<?php echo $workshop['id']; ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <i class="bi bi-arrow-left"></i> <span>ថយក្រោយ: ជំហានទី ៤</span>
                            </a>
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center justify-content-center px-4 py-2">
                                <span>បញ្ចប់ & ទៅកាន់ការទូទាត់ប្រព័ន្ធ</span> <i class="bi bi-check-lg"></i>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dynamic fields handling
    const addFieldBtn = document.getElementById('addFieldBtn');
    const fieldsContainer = document.getElementById('fieldsContainer');
    if (addFieldBtn && fieldsContainer) {
        addFieldBtn.addEventListener('click', function() {
            const count = fieldsContainer.querySelectorAll('.field-row').length;
            const html = `
                <div class="card bg-light border p-3 mb-3 field-row">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ឈ្មោះប្រអប់ (Field Label)</label>
                            <input type="text" name="fields[${count}][label]" class="form-control" placeholder="ឈ្មោះប្រអប់ (Field Label)" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">ប្រភេទប្រអប់</label>
                            <select name="fields[${count}][type]" class="form-select">
                                <option value="text">អក្សរខ្លី (Text Input)</option>
                                <option value="dropdown">ជម្រើសទម្លាក់ចុះ (Dropdown)</option>
                                <option value="textarea">អត្ថបទវែង (Textarea)</option>
                                <option value="number">លេខ (Number)</option>
                            </select>
                        </div>
                        <div class="col-md-3 pt-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[${count}][required]" value="1">
                                <label class="form-check-label">តម្រូវឱ្យបំពេញ</label>
                            </div>
                        </div>
                        <div class="col-md-2 text-end pt-md-4">
                            <button type="button" class="btn btn-sm btn-danger remove-field-btn"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            fieldsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // Dynamic tickets handling
    const addTicketBtn = document.getElementById('addTicketBtn');
    const ticketsContainer = document.getElementById('ticketsContainer');
    if (addTicketBtn && ticketsContainer) {
        addTicketBtn.addEventListener('click', function() {
            const count = ticketsContainer.querySelectorAll('.ticket-row').length;
            const html = `
                <div class="card bg-light border p-3 mb-3 ticket-row">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ឈ្មោះសំបុត្រ</label>
                            <input type="text" name="tickets[${count}][name]" class="form-control" placeholder="ឧទាហរណ៍៖ សំបុត្រ VIP" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">ប្រភេទសំបុត្រ</label>
                            <select name="tickets[${count}][type]" class="form-select">
                                <option value="standard">ទូទៅ (Standard)</option>
                                <option value="vip">ពិសេស (VIP)</option>
                                <option value="early_bird">ចុះឈ្មោះមុន (Early Bird)</option>
                                <option value="student">សិស្ស/និស្សិត (Student)</option>
                                <option value="complimentary">អញ្ជើញពិសេស (Complimentary)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">តម្លៃ ($ ដុល្លារ)</label>
                            <input type="number" step="0.01" name="tickets[${count}][price]" class="form-control" value="0.00" required>
                        </div>
                        <div class="col-md-2 text-end pt-3">
                            <button type="button" class="btn btn-sm btn-danger remove-ticket-btn"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>`;
            ticketsContainer.insertAdjacentHTML('beforeend', html);
        });
    }

    // Remove buttons delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-field-btn')) {
            e.target.closest('.field-row').remove();
        }
        if (e.target.closest('.remove-ticket-btn')) {
            e.target.closest('.ticket-row').remove();
        }
    });

    // Trainer Title handling
    const titleSelect = document.getElementById('trainerTitleSelect');
    const titleInput = document.getElementById('trainerTitleInput');
    if (titleSelect && titleInput) {
        titleSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                titleInput.classList.remove('d-none');
                titleInput.value = '';
                titleInput.focus();
            } else {
                titleInput.classList.add('d-none');
                titleInput.value = this.value;
            }
        });
        const form = document.getElementById('workshopStepForm');
        if (form) {
            form.addEventListener('submit', function() {
                if (titleSelect.value !== 'custom') {
                    titleInput.value = titleSelect.value;
                }
            });
        }
    }

    // Top Save Button click handler
    const topSaveBtn = document.getElementById('topSaveBtn');
    if (topSaveBtn) {
        topSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('workshopStepForm');
            if (form) {
                if (titleSelect && titleInput && titleSelect.value !== 'custom') {
                    titleInput.value = titleSelect.value;
                }
                let saveInput = form.querySelector('input[name="save_only"]');
                if (!saveInput) {
                    saveInput = document.createElement('input');
                    saveInput.type = 'hidden';
                    saveInput.name = 'save_only';
                    form.appendChild(saveInput);
                }
                saveInput.value = '1';
                if (form.reportValidity()) {
                    topSaveBtn.disabled = true;
                    topSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> <span>កំពុងរក្សាទុក...</span>';
                    form.submit();
                }
            }
        });
    }
});
</script>
