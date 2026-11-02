<?php
// api/workshops/questions.php

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Permission.php';

Session::start();

if (!headers_sent()) {
    header('Content-Type: application/json');
}

function api_workshop_questions(int $workshopId): void {
    if (!$workshopId) {
        echo json_encode(['success' => false, 'message' => 'Workshop ID required']);
        exit;
    }

    $db = Database::getInstance();
    $workshop = $db->queryOne("SELECT id, name, qa_status, qa_ends_at FROM workshops WHERE id = ? AND deleted_at IS NULL", [$workshopId]);
    if (!$workshop) {
        echo json_encode(['success' => false, 'message' => 'Workshop not found']);
        exit;
    }

    $qaStatus = $workshop['qa_status'] ?? 'open';
    $qaEndsAt = $workshop['qa_ends_at'] ?? null;
    $remainingSeconds = null;

    if (!empty($qaEndsAt)) {
        $endTime = strtotime($qaEndsAt);
        $remainingSeconds = max(0, $endTime - time());
        if ($remainingSeconds <= 0 && $qaStatus === 'open') {
            $qaStatus = 'closed';
        }
    }

    $qaControl = [
        'status' => $qaStatus,
        'ends_at' => $qaEndsAt,
        'remaining_seconds' => $remainingSeconds
    ];

    $isAdmin = false;
    if (Auth::check() && (Auth::isBusinessUser() || Auth::isPlatformAdmin() || Permission::has('workshop.view'))) {
        $isAdmin = true;
    }

    if ($isAdmin && isset($_GET['admin'])) {
        // Organizer / Admin View
        $pending = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
             FROM questions q
             LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'pending'
             ORDER BY q.created_at DESC",
            [$workshopId]
        );

        $approved = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
             FROM questions q
             LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'approved'
             ORDER BY q.is_pinned DESC, q.upvotes DESC, q.created_at DESC",
            [$workshopId]
        );

        $answered = $db->query(
            "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
             FROM questions q
             LEFT JOIN participants p ON p.id = q.participant_id
             WHERE q.workshop_id = ? AND q.status = 'answered'
             ORDER BY COALESCE(q.answered_at, q.updated_at) DESC LIMIT 50",
            [$workshopId]
        );

        echo json_encode([
            'success' => true,
            'workshop' => $workshop,
            'qa_control' => $qaControl,
            'pending' => $pending,
            'approved' => $approved,
            'answered' => $answered,
            'count_pending' => count($pending),
            'count_approved' => count($approved),
            'count_answered' => count($answered)
        ]);
        return;
    }

    // Public Screen / Attendee View: ONLY Approved questions
    $active = $db->query(
        "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
         FROM questions q
         LEFT JOIN participants p ON p.id = q.participant_id
         WHERE q.workshop_id = ? AND q.status = 'approved'
         ORDER BY q.is_pinned DESC, q.upvotes DESC, q.created_at DESC",
        [$workshopId]
    );

    $answered = $db->query(
        "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
         FROM questions q
         LEFT JOIN participants p ON p.id = q.participant_id
         WHERE q.workshop_id = ? AND q.status = 'answered'
         ORDER BY COALESCE(q.answered_at, q.updated_at) DESC LIMIT 50",
        [$workshopId]
    );

    // Optional: Fetch current attendee's own pending questions if requested
    $myPending = [];
    $myIdsParam = trim($_GET['my_ids'] ?? '');
    if (!empty($myIdsParam)) {
        $rawIds = array_filter(array_map('intval', explode(',', $myIdsParam)));
        if (!empty($rawIds)) {
            $placeholders = implode(',', array_fill(0, count($rawIds), '?'));
            $params = array_merge([$workshopId], $rawIds);
            $myPending = $db->query(
                "SELECT q.*, COALESCE(q.author_name, p.name, 'មិនបញ្ចេញឈ្មោះ') as display_name
                 FROM questions q
                 LEFT JOIN participants p ON p.id = q.participant_id
                 WHERE q.workshop_id = ? AND q.status = 'pending' AND q.id IN ($placeholders)
                 ORDER BY q.created_at DESC",
                $params
            );
        }
    }

    echo json_encode([
        'success' => true,
        'workshop' => $workshop,
        'qa_control' => $qaControl,
        'active' => $active,
        'answered' => $answered,
        'my_pending' => $myPending,
        'count_active' => count($active),
        'count_answered' => count($answered)
    ]);
}
