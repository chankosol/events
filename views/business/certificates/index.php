<?php $breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'វិញ្ញាបនបត្រ', 'url' => null,                                    'icon' => 'award'],
]; ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">វិញ្ញាបនបត្រ - <?= htmlspecialchars($workshop['name']) ?></h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#rulesModal">
            <i class="bi bi-gear"></i> លក្ខខណ្ឌទទួលវិញ្ញាបនបត្រ
        </button>
    </div>

    <!-- Eligible Participants -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">សិក្ខាកាមដែលមានសិទ្ធិទទួលបាន</h6>
            <button class="btn btn-sm btn-success" onclick="issueSelected()">ចេញវិញ្ញាបនបត្រដែលបានជ្រើសរើស</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>សិក្ខាកាម</th>
                            <th>កូដចុះឈ្មោះ</th>
                            <th>វត្តមាន</th>
                            <th>ពិន្ទុតេស្ត</th>
                            <th>ការវាយតម្លៃ</th>
                            <th>ស្ថានភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eligible as $reg): ?>
                        <tr>
                            <td>
                                <?php if (!$reg['cert_id']): ?>
                                <input type="checkbox" class="cert-select" value="<?= $reg['id'] ?>">
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($reg['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($reg['email']) ?></small></td>
                            <td><span class="font-monospace"><?= htmlspecialchars($reg['registration_code']) ?></span></td>
                            <td><?= $reg['total_sessions'] > 0 ? round(($reg['sessions_attended']/$reg['total_sessions'])*100) . '%' : ($reg['attended'] ? 'មាន' : 'អវត្តមាន') ?></td>
                            <td><?= $reg['test_score'] !== null ? $reg['test_score'].'%' : 'N/A' ?></td>
                            <td><?= $reg['feedback_count'] ? 'បានបំពេញ' : 'រង់ចាំ' ?></td>
                            <td>
                                <?php if ($reg['cert_id']): ?>
                                    <span class="badge bg-success">បានចេញរួច</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">មានសិទ្ធិទទួល</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($eligible)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">មិនមានសិក្ខាកាមដែលមានសិទ្ធិទទួលវិញ្ញាបនបត្រនៅឡើយទេ។</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Issued Certificates -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">បញ្ជីវិញ្ញាបនបត្រដែលបានចេញរួច</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>លេខវិញ្ញាបនបត្រ</th>
                            <th>សិក្ខាកាម</th>
                            <th>កាលបរិច្ឆេទចេញ</th>
                            <th>ស្ថានភាព</th>
                            <th>សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td><span class="font-monospace fw-bold"><?= htmlspecialchars($cert['certificate_number']) ?></span></td>
                            <td><?= htmlspecialchars($cert['participant_name']) ?></td>
                            <td><?= htmlspecialchars($cert['issued_at']) ?></td>
                            <td><span class="badge bg-<?= $cert['status']=='issued'?'success':'danger' ?>"><?= $cert['status']=='issued' ? 'សកម្ម' : 'ដកហូត' ?></span></td>
                            <td>
                                <a href="<?= APP_URL ?>/certificate/verify/<?= $cert['verification_token'] ?>" target="_blank" class="btn btn-sm btn-info text-white">មើល</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($certificates)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">មិនទាន់មានវិញ្ញាបនបត្រត្រូវបានចេញនៅឡើយទេ។</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.cert-select').forEach(cb => cb.checked = this.checked);
    });

    function issueSelected() {
        const selected = Array.from(document.querySelectorAll('.cert-select:checked')).map(cb => cb.value);
        if (selected.length === 0) return alert('សូមជ្រើសរើសសិក្ខាកាមជាមុនសិន។');
        if (!confirm(`តើអ្នកចង់ចេញវិញ្ញាបនបត្រជូនសិក្ខាកាមចំនួន ${selected.length} នាក់មែនទេ?`)) return;

        const formData = new FormData();
        formData.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');
        selected.forEach(id => formData.append('registration_ids[]', id));

        fetch('<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/certificates/issue', {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(res => {
            alert(res.message);
            if (res.success) location.reload();
        });
    }
</script>
