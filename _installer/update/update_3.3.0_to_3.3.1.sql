# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#GTB - 2026-07-20 - changed database_version
INSERT INTO `database_version` (`version`, `date_added`) VALUES ('MOD_3.3.1', NOW());

#GTB - 2026-07-20 - set index to speed up admin dashboard turnover query
ALTER TABLE `orders_total` ADD INDEX `idx_class` (`class`, `orders_id`);

# Keep an empty line at the end of this file for the db_updater to work properly
