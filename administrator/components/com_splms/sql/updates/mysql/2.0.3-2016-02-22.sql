
--
-- Updated 2.0.4
--

IF NOT EXISTS(SELECT NULL FROM `#__splms_certificates` WHERE COLUMN_NAME = 'issue_date') THEN
	ALTER TABLE `#__splms_certificates` ADD issue_date date DEFAULT NULL;
END IF;

