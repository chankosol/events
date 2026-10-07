<?php
// views/platform/billing.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold mb-1">ផ្ទៀងផ្ទាត់ការទូទាត់ថ្លៃប្រព័ន្ធ (Platform Payment Proofs)</h2>
        <p class="text-muted mb-0">ពិនិត្យ និងអនុម័តបង្កាន់ដៃបង់ប្រាក់ថ្លៃដំណើរការសិក្ខាសាលាពីម្ចាស់កម្មវិធី (Host / Business)។</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-receipt-cutoff me-2 text-primary"></i> បញ្ជីបង្កាន់ដៃបង់ប្រាក់រង់ចាំការផ្ទៀងផ្ទាត់</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>កាលបរិច្ឆេទ</th>
                    <th>ស្ថាប័ន / ក្រុមហ៊ុន</th>
                    <th>សិក្ខាសាលា</th>
                    <th>ទឹកប្រាក់ដែលត្រូវបង់</th>
                    <th>វិធីសាស្ត្រទូទាត់</th>
                    <th>លេខយោងប្រតិបត្តិការ</th>
                    <th>បង្កាន់ដៃ</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($proofs)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">មិនមានបង្កាន់ដៃបង់ប្រាក់នោះទេ។</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($proofs as $p): ?>
                        <tr>
                            <td><small class="text-muted"><?php echo htmlspecialchars($p['submitted_at']); ?></small></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($p['business_name']); ?></td>
                            <td><?php echo htmlspecialchars($p['workshop_name']); ?></td>
                            <td class="fw-bold text-success">$<?php echo number_format($p['platform_fee'], 2); ?> <?php echo htmlspecialchars($p['currency']); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['payment_method'] ?? 'Bank Transfer'); ?></span></td>
                            <td><code><?php echo htmlspecialchars($p['transaction_reference'] ?? 'N/A'); ?></code></td>
                            <td>
                                <?php if (!empty($p['proof_file'])): ?>
                                    <a href="<?php echo APP_URL . '/' . htmlspecialchars($p['proof_file']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-image me-1"></i>មើលរូប
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">គ្មាន</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'approved'): ?>
                                    <span class="badge bg-success">បានអនុម័ត</span>
                                <?php elseif ($p['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger">បានបដិសេធ</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">រង់ចាំពិនិត្យ</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($p['status'] === 'pending'): ?>
                                    <div class="btn-group btn-group-sm">
                                        <form method="POST" action="<?php echo APP_URL; ?>/platform/billing/<?php echo $p['id']; ?>/approve" class="d-inline" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់អនុម័តការបង់ប្រាក់នេះ និងដំណើរការសិក្ខាសាលាដែរឬទេ?');">
                                            <?php echo csrfField(); ?>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-lg me-1"></i>អនុម័ត
                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo APP_URL; ?>/platform/billing/<?php echo $p['id']; ?>/reject" class="d-inline ms-1" onsubmit="return confirm('តើអ្នកប្រាកដជាចង់បដិសេធការបង់ប្រាក់នេះដែរឬទេ?');">
                                            <?php echo csrfField(); ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-x-lg me-1"></i>បដិសេធ
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">ដំណើរការរួចរាល់</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
