# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.6.0_to_2.0.0.0.sql 3827 2012-10-30 19:16:40Z Tomcraft1980 $
#
#  modified - community made shopping
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2012 modified
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2012-11-11 - changed database_version
UPDATE database_version SET version = 'modified_2.0.0.0';


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
  carrier_name varchar(10) NOT NULL,
  carrier_tracking_link varchar(512) NOT NULL,
  carrier_sort_order int(4) NOT NULL,
  PRIMARY KEY (carrier_id)
) ENGINE=MyISAM;

INSERT INTO carriers (carrier_id, carrier_name, carrier_tracking_link, carrier_sort_order) VALUES
(1, 'DHL', 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=$1', 10),
(2, 'DPD', 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=de', 20),
(3, 'GLS', 'https://gls-group.eu/DE/de/paketverfolgung?match=$1', 30),
(4, 'UPS', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=$1', 40),
(5, 'HERMES', 'http://tracking.hlg.de/Tracking.jsp?TrackID=$1', 50),
(6, 'FEDEX', 'http://www.fedex.com/Tracking?action=track&tracknumbers=$1', 60);

DROP TABLE IF EXISTS orders_tracking;
CREATE TABLE IF NOT EXISTS orders_tracking (
  ortra_id int(11) NOT NULL AUTO_INCREMENT,
  ortra_order_id int(11) NOT NULL,
  ortra_carrier_id int(11) NOT NULL,
  ortra_parcel_id varchar(80) NOT NULL,
  PRIMARY KEY (ortra_id),
  KEY ortra_order_id (ortra_order_id)
) ENGINE=MyISAM;

#DokuMan - 2012-11-12 - set new default template, if existing template was named "xtc5"
UPDATE configuration SET configuration_value = 'tpl_modified', last_modified = NOW()
WHERE configuration_key = 'CURRENT_TEMPLATE' AND configuration_value = 'xtc5';

# Keep an empty line at the end of this file for the db_updater to work properly
