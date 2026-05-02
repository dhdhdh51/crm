-- =====================================================
-- VastuVeda Realty CRM — Database Schema
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- Version: 1.0.0
-- =====================================================

CREATE DATABASE IF NOT EXISTS vastuveda_crm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vastuveda_crm;

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- ROLES
-- -------------------------------------------------------
CREATE TABLE roles (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(50)  NOT NULL UNIQUE,
    slug      VARCHAR(50)  NOT NULL UNIQUE,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- USERS
-- -------------------------------------------------------
CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id     VARCHAR(20)  NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(150) UNIQUE,
    phone           VARCHAR(15),
    password        VARCHAR(255) NOT NULL,
    role_id         INT UNSIGNED NOT NULL,
    designation     VARCHAR(100),
    department      VARCHAR(100),
    join_date       DATE,
    is_active       TINYINT(1)   DEFAULT 1,
    last_login      TIMESTAMP    NULL,
    profile_photo   VARCHAR(255),
    face_descriptor JSON,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_employee_id (employee_id),
    INDEX idx_role_id     (role_id),
    INDEX idx_is_active   (is_active)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- PROJECTS
-- -------------------------------------------------------
CREATE TABLE projects (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    location        VARCHAR(300),
    description     TEXT,
    total_units     INT DEFAULT 0,
    available_units INT DEFAULT 0,
    price_per_sqft  DECIMAL(10,2),
    status          ENUM('active','upcoming','sold_out','on_hold') DEFAULT 'active',
    image           VARCHAR(255),
    brochure        VARCHAR(255),
    created_by      INT UNSIGNED,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- UNITS / INVENTORY
-- -------------------------------------------------------
CREATE TABLE units (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id   INT UNSIGNED NOT NULL,
    unit_number  VARCHAR(50)  NOT NULL,
    type         ENUM('apartment','plot','villa','office','shop') DEFAULT 'apartment',
    floor        INT,
    area_sqft    DECIMAL(10,2),
    price        DECIMAL(15,2),
    status       ENUM('available','booked','sold','reserved') DEFAULT 'available',
    facing       VARCHAR(50),
    description  TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY uk_unit (project_id, unit_number),
    INDEX idx_project_status (project_id, status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- LEADS
-- -------------------------------------------------------
CREATE TABLE leads (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                 VARCHAR(100) NOT NULL,
    phone                VARCHAR(15)  NOT NULL,
    email                VARCHAR(150),
    source               ENUM('website','referral','walk_in','social_media',
                              'advertisement','cold_call','other') DEFAULT 'website',
    status               ENUM('new','hot','warm','cold','closed','lost') DEFAULT 'new',
    interested_project_id INT UNSIGNED,
    budget_min           DECIMAL(15,2),
    budget_max           DECIMAL(15,2),
    preferred_type       VARCHAR(100),
    assigned_to          INT UNSIGNED,
    notes                TEXT,
    address              TEXT,
    next_followup_date   DATE,
    closed_value         DECIMAL(15,2),
    created_by           INT UNSIGNED,
    created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (interested_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to)           REFERENCES users(id)    ON DELETE SET NULL,
    FOREIGN KEY (created_by)            REFERENCES users(id)    ON DELETE SET NULL,
    INDEX idx_status           (status),
    INDEX idx_assigned_to      (assigned_to),
    INDEX idx_next_followup    (next_followup_date),
    INDEX idx_created_at       (created_at),
    INDEX idx_source           (source)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- FOLLOWUPS
-- -------------------------------------------------------
CREATE TABLE followups (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id            INT UNSIGNED NOT NULL,
    user_id            INT UNSIGNED NOT NULL,
    followup_date      DATETIME     NOT NULL,
    type               ENUM('call','visit','email','whatsapp','meeting') DEFAULT 'call',
    notes              TEXT,
    outcome            TEXT,
    next_followup_date DATE,
    status             ENUM('pending','completed','missed') DEFAULT 'pending',
    created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id)  REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_lead_id      (lead_id),
    INDEX idx_followup_date(followup_date),
    INDEX idx_status       (status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- SITE VISITS
-- -------------------------------------------------------
CREATE TABLE site_visits (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id     INT UNSIGNED NOT NULL,
    project_id  INT UNSIGNED,
    assigned_to INT UNSIGNED,
    visit_date  DATETIME     NOT NULL,
    status      ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
    feedback    TEXT,
    notes       TEXT,
    created_by  INT UNSIGNED,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id)     REFERENCES leads(id)    ON DELETE CASCADE,
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id)    ON DELETE SET NULL,
    FOREIGN KEY (created_by)  REFERENCES users(id)    ON DELETE SET NULL,
    INDEX idx_visit_date  (visit_date),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status      (status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- BOOKINGS
-- -------------------------------------------------------
CREATE TABLE bookings (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id        INT UNSIGNED   NOT NULL,
    unit_id        INT UNSIGNED   NOT NULL,
    booking_date   DATE           NOT NULL,
    total_amount   DECIMAL(15,2)  NOT NULL,
    booking_amount DECIMAL(15,2),
    payment_status ENUM('pending','partial','completed') DEFAULT 'pending',
    handled_by     INT UNSIGNED,
    notes          TEXT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id)    REFERENCES leads(id)    ON DELETE RESTRICT,
    FOREIGN KEY (unit_id)    REFERENCES units(id)    ON DELETE RESTRICT,
    FOREIGN KEY (handled_by) REFERENCES users(id)    ON DELETE SET NULL,
    INDEX idx_booking_date (booking_date),
    INDEX idx_handled_by   (handled_by),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- SALARIES / PAYROLL
-- -------------------------------------------------------
CREATE TABLE salaries (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED   NOT NULL,
    month          TINYINT        NOT NULL,
    year           YEAR           NOT NULL,
    base_salary    DECIMAL(10,2)  NOT NULL DEFAULT 0,
    incentives     DECIMAL(10,2)  DEFAULT 0,
    bonus          DECIMAL(10,2)  DEFAULT 0,
    deductions     DECIMAL(10,2)  DEFAULT 0,
    net_salary     DECIMAL(10,2)  GENERATED ALWAYS AS
                   (base_salary + incentives + bonus - deductions) STORED,
    payment_date   DATE,
    payment_status ENUM('pending','paid') DEFAULT 'pending',
    remarks        TEXT,
    generated_by   INT UNSIGNED,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_salary (user_id, month, year),
    INDEX idx_user_id    (user_id),
    INDEX idx_year_month (year, month)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- NOTIFICATIONS
-- -------------------------------------------------------
CREATE TABLE notifications (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    message    TEXT         NOT NULL,
    is_read    TINYINT(1)   DEFAULT 0,
    link       VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_created_at  (created_at)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- ACTIVITY LOGS
-- -------------------------------------------------------
CREATE TABLE activity_logs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED,
    action     VARCHAR(100) NOT NULL,
    module     VARCHAR(50),
    record_id  INT UNSIGNED,
    details    TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id   (user_id),
    INDEX idx_module    (module),
    INDEX idx_created_at(created_at)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- ATTENDANCE
-- -------------------------------------------------------
CREATE TABLE attendance (
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

SET FOREIGN_KEY_CHECKS = 1;
