--
-- Updated on v3.0
--

-- CHANGE CERTIFICATES TABLE COLUMNS;
ALTER TABLE `#__splms_certificates` CHANGE `splms_certificate_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_certificates` CHANGE `splms_coursescategory_id` `coursescategory_id` int(11) DEFAULT NULL;
ALTER TABLE `#__splms_certificates` CHANGE `splms_course_id` `course_id` int(11) DEFAULT NULL;

ALTER TABLE `#__splms_certificates` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_certificates` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_certificates` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_certificates` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_certificates` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE COURSES TABLE COLUMNS;
ALTER TABLE `#__splms_courses` CHANGE `splms_course_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_courses` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__splms_courses` CHANGE `splms_coursescategory_id` `coursecategory_id` int(11) NOT NULL;
ALTER TABLE `#__splms_courses` CHANGE `short_description` `short_description` varchar(255);
ALTER TABLE `#__splms_courses` CHANGE `course_time` `course_time` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__splms_courses` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_courses` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_courses` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_courses` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_courses` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE COURSE CATEGORIES TABLE COLUMNS;
ALTER TABLE `#__splms_coursescategories` CHANGE `splms_coursescategory_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_coursescategories` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__splms_coursescategories` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_coursescategories` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_coursescategories` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_coursescategories` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_coursescategories` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE EVENT CATEGORIES TABLE COLUMNS;
ALTER TABLE `#__splms_eventcategories` CHANGE `splms_eventcategory_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_eventcategories` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__splms_eventcategories` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_eventcategories` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_eventcategories` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_eventcategories` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_eventcategories` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE EVENTS TABLE COLUMNS;
ALTER TABLE `#__splms_events` CHANGE `splms_event_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_events` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__splms_events` CHANGE `splms_speaker_id` `speaker_id` varchar(50) DEFAULT '';
ALTER TABLE `#__splms_events` CHANGE `splms_eventcategory_id` `eventcategory_id` bigint(20) NOT NULL;

ALTER TABLE `#__splms_events` CHANGE `enabled` `published` tinyint(1) NOT NULL;
ALTER TABLE `#__splms_events` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_events` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_events` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_events` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE LESSONS TABLE COLUMNS;
ALTER TABLE `#__splms_lessons` CHANGE `splms_lesson_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_lessons` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__splms_lessons` CHANGE `splms_teacher_id` `teacher_id` int(11) NOT NULL;
ALTER TABLE `#__splms_lessons` CHANGE `splms_course_id` `course_id` bigint(20) unsigned NOT NULL;

ALTER TABLE `#__splms_lessons` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_lessons` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_lessons` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_lessons` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_lessons` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE ORDERS TABLE COLUMNS;
ALTER TABLE `#__splms_orders` CHANGE `splms_order_id` `id` bigint(20) NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_orders` CHANGE `splms_course_id` `course_id` bigint(20) NOT NULL;

ALTER TABLE `#__splms_orders` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_orders` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_orders` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_orders` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_orders` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE QUIZ QUESTIONS TABLE COLUMNS;
ALTER TABLE `#__splms_quizquestions` CHANGE `splms_quizquestion_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_quizquestions` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';
ALTER TABLE `#__splms_quizquestions` CHANGE `splms_course_id` `course_id` bigint(20) unsigned NOT NULL;

ALTER TABLE `#__splms_quizquestions` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_quizquestions` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_quizquestions` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_quizquestions` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_quizquestions` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE QUIZ QUIZ RESULTS COLUMNS;
ALTER TABLE `#__splms_quizresults` CHANGE `splms_quizresult_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_quizresults` CHANGE `splms_quizquestion_id` `quizquestion_id` int DEFAULT NULL;
ALTER TABLE `#__splms_quizresults` CHANGE `splms_course_id` `course_id` bigint(20) unsigned NOT NULL;

ALTER TABLE `#__splms_quizresults` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';

-- CHANGE SPEAKERS TABLE COLUMNS;
ALTER TABLE `#__splms_speakers` CHANGE `splms_speaker_id` `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_speakers` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__splms_speakers` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_speakers` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_speakers` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_speakers` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_speakers` CHANGE `locked_on` `checked_out_time` datetime;

-- CHANGE TEACHERS TABLE COLUMNS;
ALTER TABLE `#__splms_teachers` CHANGE `splms_teacher_id` `id` int unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_teachers` CHANGE `slug` `alias` varchar(255) NOT NULL DEFAULT '';

ALTER TABLE `#__splms_teachers` CHANGE `enabled` `published` tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE `#__splms_teachers` CHANGE `created_on` `created` datetime NOT NULL;
ALTER TABLE `#__splms_teachers` CHANGE `modified_on` `modified` datetime NOT NULL;
ALTER TABLE `#__splms_teachers` CHANGE `locked_by` `checked_out` bigint(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__splms_teachers` CHANGE `locked_on` `checked_out_time` datetime;

-- ADD QUIZRESULT TABLE COLUMNS;
ALTER TABLE `#__splms_quizresults` ADD `created_by` bigint(20) NOT NULL DEFAULT '0' AFTER `ordering`;
ALTER TABLE `#__splms_quizresults` ADD `created` datetime NOT NULL AFTER `created_by`;
ALTER TABLE `#__splms_quizresults` ADD `modified_by` bigint(20) NOT NULL DEFAULT '0' AFTER `created`;
ALTER TABLE `#__splms_quizresults` ADD `modified` datetime NOT NULL AFTER `modified_by`;
ALTER TABLE `#__splms_quizresults` ADD `checked_out` bigint(20) NOT NULL DEFAULT '0' AFTER `modified`;
ALTER TABLE `#__splms_quizresults` ADD `checked_out_time` datetime AFTER `checked_out`;

-- ADD COURSES TABLE COLUMNS;
ALTER TABLE `#__splms_courses` ADD `level` varchar(55) NOT NULL DEFAULT '' AFTER `short_description`;
ALTER TABLE `#__splms_courses` ADD `sale_price` float(8,2) DEFAULT NULL AFTER `price`;

-- Create syntax for TABLE '#__splms_useritems';
CREATE TABLE IF NOT EXISTS `#__splms_useritems` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `item_type` varchar(55) DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_reviews'
CREATE TABLE IF NOT EXISTS `#__splms_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `rating` tinyint(2) DEFAULT '0',
  `review` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;