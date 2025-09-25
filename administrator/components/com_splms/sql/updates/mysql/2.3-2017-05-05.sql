--
-- Updated 2.2.1
--

-- ALTER TABLES;
ALTER TABLE `#__splms_courses` ADD `course_schedules` text NOT NULL AFTER `featured_course`;
ALTER TABLE `#__splms_courses` ADD `course_infos` text NOT NULL AFTER `course_schedules`;
ALTER TABLE `#__splms_courses` ADD `course_time` varchar(255) NOT NULL DEFAULT '' AFTER `course_infos`;

ALTER TABLE `#__splms_events` ADD `topics` text NOT NULL DEFAULT '' AFTER `event_address`;
ALTER TABLE `#__splms_events` ADD `gallery` text NOT NULL DEFAULT '' AFTER `topics`;
ALTER TABLE `#__splms_events` ADD `pricing_tables` text NOT NULL DEFAULT '' AFTER `gallery`;

ALTER TABLE `#__splms_teachers` ADD `designation` varchar(55) NOT NULL DEFAULT '' AFTER `slug`;
