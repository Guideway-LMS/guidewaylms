-- Basic release schema 1.0
CREATE TABLE IF NOT EXISTS `#__gptranslate` (
	 `id` int unsigned NOT NULL AUTO_INCREMENT,
	 `pagelink` VARCHAR( 191 ) NOT NULL,
	 `translations` MEDIUMTEXT NOT NULL,
	 `languageoriginal` CHAR( 20 ) NOT NULL,
	 `languagetranslated` CHAR( 20 ) NOT NULL,
	 `checked_out` int unsigned NULL,
	 `checked_out_time` datetime NULL,
  	 `published` tinyint NOT NULL default '1',
  	 `translate_date` datetime NULL,
  	 `translation_engine` VARCHAR( 20 ) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET `utf8`;