-- Guideway LMS Database Update
-- Assunto: add_teacher_id_to_submissions
-- Data: 2026-03-02 03:28:14

ALTER TABLE #__splms_submissions
ADD COLUMN teacher_id INT(11) NOT NULL DEFAULT 0 AFTER user_id;;
