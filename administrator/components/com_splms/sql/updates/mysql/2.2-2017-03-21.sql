
--
-- Updated 2.2
--
-- ALTER TABLES;
ALTER TABLE `#__splms_courses` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_courses` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_coursescategories` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_coursescategories` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_eventcategories` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_eventcategories` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_events` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_events` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;
ALTER TABLE `#__splms_events` ADD `buy_url` varchar(255) DEFAULT NULL AFTER `price`;

ALTER TABLE `#__splms_lessons` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_lessons` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_quizquestions` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_quizquestions` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_speakers` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_speakers` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_teachers` ADD `language` char(7) NOT NULL DEFAULT '*' AFTER `enabled`;
ALTER TABLE `#__splms_teachers` ADD `access` int(5) NOT NULL DEFAULT '1' AFTER `enabled`;

ALTER TABLE `#__splms_orders` ADD `payment_note` text AFTER `enabled`;
