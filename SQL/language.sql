USE `deltaEvo`;

DROP TABLE IF EXISTS `delta__language`;
CREATE TABLE IF NOT EXISTS `delta__language` (
  `id_language` INT         NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(3)  NOT NULL,
  `name`        VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id_language`),
  INDEX `idx_code` (`code` ASC),
  INDEX `idx_name` (`name` ASC));


INSERT INTO `delta__language` (`id_language`, `code`, `name`) VALUES (NULL, 'RUS', 'Русский');