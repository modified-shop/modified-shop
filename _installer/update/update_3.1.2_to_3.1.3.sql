# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2024-12-03 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.1.3', NOW());

#GTB - 2024-12-03 - add index 
ALTER TABLE `banners_history` DROP INDEX `idx_banners_id`, ADD INDEX `idx_banners_id` (`banners_id`, `banners_history_date`);
UPDATE `banners_history` SET `banners_history_date` = date_format(banners_history_date, '%Y-%m-%d 00:00:00');

# Keep an empty line at the end of this file for the db_updater to work properly