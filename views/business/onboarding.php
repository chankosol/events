<div class="row justify-content-center w-100 my-5">
    <div class="col-md-8 col-lg-7">
        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">សូមស្វាគមន៍មកកាន់ Workshop OS!</h3>
                    <p class="text-muted">សូមរៀបចំការកំណត់ស្ថាប័នរបស់អ្នកតាមរយៈជំហានងាយៗមួយចំនួន។</p>
                </div>

                <!-- Progress Wizard -->
                <div class="wizard-steps">
                    <div class="wizard-step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-circle"><i class="bi <?php echo $step > 1 ? 'bi-check' : 'bi-building'; ?>"></i></div>
                        <div class="step-label">ព័ត៌មានស្ថាប័ន</div>
                    </div>
                    <div class="wizard-step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-circle"><i class="bi <?php echo $step > 2 ? 'bi-check' : 'bi-palette'; ?>"></i></div>
                        <div class="step-label">ស្លាកសញ្ញា</div>
                    </div>
                    <div class="wizard-step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-circle"><i class="bi <?php echo $step > 3 ? 'bi-check' : 'bi-credit-card'; ?>"></i></div>
                        <div class="step-label">ការទូទាត់</div>
                    </div>
                    <div class="wizard-step <?php echo $step >= 4 ? ($step > 4 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-circle"><i class="bi <?php echo $step > 4 ? 'bi-check' : 'bi-bell'; ?>"></i></div>
                        <div class="step-label">ការជូនដំណឹង</div>
                    </div>
                    <div class="wizard-step <?php echo $step == 5 ? 'active' : ''; ?>">
                        <div class="step-circle"><i class="bi bi-flag"></i></div>
                        <div class="step-label">រួចរាល់</div>
                    </div>
                </div>

                <?php if ($flash_error = Session::flash('error')): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo APP_URL; ?>/onboarding">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="step" value="<?php echo $step; ?>">

                    <?php if ($step === 1): ?>
                        <!-- Step 1: Business Info -->
                        <h5 class="mb-4 fw-bold">ផ្ទៀងផ្ទាត់ព័ត៌មានស្ថាប័ន</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ឈ្មោះស្ថាប័ន / ក្រុមហ៊ុន</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($business['name'] ?? ''); ?>" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ប្រភេទអាជីវកម្ម / ស្ថាប័ន</label>
                                <select name="business_type" class="form-select" required>
                                    <option value="Training Institute" <?php echo ($business['business_type'] ?? '') == 'Training Institute' ? 'selected' : ''; ?>>វិទ្យាស្ថានបណ្តុះបណ្តាល (Training Institute)</option>
                                    <option value="Corporate Training" <?php echo ($business['business_type'] ?? '') == 'Corporate Training' ? 'selected' : ''; ?>>ការបណ្តុះបណ្តាលសាជីវកម្ម (Corporate Training)</option>
                                    <option value="Education Center" <?php echo ($business['business_type'] ?? '') == 'Education Center' ? 'selected' : ''; ?>>មជ្ឈមណ្ឌលអប់រំ (Education Center)</option>
                                    <option value="Conference Organizer" <?php echo ($business['business_type'] ?? '') == 'Conference Organizer' ? 'selected' : ''; ?>>អ្នករៀបចំសន្និសីទ (Conference Organizer)</option>
                                    <option value="Other" <?php echo ($business['business_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>ផ្សេងៗ (Other)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ឈ្មោះអ្នកទំនាក់ទំនង</label>
                                <input type="text" name="contact_person" class="form-control" value="<?php echo htmlspecialchars($business['contact_person'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">លេខទូរស័ព្ទ</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($business['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ប្រទេស</label>
                                <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($business['country'] ?? 'កម្ពុជា'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">រាជធានី / ខេត្ត</label>
                            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($business['city'] ?? 'ភ្នំពេញ'); ?>">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">អាសយដ្ឋាន</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($business['address'] ?? ''); ?></textarea>
                        </div>

                    <?php elseif ($step === 2): ?>
                        <!-- Step 2: Branding -->
                        <h5 class="mb-4 fw-bold">រៀបចំស្លាកសញ្ញារបស់អ្នក</h5>
                        <p class="text-muted small mb-4">កំណត់ពណ៌ចម្បងនៃម៉ាកយីហោរបស់អ្នក។ ពណ៌នេះនឹងត្រូវប្រើលើទំព័រសិក្ខាសាលាសាធារណៈ ផតថលសិក្ខាកាម និងវិញ្ញាបនបត្រ។</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label d-block fw-bold">ពណ៌ចម្បង (Primary Color)</label>
                                <input type="color" name="primary_color" class="form-control form-control-color w-100" value="<?php echo htmlspecialchars($branding['primary_color'] ?? '#0d6efd'); ?>" title="ជ្រើសរើសពណ៌ចម្បង">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block fw-bold">ពណ៌បន្ទាប់បន្សំ (Secondary Color)</label>
                                <input type="color" name="secondary_color" class="form-control form-control-color w-100" value="<?php echo htmlspecialchars($branding['secondary_color'] ?? '#6c757d'); ?>" title="ជ្រើសរើសពណ៌បន្ទាប់បន្សំ">
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> អ្នកអាចផ្ទុកឡើងឡូហ្គោស្ថាប័ននៅពេលក្រោយពីម៉ឺនុយ ការកំណត់។
                        </div>

                    <?php elseif ($step === 3): ?>
                        <!-- Step 3: Payments -->
                        <h5 class="mb-4 fw-bold">វិធីសាស្ត្រទទួលប្រាក់ពីសិក្ខាកាម</h5>
                        <p class="text-muted small mb-4">អ្នកអាចកំណត់កូដ QR ធនាគារជាក់ស្តែងនៅពេលក្រោយ។ តើអ្នកចង់បើកដំណើរការទូទាត់សាច់ប្រាក់ ឬផ្ទេរតាមធនាគារដែរឬទេ?</p>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="enable_cash" id="enableCash" checked>
                            <label class="form-check-label" for="enableCash">អនុញ្ញាតបង់សាច់ប្រាក់ផ្ទាល់នៅទីតាំង</label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="enable_transfer" id="enableTransfer" checked>
                            <label class="form-check-label" for="enableTransfer">អនុញ្ញាតផ្ទេរប្រាក់តាមធនាគារ / ស្កេនកូដ QR</label>
                        </div>

                    <?php elseif ($step === 4): ?>
                        <!-- Step 4: Notifications -->
                        <h5 class="mb-4 fw-bold">ចំណូលចិត្តនៃការជូនដំណឹង</h5>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="notif_new_registration" id="notifReg" checked>
                            <label class="form-check-label" for="notifReg">ផ្ញើអ៊ីមែលជូនខ្ញុំនៅពេលមានសិក្ខាកាមថ្មីចុះឈ្មោះ</label>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="notif_payment" id="notifPay" checked>
                            <label class="form-check-label" for="notifPay">ផ្ញើអ៊ីមែលជូនខ្ញុំនៅពេលមានការដាក់ស្នើបង្កាន់ដៃបង់ប្រាក់</label>
                        </div>

                    <?php elseif ($step === 5): ?>
                        <!-- Step 5: Finish -->
                        <div class="text-center py-4">
                            <i class="bi bi-rocket-takeoff text-primary" style="font-size: 4rem;"></i>
                            <h4 class="mt-4 fw-bold">ការរៀបចំត្រូវបានបញ្ចប់រួចរាល់!</h4>
                            <p class="text-muted">ទីកន្លែងធ្វើការរបស់អ្នកបានត្រៀមរួចរាល់។ សូមចុចខាងក្រោមដើម្បីទៅកាន់ផ្ទាំងគ្រប់គ្រង និងចាប់ផ្តើមបង្កើតសិក្ខាសាលាដំបូងរបស់អ្នក។</p>
                        </div>

                    <?php endif; ?>

                    <div class="d-flex justify-content-between mt-5 pt-3 border-top">
                        <?php if ($step > 1): ?>
                            <a href="#" onclick="history.back(); return false;" class="btn btn-light px-4">ថយក្រោយ</a>
                        <?php else: ?>
                            <div></div> <!-- Spacer -->
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary px-5">
                            <?php echo $step === 5 ? 'ទៅកាន់ផ្ទាំងគ្រប់គ្រង' : 'ជំហានបន្ទាប់ <i class="bi bi-arrow-right ms-1"></i>'; ?>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
