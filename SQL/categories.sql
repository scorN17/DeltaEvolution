DROP TABLE IF EXISTS `delta__categories_tree`;
CREATE TABLE IF NOT EXISTS `delta__categories_tree` (
  `id_category` INT NOT NULL AUTO_INCREMENT,
  `parent` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_category`),
  INDEX `idx_parent` (`parent` ASC));





DROP TABLE IF EXISTS `delta__categories_props`;
CREATE TABLE IF NOT EXISTS `delta__categories_props` (
  `id_category` INT NOT NULL,
  `alias` VARCHAR(30) NOT NULL,
  `cover` VARCHAR(200) NULL,
  `visible` SET('0', '1') NOT NULL DEFAULT '1',
  `searcable` SET('0', '1') NOT NULL DEFAULT '1',
  `visible_child` SET('0', '1') NOT NULL DEFAULT '1',
  `position` INT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NULL,
  `counter` VARCHAR(45) NULL DEFAULT 0,
  INDEX `fk_id_cat_to_main_idx` (`id_category` ASC),
  UNIQUE INDEX `position_UNIQUE` (`position` ASC),
  CONSTRAINT `fk_id_cat_to_main`
	FOREIGN KEY (`id_category`)
	REFERENCES `deltaEvo`.`delta__categories_tree` (`id_category`)
	ON DELETE CASCADE
	ON UPDATE NO ACTION);




DROP TABLE IF EXISTS `delta__categories_description`;
CREATE TABLE IF NOT EXISTS `delta__categories_description` (
  `id_category` int(11) NOT NULL,
  `id_language` int(11) NOT NULL DEFAULT '1',
  `name` varchar(150) NOT NULL,
  `descriptin` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `delta__categories_description`
  ADD KEY `fk_id_to_main_idx` (`id_category`),
  ADD KEY `fk_id_lang_descr_to_main_idx` (`id_language`);
  

ALTER TABLE `delta__categories_description` 
ADD INDEX `fk_id_to_main_idx` (`id_category` ASC),
ADD INDEX `fk_id_lang_descr_to_main_idx` (`id_language` ASC);
ALTER TABLE `delta__categories_description` 
ADD CONSTRAINT `fk_id_cat_descr_to_main`
  FOREIGN KEY (`id_category`)
  REFERENCES `delta__categories_tree` (`id_category`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_id_lang_descr_to_main`
  FOREIGN KEY (`id_language`)
  REFERENCES `delta__language` (`id_language`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;







CREATE 
	OR REPLACE ALGORITHM = UNDEFINED 
	DEFINER = `root`@`%` 
	SQL SECURITY DEFINER
VIEW `v_categories` AS
	SELECT 
		`ct`.`id_category` AS `id_category`,
		`ct`.`parent` AS `parent`,
		`cp`.`alias` AS `alias`,
		`cp`.`cover` AS `cover`,
		`cp`.`visible` AS `visible`,
		`cp`.`searcable` AS `searcable`,
		`cp`.`visible_child` AS `visible_child`,
		`cp`.`created` AS `created`,
		`cp`.`counter` AS `counter`,
		`cd`.`name` AS `name`,
		`cd`.`description` AS `description`
	FROM
		(`delta__categories_tree` `ct`
		  JOIN `delta__categories_props` `cp` ON ((`ct`.`id_category` = `cp`.`id_category`))
		  JOIN `delta__categories_description` `cd` ON ((`ct`.`id_category` = `cd`.`id_category`))
		);

