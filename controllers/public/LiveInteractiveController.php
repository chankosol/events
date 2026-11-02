<?php
// controllers/public/LiveInteractiveController.php

class LiveInteractiveController {
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

        $sessions = $db->query(
            "SELECT * FROM workshop_sessions WHERE workshop_id = ? ORDER BY session_date, start_time",
            [$workshopId]
        );

        $currentUser = null;
        if (Auth::isParticipant()) {
            $currentUser = Auth::participant();
        }

        $title = 'ចូលរួមសួរផ្ទាល់ (Live Q&A) - ' . $workshop['name'];
        require VIEWS_PATH . '/public/live_interactive.php';
    }
}
