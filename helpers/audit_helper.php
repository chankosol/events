<?php
function auditLog(
    string $action,
    string $entityType,
    mixed  $entityId,
    mixed  $oldValue  = null,
    mixed  $newValue  = null,
    ?int   $businessId = null
): void {
    try {
        $db       = Database::getInstance();
        $user     = Auth::user();
        $userId   = $user['id'] ?? null;
        $bizId    = $businessId ?? ($user['business_id'] ?? null);
        $ip       = $_SERVER['REMOTE_ADDR']   ?? '127.0.0.1';
        $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $db->execute(
            "INSERT INTO audit_logs
             (user_id, business_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $bizId,
                $action,
                $entityType,
                (string)$entityId,
                $oldValue !== null ? json_encode($oldValue) : null,
                $newValue !== null ? json_encode($newValue) : null,
                $ip,
                substr($ua, 0, 500),
            ]
        );
    } catch (Exception $e) {
        // Never let audit failure break the request
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
