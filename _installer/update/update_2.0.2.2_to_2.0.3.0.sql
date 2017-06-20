# -----------------------------------------------------------------------------------------
#  $Id$
#
#  modified eCommerce Shopsoftware
#  http://www.modified-shop.org
#
#  Copyright (c) 2009 - 2013 [www.modified-shop.org]
#  -----------------------------------------------------------------------------------------

#Tomcraft - 2017-03-08 - changed database_version
INSERT INTO `database_version` (`version`) VALUES ('MOD_2.0.3.0');

#GTB - 2017-06-10 - fix #1179
UPDATE admin_access SET filemanager = 1 WHERE customers_id = 1 LIMIT 1;
ALTER TABLE admin_access DROP fck_wrapper;

#GTB - 2017-06-10 - fix #1157
ALTER TABLE `personal_offers_by_customers_status_0`
ADD KEY `idx_products_id` (`products_id`);

ALTER TABLE `personal_offers_by_customers_status_1`
ADD KEY `idx_products_id` (`products_id`);

ALTER TABLE `personal_offers_by_customers_status_2`
ADD KEY `idx_products_id` (`products_id`);

ALTER TABLE `personal_offers_by_customers_status_3`
ADD KEY `idx_products_id` (`products_id`);

ALTER TABLE `personal_offers_by_customers_status_4`
ADD KEY `idx_products_id` (`products_id`);

ALTER TABLE `categories`
ADD KEY `idx_categories_status` (`categories_status`);

ALTER TABLE `content_manager`
DROP KEY `idx_content_group`;
ALTER TABLE `content_manager`
ADD KEY `idx_content_group` (`content_group`, `languages_id`);

ALTER TABLE `countries`
ADD KEY `idx_status` (`status`);

ALTER TABLE `customers`
ADD KEY `idx_customers_default_address_id` (`customers_default_address_id`);

ALTER TABLE `languages`
ADD KEY `idx_status` (`status`);

ALTER TABLE `tax_rates`
ADD KEY `idx_tax_class_id` (`tax_class_id`);

ALTER TABLE `zones`
ADD KEY `idx_zone_country_id` (`zone_country_id`);

#GTB - 2017-06-13 - add index for xsell
ALTER TABLE `products_xsell`
ADD KEY idx_xsell_id (xsell_id),
ADD KEY idx_products_id (products_id),
ADD KEY idx_products_xsell_grp_name_id (products_xsell_grp_name_id);

# Keep an empty line at the end of this file for the db_updater to work properly