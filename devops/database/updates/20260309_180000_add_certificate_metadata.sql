-- Guideway LMS Database Update
-- Assunto: add_certificate_metadata
-- Data: 2026-03-09 18:00:00

 ALTER TABLE bak_lepgs_splms_certificates
  ADD COLUMN completion_date DATETIME NULL AFTER issue_date,
  ADD COLUMN certificate_status VARCHAR(20) NOT NULL DEFAULT 'emitido' AFTER completion_date,
  ADD COLUMN issue_city VARCHAR(100) NULL AFTER certificate_status,
  ADD COLUMN signature_path VARCHAR(255) NULL AFTER issue_city;
