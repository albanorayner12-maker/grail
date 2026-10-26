-- ============================================================
-- GRAIL System database: grail_db
-- Built to match the current PHP code (submit_report, case_portal,
-- dashboard, view, case_manage, notifications, analytics, login).
--
-- HOW TO IMPORT (XAMPP):
--   1. Start Apache and MySQL in the XAMPP Control Panel.
--   2. Open http://localhost/phpmyadmin
--   3. Click the "Import" tab, choose this file, then click "Import".
--
-- WARNING: this file DROPS and re-creates every GRAIL table.
-- That is safe now (no real data yet). Do NOT import it again
-- later once real reports have been saved, or they will be erased.
--
-- Default admin login (use login.php?live=1):
--   username: admin
--   password: admin123   (change it after your first login)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `grail_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `grail_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `case_conflicts`;
DROP TABLE IF EXISTS `case_assignments`;
DROP TABLE IF EXISTS `evidence_files`;
DROP TABLE IF EXISTS `resolution_feedback`;
DROP TABLE IF EXISTS `case_appeals`;
DROP TABLE IF EXISTS `case_notifications`;
DROP TABLE IF EXISTS `case_messages`;
DROP TABLE IF EXISTS `case_events`;
DROP TABLE IF EXISTS `grievance_status_history`;
DROP TABLE IF EXISTS `grievances`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Administrators (login.php, case_manage.php)
-- ------------------------------------------------------------
CREATE TABLE `admins` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(100) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `name`       VARCHAR(100) NOT NULL,
  `role`       VARCHAR(30)  NOT NULL DEFAULT 'case_manager',
  `department` VARCHAR(100) NULL,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`username`, `password`, `name`, `role`, `department`, `is_active`) VALUES
  ('admin', '$2y$10$WaRyUEay48N8EUM49JAUAu5GWbTvB2mQBK.ng8LP8j153JiNoo/bS', 'Derayner', 'super_admin', 'CITE', 1);

-- ------------------------------------------------------------
-- Reports (submit_report.php writes here)
-- ------------------------------------------------------------
CREATE TABLE `grievances` (
  `id`                   INT          NOT NULL AUTO_INCREMENT,
  `tracking_token`       VARCHAR(20)  NOT NULL,
  `case_code_hash`       CHAR(64)     NULL,
  `is_anonymous`         TINYINT(1)   NOT NULL DEFAULT 0,
  `name`                 VARCHAR(255) NULL DEFAULT 'Anonymous',
  `email`                VARCHAR(255) NULL,
  `phone`                VARCHAR(50)  NULL,
  `user_type`            VARCHAR(50)  NULL DEFAULT 'Other',
  `id_number`            VARCHAR(100) NULL,
  `category`             VARCHAR(100) NOT NULL,
  `subject`              VARCHAR(255) NOT NULL,
  `incident_date`        DATE         NULL,
  `location`             VARCHAR(255) NULL,
  `description`          TEXT         NOT NULL,
  `evidence`             VARCHAR(255) NULL,
  `risk_danger`          TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_ongoing`         TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_repeated`        TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_retaliation`     TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_multiple_people` TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_urgent`          TINYINT(1)   NOT NULL DEFAULT 0,
  `risk_score`           INT          NOT NULL DEFAULT 0,
  `priority`             VARCHAR(30)  NOT NULL DEFAULT 'medium',
  `status`               VARCHAR(50)  NOT NULL DEFAULT 'unreviewed',
  `sla_hours`            INT          NOT NULL DEFAULT 168,
  `due_at`               DATETIME     NULL,
  `escalation_level`     TINYINT      NOT NULL DEFAULT 0,
  `assigned_admin_id`    INT          NULL,
  `conflict_status`      VARCHAR(30)  NOT NULL DEFAULT 'none',
  `reopened_at`          DATETIME     NULL,
  `resolved_at`          DATETIME     NULL,
  `created_at`           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grievances_tracking_token` (`tracking_token`),
  UNIQUE KEY `uq_grievances_case_code_hash` (`case_code_hash`),
  KEY `idx_grievances_status` (`status`),
  KEY `idx_grievances_priority` (`priority`),
  KEY `idx_grievances_due_at` (`due_at`),
  KEY `idx_grievances_created_at` (`created_at`),
  KEY `idx_grievances_assigned` (`assigned_admin_id`),
  CONSTRAINT `fk_grievances_admin` FOREIGN KEY (`assigned_admin_id`)
    REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Audited status and priority decisions (dashboard.php)
-- ------------------------------------------------------------
CREATE TABLE `grievance_status_history` (
  `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `grievance_id`           INT          NOT NULL,
  `old_status`             VARCHAR(50)  NOT NULL,
  `new_status`             VARCHAR(50)  NOT NULL,
  `justification`          TEXT         NOT NULL,
  `old_priority`           VARCHAR(30)  NULL,
  `new_priority`           VARCHAR(30)  NULL,
  `priority_justification` TEXT         NULL,
  `changed_by`             VARCHAR(150) NOT NULL,
  `created_at`             TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_grievance_id` (`grievance_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_history_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Case timeline (case_service.php: case_add_event)
-- ------------------------------------------------------------
CREATE TABLE `case_events` (
  `id`               INT          NOT NULL AUTO_INCREMENT,
  `grievance_id`     INT          NOT NULL,
  `event_type`       VARCHAR(50)  NOT NULL,
  `title`            VARCHAR(255) NOT NULL,
  `details`          TEXT         NULL,
  `actor_type`       VARCHAR(20)  NOT NULL,
  `actor_id`         INT          NULL,
  `actor_name`       VARCHAR(150) NOT NULL,
  `reporter_visible` TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_events_case` (`grievance_id`, `reporter_visible`, `created_at`),
  KEY `idx_events_actor` (`grievance_id`, `actor_type`),
  CONSTRAINT `fk_events_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Private reporter inbox (case_portal.php, view.php, case_manage.php)
-- ------------------------------------------------------------
CREATE TABLE `case_messages` (
  `id`                  INT          NOT NULL AUTO_INCREMENT,
  `grievance_id`        INT          NOT NULL,
  `sender_type`         VARCHAR(20)  NOT NULL,
  `sender_id`           INT          NULL,
  `sender_name`         VARCHAR(150) NOT NULL,
  `message`             TEXT         NOT NULL,
  `reporter_visible`    TINYINT(1)   NOT NULL DEFAULT 1,
  `read_by_reporter_at` DATETIME     NULL,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_case` (`grievance_id`, `created_at`),
  CONSTRAINT `fk_messages_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Notifications (notifications.php, case_service.php)
-- ------------------------------------------------------------
CREATE TABLE `case_notifications` (
  `id`             INT          NOT NULL AUTO_INCREMENT,
  `grievance_id`   INT          NOT NULL,
  `recipient_type` VARCHAR(20)  NOT NULL,
  `recipient_id`   INT          NULL,
  `subject`        VARCHAR(255) NOT NULL,
  `body`           TEXT         NOT NULL,
  `status`         VARCHAR(20)  NOT NULL DEFAULT 'queued',
  `read_at`        DATETIME     NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_recipient` (`recipient_type`, `recipient_id`, `created_at`),
  KEY `idx_notifications_case` (`grievance_id`),
  CONSTRAINT `fk_notifications_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Appeals and reopen requests (case_portal.php)
-- ------------------------------------------------------------
CREATE TABLE `case_appeals` (
  `id`           INT         NOT NULL AUTO_INCREMENT,
  `grievance_id` INT         NOT NULL,
  `appeal_type`  VARCHAR(20) NOT NULL,
  `reason`       TEXT        NOT NULL,
  `status`       VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_appeals_case` (`grievance_id`),
  CONSTRAINT `fk_appeals_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Reporter-confirmed outcomes (resolution_verification.php, analytics.php)
-- ------------------------------------------------------------
CREATE TABLE `resolution_feedback` (
  `id`           INT         NOT NULL AUTO_INCREMENT,
  `grievance_id` INT         NOT NULL,
  `outcome`      VARCHAR(30) NOT NULL,
  `comment`      TEXT        NULL,
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_feedback_case` (`grievance_id`),
  KEY `idx_feedback_outcome` (`outcome`),
  CONSTRAINT `fk_feedback_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Uploaded evidence (submit_report.php, view.php, case_portal.php)
-- ------------------------------------------------------------
CREATE TABLE `evidence_files` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `grievance_id`  INT          NOT NULL,
  `stored_name`   VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type`     VARCHAR(100) NOT NULL,
  `file_size`     INT          NOT NULL,
  `sha256`        CHAR(64)     NOT NULL,
  `scan_status`   VARCHAR(20)  NOT NULL DEFAULT 'pending',
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_evidence_case` (`grievance_id`),
  CONSTRAINT `fk_evidence_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Investigator assignment history (case_manage.php)
-- ------------------------------------------------------------
CREATE TABLE `case_assignments` (
  `id`                INT      NOT NULL AUTO_INCREMENT,
  `grievance_id`      INT      NOT NULL,
  `admin_id`          INT      NOT NULL,
  `assigned_by`       INT      NULL,
  `assignment_reason` TEXT     NULL,
  `active`            TINYINT(1) NOT NULL DEFAULT 1,
  `assigned_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at`          DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_assignments_case` (`grievance_id`, `active`),
  CONSTRAINT `fk_assignments_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Conflict-of-interest declarations (case_manage.php)
-- ------------------------------------------------------------
CREATE TABLE `case_conflicts` (
  `id`            INT         NOT NULL AUTO_INCREMENT,
  `grievance_id`  INT         NOT NULL,
  `admin_id`      INT         NULL,
  `conflict_type` VARCHAR(30) NOT NULL,
  `explanation`   TEXT        NOT NULL,
  `created_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conflicts_case` (`grievance_id`),
  CONSTRAINT `fk_conflicts_grievance` FOREIGN KEY (`grievance_id`)
    REFERENCES `grievances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
