-- Guideway LMS Database Update
-- Assunto: remove_test_manual_script
-- Data: 2026-02-25 05:00:40

ALTER TABLE `#__splms_lessons` DROP COLUMN `test_manual_script`
;
