<?php
function formatDate(?string $date, string $format = 'd M Y'): string {
    if (empty($date) || $date === '0000-00-00') return '-';
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

function formatDateTime(?string $datetime, string $format = 'd M Y, H:i'): string {
    if (empty($datetime)) return '-';
    try {
        return (new DateTime($datetime))->format($format);
    } catch (Exception $e) {
        return '-';
    }
}

function formatCurrency(float|string|null $amount, string $currency = 'USD'): string {
    $amount = (float)($amount ?? 0);
    return match(strtoupper($currency)) {
        'KHR' => number_format($amount, 0) . ' ៛',
        'USD' => '$' . number_format($amount, 2),
        default => number_format($amount, 2) . ' ' . strtoupper($currency),
    };
}

function formatFileSize(int $bytes): string {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576)    return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024)       return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' bytes';
}

function slugify(string $text, string $defaultPrefix = 'item'): string {
    $text = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (empty($slug)) {
        $slug = $defaultPrefix . '-' . date('Ymd') . '-' . substr(bin2hex(random_bytes(3)), 0, 4);
    }
    return $slug;
}

function uniqueSlug(string $text, string $table, string $column, ?int $excludeId = null, ?int $businessId = null): string {
    $db     = Database::getInstance();
    $prefix = ($table === 'workshops') ? 'workshop' : (($table === 'businesses') ? 'biz' : 'item');
    $base   = slugify($text, $prefix);
    if (empty($base)) {
        $base = $prefix . '-' . date('Ymd') . '-' . substr(bin2hex(random_bytes(3)), 0, 4);
    }
    $slug = $base;
    $i    = 1;
    do {
        $params = [$slug];
        $sql = "SELECT id FROM {$table} WHERE {$column} = ?";
        if ($businessId !== null) {
            $sql .= ' AND business_id = ?';
            $params[] = $businessId;
        }
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $exists = $db->queryOne($sql, $params);
        if (!$exists) break;
        $slug = $base . '-' . $i++;
    } while (true);
    return $slug;
}

function qrCodeUrl(string $path = ''): string {
    $url = defined('APP_URL') ? APP_URL : 'http://localhost/workshopos';
    if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
        $localIp = gethostbyname(gethostname());
        if (!empty($localIp) && $localIp !== '127.0.0.1' && !str_starts_with($localIp, '127.')) {
            $url = str_replace(['localhost', '127.0.0.1'], $localIp, $url);
        }
    }
    return rtrim($url, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
}

function truncate(string $text, int $length = 100, string $suffix = '...'): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

if (!function_exists('e')) {
    function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

function generateCode(string $prefix = 'REG', int $length = 8): string {
    return strtoupper($prefix . bin2hex(random_bytes($length / 2)));
}

function generateInvoiceNumber(): string {
    return 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function generateCertificateNumber(string $prefix = 'CERT'): string {
    return $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function timeAgo(?string $datetime): string {
    if (empty($datetime)) return '-';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return $diff . ' sec ago';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}

function statusBadge(?string $status): string {
    $status = $status ?? '';
    $map = [
        'active'               => ['success', 'សកម្ម'],
        'confirmed'            => ['success', 'បានបញ្ជាក់ផ្លូវការ'],
        'paid'                 => ['success', 'បានបង់ប្រាក់រួច'],
        'paid_cash'            => ['success', 'បង់ប្រាក់សុទ្ធ'],
        'attended'             => ['success', 'បានចូលរួម'],
        'completed'            => ['success', 'បានបញ្ចប់'],
        'issued'               => ['success', 'បានចេញរួច'],
        'approved'             => ['success', 'បានអនុម័ត'],
        'pending'              => ['warning', 'រង់ចាំពិនិត្យ'],
        'pending_payment'      => ['warning', 'រង់ចាំបង់ប្រាក់'],
        'pending_approval'     => ['warning', 'រង់ចាំការអនុម័ត'],
        'pending_verification' => ['warning', 'រង់ចាំផ្ទៀងផ្ទាត់'],
        'in_progress'          => ['info', 'កំពុងដំណើរការ'],
        'draft'                => ['secondary', 'ព្រាងទុក'],
        'inactive'             => ['secondary', 'អសកម្ម'],
        'waitlisted'           => ['info', 'បញ្ជីរង់ចាំ'],
        'cancelled'            => ['danger', 'បានបោះបង់'],
        'rejected'             => ['danger', 'បានបដិសេធ'],
        'unpaid'               => ['danger', 'មិនទាន់បង់'],
        'revoked'              => ['danger', 'បានដកហូត'],
        'suspended'            => ['danger', 'បានផ្អាក'],
        'complimentary'        => ['primary', 'សំបុត្រកិត្តិយស'],
        'waived'               => ['primary', 'លើកលែងការបង់'],
        'registration_open'    => ['success', 'កំពុងបើកចុះឈ្មោះ'],
        'registration_closed'  => ['secondary', 'បានបិទចុះឈ្មោះ'],
    ];
    $s = strtolower($status);
    $color = $map[$s][0] ?? 'secondary';
    $label = $map[$s][1] ?? ucwords(str_replace('_', ' ', $status));
    return "<span class=\"badge bg-{$color}\">" . e($label) . '</span>';
}

function businessLogoHtml(array|object $business, int $size = 42, string $class = ''): string {
    if (is_object($business)) {
        $business = (array)$business;
    }
    $name = $business['name'] ?? ($business['business_name'] ?? 'Company');
    $logoPath = $business['logo_path'] ?? ($business['logo'] ?? '');

    // Check if uploaded logo file exists on disk
    if (!empty($logoPath)) {
        $fullPath = ROOT_PATH . '/' . ltrim($logoPath, '/\\');
        if (file_exists($fullPath)) {
            $url = function_exists('getUploadUrl') ? getUploadUrl($logoPath) : APP_URL . '/' . ltrim($logoPath, '/\\');
            return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="rounded-circle border object-fit-cover shadow-sm bg-white ' . $class . '" style="width:' . $size . 'px;height:' . $size . 'px;flex-shrink:0;">';
        }
    }

    // Generate clean brand initials
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    if (count($words) >= 1 && mb_strlen($words[0]) <= 4 && ctype_upper($words[0])) {
        // e.g. "KSH Training Institute" -> "KSH"
        $initials = $words[0];
    } elseif (count($words) >= 2) {
        // e.g. "Competitor Academy" -> "CA"
        $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    } else {
        $initials = mb_strtoupper(mb_substr($name, 0, 2));
    }

    // Modern color palettes based on business ID or name hash
    $palettes = [
        ['#0d6efd', '#0a58ca'], // Classic Blue
        ['#198754', '#146c43'], // Forest Green
        ['#6f42c1', '#59359a'], // Royal Purple
        ['#d63384', '#a61e60'], // Vivid Pink
        ['#fd7e14', '#ca6510'], // Warm Orange
        ['#0dcaf0', '#0aa2c0'], // Cyan
        ['#20c997', '#179973'], // Teal
        ['#495057', '#212529'], // Dark Slate
    ];
    $idx = abs(crc32($name)) % count($palettes);
    $bgGrad = "linear-gradient(135deg, {$palettes[$idx][0]} 0%, {$palettes[$idx][1]} 100%)";

    return '<div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm ' . $class . '" style="width:' . $size . 'px;height:' . $size . 'px;flex-shrink:0;background:' . $bgGrad . ';font-size:' . round($size * 0.36) . 'px;letter-spacing:0.5px;" title="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</div>';
}

function currentPath(): string {
    $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
    if (isset($_GET['_url']) && !empty($_GET['_url'])) {
        $uri = '/' . ltrim($_GET['_url'], '/');
    } else {
        $uri = parse_url($rawUri, PHP_URL_PATH) ?: '/';
    }
    $basePath = parse_url(defined('APP_URL') ? APP_URL : 'http://localhost/workshopos', PHP_URL_PATH) ?: '/workshopos';
    $basePath = rtrim($basePath, '/');
    if ($basePath !== '') {
        while (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            if ($uri === '' || $uri === false) {
                $uri = '/';
                break;
            }
        }
    }
    $path = '/' . trim($uri, '/');
    return ($path === '//' || $path === '') ? '/' : $path;
}

function isNavActive(string|array $routes, bool|array $exact = false, array $except = []): bool {
    $current = currentPath();
    if (is_array($exact)) {
        $except = $exact;
        $exact = false;
    }
    $routes = (array)$routes;
    foreach ($routes as $route) {
        $target = '/' . trim($route, '/');
        if ($target === '//' || $target === '') $target = '/';

        if ($exact || $target === '/platform' || $target === '/dashboard' || $target === '/') {
            if ($current === $target) {
                return true;
            }
        } else {
            if ($current === $target || str_starts_with($current, $target . '/')) {
                $isExcluded = false;
                foreach ($except as $ex) {
                    $exTarget = '/' . trim($ex, '/');
                    if ($current === $exTarget || str_starts_with($current, $exTarget . '/')) {
                        $isExcluded = true;
                        break;
                    }
                }
                if (!$isExcluded) {
                    return true;
                }
            }
        }
    }
    return false;
}

function navActive(string|array $routes, bool|array $exact = false, string $activeClass = 'active', array $except = []): string {
    if (is_array($exact)) {
        $except = $exact;
        $exact = false;
    }
    return isNavActive($routes, $exact, $except) ? $activeClass : '';
}

function buildBreadcrumbs(?array $workshop = null, ?string $title = null): array {
    $path = trim(currentPath(), '/');
    $parts = explode('/', $path);

    $crumbs = [
        ['label' => 'ទំព័រដើម', 'url' => APP_URL . '/dashboard', 'icon' => 'house-door-fill']
    ];

    if (empty($parts[0]) || $parts[0] === 'dashboard') {
        return $crumbs;
    }

    if ($parts[0] === 'workshops') {
        if (!isset($parts[1]) || $parts[1] === '') {
            $crumbs[] = ['label' => 'សិក្ខាសាលាទាំងអស់', 'url' => null, 'icon' => 'collection-play'];
            return $crumbs;
        }

        $crumbs[] = ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'];

        if ($parts[1] === 'create') {
            $crumbs[] = ['label' => 'បង្កើតសិក្ខាសាលាថ្មី', 'url' => null, 'icon' => 'plus-circle'];
            return $crumbs;
        }

        if (is_numeric($parts[1])) {
            $wsId = (int)$parts[1];
            $wsName = $workshop['name'] ?? null;
            if (!$wsName) {
                try {
                    $row = Database::getInstance()->queryOne("SELECT name FROM workshops WHERE id = ?", [$wsId]);
                    $wsName = $row['name'] ?? ('សិក្ខាសាលា #' . $wsId);
                } catch (\Throwable $e) {
                    $wsName = 'សិក្ខាសាលា #' . $wsId;
                }
            }

            $wsShortName = mb_strimwidth($wsName, 0, 35, '…');
            $wsUrl = APP_URL . '/workshops/' . $wsId;

            if (!isset($parts[2]) || $parts[2] === '') {
                $crumbs[] = ['label' => $wsShortName, 'url' => null, 'icon' => null];
                return $crumbs;
            }

            $crumbs[] = ['label' => $wsShortName, 'url' => $wsUrl, 'icon' => null];

            $action = $parts[2];
            $actionMap = [
                'activate'      => ['label' => 'បង់ថ្លៃដំណើរការប្រព័ន្ធ (Activation)', 'icon' => 'shield-check'],
                'edit'          => ['label' => 'កែសម្រួល', 'icon' => 'pencil'],
                'delegations'   => ['label' => 'ប្រតិភូ / កូតា ២៥ ខេត្ត', 'icon' => 'geo-alt-fill'],
                'allowances'    => ['label' => 'ថវិកា / ប្រាក់ឧបត្ថម្ភ', 'icon' => 'cash-stack'],
                'registrations' => ['label' => 'បញ្ជីចុះឈ្មោះ', 'icon' => 'person-check'],
                'certificates'  => ['label' => 'វិញ្ញាបនបត្រ', 'icon' => 'award'],
                'gifts'         => ['label' => 'កាដូ / អំណោយ', 'icon' => 'gift'],
                'payments'      => ['label' => 'ការទូទាត់', 'icon' => 'credit-card'],
                'reports'       => ['label' => 'របាយការណ៍', 'icon' => 'graph-up'],
                'duplicate'     => ['label' => 'ចម្លងទម្រង់', 'icon' => 'copy'],
            ];

            if ($action === 'allowances' && isset($parts[3])) {
                $crumbs[] = ['label' => 'ថវិកា / ប្រាក់ឧបត្ថម្ភ', 'url' => $wsUrl . '/allowances', 'icon' => 'cash-stack'];
                if ($parts[3] === 'desk') {
                    $crumbs[] = ['label' => 'តុស្កេនបើកប្រាក់ (Payout Desk)', 'url' => null, 'icon' => 'upc-scan'];
                } elseif ($parts[3] === 'sheet') {
                    $crumbs[] = ['label' => 'តារាងសវនកម្ម (Audit Sheet)', 'url' => null, 'icon' => 'file-earmark-spreadsheet'];
                } else {
                    $crumbs[] = ['label' => ucfirst($parts[3]), 'url' => null, 'icon' => null];
                }
                return $crumbs;
            }

            if (isset($actionMap[$action])) {
                $crumbs[] = ['label' => $actionMap[$action]['label'], 'url' => null, 'icon' => $actionMap[$action]['icon']];
            } else {
                $crumbs[] = ['label' => ucfirst($action), 'url' => null, 'icon' => null];
            }
            return $crumbs;
        }
    }

    if ($parts[0] === 'checkin' && isset($parts[1]) && is_numeric($parts[1])) {
        $wsId = (int)$parts[1];
        $wsName = $workshop['name'] ?? null;
        if (!$wsName) {
            try {
                $row = Database::getInstance()->queryOne("SELECT name FROM workshops WHERE id = ?", [$wsId]);
                $wsName = $row['name'] ?? ('សិក្ខាសាលា #' . $wsId);
            } catch (\Throwable $e) {
                $wsName = 'សិក្ខាសាលា #' . $wsId;
            }
        }
        $crumbs[] = ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'];
        $crumbs[] = ['label' => mb_strimwidth($wsName, 0, 35, '…'), 'url' => APP_URL . '/workshops/' . $wsId, 'icon' => null];
        $crumbs[] = ['label' => 'ស្កេនវត្តមាន (Check-in)', 'url' => null, 'icon' => 'qr-code-scan'];
        return $crumbs;
    }

    if ($parts[0] === 'live' && isset($parts[1]) && is_numeric($parts[1])) {
        $wsId = (int)$parts[1];
        $wsName = $workshop['name'] ?? null;
        if (!$wsName) {
            try {
                $row = Database::getInstance()->queryOne("SELECT name FROM workshops WHERE id = ?", [$wsId]);
                $wsName = $row['name'] ?? ('សិក្ខាសាលា #' . $wsId);
            } catch (\Throwable $e) {
                $wsName = 'សិក្ខាសាលា #' . $wsId;
            }
        }
        $crumbs[] = ['label' => 'សិក្ខាសាលា', 'url' => APP_URL . '/workshops', 'icon' => 'calendar-event'];
        $crumbs[] = ['label' => mb_strimwidth($wsName, 0, 35, '…'), 'url' => APP_URL . '/workshops/' . $wsId, 'icon' => null];
        $crumbs[] = ['label' => 'មជ្ឈមណ្ឌលផ្ទាល់ (Live)', 'url' => null, 'icon' => 'broadcast'];
        return $crumbs;
    }

    $topSectionMap = [
        'billing'  => ['label' => 'ថ្លៃសេវាប្រព័ន្ធ (System A)', 'icon' => 'receipt'],
        'reports'  => ['label' => 'របាយការណ៍សរុប', 'icon' => 'graph-up'],
        'staff'    => ['label' => 'ក្រុមការងារ និងសិទ្ធិ', 'icon' => 'people'],
        'settings' => ['label' => 'ការកំណត់ និង QR ទទួលប្រាក់', 'icon' => 'sliders'],
        'profile'  => ['label' => 'ព័ត៌មានផ្ទាល់ខ្លួន & រូបថត', 'icon' => 'person-gear'],
    ];

    if (isset($topSectionMap[$parts[0]])) {
        $crumbs[] = ['label' => $topSectionMap[$parts[0]]['label'], 'url' => null, 'icon' => $topSectionMap[$parts[0]]['icon']];
        return $crumbs;
    }

    if (!empty($title)) {
        $cleanTitle = trim(explode('-', $title)[0]);
        $crumbs[] = ['label' => $cleanTitle, 'url' => null, 'icon' => null];
    }

    return $crumbs;
}

function userRoleTitle(?array $user): string {
    if (!$user) return '';
    if (empty($user['business_id'])) {
        return 'អ្នកគ្រប់គ្រងប្រព័ន្ធ (Super Admin)';
    }
    if (isset($user['role_name']) && !empty($user['role_name'])) {
        return $user['role_name'];
    }
    try {
        $db = Database::getInstance();
        $role = $db->queryOne(
            "SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id = r.id WHERE ur.user_id = ? ORDER BY r.id ASC LIMIT 1",
            [$user['id']]
        );
        if ($role && !empty($role['name'])) {
            return $role['name'];
        }
    } catch (Throwable $e) {}
    return 'សមាជិកស្ថាប័ន (Staff)';
}

function userAvatarHtml(?array $user, int $size = 36, string $class = ''): string {
    if (!$user) {
        return '<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center ' . $class . '" style="width:' . $size . 'px;height:' . $size . 'px;font-size:' . round($size * 0.4) . 'px;"><i class="bi bi-person"></i></div>';
    }
    $name = trim($user['name'] ?? 'User');
    $photo = $user['profile_photo'] ?? '';
    if (!empty($photo)) {
        $fullPath = ROOT_PATH . '/' . ltrim($photo, '/\\');
        if (file_exists($fullPath)) {
            $url = function_exists('getUploadUrl') ? getUploadUrl($photo) : APP_URL . '/' . ltrim($photo, '/\\');
            return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="rounded-circle object-fit-cover border shadow-sm ' . $class . '" style="width:' . $size . 'px;height:' . $size . 'px;flex-shrink:0;">';
        }
    }

    // Initials
    $words = preg_split('/\s+/', $name);
    if (count($words) >= 2) {
        $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[count($words) - 1], 0, 1));
    } else {
        $initials = mb_strtoupper(mb_substr($name, 0, 2));
    }

    $isPlatform = empty($user['business_id']);
    $bgGrad = $isPlatform
        ? 'linear-gradient(135deg, #dc3545 0%, #b02a37 100%)'
        : 'linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%)';

    return '<div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm ' . $class . '" style="width:' . $size . 'px;height:' . $size . 'px;flex-shrink:0;background:' . $bgGrad . ';font-size:' . round($size * 0.38) . 'px;letter-spacing:0.5px;">' . htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') . '</div>';
}

function cambodiaProvinces(): array {
    return [
        'រាជធានីភ្នំពេញ',
        'ខេត្តកណ្តាល',
        'ខេត្តកំពង់ចាម',
        'ខេត្តកំពង់ឆ្នាំង',
        'ខេត្តកំពង់ស្ពឺ',
        'ខេត្តកំពង់ធំ',
        'ខេត្តកំពត',
        'ខេត្តកែប',
        'ខេត្តកោះកុង',
        'ខេត្តក្រចេះ',
        'ខេត្តតាកែវ',
        'ខេត្តត្បូងឃ្មុំ',
        'ខេត្តបន្ទាយមានជ័យ',
        'ខេត្តបាត់ដំបង',
        'ខេត្តប៉ៃលិន',
        'ខេត្តពោធិ៍សាត់',
        'ខេត្តព្រៃវែង',
        'ខេត្តព្រះវិហារ',
        'ខេត្តព្រះសីហនុ',
        'ខេត្តមណ្ឌលគិរី',
        'ខេត្តរតនគិរី',
        'ខេត្តសៀមរាប',
        'ខេត្តស្ទឹងត្រែង',
        'ខេត្តស្វាយរៀង',
        'ខេត្តឧត្តរមានជ័យ',
    ];
}

function attendeeTypes(): array {
    return [
        'delegate'         => ['label' => 'ប្រតិភូផ្លូវការ', 'badge' => 'primary', 'default_allowance' => 1],
        'vip_assistant'    => ['label' => 'ជំនួយការគណៈអធិបតី / ពិធីការ', 'badge' => 'info text-dark', 'default_allowance' => 0],
        'vip_guest'        => ['label' => 'ភ្ញៀវកិត្តិយស / ភរិយា', 'badge' => 'warning text-dark', 'default_allowance' => 0],
        'media'            => ['label' => 'សារព័ត៌មាន / ជាងថតរូប', 'badge' => 'purple', 'default_allowance' => 0],
        'driver_security'  => ['label' => 'អ្នកបើកបរ / អង្គរក្ស', 'badge' => 'secondary', 'default_allowance' => 0],
        'extra_delegate'   => ['label' => 'ប្រតិភូមកលើសកូតា', 'badge' => 'dark', 'default_allowance' => 0],
        'observer'         => ['label' => 'អ្នកសង្កេតការណ៍', 'badge' => 'light text-dark border', 'default_allowance' => 0],
    ];
}

function attendeeTypeLabel(string $type): string {
    $types = attendeeTypes();
    return $types[$type]['label'] ?? 'សិក្ខាកាមទូទៅ';
}

function attendeeTypeBadge(string $type): string {
    $types = attendeeTypes();
    $info = $types[$type] ?? ['label' => 'ប្រតិភូផ្លូវការ', 'badge' => 'primary'];
    $color = $info['badge'];
    $style = $color === 'purple' ? 'style="background-color: #6f42c1; color: white;"' : '';
    $cls = $color === 'purple' ? 'badge' : "badge bg-{$color}";
    return "<span class=\"{$cls}\" {$style}>" . htmlspecialchars($info['label'], ENT_QUOTES, 'UTF-8') . "</span>";
}

/**
 * Generate a mobile-accessible public URL.
 * When on localhost / 127.0.0.1, replaces host with LAN IP so phones on the same Wi-Fi can open it.
 */
function shareableUrl(string $path = ''): string {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0) {
        $lanIp = gethostbyname(gethostname());
        if (!empty($lanIp) && $lanIp !== '127.0.0.1') {
            $port = '';
            if (strpos($host, ':') !== false) {
                $port = ':' . explode(':', $host)[1];
            }
            $base = "{$scheme}://{$lanIp}{$port}/workshopos";
            return rtrim($base, '/') . '/' . ltrim($path, '/');
        }
    }
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}


