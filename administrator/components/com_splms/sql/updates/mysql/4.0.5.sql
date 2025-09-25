-- v4.0.5

--
-- Alter Table structure for table `#__splms_certificates`
--
UPDATE `#__splms_certificates` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__splms_certificates` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_courses`
--

UPDATE `#__splms_courses` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__splms_courses` CHANGE `coursecategory_id` `coursecategory_id` int NOT NULL;
ALTER TABLE `#__splms_courses` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_coursescategories`
--
UPDATE `#__splms_coursescategories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_coursescategories` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__splms_coursescategories` ADD COLUMN `parent_id` bigint unsigned NOT NULL DEFAULT '0' AFTER `id`;

--
-- Alter Table structure for table `#__splms_eventcategories`
--
UPDATE `#__splms_eventcategories` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_eventcategories` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_events`
--

UPDATE `#__splms_events` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_events` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_lessiontopics`
--

UPDATE `#__splms_lessiontopics` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_lessiontopics` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_lessons`
--
UPDATE `#__splms_lessons` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';

ALTER TABLE `#__splms_lessons` CHANGE `course_id` `course_id` bigint(20) unsigned NOT NULL;
ALTER TABLE `#__splms_lessons` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_orders`
--

UPDATE `#__splms_orders` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_orders` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__splms_orders` CHANGE `id` `id` bigint NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__splms_orders` CHANGE `published` `published` tinyint(1) NOT NULL DEFAULT '0';

--
-- Alter Table structure for table `#__splms_quizquestions`
--

UPDATE `#__splms_quizquestions` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_quizquestions` CHANGE `course_id` `course_id` bigint(20) unsigned NOT NULL;
ALTER TABLE `#__splms_quizquestions` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_quizresults`
--

UPDATE `#__splms_quizresults` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_quizresults` CHANGE `course_id` `course_id` bigint(20) unsigned NOT NULL;
ALTER TABLE `#__splms_quizresults` CHANGE `quizquestion_id` `quizquestion_id` int DEFAULT NULL;
ALTER TABLE `#__splms_quizresults` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_speakers`
--

UPDATE `#__splms_speakers` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_speakers` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_teachers`
--

UPDATE `#__splms_teachers` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_teachers` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;
ALTER TABLE `#__splms_teachers` CHANGE `id` `id` int unsigned NOT NULL AUTO_INCREMENT;

--
-- Alter Table structure for table `#__splms_useritems`
--

UPDATE `#__splms_useritems` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_useritems` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;

--
-- Alter Table structure for table `#__splms_reviews`
--

UPDATE `#__splms_reviews` SET `checked_out_time` = NULL WHERE `checked_out_time` = '0000-00-00 00:00:00';
ALTER TABLE `#__splms_reviews` CHANGE `checked_out_time` `checked_out_time` datetime NULL DEFAULT NULL;