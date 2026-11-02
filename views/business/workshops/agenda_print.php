<?php
/**
 * Official Printable Agenda View (A4 Portrait)
 * Khmer Standard Professional Agenda Format
 */
$csrfToken = generateCsrfToken();
$businessName = !empty($workshop['business_name']) ? $workshop['business_name'] : 'អង្គការ / ស្ថាប័ន / ក្រុមហ៊ុន';
$businessAddressParts = array_filter([$workshop['business_address'] ?? '', $workshop['business_city'] ?? '']);
$businessAddress = implode(', ', $businessAddressParts);
$businessPhone = $workshop['business_phone'] ?? '';
$workshopTitle = !empty($workshop['name']) ? $workshop['name'] : (!empty($workshop['title']) ? $workshop['title'] : 'សិក្ខាសាលា');
$venue = $workshop['venue'] ?? 'ទីតាំងសិក្ខាសាលា';
$trainerName = $workshop['trainer_name'] ?? '';
$startDate = !empty($workshop['start_date']) ? date('d/m/Y', strtotime($workshop['start_date'])) : '';
$endDate = !empty($workshop['end_date']) ? date('d/m/Y', strtotime($workshop['end_date'])) : '';
$dateDisplay = $startDate;
if (!empty($endDate) && $endDate !== $startDate) {
    $dateDisplay .= ' ដល់ ' . $endDate;
}
$rawLogo = !empty($workshop['business_logo']) ? $workshop['business_logo'] : (!empty($workshop['logo']) ? $workshop['logo'] : null);
$primaryColor = !empty($workshop['primary_color']) ? $workshop['primary_color'] : '#1e3c72';
$logoUrl = null;

if (!empty($rawLogo)) {
    // If local file exists, embed directly as Base64 for 100% reliable printing without 404
    $diskPath = defined('ROOT_PATH') ? ROOT_PATH . '/' . ltrim($rawLogo, '/') : null;
    if ($diskPath && file_exists($diskPath) && is_readable($diskPath)) {
        $ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
        $mime = match($ext) {
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
        $data = base64_encode(file_get_contents($diskPath));
        $logoUrl = 'data:' . $mime . ';base64,' . $data;
    } else {
        // Fallback to absolute URL with APP_URL
        $baseUrl = defined('APP_URL') ? rtrim(APP_URL, '/') : '';
        $logoUrl = $baseUrl . '/' . ltrim($rawLogo, '/');
    }
}
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>របៀបវារៈ - <?= htmlspecialchars($workshopTitle) ?></title>
    <!-- Google Fonts: Kantumruy Pro & Battambang -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Moul&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --brand-primary: <?= htmlspecialchars($primaryColor) ?>;
            --primary-color: #0f172a;
            --accent-color: <?= htmlspecialchars($primaryColor) ?>;
            --border-color: #cbd5e1;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Kantumruy Pro', 'Khmer OS Siemreap', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Action Bar (Screen Only) */
        .top-action-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .action-btns {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-ui {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: none;
            font-family: inherit;
        }

        .btn-ui-back {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-ui-back:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .btn-ui-print {
            background-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37,99,235,0.25);
        }
        .btn-ui-print:hover {
            background-color: #1d4ed8;
            color: #ffffff;
        }

        /* A4 Page Container */
        .page-wrapper {
            padding: 30px 15px;
            display: flex;
            justify-content: center;
        }

        .a4-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 22mm 20mm;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header Layout */
        .agenda-header {
            margin-bottom: 22px;
            text-align: center;
            position: relative;
        }

        .org-branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 14px;
        }

        .org-logo {
            max-height: 56px;
            max-width: 120px;
            object-fit: contain;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .org-info-text {
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .org-name {
            font-size: 19.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
            line-height: 1.25;
        }

        .org-contact {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.3;
        }

        .org-contact-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .org-contact-sep {
            color: #cbd5e1;
            font-size: 11px;
        }

        .agenda-title-badge {
            display: inline-block;
            font-size: 15px;
            font-weight: 600;
            color: #475569;
            margin-top: 6px;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .workshop-subtitle {
            font-size: 22px;
            font-weight: 700;
            color: var(--brand-primary);
            line-height: 1.35;
            max-width: 92%;
            margin: 0 auto;
        }

        /* Meta Info Card */
        .meta-container {
            background-color: #f0f5fc;
            border: 1px solid #cbdcf8;
            border-left: 5px solid var(--brand-primary);
            border-radius: 8px;
            padding: 13px 20px;
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
            font-size: 13.5px;
            box-shadow: 0 1px 3px rgba(30, 60, 114, 0.05);
        }

        .meta-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .meta-label {
            font-weight: 600;
            color: var(--brand-primary);
            min-width: 110px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
        }

        .meta-label i {
            color: var(--brand-primary);
            margin-right: 5px;
        }

        .meta-val {
            color: #0f172a;
            font-weight: 600;
            word-break: break-word;
        }

        /* Agenda Table */
        .agenda-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .agenda-table th {
            background-color: var(--brand-primary);
            color: #ffffff !important;
            font-weight: 700;
            text-align: left;
            padding: 11px 12px;
            border: 1px solid var(--brand-primary);
            font-size: 13px;
            vertical-align: middle;
            letter-spacing: 0.3px;
        }

        .agenda-table th i {
            margin-right: 5px;
            font-size: 13px;
            opacity: 0.95;
            vertical-align: -1px;
        }

        .agenda-table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }

        .agenda-table tr:nth-child(even) {
            background-color: #fbfcfe;
        }

        .agenda-table tr.break-row {
            background-color: #fffbeb !important;
        }

        .session-time {
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            font-size: 13px;
        }

        .session-name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13.5px;
            margin-bottom: 2px;
        }

        .session-desc {
            font-size: 12px;
            color: #64748b;
            line-height: 1.35;
        }

        .speaker-name {
            font-weight: 500;
            color: #334155;
            font-size: 12.5px;
        }

        /* Signatures Block */
        .signatures-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            page-break-inside: avoid;
        }

        .sign-box {
            text-align: center;
            width: 200px;
        }

        .sign-title {
            font-weight: 600;
            font-size: 13.5px;
            color: #334155;
            margin-bottom: 65px;
        }

        .sign-line {
            border-bottom: 1px dotted #94a3b8;
            margin-bottom: 6px;
        }

        .sign-note {
            font-size: 12px;
            color: #64748b;
        }

        /* Empty State */
        .empty-sessions {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        /* Print Media Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .page-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }

            .a4-sheet {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .agenda-table th {
                background-color: <?= htmlspecialchars($primaryColor) ?> !important;
                color: #ffffff !important;
                border: 1px solid <?= htmlspecialchars($primaryColor) ?> !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .agenda-table tr.break-row {
                background-color: #fffbeb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .meta-container {
                background-color: #f0f5fc !important;
                border: 1px solid #cbdcf8 !important;
                border-left: 5px solid <?= htmlspecialchars($primaryColor) ?> !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .meta-label {
                color: <?= htmlspecialchars($primaryColor) ?> !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                size: A4 portrait;
                margin: 15mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Screen-only top action bar -->
    <div class="top-action-bar no-print">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="/workshops/<?= $workshop['id'] ?>" class="btn-ui btn-ui-back">
                <i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ
            </a>
            <span style="font-size: 13px; color: #64748b; font-weight: 500;">
                ទិដ្ឋភាពមុនបោះពុម្ពរបៀបវារៈ (A4 Portrait)
            </span>
        </div>
        <div class="action-btns">
            <button onclick="window.print()" class="btn-ui btn-ui-print">
                <i class="bi bi-printer-fill"></i> បោះពុម្ព (Print)
            </button>
        </div>
    </div>

    <!-- Page Content Container -->
    <div class="page-wrapper">
        <div class="a4-sheet">

            <div>
                <!-- Header -->
                <div class="agenda-header">
                    <div class="org-branding">
                        <?php if ($logoUrl): ?>
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="org-logo">
                        <?php endif; ?>
                        <div class="org-info-text">
                            <div class="org-name"><?= htmlspecialchars($businessName) ?></div>
                            <?php if (!empty($businessAddress)): ?>
                                <div class="org-contact">
                                    <span class="org-contact-item"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($businessAddress) ?></span>
                                </div>
                            <?php elseif (!empty($businessPhone)): ?>
                                <div class="org-contact">
                                    <span class="org-contact-item"><i class="bi bi-telephone"></i> <?= htmlspecialchars($businessPhone) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="agenda-title-badge">របៀបវារៈកម្មវិធី</div>
                    <div class="workshop-subtitle"><?= htmlspecialchars($workshopTitle) ?></div>
                </div>

                <!-- Meta Information Box -->
                <div class="meta-container">
                    <div class="meta-item">
                        <span class="meta-label"><i class="bi bi-calendar3 me-1"></i> កាលបរិច្ឆេទ ៖</span>
                        <span class="meta-val"><?= htmlspecialchars($dateDisplay ?: 'មិនទាន់កំណត់') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label"><i class="bi bi-geo-alt me-1"></i> ទីតាំង ៖</span>
                        <span class="meta-val"><?= htmlspecialchars($venue ?: 'មិនទាន់កំណត់') ?></span>
                    </div>
                    <?php if (!empty($trainerName)): ?>
                    <div class="meta-item">
                        <span class="meta-label"><i class="bi bi-person me-1"></i> គ្រូឧទ្ទេស / វាគ្មិន ៖</span>
                        <span class="meta-val"><?= htmlspecialchars($trainerName) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <span class="meta-label"><i class="bi bi-clock me-1"></i> ពេលវេលាសរុប ៖</span>
                        <span class="meta-val">
                            <?= !empty($workshop['start_time']) ? date('H:i', strtotime($workshop['start_time'])) : '08:00' ?> - 
                            <?= !empty($workshop['end_time']) ? date('H:i', strtotime($workshop['end_time'])) : '17:00' ?>
                        </span>
                    </div>
                </div>

                <!-- Sessions Table -->
                <?php if (!empty($sessions)): ?>
                    <table class="agenda-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">ល.រ</th>
                                <th style="width: 145px; text-align: center;"><i class="bi bi-clock"></i> ពេលវេលា</th>
                                <th><i class="bi bi-journal-text"></i> ខ្លឹមសារកម្មវិធី / របៀបវារៈ</th>
                                <th style="width: 195px;"><i class="bi bi-person-badge"></i> វាគ្មិន / អ្នកសម្របសម្រួល</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            foreach ($sessions as $session): 
                                $isBreak = stripos($session['name'], 'Break') !== false || 
                                           stripos($session['name'], 'Lunch') !== false || 
                                           stripos($session['name'], 'សម្រាក') !== false || 
                                           stripos($session['name'], 'កាហ្វេ') !== false;
                                
                                $startTimeFmt = !empty($session['start_time']) ? date('H:i', strtotime($session['start_time'])) : '';
                                $endTimeFmt = !empty($session['end_time']) ? date('H:i', strtotime($session['end_time'])) : '';
                                $timeStr = '';
                                if ($startTimeFmt && $endTimeFmt) {
                                    $timeStr = $startTimeFmt . ' - ' . $endTimeFmt;
                                } elseif ($startTimeFmt) {
                                    $timeStr = $startTimeFmt;
                                } else {
                                    $timeStr = '-';
                                }
                            ?>
                            <tr class="<?= $isBreak ? 'break-row' : '' ?>">
                                <td style="text-align: center; font-weight: 600; color: #64748b;"><?= $count++ ?></td>
                                <td style="text-align: center;">
                                    <span class="session-time"><?= htmlspecialchars($timeStr) ?></span>
                                </td>
                                <td>
                                    <div class="session-name">
                                        <?php if ($isBreak): ?>
                                            <i class="bi bi-cup-hot me-1 text-warning"></i>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($session['name']) ?>
                                    </div>
                                    <?php if (!empty($session['description'])): ?>
                                        <div class="session-desc"><?= nl2br(htmlspecialchars($session['description'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="speaker-name"><?= htmlspecialchars($session['facilitator'] ?: '-') ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-sessions">
                        <i class="bi bi-calendar-x" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                        <p>មិនទាន់មានវគ្គកម្មវិធីនៅក្នុងរបៀបវារៈនេះនៅឡើយទេ</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Signatures Section -->
            <div class="signatures-section">
                <div class="sign-box">
                    <div class="sign-title">រៀបចំដោយ</div>
                    <div class="sign-line"></div>
                    <div class="sign-note">ហត្ថលេខា និងឈ្មោះ</div>
                </div>

                <div class="sign-box">
                    <div class="sign-title">បានឃើញ និងឯកភាព</div>
                    <div class="sign-line"></div>
                    <div class="sign-note">ហត្ថលេខា និងត្រា</div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
