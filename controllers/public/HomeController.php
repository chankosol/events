<?php
// C:\xampp\htdocs\workshopos\controllers\public\HomeController.php

class HomeController {
    public static function index(): void {
        $title = APP_NAME . ' - Complete Workshop Operating System';
        
        $db = Database::getInstance();
        
        // Fetch pricing tiers from platform_pricing_rules
        $pricingTiers = $db->query("SELECT * FROM platform_pricing_rules WHERE status = 'active' ORDER BY sort_order ASC, price ASC");
        
        // Fetch upcoming public workshops
        $upcomingWorkshops = $db->query("
            SELECT w.*, b.name as business_name, b.slug as business_slug,
                   wb.cover_image, wb.theme_color
            FROM workshops w
            JOIN businesses b ON w.business_id = b.id
            LEFT JOIN workshop_branding wb ON wb.workshop_id = w.id
            WHERE w.visibility = 'public' 
              AND w.status IN ('registration_open', 'in_progress')
              AND w.deleted_at IS NULL
            ORDER BY w.start_date ASC
            LIMIT 6
        ");
        
        ob_start();
        require VIEWS_PATH . '/public/home.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/public.php';
    }
}
