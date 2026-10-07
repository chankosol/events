<?php
class CertificateVerifyController {
    public static function show(string $token): void {
        $db = Database::getInstance();
        $cert = $db->queryOne(
            "SELECT c.*, p.name as participant_name,
                    w.name as workshop_name, w.start_date, w.end_date, w.trainer_name,
                    b.name as issuer_name
             FROM certificates c
             JOIN participants p ON p.id = c.participant_id
             JOIN workshops w ON w.id = c.workshop_id
             JOIN businesses b ON b.id = c.business_id
             WHERE c.verification_token = ? AND c.status = 'issued'",
            [$token]
        );
        
        $title = $cert ? 'Certificate Verified - ' . APP_NAME : 'Invalid Certificate';
        // DO NOT expose: email, phone, payment info
        ob_start();
        require VIEWS_PATH . '/public/certificate_verify.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/public.php';
    }
}
