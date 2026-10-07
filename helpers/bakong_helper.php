<?php
// helpers/bakong_helper.php
// Workshop OS - Bakong NBC Open API Gateway & Multi-Token Rate-Limit Switcher

if (!function_exists('bakong_tlv')) {
    function bakong_tlv(string $id, string $value): string {
        $len = str_pad((string)strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $len . $value;
    }
}

if (!function_exists('bakong_crc16')) {
    function bakong_crc16(string $payload): string {
        $crc = 0xFFFF;
        $len = strlen($payload);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($payload[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}

if (!function_exists('bakong_get_platform_setting')) {
    function bakong_get_platform_setting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $row = $db->queryOne("SELECT setting_value FROM platform_settings WHERE setting_key = ?", [$key]);
            return ($row && $row['setting_value'] !== null && $row['setting_value'] !== '') ? (string)$row['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

/**
 * Generate standard EMVCo Bakong KHQR String and MD5 hash
 */
if (!function_exists('bakong_create_khqr')) {
    function bakong_create_khqr(string $invoiceNo, float $amount, string $currency = 'USD'): array {
        $account  = trim(bakong_get_platform_setting('bakong_account_id', 'kosol@abaa'));
        $merchant = trim(bakong_get_platform_setting('bakong_merchant_name', 'Chan Kosol'));
        $city     = trim(bakong_get_platform_setting('bakong_merchant_city', 'PHNOM PENH'));
        $mcc      = trim(bakong_get_platform_setting('bakong_mcc', '5999'));
        $staticQr = trim(bakong_get_platform_setting('bakong_static_qr_text', ''));

        if ($staticQr !== '') {
            return [
                'ok' => true,
                'qr_text' => $staticQr,
                'md5' => md5($staticQr),
                'account' => $account,
                'merchant' => $merchant,
                'raw' => ['mode' => 'static_override']
            ];
        }

        if ($account === '') {
            $fallback = "KHQR|MERCHANT={$merchant}|INV={$invoiceNo}|AMOUNT={$amount}|CCY={$currency}";
            return [
                'ok' => false,
                'qr_text' => $fallback,
                'md5' => md5($fallback),
                'account' => 'unconfigured',
                'merchant' => $merchant,
                'message' => 'Bakong account ID is not configured in Super Admin',
                'raw' => ['mode' => 'fallback']
            ];
        }

        $currency = strtoupper($currency) === 'KHR' ? 'KHR' : 'USD';
        $currencyCode = $currency === 'KHR' ? '116' : '840';
        $amountText = rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
        $merchant = mb_substr($merchant, 0, 25, 'UTF-8');
        $city = mb_substr($city, 0, 15, 'UTF-8');

        $merchantAccount = bakong_tlv('00', $account);
        $additional = bakong_tlv('01', substr(preg_replace('/[^A-Za-z0-9._-]/', '', $invoiceNo), 0, 25));
        $nowMs = (string)(time() * 1000);
        $expiresMs = (string)((time() + 900) * 1000); // 15 mins expiry
        $timestamps = bakong_tlv('00', $nowMs) . bakong_tlv('01', $expiresMs);

        $payload = '';
        $payload .= bakong_tlv('00', '01');
        $payload .= bakong_tlv('01', '12'); // Dynamic QR
        $payload .= bakong_tlv('29', $merchantAccount);
        $payload .= bakong_tlv('52', str_pad(substr($mcc, 0, 4), 4, '0', STR_PAD_LEFT));
        $payload .= bakong_tlv('53', $currencyCode);
        $payload .= bakong_tlv('54', $amountText);
        $payload .= bakong_tlv('58', 'KH');
        $payload .= bakong_tlv('59', $merchant);
        $payload .= bakong_tlv('60', $city);
        $payload .= bakong_tlv('62', $additional);
        $payload .= bakong_tlv('99', $timestamps);

        $payloadForCrc = $payload . '6304';
        $qrText = $payloadForCrc . bakong_crc16($payloadForCrc);

        return [
            'ok' => true,
            'qr_text' => $qrText,
            'md5' => md5($qrText),
            'account' => $account,
            'merchant' => $merchant,
            'amount' => $amountText,
            'currency' => $currency,
            'raw' => ['mode' => 'emv_khqr']
        ];
    }
}

/**
 * Check payment on Bakong NBC Open API with Multi-Token Rate-Limit Switcher
 * Learnt from telegram_ai_settings.php to prevent 100 daily limit errors.
 */
if (!function_exists('bakong_check_payment_by_md5')) {
    function bakong_check_payment_by_md5(string $md5): array {
        $md5 = trim($md5);
        if ($md5 === '') {
            return ['ok' => false, 'paid' => false, 'message' => 'Missing Bakong MD5 hash'];
        }

        $tokenConfig = trim(bakong_get_platform_setting('bakong_api_token', ''));
        $baseUrl = rtrim(trim(bakong_get_platform_setting('bakong_api_base_url', 'https://api-bakong.nbc.gov.kh')), '/');

        if ($tokenConfig === '') {
            return ['ok' => false, 'paid' => false, 'message' => 'Bakong API token is not configured in Super Admin'];
        }

        // Split tokens by comma to support multi-token rotation & rate-limit switching
        $tokens = array_filter(array_map('trim', preg_split('/[\s,]+/', $tokenConfig)));
        if (empty($tokens)) {
            return ['ok' => false, 'paid' => false, 'message' => 'No valid Bakong API tokens available'];
        }

        $url = $baseUrl . '/v1/check_transaction_by_md5';
        $payload = json_encode(['md5' => $md5], JSON_UNESCAPED_UNICODE);

        $lastResult = ['ok' => false, 'paid' => false, 'message' => 'No tokens processed'];
        $tokenIndex = 0;

        foreach ($tokens as $currentToken) {
            $tokenIndex++;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $currentToken,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Cache-Control: no-cache',
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'WorkshopOS/1.0 (+https://findinyou.com)',
            ]);

            $res = curl_exec($ch);
            $err = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $json = json_decode((string)$res, true);
            $paid = false;
            if (is_array($json)) {
                $responseCode = (string)($json['responseCode'] ?? $json['response_code'] ?? $json['code'] ?? '');
                $status = strtoupper((string)($json['status'] ?? $json['data']['status'] ?? ''));
                $paid = in_array($responseCode, ['0', '00', 'SUCCESS'], true) || in_array($status, ['SUCCESS', 'PAID', 'COMPLETED'], true);
            }

            $message = $err ?: ($json['responseMessage'] ?? $json['message'] ?? '');
            if ($message === '' && $code === 403) $message = 'Bakong API blocked request with HTTP 403.';
            if ($message === '' && $code === 401) $message = 'Bakong API token is unauthorized or expired.';
            if ($message === '' && $code === 429) $message = 'Bakong API rate limit reached (HTTP 429).';

            $lastResult = [
                'ok' => ($code >= 200 && $code < 300 && is_array($json)),
                'paid' => $paid,
                'http_code' => $code,
                'token_used_index' => $tokenIndex,
                'message' => $message,
                'data' => $json['data'] ?? null,
                'raw' => $json ?: $res
            ];

            // Check if rate limit exceeded (errorCode 17 or "limit exceeded" or HTTP 429)
            $limitExceeded = ($code === 429)
                || (stripos($message, 'limit') !== false && stripos($message, 'exceeded') !== false)
                || ((int)($json['errorCode'] ?? 0) === 17);

            if ($limitExceeded) {
                // Log and switch to next available token!
                error_log("Bakong Token #{$tokenIndex} hit limit. Switching to next token in pool...");
                continue;
            }

            // If token worked (or normal response like transaction not found yet), return result
            return $lastResult;
        }

        return $lastResult;
    }
}
