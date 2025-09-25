-----
----- Update v4.0.1
-----
-- ADD TABLE COLUMNS;
ALTER TABLE `#__splms_speakers` ADD `social_instagram` varchar(50) DEFAULT NULL AFTER `social_linkedin`;
ALTER TABLE `#__splms_teachers` ADD `social_instagram` varchar(50) DEFAULT NULL AFTER `social_linkedin`;

-- CHANGE TABLE COLUMN;
ALTER TABLE `#__splms_courses` MODIFY `duration` varchar(150) DEFAULT NULL;