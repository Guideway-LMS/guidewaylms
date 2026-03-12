-- Add missing columns to #__splms_courses table
ALTER TABLE `#__splms_courses` 
ADD COLUMN `start_date` DATETIME NULL DEFAULT NULL AFTER `admission_deadline`,
ADD COLUMN `end_date` DATETIME NULL DEFAULT NULL AFTER `start_date`,
ADD COLUMN `workload_hours` INT DEFAULT 0 AFTER `end_date`;
