-- =====================================================
-- VastuVeda Realty CRM — Migration v2
-- Run this on existing installations
-- =====================================================

USE vastuveda_crm;

-- Add missing columns to users
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS designation   VARCHAR(100) AFTER role_id,
    ADD COLUMN IF NOT EXISTS department    VARCHAR(100) AFTER designation,
    ADD COLUMN IF NOT EXISTS join_date     DATE         AFTER department,
    ADD COLUMN IF NOT EXISTS face_descriptor JSON       AFTER profile_photo;

-- Attendance table
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
) ENGINE=InnoDB;
