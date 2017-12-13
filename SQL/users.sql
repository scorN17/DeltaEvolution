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
  INDEX `idx_passw` (`passw` ASC));



CREATE TABLE `delta_users_address` (
  `id_users_address` INT NOT NULL AUTO_INCREMENT,
  `id_users` INT NOT NULL,
  `id_cityes` INT NULL,
  `id_country` INT NULL,
  `street` VARCHAR(60) NULL,
  `postcode` VARCHAR(12) NULL,
  `house` VARCHAR(15) NULL,
  `entrance` VARCHAR(10) NULL,
  `apartment` VARCHAR(10) NULL,
  PRIMARY KEY (`id_users_address`),
  INDEX `user_address_to_user_idx` (`id_users` ASC),
  CONSTRAINT `user_address_to_user`
    FOREIGN KEY (`id_users`)
    REFERENCES `delta__users` (`id_user`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);
