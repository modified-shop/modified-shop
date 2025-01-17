# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2024-12-03 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.3', NOW());

#GTB - 2024-12-03 - add index 
ALTER TABLE `banners_history` DROP INDEX `idx_banners_id`, ADD INDEX `idx_banners_id` (`banners_id`, `banners_history_date`);
UPDATE `banners_history` SET `banners_history_date` = date_format(banners_history_date, '%Y-%m-%d 00:00:00');

#GTB - 2025-01-17 - extend manufacturers
ALTER TABLE `manufacturers` 
    ADD `manufacturers_gender` CHAR(1) NOT NULL AFTER `manufacturers_status`,
    ADD `manufacturers_company` VARCHAR(64) NOT NULL AFTER `manufacturers_gender`,
    ADD `manufacturers_firstname` VARCHAR(64) NOT NULL AFTER `manufacturers_company`,
    ADD `manufacturers_lastname` VARCHAR(64) NOT NULL AFTER `manufacturers_firstname`,
    ADD `manufacturers_street_address` VARCHAR(64) NOT NULL AFTER `manufacturers_lastname`,
    ADD `manufacturers_suburb` VARCHAR(32) NOT NULL AFTER `manufacturers_street_address`,
    ADD `manufacturers_postcode` VARCHAR(10) NOT NULL AFTER `manufacturers_suburb`,
    ADD `manufacturers_city` VARCHAR(64) NOT NULL AFTER `manufacturers_postcode`,
    ADD `manufacturers_state` VARCHAR(64) NOT NULL AFTER `manufacturers_city`,
    ADD `manufacturers_country_id` INT(11) NOT NULL AFTER `manufacturers_state`,
    ADD `manufacturers_zone_id` INT(11) NOT NULL AFTER `manufacturers_country_id`,
    ADD `manufacturers_email_address` VARCHAR(255) NOT NULL AFTER `manufacturers_zone_id`,
    ADD `manufacturers_telephone` VARCHAR(32) NOT NULL AFTER `manufacturers_email_address`;
ALTER TABLE `manufacturers` 
    ADD `responsible_gender` CHAR(1) NOT NULL AFTER `manufacturers_telephone`,
    ADD `responsible_company` VARCHAR(64) NOT NULL AFTER `responsible_gender`,
    ADD `responsible_firstname` VARCHAR(64) NOT NULL AFTER `responsible_company`,
    ADD `responsible_lastname` VARCHAR(64) NOT NULL AFTER `responsible_firstname`,
    ADD `responsible_street_address` VARCHAR(64) NOT NULL AFTER `responsible_lastname`,
    ADD `responsible_suburb` VARCHAR(32) NOT NULL AFTER `responsible_street_address`,
    ADD `responsible_postcode` VARCHAR(10) NOT NULL AFTER `responsible_suburb`,
    ADD `responsible_city` VARCHAR(64) NOT NULL AFTER `responsible_postcode`,
    ADD `responsible_state` VARCHAR(64) NOT NULL AFTER `responsible_city`,
    ADD `responsible_country_id` INT(11) NOT NULL AFTER `responsible_state`,
    ADD `responsible_zone_id` INT(11) NOT NULL AFTER `responsible_state`,
    ADD `responsible_email_address` VARCHAR(255) NOT NULL AFTER `responsible_country_id`,
    ADD `responsible_telephone` VARCHAR(32) NOT NULL AFTER `responsible_email_address`;

# Keep an empty line at the end of this file for the db_updater to work properly