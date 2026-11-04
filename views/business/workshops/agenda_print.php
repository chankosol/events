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
$startDate = !empty($workshop['start_date']) ? date('d-M-Y', strtotime($workshop['start_date'])) : '';
$endDate = !empty($workshop['end_date']) ? date('d-M-Y', strtotime($workshop['end_date'])) : '';
$dateDisplay = $startDate;
if (!empty($endDate) && $endDate !== $startDate) {
    $dateDisplay .= ' ដល់ ' . $endDate;
}
$rawLogo = !empty($workshop['business_logo']) ? $workshop['business_logo'] : (!empty($workshop['logo']) ? $workshop['logo'] : null);
$primaryColor = !empty($workshop['primary_color']) ? $workshop['primary_color'] : '#1e3c72';
$googleMapsUrl = !empty($workshop['google_maps_url']) ? trim($workshop['google_maps_url']) : null;
$hasMap = !empty($googleMapsUrl);
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
            justify-content: flex-end;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .action-btns {
            display: flex;
            align-items: center;
            gap: 10px;
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

        .header-top-bar {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
        }

        /* When Map QR exists: Brand on Left, QR on Right */
        .agenda-header.has-map-header .header-top-bar {
            justify-content: space-between;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .agenda-header.has-map-header .org-branding {
            justify-content: flex-start;
            margin-bottom: 0;
        }

        /* When No Map QR: Brand is Centered */
        .agenda-header.centered-header .header-top-bar {
            justify-content: center;
            margin-bottom: 14px;
        }

        .agenda-header.centered-header .org-branding {
            justify-content: center;
            margin-bottom: 0;
        }

        .org-branding {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Map QR Section */
        .map-qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex-shrink: 0;
        }

        .map-qr-link {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .map-qr-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px;
            width: 66px;
            height: 66px;
            box-sizing: border-box;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .map-qr-link:hover .map-qr-box {
            transform: scale(1.02);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            border-color: var(--brand-primary);
        }

        .map-qr-img {
            width: 54px;
            height: 54px;
            display: block;
            border-radius: 4px;
        }

        .map-qr-label {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .org-logo {
            max-height: 72px;
            max-width: 140px;
            object-fit: contain;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .workshop-divider-art {
            display: block;
            margin: 6px auto 0 auto;
            width: 130px;
            height: 7px;
            max-width: 60%;
        }

        .org-info-text {
            text-align: left;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .org-name {
            font-size: 19px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.3px;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        .org-contact {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            line-height: 1.35;
        }

        .org-contact-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
            align-items: center;
            gap: 6px;
        }

        .meta-label {
            font-weight: 600;
            color: var(--brand-primary);
            flex-shrink: 0;
            white-space: nowrap;
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
            margin-bottom: 8px;
        }

        .agenda-note {
            font-size: 12px;
            color: #64748b;
            font-style: italic;
            margin-bottom: 24px;
            padding-left: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .agenda-note .note-star {
            color: #dc2626;
            font-weight: 700;
            font-style: normal;
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

            .agenda-header.has-map-header .header-top-bar {
                border-bottom: 1px dashed #cbd5e1 !important;
            }

            .map-qr-box {
                width: 66px !important;
                height: 66px !important;
                padding: 5px !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
            }

            .workshop-divider-art {
                width: 130px !important;
                height: 7px !important;
                margin: 6px auto 0 auto !important;
            }

            .workshop-divider-art path {
                fill: <?= htmlspecialchars($primaryColor) ?> !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .agenda-note {
                color: #475569 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .agenda-note .note-star {
                color: #dc2626 !important;
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
        <div class="action-btns">
            <a href="/workshops/<?= $workshop['id'] ?>" class="btn-ui btn-ui-back">
                <i class="bi bi-arrow-left"></i> ត្រឡប់ក្រោយ
            </a>
            <button onclick="window.print()" class="btn-ui btn-ui-print">
                <i class="bi bi-printer-fill"></i> បោះពុម្ព
            </button>
        </div>
    </div>

    <!-- Page Content Container -->
    <div class="page-wrapper">
        <div class="a4-sheet">

            <div>
                <!-- Header -->
                <div class="agenda-header <?= $hasMap ? 'has-map-header' : 'centered-header' ?>">
                    <div class="header-top-bar">
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
                                <?php endif; ?>
                                <?php if (!empty($businessPhone)): ?>
                                    <div class="org-contact">
                                        <span class="org-contact-item"><i class="bi bi-telephone"></i> <?= htmlspecialchars($businessPhone) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($hasMap): ?>
                            <div class="map-qr-section">
                                <a href="<?= htmlspecialchars($googleMapsUrl) ?>" target="_blank" rel="noopener" class="map-qr-link" style="text-decoration: none; color: inherit;" title="ចុចដើម្បីបើកមើលទីតាំងលើ Google Maps">
                                    <div class="map-qr-box">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=<?= urlencode($googleMapsUrl) ?>" 
                                             alt="QR Code Map" 
                                             class="map-qr-img">
                                    </div>
                                    <div class="map-qr-label">
                                        <i class="bi bi-geo-alt-fill text-danger"></i> ស្កេនមើលទីតាំង
                                    </div>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="agenda-title-badge">របៀបវារៈកម្មវិធី</div>
                    <div class="workshop-subtitle"><?= htmlspecialchars($workshopTitle) ?></div>
                    <!-- Tacteing Decorative Art Flourish -->
                    <svg class="workshop-divider-art" viewBox="30 -531 2406 126" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <g transform="scale(1, -1)">
                            <path d="M948 524Q958 525 963 525Q976 525 982 515L974 491Q934 487 880 487H868Q850 487 831.5 487.0Q813 487 795 488H785Q805 492 824.5 493.0Q844 494 871 494Q887 494 903.5 493.5Q920 493 937 493Q939 497 940.0 509.0Q941 521 948 524ZM1174 498 1215 527Q1243 523 1253 520Q1274 513 1279 494Q1303 529 1348 529Q1361 529 1373 526L1383 519Q1372 508 1365 501Q1350 487 1354 475Q1351 484 1339.5 491.0Q1328 498 1316 498Q1310 498 1300 494Q1289 483 1289 472Q1289 468 1293 460Q1303 447 1322 447Q1331 447 1338 451L1347 461L1389 421Q1371 416 1360.5 414.0Q1350 412 1337 412Q1319 412 1307 420L1288 446Q1276 435 1265 424Q1252 414 1237 412Q1215 412 1210 413Q1186 418 1174 442Q1157 409 1111 409Q1092 409 1078 416L1073 419Q1083 429 1094.0 441.0Q1105 453 1103 466Q1108 457 1117.5 450.0Q1127 443 1137 443Q1147 443 1154 450Q1157 455 1161.0 461.0Q1165 467 1165 472Q1165 480 1157 484Q1146 495 1132 495Q1125 495 1120.0 492.5Q1115 490 1112.5 482.0Q1110 474 1103 476Q1104 489 1093.5 502.0Q1083 515 1072 517Q1085 523 1095.0 525.5Q1105 528 1117 528Q1127 528 1136 525ZM1493 528Q1513 531 1524 518L1523 501L1681 497L1677 489Q1662 489 1629.5 488.0Q1597 487 1581 487Q1555 487 1532.5 489.0Q1510 491 1489 498Q1485 503 1485 511Q1485 523 1493 528ZM753 502Q757 508 762 508Q768 508 771.5 500.5Q775 493 772 487Q765 480 755 482Q753 483 753 502ZM1063 503 1084 476Q1087 460 1075.0 445.5Q1063 431 1048 428Q1033 425 1018 443Q1008 454 1003 456Q960 460 900.5 462.0Q841 464 783 464Q742 464 700.5 463.5Q659 463 618 463Q615 451 606.5 447.5Q598 444 583 444L567 465L30 461L34 468H570Q578 489 597 489Q605 489 611 483Q609 477 614.5 471.5Q620 466 625 468L972 472Q991 467 1011.5 488.5Q1032 510 1043 510Q1052 510 1063 503ZM704 489Q704 500 716 500Q725 500 729 493Q731 489 728.0 486.0Q725 483 722 480Q709 478 704 489ZM1388 497Q1398 510 1412 510Q1425 510 1450 494Q1467 483 1472 481Q1503 478 1550.5 477.0Q1598 476 1604 476Q1629 476 1678.5 477.0Q1728 478 1752 478Q1751 482 1746.5 487.5Q1742 493 1743 498Q1746 507 1758 505Q1768 499 1765 488L1752 478Q1755 479 1758.5 479.0Q1762 479 1763 479Q1773 479 1796.0 475.0Q1819 471 1831 471Q1847 471 1858.5 477.0Q1870 483 1878 497L1911 473H2436Q2314 473 2123.5 470.5Q1933 468 1900 468Q1898 462 1894 457Q1885 447 1874 447Q1866 447 1862 454Q1852 463 1835.0 466.5Q1818 470 1796 470Q1788 470 1780.0 469.5Q1772 469 1765 469L1762 441L1743 444Q1741 457 1752 465L1468 460Q1459 458 1441.0 443.0Q1423 428 1413 428Q1403 428 1393 438Q1386 450 1382.0 457.5Q1378 465 1378 475Q1378 487 1388 497ZM1692 494Q1689 502 1693.0 506.0Q1697 510 1705 510Q1718 510 1718 498Q1719 494 1717 491Q1712 487 1710 485Q1704 482 1692 494ZM710 453Q728 457 726 443Q728 435 716 430Q709 434 705 435ZM761 453Q765 455 777 443Q777 431 770 427L753 434ZM968 443 983 431Q980 423 978.0 417.0Q976 411 968 409Q954 405 943.5 415.0Q933 425 938 438L786 444Q815 444 843.0 445.0Q871 446 900 447Q937 447 968 443ZM1689 443Q1688 450 1694.0 455.5Q1700 461 1707 461Q1716 461 1719 452Q1721 447 1719 443Q1712 434 1709 430Q1701 426 1695.5 430.5Q1690 435 1689 443ZM1682 450 1678 443 1523 441Q1525 433 1525 429Q1525 417 1513 410Q1504 412 1495.0 417.0Q1486 422 1483 431Q1482 441 1492.5 446.0Q1503 451 1517 453ZM1271 483Q1263 514 1224 514Q1191 514 1181 488Q1180 483 1180 473Q1180 456 1184.5 444.5Q1189 433 1204.0 427.0Q1219 421 1232 421Q1241 421 1248.0 424.5Q1255 428 1259.5 438.0Q1264 448 1268.5 456.5Q1273 465 1273 472Q1273 475 1271 483ZM1212 493Q1222 493 1233 493Q1251 493 1253 478Q1256 452 1237 445Q1214 442 1203 458Q1202 464 1203.0 474.5Q1204 485 1212 493Z" fill="var(--brand-primary)" />
                        </g>
                    </svg>
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
                        <span class="meta-label"><i class="bi bi-clock me-1"></i> ម៉ោង ៖</span>
                        <span class="meta-val"><?= !empty($workshop['start_time']) ? date('h:i A', strtotime($workshop['start_time'])) : '08:00 AM' ?> - <?= !empty($workshop['end_time']) ? date('h:i A', strtotime($workshop['end_time'])) : '05:00 PM' ?></span>
                    </div>
                </div>

                <!-- Sessions Table -->
                <?php if (!empty($sessions)): ?>
                    <table class="agenda-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">ល.រ</th>
                                <th style="width: 170px; text-align: center;"><i class="bi bi-clock"></i> ពេលវេលា</th>
                                <th><i class="bi bi-journal-text"></i> ខ្លឹមសារកម្មវិធី / របៀបវារៈ</th>
                                <th style="width: 180px;"><i class="bi bi-person-badge"></i> វាគ្មិន / អ្នកសម្របសម្រួល</th>
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
                                
                                $startTimeFmt = !empty($session['start_time']) ? date('h:i A', strtotime($session['start_time'])) : '';
                                $endTimeFmt = !empty($session['end_time']) ? date('h:i A', strtotime($session['end_time'])) : '';
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
                    <div class="agenda-note">
                        <span class="note-star">*</span> រក្សាសិទ្ធិកែប្រែក្នុងករណីចាំបាច់
                    </div>
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
