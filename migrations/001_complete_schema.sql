-- C:\xampp\htdocs\workshopos\migrations\001_complete_schema.sql

-- Platform configuration
CREATE TABLE platform_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value TEXT,
  setting_type ENUM('string','integer','boolean','json','text') DEFAULT 'string',
  description TEXT,
  is_public TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Platform pricing rules for workshop activation fees
CREATE TABLE platform_pricing_rules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  min_capacity INT UNSIGNED NOT NULL,
  max_capacity INT UNSIGNED,  -- NULL = unlimited
  price DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  effective_from DATE,
  effective_to DATE,
  is_custom TINYINT(1) DEFAULT 0,
  description TEXT,
  created_by INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Businesses (tenants)
CREATE TABLE businesses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  business_type VARCHAR(100),
  contact_person VARCHAR(255),
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(50),
  country VARCHAR(100),
  city VARCHAR(100),
  address TEXT,
  website VARCHAR(255),
  preferred_language VARCHAR(10) DEFAULT 'en',
  preferred_currency VARCHAR(10) DEFAULT 'USD',
  timezone VARCHAR(100) DEFAULT 'Asia/Phnom_Penh',
  status ENUM('active','inactive','suspended','pending') DEFAULT 'pending',
  onboarding_completed TINYINT(1) DEFAULT 0,
  onboarding_step INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Business settings (key-value per business)
CREATE TABLE business_settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  setting_key VARCHAR(100) NOT NULL,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_business_setting (business_id, setting_key),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Business branding
CREATE TABLE business_branding (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL UNIQUE,
  logo_path VARCHAR(500),
  cover_path VARCHAR(500),
  primary_color VARCHAR(20) DEFAULT '#0d6efd',
  secondary_color VARCHAR(20) DEFAULT '#6c757d',
  accent_color VARCHAR(20) DEFAULT '#198754',
  font_family VARCHAR(100),
  custom_css TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Business payment methods
CREATE TABLE business_payment_methods (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  bank VARCHAR(100),
  account_name VARCHAR(255),
  account_number VARCHAR(100),
  qr_image_path VARCHAR(500),
  instructions TEXT,
  currency VARCHAR(10) DEFAULT 'USD',
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Roles
CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  scope ENUM('platform','business','participant') NOT NULL,
  description TEXT,
  is_system TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permissions
CREATE TABLE permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(100) UNIQUE NOT NULL,
  group_name VARCHAR(100),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Role permissions mapping
CREATE TABLE role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users (platform and business staff)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(50),
  password VARCHAR(255) NOT NULL,
  status ENUM('active','inactive','suspended') DEFAULT 'active',
  email_verified_at TIMESTAMP NULL,
  last_login_at TIMESTAMP NULL,
  last_login_ip VARCHAR(45),
  login_attempts INT DEFAULT 0,
  locked_until TIMESTAMP NULL,
  remember_token VARCHAR(255) NULL,
  password_reset_token VARCHAR(255) NULL,
  password_reset_expires TIMESTAMP NULL,
  preferred_language VARCHAR(10) DEFAULT 'en',
  profile_photo VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  INDEX idx_business_id (business_id),
  INDEX idx_status (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User role assignments
CREATE TABLE user_roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NULL,
  assigned_by INT UNSIGNED,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_user_role_business (user_id, role_id, business_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Login attempts log
CREATE TABLE login_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255),
  ip_address VARCHAR(45),
  user_agent TEXT,
  success TINYINT(1) DEFAULT 0,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_ip (ip_address),
  INDEX idx_attempted (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workshops
CREATE TABLE workshops (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  slug VARCHAR(255) NOT NULL,
  name VARCHAR(500) NOT NULL,
  description TEXT,
  short_description VARCHAR(500),
  category VARCHAR(100),
  workshop_type ENUM('in-person','online','hybrid') DEFAULT 'in-person',
  trainer_title VARCHAR(100) DEFAULT 'គ្រូបណ្តុះបណ្តាល',
  trainer_name VARCHAR(255),
  trainer_bio TEXT,
  trainer_photo VARCHAR(500),
  organizer VARCHAR(255),
  start_date DATE,
  end_date DATE,
  start_time TIME,
  end_time TIME,
  timezone VARCHAR(100) DEFAULT 'Asia/Phnom_Penh',
  venue VARCHAR(255),
  address TEXT,
  google_maps_url VARCHAR(500),
  online_meeting_url VARCHAR(500),
  capacity INT UNSIGNED DEFAULT 100,
  language VARCHAR(50) DEFAULT 'English',
  visibility ENUM('public','private','unlisted') DEFAULT 'public',
  registration_open_date DATETIME,
  registration_close_date DATETIME,
  requires_approval TINYINT(1) DEFAULT 0,
  auto_confirm TINYINT(1) DEFAULT 0,
  allow_waitlist TINYINT(1) DEFAULT 1,
  payment_mode ENUM('free','paid','freemium') DEFAULT 'free',
  status ENUM('draft','pending_payment','active','registration_open','registration_closed','in_progress','completed','cancelled','archived') DEFAULT 'draft',
  performance_score DECIMAL(5,2),
  duplicated_from INT UNSIGNED,
  created_by INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY unique_slug_per_business (business_id, slug),
  INDEX idx_business_id (business_id),
  INDEX idx_status (status),
  INDEX idx_start_date (start_date),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workshop branding
CREATE TABLE workshop_branding (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL UNIQUE,
  business_id INT UNSIGNED NOT NULL,
  cover_image VARCHAR(500),
  logo VARCHAR(500),
  banner VARCHAR(500),
  theme_color VARCHAR(20) DEFAULT '#0d6efd',
  custom_registration_image VARCHAR(500),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workshop sessions
CREATE TABLE workshop_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  session_date DATE,
  start_time TIME,
  end_time TIME,
  location VARCHAR(255),
  facilitator VARCHAR(255),
  sort_order INT DEFAULT 0,
  status ENUM('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  attendance_tracking TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registration fields
CREATE TABLE registration_fields (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  field_name VARCHAR(100) NOT NULL,
  field_label VARCHAR(255) NOT NULL,
  field_type ENUM('text','textarea','number','email','phone','date','dropdown','radio','checkbox','multi_select','file') NOT NULL,
  field_options JSON,
  is_required TINYINT(1) DEFAULT 0,
  is_internal TINYINT(1) DEFAULT 0,
  placeholder VARCHAR(255),
  help_text TEXT,
  sort_order INT DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tickets
CREATE TABLE tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  ticket_type ENUM('standard','vip','early_bird','student','group','complimentary','custom') DEFAULT 'standard',
  price DECIMAL(10,2) DEFAULT 0.00,
  currency VARCHAR(10) DEFAULT 'USD',
  capacity INT UNSIGNED,
  sold_count INT UNSIGNED DEFAULT 0,
  sale_start DATETIME,
  sale_end DATETIME,
  min_per_order INT DEFAULT 1,
  max_per_order INT DEFAULT 1,
  status ENUM('active','inactive','sold_out') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  is_visible TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Promo codes
CREATE TABLE promo_codes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  code VARCHAR(50) NOT NULL,
  discount_type ENUM('fixed','percentage') NOT NULL,
  discount_value DECIMAL(10,2) NOT NULL,
  usage_limit INT,
  per_user_limit INT DEFAULT 1,
  used_count INT DEFAULT 0,
  start_date DATETIME,
  end_date DATETIME,
  ticket_restrictions JSON,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_code_workshop (workshop_id, code),
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PLATFORM BILLING
CREATE TABLE workshop_billing (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL UNIQUE,
  pricing_rule_id INT UNSIGNED,
  capacity INT UNSIGNED NOT NULL,
  platform_fee DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  payment_status ENUM('unpaid','pending','paid','refunded','waived') DEFAULT 'unpaid',
  payment_method VARCHAR(100),
  invoice_number VARCHAR(50) UNIQUE,
  transaction_reference VARCHAR(255),
  notes TEXT,
  paid_at TIMESTAMP NULL,
  due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_business_id (business_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_payment_status (payment_status),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (pricing_rule_id) REFERENCES platform_pricing_rules(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE platform_invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_number VARCHAR(50) UNIQUE NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_billing_id INT UNSIGNED,
  invoice_type ENUM('workshop_activation','capacity_upgrade','other') DEFAULT 'workshop_activation',
  description TEXT,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  status ENUM('unpaid','paid','cancelled','refunded') DEFAULT 'unpaid',
  issue_date DATE NOT NULL,
  due_date DATE,
  paid_date DATE,
  pdf_path VARCHAR(500),
  created_by INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_business_id (business_id),
  INDEX idx_status (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_billing_id) REFERENCES workshop_billing(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE platform_payment_proofs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_billing_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  invoice_id INT UNSIGNED,
  proof_file VARCHAR(500) NOT NULL,
  amount_claimed DECIMAL(10,2),
  currency VARCHAR(10) DEFAULT 'USD',
  payment_method VARCHAR(100),
  transaction_reference VARCHAR(255),
  payment_date DATE,
  notes TEXT,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  reviewed_by INT UNSIGNED,
  reviewed_at TIMESTAMP NULL,
  review_notes TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_billing_id (workshop_billing_id),
  INDEX idx_status (status),
  FOREIGN KEY (workshop_billing_id) REFERENCES workshop_billing(id),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE capacity_upgrades (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_billing_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  old_capacity INT UNSIGNED NOT NULL,
  new_capacity INT UNSIGNED NOT NULL,
  old_fee DECIMAL(10,2) NOT NULL,
  new_fee DECIMAL(10,2) NOT NULL,
  upgrade_fee DECIMAL(10,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'USD',
  old_pricing_rule_id INT UNSIGNED,
  new_pricing_rule_id INT UNSIGNED,
  payment_status ENUM('unpaid','pending','paid') DEFAULT 'unpaid',
  transaction_reference VARCHAR(255),
  paid_at TIMESTAMP NULL,
  created_by INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workshop_billing_id) REFERENCES workshop_billing(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Participants
CREATE TABLE participants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255),
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(50),
  gender ENUM('male','female','other','prefer_not_to_say'),
  date_of_birth DATE,
  company VARCHAR(255),
  position VARCHAR(255),
  province VARCHAR(100),
  city VARCHAR(100),
  country VARCHAR(100),
  address TEXT,
  photo VARCHAR(500),
  preferred_language VARCHAR(10) DEFAULT 'en',
  emergency_contact_name VARCHAR(255),
  emergency_contact_phone VARCHAR(50),
  portal_token VARCHAR(255) UNIQUE,
  email_verified_at TIMESTAMP NULL,
  last_login_at TIMESTAMP NULL,
  status ENUM('active','inactive','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  INDEX idx_email (email),
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Registrations
CREATE TABLE registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_code VARCHAR(20) UNIQUE NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  ticket_id INT UNSIGNED,
  group_id INT UNSIGNED NULL,
  status ENUM('draft','pending_payment','pending_approval','pending_verification','confirmed','waitlisted','cancelled','rejected','attended','completed') DEFAULT 'draft',
  payment_status ENUM('unpaid','pending','paid','paid_cash','complimentary','waived','refunded','rejected') DEFAULT 'unpaid',
  ticket_price DECIMAL(10,2) DEFAULT 0.00,
  currency VARCHAR(10) DEFAULT 'USD',
  discount_amount DECIMAL(10,2) DEFAULT 0.00,
  promo_code_id INT UNSIGNED,
  final_amount DECIMAL(10,2) DEFAULT 0.00,
  is_vip TINYINT(1) DEFAULT 0,
  is_complimentary TINYINT(1) DEFAULT 0,
  waitlist_position INT,
  notes TEXT,
  internal_notes TEXT,
  confirmed_at TIMESTAMP NULL,
  cancelled_at TIMESTAMP NULL,
  registered_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  INDEX idx_business_id (business_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_participant_id (participant_id),
  INDEX idx_status (status),
  INDEX idx_payment_status (payment_status),
  INDEX idx_registration_code (registration_code),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id),
  FOREIGN KEY (ticket_id) REFERENCES tickets(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE registration_answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_id INT UNSIGNED NOT NULL,
  field_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  answer_text TEXT,
  answer_file VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_reg_field (registration_id, field_id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (field_id) REFERENCES registration_fields(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE group_registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  group_name VARCHAR(255) NOT NULL,
  contact_person VARCHAR(255),
  contact_email VARCHAR(255),
  contact_phone VARCHAR(50),
  seat_count INT UNSIGNED NOT NULL,
  payment_mode ENUM('group','individual','mixed') DEFAULT 'group',
  status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments
CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  payment_method_id INT UNSIGNED,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  currency VARCHAR(10) DEFAULT 'USD',
  status ENUM('unpaid','pending','paid','paid_cash','complimentary','waived','refunded','rejected') DEFAULT 'unpaid',
  transaction_reference VARCHAR(255),
  payment_date DATE,
  notes TEXT,
  internal_notes TEXT,
  verified_by INT UNSIGNED,
  verified_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_business_id (business_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_registration_id (registration_id),
  INDEX idx_status (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payment_proofs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  payment_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  proof_file VARCHAR(500) NOT NULL,
  amount_claimed DECIMAL(10,2),
  currency VARCHAR(10) DEFAULT 'USD',
  payment_method_id INT UNSIGNED,
  transaction_reference VARCHAR(255),
  payment_date DATE,
  notes TEXT,
  status ENUM('pending','approved','rejected','needs_resubmission') DEFAULT 'pending',
  reviewed_by INT UNSIGNED,
  reviewed_at TIMESTAMP NULL,
  review_notes TEXT,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_payment_id (payment_id),
  INDEX idx_registration_id (registration_id),
  INDEX idx_status (status),
  FOREIGN KEY (payment_id) REFERENCES payments(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE participant_qr (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_id INT UNSIGNED NOT NULL UNIQUE,
  participant_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  token VARCHAR(64) UNIQUE NOT NULL,
  qr_image_path VARCHAR(500),
  is_active TINYINT(1) DEFAULT 1,
  expires_at TIMESTAMP NULL,
  generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  regenerated_count INT DEFAULT 0,
  INDEX idx_token (token),
  INDEX idx_registration_id (registration_id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
  FOREIGN KEY (participant_id) REFERENCES participants(id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL UNIQUE,
  participant_id INT UNSIGNED NOT NULL,
  checked_in_at TIMESTAMP NULL,
  checked_out_at TIMESTAMP NULL,
  check_in_method ENUM('qr','manual','offline') DEFAULT 'qr',
  check_in_staff_id INT UNSIGNED,
  check_in_device_id VARCHAR(100),
  notes TEXT,
  is_synced TINYINT(1) DEFAULT 1,
  INDEX idx_business_id (business_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_registration_id (registration_id),
  INDEX idx_participant_id (participant_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE session_attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  session_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  status ENUM('present','late','absent','excused') DEFAULT 'present',
  checked_in_at TIMESTAMP NULL,
  notes TEXT,
  marked_by INT UNSIGNED,
  UNIQUE KEY unique_session_reg (session_id, registration_id),
  INDEX idx_session_id (session_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_registration_id (registration_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (session_id) REFERENCES workshop_sessions(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE qr_scan_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  scan_type ENUM('checkin','checkout','attendance','gift','certificate','identity') NOT NULL,
  registration_id INT UNSIGNED,
  participant_id INT UNSIGNED,
  scanned_by INT UNSIGNED,
  device_id VARCHAR(100),
  ip_address VARCHAR(45),
  result ENUM('success','already_scanned','invalid','expired','unauthorized') NOT NULL,
  notes TEXT,
  scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_token (token),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_scan_type (scan_type),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE offline_checkin_buffer (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  device_id VARCHAR(100) NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  token VARCHAR(64) NOT NULL,
  scan_type ENUM('checkin','checkout','attendance','gift') DEFAULT 'checkin',
  staff_id INT UNSIGNED,
  local_scan_id VARCHAR(100),
  scanned_at_local TIMESTAMP NOT NULL,
  synced_at TIMESTAMP NULL,
  sync_result VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_device_workshop (device_id, workshop_id),
  INDEX idx_synced (synced_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gifts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  sku VARCHAR(100),
  quantity INT UNSIGNED,
  distributed_count INT UNSIGNED DEFAULT 0,
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE gift_distributions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  gift_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  distributed_by INT UNSIGNED,
  status ENUM('distributed','returned','pending') DEFAULT 'distributed',
  quantity INT DEFAULT 1,
  notes TEXT,
  distributed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_registration_id (registration_id),
  INDEX idx_gift_id (gift_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (gift_id) REFERENCES gifts(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED,
  participant_id INT UNSIGNED,
  question_text TEXT NOT NULL,
  category VARCHAR(100),
  priority ENUM('normal','high','urgent') DEFAULT 'normal',
  is_anonymous TINYINT(1) DEFAULT 0,
  status ENUM('pending','approved','answered','rejected','archived','pinned') DEFAULT 'pending',
  upvotes INT DEFAULT 0,
  is_pinned TINYINT(1) DEFAULT 0,
  moderated_by INT UNSIGNED,
  answer_text TEXT,
  answered_by INT UNSIGNED,
  answered_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_status (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE question_votes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_vote (question_id, registration_id),
  FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE participant_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  request_type VARCHAR(100) NOT NULL,
  description TEXT,
  priority ENUM('normal','high','urgent') DEFAULT 'normal',
  status ENUM('new','assigned','in_progress','resolved','closed') DEFAULT 'new',
  assigned_to INT UNSIGNED,
  resolution_notes TEXT,
  resolved_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_status (status),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE polls (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  session_id INT UNSIGNED,
  title VARCHAR(500) NOT NULL,
  description TEXT,
  question_type ENUM('single','multiple','rating','yes_no','scale') DEFAULT 'single',
  min_value INT DEFAULT 1,
  max_value INT DEFAULT 5,
  allow_anonymous TINYINT(1) DEFAULT 0,
  status ENUM('draft','active','closed','archived') DEFAULT 'draft',
  launched_at TIMESTAMP NULL,
  closed_at TIMESTAMP NULL,
  created_by INT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE poll_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  poll_id INT UNSIGNED NOT NULL,
  option_text VARCHAR(500) NOT NULL,
  sort_order INT DEFAULT 0,
  response_count INT DEFAULT 0,
  FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE poll_responses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  poll_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  selected_options JSON,
  rating_value INT,
  text_response TEXT,
  is_anonymous TINYINT(1) DEFAULT 0,
  responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_poll_reg (poll_id, registration_id),
  FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  title VARCHAR(500) NOT NULL,
  body TEXT,
  type ENUM('general','urgent','reminder','session') DEFAULT 'general',
  status ENUM('draft','published','archived') DEFAULT 'draft',
  published_by INT UNSIGNED,
  published_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  test_type ENUM('pre_test','post_test','quiz','assessment') DEFAULT 'quiz',
  time_limit_minutes INT,
  pass_score INT DEFAULT 70,
  max_attempts INT DEFAULT 1,
  randomize_questions TINYINT(1) DEFAULT 0,
  status ENUM('draft','active','closed') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE test_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  test_id INT UNSIGNED NOT NULL,
  question_text TEXT NOT NULL,
  question_type ENUM('multiple_choice','true_false','multiple_answer') DEFAULT 'multiple_choice',
  points INT DEFAULT 1,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE test_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question_id INT UNSIGNED NOT NULL,
  option_text VARCHAR(500) NOT NULL,
  is_correct TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (question_id) REFERENCES test_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE test_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  test_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  participant_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  attempt_number INT DEFAULT 1,
  score DECIMAL(5,2),
  max_score INT,
  percentage DECIMAL(5,2),
  passed TINYINT(1),
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  time_taken_seconds INT,
  status ENUM('in_progress','completed','abandoned') DEFAULT 'in_progress',
  UNIQUE KEY unique_attempt (test_id, registration_id, attempt_number),
  INDEX idx_registration_id (registration_id),
  FOREIGN KEY (test_id) REFERENCES tests(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE test_answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL,
  question_id INT UNSIGNED NOT NULL,
  selected_options JSON,
  is_correct TINYINT(1),
  points_earned INT DEFAULT 0,
  FOREIGN KEY (attempt_id) REFERENCES test_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES test_questions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feedback_forms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL UNIQUE,
  title VARCHAR(255) DEFAULT 'Workshop Feedback',
  description TEXT,
  allow_anonymous TINYINT(1) DEFAULT 0,
  status ENUM('draft','active','closed') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feedback_questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id INT UNSIGNED NOT NULL,
  question_text VARCHAR(500) NOT NULL,
  question_type ENUM('rating','text','textarea','radio','checkbox','scale') DEFAULT 'rating',
  options JSON,
  min_value INT DEFAULT 1,
  max_value INT DEFAULT 5,
  is_required TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (form_id) REFERENCES feedback_forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE feedback_responses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  answers JSON NOT NULL,
  overall_rating DECIMAL(3,2),
  is_anonymous TINYINT(1) DEFAULT 0,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_form_reg (form_id, registration_id),
  INDEX idx_workshop_id (workshop_id),
  FOREIGN KEY (form_id) REFERENCES feedback_forms(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certificate_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED,
  name VARCHAR(255) NOT NULL,
  template_file VARCHAR(500),
  layout_config JSON,
  font_family VARCHAR(100),
  primary_color VARCHAR(20),
  custom_text TEXT,
  include_qr TINYINT(1) DEFAULT 1,
  include_signature TINYINT(1) DEFAULT 1,
  signature_image VARCHAR(500),
  status ENUM('active','inactive') DEFAULT 'active',
  is_default TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_business_id (business_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certificate_rules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  workshop_id INT UNSIGNED NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  rule_type ENUM('attendance_percent','session_count','test_pass','feedback_completed','manual') NOT NULL,
  rule_value VARCHAR(100),
  is_required TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE certificates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  certificate_number VARCHAR(50) UNIQUE NOT NULL,
  business_id INT UNSIGNED NOT NULL,
  workshop_id INT UNSIGNED NOT NULL,
  registration_id INT UNSIGNED NOT NULL UNIQUE,
  participant_id INT UNSIGNED NOT NULL,
  template_id INT UNSIGNED,
  verification_token VARCHAR(64) UNIQUE NOT NULL,
  pdf_path VARCHAR(500),
  status ENUM('eligible','issued','revoked') DEFAULT 'eligible',
  issued_at TIMESTAMP NULL,
  issued_by INT UNSIGNED,
  revoked_at TIMESTAMP NULL,
  revoked_by INT UNSIGNED,
  revoke_reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_business_id (business_id),
  INDEX idx_workshop_id (workshop_id),
  INDEX idx_registration_id (registration_id),
  INDEX idx_verification_token (verification_token),
  INDEX idx_certificate_number (certificate_number),
  FOREIGN KEY (business_id) REFERENCES businesses(id),
  FOREIGN KEY (workshop_id) REFERENCES workshops(id),
  FOREIGN KEY (registration_id) REFERENCES registrations(id),
  FOREIGN KEY (participant_id) REFERENCES participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notification_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED,
  template_key VARCHAR(100) NOT NULL,
  name VARCHAR(255) NOT NULL,
  channel ENUM('email','sms','push','whatsapp','telegram') DEFAULT 'email',
  subject VARCHAR(500),
  body TEXT,
  variables JSON,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_key_business_channel (template_key, business_id, channel),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notification_queue (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  business_id INT UNSIGNED,
  template_id INT UNSIGNED,
  recipient_type ENUM('participant','user','business') NOT NULL,
  recipient_id INT UNSIGNED NOT NULL,
  recipient_email VARCHAR(255),
  recipient_phone VARCHAR(50),
  channel ENUM('email','sms','push','whatsapp','telegram') DEFAULT 'email',
  subject VARCHAR(500),
  body TEXT,
  variables JSON,
  status ENUM('queued','processing','sent','failed','cancelled') DEFAULT 'queued',
  attempts INT DEFAULT 0,
  max_attempts INT DEFAULT 3,
  scheduled_at TIMESTAMP NULL,
  processed_at TIMESTAMP NULL,
  sent_at TIMESTAMP NULL,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_scheduled (scheduled_at),
  FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notification_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  queue_id INT UNSIGNED,
  business_id INT UNSIGNED,
  channel ENUM('email','sms','push','whatsapp','telegram') DEFAULT 'email',
  recipient_email VARCHAR(255),
  recipient_phone VARCHAR(50),
  subject VARCHAR(500),
  status ENUM('sent','failed','bounced','opened') DEFAULT 'sent',
  provider_response TEXT,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED,
  business_id INT UNSIGNED,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(100) NOT NULL,
  entity_id VARCHAR(100) NOT NULL,
  old_value JSON,
  new_value JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_id (user_id),
  INDEX idx_business_id (business_id),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
