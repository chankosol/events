<?php
// views/business/allowances/desk.php
?>
<style>
    .scanner-header {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        color: #fff;
    }
    .signature-box {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: #fff;
        touch-action: none;
        cursor: crosshair;
    }
    .alert-already-paid {
        animation: pulse-danger 1.5s infinite;
    }
    @keyframes pulse-danger {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }
</style>

<div class="container-fluid py-2">
    <!-- Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="<?= APP_URL ?>/workshops/<?= $workshop['id'] ?>/allowances" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> ត្រឡប់ទៅការគ្រប់គ្រងថវិកា
            </a>
            <span class="ms-3 fw-bold fs-5 text-dark">
                <i class="bi bi-cash-coin text-success me-2"></i>តុបើកប្រាក់ឧបត្ថម្ភ (Payout Desk)
            </span>
            <span class="badge bg-success ms-2">$<?= number_format((float)$allowance['default_amount'], 2) ?> / នាក់</span>
        </div>
        <div class="text-end">
            <span class="text-muted small">សិក្ខាសាលា៖ <strong><?= htmlspecialchars($workshop['name']) ?></strong></span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Side: Scanner & Verification Panel -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header scanner-header py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-qr-code-scan me-2"></i>ស្កេន QR Code ឬបញ្ចូលកូដសិក្ខាកាម
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-success fs-4"></i></span>
                        <input type="text" id="scanInput" class="form-control form-control-lg" placeholder="ស្កេន QR Code ឬវាយលេខទូរស័ព្ទ / កូដ REG..." autofocus autocomplete="off">
                        <button class="btn btn-success fw-bold px-4" type="button" id="btnVerify">
                            <i class="bi bi-check-circle me-1"></i> ពិនិត្យ
                        </button>
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> ម៉ាស៊ីនស្កេន Barcode/QR Scanner នឹងបញ្ចូលកូដ និងដំណើរការផ្ទៀងផ្ទាត់ដោយស្វ័យប្រវត្ត។</small>
                </div>
            </div>

            <!-- Dynamic Verification Result -->
            <div id="resultCard" style="display: none;">
                <!-- Result injected via JavaScript -->
            </div>
        </div>

        <!-- Right Side: Group / Delegation Head Payout Tab -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold text-primary mb-0">
                        <i class="bi bi-building me-2"></i>បើកប្រាក់តាមប្រធានប្រតិភូខេត្ត (Group Claim)
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small">
                        ប្រសិនបើប្រធានប្រតិភូខេត្តមកបើកជំនួសសមាជិករបស់គាត់ ប្រព័ន្ធនឹងគណនាប្រាក់ផ្អែកលើ <strong>សមាជិកដែលមានវត្តមានជាក់ស្តែង និងមិនទាន់បានបើកប្រាក់ប៉ុណ្ណោះ</strong>។
                    </p>

                    <form id="delegationPayoutForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ជ្រើសរើសរាជធានី-ខេត្ត / ប្រតិភូ</label>
                            <select id="delSelect" class="form-select" required>
                                <option value="">-- ជ្រើសរើសខេត្ត --</option>
                                <?php foreach ($delegations as $del): ?>
                                    <option value="<?= $del['id'] ?>" data-head="<?= htmlspecialchars($del['head_name'] ?? '') ?>" data-attended="<?= (int)$del['attended_members'] ?>" data-paid="<?= (int)$del['paid_members'] ?>" data-rate="<?= (float)$allowance['default_amount'] ?>">
                                        <?= htmlspecialchars($del['province']) ?> (វត្តមាន: <?= (int)$del['attended_members'] ?> នាក់ | បើករួច: <?= (int)$del['paid_members'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="delDetailsBox" class="p-3 bg-light rounded mb-3" style="display: none;">
                            <div class="d-flex justify-content-between mb-1">
                                <span>សមាជិកមានវត្តមានជាក់ស្តែង៖</span>
                                <strong id="delAttendedCount">0 នាក់</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>ចំនួនទឹកប្រាក់សរុបត្រូវបើក៖</span>
                                <strong class="text-success fs-5" id="delTotalAmount">$0.00</strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ឈ្មោះប្រធានប្រតិភូដែលមកទទួលលុយ</label>
                            <input type="text" id="delHeadName" class="form-control" placeholder="ឈ្មោះអ្នកមកទទួលលុយ" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">លេខអត្តសញ្ញាណប័ណ្ណប្រធាន</label>
                            <input type="text" id="delHeadIdCard" class="form-control" placeholder="លេខអត្តសញ្ញាណប័ណ្ណ" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">វិធីសាស្ត្របើកផ្តល់</label>
                            <select id="delPayoutMethod" class="form-select">
                                <option value="cash">សាច់ប្រាក់សុទ្ធ (Cash)</option>
                                <option value="bakong">Bakong / KHQR</option>
                                <option value="bank_transfer">ផ្ទេរតាមធនាគារ (Bank Transfer)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2" id="btnDelSubmit">
                            <i class="bi bi-cash-stack me-1"></i> បញ្ជាក់ការបើកប្រាក់ជូនប្រតិភូ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const WORKSHOP_ID = <?= $workshop['id'] ?>;
const APP_URL = "<?= APP_URL ?>";
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const scanInput = document.getElementById('scanInput');
const btnVerify = document.getElementById('btnVerify');
const resultCard = document.getElementById('resultCard');

scanInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        verifyAttendee();
    }
});
btnVerify.addEventListener('click', verifyAttendee);

function verifyAttendee() {
    const val = scanInput.value.trim();
    if (!val) return;

    let url = `${APP_URL}/api/workshops/${WORKSHOP_ID}/allowances/check?`;
    if (val.length >= 20 && !val.includes('REG')) {
        url += `token=${encodeURIComponent(val)}`;
    } else {
        url += `code=${encodeURIComponent(val)}`;
    }

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            renderError(data.message);
            return;
        }
        renderResult(data.data);
    })
    .catch(err => {
        renderError('មានបញ្ហាក្នុងការតភ្ជាប់ សូមព្យាយាមម្តងទៀត');
    });
}

function renderError(msg) {
    resultCard.style.display = 'block';
    resultCard.innerHTML = `
        <div class="alert alert-danger shadow-sm border-0 p-4 text-center">
            <i class="bi bi-x-octagon-fill text-danger fs-1 d-block mb-2"></i>
            <h5 class="fw-bold text-danger mb-1">${msg}</h5>
            <small class="text-muted">សូមពិនិត្យមើលកូដ ឬ QR របស់សិក្ខាកាមម្តងទៀត។</small>
        </div>
    `;
    scanInput.select();
}

function renderResult(data) {
    const reg = data.registration;
    const allowance = data.allowance;
    const alreadyDisbursed = data.already_disbursed;
    const isEligible = data.is_eligible;

    resultCard.style.display = 'block';

    if (alreadyDisbursed) {
        // ANTI-DOUBLE PAYOUT ALERT!
        const d = data.disbursement_info;
        resultCard.innerHTML = `
            <div class="alert alert-danger alert-already-paid shadow-sm border-0 p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger me-3"></i>
                    <div>
                        <h4 class="fw-bold text-danger mb-0">បានបើកប្រាក់ឧបត្ថម្ភរួចហើយ! (ALREADY PAID)</h4>
                        <div class="text-dark fw-medium">សិក្ខាកាមនេះបានមកបើកប្រាក់រួចរាល់ហើយ មិនអាចបើកលើសពី ១ ដងបានទេ!</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded text-dark border">
                    <div class="row g-2">
                        <div class="col-md-6"><strong>ឈ្មោះអ្នកទទួល៖</strong> ${d.disbursed_to_name}</div>
                        <div class="col-md-6"><strong>លេខប័ណ្ណ៖</strong> <span class="badge bg-danger">${d.receipt_voucher_no}</span></div>
                        <div class="col-md-6"><strong>កាលបរិច្ឆេទ & ម៉ោង៖</strong> ${d.disbursed_at}</div>
                        <div class="col-md-6"><strong>បុគ្គលិកដែលបានបើក៖</strong> ${d.staff_name}</div>
                        <div class="col-md-6"><strong>ចំនួនទឹកប្រាក់៖</strong> $${parseFloat(d.amount).toFixed(2)}</div>
                        <div class="col-md-6"><strong>វិធីសាស្ត្រ៖</strong> ${d.payout_method.toUpperCase()}</div>
                    </div>
                </div>
            </div>
        `;
        scanInput.select();
        return;
    }

    if (!isEligible) {
        resultCard.innerHTML = `
            <div class="alert alert-warning shadow-sm border-0 p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-shield-exclamation fs-1 text-warning me-3"></i>
                    <div>
                        <h4 class="fw-bold text-dark mb-0">មិនទាន់គ្រប់លក្ខខណ្ឌបើកប្រាក់ (INELIGIBLE)</h4>
                        <div class="text-muted">សិក្ខាកាមមិនទាន់មានវត្តមានគ្រប់តាមលក្ខខណ្ឌកំណត់ (${allowance.min_attendance_percent}%) នៅឡើយទេ។</div>
                    </div>
                </div>
                <div class="bg-white p-3 rounded text-dark border">
                    <strong>ឈ្មោះ៖</strong> ${reg.name} &bull; <strong>វត្តមានជាក់ស្តែង៖</strong> <span class="badge bg-warning text-dark">${data.attendance_rate}%</span>
                </div>
            </div>
        `;
        scanInput.select();
        return;
    }

    // ELIGIBLE & NOT PAID YET!
    resultCard.innerHTML = `
        <div class="card border-0 shadow-sm border-top border-5 border-success mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 mb-2">
                            <i class="bi bi-check-circle-fill me-1"></i> គ្រប់លក្ខខណ្ឌបើកប្រាក់
                        </span>
                        <h3 class="fw-bold text-dark mb-1">${reg.name}</h3>
                        <p class="text-muted mb-0">
                            <i class="bi bi-geo-alt me-1"></i> ${reg.delegation_province || reg.province || 'ទូទៅ'}
                            &bull; <i class="bi bi-telephone me-1"></i> ${reg.phone || 'គ្មានលេខ'}
                            &bull; កូដ៖ <strong>${reg.registration_code}</strong>
                        </p>
                    </div>
                    <div class="text-end">
                        <div class="text-xs text-uppercase text-muted">ចំនួនទឹកប្រាក់ត្រូវបើក</div>
                        <div class="h2 fw-bold text-success mb-0">$${parseFloat(allowance.default_amount).toFixed(2)}</div>
                    </div>
                </div>

                <hr>

                <form id="individualPayoutForm">
                    <input type="hidden" name="registration_id" value="${reg.id}">
                    <input type="hidden" name="amount" value="${allowance.default_amount}">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ឈ្មោះអ្នកទទួលជាក់ស្តែង</label>
                            <input type="text" name="disbursed_to_name" class="form-control" value="${reg.name}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">លេខអត្តសញ្ញាណប័ណ្ណ</label>
                            <input type="text" name="recipient_id_card" class="form-control" value="${reg.id_card_number || ''}" placeholder="បញ្ចូលលេខអត្តសញ្ញាណប័ណ្ណ">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">វិធីសាស្ត្របើកផ្តល់</label>
                            <select name="payout_method" class="form-select">
                                <option value="cash" selected>សាច់ប្រាក់សុទ្ធ (Cash)</option>
                                <option value="bakong">Bakong / KHQR</option>
                                <option value="bank_transfer">ផ្ទេរតាមធនាគារ (${reg.bank_name || 'Bank'})</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">កំណត់សម្គាល់ (បើមាន)</label>
                            <input type="text" name="notes" class="form-control" placeholder="ផ្សេងៗ...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-flex justify-content-between">
                            <span>ហត្ថលេខាឌីជីថលអ្នកទទួល (Digital Signature)</span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" id="btnClearSig">លុបហត្ថលេខា</button>
                        </label>
                        <canvas id="sigCanvas" class="signature-box w-100" height="120"></canvas>
                        <input type="hidden" name="signature_data" id="sigInput">
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm" id="btnConfirmPay">
                        <i class="bi bi-check2-circle me-1"></i> បញ្ជាក់ការបើកប្រាក់ $${parseFloat(allowance.default_amount).toFixed(2)} ភ្លាមៗ
                    </button>
                </form>
            </div>
        </div>
    `;

    initSignaturePad();
    setupIndividualPayoutSubmit();
}

function initSignaturePad() {
    const canvas = document.getElementById('sigCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.offsetWidth;
    ctx.strokeStyle = '#0f172a';
    ctx.lineWidth = 2.5;

    let drawing = false;

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return { x: clientX - rect.left, y: clientY - rect.top };
    }

    function start(e) {
        drawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }
    function move(e) {
        if (!drawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }
    function end() {
        if (drawing) {
            drawing = false;
            document.getElementById('sigInput').value = canvas.toDataURL();
        }
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    window.addEventListener('mouseup', end);

    canvas.addEventListener('touchstart', start);
    canvas.addEventListener('touchmove', move);
    window.addEventListener('touchend', end);

    document.getElementById('btnClearSig').addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('sigInput').value = '';
    });
}

function setupIndividualPayoutSubmit() {
    const form = document.getElementById('individualPayoutForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnConfirmPay');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> កំពុងដំណើរការ...';

        const formData = new FormData(form);
        formData.append('_csrf_token', CSRF_TOKEN);

        fetch(`${APP_URL}/workshops/${WORKSHOP_ID}/allowances/disburse`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                resultCard.innerHTML = `
                    <div class="alert alert-success shadow-sm p-4 text-center">
                        <i class="bi bi-check-circle-fill fs-1 text-success d-block mb-2"></i>
                        <h4 class="fw-bold text-success mb-1">បានបើកប្រាក់ឧបត្ថម្ភជោគជ័យ!</h4>
                        <div class="h5 fw-bold text-dark font-monospace mb-2">ប័ណ្ណលេខ៖ ${res.data.voucher_no}</div>
                        <div class="text-muted mb-3">ចំនួនទឹកប្រាក់៖ $${parseFloat(res.data.amount).toFixed(2)} (${res.data.disbursed_at})</div>
                        <button class="btn btn-outline-success" onclick="scanInput.value=''; resultCard.style.display='none'; scanInput.focus();">
                            <i class="bi bi-arrow-repeat me-1"></i> ស្កេនអ្នកបន្ទាប់
                        </button>
                    </div>
                `;
                scanInput.value = '';
                scanInput.focus();
            } else {
                alert(res.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> បញ្ជាក់ការបើកប្រាក់';
            }
        })
        .catch(err => {
            alert('មានបញ្ហាក្នុងការរក្សាទុក!');
            btn.disabled = false;
        });
    });
}

// Delegation Selection
const delSelect = document.getElementById('delSelect');
const delDetailsBox = document.getElementById('delDetailsBox');
const delAttendedCount = document.getElementById('delAttendedCount');
const delTotalAmount = document.getElementById('delTotalAmount');
const delHeadName = document.getElementById('delHeadName');

delSelect.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    if (!this.value) {
        delDetailsBox.style.display = 'none';
        return;
    }
    const attended = parseInt(opt.getAttribute('data-attended') || 0);
    const paid = parseInt(opt.getAttribute('data-paid') || 0);
    const rate = parseFloat(opt.getAttribute('data-rate') || 0);
    const head = opt.getAttribute('data-head');

    delHeadName.value = head || '';
    const unpaidAttended = Math.max(0, attended - paid);
    delAttendedCount.innerText = `${unpaidAttended} នាក់ (មកសរុប ${attended} នាក់)`;
    delTotalAmount.innerText = `$${(unpaidAttended * rate).toFixed(2)}`;
    delDetailsBox.style.display = 'block';
});

// Delegation Form Submit
document.getElementById('delegationPayoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const delId = delSelect.value;
    if (!delId) return;

    if (!confirm('តើអ្នកពិតជាចង់បើកប្រាក់សរុបជូនប្រធានប្រតិភូនេះមែនទេ?')) return;

    const fd = new FormData();
    fd.append('_csrf_token', CSRF_TOKEN);
    fd.append('delegation_id', delId);
    fd.append('head_name', delHeadName.value);
    fd.append('head_id_card', document.getElementById('delHeadIdCard').value);
    fd.append('payout_method', document.getElementById('delPayoutMethod').value);

    fetch(`${APP_URL}/workshops/${WORKSHOP_ID}/allowances/disburse-delegation`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) {
            location.reload();
        }
    });
});
</script>
