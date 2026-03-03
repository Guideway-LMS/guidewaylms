-- Adiciona a coluna max_attempts à tabela splms_quizquestions
ALTER TABLE `#__splms_quizquestions` ADD COLUMN `max_attempts` INT(11) NOT NULL DEFAULT 1 AFTER `quiz_type`;
