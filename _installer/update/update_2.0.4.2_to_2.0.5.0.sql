# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-06-11 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.5.0');

#GTB - 2018-06-11 - fix #1378
ALTER TABLE orders_products MODIFY products_weight DECIMAL(15,4) NOT NULL;
ALTER TABLE products MODIFY products_weight DECIMAL(15,4) NOT NULL;

#GTB - 2018-06-19 - add newsletter update
ALTER TABLE admin_access ADD newsletter_recipients INT(1) NOT NULL DEFAULT '0' AFTER paypal_module;
UPDATE admin_access SET newsletter_recipients = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET newsletter_recipients = 5 WHERE customers_id = 'groups' LIMIT 1;

DROP TABLE IF EXISTS newsletter_recipients_history;
CREATE TABLE newsletter_recipients_history (
  customers_email_address VARCHAR(255) NOT NULL,
  customers_action VARCHAR(32) NOT NULL,
  ip_address varchar(50) DEFAULT NULL,
  date_added datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY idx_customers_email_address (customers_email_address)
);

#GTB - 2018-06-27 - add sort order tags
ALTER TABLE `products_tags` ADD `sort_order` INT(11) NOT NULL DEFAULT '0' AFTER `values_id` ;

#GTB - 2018-08-14 - remove unique id due to problems with coupons for create account or newsletter registration
ALTER TABLE `coupon_email_track` DROP INDEX `idx_coupon_id`;

#GTB - 2018-09-04 new captcha handling
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_USE_COLOR';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_USE_SHADOW';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_CODE_LENGTH';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_NUM_LINES';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_MIN_FONT';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_MAX_FONT';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_BACKGROUND_RGB';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_LINES_RGB';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_CHARS_RGB';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_WIDTH';
DELETE FROM `configuration` WHERE configuration_key = 'MODULE_CAPTCHA_HEIGHT';

#Tomcraft - 2018-10-13 - Delete entries for France, Metropolitan and Yugoslavia
DELETE FROM `zones_to_geo_zones` WHERE association_id = 74;
DELETE FROM `zones_to_geo_zones` WHERE association_id = 236;

#Tomcraft - 2019-01-09 - Update Tracking Link for HERMES
UPDATE carriers SET carrier_tracking_link = 'https://tracking.hermesworld.com/?TrackID=$1' WHERE carrier_name = 'HERMES';

#Tomcraft - 2019-01-18 - Added Monaco to EU Zones
UPDATE zones_to_geo_zones SET geo_zone_id = 5 WHERE zone_country_id = 141;

#GTB - 2019-02-05 - fix #1505
ALTER TABLE orders MODIFY shipping_class VARCHAR(64) NOT NULL;

#GTB - 2019-02-05 - fix #1510
UPDATE customers_info set customers_info_date_of_last_logon = customers_info_date_account_created WHERE customers_info_date_of_last_logon = '0000-00-00 00:00:00';
UPDATE customers_info set customers_info_number_of_logons = 1 WHERE customers_info_number_of_logons = 0;

#GTB - 2019-04-01 update contact us
UPDATE content_manager SET content_file = 'contact_us.php' WHERE content_group = '7';

#GTB - 2019-04-03 - fix #1517
ALTER TABLE module_backup MODIFY configuration_key VARCHAR(128) NOT NULL;
ALTER TABLE configuration MODIFY configuration_key VARCHAR(128) NOT NULL;

#GTB - 2019-04-03 - fix #1541
ALTER TABLE content_manager MODIFY content_text longtext NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly