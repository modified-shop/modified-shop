# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2016-01-18 - changed database_version
UPDATE database_version SET version = 'MOD_1.0.6.3';

### Subsequent updates for 1.06 rev 4642 SP2 to 1.06 rev 4642 SP3
#GTB - 2015-01-16 - add track & trace
CREATE TABLE IF NOT EXISTS carriers (
  carrier_id INT(11) NOT NULL AUTO_INCREMENT,
  carrier_name VARCHAR(80) NOT NULL,
  carrier_tracking_link VARCHAR(512) NOT NULL,
  carrier_sort_order INT(11) NOT NULL,
  carrier_date_added DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  carrier_last_modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (carrier_id)
);

INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1', '10', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2', '20', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', '30', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', '40', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', '50', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', '60', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (7, 'TNT', 'http://www.tnt.de/servlet/Tracking?cons=$1', '70', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (8, 'TRANS-O-FLEX', 'http://track.tof.de/trace/tracking.cgi?barcode=$1', '80', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (9, 'KUEHNE-NAGEL', 'https://knlogin.kuehne-nagel.com/apps/fls.do?subevent=search&knReference=$1', '90', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (10, 'ILOXX', 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=$1', '100', NOW(), '');
INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES (11, 'LogoiX', 'http://www.logoix.com/cgi-bin/tnt.pl?q=$1', '110', NOW(), '');

CREATE TABLE IF NOT EXISTS orders_tracking (
  tracking_id INT(11) NOT NULL AUTO_INCREMENT,
  orders_id INT(11) NOT NULL,
  carrier_id INT(11) NOT NULL,
  parcel_id VARCHAR(80) NOT NULL,
  PRIMARY KEY (tracking_id),
  KEY idx_orders_id (orders_id)
);

ALTER TABLE admin_access ADD parcel_carriers INT(1) NOT NULL DEFAULT 0 AFTER payone_logs;
UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET parcel_carriers = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2015-04-09 - add shipcloud
ALTER TABLE admin_access ADD shipcloud INT(1) NOT NULL DEFAULT 0 AFTER parcel_carriers;
UPDATE admin_access SET shipcloud = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET shipcloud = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2015-11-18 - change field to TEXT
ALTER TABLE coupons MODIFY restrict_to_products TEXT DEFAULT NULL;
ALTER TABLE coupons MODIFY restrict_to_categories TEXT DEFAULT NULL;

#Tomcraft - 2015-12-29 - change field length to 255
ALTER TABLE `products_options` MODIFY `products_options_name` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `products_options_values` MODIFY `products_options_values_name` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `orders_products_attributes` MODIFY `products_options` VARCHAR(255) NOT NULL;
ALTER TABLE `orders_products_attributes` MODIFY `products_options_values` VARCHAR(255) NOT NULL;

#Tomcraft - 2014-08-20 - added protectedshops
ALTER TABLE admin_access ADD protectedshops INT(1) NOT NULL DEFAULT 0 AFTER payone_logs;
UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET protectedshops = 1 WHERE customers_id = 'groups' LIMIT 1;

# Keep an empty line at the end of this file for the db_updater to work properly
