-- V1.7.1 additive staging migration, AFTER 001..005. Test restore before applying.
-- Draft policies are INACTIVE by default; no clinical/health-information consent is enabled.
-- A legal/clinical reviewer must publish signed versioned policy documents before activation.
USE buddha_care;
CREATE TABLE IF NOT EXISTS consent_policy_versions (
 purpose ENUM('telehealth','care_share','notifications','research') NOT NULL PRIMARY KEY,
 policy_version VARCHAR(30) NOT NULL,
 summary VARCHAR(300) NOT NULL,
 document_url VARCHAR(500) NULL,
 document_sha256 CHAR(64) NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 0,
 reviewed_by BIGINT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(reviewed_by) REFERENCES users(id),
 CHECK (is_active=0 OR (document_url IS NOT NULL AND document_sha256 IS NOT NULL AND reviewed_by IS NOT NULL AND reviewed_at IS NOT NULL))
) ENGINE=InnoDB;
INSERT IGNORE INTO consent_policy_versions(purpose,policy_version,summary,is_active) VALUES
('telehealth','1.7.1','DRAFT: optional remote-consultation consent. Not approved for patient use.',0),
('care_share','1.7.1','DRAFT: sharing a scoped care summary with assigned care staff. Not approved.',0),
('notifications','1.7.1','DRAFT: optional appointment reminder notifications. Not approved.',0),
('research','1.7.1','DRAFT: separately optional de-identified research use. Not approved.',0);
-- This migration deliberately grants zero patient permissions and processes no real patient data.
