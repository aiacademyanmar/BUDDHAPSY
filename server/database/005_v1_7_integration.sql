-- V1.7 staging migration. BACK UP and test in staging before production.
-- Requires MySQL 8.0+, executed after 001..004 in order. Non-destructive additive tables.
USE buddha_care;
CREATE TABLE IF NOT EXISTS learning_courses (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(180) NOT NULL, summary VARCHAR(500) NOT NULL DEFAULT '',
 language_code CHAR(2) NOT NULL DEFAULT 'my', state ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
 clinical_review_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS learning_lessons (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, course_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL, body_text MEDIUMTEXT NULL, sequence_no SMALLINT UNSIGNED NOT NULL,
 state ENUM('draft','published') NOT NULL DEFAULT 'draft',
 FOREIGN KEY(course_id) REFERENCES learning_courses(id), UNIQUE KEY uniq_course_sequence(course_id,sequence_no)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS course_enrollments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 course_id BIGINT UNSIGNED NOT NULL, enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(course_id) REFERENCES learning_courses(id),
 UNIQUE KEY uniq_user_course(user_id,course_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS media_catalog (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL,
 kind ENUM('series_tv','audio_ebook','ebook') NOT NULL, media_uri VARCHAR(500) NULL,
 rights_status ENUM('pending','owned','licensed') NOT NULL DEFAULT 'pending',
 state ENUM('draft','published','archived') NOT NULL DEFAULT 'draft', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CHECK(state <> 'published' OR rights_status <> 'pending')
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS community_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL,
 starts_at DATETIME NOT NULL, event_type ENUM('qa','workshop','meditation') NOT NULL,
 state ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
 capacity INT UNSIGNED NOT NULL DEFAULT 30, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS consent_status (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 purpose ENUM('telehealth','care_share','notifications','research') NOT NULL,
 granted TINYINT(1) NOT NULL DEFAULT 0, document_version VARCHAR(30) NOT NULL,
 changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), UNIQUE KEY uniq_user_purpose(user_id,purpose)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS consent_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 purpose ENUM('telehealth','care_share','notifications','research') NOT NULL,
 granted TINYINT(1) NOT NULL, document_version VARCHAR(30) NOT NULL,
 event_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, origin VARCHAR(30) NOT NULL DEFAULT 'api',
 FOREIGN KEY(user_id) REFERENCES users(id), INDEX ix_user_event(user_id,event_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS educational_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL,
 course_id BIGINT UNSIGNED NOT NULL, score SMALLINT UNSIGNED NOT NULL,
 max_score SMALLINT UNSIGNED NOT NULL, completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(course_id) REFERENCES learning_courses(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS admin_release_drafts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, version_tag VARCHAR(50) NOT NULL,
 notes TEXT NOT NULL, state ENUM('draft','review_requested','rejected') NOT NULL DEFAULT 'draft',
 created_by BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES users(id), UNIQUE KEY uq_release_tag(version_tag)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS security_incident_drafts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 severity ENUM('low','medium','high') NOT NULL DEFAULT 'low',
 category VARCHAR(60) NOT NULL, summary VARCHAR(1000) NOT NULL,
 state ENUM('open','investigating','closed') NOT NULL DEFAULT 'open',
 created_by BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;
-- Clinical notes, medical documents, verified credentials and live video are intentionally
-- NOT enabled through this migration. Those require formal review, access control and testing.
