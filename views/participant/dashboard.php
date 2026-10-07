<div class="container-fluid py-4">
    <h2 class="mb-4 fw-bold">សូមស្វាគមន៍មកវិញ, <?= htmlspecialchars(Auth::participant()['name']) ?></h2>
    
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-3 fw-bold">សិក្ខាសាលាដែលនឹងមកដល់</h4>
            <?php if (empty($upcomingWorkshops)): ?>
                <div class="alert alert-info">អ្នកមិនទាន់មានសិក្ខាសាលាដែលត្រូវចូលរួមនៅឡើយទេ។</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($upcomingWorkshops as $w): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?= htmlspecialchars($w['name']) ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($w['business_name']) ?></h6>
                                    <p class="card-text mb-1"><i class="bi bi-calendar me-1"></i> <?= htmlspecialchars($w['start_date']) ?></p>
                                    <p class="card-text"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($w['location']) ?></p>
                                    <span class="badge bg-<?= $w['status'] === 'confirmed' ? 'success' : 'warning' ?>"><?= $w['status'] === 'confirmed' ? 'បានបញ្ជាក់' : 'រង់ចាំ' ?></span>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <a href="<?= APP_URL ?>/participant/portal/workshop/<?= $w['id'] ?>" class="btn btn-outline-primary btn-sm w-100">មើលព័ត៌មានលម្អិត</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">តំណភ្ជាប់រហ័ស</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?= APP_URL ?>/participant/portal/workshops" class="list-group-item list-group-item-action"><i class="bi bi-list-check me-2 text-primary"></i> សិក្ខាសាលារបស់ខ្ញុំទាំងអស់</a>
                    <a href="<?= APP_URL ?>/participant/portal/profile" class="list-group-item list-group-item-action"><i class="bi bi-person me-2 text-primary"></i> កែប្រែព័ត៌មានផ្ទាល់ខ្លួន</a>
                </div>
            </div>
        </div>
    </div>
</div>
