-- ----------------------------------------------------
-- Database: belge
-- ----------------------------------------------------
CREATE DATABASE IF NOT EXISTS belge
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE belge;

-- ----------------------------------------------------
-- Users
-- role: "1" = admin, "0" = standard user (you can change)
-- password_hash is created with PHP password_hash()
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL DEFAULT '0',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------
-- Records
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS records (
  id INT AUTO_INCREMENT PRIMARY KEY,

  file_no VARCHAR(50) NOT NULL UNIQUE,
  national_id VARCHAR(50) NULL,
  first_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,

  gender VARCHAR(10) NULL,          -- M / F (or MALE/FEMALE)
  date_of_birth DATE NULL,
  date_of_death DATE NULL,

  mother_name VARCHAR(100) NULL,
  father_name VARCHAR(100) NULL,
  department VARCHAR(150) NULL,

  category VARCHAR(30) NULL,        -- NORMAL / EX_ENTRY / LEGAL_CASE / FETUS
  notes TEXT NULL,

  created_at DATETIME NOT NULL,
  created_by VARCHAR(100) NOT NULL,

  has_file TINYINT(1) NOT NULL DEFAULT 0,

  -- Cancel (soft cancel)
  cancelled TINYINT(1) NOT NULL DEFAULT 0,
  cancel_reason TEXT NULL,
  cancelled_at DATETIME NULL,
  cancelled_by VARCHAR(100) NULL,

  -- Delete (soft delete)
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  deleted_reason TEXT NULL,
  deleted_at DATETIME NULL,
  deleted_by VARCHAR(100) NULL

) ENGINE=InnoDB;

CREATE INDEX idx_records_created_at ON records (created_at);
CREATE INDEX idx_records_cancelled ON records (cancelled);
CREATE INDEX idx_records_is_deleted ON records (is_deleted);

-- ----------------------------------------------------
-- Record change log (updated records)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS record_changes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  record_id INT NOT NULL,
  changed_at DATETIME NOT NULL,
  changed_by VARCHAR(100) NOT NULL,
  INDEX idx_changes_record_id (record_id),
  CONSTRAINT fk_changes_record
    FOREIGN KEY (record_id) REFERENCES records(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------
-- Record deletions log (when soft delete is used)
-- ----------------------------------------------------
CREATE TABLE IF NOT EXISTS record_deletions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  record_id INT NOT NULL,
  deleted_at DATETIME NOT NULL,
  deleted_by VARCHAR(100) NOT NULL,
  deleted_reason TEXT NOT NULL,
  INDEX idx_deletions_record_id (record_id),
  CONSTRAINT fk_deletions_record
    FOREIGN KEY (record_id) REFERENCES records(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------
-- OPTIONAL: Create an admin user (you MUST paste a real hash)
-- Step: run password_hash in PHP and replace <PASTE_HASH_HERE>
-- ----------------------------------------------------
-- Example:
-- INSERT INTO users (username, password_hash, role)
-- VALUES ('admin', '$2y$10$NYhAiMtMMNWdymdfIfZtp.mkXAujsmCtLMDJZlzx8vHnvazJ4aFq2', '1');
