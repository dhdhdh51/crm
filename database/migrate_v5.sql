-- migrate_v5.sql — Global settings table for office geo-fence
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(100) PRIMARY KEY,
    `value` TEXT         DEFAULT NULL
);

INSERT IGNORE INTO settings (`key`, `value`) VALUES
  ('office_lat',    NULL),
  ('office_lng',    NULL),
  ('office_radius', '100'),
  ('office_name',   'Main Office');
