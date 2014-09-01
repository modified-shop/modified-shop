ALTER TABLE `campaigns` CHANGE `campaigns_refid` `campaigns_refID` VARCHAR( 64 ) NULL DEFAULT NULL;
ALTER TABLE `products_xsell` CHANGE `id` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `campaigns_ip` CHANGE `TIME` `time` DATETIME NOT NULL;
DELETE FROM `configuration` WHERE `configuration_key` = 'haendlerbund_rueckgabe';
INSERT INTO `configuration` ( `configuration_key` ) VALUES  ( 'haendlerbund_rueckgabe' );
DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_DEALERS';
DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_IGNORE_GROUPE';
DELETE FROM `configuration` WHERE `configuration_key` = 'SEARCH_HIGHLIGHT';
DELETE FROM `configuration` WHERE `configuration_key` = 'SEARCH_HIGHLIGHT_STYLE';