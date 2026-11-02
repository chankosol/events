<?php
// controllers/public/TelegramWebhookController.php
// Workshop OS - Telegram Bot Webhook Receiver

class TelegramWebhookController {
    public static function handle(): void {
        // Return 200 immediately to acknowledge receipt
        header('Content-Type: application/json');

        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            echo json_encode(['ok' => true, 'message' => 'Telegram Webhook Endpoint is active.']);
            exit;
        }

        $update = json_decode($rawInput, true);
        if (!is_array($update)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid JSON']);
            exit;
        }

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) {
            echo json_encode(['ok' => true]);
            exit;
        }

        $chatId = $message['chat']['id'] ?? null;
        $chatType = $message['chat']['type'] ?? 'private';
        $text = trim($message['text'] ?? '');
        $firstName = trim($message['from']['first_name'] ?? 'សិក្ខាកាម');

        if (!$chatId) {
            echo json_encode(['ok' => true]);
            exit;
        }

        // Handle Group / Supergroup Chats
        if ($chatType === 'group' || $chatType === 'supergroup') {
            $groupTitle = $message['chat']['title'] ?? 'ក្រុមការងារ';

            // Record group activity for 1-click backend scanning
            self::recordGroupActivity((string)$chatId, $groupTitle, $chatType, $text);

            // Check if Bot was added to group or group was created
            $isBotAdded = false;
            if (!empty($message['new_chat_members'])) {
                foreach ($message['new_chat_members'] as $m) {
                    if (!empty($m['is_bot'])) {
                        $isBotAdded = true;
                        break;
                    }
                }
            } elseif (!empty($message['group_chat_created']) || !empty($message['supergroup_chat_created'])) {
                $isBotAdded = true;
            }

            // Command /id, /groupid, /start in group, or bot added
            $lowerText = strtolower($text);
            $cleanCmd = explode('@', $lowerText)[0]; // handle /id@botname

            if ($isBotAdded || in_array($cleanCmd, ['/id', '/groupid', '/group_id', '/chatid', '/status', '/start'])) {
                $msg = "🤖 <b>Bot ត្រូវបានភ្ជាប់ចូលក្នុងក្រុមជោគជ័យ!</b>\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━\n";
                $msg .= "📋 <b>ឈ្មោះក្រុម៖</b> " . htmlspecialchars($groupTitle) . "\n";
                $msg .= "🆔 <b>Group Chat ID៖</b> <code>" . $chatId . "</code>\n\n";
                $msg .= "💡 <b>ការប្រើប្រាស់ងាយស្រួល៖</b>\n";
                $msg .= "លោកអ្នកអាចចូលទៅកាន់ផ្ទាំងគ្រប់គ្រង (Admin Dashboard) រួចចុចប៊ូតុង <b>«🔍 ស្កេនរក Group»</b> ដើម្បីភ្ជាប់ក្រុមនេះដោយស្វ័យប្រវត្តិតែម្តង ដោយមិនបាច់ Copy លេខ ID ឡើយ!\n";
                $msg .= "━━━━━━━━━━━━━━━━━━━";

                telegram_api_request('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $msg,
                    'parse_mode' => 'HTML'
                ]);
            } elseif (in_array($lowerText, ['hi', 'hello', 'hey', 'សួស្តី', 'test', 'ok'])) {
                $msg = "👋 <b>សួស្តី! Bot បានកត់ត្រាក្រុម «" . htmlspecialchars($groupTitle) . "» រួចរាល់ហើយ។</b>\n\n";
                $msg .= "លោកអ្នកអាចចូលទៅកាន់ផ្ទាំងគ្រប់គ្រង (Admin Backend) រួចចុចប៊ូតុង <b>«🔍 ស្កេនរក Group»</b> ដើម្បីភ្ជាប់បានភ្លាមៗ!";

                telegram_api_request('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $msg,
                    'parse_mode' => 'HTML'
                ]);
            }

            // In groups, ignore other general chatter so Bot doesn't spam ticket errors
            echo json_encode(['ok' => true]);
            exit;
        }

        // Determine if command is /start with payload (e.g. /start REG5324A41102)
        $lookupCode = '';
        if (strpos($text, '/start') === 0) {
            $parts = explode(' ', $text, 2);
            $lookupCode = trim($parts[1] ?? '');
        } else {
            // User sent code directly (e.g. REG-12345 or phone number)
            $lookupCode = $text;
        }

        if (!empty($lookupCode)) {
            self::sendTicketByCodeOrPhone($chatId, $lookupCode, $firstName);
        } else {
            self::sendWelcomeMessage($chatId, $firstName);
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    private static function sendTicketByCodeOrPhone($chatId, string $query, string $firstName): void {
        $db = Database::getInstance();
        $cleanPhone = preg_replace('/[^0-9]/', '', $query);
        $tail8 = strlen($cleanPhone) >= 8 ? substr($cleanPhone, -8) : 'NON_EXISTENT';

        $sql = "SELECT r.*, p.name as participant_name, p.phone as participant_phone, p.email as participant_email,
                       w.name as workshop_name, w.slug as workshop_slug, w.start_date, w.end_date, w.start_time, w.end_time, w.venue, w.address,
                       pq.token as qr_token, t.name as ticket_name
                FROM registrations r
                JOIN participants p ON p.id = r.participant_id
                JOIN workshops w ON w.id = r.workshop_id
                LEFT JOIN participant_qr pq ON pq.registration_id = r.id
                LEFT JOIN tickets t ON t.id = r.ticket_id
                WHERE (r.registration_code = ? 
                       OR pq.token = ? 
                       OR p.phone = ? 
                       OR REPLACE(p.phone, ' ', '') = ? 
                       OR REPLACE(p.phone, '-', '') = ? 
                       OR RIGHT(REPLACE(p.phone, ' ', ''), 8) = ?)
                  AND r.status NOT IN ('cancelled', 'rejected')
                  AND r.deleted_at IS NULL
                ORDER BY r.id DESC LIMIT 1";

        $reg = $db->queryOne($sql, [$query, $query, $query, $cleanPhone, $cleanPhone, $tail8]);

        if (!$reg) {
            $reply = "❌ <b>រកមិនឃើញទិន្នន័យសំបុត្រទេ!</b>\n\n";
            $reply .= "មិនស្វែងរកឃើញការចុះឈ្មោះជាមួយកូដ ឬលេខទូរស័ព្ទ «<code>" . htmlspecialchars($query) . "</code>» ឡើយ។\n\n";
            $reply .= "💡 សូមពិនិត្យលេខកូដចុះឈ្មោះ (ឧទាហរណ៍៖ <code>REG5324A41102</code>) ឬលេខទូរស័ព្ទដែលបានចុះឈ្មោះឡើងវិញ។";

            telegram_api_request('sendMessage', [
                'chat_id' => $chatId,
                'text' => $reply,
                'parse_mode' => 'HTML'
            ]);
            return;
        }

        // Generate QR token if missing
        $qrToken = $reg['qr_token'];
        if (empty($qrToken)) {
            $qrToken = bin2hex(random_bytes(32));
            $db->execute(
                "INSERT INTO participant_qr (registration_id, participant_id, business_id, workshop_id, token, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)",
                [$reg['id'], $reg['participant_id'], $reg['business_id'], $reg['workshop_id'], $qrToken]
            );
        }

        $ticketUrl = APP_URL . '/ticket/' . urlencode($reg['registration_code']);
        $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=450x450&data=" . urlencode($qrToken);

        $caption = "🎟 <b>សំបុត្រចូលរួមសិក្ខាសាលាផ្លូវការ</b>\n";
        $caption .= "━━━━━━━━━━━━━━━━━━━\n";
        $caption .= "📌 <b>កម្មវិធី៖</b> " . htmlspecialchars($reg['workshop_name']) . "\n";
        $caption .= "👤 <b>ឈ្មោះសិក្ខាកាម៖</b> " . htmlspecialchars($reg['participant_name']) . "\n";
        $caption .= "🎫 <b>ប្រភេទសំបុត្រ៖</b> " . htmlspecialchars($reg['ticket_name'] ?: 'ទូទៅ / Standard') . "\n";
        $caption .= "🔢 <b>កូដសំបុត្រ៖</b> <code>" . htmlspecialchars($reg['registration_code']) . "</code>\n";
        $caption .= "🗓 <b>កាលបរិច្ឆេទ៖</b> " . formatDate($reg['start_date']);
        if (!empty($reg['start_time'])) {
            $caption .= " (ម៉ោង " . substr($reg['start_time'], 0, 5) . ")";
        }
        $caption .= "\n";
        if (!empty($reg['venue'])) {
            $caption .= "📍 <b>ទីតាំង៖</b> " . htmlspecialchars($reg['venue']) . "\n";
        }
        $caption .= "━━━━━━━━━━━━━━━━━━━\n";
        $caption .= "✅ <b>ស្ថានភាព៖</b> បានបញ្ជាក់រួចរាល់\n\n";
        $caption .= "👉 <i>សូមបង្ហាញរូបភាព QR Code ខាងលើនេះដល់បុគ្គលិកនៅច្រកចូលដើម្បីស្កេនចូលរួមកម្មវិធី។</i>";

        $buttons = [
            [
                ['text' => '🎫 បើកមើលសំបុត្រពេញលេញ', 'url' => $ticketUrl]
            ]
        ];

        if (!empty($reg['workshop_slug'])) {
            $buttons[0][] = [
                'text' => '🌐 ព័ត៌មានសិក្ខាសាលា',
                'url' => APP_URL . '/event/' . urlencode($reg['workshop_slug'])
            ];
        }

        $res = telegram_api_request('sendPhoto', [
            'chat_id' => $chatId,
            'photo' => $qrImageUrl,
            'caption' => $caption,
            'parse_mode' => 'HTML',
            'reply_markup' => ['inline_keyboard' => $buttons]
        ]);

        // Fallback to text message if photo sending fails
        if (empty($res['ok'])) {
            telegram_api_request('sendMessage', [
                'chat_id' => $chatId,
                'text' => $caption . "\n\n🔗 តំណភ្ជាប់សំបុត្រ៖ " . $ticketUrl,
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => $buttons]
            ]);
        }
    }

    private static function sendWelcomeMessage($chatId, string $firstName): void {
        $msg = "👋 <b>សួស្តី " . htmlspecialchars($firstName) . "!</b>\n\n";
        $msg .= "សូមស្វាគមន៍មកកាន់ប្រព័ន្ធបម្រើសេវាកម្មសំបុត្រ និងកាត QR Code ផ្លូវការ។\n\n";
        $msg .= "❓ <b>ដើម្បីទទួលសំបុត្រ និង QR Code ចូលរួមរបស់អ្នក៖</b>\n";
        $msg .= "• សូមចុចលើប៊ូតុង <b>Telegram</b> ពីទំព័រសំបុត្ររបស់អ្នក\n";
        $msg .= "• ឬវាយផ្ញើ <b>«កូដចុះឈ្មោះ»</b> (ឧ. <code>REG5324A41102</code>) ឬ <b>«លេខទូរស័ព្ទ»</b> របស់អ្នកមកទីនេះភ្លាមៗ។";

        telegram_api_request('sendMessage', [
            'chat_id' => $chatId,
            'text' => $msg,
            'parse_mode' => 'HTML'
        ]);
    }

    private static function recordGroupActivity(string $chatId, string $title, string $type, string $lastMsg): void {
        try {
            $db = Database::getInstance();
            // 1. Save to telegram_groups table
            try {
                $db->execute(
                    "INSERT INTO telegram_groups (chat_id, title, chat_type, last_message, last_active_at)
                     VALUES (?, ?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE title = VALUES(title), chat_type = VALUES(chat_type), last_message = VALUES(last_message), last_active_at = NOW()",
                    [$chatId, $title, $type, mb_substr($lastMsg ?: 'Active', 0, 255)]
                );
            } catch (Throwable $e) {}

            // 2. Also save to platform_settings as JSON fallback
            try {
                $row = $db->queryOne("SELECT setting_value FROM platform_settings WHERE setting_key = 'telegram_recent_groups'");
                $recent = !empty($row['setting_value']) ? json_decode($row['setting_value'], true) : [];
                if (!is_array($recent)) $recent = [];

                $recent = array_values(array_filter($recent, fn($g) => ($g['chat_id'] ?? '') !== $chatId));
                array_unshift($recent, [
                    'chat_id' => $chatId,
                    'title'   => $title,
                    'type'    => $type,
                    'time'    => date('Y-m-d H:i:s')
                ]);
                $recent = array_slice($recent, 0, 10);

                $db->execute(
                    "INSERT INTO platform_settings (setting_key, setting_value, setting_type)
                     VALUES ('telegram_recent_groups', ?, 'json')
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                    [json_encode($recent)]
                );
            } catch (Throwable $e2) {}
        } catch (Throwable $e) {
            error_log('recordGroupActivity error: ' . $e->getMessage());
        }
    }
}
