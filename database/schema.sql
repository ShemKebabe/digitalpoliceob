CREATE DATABASE IF NOT EXISTS digital_police_ob CHARACTER SET utf8mb4;
USE digital_police_ob;

DROP TABLE IF EXISTS ob_amendments, ob_actions, ob_entries, audit_log, public_reports, incident_types, locations, officers;

CREATE TABLE officers (
  officer_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  service_number VARCHAR(20)  NOT NULL UNIQUE,
  full_name      VARCHAR(100) NOT NULL,
  `rank`         VARCHAR(40)  NOT NULL,
  role           ENUM('officer','admin')    NOT NULL DEFAULT 'officer',
  password_hash  VARCHAR(255) NOT NULL,
  status         ENUM('active','inactive')  NOT NULL DEFAULT 'active',
  created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE incident_types (
  incident_type_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type_name        VARCHAR(60)  NOT NULL UNIQUE,
  category         VARCHAR(50)  NULL,
  description      VARCHAR(255) NULL
);

CREATE TABLE locations (
  location_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location_name    VARCHAR(100) NOT NULL UNIQUE,
  area_description VARCHAR(255) NULL
);

CREATE TABLE ob_entries (
  entry_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  srs_number        VARCHAR(20)  NOT NULL UNIQUE,
  entry_datetime    DATETIME     NOT NULL,
  incident_type_id  INT UNSIGNED NOT NULL,
  location_id       INT UNSIGNED NOT NULL,
  recorded_by       INT UNSIGNED NOT NULL,
  complainant_name  VARCHAR(100) NOT NULL,
  complainant_id_no VARCHAR(20)  NULL,
  complainant_phone VARCHAR(16)  NULL,
  narrative         TEXT         NOT NULL,
  status            VARCHAR(30)  NOT NULL DEFAULT 'Open',
  is_amended        TINYINT(1)   NOT NULL DEFAULT 0,
  created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (incident_type_id) REFERENCES incident_types (incident_type_id),
  FOREIGN KEY (location_id)      REFERENCES locations (location_id),
  FOREIGN KEY (recorded_by)      REFERENCES officers (officer_id)
);

CREATE TABLE ob_actions (
  action_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entry_id           INT UNSIGNED NOT NULL,
  action_by          INT UNSIGNED NOT NULL,
  action_datetime    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  action_description TEXT         NOT NULL,
  FOREIGN KEY (entry_id)  REFERENCES ob_entries (entry_id),
  FOREIGN KEY (action_by) REFERENCES officers (officer_id)
);

CREATE TABLE ob_amendments (
  amendment_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entry_id      INT UNSIGNED NOT NULL,
  amended_by    INT UNSIGNED NOT NULL,
  amended_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  field_changed VARCHAR(50)  NOT NULL,
  old_value     TEXT         NULL,
  new_value     TEXT         NULL,
  reason        VARCHAR(255) NOT NULL,
  FOREIGN KEY (entry_id)   REFERENCES ob_entries (entry_id),
  FOREIGN KEY (amended_by) REFERENCES officers (officer_id)
);

-- Supporting tables used by the application but not shown on the ERD.
CREATE TABLE audit_log (
  log_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  officer_id INT UNSIGNED NULL,
  action     VARCHAR(40)  NOT NULL,
  details    VARCHAR(255) NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (officer_id) REFERENCES officers (officer_id) ON DELETE SET NULL
);

CREATE TABLE public_reports (
  report_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference_number  VARCHAR(20)  NOT NULL UNIQUE,
  full_name         VARCHAR(100) NOT NULL,
  id_number         VARCHAR(20)  NULL,
  email             VARCHAR(120) NULL,
  phone             VARCHAR(16)  NOT NULL,
  address           VARCHAR(150) NULL,
  incident_datetime DATETIME     NOT NULL,
  location          VARCHAR(100) NOT NULL,
  incident_type     VARCHAR(60)  NOT NULL,
  description       TEXT         NOT NULL,
  status            ENUM('received','under_review','in_progress','resolved','closed') NOT NULL DEFAULT 'received',
  remarks           VARCHAR(255) NULL,
  ip_address        VARCHAR(45)  NULL,
  created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);