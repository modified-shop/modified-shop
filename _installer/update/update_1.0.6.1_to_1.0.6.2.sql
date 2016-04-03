# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2016-01-18 - changed database_version
UPDATE database_version SET version = 'MOD_1.0.6.2';

### Subsequent updates for 1.06 rev 4642 SP1 to 1.06 rev 4642 SP2
#Tomcraft - 2013-06-21 - Added Safeterms module
ALTER TABLE admin_access ADD safeterms INT(1) NOT NULL DEFAULT 0 AFTER haendlerbund;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2013-08-29 - Added easymarketing
ALTER TABLE admin_access ADD easymarketing INT(1) NOT NULL DEFAULT 0 AFTER safeterms;
UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET easymarketing = 1 WHERE customers_id = 'groups' LIMIT 1;

#Tomcraft - 2014-04-08 - Added it_recht_kanzlei
ALTER TABLE admin_access ADD it_recht_kanzlei INT(1) NOT NULL DEFAULT 0 AFTER easymarketing;
UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET it_recht_kanzlei = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
ALTER TABLE admin_access ADD payone_config INT(1) NOT NULL DEFAULT 0 AFTER it_recht_kanzlei;
UPDATE admin_access SET payone_config = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET payone_config = 1 WHERE customers_id = 'groups' LIMIT 1;

#GTB - 2014-07-01 - added payone
ALTER TABLE admin_access ADD payone_logs INT(1) NOT NULL DEFAULT 0 AFTER payone_config;
UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET payone_logs = 1 WHERE customers_id = 'groups' LIMIT 1;

# Keep an empty line at the end of this file for the db_updater to work properly
