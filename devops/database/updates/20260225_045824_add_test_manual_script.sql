-- Guideway LMS Database Update
-- Assunto: add_test_manual_script
-- Data: 2026-02-25 04:58:24

ALTER TABLE `#__splms_lessons` ADD COLUMN `test_manual_script` VARCHAR(50) DEFAULT NULL
;
