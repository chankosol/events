<?php
class CertificateController {
    public static function index(int $workshopId): void {
        requireAuth();
        requirePermission('certificate.view');
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ? AND business_id = ? AND deleted_at IS NULL", [$workshopId, $businessId]);
        if (!$workshop) { http_response_code(404); die(); }
        
        // Get certificate rules for this workshop
        $rules = $db->query("SELECT * FROM certificate_rules WHERE workshop_id = ?", [$workshopId]);
        
        // Calculate eligible participants
        $eligible = self::calculateEligible($db, $workshopId, $businessId, $rules);
        
        // Get issued certificates
        $certificates = $db->query(
            "SELECT c.*, p.name as participant_name, p.email, r.registration_code
             FROM certificates c
             JOIN registrations r ON r.id = c.registration_id
             JOIN participants p ON p.id = c.participant_id
             WHERE c.workshop_id = ? AND c.business_id = ?
             ORDER BY c.created_at DESC",
            [$workshopId, $businessId]
        );
        
        $title = 'Certificates - ' . $workshop['name'];
        ob_start();
        require VIEWS_PATH . '/business/certificates/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/business.php';
    }
    
    private static function calculateEligible(object $db, int $workshopId, int $businessId, array $rules): array {
        // Get all confirmed registrations
        $registrations = $db->query(
            "SELECT r.id, r.participant_id, r.registration_code,
                    p.name, p.email,
                    COALESCE(a.checked_in_at IS NOT NULL, 0) as attended,
                    (SELECT COUNT(*) FROM session_attendance sa WHERE sa.registration_id=r.id AND sa.status='present') as sessions_attended,
                    (SELECT COUNT(*) FROM workshop_sessions ws WHERE ws.workshop_id=r.workshop_id AND ws.status!='cancelled') as total_sessions,
                    (SELECT MAX(ta.percentage) FROM test_attempts ta WHERE ta.registration_id=r.id AND ta.passed=1) as test_score,
                    (SELECT COUNT(*) FROM feedback_responses fr WHERE fr.registration_id=r.id) as feedback_count,
                    (SELECT id FROM certificates c WHERE c.registration_id=r.id) as cert_id
             FROM registrations r
             JOIN participants p ON p.id = r.participant_id
             LEFT JOIN attendance a ON a.registration_id = r.id
             WHERE r.workshop_id = ? AND r.business_id = ? AND r.status IN ('confirmed','attended','completed') AND r.deleted_at IS NULL",
            [$workshopId, $businessId]
        );
        
        $eligible = [];
        foreach ($registrations as $reg) {
            $isEligible = true;
            foreach ($rules as $rule) {
                switch ($rule['rule_type']) {
                    case 'attendance_percent':
                        if ($reg['total_sessions'] > 0) {
                            $pct = ($reg['sessions_attended'] / $reg['total_sessions']) * 100;
                            if ($pct < (float)$rule['rule_value']) $isEligible = false;
                        } elseif (!$reg['attended']) {
                            $isEligible = false;
                        }
                        break;
                    case 'test_pass':
                        if (!$reg['test_score'] || $reg['test_score'] < (float)$rule['rule_value']) $isEligible = false;
                        break;
                    case 'feedback_completed':
                        if (!$reg['feedback_count']) $isEligible = false;
                        break;
                    case 'session_count':
                        if ($reg['sessions_attended'] < (int)$rule['rule_value']) $isEligible = false;
                        break;
                }
            }
            if ($isEligible) {
                $reg['is_eligible'] = true;
                $eligible[] = $reg;
            }
        }
        return $eligible;
    }
    
    public static function issue(int $workshopId): void {
        requireAuth();
        requirePermission('certificate.issue');
        requireCsrf();
        $businessId = Tenant::id();
        $db = Database::getInstance();
        
        $registrationIds = $_POST['registration_ids'] ?? [];
        if (empty($registrationIds)) jsonResponse(false, 'No registrations selected.');
        
        $issued = 0;
        foreach ($registrationIds as $regId) {
            $regId = (int)$regId;
            // Verify belongs to business
            $reg = $db->queryOne("SELECT r.*, p.name as participant_name FROM registrations r JOIN participants p ON p.id = r.participant_id WHERE r.id = ? AND r.business_id = ? AND r.deleted_at IS NULL", [$regId, $businessId]);
            if (!$reg) continue;
            
            // Skip if already issued
            $existing = $db->queryOne("SELECT id FROM certificates WHERE registration_id = ?", [$regId]);
            if ($existing) continue;
            
            $certNumber = generateCertificateNumber('CERT');
            $verifyToken = bin2hex(random_bytes(32));
            
            $db->execute(
                "INSERT INTO certificates (certificate_number, business_id, workshop_id, registration_id, participant_id, verification_token, status, issued_at, issued_by)
                 VALUES (?, ?, ?, ?, ?, ?, 'issued', NOW(), ?)",
                [$certNumber, $businessId, $workshopId, $regId, $reg['participant_id'], $verifyToken, Auth::id()]
            );
            $issued++;
            auditLog('certificate_issued', 'certificate', $db->lastInsertId());
        }
        
        jsonResponse(true, "{$issued} certificate(s) issued successfully.", ['issued' => $issued]);
    }
}
