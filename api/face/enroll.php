<?php
// api/face/enroll.php
// Real Face Enrollment API for participants & organizers

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/core/Database.php';
require_once ROOT_PATH . '/core/Session.php';
require_once ROOT_PATH . '/core/Auth.php';
require_once ROOT_PATH . '/core/Tenant.php';

Session::start();
if (Auth::check()) {
    Tenant::resolve();
}

header('Content-Type: application/json; charset=utf-8');

// Read input (support JSON payload or Form data)
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
}

$regCode = trim($input['registration_code'] ?? ($input['code'] ?? ''));
$descriptor = $input['descriptor'] ?? null;
$photoBase64 = $input['photo'] ?? ($input['image'] ?? null);

if (empty($regCode)) {
    echo json_encode(['success' => false, 'message' => 'សូមបញ្ចូលលេខកូដសំបុត្រ (Registration Code)។']);
    exit;
}

// Ensure descriptor is array of 128 numbers
if (is_string($descriptor)) {
    $descriptor = json_decode($descriptor, true);
}

if (!is_array($descriptor) || count($descriptor) !== 128) {
    echo json_encode(['success' => false, 'message' => 'ទិន្នន័យ Face Descriptor មិនត្រឹមត្រូវ (តម្រូវឱ្យមាន 128 floats)។']);
    exit;
}

$db = Database::getInstance();

// Look up registration by registration_code
$reg = $db->queryOne(
    "SELECT r.id as registration_id, r.business_id, r.workshop_id, r.participant_id, r.status,
            p.name as participant_name, p.photo as current_photo,
            w.name as workshop_name
     FROM registrations r
     JOIN participants p ON p.id = r.participant_id
     JOIN workshops w ON w.id = r.workshop_id
     WHERE r.registration_code = ? AND r.deleted_at IS NULL",
    [$regCode]
);

if (!$reg) {
    echo json_encode(['success' => false, 'message' => 'រកមិនឃើញទិន្នន័យចុះឈ្មោះជាមួយកូដនេះទេ។']);
    exit;
}

// Handle Photo upload (Base64 JPEG)
$photoPath = null;
if (!empty($photoBase64) && strpos($photoBase64, 'data:image') === 0) {
    try {
        $parts = explode(',', $photoBase64);
        if (count($parts) === 2) {
            $imageData = base64_decode($parts[1]);
            if ($imageData !== false && strlen($imageData) > 100) {
                $dir = ROOT_PATH . '/uploads/faces';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $fileName = 'face_' . $reg['registration_id'] . '_' . time() . '.jpg';
                $fullPath = $dir . '/' . $fileName;
                if (file_put_contents($fullPath, $imageData)) {
                    $photoPath = 'uploads/faces/' . $fileName;
                    // If participant has no photo, also set participant photo
                    if (empty($reg['current_photo'])) {
                        $db->execute("UPDATE participants SET photo = ? WHERE id = ?", [$photoPath, $reg['participant_id']]);
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log('Face photo save error: ' . $e->getMessage());
    }
}

// Encode descriptor array
$descriptorJson = json_encode(array_map('floatval', $descriptor));

try {
    // Upsert into participant_faces
    $db->execute(
        "INSERT INTO participant_faces (business_id, workshop_id, participant_id, registration_id, face_descriptor, photo_path, is_active, enrolled_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
         ON DUPLICATE KEY UPDATE 
            face_descriptor = VALUES(face_descriptor),
            photo_path = COALESCE(VALUES(photo_path), photo_path),
            is_active = 1,
            updated_at = NOW()",
        [
            (int)$reg['business_id'],
            (int)$reg['workshop_id'],
            (int)$reg['participant_id'],
            (int)$reg['registration_id'],
            $descriptorJson,
            $photoPath
        ]
    );

    // Audit log
    if (function_exists('auditLog')) {
        auditLog('face_enrolled', 'registration', (int)$reg['registration_id'], null, [
            'code' => $regCode,
            'name' => $reg['participant_name']
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'បានចុះឈ្មោះផ្ទៃមុខជោគជ័យ! ពេលមកដល់កម្មវិធី លោកអ្នកអាចដើរកាត់កាមេរ៉ាដើម្បី Check-in ភ្លាមៗ។',
        'participant' => [
            'name' => $reg['participant_name'],
            'registration_code' => $regCode,
            'workshop_name' => $reg['workshop_name'],
            'photo_url' => $photoPath ? (APP_URL . '/' . $photoPath) : null,
            'enrolled_at' => date('d/m/Y H:i')
        ]
    ]);
} catch (Exception $e) {
    error_log('Enroll Face DB Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ៖ ' . $e->getMessage()]);
}
