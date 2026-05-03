-- ============================================================
-- VastuVeda CRM v3 — Migration
-- Run in phpMyAdmin: SQL tab
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Extend roles table with new slugs ──────────────────────
INSERT IGNORE INTO roles (name, slug, permissions) VALUES
('Super Admin', 'super_admin', '["*"]'),
('HR Manager',  'hr',         '["employees","attendance","salary","targets","expenses"]');

-- ── 2. Extend users table ─────────────────────────────────────
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS geo_lat    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS geo_lng    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS geo_radius INT DEFAULT 200;

-- ── 3. Extend attendance table ────────────────────────────────
ALTER TABLE attendance
  ADD COLUMN IF NOT EXISTS checkin_image  VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkin_lat    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkin_lng    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_image VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_lat   DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_lng   DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS geo_valid      TINYINT(1) DEFAULT 1;

-- ── 4. Lead upload logs ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS lead_upload_logs (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  filename     VARCHAR(255) NOT NULL,
  total_rows   INT DEFAULT 0,
  imported     INT DEFAULT 0,
  failed       INT DEFAULT 0,
  uploaded_by  INT UNSIGNED,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Targets ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS targets (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  month        TINYINT NOT NULL,
  year         YEAR NOT NULL,
  target_leads INT DEFAULT 0,
  target_visits INT DEFAULT 0,
  target_sales INT DEFAULT 0,
  target_revenue DECIMAL(12,2) DEFAULT 0,
  created_by   INT UNSIGNED,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_target (user_id, month, year),
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Expenses ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS expenses (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category     VARCHAR(100) NOT NULL,
  description  TEXT,
  amount       DECIMAL(10,2) NOT NULL DEFAULT 0,
  expense_date DATE NOT NULL,
  receipt      VARCHAR(255) DEFAULT NULL,
  added_by     INT UNSIGNED,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_date (expense_date),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
