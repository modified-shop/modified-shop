# -----------------------------------------------------------------------------------------
#  $Id: update_1.0.5.0_to_1.0.6.0.sql 3813 2012-10-29 11:54:40Z Tomcraft1980 $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2010-07-19 - changed database_version
UPDATE database_version SET version = 'MOD_1.0.7.0';

#Web28 - 2010-11-13 - add missing listproducts to admin_access
ALTER TABLE admin_access
  ADD check_update INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET check_update = 1 WHERE customers_id = 'groups' LIMIT 1;

# Keep an empty line at the end of this file for the db_updater to work properly
