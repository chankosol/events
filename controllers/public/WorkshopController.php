<?php
// C:\xampp\htdocs\workshopos\controllers\public\WorkshopController.php

class PublicWorkshopController {
    public static function show(string $slug): void {
        $db = Database::getInstance();
        $workshop = $db->queryOne(
            "SELECT w.*, b.name as business_name, b.email as business_email, b.phone as business_phone,
                    wb.cover_image, wb.logo, wb.banner, wb.theme_color
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
             WHERE w.slug = ? AND w.status IN ('active','registration_open','in_progress','completed') 
             AND w.deleted_at IS NULL",
            [$slug]
        );

        $isPreview = false;
        if (!$workshop) {
            $pendingWorkshop = $db->queryOne(
                "SELECT w.*, b.name as business_name, b.email as business_email, b.phone as business_phone,
                        wb.cover_image, wb.logo, wb.banner, wb.theme_color
                 FROM workshops w
                 JOIN businesses b ON b.id = w.business_id
                 LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
                 WHERE w.slug = ? AND w.deleted_at IS NULL",
                [$slug]
            );

            // Allow preview if logged in as Super Admin or Business owner/staff of this workshop
            if ($pendingWorkshop && (Auth::isPlatformAdmin() || (isLoggedIn() && Tenant::id() == $pendingWorkshop['business_id']))) {
                $workshop = $pendingWorkshop;
                $isPreview = true;
            } else {
                http_response_code(404);
                $title = $pendingWorkshop ? 'សិក្ខាសាលាមិនទាន់បើកដំណើរការ' : 'Workshop Not Found';
                $customMessage = $pendingWorkshop 
                    ? 'សិក្ខាសាលា «' . htmlspecialchars($pendingWorkshop['name']) . '» មិនទាន់បើកដំណើរការជាផ្លូវការនៅឡើយទេ។ សូមរង់ចាំការប្រកាសបើកចុះឈ្មោះពីអ្នករៀបចំ!' 
                    : null;
                require VIEWS_PATH . '/errors/404.php';
                return;
            }
        }

        $tickets = $db->query(
            "SELECT * FROM tickets WHERE workshop_id = ? AND status = 'active'
             ORDER BY sort_order",
            [$workshop['id']]
        );

        $sessions = $db->query(
            "SELECT * FROM workshop_sessions WHERE workshop_id = ? AND status != 'cancelled'
             ORDER BY session_date, start_time",
            [$workshop['id']]
        );

        $regCount = $db->queryOne(
            "SELECT COUNT(*) as c FROM registrations 
             WHERE workshop_id = ? AND status NOT IN ('cancelled','rejected') AND deleted_at IS NULL",
            [$workshop['id']]
        )['c'] ?? 0;

        $remainingSeats = max(0, $workshop['capacity'] - $regCount);

        $title = $workshop['name'] . ' - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/public/workshop.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/public.php';
    }
}
