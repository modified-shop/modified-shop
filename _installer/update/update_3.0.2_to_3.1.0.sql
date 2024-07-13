# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2024-05-29 - add date_added for database_version
ALTER TABLE `database_version` ADD `date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

#Tomcraft - 2024-03-19 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.0', NOW());

#GTB - 2024-03-19 - remove paypal sofort
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_STATUS';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_ALLOWED';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_ZONE';
DELETE FROM `configuration` WHERE `configuration_key` = 'MODULE_PAYMENT_PAYPALSOFORT_SORT_ORDER';

#GTB - 2024-04-26 - add index 
ALTER TABLE `customers` ADD INDEX `idx_customers_date_added` (`customers_date_added`); 

#GTB - 2024-06-28 - extend banners_title to 255 chars
ALTER TABLE `banners` MODIFY `banners_title` VARCHAR(255) NOT NULL;

#GTB - 2024-06-28 - change tracking link
UPDATE `carriers` SET `carrier_tracking_link` = 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1' WHERE `carrier_tracking_link` = 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1';
UPDATE `carriers` SET `carrier_tracking_link` = 'https://www.fedex.com/fedextrack/?trknbr=$1&cntry_code=$2' WHERE `carrier_tracking_link` = 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1';

#GTB - 2024-07-02 - extend coupons
ALTER TABLE `coupons` ADD `coupon_specials` INT(1) NOT NULL DEFAULT 0 AFTER `restrict_to_customers`; 
ALTER TABLE `coupons` ADD `restrict_to_manufacturers` TEXT DEFAULT NULL AFTER `restrict_to_categories`; 
ALTER TABLE `coupons` MODIFY `restrict_to_customers` TEXT DEFAULT NULL;

ALTER TABLE `admin_access` ADD `listmanufacturers` INT(1) NOT NULL DEFAULT 0 AFTER `listcategories`;
ALTER TABLE `admin_access` ADD `validmanufacturers` INT(1) NOT NULL DEFAULT 0 AFTER `validcategories`;

UPDATE `admin_access` SET `listmanufacturers` = 1 WHERE `customers_id` = 1 LIMIT 1;
UPDATE `admin_access` SET `validmanufacturers` = 1 WHERE `customers_id` = 1 LIMIT 1;

UPDATE `admin_access` SET `listmanufacturers` = 6 WHERE `customers_id` = 'groups' LIMIT 1;
UPDATE `admin_access` SET `validmanufacturers` = 6 WHERE `customers_id` = 'groups' LIMIT 1;

#GTB - 2024-07-03 - extend manufacturers/categories
ALTER TABLE `manufacturers_info` ADD `manufacturers_short_description` TEXT AFTER `manufacturers_description`; 
ALTER TABLE `manufacturers_info` ADD `manufacturers_add_description` TEXT AFTER `manufacturers_short_description`; 
ALTER TABLE `categories_description` ADD `categories_short_description` TEXT AFTER `categories_description`; 
ALTER TABLE `categories_description` MODIFY `categories_description` TEXT;


# Keep an empty line at the end of this file for the db_updater to work properly