<?php
// controllers/public/LiveStageController.php

class LiveStageController {
    public static function index(int $workshopId): void {
        Session::start();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT w.*, b.name as business_name
             FROM workshops w
             JOIN businesses b ON b.id = w.business_id
             WHERE w.id = ? AND w.deleted_at IS NULL",
            [$workshopId]
        );

        if (!$workshop) {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            exit;
        }

        $title = 'ផ្ទាំងបញ្ចាំងផ្ទាល់ក្នុងសាល (Stage Display) - ' . $workshop['name'];
        require VIEWS_PATH . '/business/live/stage_screen.php';
    }
}
