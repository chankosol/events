<?php
// helpers/telegram_helper.php
// Workshop OS - Official Telegram Bot Helper

if (!function_exists('telegram_get_setting')) {
    function telegram_get_setting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $row = $db->queryOne("SELECT setting_value FROM platform_settings WHERE setting_key = ?", [$key]);
            if ($row && $row['setting_value'] !== null && $row['setting_value'] !== '') {
                return (string)$row['setting_value'];
            }
        } catch (Exception $e) {
            // Fallback to environment
        }
        $envVal = getenv(strtoupper($key));
        return ($envVal !== false && $envVal !== '') ? (string)$envVal : $default;
    }
}

if (!function_exists('telegram_is_bot_active')) {
    function telegram_is_bot_active(): bool {
        $enabled  = telegram_get_setting('telegram_bot_enabled', '0');
        $token    = telegram_get_setting('telegram_bot_token', '');
        $username = telegram_get_setting('telegram_bot_username', '');
        return ($enabled === '1' || $enabled === 'true') && !empty($token) && !empty($username);
    }
}

if (!function_exists('telegram_get_bot_username')) {
    function telegram_get_bot_username(): string {
        $user = telegram_get_setting('telegram_bot_username', '');
        return ltrim(trim($user), '@');
    }
}

if (!function_exists('telegram_get_bot_token')) {
    function telegram_get_bot_token(): string {
        return trim(telegram_get_setting('telegram_bot_token', ''));
    }
}

if (!function_exists('telegram_get_ticket_bot_url')) {
    function telegram_get_ticket_bot_url(string $regCode): string {
        $username = telegram_get_bot_username();
        if (empty($username)) {
            return '';
        }
        return "https://t.me/{$username}?start=" . urlencode($regCode);
    }
}

if (!function_exists('telegram_api_request')) {
    function telegram_api_request(string $method, array $params = [], string $tokenOverride = ''): array {
        $token = !empty($tokenOverride) ? $tokenOverride : telegram_get_bot_token();
        if (empty($token)) {
            return ['ok' => false, 'description' => 'Telegram Bot Token មិនទាន់ត្រូវបានកំណត់។'];
        }

        $url = "https://api.telegram.org/bot{$token}/{$method}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['ok' => false, 'description' => 'cURL Error: ' . $err];
        }

        $json = json_decode((string)$res, true);
        return is_array($json) ? $json : ['ok' => false, 'description' => 'Invalid JSON from Telegram'];
    }
}

if (!function_exists('telegram_db_count')) {
    function telegram_db_count($db, string $sql, array $params = []): int {
        if (method_exists($db, 'queryValue')) {
            return (int)$db->queryValue($sql, $params);
        }
        $row = $db->queryOne($sql, $params);
        if (!$row || !is_array($row)) return 0;
        return (int)reset($row);
    }
}

if (!function_exists('telegram_notify_new_registration')) {
    function telegram_notify_new_registration(int $workshopId, array $regData): bool {
        try {
            if (!telegram_is_bot_active()) {
                return false;
            }

            $db = Database::getInstance();
            $workshop = $db->queryOne("SELECT id, name, capacity, telegram_chat_id, telegram_notify_registration FROM workshops WHERE id = ?", [$workshopId]);
            if (!$workshop || empty($workshop['telegram_chat_id']) || empty($workshop['telegram_notify_registration'])) {
                return false;
            }

            $totalReg = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND deleted_at IS NULL", [$workshopId]);
            $cap = (int)$workshop['capacity'];
            $pct = $cap > 0 ? round(($totalReg / $cap) * 100) : 0;

            $ticketInfo = !empty($regData['ticket_name']) ? $regData['ticket_name'] : 'ស្តង់ដារ';
            if (isset($regData['amount']) && (float)$regData['amount'] > 0) {
                $ticketInfo .= " ($" . number_format((float)$regData['amount'], 2) . ")";
            }

            $payStatusMap = [
                'paid'          => '✅ បង់ប្រាក់រួច (Paid)',
                'paid_cash'     => '💵 បង់សាច់ប្រាក់ (Cash)',
                'pending'       => '⏳ រង់ចាំការទូទាត់ (Pending)',
                'complimentary' => '🎁 ឥតគិតថ្លៃ (Free)',
                'waived'        => '🎁 លើកលែងថ្លៃ'
            ];
            $payStatus = $payStatusMap[$regData['payment_status'] ?? ''] ?? '📝 បានចុះឈ្មោះ';

            $msg = "🔔 <b>ការចុះឈ្មោះថ្មី! (សិក្ខាកាមទី #{$totalReg})</b>\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "📌 <b>កម្មវិធី៖</b> " . htmlspecialchars($workshop['name']) . "\n";
            $msg .= "👤 <b>ឈ្មោះ៖</b> " . htmlspecialchars($regData['name'] ?? '-') . "\n";
            $msg .= "📱 <b>ទូរស័ព្ទ៖</b> " . htmlspecialchars($regData['phone'] ?? '-') . "\n";
            if (!empty($regData['company'])) {
                $msg .= "🏢 <b>ស្ថាប័ន៖</b> " . htmlspecialchars($regData['company']) . "\n";
            }
            if (!empty($regData['position']) && $regData['position'] !== '-') {
                $msg .= "💼 <b>តួនាទី៖</b> " . htmlspecialchars($regData['position']) . "\n";
            }
            $msg .= "🎫 <b>ប្រភេទសំបុត្រ៖</b> " . htmlspecialchars($ticketInfo) . "\n";
            $msg .= "💳 <b>ការទូទាត់៖</b> {$payStatus}\n";
            if (!empty($regData['registration_code'])) {
                $msg .= "🔢 <b>កូដសម្គាល់៖</b> <code>" . htmlspecialchars($regData['registration_code']) . "</code>\n";
            }
            $msg .= "━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "📊 <b>ចំនួនចុះឈ្មោះសរុប៖</b> <b>{$totalReg}</b>" . ($cap > 0 ? " / {$cap} នាក់ ({$pct}% ពេញ)" : " នាក់");

            $res = telegram_api_request('sendMessage', [
                'chat_id'    => $workshop['telegram_chat_id'],
                'text'       => $msg,
                'parse_mode' => 'HTML'
            ]);

            return !empty($res['ok']);
        } catch (Throwable $e) {
            error_log('telegram_notify_new_registration error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('telegram_send_workshop_summary_report')) {
    function telegram_send_workshop_summary_report(int $workshopId): array {
        try {
            if (!telegram_is_bot_active()) {
                return ['ok' => false, 'description' => 'Telegram Bot មិនទាន់ត្រូវបានបើកដំណើរការក្នុង Platform Settings ឡើយ។'];
            }

            $db = Database::getInstance();
            $workshop = $db->queryOne("SELECT * FROM workshops WHERE id = ?", [$workshopId]);
            if (!$workshop) {
                return ['ok' => false, 'description' => 'រកមិនឃើញសិក្ខាសាលាឡើយ។'];
            }

            if (empty($workshop['telegram_chat_id'])) {
                return ['ok' => false, 'description' => 'សិក្ខាសាលានេះពុំទាន់បានភ្ជាប់ Telegram Group Chat ID នៅឡើយទេ។'];
            }

            // Compute live stats
            $totalReg = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND deleted_at IS NULL", [$workshopId]);
            $confirmed = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND status IN ('confirmed','attended','completed') AND deleted_at IS NULL", [$workshopId]);
            $attended = telegram_db_count($db, "SELECT COUNT(DISTINCT registration_id) FROM attendance WHERE workshop_id = ? AND checked_in_at IS NOT NULL", [$workshopId]);
            $pendingPay = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND payment_status = 'pending' AND deleted_at IS NULL", [$workshopId]);
            $waitlist = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND status = 'waitlisted' AND deleted_at IS NULL", [$workshopId]);

            $capacity = max(1, (int)$workshop['capacity']);
            $capPct = min(100, round(($confirmed / $capacity) * 100));
            $checkinPct = $confirmed > 0 ? round(($attended / $confirmed) * 100) : 0;

            $dateText = !empty($workshop['start_date']) ? date('d/m/Y', strtotime($workshop['start_date'])) : '-';
            if (!empty($workshop['start_time'])) {
                $dateText .= ' (ម៉ោង ' . substr($workshop['start_time'], 0, 5) . ')';
            }

            $msg = "📊 <b>របាយការណ៍៖</b> " . htmlspecialchars($workshop['name']) . "\n";
            $msg .= "━━━━━━━━━━━━━━━\n";
            $msg .= "🗓 <b>កាលបរិច្ឆេទ៖</b> {$dateText}\n";
            if (!empty($workshop['venue'])) {
                $msg .= "📍 <b>ទីតាំង៖</b> " . htmlspecialchars($workshop['venue']) . "\n";
            }
            $msg .= "━━━━━━━━━━━━━━━\n";
            $msg .= "👥 <b>ទិន្នន័យសិក្ខាកាម៖</b>\n";
            $msg .= "• ចុះឈ្មោះសរុប៖ <b>{$totalReg}</b> នាក់\n";
            $msg .= "• បានបញ្ជាក់ (Confirmed)៖ <b>{$confirmed}</b> នាក់\n";
            $msg .= "• វត្តមាន Check-in រួច៖ <b>{$attended}</b> នាក់ ({$checkinPct}%)\n";
            if ($pendingPay > 0) {
                $msg .= "• រង់ចាំការទូទាត់៖ <b>{$pendingPay}</b> នាក់\n";
            }
            if ($waitlist > 0) {
                $msg .= "• បញ្ជីរង់ចាំ (Waitlist)៖ <b>{$waitlist}</b> នាក់\n";
            }
            $msg .= "━━━━━━━━━━━━━━━\n";
            $msg .= "📈 <b>អ្នកចូលរួម៖</b> <b>{$confirmed}</b> / {$capacity} នាក់ ({$capPct}% ពេញ)\n";
            $msg .= "⏰ <b>រាយការណ៍ម៉ោង៖</b> " . date('H:i:s, d/m/Y') . "\n";
            $msg .= "📢 <b>រាយការណ៍ដោយអ្នកគ្រប់គ្រង</b>";

            return telegram_api_request('sendMessage', [
                'chat_id'    => $workshop['telegram_chat_id'],
                'text'       => $msg,
                'parse_mode' => 'HTML'
            ]);
        } catch (Throwable $e) {
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}

if (!function_exists('telegram_notify_checkin')) {
    function telegram_notify_checkin(int $workshopId, array $pData, string $method = 'face', string $slotName = ''): bool {
        try {
            if (!telegram_is_bot_active()) {
                return false;
            }

            $db = Database::getInstance();
            $workshop = $db->queryOne("SELECT id, name, capacity, telegram_chat_id FROM workshops WHERE id = ?", [$workshopId]);
            if (!$workshop || empty($workshop['telegram_chat_id'])) {
                return false;
            }

            // Calculate current total attended count
            $attendedCount = telegram_db_count($db, "SELECT COUNT(DISTINCT registration_id) FROM attendance WHERE workshop_id = ? AND checked_in_at IS NOT NULL", [$workshopId]);
            $confirmedCount = telegram_db_count($db, "SELECT COUNT(*) FROM registrations WHERE workshop_id = ? AND status IN ('confirmed','attended','completed') AND deleted_at IS NULL", [$workshopId]);

            $pct = $confirmedCount > 0 ? round(($attendedCount / $confirmedCount) * 100) : 0;

            $methodText = ($method === 'face') ? '👤 ស្កេនផ្ទៃមុខ' : '📷 ស្កេនកូដ QR';
            if ($method === 'helper_station') {
                $methodText = '📱 តុជំនួយការ';
            } elseif ($method === 'manual') {
                $methodText = '✍️ ស្កេនដោយដៃ';
            }

            $vipBadge = !empty($pData['is_vip']) ? ' 🌟 [VIP]' : '';
            $ticketText = !empty($pData['ticket_name']) ? $pData['ticket_name'] : ($pData['ticket'] ?? 'ស្តង់ដារ');

            // Clean slot name to Khmer only
            $cleanSlot = preg_replace('/\s*\([^)]*\)/', '', $slotName);
            $cleanSlot = trim($cleanSlot);

            $msg = "✅ <b>វត្តមាន Check-in ថ្មី! (អ្នកទី #{$attendedCount})</b>\n";
            $msg .= "━━━━━━━━━━━━━━━\n";
            $msg .= "📌 <b>កម្មវិធី៖</b> " . htmlspecialchars($workshop['name']) . "\n";
            $msg .= "👤 <b>ឈ្មោះ៖</b> " . htmlspecialchars($pData['name'] ?? '-') . "{$vipBadge}\n";
            if (!empty($pData['phone'])) {
                $msg .= "📱 <b>ទូរស័ព្ទ៖</b> " . htmlspecialchars($pData['phone']) . "\n";
            }
            $companyText = !empty($pData['company']) ? $pData['company'] : ($pData['delegation_org'] ?? '');
            if (!empty($companyText) && $companyText !== '-') {
                $msg .= "🏢 <b>ស្ថាប័ន៖</b> " . htmlspecialchars($companyText) . "\n";
            }
            if (!empty($pData['position']) && $pData['position'] !== '-') {
                $msg .= "💼 <b>តួនាទី៖</b> " . htmlspecialchars($pData['position']) . "\n";
            }
            $msg .= "🎫 <b>សំបុត្រ៖</b> " . htmlspecialchars($ticketText) . "\n";
            if (!empty($cleanSlot)) {
                $msg .= "🕒 <b>វេនស្កេន៖</b> " . htmlspecialchars($cleanSlot) . "\n";
            }
            $msg .= "⚡️ <b>វិធី Check-in៖</b> {$methodText}\n";
            $msg .= "⏰ <b>ម៉ោង Check-in៖</b> " . date('H:i:s, d/m/Y') . "\n";
            if (!empty($pData['registration_code']) || !empty($pData['reg_code'])) {
                $code = $pData['registration_code'] ?? $pData['reg_code'];
                $msg .= "🔢 <b>កូដ៖</b> " . htmlspecialchars($code) . "\n";
            }
            $msg .= "━━━━━━━━━━━━━━━\n";
            $msg .= "📊 <b>វត្តមានសរុប៖</b> <b>{$attendedCount} / {$confirmedCount} នាក់ ({$pct}%)</b>";

            $res = telegram_api_request('sendMessage', [
                'chat_id'    => $workshop['telegram_chat_id'],
                'text'       => $msg,
                'parse_mode' => 'HTML'
            ]);

            return !empty($res['ok']);
        } catch (Throwable $e) {
            error_log('telegram_notify_checkin error: ' . $e->getMessage());
            return false;
        }
    }
}
