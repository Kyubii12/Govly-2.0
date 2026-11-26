-- GovLy 2.0 — Barangay Sta. Cruz (Hostinger MySQL)
-- Import in hPanel → phpMyAdmin → Import
-- Email OTP + certificate completion emails only (no SMS)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(80) NOT NULL DEFAULT '',
  middle_name VARCHAR(80) DEFAULT NULL,
  last_name VARCHAR(80) NOT NULL DEFAULT '',
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(190) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','super_admin','resident') NOT NULL DEFAULT 'resident',
  status ENUM('active','pending','inactive','rejected') NOT NULL DEFAULT 'pending',
  street_address VARCHAR(255) DEFAULT NULL,
  purok VARCHAR(40) DEFAULT NULL,
  occupation VARCHAR(120) DEFAULT NULL,
  id_path VARCHAR(255) DEFAULT NULL,
  selfie_path VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_phone (phone),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_otps (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  purpose ENUM('register','login') NOT NULL DEFAULT 'register',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_otp_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS residents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  full_name VARCHAR(190) NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  purok VARCHAR(40) DEFAULT NULL,
  occupation VARCHAR(120) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Listed',
  authorized TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_residents_user (user_id),
  KEY idx_residents_phone (phone),
  CONSTRAINT fk_residents_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS registrations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  first_name VARCHAR(80) DEFAULT NULL,
  middle_name VARCHAR(80) DEFAULT NULL,
  last_name VARCHAR(80) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(190) DEFAULT NULL,
  street_address VARCHAR(255) DEFAULT NULL,
  purok VARCHAR(40) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  resident_id INT UNSIGNED DEFAULT NULL,
  resident_name VARCHAR(190) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  email VARCHAR(190) DEFAULT NULL,
  certificate_type VARCHAR(120) DEFAULT NULL,
  payment_mode VARCHAR(40) DEFAULT NULL,
  purpose VARCHAR(255) DEFAULT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  admin_note VARCHAR(255) DEFAULT NULL,
  last_cert JSON DEFAULT NULL,
  walk_in TINYINT(1) NOT NULL DEFAULT 0,
  status_email_sent_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS request_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_id INT UNSIGNED NOT NULL,
  action VARCHAR(120) NOT NULL,
  detail TEXT,
  actor VARCHAR(120) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_logs_req (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(120) NOT NULL,
  detail TEXT,
  actor VARCHAR(120) DEFAULT NULL,
  actor_role VARCHAR(40) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  body TEXT,
  cover_path VARCHAR(255) DEFAULT NULL,
  gallery_json TEXT DEFAULT NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS officials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  position VARCHAR(80) DEFAULT NULL,
  description TEXT,
  order_num INT NOT NULL DEFAULT 99,
  photo_url VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id INT UNSIGNED DEFAULT NULL,
  sender_name VARCHAR(190) DEFAULT NULL,
  sender_role VARCHAR(20) DEFAULT NULL,
  recipient_role VARCHAR(20) DEFAULT 'admin',
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cert_templates (
  type VARCHAR(120) PRIMARY KEY,
  body TEXT,
  purpose TEXT,
  details TEXT,
  closing TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS printed_certs (
  id VARCHAR(40) PRIMARY KEY,
  type VARCHAR(120) DEFAULT NULL,
  recipient_name VARCHAR(190) DEFAULT NULL,
  purpose VARCHAR(255) DEFAULT NULL,
  issued_date VARCHAR(120) DEFAULT NULL,
  certified_by VARCHAR(190) DEFAULT NULL,
  receipt_no VARCHAR(40) DEFAULT NULL,
  amount VARCHAR(40) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  id VARCHAR(40) PRIMARY KEY,
  content JSON NOT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Super Admin
-- Email: admin@stacruzbacnotan.com
-- Password: Admin@12345
INSERT INTO users (first_name, last_name, phone, email, password_hash, role, status)
VALUES (
  'Super',
  'Admin',
  '09190000000',
  'admin@stacruzbacnotan.com',
  '$2y$10$TQXZrpHV/5ejj9Y.45ONN.J1k3phnD4r/e.z1rsN3aRwjarSYO8L2',
  'super_admin',
  'active'
) ON DUPLICATE KEY UPDATE role = 'super_admin', status = 'active';

INSERT INTO cert_templates (type, body, purpose, details, closing) VALUES
('Barangay Clearance', 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS and is known to this office to be a person of good moral character, with no pending complaint or derogatory record on file in this barangay.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.'),
('Certificate of Residency', 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.'),
('Certificate of Indigency', 'This is to certify that RESIDENT NAME, of legal age, is a bona fide resident of ADDRESS and that their family had low income as considered an indigent family in this barangay.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.'),
('Business Permit', 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is granted barangay clearance in connection with a business application as stated below.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.'),
('Solo Parent ID Application', 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is a qualified solo parent of this barangay.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.'),
('Solo Parent Assistance', 'This is to certify that RESIDENT NAME, a bona fide resident of ADDRESS, is a qualified solo parent and may be considered for assistance programs of this barangay.', '', '', 'This certification is issued upon the request of the party concerned for intents and purposes it may serve.')
ON DUPLICATE KEY UPDATE body = VALUES(body);

INSERT INTO settings (id, content) VALUES
('portal', JSON_OBJECT(
  'emergencyCall', '911',
  'emergencyLead', 'For an immediate emergency in Barangay Sta. Cruz, Bacnotan, La Union, call 911 or the appropriate local response number directly.',
  'officeAddress', 'Barangay Sta. Cruz, Bacnotan\nLa Union, Philippines',
  'contactPhones', '0951-960-2398\n0919-546-0617',
  'contactEmail', 'brgy.stacruz@bacnotan.gov.ph',
  'officeHours', 'Monday – Friday: 8:00 AM – 5:00 PM\nSaturday: 8:00 AM – 12:00 PM',
  'footerLocation', 'Bacnotan, La Union',
  'services', JSON_ARRAY(
    JSON_OBJECT('type','Barangay Clearance','price','₱50.00','time','1-2 days','desc','Official clearance certificate for employment, business, and legal purposes.'),
    JSON_OBJECT('type','Certificate of Residency','price','₱50.00','time','1-2 days','desc','Proof of residence for government transactions and applications.'),
    JSON_OBJECT('type','Certificate of Indigency','price','FREE','time','1-2 days','desc','For medical, educational, and social welfare assistance programs.'),
    JSON_OBJECT('type','Business Permit','price','₱500.00+','time','3-5 days','desc','Barangay business clearance for new and renewal applications.'),
    JSON_OBJECT('type','Solo Parent ID Application','price','FREE','time','Same day','desc','Official identification for solo parents to access government benefits and programs.'),
    JSON_OBJECT('type','Solo Parent Assistance','price','FREE','time','By schedule','desc','Financial and livelihood assistance programs for solo parents and their families.')
  )
)) ON DUPLICATE KEY UPDATE id = id;

SET FOREIGN_KEY_CHECKS = 1;
