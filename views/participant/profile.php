<?php
// views/participant/profile.php
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
          <h4 class="mb-0 fw-bold text-primary">
            <i class="bi bi-person-lines-fill me-2"></i>កែប្រែព័ត៌មានផ្ទាល់ខ្លួន
          </h4>
        </div>
        <div class="card-body p-4">
          <form method="POST" action="<?= APP_URL ?>/participant/profile">
            <?= csrfField() ?>

            <div class="mb-3">
              <label class="form-label fw-semibold">ឈ្មោះពេញ <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($participant['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">អ៊ីមែល</label>
              <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($participant['email'] ?? '') ?>" disabled readonly>
              <small class="text-muted">អ៊ីមែលមិនអាចកែប្រែបានទេ</small>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">លេខទូរស័ព្ទ</label>
              <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($participant['phone'] ?? '') ?>">
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">ស្ថាប័ន / ក្រុមហ៊ុន</label>
                <input type="text" name="company" class="form-control" value="<?= htmlspecialchars($participant['company'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">មុខតំណែង</label>
                <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($participant['position'] ?? '') ?>">
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">ខេត្ត / រាជធានី</label>
                <input type="text" name="province" class="form-control" value="<?= htmlspecialchars($participant['province'] ?? '') ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">ក្រុង / ស្រុក</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($participant['city'] ?? '') ?>">
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
              <a href="<?= APP_URL ?>/participant/portal" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>ត្រឡប់ក្រោយ
              </a>
              <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i>រក្សាទុកការផ្លាស់ប្តូរ
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
