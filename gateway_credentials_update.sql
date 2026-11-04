-- ========================================================================
-- WORKSHOP OS: Gateway Settings Migration (Bakong QR & ABA PayWay)
-- Run this in phpMyAdmin on your Live Server (findppsz_event)
-- ========================================================================

-- ១. បង្កើតតារាង business_settings ប្រសិនបើមិនទាន់មាន
CREATE TABLE IF NOT EXISTS business_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_business_key (business_id, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ២. បញ្ចូល ឬធ្វើបច្ចុប្បន្នភាពព័ត៌មាន Gateway (Bakong QR & ABA Payway) សម្រាប់ Business #1
INSERT INTO business_settings (business_id, setting_key, setting_value) VALUES
(1, 'payment_provider', 'both'),
(1, 'bakong_account_id', 'kosol@abaa'),
(1, 'merchant_name', 'Chan Kosol'),
(1, 'bakong_api_token', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiZGUyMzQxZTliYzEzNDNmMyJ9LCJpYXQiOjE3ODIzNzM0MzQsImV4cCI6MTc5MDE0OTQzNH0.60xkQ5lW92Ftm3RsDY3luMXlcQTXygBKva-3jvAFUEo'),
(1, 'bakong_api_base_url', 'https://api-bakong.nbc.gov.kh'),
(1, 'bakong_static_qr_text', ''),
(1, 'aba_merchant_id', 'findinyou'),
(1, 'aba_merchant_name', 'Chan Kosol'),
(1, 'aba_api_key', '9dc491c5-aabd-4c5f-bff6-b218fb07cef3'),
(1, 'aba_api_url', 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

-- ៣. បញ្ចូល ឬធ្វើបច្ចុប្បន្នភាពចូល platform_settings ផងដែរ (សម្រាប់ Platform Level)
INSERT INTO platform_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('bakong_account_id', 'kosol@abaa', 'string', 'Platform Bakong Account ID', 0),
('bakong_merchant_name', 'Chan Kosol', 'string', 'Platform Official Merchant Name for KHQR', 0),
('bakong_merchant_city', 'PHNOM PENH', 'string', 'Platform Merchant City for KHQR', 0),
('bakong_api_base_url', 'https://api-bakong.nbc.gov.kh', 'string', 'Bakong NBC API Base URL', 0),
('bakong_api_token', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7ImlkIjoiZGUyMzQxZTliYzEzNDNmMyJ9LCJpYXQiOjE3ODIzNzM0MzQsImV4cCI6MTc5MDE0OTQzNH0.60xkQ5lW92Ftm3RsDY3luMXlcQTXygBKva-3jvAFUEo', 'text', 'Bakong API Token', 0),
('aba_merchant_id', 'findinyou', 'string', 'ABA PayWay Merchant ID', 0),
('aba_api_key', '9dc491c5-aabd-4c5f-bff6-b218fb07cef3', 'string', 'ABA PayWay API Key', 0),
('aba_api_url', 'https://checkout.payway.com.kh/api/payment-gateway/v1/payments/purchase', 'string', 'ABA PayWay Endpoint', 0)
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();
