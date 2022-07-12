# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2022-07-10 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.8.0');

#GTB - 2022-07-10 - fix wrong function
UPDATE configuration SET set_function = 'xtc_cfg_select_content(\'REVIEWS_PURCHASED_INFOS\',' WHERE configuration_key = 'REVIEWS_PURCHASED_INFOS';

#GTB - 2022-07-10 - fix #2266 - fix NULL for zone_id
ALTER TABLE `zones_to_geo_zones` MODIFY `zone_id` INT(11) NOT NULL;

#GTB - 2022-07-10 - expand password field
ALTER TABLE `customers` MODIFY `customers_password` VARCHAR(255) NOT NULL;

#GTB - 2022-07-12 - extend manufacturers
ALTER TABLE `manufacturers` ADD `manufacturers_status` INT(1) NOT NULL AFTER `manufacturers_image`; 
ALTER TABLE `manufacturers` ADD `sort_order` INT(3) DEFAULT 0 NOT NULL AFTER `manufacturers_status`; 
ALTER TABLE `manufacturers` ADD `products_sorting` VARCHAR(64) NULL AFTER `sort_order`; 
ALTER TABLE `manufacturers` ADD `products_sorting2` VARCHAR(64) NOT NULL AFTER `products_sorting`; 
ALTER TABLE `manufacturers` ADD `listing_template` VARCHAR(64) NOT NULL DEFAULT '' AFTER `products_sorting2`; 
ALTER TABLE `manufacturers` ADD `categories_template` VARCHAR(64) AFTER `listing_template`; 
ALTER TABLE `manufacturers` ADD KEY `idx_manufacturers_status` (`manufacturers_status`);
ALTER TABLE `manufacturers` ADD KEY `idx_sort_order` (`sort_order`);

# Keep an empty line at the end of this file for the db_updater to work properly