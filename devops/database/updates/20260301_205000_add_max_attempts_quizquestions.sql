-- Guideway LMS Database Update
-- Assunto: add_max_attempts_quizquestions
-- Data: 2026-03-01 20:50:00

ALTER TABLE `#__splms_quizquestions` ADD COLUMN `max_attempts` INT(11) NOT NULL DEFAULT 1 AFTER `quiz_type`;
