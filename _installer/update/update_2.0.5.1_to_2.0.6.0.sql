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
ALTER TABLE `customers` ADD `customers_password_time` INT(11) DEFAULT 0 NOT NULL AFTER `customers_password`;

#GTB - 2020-12-15 - fix #1047 - update banner manager
ALTER TABLE `banners` ADD `banners_group_id` INT(11) NOT NULL AFTER `banners_id`;
ALTER TABLE `banners` ADD `banners_sort` INT(11) NOT NULL AFTER `banners_html_text`;
UPDATE `banners` SET `banners_group_id` = `banners_id`;

#GTB - 2021-01-16 - multilanguage for tax rates and tax classes
ALTER TABLE `geo_zones` MODIFY `geo_zone_name` VARCHAR(255) NOT NULL;
UPDATE `geo_zones` SET `geo_zone_name` = 'DE::Steuerzone EU||EN::Tax zone EU' WHERE `geo_zone_name` = 'Steuerzone EU';
UPDATE `geo_zones` SET `geo_zone_name` = 'DE::Steuerzone Nicht-EU-Ausland||EN::Tax zone for non-EU countries' WHERE `geo_zone_name` = 'Steuerzone Nicht-EU-Ausland';
UPDATE `geo_zones` SET `geo_zone_name` = 'DE::Steuerzone B2B||EN::Tax zone B2B' WHERE `geo_zone_name` = 'Steuerzone B2B';
UPDATE `geo_zones` SET `geo_zone_name` = 'DE::Steuerzone CH/LI||EN::Tax zone CH/LI' WHERE `geo_zone_name` = 'Steuerzone CH/LI';
UPDATE `geo_zones` SET `geo_zone_name` = 'DE::Steuerzone DE||EN::Tax zone DE' WHERE `geo_zone_name` = 'Steuerzone DE';
UPDATE `geo_zones` SET `geo_zone_description` = 'DE::Steuerzone für die EU||EN::Tax zone for the EU' WHERE `geo_zone_description` = 'Steuerzone für die EU';
UPDATE `geo_zones` SET `geo_zone_description` = 'DE::Steuerzone für Nicht-EU-Ausland||EN::Tax zone for non-EU countries' WHERE `geo_zone_description` = 'Steuerzone für Nicht-EU-Ausland';
UPDATE `geo_zones` SET `geo_zone_description` = 'DE::Steuerzone für B2B||EN::Tax zone for B2B' WHERE `geo_zone_description` = 'Steuerzone für B2B';
UPDATE `geo_zones` SET `geo_zone_description` = 'DE::Steuerzone für CH/LI||EN::Tax zone for CH/LI' WHERE `geo_zone_description` = 'Steuerzone für CH/LI';
UPDATE `geo_zones` SET `geo_zone_description` = 'DE::Steuerzone für DE||EN::Tax zone for DE' WHERE `geo_zone_description` = 'Steuerzone für DE';
ALTER TABLE `tax_class` MODIFY `tax_class_title` VARCHAR(255) NOT NULL;
UPDATE `tax_class` SET `tax_class_title` = 'DE::Standardsatz||EN::Standard rate' WHERE `tax_class_title` = 'Standardsatz';
UPDATE `tax_class` SET `tax_class_title` = 'DE::ermäßigter Steuersatz||EN::reduced tax rate' WHERE `tax_class_title` = 'ermäßigter Steuersatz';

#GTB - 2021-01-20 - banners_redirect
ALTER TABLE `banners` ADD `banners_redirect` INT(11) NOT NULL DEFAULT '1' AFTER `banners_url`; 

#GTB - 2021-01-21 - add primary key
ALTER TABLE `newsletter_recipients_history` ADD `history_id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`history_id`); 

#GTB - 2021-01-26 - add additional images
ALTER TABLE `categories` ADD `categories_image_mobile` VARCHAR(255) NOT NULL AFTER `categories_image`; 
ALTER TABLE `categories` ADD `categories_image_list` VARCHAR(255) NOT NULL AFTER `categories_image_mobile`;
ALTER TABLE `banners` ADD `banners_image_mobile` VARCHAR(255) NOT NULL AFTER `banners_image`; 

#GTB - 2021-01-29 - update banner for new image handling
UPDATE `banners` SET `banners_image` = 'modified_banner.jpg', `banners_image_mobile` = 'modified_banner_mobile.jpg' WHERE banners_image = 'banner_modified-ecommerce-shopsoftware_de.jpg';
UPDATE `banners` SET `banners_image` = 'modified_banner.jpg', `banners_image_mobile` = 'modified_banner_mobile.jpg' WHERE banners_image = 'banner_modified-ecommerce-shopsoftware_en.jpg';

#GTB - 2021-02-03 - new_attributes
ALTER TABLE `admin_access` DROP `new_attributes`;

#GTB - 2021-02-08 - address format for netherlands
UPDATE `countries` SET `address_format_id` = 5 WHERE `countries_iso_code_2` = 'NL';

#GTB - 2021-04-01 - remove GB from EU
UPDATE `zones_to_geo_zones` SET `geo_zone_id` = '6' WHERE `zone_country_id` = 222;

#Tomcraft - 2021-04-01 - add semknox
ALTER TABLE `admin_access` ADD `semknox` INT(1) NOT NULL DEFAULT '0' AFTER `newsletter_recipients`;
UPDATE `admin_access` SET `semknox` = 1 WHERE `customers_id` = 1 LIMIT 1;
UPDATE `admin_access` SET `semknox` = 9 WHERE `customers_id` = 'groups' LIMIT 1;

# Keep an empty line at the end of this file for the db_updater to work properly