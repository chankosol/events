<div class="container-fluid py-4">
    <h2 class="mb-4 fw-bold">សិក្ខាសាលារបស់ខ្ញុំ</h2>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>សិក្ខាសាលា</th>
                        <th>កាលបរិច្ឆេទ</th>
                        <th>ស្ថានភាព</th>
                        <th>ការបង់ប្រាក់</th>
                        <th>សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($workshops)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">មិនមានការចុះឈ្មោះនៅឡើយទេ។</td></tr>
                    <?php endif; ?>
                    <?php foreach ($workshops as $w): ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($w['name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($w['business_name']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($w['start_date']) ?> ដល់ <?= htmlspecialchars($w['end_date']) ?></td>
                            <td>
                                <span class="badge bg-<?= $w['status'] === 'confirmed' ? 'success' : ($w['status'] === 'attended' ? 'info' : 'secondary') ?>">
                                    <?= $w['status'] === 'confirmed' ? 'បានបញ្ជាក់' : ($w['status'] === 'attended' ? 'បានចូលរួម' : 'រង់ចាំ') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $w['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                    <?= $w['payment_status'] === 'paid' ? 'បានបង់ប្រាក់' : 'រង់ចាំបង់ប្រាក់' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= APP_URL ?>/participant/portal/workshop/<?= $w['id'] ?>" class="btn btn-sm btn-primary">ព័ត៌មានលម្អិត</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
