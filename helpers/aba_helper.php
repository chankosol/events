<?php
// helpers/aba_helper.php
// Workshop OS - ABA PayWay Gateway Helper
// Implemented from fb_api/telegram_ai and fb_api/admin/facebook_plans.php standards

if (!function_exists('aba_get_platform_setting')) {
    function aba_get_platform_setting(string $key, string $default = ''): string {
        try {
            $db = Database::getInstance();
            $row = $db->queryOne("SELECT setting_value FROM platform_settings WHERE setting_key = ?", [$key]);
            return ($row && $row['setting_value'] !== null && $row['setting_value'] !== '') ? (string)$row['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('aba_hash')) {
    function aba_hash(string $data, string $apiKey): string {
        $key = base64_decode($apiKey, true);
        if ($key === false || $key === '') {
            $key = $apiKey;
        }
        return base64_encode(hash_hmac('sha512', $data, $key, true));
    }
}

if (!function_exists('aba_create_transaction')) {
    function aba_create_transaction(string $tranId, float $amount, string $currency = 'USD', ?string $customMerchantId = null, ?string $customApiKey = null, ?string $customApiUrl = null, ?string $customOption = null, ?string $returnUrl = null): array {
        $merchantId = $customMerchantId ?: trim(aba_get_platform_setting('aba_merchant_id', 'findinyou'));
        $apiKey     = $customApiKey     ?: trim(aba_get_platform_setting('aba_api_key', ''));
        $apiUrl     = $customApiUrl     ?: trim(aba_get_platform_setting('aba_api_url', 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase'));
        $option     = $customOption     ?: trim(aba_get_platform_setting('aba_payment_option', 'abapay_khqr'));
        
        $reqTime = (string)time();
        $amountStr = number_format($amount, 2, '.', '');
        $returnUrl = $returnUrl ?: (defined('APP_URL') ? APP_URL : '') . '/billing/aba-callback';

        $hash = ($apiKey !== '') ? aba_hash($reqTime . $merchantId . $tranId . $amountStr . $option . $returnUrl, $apiKey) : '';

        return [
            'ok' => ($merchantId !== '' && $apiKey !== ''),
            'merchant_id' => $merchantId,
            'tran_id' => $tranId,
            'amount' => $amountStr,
            'currency' => $currency,
            'payment_option' => $option,
            'req_time' => $reqTime,
            'hash' => $hash,
            'api_url' => $apiUrl,
            'return_url' => $returnUrl
        ];
    }
}

if (!function_exists('aba_check_payment')) {
    function aba_check_payment(string $tranId, ?string $customVerifyUrl = null): array {
        $tranId = trim($tranId);
        $verifyUrl = $customVerifyUrl ?: trim(aba_get_platform_setting('aba_verify_url', 'https://us-central1-ppds-api.cloudfunctions.net/app/api/payments/check-transaction/find-in-you'));
        
        if ($tranId === '') {
            return ['ok' => false, 'paid' => false, 'message' => 'Missing ABA transaction ID'];
        }
        if ($verifyUrl === '') {
            return ['ok' => false, 'paid' => false, 'message' => 'ABA verify URL is not configured'];
        }

        $payload = json_encode(['tran_id' => $tranId], JSON_UNESCAPED_UNICODE);
        $ch = curl_init($verifyUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string)$res, true);
        $status = strtoupper((string)($json['data']['payment_status'] ?? $json['payment_status'] ?? $json['status'] ?? ''));
        $paid = in_array($status, ['APPROVED', 'PAID', 'SUCCESS', 'COMPLETED'], true);
        $message = $err ?: ($json['message'] ?? ($paid ? 'ABA payment approved' : 'ABA payment is pending / not approved'));

        return [
            'ok' => ($code >= 200 && $code < 300 && is_array($json)),
            'paid' => $paid,
            'http_code' => $code,
            'message' => $message,
            'raw' => $json ?: $res,
            'tran_id' => $tranId
        ];
    }
}
