<?php $breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'ការទូទាត់',  'url' => null,                                      'icon' => 'credit-card'],
]; ?>
<div class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold">ការទូទាត់របស់អ្នករៀបចំ - <?= htmlspecialchars($workshop['name']) ?></h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="ស្វែងរកតាមឈ្មោះ អ៊ីមែល ឬលេខកូដយោង..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>រង់ចាំពិនិត្យ</option>
                    <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>បានអនុម័ត</option>
                    <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>បានបដិសេធ</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">ស្វែងរក</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>សិក្ខាកាម</th>
                    <th>ចំនួនទឹកប្រាក់ & វិធីសាស្ត្រ</th>
                    <th>លេខកូដយោង</th>
                    <th>បានដាក់ស្នើ</th>
                    <th>ស្ថានភាព</th>
                    <th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proofs)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">មិនមានបង្កាន់ដៃបង់ប្រាក់នោះទេ។</td></tr>
                <?php endif; ?>
                <?php foreach ($proofs as $p): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($p['participant_name']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($p['participant_email']) ?></div>
                            <div class="small"><span class="badge bg-secondary"><?= htmlspecialchars($p['registration_code']) ?></span></div>
                        </td>
                        <td>
                            <div class="fw-bold">$<?= number_format($p['amount_paid'], 2) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($p['payment_method_name'] ?? 'មិនបានបញ្ជាក់') ?></div>
                        </td>
                        <td><code><?= htmlspecialchars($p['transaction_reference'] ?: 'N/A') ?></code></td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['submitted_at']))) ?></td>
                        <td>
                            <span class="badge bg-<?= $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= APP_URL . htmlspecialchars($p['proof_file_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info">មើលបង្កាន់ដៃ</a>
                            <?php if ($p['status'] === 'pending' && Permission::has('payment.verify')): ?>
                                <button type="button" class="btn btn-sm btn-success" onclick="approveProof(<?= $p['id'] ?>)">អនុម័ត</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="rejectProof(<?= $p['id'] ?>)">បដិសេធ</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function approveProof(id) {
    if (confirm('តើអ្នកប្រាកដថាចង់អនុម័តការទូទាត់នេះមែនទេ? ការចុះឈ្មោះនឹងត្រូវបានបញ្ជាក់ភ្លាមៗ។')) {
        let fd = new FormData();
        fd.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');
        fetch('<?= APP_URL ?>/workshops/<?= $workshopId ?>/payments/' + id + '/approve', {
            method: 'POST',
            body: fd
        }).then(r => r.json()).then(res => {
            if (res.success) location.reload();
            else alert(res.message);
        });
    }
}
function rejectProof(id) {
    let reason = prompt('មូលហេតុនៃការបដិសេធ:');
    if (reason !== null) {
        let fd = new FormData();
        fd.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');
        fd.append('reason', reason);
        fetch('<?= APP_URL ?>/workshops/<?= $workshopId ?>/payments/' + id + '/reject', {
            method: 'POST',
            body: fd
        }).then(r => r.json()).then(res => {
            if (res.success) location.reload();
            else alert(res.message);
        });
    }
}
</script>
