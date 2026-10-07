<?php

const UPLOAD_TYPES = [
    'logo'          => ['jpg','jpeg','png','webp','gif'],
    'cover'         => ['jpg','jpeg','png','webp'],
    'banner'        => ['jpg','jpeg','png','webp'],
    'qr_image'      => ['jpg','jpeg','png'],
    'payment_proof' => ['jpg','jpeg','png','pdf'],
    'platform_proof'=> ['jpg','jpeg','png','pdf'],
    'certificate'   => ['jpg','jpeg','png','pdf'],
    'profile_photo' => ['jpg','jpeg','png','webp'],
    'template'      => ['jpg','jpeg','png'],
    'signature'     => ['jpg','jpeg','png'],
    'attachment'    => ['jpg','jpeg','png','pdf','doc','docx','xls','xlsx'],
];

const UPLOAD_MAX_SIZES = [
    'logo'          => 2097152,   // 2MB
    'cover'         => 5242880,   // 5MB
    'banner'        => 5242880,   // 5MB
    'qr_image'      => 2097152,   // 2MB
    'payment_proof' => 10485760,  // 10MB
    'platform_proof'=> 10485760,  // 10MB
    'certificate'   => 20971520,  // 20MB
    'profile_photo' => 2097152,   // 2MB
    'template'      => 5242880,   // 5MB
    'signature'     => 1048576,   // 1MB
    'attachment'    => 10485760,  // 10MB
];

// NEVER allow these extensions regardless of type
const BLOCKED_EXTENSIONS = ['php','php3','php4','php5','phtml','phar','pl','py','sh','cgi','exe','bat','cmd','js','html','htm','xml','svg'];

function uploadFile(array $file, string $type, ?int $businessId = null, ?int $workshopId = null): array {
    if (!isset(UPLOAD_TYPES[$type])) {
        return ['success' => false, 'message' => 'Invalid upload type.'];
    }

    $validate = validateUpload($file, UPLOAD_TYPES[$type], UPLOAD_MAX_SIZES[$type] ?? UPLOAD_MAX_SIZE);
    if (!$validate['success']) return $validate;

    // Build destination path
    if ($workshopId && $businessId) {
        $subDir = "business/{$businessId}/workshops/{$workshopId}/{$type}";
    } elseif ($businessId) {
        $subDir = "business/{$businessId}/{$type}";
    } else {
        $subDir = "users/{$type}";
    }
    $destDir = UPLOAD_PATH . '/' . $subDir;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);

    // Secure random filename
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destPath = $destDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }

    // Set permissions
    chmod($destPath, 0644);

    return [
        'success'    => true,
        'path'       => 'uploads/' . $subDir . '/' . $filename,
        'url'        => UPLOAD_URL . '/' . $subDir . '/' . $filename,
        'filename'   => $filename,
        'size'       => $file['size'],
        'extension'  => $ext,
    ];
}

function validateUpload(array $file, array $allowedExtensions, int $maxSize): array {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
            UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit).',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
        ];
        return ['success' => false, 'message' => $errors[$file['error']] ?? 'Upload failed.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File exceeds maximum size of ' . formatFileSize($maxSize) . '.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Block dangerous extensions
    if (in_array($ext, BLOCKED_EXTENSIONS, true)) {
        return ['success' => false, 'message' => 'File type not allowed.'];
    }

    if (!in_array($ext, $allowedExtensions, true)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)];
    }

    // Validate MIME type using fileinfo
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
        'png' => ['image/png'], 'webp' => ['image/webp'],
        'gif' => ['image/gif'], 'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx'=> ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx'=> ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    ];
    if (isset($allowedMimes[$ext]) && !in_array($mimeType, $allowedMimes[$ext], true)) {
        return ['success' => false, 'message' => 'File content does not match its extension.'];
    }

    return ['success' => true];
}

function getUploadUrl(?string $path): string {
    if (empty($path)) return '';
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $path = ltrim($path, '/\\');
    return APP_URL . '/' . $path;
}

function deleteFile(?string $path): bool {
    if (empty($path)) return false;
    $full = ROOT_PATH . '/' . ltrim($path, '/\\');
    if (file_exists($full) && is_file($full)) {
        return @unlink($full);
    }
    return false;
}
