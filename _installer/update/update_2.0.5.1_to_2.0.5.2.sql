# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2020-06-26 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.5.2');

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

# Keep an empty line at the end of this file for the db_updater to work properly