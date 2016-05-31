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

# DokuMan - 2013-03-20 - change address format to 5 for Luxembourg
UPDATE countries SET address_format_id = 5 WHERE countries_id = 124;

#Tomcraft -2015-03-05 - Drop obsolete tables from old GLS shipping module
DROP TABLE IF EXISTS gls_country_to_postal;
DROP TABLE IF EXISTS gls_postal_to_weight;
DROP TABLE IF EXISTS gls_weight;

### Subsequent bugfixes for update_xtc3.0.4sp2.1_to_1.0.1.0.sql
ALTER TABLE campaigns DROP INDEX IDX_CAMPAIGNS_NAME;
ALTER TABLE `campaigns`
 ADD KEY `idx_campaigns_name` (`campaigns_name`);
ALTER TABLE manufacturers DROP INDEX IDX_MANUFACTURERS_NAME;
ALTER TABLE `manufacturers`
 ADD KEY `idx_manufacturers_name` (`manufacturers_name`);

### Subsequent bugfixes for update_1.0.2.0_to_1.0.3.0.sql
ALTER TABLE `languages` MODIFY `status` INT( 1 ) NOT NULL DEFAULT 1;

### Subsequent bugfixes for update_1.0.3.0_to_1.0.4.0.sql
ALTER TABLE whos_online MODIFY http_referer varchar(255) NOT NULL;

### Subsequent bugfixes for update_1.0.4.0_to_1.0.5.0.sql
#UPDATE admin_access SET shop_offline = 1 WHERE customers_id = 'groups' LIMIT 1;

### Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql
ALTER TABLE address_book MODIFY entry_firstname VARCHAR(64) NOT NULL;
ALTER TABLE address_book MODIFY entry_lastname VARCHAR(64) NOT NULL;
ALTER TABLE address_book MODIFY entry_street_address VARCHAR(64) NOT NULL;
ALTER TABLE address_book MODIFY entry_city VARCHAR(64) NOT NULL;
ALTER TABLE customers MODIFY customers_firstname VARCHAR(64) NOT NULL;
ALTER TABLE customers MODIFY customers_lastname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY customers_firstname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY customers_lastname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY customers_street_address VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY customers_city VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY delivery_firstname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY delivery_lastname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY delivery_street_address VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY delivery_city VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY billing_firstname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY billing_lastname VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY billing_street_address VARCHAR(64) NOT NULL;
ALTER TABLE orders MODIFY billing_city VARCHAR(64) NOT NULL;
ALTER TABLE newsletter_recipients MODIFY customers_firstname VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE newsletter_recipients MODIFY customers_lastname VARCHAR(64) NOT NULL DEFAULT '';
ALTER TABLE products_description MODIFY products_name varchar(255) NOT NULL DEFAULT '';
ALTER TABLE categories_description MODIFY categories_name VARCHAR(255) NOT NULL;
ALTER TABLE campaigns_ip MODIFY user_ip VARCHAR (39) NOT NULL;
ALTER TABLE coupon_gv_queue MODIFY ipaddr VARCHAR (39) NOT NULL DEFAULT '';
ALTER TABLE customers_ip MODIFY customers_ip VARCHAR (39) NOT NULL DEFAULT '';
ALTER TABLE orders MODIFY customers_ip VARCHAR (39) NOT NULL;
ALTER TABLE whos_online MODIFY ip_address VARCHAR (39) NOT NULL;
ALTER TABLE coupon_redeem_track MODIFY redeem_ip VARCHAR (39) NOT NULL DEFAULT '';

### Bugfixes from bugfixes_106beta.sql
ALTER TABLE `campaigns` CHANGE `campaigns_refid` `campaigns_refID` VARCHAR( 64 ) NOT NULL;
ALTER TABLE `products_xsell` CHANGE `id` `ID` INT( 10 ) NOT NULL AUTO_INCREMENT;
ALTER TABLE `campaigns_ip` CHANGE `TIME` `time` DATETIME NOT NULL;
#DELETE FROM `configuration` WHERE `configuration_key` = 'haendlerbund_rueckgabe'; # Not needed any more with the new haendlerbund module
#INSERT INTO `configuration` ( `configuration_key` ) VALUES  ( 'haendlerbund_rueckgabe' ); # Not needed any more with the new haendlerbund module
DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_DEALERS';
DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_IGNORE_GROUPE';
DELETE FROM `configuration` WHERE `configuration_key` = 'SEARCH_HIGHLIGHT';
DELETE FROM `configuration` WHERE `configuration_key` = 'SEARCH_HIGHLIGHT_STYLE';

### Bugfixes from bugfixes_106r4356.sql
#ALTER TABLE campaigns_ip MODIFY user_ip VARCHAR (39) NOT NULL; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"
#ALTER TABLE coupon_gv_queue MODIFY ipaddr VARCHAR (39) NOT NULL DEFAULT ''; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"
#ALTER TABLE customers_ip MODIFY customers_ip VARCHAR (39) NOT NULL DEFAULT ''; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"
#ALTER TABLE orders MODIFY customers_ip VARCHAR (39) NOT NULL; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"
#ALTER TABLE whos_online MODIFY ip_address VARCHAR (39) NOT NULL; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"
#ALTER TABLE coupon_redeem_track MODIFY redeem_ip VARCHAR (39) NOT NULL DEFAULT ''; # Already part of "Subsequent bugfixes for update_1.0.5.0_to_1.0.6.0.sql"

### Bugfixes from bugfixes_106r4642.sql
#Web28 - 2012-12-30 - set new sort_order by configuration_group_id 5 , Customer Details
#UPDATE configuration SET configuration_group_id = '5', sort_order = '10', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_GENDER'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '20', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_DOB'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '30', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_COMPANY'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '50', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_SUBURB'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '60', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_STATE'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '100', last_modified = NOW() WHERE configuration_key = 'ACCOUNT_OPTIONS'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE configuration SET configuration_group_id = '5', sort_order = '110', last_modified = NOW() WHERE configuration_key = 'DELETE_GUEST_ACCOUNT'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#Web28 - 2012-12-31 - add comments_sent for correct representation of the comments in the customers account
#ALTER TABLE orders_status_history ADD comments_sent INT( 1 )  NULL DEFAULT '0'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
#UPDATE orders_status_history SET comments_sent = '1' WHERE customer_notified = '1'; # Already included in "update_1.0.5.0_to_1.0.6.0.sql"
# ABER BEREITS IN bugfixes_106beta.sql enthalten!!!
#DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_DEALERS';
#DELETE FROM `configuration` WHERE `configuration_key` = 'AFTERBUY_IGNORE_GROUPE';

### Subsequent updates for 1.06 rev 4642 to 1.06 rev 4642 SP1
#Web28 - 2013-10-27 - added IBAN and BIC in banktransfer payment module
# Moved to update_1.0.6.0_to_1.0.6.1.sql
#ALTER TABLE banktransfer ADD banktransfer_iban VARCHAR(34) DEFAULT NULL AFTER banktransfer_blz;
#ALTER TABLE banktransfer ADD banktransfer_bic VARCHAR(11) DEFAULT NULL AFTER banktransfer_iban;
#ALTER TABLE banktransfer ADD banktransfer_owner_email VARCHAR(96) DEFAULT NULL;

### Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2
#Tomcraft - 2013-06-21 - Added Safeterms module
# Moved to update_1.0.6.1_to_1.0.6.2.sql
#ALTER TABLE admin_access ADD safeterms INT(1) NOT NULL DEFAULT 0 AFTER haendlerbund;
#UPDATE admin_access SET safeterms = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET safeterms = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2013-08-29 - Added easymarketing
# Moved to update_1.0.6.1_to_1.0.6.2.sql
#ALTER TABLE admin_access ADD easymarketing INT(1) NOT NULL DEFAULT 0 AFTER safeterms;
#UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2014-04-08 - Added it_recht_kanzlei
# Moved to update_1.0.6.1_to_1.0.6.2.sql
#ALTER TABLE admin_access ADD it_recht_kanzlei INT(1) NOT NULL DEFAULT 0 AFTER easymarketing;
#UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
# Moved to update_1.0.6.1_to_1.0.6.2.sql
#ALTER TABLE admin_access ADD payone_config INT(1) NOT NULL DEFAULT 0 AFTER it_recht_kanzlei;
#UPDATE admin_access SET payone_config = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET payone_config = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
# Moved to update_1.0.6.1_to_1.0.6.2.sql
#ALTER TABLE admin_access ADD payone_logs INT(1) NOT NULL DEFAULT 0 AFTER payone_config;
#UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 'groups' LIMIT 1;

### Subsequent bugfixes for update_1.0.6.0_to_2.0.0.0.sql
#ALTER TABLE admin_access DROP xajax; # Does not exist on updated databases! Only on newly installed shops since 1.05 SP1e
ALTER TABLE orders_status MODIFY orders_status_name VARCHAR(64) NOT NULL;
ALTER TABLE `currencies`
 ADD UNIQUE KEY `idx_code` (`code`);
ALTER TABLE `products_options_values_to_products_options`
 ADD KEY `idx_products_options_id` (`products_options_id`);

#Tomcraft - 2013-06-21 - Added Safeterms module
#ALTER TABLE admin_access ADD safeterms INT(1) NOT NULL DEFAULT 0; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET safeterms = 1 WHERE customers_id = 1 LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET safeterms = 1 WHERE customers_id = 'groups' LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"

#Web28 - 2010-11-13 - add missing listproducts to admin_access
ALTER TABLE admin_access ADD check_update INT(1) NOT NULL DEFAULT 0 AFTER safeterms;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2013-10-24
ALTER TABLE admin_access ADD gv_customers INT(1) NOT NULL DEFAULT 0 AFTER gv_sent;
UPDATE admin_access SET gv_customers = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET gv_customers = 4 WHERE customers_id = 'groups' LIMIT 1;

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
  UNIQUE idx_configuration_key (configuration_key)
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
#ALTER TABLE admin_access ADD easymarketing INT(1) NOT NULL DEFAULT 0; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 1 LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 'groups' LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"

#Web28 - 2013-09-28 - Added required_zones
ALTER TABLE countries ADD required_zones INT(1) DEFAULT '0';

#Web28 - 2013-10-27 - Added gender
ALTER TABLE orders ADD customers_gender char(1) NOT NULL AFTER customers_lastname;
ALTER TABLE orders ADD delivery_gender char(1) NOT NULL AFTER delivery_lastname;
ALTER TABLE orders ADD billing_gender char(1) NOT NULL AFTER billing_lastname;

#Web28 - 2013-10-27 - added IBAN and BIC in banktransfer payment module
#ALTER TABLE banktransfer ADD banktransfer_iban VARCHAR(34) DEFAULT NULL AFTER banktransfer_blz; # Already part of section "Subsequent updates for 1.06 rev 4642 to 1.06 rev 4642 SP1"
#ALTER TABLE banktransfer ADD banktransfer_bic VARCHAR(11) DEFAULT NULL AFTER banktransfer_iban; # Already part of section "Subsequent updates for 1.06 rev 4642 to 1.06 rev 4642 SP1"
#ALTER TABLE banktransfer ADD banktransfer_owner_email VARCHAR(96) DEFAULT NULL; # Already part of section "Subsequent updates for 1.06 rev 4642 to 1.06 rev 4642 SP1"

ALTER TABLE configuration MODIFY configuration_value text NOT NULL;
ALTER TABLE orders MODIFY payment_method varchar(128) NOT NULL;
ALTER TABLE orders MODIFY shipping_method varchar(128) NOT NULL;

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
ALTER TABLE orders ADD languages_id int(11) NOT NULL AFTER language;

#GTB - 2014-02-04 - new fields for newsletter extension
ALTER TABLE newsletter_recipients ADD ip_date_added varchar(32) DEFAULT NULL;
ALTER TABLE newsletter_recipients ADD date_confirmed datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE newsletter_recipients ADD ip_date_confirmed varchar(32) DEFAULT NULL;

#Web28 - 2014-03-20 change password length
ALTER TABLE customers MODIFY customers_password varchar(60) NOT NULL;

#Web28 - 2014-04-14 - Added delivery time
INSERT INTO configuration (configuration_id,  configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES   ('', 'SHIPPING_STATUS_INFOS', '', 17, 14, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'SHIPPING_STATUS_INFOS\',');
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`)
  SELECT MAX(content_id)+1, '1','Delivery time','Delivery time','The deadline for delivery begins when paying in advance on the day after the payment order to the remitting bank or for other payments on the day to run after the conclusion and ends with the expiry of the last day of the period. Falls on a Saturday, Sunday or a public holiday delivery nationally recognized, the last day of the period, as occurs, the next business day at the place of such a day.','0','1','','1',MAX(content_group)+1,'0' FROM content_manager;
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`)
  SELECT MAX(content_id)+1, '2','Lieferzeit','Lieferzeit','Die Frist f&uuml;r die Lieferung beginnt bei Zahlung per Vorkasse am Tag nach Erteilung des Zahlungsauftrags an das &uuml;berweisende Kreditinstitut bzw. bei anderen Zahlungsarten am Tag nach Vertragsschluss zu laufen und endet mit dem Ablauf des letzten Tages der Frist. F&auml;llt der letzte Tag der Frist auf einen Samstag, Sonntag oder einen am Lieferort staatlich anerkannten allgemeinen Feiertag, so tritt an die Stelle eines solchen Tages der n&auml;chste Werktag.','0','1','','1',MAX(content_group),'0' FROM content_manager;
UPDATE configuration SET configuration_value = (SELECT MAX(content_group) FROM content_manager) WHERE configuration_key = 'SHIPPING_STATUS_INFOS';

#Web28 - 2014-03-20 add content_active
ALTER TABLE content_manager ADD content_active int(1) NOT NULL DEFAULT '1';

#Tomcraft - 2014-04-08 - Added it_recht_kanzlei
#ALTER TABLE admin_access ADD it_recht_kanzlei INT(1) NOT NULL DEFAULT 0; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 1 LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 'groups' LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"

#GTB - 2014-07-01 - added payone
#ALTER TABLE admin_access ADD payone_config INT(1) NOT NULL DEFAULT 0; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET payone_config = 1 WHERE customers_id = 1 LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET payone_config = 1 WHERE customers_id = 'groups' LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"

#GTB - 2014-07-01 - added payone
#ALTER TABLE admin_access ADD payone_logs INT(1) NOT NULL DEFAULT 0; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 1 LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"
#UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 'groups' LIMIT 1; # Already part of section "Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2"

#GTB - 2014-07-01 - delete configuration
DELETE FROM configuration WHERE configuration_key = 'STORE_PAGE_PARSE_TIME_LOG';

#GTB - 2014-08-15 - added geo_zone_info
ALTER TABLE geo_zones ADD geo_zone_info INT(1) DEFAULT 0 AFTER geo_zone_description;

#GTB - 2014-08-15 - added status for currencies
ALTER TABLE currencies ADD status INT(1) NOT NULL DEFAULT 1;

#Tomcraft - 2014-08-20 - added protectedshops
# Moved to update_1.0.6.2_to_1.0.6.3.sql
#ALTER TABLE admin_access ADD protectedshops INT(1) NOT NULL DEFAULT 0 AFTER payone_logs;
#UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 'groups' LIMIT 1;

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
ALTER TABLE admin_access DROP blacklist;
DELETE FROM configuration WHERE configuration_key = 'CC_KEYCHAIN';
DELETE FROM configuration WHERE configuration_key = 'CC_OWNER_MIN_LENGTH';
DELETE FROM configuration WHERE configuration_key = 'CC_NUMBER_MIN_LENGTH';

#GTB - 2015-01-16 - add track & trace
# Moved to update_1.0.6.2_to_1.0.6.3.sql
#CREATE TABLE IF NOT EXISTS carriers (
#  carrier_id INT(11) NOT NULL AUTO_INCREMENT,
#  carrier_name VARCHAR(80) NOT NULL,
#  carrier_tracking_link VARCHAR(512) NOT NULL,
#  carrier_sort_order INT(11) NOT NULL,
#  carrier_date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
#  carrier_last_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
#  PRIMARY KEY (carrier_id)
#) ENGINE=MyISAM;

# Moved to update_1.0.6.2_to_1.0.6.3.sql
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1', '10', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2', '20', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', '30', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', '40', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', '50', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', '60', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (7, 'TNT', 'http://www.tnt.de/servlet/Tracking?cons=$1', '70', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (8, 'TRANS-O-FLEX', 'http://track.tof.de/trace/tracking.cgi?barcode=$1', '80', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (9, 'KUEHNE-NAGEL', 'https://knlogin.kuehne-nagel.com/apps/fls.do?subevent=search&knReference=$1', '90', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (10, 'ILOXX', 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=$1', '100', NOW(), '');
#INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (11, 'LogoiX', 'http://www.logoix.com/cgi-bin/tnt.pl?q=$1', '110', NOW(), '');

# Moved to update_1.0.6.2_to_1.0.6.3.sql
#CREATE TABLE IF NOT EXISTS orders_tracking (
#  tracking_id INT(11) NOT NULL AUTO_INCREMENT,
#  orders_id INT(11) NOT NULL,
#  carrier_id INT(11) NOT NULL,
#  parcel_id VARCHAR(80) NOT NULL,
#  PRIMARY KEY (tracking_id),
#  KEY idx_orders_id (orders_id)
#) ENGINE=MyISAM;

# Moved to update_1.0.6.2_to_1.0.6.3.sql
#ALTER TABLE admin_access ADD parcel_carriers INT(1) NOT NULL DEFAULT 0 AFTER protectedshops;
#UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 'groups' LIMIT 1;

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
ALTER TABLE admin_access CHANGE fck_wrapper filemanager INT(1) NOT NULL DEFAULT 0;

#GTB - 2015-02-05 - sort_order
ALTER TABLE orders_status ADD sort_order INT(11) DEFAULT 0 NOT NULL;
ALTER TABLE shipping_status ADD sort_order INT(11) DEFAULT 0 NOT NULL;

#GTB - 2015-02-12 - add index
ALTER TABLE products_vpe ADD PRIMARY KEY (products_vpe_id, language_id);
ALTER TABLE products_xsell_grp_name ADD PRIMARY KEY (products_xsell_grp_name_id, language_id);
ALTER TABLE coupons DROP INDEX idx_coupon_code;
ALTER TABLE coupons ADD UNIQUE idx_coupon_code (coupon_code);
ALTER TABLE countries ADD UNIQUE idx_countries_iso_code_2 (countries_iso_code_2);
ALTER TABLE countries ADD UNIQUE idx_countries_iso_code_3 (countries_iso_code_3);
ALTER TABLE coupon_gv_customer DROP INDEX customer_id;
ALTER TABLE coupons_description DROP INDEX coupon_id;
ALTER TABLE coupons_description ADD PRIMARY KEY (coupon_id, language_id);
ALTER TABLE coupon_email_track ADD UNIQUE idx_coupon_id (coupon_id);
ALTER TABLE customers_status DROP INDEX idx_orders_status_name;
ALTER TABLE customers_status ADD UNIQUE idx_customers_status_name (customers_status_name, language_id);
ALTER TABLE module_newsletter MODIFY title VARCHAR(255) NOT NULL;
ALTER TABLE campaigns MODIFY campaigns_refID VARCHAR(64) NOT NULL;
ALTER TABLE campaigns ADD UNIQUE idx_campaigns_refID (campaigns_refID);
ALTER TABLE banners_history ADD KEY idx_banners_id (banners_id);
ALTER TABLE languages ADD UNIQUE idx_code (code);
ALTER TABLE languages DROP INDEX idx_languages_name;
ALTER TABLE content_manager DROP INDEX content_meta_title;
#ALTER TABLE content_manager DROP INDEX content_meta_description; # Cannot be dropped as this index never existed!
#ALTER TABLE content_manager DROP INDEX content_meta_keywords; # Cannot be dropped as this index never existed!
ALTER TABLE content_manager ADD KEY idx_content_group (content_group);
ALTER TABLE countries DROP INDEX IDX_COUNTRIES_NAME;
ALTER TABLE countries ADD KEY idx_countries_name (countries_name);
ALTER TABLE customers ADD KEY idx_customers_email_address (customers_email_address);
ALTER TABLE customers_ip DROP INDEX customers_id;
ALTER TABLE customers_ip ADD KEY idx_customers_id (customers_id);
ALTER TABLE newsletter_recipients ADD UNIQUE idx_customers_email_address (customers_email_address);
ALTER TABLE newsletter_recipients ADD KEY idx_mail_key (mail_key);
ALTER TABLE orders_products_attributes ADD KEY idx_orders_id (orders_id);
ALTER TABLE orders_products_attributes ADD KEY idx_orders_products_id (orders_products_id);
ALTER TABLE orders_products_download ADD KEY idx_orders_id (orders_id);
ALTER TABLE orders_products_download ADD KEY idx_orders_products_id (orders_products_id);
ALTER TABLE orders_total DROP INDEX idx_orders_total_orders_id;
ALTER TABLE orders_total ADD KEY idx_orders_id (orders_id);
ALTER TABLE products ADD KEY idx_products_status (products_status);
ALTER TABLE products_description DROP INDEX products_name;
ALTER TABLE products_description ADD KEY idx_products_name (products_name);
ALTER TABLE products_graduated_prices DROP INDEX products_id;
ALTER TABLE products_graduated_prices ADD KEY idx_products_id (products_id);
ALTER TABLE reviews ADD KEY idx_products_id (products_id);
ALTER TABLE shop_configuration DROP INDEX configuration_key;
ALTER TABLE shop_configuration ADD KEY idx_configuration_key (configuration_key);
ALTER TABLE specials DROP INDEX idx_specials_products_id;
ALTER TABLE specials ADD KEY idx_products_id (products_id);
ALTER TABLE specials ADD KEY idx_status (status);
ALTER TABLE products_content ADD KEY idx_products_id (products_id);
ALTER TABLE address_book DROP INDEX idx_address_book_customers_id;
ALTER TABLE address_book ADD KEY idx_customers_id (customers_id);
ALTER TABLE banktransfer DROP INDEX orders_id;
ALTER TABLE banktransfer ADD KEY idx_orders_id (orders_id);
ALTER TABLE campaigns_ip ADD KEY idx_campaign (campaign);
ALTER TABLE coupon_gv_queue DROP INDEX uid;
ALTER TABLE coupon_gv_queue ADD KEY idx_customer_id (customer_id);
ALTER TABLE tax_rates ADD KEY idx_tax_zone_id (tax_zone_id);
ALTER TABLE zones_to_geo_zones ADD KEY idx_geo_zone_id (geo_zone_id);

#Tomcraft - 2015-02-14 - add SuperMailer
ALTER TABLE admin_access ADD supermailer INT(1) NOT NULL DEFAULT 0 AFTER parcel_carriers;
UPDATE admin_access SET supermailer = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET supermailer = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-02-16 - add shopgate
ALTER TABLE admin_access ADD shopgate INT(1) NOT NULL DEFAULT 0 AFTER supermailer;
UPDATE admin_access SET shopgate = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET shopgate = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2015-02-16 - Added status for cancelled orders
#(Set next available number for status ID in both languages)
INSERT INTO orders_status (orders_status_id, language_id, orders_status_name)
  SELECT MAX(orders_status_id)+1, 1, 'Reversed' FROM orders_status;
INSERT INTO orders_status (orders_status_id, language_id, orders_status_name)
  SELECT MAX(orders_status_id)+1, 2, 'Storniert' FROM orders_status;

#GTB - 2015-02-18 - remove tables
DROP TABLE IF EXISTS counter;
DROP TABLE IF EXISTS counter_history;
DROP TABLE IF EXISTS payment_moneybookers_countries;

#GTB - 2015-02-18 - remove configuration
DELETE FROM configuration WHERE configuration_key = 'SHIPPING_ORIGIN_COUNTRY';
DELETE FROM configuration WHERE configuration_key = 'SHIPPING_ORIGIN_ZIP';

#GTB - 2015-02-25 - add newsfeed
CREATE TABLE newsfeed (
  news_id INT( 11 ) NOT NULL AUTO_INCREMENT,
  news_title VARCHAR( 128 ) NULL,
  news_text TEXT NULL,
  news_link VARCHAR( 128 ) NULL,
  news_date INT( 11 ) NULL,
  PRIMARY KEY (news_id),
  UNIQUE idx_news_link (news_link)
) ENGINE=MyISAM;
ALTER TABLE admin_access ADD newsfeed INT(1) NOT NULL DEFAULT 0 AFTER shopgate;
UPDATE admin_access SET newsfeed = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET newsfeed = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-02-25 - remove image options
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_BEVEL';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_GREYSCALE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_ELLIPSE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_ROUND_EDGES';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_FRAME';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_DROP_SHADOW';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_THUMBNAIL_MOTION_BLUR';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_BEVEL';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_GREYSCALE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_ELLIPSE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_ROUND_EDGES';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_FRAME';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_DROP_SHADOW';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_INFO_MOTION_BLUR';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_BEVEL';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_GREYSCALE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_ELLIPSE';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_ROUND_EDGES';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_FRAME';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_DROP_SHADOW';
DELETE FROM configuration WHERE configuration_key = 'PRODUCT_IMAGE_POPUP_MOTION_BLUR';

#Tomcraft - 2015-02-25 - Fix zones iso codes
UPDATE zones SET zone_code = 'GR' WHERE zone_name = 'Graubünden';
UPDATE zones SET zone_code = 'RR' WHERE zone_name = 'Roraima';
UPDATE zones SET zone_code = 'LAG' WHERE zone_name = 'Guajira';
UPDATE zones SET zone_code = 'BMH' WHERE zone_name = 'Bournemouth';
UPDATE zones SET zone_code = 'MIK' WHERE zone_name = 'Milton Keynes';

#Tomcraft - 2015-02-25 - add index
ALTER TABLE zones ADD UNIQUE idx_country_code (zone_country_id, zone_code);
ALTER TABLE geo_zones ADD UNIQUE idx_geo_zone_name (geo_zone_name);

#Tomcraft - 2015-02-25 - change geo_zone_name/geo_zone_info
UPDATE geo_zones SET geo_zone_name = 'Steuerzone Nicht-EU-Ausland', geo_zone_description = 'Steuerzone für Nicht-EU-Ausland', geo_zone_info = '1' WHERE geo_zone_name = 'Steuerzone EU-Ausland';

#GTB - 2015-03-12 - add Signature
INSERT INTO configuration (configuration_id,  configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES   ('', 'EMAIL_SIGNATURE_ID', '', 12, 19, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'EMAIL_SIGNATURE_ID\',');
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`, `content_active`)
  SELECT MAX(content_id)+1, '1','E-Mail Signature','','<b>Company</b><br />Address<br />Location<br />Homepage<br />E-mail:<br />Phone:<br />Fax:<br />CEO:<br />VAT Reg No:','0','1','','0',MAX(content_group)+1,'0','0' FROM content_manager;
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`, `content_active`)
  SELECT MAX(content_id)+1, '2','E-Mail Signatur','','Firma<br />Adresse<br />Ort<br />Homepage<br />E-Mail:<br />Fon:<br />Fax:<br />USt-IdNr.:<br />Handelsregister<br />Gesch&auml;ftsf&uuml;hrer:','0','1','','0',MAX(content_group),'0','0' FROM content_manager;
UPDATE configuration SET configuration_value = (SELECT MAX(content_group) FROM content_manager) WHERE configuration_key = 'EMAIL_SIGNATURE_ID';

#GTB - 2015-03-12 - brute force login
ALTER TABLE customers ADD password_request_time DATETIME DEFAULT '0000-00-00 00:00:00' AFTER password_request_key;
ALTER TABLE customers ADD customers_login_tries INT(11) NOT NULL DEFAULT '0';
ALTER TABLE customers ADD customers_login_time DATETIME DEFAULT '0000-00-00 00:00:00';

#GTB - 2015-03-19 - change payment_class
ALTER TABLE orders MODIFY payment_class VARCHAR(64) NOT NULL;

#GTB - 2015-03-31 - add logs
ALTER TABLE admin_access ADD logs INT(1) NOT NULL DEFAULT 0 AFTER newsfeed;
UPDATE admin_access SET logs = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET logs = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-03-31 - move session configs
UPDATE configuration SET configuration_value = 'False', configuration_group_id = '6' WHERE configuration_key = 'SESSION_CHECK_SSL_SESSION_ID';
UPDATE configuration SET configuration_value = 'False', configuration_group_id = '6' WHERE configuration_key = 'SESSION_CHECK_USER_AGENT';
UPDATE configuration SET configuration_value = 'False', configuration_group_id = '6' WHERE configuration_key = 'SESSION_CHECK_IP_ADDRESS';
UPDATE configuration SET configuration_value = 'False', configuration_group_id = '6' WHERE configuration_key = 'SESSION_RECREATE';

#Tomcraft - 2015-04-09 - add shipcloud
# Moved to update_1.0.6.2_to_1.0.6.3.sql
#ALTER TABLE admin_access ADD shipcloud INT(1) NOT NULL DEFAULT 0 AFTER logs;
#UPDATE admin_access SET shipcloud = 1 WHERE customers_id = 1 LIMIT 1;
#UPDATE admin_access SET shipcloud = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-04-21 - update products image
ALTER TABLE products MODIFY products_image VARCHAR(254) NOT NULL;

#GTB - 2015-05-18 - change customers_basket_date_added
ALTER TABLE customers_basket MODIFY customers_basket_date_added DATETIME;

#web28 - 2015-05-19 - add content_group_index
ALTER TABLE content_manager ADD content_group_index int(4) NOT NULL DEFAULT '0';

#Tomcraft - 2015-07-02 - not needed anymore!
DELETE FROM `configuration` WHERE `configuration_key` = 'USE_CONTACT_EMAIL_ADDRESS';

#Tomcraft - 2015-07-21 - Revised spanish country codes, thx to webald
# Spain
DELETE FROM zones WHERE zone_country_id = '195';
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-C','A Coruña');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-VI','Álava');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-AB','Albacete');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-A','Alicante');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-AL','Almería');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-O','Asturias');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-AV','Ávila');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-BA','Badajoz');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-PM','Balears');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-B','Barcelona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-BU','Burgos');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CC','Cáceres');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CA','Cádiz');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-S','Cantabria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CS','Castellón');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CE','Ceuta');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CR','Ciudad Real');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CO','Córdoba');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-CU','Cuenca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-GI','Girona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-GR','Granada');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-GU','Guadalajara');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-SS','Guipúzcoa');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-H','Huelva');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-HU','Huesca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-J','Jaén');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-LO','La Rioja');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-GC','Las Palmas');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-LE','León');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-L','Lleida');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-LU','Lugo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-M','Madrid');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-MA','Malaga');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-ML','Melilla');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-MU','Murcia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-NA','Navarra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-OR','Ourense');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-P','Palencia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-PO','Pontevedra');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-SA','Salamanca');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-TF','Santa Cruz de Tenerife');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-SG','Segovia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-SE','Sevilla');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-SO','Soria');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-T','Tarragona');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-TE','Teruel');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-TO','Toledo');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-V','Valencia');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-VA','Valladolid');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-BI','Vizcaya');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-ZA','Zamora');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES (195,'ES-Z','Zaragoza');

#GTB - 2015-06-30 - add products_tags
ALTER TABLE admin_access ADD products_tags INT(1) NOT NULL DEFAULT 0 AFTER listcategories;
UPDATE admin_access SET products_tags = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET products_tags = 3 WHERE customers_id = 'groups' LIMIT 1;

DROP TABLE IF EXISTS products_tags;
CREATE TABLE products_tags (
  products_id int(11) NOT NULL,
  options_id int(11) NOT NULL,
  values_id int(11) NOT NULL,
  products_options_id int(11) NOT NULL DEFAULT '0',
  products_options_values_id int(11) NOT NULL DEFAULT '0',
  KEY idx_products_options_values (products_id,options_id,values_id),
  KEY idx_products_options_id (products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_tags_options;
CREATE TABLE products_tags_options (
  options_id int(11) NOT NULL,
  options_name varchar(128) NOT NULL,
  options_description text NOT NULL,
  options_content_group int(11) DEFAULT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL,
  status int(1) NOT NULL DEFAULT '1',
  filter int(1) NOT NULL DEFAULT '1',
  last_modified datetime DEFAULT NULL,
  date_added datetime NOT NULL,
  products_options_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (options_id,languages_id),
  KEY idx_products_options_id (products_options_id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS products_tags_values;
CREATE TABLE products_tags_values (
  values_id int(11) NOT NULL,
  options_id int(11) NOT NULL,
  values_name varchar(128) NOT NULL,
  values_description text NOT NULL,
  values_image varchar(128) NOT NULL,
  values_content_group int(11) DEFAULT NULL,
  sort_order int(11) NOT NULL DEFAULT '0',
  languages_id int(11) NOT NULL,
  status int(1) NOT NULL DEFAULT '1',
  filter int(1) NOT NULL DEFAULT '1',
  last_modified datetime DEFAULT NULL,
  date_added datetime NOT NULL,
  products_options_values_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (values_id,languages_id),
  KEY idx_options_id (options_id),
  KEY idx_products_options_values_id (products_options_values_id)
) ENGINE=MyISAM;

#WEB28 - 2015-08-11 - add attributes_vpe
ALTER TABLE products_attributes ADD attributes_vpe_id int(11) NOT NULL AFTER attributes_ean;
ALTER TABLE products_attributes ADD attributes_vpe_value decimal(15,4) NOT NULL AFTER attributes_vpe_id;

#Tomcraft - 2015-08-13 - update meta configuration values
UPDATE configuration SET configuration_value = 5 WHERE configuration_key = 'META_MIN_KEYWORD_LENGTH';
UPDATE configuration SET configuration_value = 15 WHERE configuration_key = 'META_KEYWORDS_NUMBER';
UPDATE configuration SET configuration_value = 156 WHERE configuration_key = 'META_DESCRIPTION_LENGTH';

#Tomcraft - 2015-08-19 - Removed France, Metropolitan
DELETE FROM countries WHERE countries_iso_code_3 = 'FXX';

#Tomcraft -2015-08-19 - Fixed zone name for "Terres australes et Antartiques françaises" as the field was only 32 characters before!
UPDATE zones SET zone_name = 'Terres australes et Antartiques françaises' WHERE zone_code = '984 (TOM)';

#GTB - 2015-09-28 - add Trusted Shops
ALTER TABLE admin_access ADD trustedshops INT(1) NOT NULL DEFAULT 0 AFTER shipcloud;
UPDATE admin_access SET trustedshops = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET trustedshops = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2015-12-10 - multilanguage banner
ALTER TABLE banners ADD languages_id INT(11) NOT NULL DEFAULT 0 AFTER banners_html_text;
UPDATE banners SET languages_id = (SELECT l.languages_id FROM languages l JOIN configuration c ON c.configuration_value = l.code AND c.configuration_key = 'DEFAULT_LANGUAGE');

#GTB - 2016-01-20 - added orders products ean/model
ALTER TABLE orders_products ADD products_ean VARCHAR(128) AFTER products_model;
ALTER TABLE orders_products ADD products_price_origin DECIMAL(15,4) NOT NULL AFTER products_price;
ALTER TABLE orders_products_attributes ADD attributes_model VARCHAR(64) AFTER products_options_values;
ALTER TABLE orders_products_attributes ADD attributes_ean VARCHAR(128) AFTER attributes_model;

#Tomcraft - 2016-01-29 - Add missing province Bournemouth for updated shops due to an error in previous update sql file, see r9302
DELETE FROM zones WHERE zone_code = 'BPL';
DELETE FROM zones WHERE zone_code = 'BMH';
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES ('222','BPL','Blackpool');
INSERT INTO zones (zone_country_id, zone_code, zone_name) VALUES ('222','BMH','Bournemouth');

#GTB - 2016-02-24 - added customers_status_specials
ALTER TABLE customers_status ADD customers_status_specials INT(1) NOT NULL DEFAULT 1 AFTER customers_status_read_reviews;

#Tomcraft - 2016-03-01 - Remove PayPal Classic API Module
DELETE FROM configuration_group WHERE configuration_group_id = 111125;

#GTB - 2016-03-23 - added date_added/last_modified
ALTER TABLE content_manager ADD date_added DATETIME NOT NULL AFTER content_group_index;
ALTER TABLE content_manager ADD last_modified DATETIME NOT NULL AFTER date_added;

#GTB - 2016-04-01 - Added Invoice content
INSERT INTO configuration (configuration_id,  configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES   ('', 'INVOICE_INFOS', '', 17, 14, NULL, NOW(), NULL, 'xtc_cfg_select_content(\'INVOICE_INFOS\',');
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`, `content_active`)
  SELECT MAX(content_id)+1, '1','Invoice data','Company - Address - Code City','Company<br/>Address<br/>Code City<br/><br/>Phone: 0123456789<br/>E-Mail: info@shop.de<br/>www: www.shopurl.de<br/><br/>IBAN: DE123456789011<br/>BIC: BYLEMDNE1DE<br/><br/>You can change this in the content manager.','0','1','','0',MAX(content_group)+1,'0','0' FROM content_manager;
INSERT INTO `content_manager` (`content_id`, `languages_id`, `content_title`, `content_heading`, `content_text`, `sort_order`, `file_flag`, `content_file`, `content_status`, `content_group`, `content_delete`, `content_active`)
  SELECT MAX(content_id)+1, '2','Rechnungsdaten','Firma - Adresse - PLZ Stadt','Firma<br/>Adresse<br/>PLZ Stadt<br/><br/>Tel: 0123456789<br/>E-Mail: info@shop.de<br/>www: www.shopurl.de<br/><br/>IBAN: DE123456789011<br/>BIC: BYLEMDNE1DE<br/><br/>Diese Daten k&ouml;nnen im Content Manager ge&auml;ndert werden.','0','1','','0',MAX(content_group),'0','0' FROM content_manager;
UPDATE configuration SET configuration_value = (SELECT MAX(content_group) FROM content_manager) WHERE configuration_key = 'INVOICE_INFOS';

# Keep an empty line at the end of this file for the db_updater to work properly
