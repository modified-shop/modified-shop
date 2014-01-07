# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2010-07-19 - changed database_version
UPDATE database_version SET version = 'MOD_2.0.0.0';

#Web28 - 2010-11-13 - add missing listproducts to admin_access
ALTER TABLE admin_access
  ADD check_update INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2013-06-21 - Added Safeterms module
ALTER TABLE admin_access ADD safeterms INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2013-10-24
ALTER TABLE admin_access ADD gv_customers INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET gv_customers = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET gv_customers = 1 WHERE customers_id = 'groups' LIMIT 1;

#web28 - 2013-07-21 - Add content_meta_robots option to content_manager
ALTER TABLE content_manager ADD content_meta_robots VARCHAR(32) NOT NULL;

#web28 - 2013-07-04 - Languages in the admin can be de/activated individually
ALTER TABLE languages ADD status_admin INT( 1 ) NOT NULL DEFAULT '1';

#GTB - 2013-07-22 - Add customers_country_iso_code_2
ALTER TABLE orders ADD customers_country_iso_code_2 varchar(2) NOT NULL AFTER customers_address_format_id;

#GTB - 2013-07-22 - Add new index on products_model
ALTER TABLE products ADD INDEX idx_products_model (products_model);

#GTB - 2013-08-02 - Add new index on customers_basket
ALTER TABLE customers_basket ADD INDEX idx_customers_id (customers_id);

#GTB - 2013-08-02 - Add new index on customers_basket_attributes
ALTER TABLE customers_basket_attributes ADD INDEX idx_customers_id (customers_id);

#GTB - 2013-08-02 - Add new column on orders_products_download
ALTER TABLE orders_products_download ADD download_key VARCHAR(32) NOT NULL DEFAULT '';

#GTB - 2013-08-02 - Add new index on products_images
ALTER TABLE products_images ADD INDEX idx_products_id (products_id);

#GTB - 2013-08-02 - Add new index on sessions
ALTER TABLE sessions ADD INDEX idx_expiry (expiry);

#GTB - 2013-08-02 - Add new index on whos_online
ALTER TABLE whos_online ADD PRIMARY KEY (session_id);
ALTER TABLE whos_online ADD INDEX idx_time_last_click (time_last_click);

#GTB - 2013-08-02 - Add new index on coupons
ALTER TABLE coupons ADD INDEX idx_coupon_code (coupon_code);

#GTB - 2013-08-02 - Changed Logging filename
UPDATE configuration SET configuration_value = 'query.log' WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';

#Web28 - 2013-08-02 - Add new table for module backups
CREATE TABLE module_backup (
  configuration_id int(11) NOT NULL AUTO_INCREMENT,
  configuration_key varchar(64) NOT NULL,
  configuration_value text NOT NULL,
  last_modified datetime DEFAULT NULL,
  PRIMARY KEY (configuration_id),
  KEY idx_configuration_key (configuration_key)
) ENGINE=MyISAM;

#Tomcraft - 2013-08-21 - Added hidden stock feature
ALTER TABLE admin_access ADD stats_stock_warning INT(1) NOT NULL DEFAULT 0 AFTER stats_sales_report;
UPDATE admin_access SET stats_stock_warning = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET stats_stock_warning = 5 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2013-08-23 - Added swedish provinces
# Sweden
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'K','Blekinge');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'W','Dalarna');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'I','Gotland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'X','Gävleborg');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'N','Halland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'Z','Jämtland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'F','Jönköping');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'H','Kalmar');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'G','Kronoberg');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'BD','Norrbotten');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'T','Örebro');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'E','Östergötland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'M','Skåne');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'AB','Stockholm');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'D','Södermanland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'C','Uppsala');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'S','Värmland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'AC','Västerbotten');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'Y','Västernorrland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'U','Västmanland');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (203,'O','Västra Götaland');

#Tomcraft - 2013-08-29 - Added easymarketing
ALTER TABLE admin_access
  ADD easymarketing INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 'groups' LIMIT 1;

#Web28 - 2013-09-28 - Added required_zones
ALTER TABLE countries ADD required_zones INT(1) DEFAULT '0';

#Web28 - 2013-10-24 - Added gv_customers
ALTER TABLE admin_access ADD gv_customers INT(1) NOT NULL DEFAULT 0 AFTER gv_sent;
UPDATE admin_access SET gv_customers = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET gv_customers = 4 WHERE customers_id = 'groups' LIMIT 1;

#Web28 - 2013-10-27 - Added gender
ALTER TABLE orders ADD customers_gender char(1) NOT NULL AFTER customers_lastname;
ALTER TABLE orders ADD delivery_gender char(1) NOT NULL AFTER delivery_lastname;
ALTER TABLE orders ADD billing_gender char(1) NOT NULL AFTER billing_lastname;

#Web28 - 2013-10-27 - added IBAN and BIC in banktransfer payment module
ALTER TABLE banktransfer ADD banktransfer_iban VARCHAR(34) DEFAULT NULL AFTER banktransfer_blz;
ALTER TABLE banktransfer ADD banktransfer_bic VARCHAR(11) DEFAULT NULL AFTER banktransfer_iban;
ALTER TABLE banktransfer ADD banktransfer_email_address VARCHAR(96) DEFAULT NULL;

ALTER TABLE configuration MODIFY configuration_value text NOT NULL;
ALTER TABLE orders MODIFY payment_method varchar(128);
ALTER TABLE orders MODIFY shipping_method varchar(128);

#GTB - 2013-10-31 - added show always tax
ALTER TABLE customers_status ADD customers_status_show_tax_total int(7) DEFAULT '150' AFTER customers_status_show_price_tax;

ALTER TABLE categories_description MODIFY categories_id INT(11) NOT NULL;
ALTER TABLE products_description MODIFY products_id INT(11) NOT NULL;

ALTER TABLE zones MODIFY zone_name VARCHAR(64) NOT NULL;

#Web28 - 2013-11-11 - Added weight to orders
ALTER TABLE orders_products ADD products_weight DECIMAL(6,3) NOT NULL;
ALTER TABLE orders_products_attributes ADD options_values_weight DECIMAL(15,4) NOT NULL;
ALTER TABLE orders_products_attributes ADD weight_prefix CHAR(1) NOT NULL;

#h-h-h - 2013-12-05 - change min length to 0 for state dropdown
UPDATE configuration SET configuration_value = '0' WHERE configuration_key = 'ENTRY_STATE_MIN_LENGTH'

#Web28 - 2014-01-05 - Added languages_id to orders
ALTER TABLE orders ADD languages_id int(11) NOT NULL;
# Keep an empty line at the end of this file for the db_updater to work properly
