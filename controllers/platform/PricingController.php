<?php
class PricingController {
    public static function index(): void {
        requireAuth();
        requirePlatformAdmin();
        $db = Database::getInstance();
        
        $rules = $db->query("SELECT * FROM platform_pricing_rules ORDER BY sort_order ASC");
        
        $title = 'Pricing Rules - ' . APP_NAME;
        ob_start();
        require VIEWS_PATH . '/platform/pricing.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/platform.php';
    }
    
    public static function create(): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        
        $db = Database::getInstance();
        $name = $_POST['name'] ?? '';
        $minCapacity = (int)($_POST['min_capacity'] ?? 0);
        $maxCapacity = (int)($_POST['max_capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $currency = $_POST['currency'] ?? 'USD';
        
        if (empty($name) || $minCapacity < 0 || $maxCapacity < $minCapacity || $price < 0) {
            jsonResponse(false, 'Invalid input.');
        }
        
        // Validate overlapping ranges
        $overlap = $db->queryOne("SELECT id FROM platform_pricing_rules WHERE status = 'active' AND (
            (min_capacity <= ? AND max_capacity >= ?) OR
            (min_capacity <= ? AND max_capacity >= ?) OR
            (? <= min_capacity AND ? >= max_capacity)
        )", [$minCapacity, $minCapacity, $maxCapacity, $maxCapacity, $minCapacity, $maxCapacity]);
        
        if ($overlap && isset($_POST['status']) && $_POST['status'] === 'active') {
            jsonResponse(false, 'Overlapping active pricing rule exists.');
        }
        
        $db->execute("INSERT INTO platform_pricing_rules (name, min_capacity, max_capacity, price, currency, status, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $name, $minCapacity, $maxCapacity, $price, $currency, $_POST['status'] ?? 'inactive', 0
        ]);
        
        jsonResponse(true, 'Pricing rule created successfully.');
    }
    
    public static function update(int $id): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        
        $db = Database::getInstance();
        $name = $_POST['name'] ?? '';
        $minCapacity = (int)($_POST['min_capacity'] ?? 0);
        $maxCapacity = (int)($_POST['max_capacity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $currency = $_POST['currency'] ?? 'USD';
        
        if (empty($name) || $minCapacity < 0 || $maxCapacity < $minCapacity || $price < 0) {
            jsonResponse(false, 'Invalid input.');
        }
        
        $overlap = $db->queryOne("SELECT id FROM platform_pricing_rules WHERE id != ? AND status = 'active' AND (
            (min_capacity <= ? AND max_capacity >= ?) OR
            (min_capacity <= ? AND max_capacity >= ?) OR
            (? <= min_capacity AND ? >= max_capacity)
        )", [$id, $minCapacity, $minCapacity, $maxCapacity, $maxCapacity, $minCapacity, $maxCapacity]);
        
        if ($overlap && isset($_POST['status']) && $_POST['status'] === 'active') {
            jsonResponse(false, 'Overlapping active pricing rule exists.');
        }
        
        $db->execute("UPDATE platform_pricing_rules SET name = ?, min_capacity = ?, max_capacity = ?, price = ?, currency = ?, status = ? WHERE id = ?", [
            $name, $minCapacity, $maxCapacity, $price, $currency, $_POST['status'] ?? 'inactive', $id
        ]);
        
        jsonResponse(true, 'Pricing rule updated successfully.');
    }
    
    public static function toggle(int $id): void {
        requireAuth();
        requirePlatformAdmin();
        requireCsrf();
        
        $db = Database::getInstance();
        $rule = $db->queryOne("SELECT * FROM platform_pricing_rules WHERE id = ?", [$id]);
        if (!$rule) jsonResponse(false, 'Not found.');
        
        $newStatus = $rule['status'] === 'active' ? 'inactive' : 'active';
        
        if ($newStatus === 'active') {
            $overlap = $db->queryOne("SELECT id FROM platform_pricing_rules WHERE id != ? AND status = 'active' AND (
                (min_capacity <= ? AND max_capacity >= ?) OR
                (min_capacity <= ? AND max_capacity >= ?) OR
                (? <= min_capacity AND ? >= max_capacity)
            )", [$id, $rule['min_capacity'], $rule['min_capacity'], $rule['max_capacity'], $rule['max_capacity'], $rule['min_capacity'], $rule['max_capacity']]);
            if ($overlap) jsonResponse(false, 'Overlapping active pricing rule exists.');
        }
        
        $db->execute("UPDATE platform_pricing_rules SET status = ? WHERE id = ?", [$newStatus, $id]);
        jsonResponse(true, 'Status toggled successfully.');
    }
}

class PlatformPricingController extends PricingController {}

