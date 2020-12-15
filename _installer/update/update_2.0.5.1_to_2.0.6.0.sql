# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2020-06-26 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.6.0');

#Tomcraft - 2020-06-26 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_CERTIFIED_SHOPS_MERCHANT_ACTIVE';
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_SHOPPING_ID';
DELETE FROM `configuration` WHERE `configuration_key` = 'GOOGLE_TRUSTED_ID';

#Tomcraft - 2020-08-03 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'MAX_ROW_LISTS_ATTR_VALUES';
DELETE FROM `configuration` WHERE `configuration_key` = 'MAX_ROW_LISTS_ATTR_OPTIONS';

#Tomcraft - 2020-08-03 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'MAX_DISPLAY_STATS_RESULTS';

#GTB - 2020-09-10 - delete obsolete downloads
DELETE pad FROM `products_attributes_download` pad LEFT JOIN `products_attributes` pa ON pad.products_attributes_id = pa.products_attributes_id WHERE pa.products_attributes_id IS NULL;

#Tomcraft - 2020-09-21 - delete obsolete configuration
DELETE FROM `configuration` WHERE `configuration_key` = 'DISPLAY_PRICE_WITH_TAX';

#GTB - 2020-11-11 - extend banners_group to 32 chars
ALTER TABLE `banners` MODIFY `banners_group` VARCHAR(32) NOT NULL;

#GTB - 2020-11-24 - account password security
ALTER TABLE `customers` ADD `customers_password_time` INT(11) DEFAULT 0 NOT NULL;

#GTB - 2020-12-15 - fix #1047 - update banner manager
ALTER TABLE `banners` ADD `banners_group_id` INT(11) NOT NULL AFTER `banners_id`;
ALTER TABLE `banners` ADD `banners_sort` INT(11) NOT NULL AFTER `banners_html_text`;

# Keep an empty line at the end of this file for the db_updater to work properly