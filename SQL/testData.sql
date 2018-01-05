/*!40101 SET NAMES utf8 */;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'TRADITIONAL,ALLOW_INVALID_DATES';
SET NAMES utf8;



INSERT INTO `deltaEvo`.`delta__language` (`id_language`, `code`, `name`) VALUES (NULL, 'RUS', 'Русский');

INSERT INTO `deltaEvo`.`delta__product` (`id_product`, `alias`) VALUES (NULL, 'testcable');

INSERT INTO `deltaEvo`.`delta__product_description` (`id_product`, `name`, `intro`, `id_language`) VALUES ('1', 'Кабель D-SUB', 'Позолоченные контакты', '1');

INSERT INTO `deltaEvo`.`delta__product_options` (`id_product`, `visible`, `searchable`, `deleted`, `state_stock`, `counter_visible`, `create_date`, `update_date`) VALUES ('1', '1', '1', '0', '1', '0', NULL, NULL);

INSERT INTO `deltaEvo`.`delta__vendors` (`id_vendor`, `name`) VALUES (NULL, 'Philips');

INSERT INTO `deltaEvo`.`delta__vendor_to_product` (`id_vendor`, `id_product`) VALUES ('1', '1');

INSERT INTO `deltaEvo`.`delta__product_to_directory` (`id_product`, `id_sc`) VALUES ('1', '7');

INSERT INTO `deltaEvo`.`delta__language` (`id_language`, `code`, `name`) VALUES (NULL, 'ENG', 'English');

INSERT INTO `deltaEvo`.`delta__product_description` (`id_product`, `name`, `intro`, `id_language`) VALUES ('1', 'd-sub cable', 'gold', '2');

SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
