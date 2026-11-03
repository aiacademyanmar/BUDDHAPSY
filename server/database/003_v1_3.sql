-- V1.3: run after 001 and 002 on a backed-up staging database.
USE buddha_care;
CREATE TABLE IF NOT EXISTS care_consents (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 patient_id BIGINT UNSIGNED NOT NULL,
 appointment_id BIGINT UNSIGNED NOT NULL,
 consent_version VARCHAR(32) NOT NULL,
 permitted TINYINT(1) NOT NULL DEFAULT 0,
 recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(patient_id) REFERENCES users(id),
 FOREIGN KEY(appointment_id) REFERENCES appointments(id),
 UNIQUE KEY uq_patient_appointment(patient_id,appointment_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS clinical_notes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id BIGINT UNSIGNED NOT NULL,
 doctor_user_id BIGINT UNSIGNED NOT NULL,
 note_text TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(appointment_id) REFERENCES appointments(id),
 FOREIGN KEY(doctor_user_id) REFERENCES users(id),
 UNIQUE KEY uq_note_appointment(appointment_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS followups (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id BIGINT UNSIGNED NOT NULL,
 created_by BIGINT UNSIGNED NOT NULL,
 guidance VARCHAR(1000) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(appointment_id) REFERENCES appointments(id),
 FOREIGN KEY(created_by) REFERENCES users(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS login_attempts (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 email_digest CHAR(64) NOT NULL,
 ip_digest CHAR(64) NOT NULL,
 attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_attempts(email_digest,attempted_at),
 INDEX idx_ip(ip_digest,attempted_at)
) ENGINE=InnoDB;
