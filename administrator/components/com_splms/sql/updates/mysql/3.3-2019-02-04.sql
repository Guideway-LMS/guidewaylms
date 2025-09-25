--
-- Updated on v3.3
--

-- CHANGE TABLE COLUMNS;
ALTER TABLE `#__splms_courses` MODIFY `duration` varchar(150) DEFAULT NULL;
-- ADD TABLE COLUMNS;
ALTER TABLE `#__splms_courses` ADD `admission_deadline` varchar(150) NOT NULL DEFAULT '' AFTER `download`;