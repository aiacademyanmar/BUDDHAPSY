-- V1.2 migration: BACK UP database before applying; run AFTER 001_foundation.sql.
USE buddha_care;
CREATE TABLE IF NOT EXISTS doctor_slots (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 doctor_id BIGINT UNSIGNED NOT NULL,
 starts_at DATETIME NOT NULL,
 duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 50,
 modality ENUM('online','in_person') NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(doctor_id) REFERENCES doctors(id),
 UNIQUE KEY uq_doctor_start(doctor_id,starts_at),
 CHECK(duration_minutes BETWEEN 15 AND 180)
) ENGINE=InnoDB;
-- Atomic exclusion: cancelled appointments free slots; requested/confirmed/completed block duplicates.
-- MySQL 8.0.13+ generated columns supported; tests recommended with exact server version.
ALTER TABLE appointments
 ADD COLUMN slot_id BIGINT UNSIGNED NULL,
 ADD COLUMN active_doctor_id BIGINT UNSIGNED GENERATED ALWAYS AS (IF(status='cancelled',NULL,doctor_id)) STORED,
 ADD COLUMN active_starts_at DATETIME GENERATED ALWAYS AS (IF(status='cancelled',NULL,starts_at)) STORED,
 ADD UNIQUE KEY uq_active_booking(active_doctor_id,active_starts_at),
 ADD CONSTRAINT fk_appointment_slot FOREIGN KEY (slot_id) REFERENCES doctor_slots(id);
CREATE TABLE IF NOT EXISTS notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 event_type VARCHAR(80) NOT NULL,
 message VARCHAR(255) NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(id), INDEX(user_id,created_at)
) ENGINE=InnoDB;
