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

#GTB - 2026-07-23 - update iso codes for some countries
UPDATE `countries` SET `countries_iso_code_3` = 'AUS' WHERE countries_iso_code_2 = 'AU' AND countries_iso_code_3 = 'AUD';
UPDATE `countries` SET `countries_iso_code_3` = 'ROU' WHERE countries_iso_code_2 = 'RO' AND countries_iso_code_3 = 'ROM';
UPDATE `countries` SET `countries_iso_code_2` = 'TL', `countries_iso_code_3` = 'TLS' WHERE countries_iso_code_2 = 'TP' AND countries_iso_code_3 = 'TMP';

# Keep an empty line at the end of this file for the db_updater to work properly
