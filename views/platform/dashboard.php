<div class="container-fluid py-4">
    <h1 class="h3 mb-4 fw-bold">ផ្ទាំងព័ត៌មានអ្នកគ្រប់គ្រងប្រព័ន្ធ (Platform Dashboard)</h1>
    
    <div class="row mb-4 g-3">
        <div class="col-md-4 col-lg-2">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">ស្ថាប័ន / ក្រុមហ៊ុន</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['active_businesses'] ?> / <?= $stats['total_businesses'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-success text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">សិក្ខាសាលាទាំងអស់</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['active_workshops'] ?> / <?= $stats['total_workshops'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-info text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">សិក្ខាកាមសរុប</h6>
                    <h3 class="mb-0 fw-bold"><?= number_format($stats['total_participants']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-warning text-dark h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">ចំណូលថ្លៃប្រព័ន្ធសរុប</h6>
                    <h3 class="mb-0 fw-bold">$<?= number_format($stats['platform_revenue'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-danger text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">បង្កាន់ដៃរង់ចាំពិនិត្យ</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['pending_payments'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-secondary text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">ចំណូលប្រចាំខែនេះ</h6>
                    <h3 class="mb-0 fw-bold">$<?= number_format($stats['this_month_revenue'], 2) ?></h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">ប្រាក់ចំណូលប្រចាំខែ</h5>
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">តំណភ្ជាប់រហ័ស</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= APP_URL ?>/platform/pricing" class="btn btn-outline-primary py-2 text-start"><i class="bi bi-tags me-2"></i> គ្រប់គ្រងតារាងតម្លៃប្រព័ន្ធ</a>
                        <a href="<?= APP_URL ?>/platform/businesses" class="btn btn-outline-secondary py-2 text-start"><i class="bi bi-building me-2"></i> គ្រប់គ្រងបញ្ជីស្ថាប័ន</a>
                        <a href="<?= APP_URL ?>/platform/audit-logs" class="btn btn-outline-info py-2 text-start"><i class="bi bi-journal-text me-2"></i> កំណត់ត្រាសវនកម្ម</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold">បង្កាន់ដៃបង់ថ្លៃប្រព័ន្ធដែលរង់ចាំពិនិត្យ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ស្ថាប័ន</th>
                                    <th>ចំនួនទឹកប្រាក់</th>
                                    <th>កាលបរិច្ឆេទ</th>
                                    <th>សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingProofs as $proof): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($proof['business_name']) ?></td>
                                    <td class="text-success fw-bold">$<?= number_format($proof['platform_fee'], 2) ?></td>
                                    <td><?= htmlspecialchars($proof['submitted_at']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="approveProof(<?= $proof['id'] ?>)">អនុម័ត</button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectProof(<?= $proof['id'] ?>)">បដិសេធ</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($pendingProofs)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">មិនមានបង្កាន់ដៃរង់ចាំនោះទេ។</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">ស្ថាប័នដែលបានចុះឈ្មោះថ្មីៗ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ឈ្មោះស្ថាប័ន</th>
                                    <th>អ៊ីមែល</th>
                                    <th>សិក្ខាសាលា</th>
                                    <th>ថ្ងៃចូលរួម</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBusinesses as $biz): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($biz['name']) ?></td>
                                    <td><?= htmlspecialchars($biz['email']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $biz['workshop_count'] ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($biz['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentBusinesses)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">មិនទាន់មានស្ថាប័ននៅឡើយទេ។</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const revData = <?= json_encode($monthlyRevenue ?? []) ?>;
    const labels = revData.map(d => d.month);
    const data = revData.map(d => d.revenue);

    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'ប្រាក់ចំណូល ($)',
                data: data,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    function approveProof(id) {
        if (!confirm('តើអ្នកពិតជាចង់អនុម័តការទូទាត់នេះ និងបើកដំណើរការសិក្ខាសាលាមែនទេ?')) return;
        fetch(`<?= APP_URL ?>/platform/billing/approve/${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_csrf_token=<?= Session::get('_csrf_token') ?>`
        }).then(r => r.json()).then(res => {
            alert(res.message);
            if(res.success) location.reload();
        });
    }

    function rejectProof(id) {
        const reason = prompt('មូលហេតុនៃការបដិសេធ:');
        if (!reason) return;
        fetch(`<?= APP_URL ?>/platform/billing/reject/${id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `_csrf_token=<?= Session::get('_csrf_token') ?>&reason=${encodeURIComponent(reason)}`
        }).then(r => r.json()).then(res => {
            alert(res.message);
            if(res.success) location.reload();
        });
    }
</script>
