-- =====================================================
-- VastuVeda Realty CRM — Migration v2
-- Run on EXISTING installations (already have v1 schema)
-- Compatible with MySQL 8.0+ and MariaDB 10.3+
-- =====================================================

SET NAMES utf8mb4;

USE vastuveda_crm;

-- -------------------------------------------------------
-- Add new columns to users (MySQL 8.0-safe: check first)
-- -------------------------------------------------------

-- designation
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'designation');
SET @sql = IF(@col = 0,
    'ALTER TABLE users ADD COLUMN designation VARCHAR(100) AFTER role_id',
    'SELECT ''Column designation already exists'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- department
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'department');
SET @sql = IF(@col = 0,
    'ALTER TABLE users ADD COLUMN department VARCHAR(100) AFTER designation',
    'SELECT ''Column department already exists'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- join_date
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'join_date');
SET @sql = IF(@col = 0,
    'ALTER TABLE users ADD COLUMN join_date DATE AFTER department',
    'SELECT ''Column join_date already exists'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- face_descriptor
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'face_descriptor');
SET @sql = IF(@col = 0,
    'ALTER TABLE users ADD COLUMN face_descriptor JSON AFTER profile_photo',
    'SELECT ''Column face_descriptor already exists'' AS info');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------
-- Create attendance table if not exists
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    date           DATE         NOT NULL,
    check_in       TIME,
    check_out      TIME,
    status         ENUM('present','absent','late','half_day','holiday') DEFAULT 'present',
    method         ENUM('face','manual') DEFAULT 'face',
    check_in_photo VARCHAR(255),
    notes          TEXT,
    marked_by      INT UNSIGNED,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_attendance (user_id, date),
    INDEX idx_date    (date),
    INDEX idx_user_id (user_id),
    INDEX idx_status  (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- Update roles permissions to include attendance
-- -------------------------------------------------------
UPDATE roles SET permissions = '["all"]'
    WHERE slug = 'admin';
UPDATE roles SET permissions = '["leads","projects","employees","reports","payroll","site_visits","attendance"]'
    WHERE slug = 'manager';
UPDATE roles SET permissions = '["leads","site_visits","projects","attendance"]'
    WHERE slug = 'sales_executive';
