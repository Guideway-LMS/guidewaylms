-- Guideway LMS Database Update
-- Assunto: curso: data de inicio, fim e quantidade de horas adicionadas
-- Data: 2026-03-14 15:50:37

ALTER TABLE #__splms_courses
ADD COLUMN start_date DATE NULL,
ADD COLUMN end_date DATE NULL,
ADD COLUMN workload_hours INT NOT NULL DEFAULT 360;;
