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
ALTER TABLE admin_access ADD check_update INT(1) NOT NULL DEFAULT 0;
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
ALTER TABLE admin_access ADD easymarketing INT(1) NOT NULL DEFAULT 0;
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
ALTER TABLE banktransfer ADD banktransfer_owner_email VARCHAR(96) DEFAULT NULL;

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
UPDATE configuration SET configuration_value = '0' WHERE configuration_key = 'ENTRY_STATE_MIN_LENGTH';

#Web28 - 2014-01-05 - Added languages_id to orders
ALTER TABLE orders ADD languages_id int(11) NOT NULL;

#GTB - 2014-02-04 - new fields for newsletter extension
ALTER TABLE newsletter_recipients ADD  ip_date_added varchar(32) DEFAULT NULL;
ALTER TABLE newsletter_recipients ADD  date_confirmed datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE newsletter_recipients ADD  ip_date_confirmed varchar(32) DEFAULT NULL;

#Web28 - 2014-03-20 change password length
ALTER TABLE customers MODIFY customers_password varchar(60) NOT NULL;

#Web28 - 2014-04-14 - Added delivery time
UPDATE content_manager SET content_group = 1010 WHERE content_group = 10;
INSERT INTO `content_manager` (`languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`) VALUES ('1','Delivery time','Delivery time','The deadline for delivery begins when paying in advance on the day after the payment order to the remitting bank or for other payments on the day to run after the conclusion and ends with the expiry of the last day of the period. Falls on a Saturday, Sunday or a public holiday delivery nationally recognized, the last day of the period, as occurs, the next business day at the place of such a day.','0','1','','1','10','0');
INSERT INTO `content_manager` (`languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`) VALUES ('2','Lieferzeit','Lieferzeit','Die Frist f&uuml;r die Lieferung beginnt bei Zahlung per Vorkasse am Tag nach Erteilung des Zahlungsauftrags an das &uuml;berweisende Kreditinstitut bzw. bei anderen Zahlungsarten am Tag nach Vertragsschluss zu laufen und endet mit dem Ablauf des letzten Tages der Frist. F&auml;llt der letzte Tag der Frist auf einen Samstag, Sonntag oder einen am Lieferort staatlich anerkannten allgemeinen Feiertag, so tritt an die Stelle eines solchen Tages der n&auml;chste Werktag.','0','1','','1','10','0');

#Web28 - 2014-03-20 add content_active
ALTER TABLE content_manager ADD content_active int(1) NOT NULL DEFAULT '1';

#Tomcraft - 2014-04-08 - Added it_recht_kanzlei
ALTER TABLE admin_access ADD it_recht_kanzlei INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
ALTER TABLE admin_access ADD payone_config INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET payone_config = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET payone_config = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
ALTER TABLE admin_access ADD payone_logs INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - delete configuration
DELETE FROM configuration WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';

#GTB - 2014-08-15 - added geo_zone_info
ALTER TABLE geo_zones ADD geo_zone_info INT(1) DEFAULT 0 AFTER geo_zone_description;

#GTB - 2014-08-15 - added status for currencies
ALTER TABLE currencies ADD status INT(1) NOT NULL DEFAULT 1;

#Tomcraft - 2014-08-20 - added protectedshops
ALTER TABLE admin_access ADD protectedshops INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2014-08-21 - Croatia is now member of the EU
UPDATE zones_to_geo_zones SET geo_zone_id = 5 WHERE association_id = 53;

#GTB - 2014-11-20 - added startdate for specials
ALTER TABLE specials ADD start_date DATETIME AFTER specials_last_modified;

#GTB - 2015-01-09 - Add new index on orders
ALTER TABLE orders ADD INDEX idx_orders_status (orders_status);

#GTB - 2015-01-13 - remove cc modules
ALTER TABLE orders DROP cc_type;
ALTER TABLE orders DROP cc_owner;
ALTER TABLE orders DROP cc_number;
ALTER TABLE orders DROP cc_expires;
ALTER TABLE orders DROP cc_start;
ALTER TABLE orders DROP cc_issue;
ALTER TABLE orders DROP cc_cvv;
DELETE FROM configuration WHERE configuration_key = 'CC_KEYCHAIN';
DELETE FROM configuration WHERE configuration_key = 'CC_OWNER_MIN_LENGTH';
DELETE FROM configuration WHERE configuration_key = 'CC_NUMBER_MIN_LENGTH';

#GTB - 2015-01-16 - add track & trace
CREATE TABLE IF NOT EXISTS carriers (
  carrier_id INT(11) NOT NULL AUTO_INCREMENT,
  carrier_name VARCHAR(80) NOT NULL,
  carrier_tracking_link VARCHAR(512) NOT NULL,
  carrier_sort_order INT(11) NOT NULL,
  carrier_date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  carrier_last_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (carrier_id)
) ENGINE=MyISAM;

INSERT INTO carriers VALUES (1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1', '10', NOW(), '');
INSERT INTO carriers VALUES (2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2', '20', NOW(), '');
INSERT INTO carriers VALUES (3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', '30', NOW(), '');
INSERT INTO carriers VALUES (4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', '40', NOW(), '');
INSERT INTO carriers VALUES (5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', '50', NOW(), '');
INSERT INTO carriers VALUES (6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', '60', NOW(), '');
INSERT INTO carriers VALUES (7, 'TNT', 'http://www.tnt.de/servlet/Tracking?cons=$1', '70', NOW(), '');
INSERT INTO carriers VALUES (8, 'TRANS-O-FLEX', 'http://track.tof.de/trace/tracking.cgi?barcode=$1', '80', NOW(), '');
INSERT INTO carriers VALUES (9, 'KUEHNE-NAGEL', 'https://knlogin.kuehne-nagel.com/apps/fls.do?subevent=search&knReference=$1', '90', NOW(), '');
INSERT INTO carriers VALUES (10, 'ILOXX', 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=$1', '100', NOW(), '');
INSERT INTO carriers VALUES (11, 'LogoiX', 'http://www.logoix.com/cgi-bin/tnt.pl?q=$1', '110', NOW(), '');

CREATE TABLE IF NOT EXISTS orders_tracking (
  tracking_id INT(11) NOT NULL AUTO_INCREMENT,
  order_id INT(11) NOT NULL,
  carrier_id INT(11) NOT NULL,
  parcel_id VARCHAR(80) NOT NULL,
  PRIMARY KEY (tracking_id),
  KEY order_id (order_id)
) ENGINE=MyISAM;

ALTER TABLE admin_access ADD parcel_carriers INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-01-19 - change country
ALTER TABLE orders MODIFY customers_country VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY delivery_country VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY billing_country VARCHAR(64) NOT NULL;

#GTB - 2015-01-21 - delete unused tables
DROP TABLE IF EXISTS payment_moneybookers_currencies;
DROP TABLE IF EXISTS media_content;

#GTB - 2015-01-29 - added manufacturers description
ALTER TABLE manufacturers_info ADD manufacturers_description text AFTER languages_id;

#GTB - 2015-02-05 - change fck_wrapper
ALTER TABLE admin_access CHANGE fck_wrapper filemanager;

#GTB - 2015-02-05 - sort_order
ALTER TABLE orders_status ADD sort_order INT(11) DEFAULT 0 NOT NULL;
ALTER TABLE shipping_status ADD sort_order INT(11) DEFAULT 0 NOT NULL;

#GTB - 2015-02-12 - add index
ALTER TABLE products_vpe ADD PRIMARY KEY (products_vpe_id, language_id);
ALTER TABLE products_xsell_grp_name ADD PRIMARY KEY (products_xsell_grp_name_id, language_id);
ALTER TABLE coupons DROP INDEX idx_coupon_code;
ALTER TABLE coupons ADD UNIQUE (coupon_code);
ALTER TABLE countries ADD UNIQUE (countries_iso_code_2);
ALTER TABLE countries ADD UNIQUE (countries_iso_code_3);

#Tomcraft - 2015-02-14 - add SuperMailer
ALTER TABLE admin_access ADD supermailer INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET supermailer = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET supermailer = 1 WHERE customers_id = 'groups' LIMIT 1;
    
# Keep an empty line at the end of this file for the db_updater to work properly
