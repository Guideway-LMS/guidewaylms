--
-- Updated on v3.4
--

-- ADD TABLE COLUMNS;
ALTER TABLE `#__splms_courses` ADD `metakey` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `ordering`, ADD `metadesc` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL AFTER `metakey`;
ALTER TABLE `#__splms_courses` CHANGE `admission_deadline` `admission_deadline` datetime NOT NULL;
ALTER TABLE `#__splms_lessons` ADD `topic_id` INT(11) DEFAULT 0 AFTER `course_id`;
ALTER TABLE `#__splms_teachers` ADD `education` TEXT AFTER `experience`;
ALTER TABLE `#__splms_teachers` CHANGE `social_gplus` `social_youtube` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';


-- Create syntax for TABLE '#__splms_lessiontopics' if not exist
CREATE TABLE IF NOT EXISTS `#__splms_lessiontopics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `course_id` bigint(20) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `language` char(7) NOT NULL DEFAULT '*',
  `access` int(5) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Followers
CREATE TABLE IF NOT EXISTS `#__splms_followers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `teacher` bigint(20) NOT NULL,
  `student` bigint(20) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `teacher` (`teacher`),
  KEY `student` (`student`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;