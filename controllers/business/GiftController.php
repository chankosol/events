<?php
class GiftController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('gift.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die(); }
        
        $gifts = $db->query(
            "SELECT g.*, 
             (SELECT COUNT(*) FROM gift_distributions gd WHERE gd.gift_id = g.id) as distributed_count
             FROM gifts g 
             WHERE g.workshop_id = ? AND g.business_id = ?",
            [$workshopId, $businessId]
        );
        
        $title = 'Gifts - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/gifts/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}
