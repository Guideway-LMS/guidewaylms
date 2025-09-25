--
-- Updated on v4.0.0
--
-- Update table splms_certificates
ALTER TABLE `#__splms_certificates` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_certificates` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_certificates` MODIFY `checked_out_time` DATETIME;

-- Update table splms_courses
ALTER TABLE `#__splms_courses` MODIFY `admission_deadline` DATETIME NOT NULL;
ALTER TABLE `#__splms_courses` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_courses` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_courses` MODIFY `checked_out_time` DATETIME;

-- Update table splms_coursescategories`
ALTER TABLE `#__splms_coursescategories` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_coursescategories` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_coursescategories` MODIFY `checked_out_time` DATETIME;

-- Update table splms_eventcategories
ALTER TABLE `#__splms_eventcategories` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_eventcategories` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_eventcategories` MODIFY `checked_out_time` DATETIME;

-- Update table splms_events
ALTER TABLE `#__splms_events` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_events` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_events` MODIFY `checked_out_time` DATETIME;

-- Update table splms_lessiontopics
ALTER TABLE `#__splms_lessiontopics` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_lessiontopics` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_lessiontopics` MODIFY `checked_out_time` DATETIME;

-- Update table splms_lessons
ALTER TABLE `#__splms_lessons` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_lessons` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_lessons` MODIFY `checked_out_time` DATETIME;

-- Update table splms_orders
ALTER TABLE `#__splms_orders` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_orders` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_orders` MODIFY `checked_out_time` DATETIME;

-- Update table quizquestions
ALTER TABLE `#__splms_quizquestions` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_quizquestions` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_quizquestions` MODIFY `checked_out_time` DATETIME;

-- Update table splms_quizresults
ALTER TABLE `#__splms_quizresults` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_quizresults` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_quizresults` MODIFY `checked_out_time` DATETIME;

-- Update table splms_speakers
ALTER TABLE `#__splms_speakers` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_speakers` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_speakers` MODIFY `checked_out_time` DATETIME;

-- Update table splms_teachers
ALTER TABLE `#__splms_teachers` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_teachers` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_teachers` MODIFY `checked_out_time` DATETIME;

-- Update table splms_useritems
ALTER TABLE `#__splms_useritems` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_useritems` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_useritems` MODIFY `checked_out_time` DATETIME;

-- Update table splms_reviews
ALTER TABLE `#__splms_reviews` MODIFY `created` DATETIME NOT NULL;
ALTER TABLE `#__splms_reviews` MODIFY `modified` DATETIME NOT NULL;
ALTER TABLE `#__splms_reviews` MODIFY `checked_out_time` DATETIME;
