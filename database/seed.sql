-- =====================================================
-- VastuVeda Realty CRM — Seed Data
-- Run AFTER schema.sql  (or use install.sql for both)
-- =====================================================

SET NAMES utf8mb4;

USE vastuveda_crm;

-- -------------------------------------------------------
-- ROLES
-- -------------------------------------------------------
INSERT INTO roles (name, slug, permissions) VALUES
('Administrator',  'admin',           '["all"]'),
('Manager',        'manager',         '["leads","projects","employees","reports","payroll","site_visits","attendance"]'),
('Sales Executive','sales_executive',  '["leads","site_visits","projects","attendance"]');

-- -------------------------------------------------------
-- USERS  (default password: Admin@1234)
-- -------------------------------------------------------
INSERT INTO users (employee_id, name, email, phone, password, role_id, designation, department, join_date, is_active) VALUES
('EMP001', 'Rajesh Sharma', 'admin@vastuveda.com',  '9876543210',
 '$2y$12$M1CcklWDOfU5GUiHX3QmHOt8.ioJPPKWx5hN3.7xXX2u78fkJWDBm',
 1, 'Managing Director', 'Administration', '2020-01-15', 1),
('EMP002', 'Priya Mehta',   'priya@vastuveda.com',  '9876543211',
 '$2y$12$M1CcklWDOfU5GUiHX3QmHOt8.ioJPPKWx5hN3.7xXX2u78fkJWDBm',
 2, 'Sales Manager',     'Sales',          '2021-03-01', 1),
('EMP003', 'Amit Patel',    'amit@vastuveda.com',   '9876543212',
 '$2y$12$M1CcklWDOfU5GUiHX3QmHOt8.ioJPPKWx5hN3.7xXX2u78fkJWDBm',
 3, 'Senior Sales Exec', 'Sales',          '2022-06-15', 1),
('EMP004', 'Sunita Verma',  'sunita@vastuveda.com', '9876543213',
 '$2y$12$M1CcklWDOfU5GUiHX3QmHOt8.ioJPPKWx5hN3.7xXX2u78fkJWDBm',
 3, 'Sales Executive',   'Sales',          '2022-09-01', 1),
('EMP005', 'Ravi Krishnan', 'ravi@vastuveda.com',   '9876543214',
 '$2y$12$M1CcklWDOfU5GUiHX3QmHOt8.ioJPPKWx5hN3.7xXX2u78fkJWDBm',
 3, 'Sales Executive',   'Sales',          '2023-01-10', 1);

-- -------------------------------------------------------
-- PROJECTS
-- -------------------------------------------------------
INSERT INTO projects (name, location, description, total_units, available_units, price_per_sqft, status, created_by) VALUES
('Vastu Heights Phase I',    'Sector 45, Noida, UP',       'Premium residential complex with world-class amenities, swimming pool, and landscaped gardens.',    120, 45,  8500.00,  'active',   1),
('Golden Meadows Villa',     'Gomti Nagar Ext., Lucknow',  'Exclusive villas with Vastu-compliant designs, private gardens, and state-of-the-art security.',    40,  18,  12000.00, 'active',   1),
('Realty Square Commercial', 'Hazratganj, Lucknow',        'Premium commercial spaces in the heart of the city, ideal for offices, showrooms, and retail.',     60,  35,  15000.00, 'active',   1),
('Sunrise Valley Plots',     'Amar Shaheed Path, Lucknow', 'RERA-approved residential plots with proper infrastructure, near schools, hospitals, and metro.',    200, 120, 5500.00,  'upcoming', 1),
('Pearl Residency',          'Indiranagar, Lucknow',       'Affordable luxury apartments with modern amenities and easy connectivity to major IT hubs.',         80,  5,   9200.00,  'sold_out', 1);

-- -------------------------------------------------------
-- UNITS
-- -------------------------------------------------------
INSERT INTO units (project_id, unit_number, type, floor, area_sqft, price, status, facing) VALUES
(1, 'A-101', 'apartment', 1, 1200.00, 10200000.00, 'sold',      'East'),
(1, 'A-102', 'apartment', 1, 1350.00, 11475000.00, 'available', 'North'),
(1, 'A-201', 'apartment', 2, 1200.00, 10200000.00, 'booked',    'East'),
(1, 'A-202', 'apartment', 2, 1650.00, 14025000.00, 'available', 'South'),
(1, 'B-101', 'apartment', 1, 1800.00, 15300000.00, 'available', 'North-East'),
(2, 'V-01',  'villa',     0, 3200.00, 38400000.00, 'sold',      'East'),
(2, 'V-02',  'villa',     0, 3200.00, 38400000.00, 'available', 'North'),
(2, 'V-03',  'villa',     0, 4000.00, 48000000.00, 'reserved',  'North-East'),
(3, 'G-101', 'office',    1,  800.00, 12000000.00, 'available', 'South'),
(3, 'G-102', 'shop',      0,  400.00,  6000000.00, 'booked',    'East');

-- -------------------------------------------------------
-- LEADS
-- -------------------------------------------------------
INSERT INTO leads (name, phone, email, source, status, interested_project_id, budget_min, budget_max, preferred_type, assigned_to, notes, next_followup_date, created_by) VALUES
('Arun Kumar Singh', '9811223344', 'arun@example.com',   'website',       'hot',    1, 8000000,  15000000, 'apartment', 3, 'Very interested, ready to visit',         DATE_ADD(CURDATE(), INTERVAL 1 DAY), 1),
('Meena Agarwal',    '9822334455', 'meena@example.com',  'referral',      'warm',   2, 30000000, 50000000, 'villa',     4, 'Looking for Vastu-compliant villa',       DATE_ADD(CURDATE(), INTERVAL 2 DAY), 1),
('Suresh Chandra',   '9833445566', NULL,                 'walk_in',       'new',    3, 10000000, 20000000, 'office',    3, 'Visited showroom today',                  DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1),
('Pooja Nair',       '9844556677', 'pooja@example.com',  'social_media',  'cold',   1, 5000000,  10000000, 'apartment', 5, 'Not fully decided yet',                   DATE_ADD(CURDATE(), INTERVAL 7 DAY), 2),
('Vikram Malhotra',  '9855667788', 'vikram@example.com', 'advertisement', 'closed', 1, 9000000,  12000000, 'apartment', 3, 'Booked Unit A-201',                       NULL,                                1),
('Anita Desai',      '9866778899', 'anita@example.com',  'referral',      'hot',    2, 35000000, 45000000, 'villa',     4, 'NRI client, very serious buyer',          DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2),
('Ramesh Yadav',     '9877889900', NULL,                 'cold_call',     'warm',   4, 4000000,  7000000,  'plot',      5, 'Interested in plots',                     DATE_ADD(CURDATE(), INTERVAL 4 DAY), 1),
('Divya Kapoor',     '9888990011', 'divya@example.com',  'website',       'new',    1, 7000000,  11000000, 'apartment', 3, 'First-time buyer',                        DATE_ADD(CURDATE(), INTERVAL 5 DAY), 1),
('Sanjay Mishra',    '9899001122', NULL,                 'walk_in',       'lost',   3, 8000000,  15000000, 'office',    4, 'Went with competitor',                    NULL,                                2),
('Kavita Pandey',    '9800112233', 'kavita@example.com', 'referral',      'hot',    2, 38000000, 50000000, 'villa',     5, 'Referred by Mr. Vikram',                  DATE_ADD(CURDATE(), INTERVAL 2 DAY), 1);

-- -------------------------------------------------------
-- FOLLOWUPS
-- -------------------------------------------------------
INSERT INTO followups (lead_id, user_id, followup_date, type, notes, outcome, next_followup_date, status) VALUES
(1, 3, DATE_SUB(NOW(), INTERVAL 3 DAY),  'call',     'Initial contact call',              'Very interested, wants to visit site', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'completed'),
(1, 3, DATE_ADD(NOW(), INTERVAL 1 DAY),  'visit',    'Site visit scheduled',              NULL,                                   NULL,                                'pending'),
(2, 4, DATE_SUB(NOW(), INTERVAL 5 DAY),  'call',     'Explained villa features',          'Requested brochure via WhatsApp',      DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'completed'),
(3, 3, DATE_SUB(NOW(), INTERVAL 1 DAY),  'whatsapp', 'Sent project details',              'Acknowledged, will call back',         DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'completed'),
(5, 3, DATE_SUB(NOW(), INTERVAL 10 DAY), 'meeting',  'Deal finalization meeting',         'Booking done! Amount paid.',           NULL,                                'completed'),
(6, 4, DATE_ADD(NOW(), INTERVAL 1 DAY),  'call',     'Follow-up for NRI client',          NULL,                                   NULL,                                'pending'),
(7, 5, DATE_ADD(NOW(), INTERVAL 4 DAY),  'call',     'Explain plot investment benefits',  NULL,                                   NULL,                                'pending');

-- -------------------------------------------------------
-- SITE VISITS
-- -------------------------------------------------------
INSERT INTO site_visits (lead_id, project_id, assigned_to, visit_date, status, feedback, notes, created_by) VALUES
(1, 1, 3, DATE_ADD(NOW(), INTERVAL 1 DAY),  'scheduled', NULL,                                        'Pickup from Hazratganj Metro',   1),
(2, 2, 4, DATE_SUB(NOW(), INTERVAL 2 DAY),  'completed', 'Client loved the villa, negotiating price', 'Came with spouse',               2),
(3, 3, 3, DATE_SUB(NOW(), INTERVAL 5 DAY),  'completed', 'Interested in ground floor unit',           'Wants commercial office',        1),
(5, 1, 3, DATE_SUB(NOW(), INTERVAL 12 DAY), 'completed', 'Loved the view, decided to book',           'Result: Booking confirmed',      1),
(6, 2, 4, DATE_ADD(NOW(), INTERVAL 3 DAY),  'scheduled', NULL,                                        'NRI visit, arrange interpreter', 2),
(8, 1, 3, DATE_ADD(NOW(), INTERVAL 6 DAY),  'scheduled', NULL,                                        'First-time visitor',             1);

-- -------------------------------------------------------
-- BOOKINGS
-- -------------------------------------------------------
INSERT INTO bookings (lead_id, unit_id, booking_date, total_amount, booking_amount, payment_status, handled_by, notes) VALUES
(5, 3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 10200000.00, 1000000.00, 'partial', 3, 'Booking amount received. Remaining via home loan.'),
(6, 7, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  38400000.00, 5000000.00, 'partial', 4, 'NRI booking. Full payment in 60 days.');

-- -------------------------------------------------------
-- SALARIES  (fixed: MAKEDATE avoids DATE_FORMAT string cast)
-- -------------------------------------------------------
INSERT INTO salaries (user_id, month, year, base_salary, incentives, bonus, deductions, payment_date, payment_status, remarks, generated_by) VALUES
(3, MONTH(CURDATE()),                              YEAR(CURDATE()),                              35000.00,  8000.00, 5000.00, 3500.00, MAKEDATE(YEAR(CURDATE()),1) + INTERVAL (MONTH(CURDATE())-1) MONTH + INTERVAL 27 DAY, 'paid',    'Incentive for 2 bookings',   1),
(4, MONTH(CURDATE()),                              YEAR(CURDATE()),                              40000.00, 12000.00,    0.00, 4000.00, MAKEDATE(YEAR(CURDATE()),1) + INTERVAL (MONTH(CURDATE())-1) MONTH + INTERVAL 27 DAY, 'paid',    'Incentive for NRI client',   1),
(5, MONTH(CURDATE()),                              YEAR(CURDATE()),                              32000.00,  3000.00,    0.00, 3200.00, NULL,                                                                                 'pending', 'Salary processing',          1),
(3, MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)),  YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)), 35000.00,  5000.00,    0.00, 3500.00, MAKEDATE(YEAR(DATE_SUB(CURDATE(),INTERVAL 1 MONTH)),1) + INTERVAL (MONTH(DATE_SUB(CURDATE(),INTERVAL 1 MONTH))-1) MONTH + INTERVAL 27 DAY, 'paid', 'Regular', 1);

-- -------------------------------------------------------
-- NOTIFICATIONS
-- -------------------------------------------------------
INSERT INTO notifications (user_id, type, message, is_read, link) VALUES
(3, 'followup', 'You have a site visit scheduled tomorrow with Arun Kumar Singh', 0, '/site-visits'),
(3, 'lead',     'New lead assigned: Divya Kapoor',                                 0, '/leads/8'),
(4, 'followup', 'Follow-up pending with Anita Desai (NRI Client)',                 0, '/leads/6'),
(1, 'booking',  'New booking recorded — Unit A-201 by Vikram Malhotra',            0, '/reports/sales'),
(1, 'system',   'Monthly report for this month is ready to view',                  1, '/reports');

-- -------------------------------------------------------
-- ATTENDANCE  (sample records for current & past 4 days)
-- -------------------------------------------------------
INSERT INTO attendance (user_id, date, check_in, check_out, status, method, marked_by) VALUES
(3, CURDATE(),                            '09:05:00', NULL,       'present', 'face',   3),
(4, CURDATE(),                            '09:45:00', NULL,       'late',    'face',   4),
(5, CURDATE(),                            '09:00:00', NULL,       'present', 'face',   5),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  '09:10:00', '18:05:00', 'present', 'face',   3),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  '09:55:00', '18:30:00', 'late',    'face',   4),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  '09:00:00', '18:00:00', 'present', 'face',   5),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  '09:00:00', '17:55:00', 'present', 'face',   3),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  NULL,        NULL,      'absent',  'manual', 1),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  '09:05:00', '18:10:00', 'present', 'face',   5),
(3, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  '09:30:00', '18:00:00', 'late',    'face',   3),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  '09:00:00', '17:30:00', 'present', 'face',   4),
(5, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  '09:00:00', '18:00:00', 'present', 'face',   5),
(3, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  '09:00:00', '18:00:00', 'present', 'face',   3),
(4, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  '09:00:00', '18:00:00', 'present', 'face',   4),
(5, DATE_SUB(CURDATE(), INTERVAL 4 DAY),  '09:15:00', '18:00:00', 'present', 'face',   5);

-- -------------------------------------------------------
-- Sync project unit counts from actual unit rows
-- -------------------------------------------------------
UPDATE projects p SET
    total_units     = (SELECT COUNT(*) FROM units u WHERE u.project_id = p.id),
    available_units = (SELECT COUNT(*) FROM units u WHERE u.project_id = p.id AND u.status = 'available');
