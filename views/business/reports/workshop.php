<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-left"></i> ត្រឡប់ទៅរបាយការណ៍</a>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">របាយការណ៍សរុប៖ <?= htmlspecialchars($report['workshop']['name']) ?></h1>
        </div>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> បោះពុម្ព / ទាញយក PDF
        </button>
    </div>

    <!-- Executive Summary -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6>ចំណូលសរុប</h6>
                    <h3 class="fw-bold mb-0">$<?= number_format((float)($report['payments']['collected'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6>អត្រាវត្តមាន</h6>
                    <h3 class="fw-bold mb-0"><?= ((int)($report['registrations']['confirmed'] ?? 0) > 0) ? round(((int)($report['attendance']['checked_in'] ?? 0) / (int)($report['registrations']['confirmed'] ?? 1)) * 100) : 0 ?>%</h3>
                    <small><?= (int)($report['attendance']['checked_in'] ?? 0) ?> នាក់ នៃ <?= (int)($report['registrations']['confirmed'] ?? 0) ?> នាក់ដែលបានបញ្ជាក់</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6>ពិន្ទុវាយតម្លៃមធ្យម</h6>
                    <h3 class="fw-bold mb-0"><?= number_format((float)($report['feedback']['avg_rating'] ?? 0), 1) ?> / 5.0</h3>
                    <small><?= (int)($report['feedback']['responses'] ?? 0) ?> ចម្លើយវាយតម្លៃ</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6>វិញ្ញាបនបត្របានចេញ</h6>
                    <h3 class="fw-bold mb-0"><?= (int)($report['certificates']['issued'] ?? 0) ?> ច្បាប់</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Financial Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3"><h6 class="m-0 font-weight-bold text-primary">សង្ខេបហិរញ្ញវត្ថុសិក្ខាសាលា</h6></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ចំណូលរំពឹងទុក <span class="badge bg-secondary rounded-pill">$<?= number_format((float)($report['payments']['expected'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ប្រមូលបានជាក់ស្តែង <span class="badge bg-success rounded-pill">$<?= number_format((float)($report['payments']['collected'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            រង់ចាំការទូទាត់ <span class="badge bg-warning rounded-pill">$<?= number_format((float)($report['payments']['pending'] ?? 0), 2) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            នៅសល់មិនទាន់បង់ <span class="badge bg-danger rounded-pill">$<?= number_format((float)($report['payments']['outstanding'] ?? 0), 2) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Registration Funnel -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3"><h6 class="m-0 font-weight-bold text-primary">ដំណើរការចុះឈ្មោះ (Registration Funnel)</h6></div>
                <div class="card-body">
                    <?php 
                    $totalReg = (int)($report['registrations']['total'] ?? 0);
                    $confCount = (int)($report['registrations']['confirmed'] ?? 0);
                    $attCount = (int)($report['attendance']['checked_in'] ?? 0);
                    $confPct = $totalReg > 0 ? ($confCount / $totalReg) * 100 : 0;
                    $attPct = $totalReg > 0 ? ($attCount / $totalReg) * 100 : 0;
                    ?>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar" style="width: 100%">ចុះឈ្មោះសរុប (<?= $totalReg ?>)</div>
                    </div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" style="width: <?= $confPct ?>%">បានបញ្ជាក់ (<?= $confCount ?>)</div>
                    </div>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-info" style="width: <?= $attPct ?>%">បានចូលរួម (<?= $attCount ?>)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            .btn, .navbar, .sidebar { display: none !important; }
            body { background-color: #fff; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>
</div>
