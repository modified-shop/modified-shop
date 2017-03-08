# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2017-03-08 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.2.2');

#Web28 - 2017-03-08 - add keys
ALTER TABLE `products`
 ADD KEY `idx_manufacturers_id` (`manufacturers_id`);
 
ALTER TABLE `products_tags_values`
 ADD KEY `idx_filter` (`filter`);

ALTER TABLE `products_tags_options`
 ADD KEY `idx_filter` (`filter`);

# Keep an empty line at the end of this file for the db_updater to work properly