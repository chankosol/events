<?php
// C:\xampp\htdocs\workshopos\api\billing/calculate.php

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';

header('Content-Type: application/json');

function api_calculate_pricing(): void {
    $capacity = max(1, (int)($_GET['capacity'] ?? 100));
    $db = Database::getInstance();

    $rule = $db->queryOne(
        "SELECT * FROM platform_pricing_rules
         WHERE status = 'active' AND min_capacity <= ?
         AND (max_capacity IS NULL OR max_capacity >= ?)
         ORDER BY sort_order ASC LIMIT 1",
        [$capacity, $capacity]
    );

    if ($rule) {
        echo json_encode([
            'success' => true,
            'data'    => [
                'capacity'     => $capacity,
                'tier_name'    => $rule['name'],
                'platform_fee' => (float)$rule['price'],
                'currency'     => $rule['currency'],
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Custom pricing tier. Contact platform owner.']);
    }
}
