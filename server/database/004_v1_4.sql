-- V1.4: Run once AFTER migrations 001-003, on a backed-up staging DB.
USE buddha_care;
CREATE TABLE IF NOT EXISTS appointment_status_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id BIGINT UNSIGNED NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 previous_status VARCHAR(30) NOT NULL,
 next_status VARCHAR(30) NOT NULL,
 happened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(appointment_id) REFERENCES appointments(id),
 FOREIGN KEY(actor_id) REFERENCES users(id),
 INDEX(appointment_id,happened_at)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS consultation_rooms (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 appointment_id BIGINT UNSIGNED NOT NULL UNIQUE,
 provider VARCHAR(40) NOT NULL DEFAULT 'not_configured',
 provider_room_reference VARCHAR(180) NULL,
 state ENUM('not_configured','ready','ended') NOT NULL DEFAULT 'not_configured',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(appointment_id) REFERENCES appointments(id)
) ENGINE=InnoDB;
