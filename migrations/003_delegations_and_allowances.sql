-- Migration 003: Delegations, Allowances, and Anti-Proxy Anti-Double-Payout System

-- 1. Workshop Delegations (Provincial / Institutional Quotas)
CREATE TABLE IF NOT EXISTS workshop_delegations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  province VARCHAR(100) NOT NULL,
  organization VARCHAR(255) NULL,
  head_name VARCHAR(255) NULL,
  head_phone VARCHAR(50) NULL,
  head_email VARCHAR(255) NULL,
  head_position VARCHAR(255) NULL,
  quota_seats INT UNSIGNED NOT NULL DEFAULT 5,
  delegation_token VARCHAR(64) UNIQUE NOT NULL,
  allowance_rate DECIMAL(10,2) NULL DEFAULT 0.00,
  notes TEXT NULL,
  status ENUM('active','confirmed','completed') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_business_id (business_id),
  INDEX idx_province (province),
  INDEX idx_token (delegation_token),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add delegation and banking/identity fields to registrations table if not exist
ALTER TABLE registrations 
  ADD COLUMN delegation_id INT UNSIGNED NULL AFTER group_id,
  ADD COLUMN bank_name VARCHAR(100) NULL AFTER notes,
  ADD COLUMN bank_account_number VARCHAR(100) NULL AFTER bank_name,
  ADD COLUMN bank_account_name VARCHAR(255) NULL AFTER bank_account_number,
  ADD COLUMN id_card_number VARCHAR(100) NULL AFTER bank_account_name,
  ADD COLUMN substituted_for_id INT UNSIGNED NULL AFTER id_card_number,
  ADD COLUMN badge_printed_at TIMESTAMP NULL AFTER substituted_for_id,
  ADD INDEX idx_delegation_id (delegation_id);

-- 3. Workshop Allowance Configuration (Per Diem / Travel Allowance Rules)
CREATE TABLE IF NOT EXISTS workshop_allowances (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  allowance_name VARCHAR(255) NOT NULL,
  default_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  currency VARCHAR(10) DEFAULT 'USD',
  min_attendance_percent INT UNSIGNED DEFAULT 80,
  require_feedback TINYINT(1) DEFAULT 0,
  allow_delegation_head_claim TINYINT(1) DEFAULT 1,
  notes TEXT NULL,
  status ENUM('active','closed') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_business_id (business_id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Allowance Disbursements (Strict Anti-Double Payout Protection)
CREATE TABLE IF NOT EXISTS allowance_disbursements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  allowance_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL UNIQUE, -- UNIQUE guarantees NO DOUBLE CLAIM for a registration!
  participant_id INT UNSIGNED NOT NULL,
  delegation_id INT UNSIGNED NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  payout_method ENUM('cash','bakong','bank_transfer') DEFAULT 'cash',
  disbursed_to_type ENUM('individual','delegation_head') DEFAULT 'individual',
  disbursed_to_name VARCHAR(255) NOT NULL,
  recipient_phone VARCHAR(50) NULL,
  recipient_id_card VARCHAR(100) NULL,
  receipt_voucher_no VARCHAR(50) UNIQUE NOT NULL,
  signature_data MEDIUMTEXT NULL,
  attendance_rate_at_payout DECIMAL(5,2) DEFAULT 100.00,
  disbursed_by INT UNSIGNED NOT NULL,
  disbursed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_business_id (business_id),
  INDEX idx_allowance_id (allowance_id),
  INDEX idx_delegation_id (delegation_id),
  INDEX idx_disbursed_by (disbursed_by),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  FOREIGN KEY (allowance_id) REFERENCES workshop_allowances(id) ON DELETE CASCADE,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
