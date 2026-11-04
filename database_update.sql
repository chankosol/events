-- ============================================================
-- Workshop OS: AI Face Recognition Check-in System Migration
-- Run this SQL in your live server's phpMyAdmin / MySQL Database
-- ============================================================

CREATE TABLE IF NOT EXISTS participant_faces (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  face_descriptor MEDIUMTEXT NOT NULL,
  photo_path VARCHAR(500) NULL,
  is_active TINYINT(1) DEFAULT 1,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_reg_face (registration_id),
  INDEX idx_workshop_active (workshop_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Allow 'face' and 'helper_station' in attendance table
ALTER TABLE attendance MODIFY COLUMN check_in_method ENUM('qr','manual','offline','face','helper_station') DEFAULT 'qr';

-- Allow 'face' in qr_scan_logs table
ALTER TABLE qr_scan_logs MODIFY COLUMN scan_type ENUM('checkin','checkout','attendance','gift','certificate','identity','face') NOT NULL DEFAULT 'checkin';

-- Add Check-in Scan Mode to workshops table (controlled by Host Organizer)
ALTER TABLE workshops ADD COLUMN checkin_scan_mode ENUM('qr', 'face') DEFAULT 'qr';

-- Add Telegram Reporting Group Chat ID and Notification Setting to workshops table
ALTER TABLE workshops ADD COLUMN telegram_chat_id VARCHAR(100) NULL, ADD COLUMN telegram_notify_registration TINYINT(1) DEFAULT 1;

-- Track active Telegram Groups for instant one-click backend scanning
CREATE TABLE IF NOT EXISTS telegram_groups (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chat_id VARCHAR(100) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  chat_type VARCHAR(50) DEFAULT 'group',
  last_message TEXT NULL,
  last_active_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_last_active (last_active_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Daily Check-in Slots Support (1, 2, 3, or 4 scans per day)
-- ============================================================
ALTER TABLE workshops ADD COLUMN daily_scans_required TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER capacity;

CREATE TABLE IF NOT EXISTS attendance_slots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  checkin_date DATE NOT NULL,
  slot_number TINYINT UNSIGNED NOT NULL DEFAULT 1,
  slot_name VARCHAR(50) NULL,
  checked_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  check_in_method ENUM('qr','manual','offline','face','helper_station') DEFAULT 'qr',
  check_in_staff_id INT UNSIGNED NULL,
  notes VARCHAR(255) NULL,
  UNIQUE KEY unique_reg_date_slot (registration_id, checkin_date, slot_number),
  INDEX idx_workshop_date_slot (workshop_id, checkin_date, slot_number),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Ticket Visibility & Payment Method Enhancements
-- ============================================================
ALTER TABLE tickets ADD COLUMN is_visible TINYINT(1) DEFAULT 1;
ALTER TABLE business_payment_methods ADD COLUMN is_default TINYINT(1) DEFAULT 0;

-- Ensure business_settings table exists for Bakong KHQR & ABA PayWay configurations
CREATE TABLE IF NOT EXISTS business_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_business_key (business_id, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


