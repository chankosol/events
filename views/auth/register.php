<div class="row justify-content-center w-100">
    <div class="col-md-8 col-lg-7">
        <div class="card auth-card shadow-sm border-0 my-4">
            <div class="card-body p-5">
                <a href="<?php echo APP_URL; ?>" class="d-block auth-logo">
                    <i class="bi bi-calendar-event-fill me-2"></i><?php echo APP_NAME; ?>
                </a>
                <h4 class="text-center fw-bold mb-1">បង្កើតគណនីក្រុមហ៊ុន / ស្ថាប័ន</h4>
                <p class="text-center text-muted small mb-4">ចាប់ផ្តើមរៀបចំ និងគ្រប់គ្រងសិក្ខាសាលារបស់អ្នកយ៉ាងមានវិជ្ជាជីវៈ</p>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($errors['general']); ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo APP_URL; ?>/register">
                    <?php echo csrfField(); ?>
                    
                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-building me-1"></i> ១. ព័ត៌មានក្រុមហ៊ុន / ស្ថាប័ន
                    </h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះក្រុមហ៊ុន ឬស្ថាប័ន <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control <?php echo isset($errors['company_name']) ? 'is-invalid' : ''; ?>" placeholder="ឧ. KSH Training Institute" value="<?php echo htmlspecialchars($old['company_name'] ?? ''); ?>" required>
                            <?php if(isset($errors['company_name'])): ?><div class="invalid-feedback"><?php echo $errors['company_name']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ប្រភេទអាជីវកម្ម / ស្ថាប័ន <span class="text-danger">*</span></label>
                            <select name="business_type" class="form-select <?php echo isset($errors['business_type']) ? 'is-invalid' : ''; ?>" required>
                                <option value="">ជ្រើសរើសប្រភេទ...</option>
                                <option value="Training Institute" <?php echo ($old['business_type'] ?? '') == 'Training Institute' ? 'selected' : ''; ?>>វិទ្យាស្ថានបណ្តុះបណ្តាល (Training Institute)</option>
                                <option value="Corporate Training" <?php echo ($old['business_type'] ?? '') == 'Corporate Training' ? 'selected' : ''; ?>>វគ្គបណ្តុះបណ្តាលក្រុមហ៊ុន (Corporate Training)</option>
                                <option value="Education Center" <?php echo ($old['business_type'] ?? '') == 'Education Center' ? 'selected' : ''; ?>>មជ្ឈមណ្ឌលអប់រំ (Education Center)</option>
                                <option value="Conference Organizer" <?php echo ($old['business_type'] ?? '') == 'Conference Organizer' ? 'selected' : ''; ?>>អ្នករៀបចំសន្និសីទ / សិក្ខាសាលា (Event Organizer)</option>
                                <option value="Other" <?php echo ($old['business_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>ផ្សេងៗ (Other)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ប្រទេស <span class="text-danger">*</span></label>
                            <select name="country" class="form-select <?php echo isset($errors['country']) ? 'is-invalid' : ''; ?>" required>
                                <option value="Cambodia" selected>កម្ពុជា (Cambodia)</option>
                                <option value="USA">សហរដ្ឋអាមេរិក (USA)</option>
                                <option value="Thailand">ថៃ (Thailand)</option>
                                <option value="Singapore">សិង្ហបុរី (Singapore)</option>
                            </select>
                            <?php if(isset($errors['country'])): ?><div class="invalid-feedback"><?php echo $errors['country']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">រាជធានី / ខេត្ត</label>
                            <input type="text" name="city" class="form-control" placeholder="ឧ. ភ្នំពេញ" value="<?php echo htmlspecialchars($old['city'] ?? 'ភ្នំពេញ'); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">អាសយដ្ឋាន</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="អាសយដ្ឋានទីតាំងក្រុមហ៊ុន..."><?php echo htmlspecialchars($old['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">គេហទំព័រ (Website)</label>
                        <input type="url" name="website" class="form-control" placeholder="https://example.com" value="<?php echo htmlspecialchars($old['website'] ?? ''); ?>">
                    </div>

                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">
                        <i class="bi bi-person-badge me-1"></i> ២. ព័ត៌មានអ្នកគ្រប់គ្រង និងគណនី
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ឈ្មោះអ្នកទំនាក់ទំនងផ្ទាល់ <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control <?php echo isset($errors['contact_person']) ? 'is-invalid' : ''; ?>" placeholder="ឧ. សុខ ពិសិដ្ឋ" value="<?php echo htmlspecialchars($old['contact_person'] ?? ''); ?>" required>
                            <?php if(isset($errors['contact_person'])): ?><div class="invalid-feedback"><?php echo $errors['contact_person']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">លេខទូរស័ព្ទ <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" placeholder="ឧ. 012 345 678" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" required>
                            <?php if(isset($errors['phone'])): ?><div class="invalid-feedback"><?php echo $errors['phone']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">អាសយដ្ឋានអ៊ីមែល (សម្រាប់ចូលប្រព័ន្ធ) <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" placeholder="manager@company.com" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" required>
                        <?php if(isset($errors['email'])): ?><div class="invalid-feedback"><?php echo $errors['email']; ?></div><?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ពាក្យសម្ងាត់ <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" placeholder="យ៉ាងតិច ៨ តួអក្សរ" required>
                            <?php if(isset($errors['password'])): ?><div class="invalid-feedback"><?php echo $errors['password']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">បញ្ជាក់ពាក្យសម្ងាត់ <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" placeholder="វាយពាក្យសម្ងាត់ម្តងទៀត" required>
                            <?php if(isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div><?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">ភាសាពេញចិត្ត</label>
                            <select name="preferred_language" class="form-select">
                                <option value="km" selected>ភាសាខ្មែរ (Khmer)</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">រូបិយប័ណ្ណចម្បង</label>
                            <select name="preferred_currency" class="form-select">
                                <option value="USD" selected>ដុល្លារអាមេរិក (USD $)</option>
                                <option value="KHR">ប្រាក់រៀល (KHR ៛)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" name="terms" class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>" id="terms" required checked>
                        <label class="form-check-label small text-muted" for="terms">ខ្ញុំយល់ព្រមតាមលក្ខខណ្ឌប្រើប្រាស់ និងគោលការណ៍ឯកជនភាពរបស់ Workshop OS។</label>
                        <?php if(isset($errors['terms'])): ?><div class="invalid-feedback"><?php echo $errors['terms']; ?></div><?php endif; ?>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> បង្កើតគណនីក្រុមហ៊ុន
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-0 small text-muted">មានគណនីរួចហើយ? <a href="<?php echo APP_URL; ?>/login" class="text-decoration-none fw-bold text-primary">ចូលប្រព័ន្ធនៅទីនេះ</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
