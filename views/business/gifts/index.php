<?php $breadcrumbs = [
    ['label' => 'ទំព័រដើម',   'url' => APP_URL . '/dashboard',                    'icon' => 'house-door-fill'],
    ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops',                    'icon' => 'calendar-event'],
    ['label' => mb_strimwidth($workshop['name'], 0, 40, '…'), 'url' => APP_URL . '/workshops/' . $workshop['id'], 'icon' => null],
    ['label' => 'កាដូ / អំណោយ', 'url' => null,                                   'icon' => 'gift'],
]; ?>
<div class="container-fluid py-4">

    <h1 class="h3 mb-4 text-gray-800 fw-bold">កាដូ &amp; អំណោយ - <?= htmlspecialchars($workshop['name']) ?></h1>
    
    <div class="row">
        <!-- Gift Scanner -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold"><i class="bi bi-qr-code-scan"></i> ចែកកាដូ / អំណោយ</h6>
                </div>
                <div class="card-body text-center p-4">
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">ជ្រើសរើសកាដូដែលត្រូវចែក</label>
                        <select id="activeGift" class="form-select mb-3">
                            <option value="">-- ជ្រើសរើសកាដូ --</option>
                            <?php foreach ($gifts as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="scanner-container" class="bg-light border rounded mb-3" style="height: 250px; display: flex; align-items: center; justify-content: center;">
                        <span class="text-muted"><i class="bi bi-camera me-1"></i> កាមេរ៉ាស្កេនត្រៀមរួចរាល់</span>
                    </div>
                    
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold">ឬបញ្ចូលកូដ QR ដោយដៃ</label>
                        <div class="input-group">
                            <input type="text" id="manualToken" class="form-control" placeholder="បញ្ចូលកូដ QR Token...">
                            <button class="btn btn-outline-primary" onclick="submitDistribution()">កត់ត្រាចែក</button>
                        </div>
                    </div>
                    
                    <div id="scanResult" class="alert d-none"></div>
                </div>
            </div>
        </div>
        
        <!-- Gifts List -->
        <div class="col-md-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">បញ្ជីស្តុកកាដូ / អំណោយ</h6>
                    <button class="btn btn-sm btn-success">បន្ថែមប្រភេទកាដូ</button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ឈ្មោះកាដូ</th>
                                    <th>ចំនួនសរុប</th>
                                    <th>បានចែករួច</th>
                                    <th>នៅសល់ក្នុងស្តុក</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gifts as $g): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($g['name']) ?></td>
                                    <td><?= $g['total_quantity'] ?></td>
                                    <td class="text-success fw-bold"><?= $g['distributed_count'] ?></td>
                                    <td class="text-primary fw-bold"><?= $g['total_quantity'] - $g['distributed_count'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($gifts)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">មិនទាន់មានកាដូត្រូវបានបន្ថែមនៅឡើយទេ។</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitDistribution() {
        const giftId = document.getElementById('activeGift').value;
        const token = document.getElementById('manualToken').value;
        const resultBox = document.getElementById('scanResult');
        
        if (!giftId) { alert('សូមជ្រើសរើសកាដូជាមុនសិន។'); return; }
        if (!token) { alert('សូមបញ្ចូល ឬស្កេនកូដ QR សិក្ខាកាម។'); return; }
        
        const fd = new FormData();
        fd.append('gift_id', giftId);
        fd.append('qr_token', token);
        fd.append('_csrf_token', '<?= Session::get('_csrf_token') ?>');
        
        fetch('<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/gifts/distribute', {
            method: 'POST',
            body: fd
        }).then(r => r.json()).then(res => {
            resultBox.classList.remove('d-none', 'alert-success', 'alert-danger');
            resultBox.classList.add(res.success ? 'alert-success' : 'alert-danger');
            resultBox.innerText = res.message;
            if(res.success) {
                document.getElementById('manualToken').value = '';
                setTimeout(() => location.reload(), 1500);
            }
        });
    }
</script>
