-- migrate_v4.sql — Performance indexes + schema fixes
-- Run once on your database

-- Attendance: index on (user_id, date) for all filtered queries
ALTER TABLE attendance
  ADD INDEX IF NOT EXISTS idx_att_user_date (user_id, date),
  ADD INDEX IF NOT EXISTS idx_att_date      (date),
  ADD INDEX IF NOT EXISTS idx_att_status    (status);

-- Salaries: index for per-user slip lookup and month queries
ALTER TABLE salaries
  ADD INDEX IF NOT EXISTS idx_sal_user        (user_id),
  ADD INDEX IF NOT EXISTS idx_sal_user_month  (user_id, month, year),
  ADD INDEX IF NOT EXISTS idx_sal_month_year  (month, year);

-- Users: active users lookup (used by descriptors + employee lists)
ALTER TABLE users
  ADD INDEX IF NOT EXISTS idx_users_active       (is_active),
  ADD INDEX IF NOT EXISTS idx_users_active_face  (is_active, face_descriptor(1));

-- Ensure geo columns exist (safe re-run)
ALTER TABLE users
  MODIFY COLUMN geo_lat    DECIMAL(10,7) DEFAULT NULL,
  MODIFY COLUMN geo_lng    DECIMAL(10,7) DEFAULT NULL,
  MODIFY COLUMN geo_radius INT           DEFAULT 100;

-- Ensure attendance image/geo columns exist
ALTER TABLE attendance
  ADD COLUMN IF NOT EXISTS checkin_image   VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_image  VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkin_lat     DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkin_lng     DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_lat    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS checkout_lng    DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS geo_valid       TINYINT(1) DEFAULT 1;
