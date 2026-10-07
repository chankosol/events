<?php
// C:\xampp\htdocs\workshopos\controllers\platform\ReportController.php

class PlatformReportController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        // 1. Overall Revenue
        $revRow = $db->queryOne("SELECT SUM(platform_fee) as total_rev FROM workshop_billing WHERE payment_status = 'paid'");
        $totalRevenue = (float)($revRow['total_rev'] ?? 0);

        $upgradeRow = $db->queryOne("SELECT SUM(upgrade_fee) as upgrade_rev FROM capacity_upgrades WHERE payment_status = 'paid'");
        $totalRevenue += (float)($upgradeRow['upgrade_rev'] ?? 0);

        // 2. Business counts
        $totalBusinesses = (int)($db->queryOne("SELECT COUNT(*) as c FROM businesses WHERE deleted_at IS NULL")['c'] ?? 0);
        $activeBusinesses = (int)($db->queryOne("SELECT COUNT(*) as c FROM businesses WHERE status = 'active' AND deleted_at IS NULL")['c'] ?? 0);

        // 3. Workshop counts
        $totalWorkshops = (int)($db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE deleted_at IS NULL")['c'] ?? 0);
        $activeWorkshops = (int)($db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE status IN ('active','registration_open','in_progress') AND deleted_at IS NULL")['c'] ?? 0);
        $completedWorkshops = (int)($db->queryOne("SELECT COUNT(*) as c FROM workshops WHERE status = 'completed' AND deleted_at IS NULL")['c'] ?? 0);

        // 4. Registrations & Attendance
        $totalRegistrations = (int)($db->queryOne("SELECT COUNT(*) as c FROM registrations WHERE deleted_at IS NULL")['c'] ?? 0);
        $totalAttendance = (int)($db->queryOne("SELECT COUNT(*) as c FROM attendance WHERE checked_in_at IS NOT NULL")['c'] ?? 0);
        $totalCapacity = (int)($db->queryOne("SELECT SUM(capacity) as c FROM workshops WHERE deleted_at IS NULL")['c'] ?? 0);

        $attendanceRate = ($totalRegistrations > 0) ? round(($totalAttendance / $totalRegistrations) * 100, 1) : 0;
        $capacityFillRate = ($totalCapacity > 0) ? round(($totalRegistrations / $totalCapacity) * 100, 1) : 0;

        // 5. Top Businesses
        $topBusinesses = $db->query(
            "SELECT b.id, b.name, b.email, b.status,
                    COUNT(DISTINCT w.id) as workshop_count,
                    COUNT(DISTINCT r.id) as registration_count,
                    COALESCE(SUM(CASE WHEN wb.payment_status = 'paid' THEN wb.platform_fee ELSE 0 END), 0) as total_fees
             FROM businesses b
             LEFT JOIN workshops w ON w.business_id = b.id AND w.deleted_at IS NULL
             LEFT JOIN registrations r ON r.business_id = b.id AND r.deleted_at IS NULL
             LEFT JOIN workshop_billing wb ON wb.workshop_id = w.id
             WHERE b.deleted_at IS NULL
             GROUP BY b.id
             ORDER BY registration_count DESC, total_fees DESC
             LIMIT 10"
        );

        // 6. Workshop Performance List
        $workshops = $db->query(
            "SELECT w.id, w.name, w.start_date, w.capacity, w.status,
                    b.name as business_name,
                    COUNT(DISTINCT r.id) as reg_count,
                    COUNT(DISTINCT a.id) as attend_count,
                    COALESCE(wb.platform_fee, 0) as platform_fee,
                    wb.payment_status as billing_status
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN registrations r ON r.workshop_id = w.id AND r.deleted_at IS NULL
             LEFT JOIN attendance a ON a.workshop_id = w.id AND a.checked_in_at IS NOT NULL
             LEFT JOIN workshop_billing wb ON wb.workshop_id = w.id
             WHERE w.deleted_at IS NULL
             GROUP BY w.id
             ORDER BY w.start_date DESC
             LIMIT 20"
        );

        $title = 'របាយការណ៍សកលប្រព័ន្ធ (Global Reports) - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/reports/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }

    public static function export(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();

        $rows = $db->query(
            "SELECT w.id, w.name as workshop_name, b.name as business_name,
                    w.start_date, w.capacity,
                    COUNT(DISTINCT r.id) as total_registered,
                    COUNT(DISTINCT a.id) as total_attended,
                    COALESCE(wb.platform_fee, 0) as platform_fee,
                    wb.payment_status as billing_status,
                    w.status as workshop_status
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN registrations r ON r.workshop_id = w.id AND r.deleted_at IS NULL
             LEFT JOIN attendance a ON a.workshop_id = w.id AND a.checked_in_at IS NOT NULL
             LEFT JOIN workshop_billing wb ON wb.workshop_id = w.id
             WHERE w.deleted_at IS NULL
             GROUP BY w.id
             ORDER BY w.created_at DESC"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="platform_workshops_report_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['ID', 'Workshop Name', 'Business / Host', 'Start Date', 'Capacity', 'Registered', 'Attended', 'Attendance Rate %', 'Platform Fee ($)', 'Billing Status', 'Workshop Status']);

        foreach ($rows as $r) {
            $attRate = ($r['total_registered'] > 0) ? round(($r['total_attended'] / $r['total_registered']) * 100, 1) . '%' : '0%';
            fputcsv($out, [
                $r['id'],
                $r['workshop_name'],
                $r['business_name'],
                $r['start_date'],
                $r['capacity'],
                $r['total_registered'],
                $r['total_attended'],
                $attRate,
                number_format($r['platform_fee'], 2),
                $r['billing_status'] ?? 'unpaid',
                $r['workshop_status']
            ]);
        }

        fclose($out);
        exit;
    }
}
