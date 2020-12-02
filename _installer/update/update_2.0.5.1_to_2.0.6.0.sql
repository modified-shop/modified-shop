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

#Hetfield - 2020-12-02 - stock limited true/false for downloads
INSERT INTO configuration (configuration_id, configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES (NULL, 'STOCK_LIMITED_DOWNLOADS', 'false', 9, 4, NULL, NOW(), NULL, 'xtc_cfg_select_option(array(\'true\', \'false\'),');
UPDATE configuration SET sort_order = 5 WHERE configuration_key = 'STOCK_ALLOW_CHECKOUT';
UPDATE configuration SET sort_order = 6 WHERE configuration_key = 'STOCK_MARK_PRODUCT_OUT_OF_STOCK';
UPDATE configuration SET sort_order = 7 WHERE configuration_key = 'STOCK_REORDER_LEVEL';
# Keep an empty line at the end of this file for the db_updater to work properly