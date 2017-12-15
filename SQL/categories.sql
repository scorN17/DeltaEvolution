DROP TABLE IF EXISTS `delta__categories_tree`;
CREATE TABLE IF NOT EXISTS `delta__categories_tree` (
  `id_category` INT NOT NULL AUTO_INCREMENT,
  `parent` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_category`),
  INDEX `idx_parent` (`parent` ASC));


ALTER TABLE `delta__categories_tree` 
ADD CONSTRAINT `fk_cat_to_parent`
  FOREIGN KEY (`parent`)
  REFERENCES `deltaEvo`.`delta__categories_tree` (`id_category`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;



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
ALTER TABLE `deltaEvo`.`delta__categories_description` 
ADD CONSTRAINT `fk_id_cat_descr_to_main`
  FOREIGN KEY (`id_category`)
  REFERENCES `deltaEvo`.`delta__categories_tree` (`id_category`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION,
ADD CONSTRAINT `fk_id_lang_descr_to_main`
  FOREIGN KEY (`id_language`)
  REFERENCES `deltaEvo`.`delta__language` (`id_language`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;
