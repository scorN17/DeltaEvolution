DROP TABLE IF EXISTS `delta__users`;
CREATE TABLE `delta__users` (
  `id_user` INT NOT NULL AUTO_INCREMENT,
  `passw` VARCHAR(32) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(80) NOT NULL,
  `lastvivsit` TIMESTAMP NULL,
  `datereg` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
  `firstname` VARCHAR(45) NULL,
  `lastname` VARCHAR(45) NULL,
  `middlename` VARCHAR(45) NULL,
  `visitcount` INT NOT NULL DEFAULT 0,
  `blocked` INT NOT NULL DEFAULT 0,
  `confirm` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_user`),
  UNIQUE INDEX `idx_mail` (`email` ASC),
  INDEX `idx_passw` (`passw` ASC))
ENGINE=InnoDB DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `delta__users_address`;
CREATE TABLE `delta__users_address` (
  `id_users_address` INT NOT NULL AUTO_INCREMENT,
  `id_user` INT NOT NULL,
  `id_city` INT NULL,
  `id_country` INT NULL,
  `street` VARCHAR(60) NULL,
  `postcode` VARCHAR(12) NULL,
  `house` VARCHAR(15) NULL,
  `entrance` VARCHAR(10) NULL,
  `apartment` VARCHAR(10) NULL,
  PRIMARY KEY (`id_users_address`),
  INDEX `user_address_to_user_idx` (`id_user` ASC),
  CONSTRAINT `user_address_to_user`
    FOREIGN KEY (`id_user`)
    REFERENCES `delta__users` (`id_user`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE=InnoDB DEFAULT CHARSET=utf8;





CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `root`@`%` 
    SQL SECURITY DEFINER
VIEW `v_user_and_address` AS
    SELECT 
        `u`.`id_user` AS `id_user`,
        `ua`.`id_users_address` AS `id_users_address`,
        `u`.`phone` AS `phone`,
        `u`.`email` AS `email`,
        `u`.`firstname` AS `firstname`,
        `u`.`lastname` AS `lastname`,
        `u`.`middlename` AS `middlename`,
        `ua`.`street` AS `street`,
        `ua`.`postcode` AS `postcode`,
        `ua`.`house` AS `house`,
        `ua`.`entrance` AS `entrance`,
        `ua`.`apartment` AS `apartment`
    FROM
        (`delta__users` `u`
        JOIN `delta__users_address` `ua` ON ((`u`.`id_user` = `ua`.`id_user`)));
