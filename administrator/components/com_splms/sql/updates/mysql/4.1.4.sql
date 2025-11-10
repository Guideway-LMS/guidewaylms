CREATE TABLE IF NOT EXISTS `#__splms_course_announcements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `course_id` INT(11) NOT NULL COMMENT 'Chave estrangeira para #__splms_courses',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL COMMENT 'O conteúdo principal do aviso (message)',
  `published` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = Não Publicado, 1 = Publicado',
  `access` INT(11) NOT NULL DEFAULT 1 COMMENT 'Nível de acesso do Joomla',
  `created_by` INT(11) NOT NULL COMMENT 'Chave estrangeira para #__users',
  `created_on` DATETIME NOT NULL COMMENT 'Corresponde a created_at',
  `modified_by` INT(11) NULL DEFAULT NULL COMMENT 'Chave estrangeira para #__users',
  `modified_on` DATETIME NULL DEFAULT NULL,
  `ordering` INT(11) NOT NULL DEFAULT 0,
  `params` TEXT NULL COMMENT 'Para armazenar parâmetros JSON extras',
  
  PRIMARY KEY (`id`),
  
  INDEX `idx_course_id` (`course_id`),
  INDEX `idx_published_access` (`published`, `access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;