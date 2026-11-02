<?php
// controllers/business/FeedbackController.php

class FeedbackController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('workshop.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();

        $workshop = $db->queryOne(
            "SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL",
            [$workshopId, $businessId]
        );

        if (!$workshop) {
            http_response_code(404);
            require VIEWS_PATH . '/errors/404.php';
            exit;
        }

        // Get or create feedback form for this workshop
        $form = $db->queryOne("SELECT * FROM feedback_forms WHERE workshop_id = ?", [$workshopId]);
        if (!$form) {
            $db->execute(
                "INSERT INTO feedback_forms (business_id, workshop_id, title, status) VALUES (?, ?, ?, 'active')",
                [$businessId, $workshopId, 'ការវាយតម្លៃ និងមតិកែលម្អ - ' . $workshop['name']]
            );
            $form = $db->queryOne("SELECT * FROM feedback_forms WHERE workshop_id = ?", [$workshopId]);
        }

        // Get stats
        $stats = $db->queryOne(
            "SELECT COUNT(*) as total_responses, COALESCE(ROUND(AVG(overall_rating), 1), 0) as avg_rating
             FROM feedback_responses WHERE workshop_id = ?",
            [$workshopId]
        );

        // Rating distribution
        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $counts = $db->query(
            "SELECT ROUND(overall_rating) as r, COUNT(*) as c 
             FROM feedback_responses WHERE workshop_id = ? GROUP BY ROUND(overall_rating)",
            [$workshopId]
        );
        foreach ($counts as $row) {
            $r = (int)$row['r'];
            if (isset($ratingCounts[$r])) {
                $ratingCounts[$r] = (int)$row['c'];
            }
        }

        // Recent responses
        $responses = $db->query(
            "SELECT fr.*, COALESCE(p.name, 'សិក្ខាកាម') as participant_name
             FROM feedback_responses fr
             LEFT JOIN registrations r ON r.id = fr.registration_id
             LEFT JOIN participants p ON p.id = r.participant_id
             WHERE fr.workshop_id = ?
             ORDER BY fr.submitted_at DESC LIMIT 50",
            [$workshopId]
        );

        $title = 'មតិកែលម្អ & ការវាយតម្លៃ - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/feedback/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
}
