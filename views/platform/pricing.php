<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">តារាងតម្លៃប្រព័ន្ធ (Platform Pricing Rules)</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">
            <i class="bi bi-plus-lg"></i> បន្ថែមច្បាប់តម្លៃថ្មី
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ឈ្មោះកម្រិត</th>
                            <th>ចន្លោះចំណុះ</th>
                            <th>តម្លៃប្រព័ន្ធ</th>
                            <th>ស្ថានភាព</th>
                            <th>សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($rule['name']) ?></td>
                            <td><?= number_format($rule['min_capacity']) ?> - <?= $rule['max_capacity'] ? number_format($rule['max_capacity']) : 'គ្មានដែនកំណត់' ?> នាក់</td>
                            <td class="text-success fw-bold">$<?= number_format($rule['price'], 2) ?> <?= htmlspecialchars($rule['currency']) ?></td>
                            <td>
                                <span class="badge bg-<?= $rule['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $rule['status'] === 'active' ? 'សកម្ម' : 'អសកម្ម' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white" onclick='editRule(<?= json_encode($rule) ?>)'>កែសម្រួល</button>
                                <button class="btn btn-sm btn-<?= $rule['status'] === 'active' ? 'warning' : 'success' ?>" onclick="toggleRule(<?= $rule['id'] ?>)">
                                    <?= $rule['status'] === 'active' ? 'ផ្អាក' : 'បើកដំណើរការ' ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rules)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">មិនទាន់មានច្បាប់កំណត់តម្លៃនៅឡើយទេ។</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ruleForm" onsubmit="saveRule(event)">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitle">ច្បាប់តម្លៃ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rule_id" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ឈ្មោះកម្រិត</label>
                        <input type="text" class="form-control" name="name" id="rule_name" required placeholder="ឧទាហរណ៍៖ Starter (1-50)">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">ចំណុះអប្បបរមា</label>
                            <input type="number" class="form-control" name="min_capacity" id="rule_min" required min="0">
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">ចំណុះអតិបរមា</label>
                            <input type="number" class="form-control" name="max_capacity" id="rule_max" required min="1">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label fw-bold">តម្លៃ ($)</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="rule_price" required min="0">
                        </div>
                        <div class="col">
                            <label class="form-label fw-bold">រូបិយប័ណ្ណ</label>
                            <select class="form-control" name="currency" id="rule_currency">
                                <option value="USD">USD ($)</option>
                                <option value="KHR">KHR (៛)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ស្ថានភាព</label>
                        <select class="form-control" name="status" id="rule_status">
                            <option value="active">សកម្ម (Active)</option>
                            <option value="inactive">អសកម្ម (Inactive)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">បោះបង់</button>
                    <button type="submit" class="btn btn-primary">រក្សាទុក</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = new bootstrap.Modal(document.getElementById('ruleModal'));
    
    document.querySelector('[data-bs-target="#addRuleModal"]').addEventListener('click', () => {
        document.getElementById('ruleForm').reset();
        document.getElementById('rule_id').value = '';
        document.getElementById('modalTitle').innerText = 'បន្ថែមច្បាប់តម្លៃថ្មី';
        modal.show();
    });

    function editRule(rule) {
        document.getElementById('rule_id').value = rule.id;
        document.getElementById('rule_name').value = rule.name;
        document.getElementById('rule_min').value = rule.min_capacity;
        document.getElementById('rule_max').value = rule.max_capacity;
        document.getElementById('rule_price').value = rule.price;
        document.getElementById('rule_currency').value = rule.currency;
        document.getElementById('rule_status').value = rule.status;
        document.getElementById('modalTitle').innerText = 'កែសម្រួលច្បាប់តម្លៃ';
        modal.show();
    }

    function saveRule(e) {
        e.preventDefault();
        const id = document.getElementById('rule_id').value;
        const url = id ? `<?= APP_URL ?>/platform/pricing/update/${id}` : `<?= APP_URL ?>/platform/pricing/create`;
        const formData = new FormData(e.target);
        formData.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');

        fetch(url, {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }

    function toggleRule(id) {
        if (!confirm('តើអ្នកចង់ផ្លាស់ប្តូរស្ថានភាពនៃច្បាប់តម្លៃនេះមែនទេ?')) return;
        const formData = new FormData();
        formData.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');
        fetch(`<?= APP_URL ?>/platform/pricing/toggle/${id}`, {
            method: 'POST',
            body: formData
        }).then(r => r.json()).then(res => {
            if (res.success) {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    }
</script>
