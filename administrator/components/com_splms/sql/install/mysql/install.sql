/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

-- Create syntax for TABLE '#__splms_certificates'
CREATE TABLE IF NOT EXISTS `#__splms_certificates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `userid` int DEFAULT NULL,
  `coursescategory_id` int DEFAULT NULL,
  `course_id` int DEFAULT NULL,
  `student_image` varchar(255) DEFAULT '',
  `certificate_no` varchar(100) DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_courses'
CREATE TABLE IF NOT EXISTS `#__splms_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `course_sub_title` varchar(55) NOT NULL DEFAULT '',
  `coursecategory_id` int NOT NULL,
  `description` text NOT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `level` varchar(55) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `video_url` varchar(255) NOT NULL,
  `ref_url` varchar(255) NOT NULL,
  `featured_course` tinyint(1) NOT NULL DEFAULT '0',
  `course_schedules` text NOT NULL,
  `course_infos` text NOT NULL,
  `course_time` varchar(255) NOT NULL DEFAULT '',
  `price` float(8,2) NOT NULL DEFAULT '0.00',
  `sale_price` float(8,2) DEFAULT NULL,
  `duration` varchar(150) DEFAULT NULL,
  `download` int NOT NULL DEFAULT '0',
  `admission_deadline` datetime NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `metakey` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadesc` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_coursescategories'
CREATE TABLE IF NOT EXISTS `#__splms_coursescategories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned NOT NULL DEFAULT '0',
  `asset_id` int NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(1) NOT NULL,
  `show` tinyint(1) NOT NULL,
  `icon` varchar(55) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_eventcategories'
CREATE TABLE IF NOT EXISTS `#__splms_eventcategories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_events'
CREATE TABLE IF NOT EXISTS `#__splms_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(255) DEFAULT '',
  `price` int DEFAULT NULL,
  `buy_url` varchar(255) DEFAULT NULL,
  `speaker_id` varchar(50) DEFAULT '',
  `eventcategory_id` bigint(20) NOT NULL,
  `event_start_date` date NULL DEFAULT NULL,
  `event_end_time` time NULL DEFAULT NULL,
  `event_time` time NULL DEFAULT NULL,
  `event_end_date` date NULL DEFAULT NULL,
  `event_address` text,
  `topics` text NOT NULL,
  `gallery` text NOT NULL,
  `pricing_tables` text NOT NULL,
  `published` tinyint(1) NOT NULL,
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  `map` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Create syntax for TABLE '#__splms_lessiontopics'
CREATE TABLE IF NOT EXISTS `#__splms_lessiontopics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `course_id` bigint(20) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  `language` char(7) NOT NULL DEFAULT '*',
  `access` int DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_lessons'
CREATE TABLE IF NOT EXISTS `#__splms_lessons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `short_description` varchar(255) DEFAULT '',
  `course_id` bigint(20) unsigned NOT NULL,
  `teacher_id` int NOT NULL,
  `topic_id` int DEFAULT NULL,
  `video_url` varchar(255) DEFAULT '',
  `vdo_thumb` text NOT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `video_duration` varchar(50) DEFAULT '',
  `attachment` varchar(255) DEFAULT '',
  `lesson_type` tinyint(1) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_orders'
CREATE TABLE IF NOT EXISTS `#__splms_orders` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_user_id` bigint(20) NOT NULL,
  `course_id` bigint(20) NOT NULL,
  `order_price` float(17,2) NOT NULL,
  `order_payment_id` varchar(255) NOT NULL,
  `invoice_id` varchar(255) NOT NULL,
  `order_payment_method` varchar(255) NOT NULL DEFAULT '',
  `order_payment_price` float NOT NULL,
  `order_duration` varchar(55) NOT NULL DEFAULT '',
  `ordering` int NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `payment_note` text,
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  `order_discount_code` varchar(255) DEFAULT '',
  `order_discount_price` decimal(17,5) DEFAULT NULL,
  `order_discount_tax` decimal(17,5) DEFAULT NULL,
  `order_payment_currency` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_quizquestions'
CREATE TABLE IF NOT EXISTS `#__splms_quizquestions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `description` text NOT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `list_answers` text,
  `quiz_type` tinyint(1) DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_quizresults'
CREATE TABLE IF NOT EXISTS `#__splms_quizresults` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `point` int DEFAULT NULL,
  `total_marks` int DEFAULT NULL,
  `user_id` text NOT NULL,
  `quizquestion_id` int DEFAULT NULL,
  `course_id` bigint(20) unsigned NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_speakers'
CREATE TABLE IF NOT EXISTS `#__splms_speakers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `designation` varchar(50) DEFAULT '',
  `description` text,
  `image` varchar(255) DEFAULT '',
  `website` varchar(50) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `social_facebook` varchar(50) DEFAULT '',
  `social_twitter` varchar(50) DEFAULT '',
  `social_youtube` varchar(50) DEFAULT '',
  `social_linkedin` varchar(50) DEFAULT NULL,
  `social_instagram` varchar(50) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_teachers'
CREATE TABLE IF NOT EXISTS `#__splms_teachers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `designation` varchar(55) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT '',
  `website` varchar(50) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `experience` varchar(255) NOT NULL,
  `education` TEXT,
  `specialist_in` text NOT NULL,
  `social_facebook` varchar(100) DEFAULT '',
  `social_twitter` varchar(100) DEFAULT '',
  `social_youtube` varchar(100) DEFAULT '',
  `social_linkedin` varchar(100) DEFAULT '',
  `social_instagram` varchar(50) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `access` int NOT NULL DEFAULT '1',
  `language` char(7) NOT NULL DEFAULT '*',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_useritems'
CREATE TABLE IF NOT EXISTS `#__splms_useritems` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int DEFAULT '0',
  `user_id` int DEFAULT NULL,
  `item_type` varchar(55) DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create syntax for TABLE '#__splms_reviews'
CREATE TABLE IF NOT EXISTS `#__splms_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int DEFAULT NULL,
  `rating` tinyint(2) DEFAULT '0',
  `review` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `ordering` int NOT NULL DEFAULT '0',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL,
  `checked_out` bigint(20) NOT NULL DEFAULT '0',
  `checked_out_time` datetime NULL DEFAULT NULL,
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

-- Create syntax for TABLE '#__splms_forum_questions'
CREATE TABLE IF NOT EXISTS `#__splms_forum_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `solved` tinyint(1) NOT NULL DEFAULT 0,
  `tags` text DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE '#__splms_forum_answers'
CREATE TABLE IF NOT EXISTS `#__splms_forum_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `votes` int(11) NOT NULL DEFAULT 0,
  `is_accepted` tinyint(1) NOT NULL DEFAULT 0,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Create syntax for TABLE '#__splms_forum_votes'
CREATE TABLE IF NOT EXISTS `#__splms_forum_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_type` varchar(20) NOT NULL,
  `vote` tinyint(2) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_vote` (`user_id`, `item_id`, `item_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;