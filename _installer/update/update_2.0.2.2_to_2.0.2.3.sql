# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2017-03-08 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.2.3');

#GTB - 2017-06-10 - fix #1179
UPDATE admin_access SET filemanager = 1 WHERE customers_id = 1 LIMIT 1;
ALTER TABLE admin_access DROP fck_wrapper;

# Keep an empty line at the end of this file for the db_updater to work properly