# -----------------------------------------------------------------------------------------
#  $Id: update_2.0.3.0_to_2.0.4.0.sql 11159 2018-05-30 10:54:05Z Tomcraft $
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2018-06-11 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.4.1');

#GTB - 2018-06-11 - fix #1378
ALTER TABLE orders_products MODIFY products_weight DECIMAL(15,4) NOT NULL;
ALTER TABLE products MODIFY products_weight DECIMAL(15,4) NOT NULL;

# Keep an empty line at the end of this file for the db_updater to work properly