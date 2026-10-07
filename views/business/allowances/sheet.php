<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300..700;1,300..700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Kantumruy Pro', serif, sans-serif !important;
            color: #000;
            background: #fff;
            font-size: 13px;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000 !important;
            padding: 6px 8px;
        }
        .signature-img {
            max-height: 40px;
            max-width: 120px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; }
            @page {
                size: A4 portrait;
                margin: 12mm;
            }
        }
    </style>
</head>
<body class="p-4">
<div class="container-fluid">
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h5 class="fw-bold mb-0">ទម្រង់បញ្ជីបើកប្រាក់ឧបត្ថម្ភសវនកម្ម (Audit Payroll Sheet)</h5>
            <small class="text-muted">អាចបោះពុម្ព ឬរក្សាទុកជា PDF</small>
        </div>
        <button class="btn btn-primary" onclick="window.print()">
            បោះពុម្ពឯកសារនេះ (Print / Save PDF)
        </button>
    </div>

    <!-- Official Header -->
    <div class="text-center mb-4">
        <h5 class="fw-bold text-uppercase mb-1"><?= htmlspecialchars($workshop['business_name'] ?? 'KSH Training Institute') ?></h5>
        <h4 class="fw-bold mb-2">បញ្ជីបើកផ្តល់ប្រាក់ឧបត្ថម្ភសោហ៊ុយ និងថ្លៃស្នាក់នៅ</h4>
        <div class="fw-bold fs-6">សិក្ខាសាលា៖ «<?= htmlspecialchars($workshop['name']) ?>»</div>
        <div>កាលបរិច្ឆេទ៖ <?= formatDate($workshop['start_date']) ?> &bull; ទីកន្លែង៖ <?= htmlspecialchars($workshop['venue'] ?: 'រាជធានីភ្នំពេញ') ?></div>
    </div>

    <!-- Summary Box -->
    <div class="row mb-3">
        <div class="col-6">
            <div>ចំនួនអ្នកបានបើកសរុប៖ <strong><?= count($records) ?> នាក់</strong></div>
            <div>អត្រាក្នុង ១ នាក់៖ <strong>$<?= number_format((float)$allowance['default_amount'], 2) ?> <?= $allowance['currency'] ?? 'USD' ?></strong></div>
        </div>
        <div class="col-6 text-end">
            <?php 
                $totalSum = array_sum(array_column($records, 'amount'));
            ?>
            <div class="fs-6">ទឹកប្រាក់សរុបដែលបានបើក៖ <strong class="text-success">$<?= number_format((float)$totalSum, 2) ?> <?= $allowance['currency'] ?? 'USD' ?></strong></div>
            <div>កាលបរិច្ឆេទរបាយការណ៍៖ <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <!-- Table -->
    <table class="table table-bordered align-middle mb-4">
        <thead class="text-center" style="background-color: #f1f5f9;">
            <tr>
                <th style="width: 40px;">ល.រ</th>
                <th>ឈ្មោះសិក្ខាកាម</th>
                <th style="width: 50px;">ភេទ</th>
                <th>រាជធានី-ខេត្ត / អង្គភាព</th>
                <th>លេខអត្តសញ្ញាណប័ណ្ណ</th>
                <th style="width: 100px;">ចំនួនទឹកប្រាក់</th>
                <th style="width: 140px;">ហត្ថលេខា / ស្នាមមេដៃ</th>
                <th>លេខប័ណ្ណ & ម៉ោងបើក</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">មិនទាន់មានទិន្នន័យបើកប្រាក់នៅឡើយទេ។</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($records as $r): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="fw-bold">
                            <?= htmlspecialchars($r['disbursed_to_name']) ?>
                            <?php if ($r['disbursed_to_type'] === 'delegation_head'): ?>
                                <span class="badge bg-secondary ms-1 no-print">ប្រធានប្រតិភូ</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $r['gender'] === 'female' ? 'ស្រី' : 'ប្រុស' ?></td>
                        <td><?= htmlspecialchars($r['delegation_province'] ?: $r['participant_province'] ?: 'ទូទៅ') ?></td>
                        <td class="text-center font-monospace"><?= htmlspecialchars($r['recipient_id_card'] ?: '-') ?></td>
                        <td class="text-end fw-bold">$<?= number_format((float)$r['amount'], 2) ?></td>
                        <td class="text-center" style="height: 50px;">
                            <?php if (!empty($r['signature_data'])): ?>
                                <img src="<?= $r['signature_data'] ?>" class="signature-img" alt="ហត្ថលេខា">
                            <?php else: ?>
                                <span class="text-muted small">បានទទួល (<?= strtoupper($r['payout_method']) ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="small">
                            <span class="font-monospace fw-bold"><?= $r['receipt_voucher_no'] ?></span>
                            <div class="text-muted" style="font-size: 11px;"><?= date('d/m/Y H:i', strtotime($r['disbursed_at'])) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="fw-bold" style="background-color: #f8fafc;">
                    <td colspan="5" class="text-end">ទឹកប្រាក់សរុប៖</td>
                    <td class="text-end">$<?= number_format((float)$totalSum, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Signature Sign-off Block for Audit Compliance -->
    <div class="row text-center mt-5 pt-3">
        <div class="col-4">
            <p class="mb-5"><strong>អ្នករៀបចំ និងបើកផ្តល់</strong></p>
            <div class="mt-4">.............................................</div>
            <small class="text-muted">(ហត្ថលេខា និងឈ្មោះ)</small>
        </div>
        <div class="col-4">
            <p class="mb-5"><strong>ប្រធានគណនេយ្យ / ហិរញ្ញវត្ថុ</strong></p>
            <div class="mt-4">.............................................</div>
            <small class="text-muted">(ហត្ថលេខា និងឈ្មោះ)</small>
        </div>
        <div class="col-4">
            <p class="mb-5"><strong>បានឃើញ និងអនុម័តដោយប្រធានស្ថាប័ន</strong></p>
            <div class="mt-4">.............................................</div>
            <small class="text-muted">(ហត្ថលេខា និងត្រា)</small>
        </div>
    </div>
</div>
</body>
</html>
