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
UPDATE `countries` SET `countries_name` = 'Timor-Leste', `countries_iso_code_2` = 'TL', `countries_iso_code_3` = 'TLS' WHERE countries_iso_code_2 = 'TP' AND countries_iso_code_3 = 'TMP';

#GTB - 2026-07-23 - migrate TP to TL in saved shipping module country lists (East Timor iso code change above)
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_DHL_COUNTRIES_10';
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_CHP_COUNTRIES_7';
UPDATE `configuration` SET `configuration_value` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `configuration_value`, ','), ',TP,', ',TL,')) WHERE `configuration_key` = 'MODULE_SHIPPING_AP_COUNTRIES_5';
#GTB - 2026-07-24 - update DPD tracking link
UPDATE `carriers` SET `carrier_tracking_link` = 'https://my.dpd.de/redirect.aspx?action=2&parcelno=$1&locale=$2' WHERE `carrier_tracking_link` = 'https://extranet.dpd.de/cgi-bin/delistrack?pknr=$1+&typ=1&lang=$2';

#GTB - 2026-07-27 - speed up startpage product selection
ALTER TABLE `products`
  ADD INDEX `idx_products_startpage_status_sort` (`products_startpage`, `products_status`, `products_startpage_sort`);

#GTB - 2026-07-27 - speed up bestseller aggregation
ALTER TABLE `orders_products`
  ADD INDEX `idx_orders_products_bestsellers` (`orders_id`, `products_id`, `products_quantity`);

ALTER TABLE `products`
  ADD INDEX `idx_products_status_ordered` (`products_status`, `products_ordered`);

#GTB - 2026-07-27 - speed up upcoming products selection
ALTER TABLE `products`
  ADD INDEX `idx_products_status_date_available` (`products_status`, `products_date_available`);

#GTB - 2026-09-08 - speed up new products selection
ALTER TABLE `products`
  ADD INDEX `idx_products_status_date_added` (`products_status`, `products_date_added`);

#GTB - 2026-09-08 - speed up reviews box
ALTER TABLE `reviews`
  ADD INDEX `idx_reviews_status_date_added` (`reviews_status`, `date_added`);

#GTB - 2026-09-08 - speed up specials box
ALTER TABLE `specials`
  ADD INDEX `idx_specials_status_expires` (`status`, `expires_date`);

#GTB - 2026-09-08 - speed up product tag filter lookup
ALTER TABLE `products_tags`
  ADD INDEX `idx_options_values_products` (`options_id`, `values_id`, `products_id`);

#GTB - 2026-09-08 - speed up content and information box menus
ALTER TABLE `content_manager`
  ADD INDEX `idx_content_menu` (`languages_id`, `file_flag`, `content_status`, `content_active`, `parent_id`, `sort_order`);

# Keep an empty line at the end of this file for the db_updater to work properly
