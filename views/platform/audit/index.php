<?php
// views/platform/audit/index.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">កំណត់ត្រាសវនកម្មប្រព័ន្ធ (System Audit Logs)</h2>
        <p class="text-muted mb-0">កំណត់ត្រាព្រឹត្តិការណ៍សំខាន់ៗទាំងអស់នៃប្រព័ន្ធ និងស្ថាប័ននានា។</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>កាលបរិច្ឆេទ & ម៉ោង</th>
                    <th>អ្នកប្រើប្រាស់</th>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>សកម្មភាព</th>
                    <th>ទិន្នន័យពាក់ព័ន្ធ</th>
                    <th>អាសយដ្ឋាន IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">មិនទាន់មានកំណត់ត្រាសវនកម្មនៅឡើយទេ។</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?php echo formatDateTime($l['created_at']); ?></td>
                            <td>
                                <div class="fw-bold"><?php echo e($l['user_name'] ?: 'ប្រព័ន្ធ (System)'); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo e($l['user_email'] ?: '-'); ?></div>
                            </td>
                            <td><?php echo e($l['business_name'] ?: 'ប្រព័ន្ធសកល (Global)'); ?></td>
                            <td><span class="badge bg-secondary font-monospace"><?php echo e($l['action']); ?></span></td>
                            <td><code><?php echo e($l['entity_type']); ?> #<?php echo e($l['entity_id']); ?></code></td>
                            <td><span class="text-muted"><?php echo e($l['ip_address']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
