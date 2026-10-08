-- Migration 004: Temporary Check-in Station & Helper Passes
-- Allows generating time-limited and device-limited passes for temporary scanning helpers

CREATE TABLE IF NOT EXISTS checkin_helper_passes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  workshop_id INT NOT NULL,
  token VARCHAR(64) UNIQUE NOT NULL,
  label VARCHAR(100) DEFAULT 'តុស្កេនជំនួយការ',
  max_devices INT DEFAULT 1,
  device_count INT DEFAULT 0,
  expires_at DATETIME NOT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_by INT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_token (token),
  INDEX idx_workshop (workshop_id),
  INDEX idx_business (business_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS checkin_helper_devices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pass_id INT NOT NULL,
  device_token VARCHAR(64) NOT NULL,
  helper_name VARCHAR(100) NULL,
  is_active TINYINT(1) DEFAULT 1,
  device_name VARCHAR(150) NULL,
  ip_address VARCHAR(45) NULL,
  last_active_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_pass_device (pass_id, device_token),
  INDEX idx_pass_id (pass_id),
  FOREIGN KEY (pass_id) REFERENCES checkin_helper_passes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Allow 'helper_station' method in attendance
ALTER TABLE attendance MODIFY COLUMN check_in_method VARCHAR(50) DEFAULT 'qr';

