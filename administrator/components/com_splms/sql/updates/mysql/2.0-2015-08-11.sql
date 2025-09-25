--
-- Updated 2.0
--
-- Table structure for table `#__splms_certificates`
--

CREATE TABLE IF NOT EXISTS `#__splms_certificates` (
  `splms_certificate_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `splms_coursescategory_id` int(11) DEFAULT NULL,
  `splms_course_id` int(11) DEFAULT NULL,
  `student_image` varchar(255) DEFAULT '',
  `certificate_no` varchar(100) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`splms_certificate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__splms_quizquestions`
--

CREATE TABLE IF NOT EXISTS `#__splms_quizquestions` (
  `splms_quizquestion_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `slug` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `splms_course_id` int(11) DEFAULT NULL,
  `list_answers` text,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` bigint(20) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`splms_quizquestion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__splms_quizresults`
--

CREATE TABLE IF NOT EXISTS `#__splms_quizresults` (
  `splms_quizresult_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `point` int(11) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `user_id` text NOT NULL,
  `splms_quizquestion_id` int(11) DEFAULT NULL,
  `splms_course_id` int(11) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`splms_quizresult_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ALTER TABLES;
-- ALTER TABLE `#__splms_courses` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_coursescategories` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_eventcategories` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_events` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_lessons` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_speakers` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
-- ALTER TABLE `#__splms_teachers` CHANGE `alias` `slug` VARCHAR(50) NOT NULL DEFAULT '';
