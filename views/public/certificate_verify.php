<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-5">
                    <?php if ($cert): ?>
                        <div class="alert alert-success py-3 mb-4 shadow-sm border-success">
                            <h4 class="alert-heading fw-bold mb-0">
                                <i class="bi bi-patch-check-fill text-success me-2"></i> វិញ្ញាបនបត្រត្រឹមត្រូវ និងមានសុពលភាព
                            </h4>
                        </div>
                        
                        <h2 class="fw-bold mb-4 text-dark">វិញ្ញាបនបត្របញ្ជាក់ការបញ្ចប់វគ្គ</h2>
                        
                        <div class="mb-4">
                            <p class="text-muted small mb-1">វិញ្ញាបនបត្រនេះប្រគល់ជូន</p>
                            <?php
                            $parts = explode(' ', $cert['participant_name']);
                            $firstName = $parts[0];
                            $lastName = isset($parts[1]) ? substr($parts[1], 0, 1) . '.' : '';
                            $displayName = $firstName . ' ' . $lastName;
                            ?>
                            <h3 class="fw-bold text-primary display-6"><?= htmlspecialchars($displayName) ?></h3>
                        </div>
                        
                        <div class="mb-4">
                            <p class="text-muted small mb-1">ដែលបានចូលរួម និងបញ្ចប់ដោយជោគជ័យនូវសិក្ខាសាលា</p>
                            <h4 class="fw-bold text-dark"><?= htmlspecialchars($cert['workshop_name']) ?></h4>
                        </div>
                        
                        <div class="card bg-light border-0 p-4 mb-4 text-start">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="text-muted small">លេខកូដវិញ្ញាបនបត្រ:</div>
                                    <div class="fw-bold font-monospace text-primary fs-5"><?= htmlspecialchars($cert['certificate_number']) ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted small">កាលបរិច្ឆេទចេញ:</div>
                                    <div class="fw-bold"><?= formatDate($cert['issued_at']) ?></div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted small">កាលបរិច្ឆេទសិក្ខាសាលា:</div>
                                    <div class="fw-bold">
                                        <?= formatDate($cert['start_date']) ?>
                                        <?php if ($cert['start_date'] !== $cert['end_date']): ?>
                                            &ndash; <?= formatDate($cert['end_date']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="text-muted small">ចេញដោយស្ថាប័ន / ក្រុមហ៊ុន:</div>
                                    <div class="fw-bold"><?= htmlspecialchars($cert['issuer_name']) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center p-3 bg-white rounded border d-inline-block">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode(qrCodeUrl('/certificate/verify/' . $cert['verification_token'])) ?>" class="img-fluid rounded" alt="Verify QR">
                            <div class="small text-muted mt-1 font-monospace">TYPE 3: QR ផ្ទៀងផ្ទាត់ផ្លូវការ</div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger py-4 mb-0 shadow-sm border-danger">
                            <h4 class="alert-heading fw-bold mb-2">
                                <i class="bi bi-x-octagon-fill text-danger me-2"></i> វិញ្ញាបនបត្រមិនត្រឹមត្រូវ ឬត្រូវបានដកហូត
                            </h4>
                            <p class="mb-0 text-muted small">លេខកូដសម្គាល់វិញ្ញាបនបត្រដែលបានផ្តល់មិនត្រឹមត្រូវ ឬវិញ្ញាបនបត្រនេះត្រូវបានដកហូតចេញពីប្រព័ន្ធ។</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 pt-3 border-top">
                        <a href="<?= APP_URL ?>" class="text-decoration-none fw-bold text-secondary">
                            &larr; ត្រឡប់ទៅទំព័រដើម Workshop OS
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
