
--
-- Updated 2.0.4
--

-- ALTER TABLES;
ALTER TABLE `#__splms_certificates` ADD issue_date date DEFAULT NULL;
ALTER TABLE `#__splms_quizquestions` ADD quiz_type tinyint(1) DEFAULT '0';

