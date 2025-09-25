--
-- Alter Table structure for table `#__splms_events`
--

ALTER TABLE `#__splms_events` CHANGE `event_time` `event_time` time NULL DEFAULT NULL;
ALTER TABLE `#__splms_events` CHANGE `event_end_time` `event_end_time` time NULL DEFAULT NULL;
ALTER TABLE `#__splms_events` CHANGE `event_start_date` `event_start_date` date NULL DEFAULT NULL;
ALTER TABLE `#__splms_events` CHANGE `event_end_date` `event_end_date` date NULL DEFAULT NULL;