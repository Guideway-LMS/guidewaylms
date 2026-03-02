-- Guideway LMS Database Update
-- Assunto: add_original_filename_to_submissions
-- Data: 2026-03-02 03:28:38

ALTER TABLE #__splms_submissions
ADD COLUMN original_filename VARCHAR(255) NULL AFTER file_path;;
