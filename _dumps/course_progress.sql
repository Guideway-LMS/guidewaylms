CREATE TABLE #__splms_course_progress (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL,
  lesson_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Iniciado', 'Concluido', 'Nao_iniciado') NOT NULL DEFAULT 'Nao_iniciado',
  progress DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user_id (user_id),
  KEY idx_lesson_id (lesson_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
