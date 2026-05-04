CREATE TABLE IF NOT EXISTS attendance (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  date        DATE NOT NULL,
  check_in    TIME DEFAULT NULL,
  check_out   TIME DEFAULT NULL,
  checkin_lat  DECIMAL(10,7) DEFAULT NULL,
  checkin_lng  DECIMAL(10,7) DEFAULT NULL,
  checkout_lat DECIMAL(10,7) DEFAULT NULL,
  checkout_lng DECIMAL(10,7) DEFAULT NULL,
  geo_valid   TINYINT(1) DEFAULT 1,
  image       VARCHAR(255) DEFAULT NULL,
  method      ENUM('camera','photo','manual') DEFAULT 'manual',
  notes       TEXT DEFAULT NULL,
  marked_by   INT DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_date (user_id, date),
  KEY idx_date (date),
  KEY idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS office_settings (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  name   VARCHAR(100) DEFAULT 'Main Office',
  lat    DECIMAL(10,7) DEFAULT NULL,
  lng    DECIMAL(10,7) DEFAULT NULL,
  radius INT DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO office_settings (id, name, lat, lng, radius) VALUES (1, 'Main Office', NULL, NULL, 100);
