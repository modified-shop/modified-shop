# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.6.0_to_2.0.0.0.sql 3827 2012-10-30 19:16:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2012-11-11 - changed database_version
UPDATE database_version SET version = 'MOD_2.0.0.0';

#DokuMan - 2012-11-29 - Uninstall Modules according to /_RELEASE README.txt before upgrading
# Uninstall all (!) payment modules due to "exlusion config for shipping modules"
DELETE FROM configuration WHERE configuration_key LIKE 'MODULE_PAYMENT_%';

# Uninstall shipping module "dp", i.e replace DP shipping module with newer version
DELETE FROM configuration WHERE configuration_key LIKE 'MODULE_SHIPPING_DP_%';

# Uninstall payment module "gls", i.e replace GLS shipping module with newer version
DROP TABLE IF EXISTS gls_country_to_postal;
DROP TABLE IF EXISTS gls_postal_to_weight;
DROP TABLE IF EXISTS gls_weight;
DELETE FROM configuration WHERE configuration_key LIKE 'MODULE_SHIPPING_GLS_%';

#Hendrik - 2010-08-29 - Xajax Support in Backend
ALTER TABLE admin_access
  ADD xajax INT(1) DEFAULT 1 NOT NULL;

#DokuMan - 2010-09-28 - display VAT description multilingually
#Updating only the German tax rates here
UPDATE tax_rates SET tax_description = '19%', last_modified = NOW() WHERE tax_description = 'MwSt 19%';
UPDATE tax_rates SET tax_description = '7%', last_modified = NOW() WHERE tax_description = 'MwSt 7%';

#Tomcraft - 2011-03-02 - Added status for cancelled orders
#(Set next available number for status ID in both languages)
INSERT INTO orders_status (orders_status_id, language_id, orders_status_name)
  SELECT MAX(orders_status_id)+1, 1, 'Cancelled' FROM orders_status;
INSERT INTO orders_status (orders_status_id, language_id, orders_status_name)
  SELECT MAX(orders_status_id)+1, 2, 'Storniert' FROM orders_status;

# hendrik - 2011-05-14 - independent invoice number and date
ALTER TABLE orders
  ADD ibn_billnr VARCHAR(32) default '',
  ADD ibn_billdate DATE NOT NULL;

#DokuMan - 2012-08-28 - Track and Trace functionality
DROP TABLE IF EXISTS carriers;
CREATE TABLE carriers (
  carrier_id int(4) NOT NULL AUTO_INCREMENT,
  carrier_name varchar(12) NOT NULL,
  carrier_tracking_link varchar(512) NOT NULL,
  carrier_sort_order int(4) NOT NULL,
  carrier_date_added DATETIME,
  carrier_last_modified DATETIME,
  PRIMARY KEY (carrier_id)
) ENGINE=MyISAM;

INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added, carrier_last_modified) VALUES
(1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=$2&idc=$1', 10, now(), null),
(2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2', 20, now(), null),
(3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', 30, now(), null),
(4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', 40, now(), null),
(5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', 50, now(), null),
(6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', 60, now(), null),
(7, 'TNT', 'http://www.tnt.de/servlet/Tracking?cons=$1', 70, now(), null),
(8, 'TRANS-O-FLEX', 'http://track.tof.de/trace/tracking.cgi?barcode=$1', 80, now(), null),
(9, 'KUEHNE-NAGEL', 'https://knlogin.kuehne-nagel.com/apps/fls.do?subevent=search&knReference=$1', 90, now(), null),
(10, 'ILOXX', 'http://www.iloxx.de/net/einzelversand/tracking.aspx?ix=$1', 100, now(), null);

DROP TABLE IF EXISTS orders_tracking;
CREATE TABLE IF NOT EXISTS orders_tracking (
  ortra_id int(11) NOT NULL AUTO_INCREMENT,
  ortra_order_id int(11) NOT NULL,
  ortra_carrier_id int(11) NOT NULL,
  ortra_parcel_id varchar(80) NOT NULL,
  PRIMARY KEY (ortra_id),
  KEY ortra_order_id (ortra_order_id)
) ENGINE=MyISAM;

ALTER TABLE admin_access
  ADD parcel_carriers INT(1) DEFAULT 1 NOT NULL;

#DokuMan - 2012-11-12 - set new default template, if existing template was named "xtc5" 
UPDATE configuration SET configuration_value = 'tpl_modified', last_modified = NOW() 
WHERE configuration_key = 'CURRENT_TEMPLATE' AND configuration_value = 'xtc5';

#DokuMan - 2012-11-26 - changed default currency float to .4 and changed 'code' as unique key
DROP TABLE IF EXISTS currencies;
CREATE TABLE currencies (
  currencies_id INT NOT NULL AUTO_INCREMENT,
  code CHAR(3) NOT NULL,
  title VARCHAR(32) NOT NULL,
  symbol_left VARCHAR(12),
  symbol_right VARCHAR(12),
  decimal_point CHAR(1),
  thousands_point CHAR(1),
  decimal_places CHAR(1),
  value FLOAT(13,4),
  last_updated DATETIME NULL,
  PRIMARY KEY (currencies_id),
  UNIQUE KEY code (code)
) ENGINE=MyISAM;

#DokuMan - 2012-11-26 - Added more shop currencies by default
INSERT INTO currencies VALUES
(1, 'EUR', 'Euro', '', '&euro;', ',', '.', '2', 1.0000, '2012-11-26 00:00:00'),
(2, 'USD', 'United States Dollar', '$', '', '.', ',', '2', 1.2978, '2012-11-26 00:00:00'),
(3, 'CHF', 'Schweizer Franken', 'CHF', '', '.', '', '2', 1.2044, '2012-11-26 00:00:00'),
(4, 'GBP', 'Great Britain Pound', '', '&pound;', '.', ',', '2', 0.8094, '2012-11-26 00:00:00');

# DokuMan - 2012-11-26 - added IBAN and BIC in banktransfer payment module
ALTER TABLE banktransfer
  ADD banktransfer_iban VARCHAR(34) DEFAULT NULL AFTER banktransfer_blz,
  ADD banktransfer_bic VARCHAR(11) DEFAULT NULL AFTER banktransfer_iban;

# DokuMan - 2012-12-04 - rename moneybookers tables to amoneybookers
ALTER TABLE payment_moneybookers_currencies
  RENAME TO payment_amoneybookers_currencies;
ALTER TABLE payment_moneybookers_countries
  RENAME TO payment_amoneybookers_countries;

# Keep an empty line at the end of this file for the db_updater to work properly
