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

#Tomcraft - 2013-06-21 - Added Safeterms module
ALTER TABLE admin_access ADD safeterms INT(1) NOT NULL DEFAULT 0;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 1 LIMIT 1;
UPDATE admin_access SET safeterms = 1 WHERE customers_id = 'groups' LIMIT 1;

#web28 - 2013-07-21 - Add content_meta_robots option to content_manager
ALTER TABLE content_manager ADD content_meta_robots VARCHAR(32) NOT NULL;

#web28 - 2013-07-04 - Languages in the admin can be de/activated individually
ALTER TABLE languages ADD status_admin INT( 1 ) NOT NULL DEFAULT '1';

#GTB - 2013-07-22 - Add customers_country_iso_code_2
ALTER TABLE orders ADD customers_country_iso_code_2 varchar(2) NOT NULL AFTER customers_address_format_id;

#GTB - 2013-07-22 - Add new index on products_model
ALTER TABLE products ADD INDEX idx_products_model (products_model);

#GTB - 2013-08-02 - Add new index on customers_basket
ALTER TABLE customers_basket ADD INDEX idx_customers_id (customers_id);

#GTB - 2013-08-02 - Add new index on customers_basket_attributes
ALTER TABLE customers_basket_attributes ADD INDEX idx_customers_id (customers_id);

#GTB - 2013-08-02 - Add new column on orders_products_download
ALTER TABLE orders_products_download ADD download_key VARCHAR(32) NOT NULL DEFAULT '';

#GTB - 2013-08-02 - Add new index on products_images
ALTER TABLE products_images ADD INDEX idx_products_id (products_id);

#GTB - 2013-08-02 - Add new index on sessions
ALTER TABLE sessions ADD INDEX idx_expiry (expiry);

#GTB - 2013-08-02 - Add new index on whos_online
ALTER TABLE whos_online ADD PRIMARY KEY (session_id);
ALTER TABLE whos_online ADD INDEX idx_time_last_click (time_last_click);

#GTB - 2013-08-02 - Add new index on coupons
ALTER TABLE coupons ADD INDEX idx_coupon_code (coupon_code);

# Keep an empty line at the end of this file for the db_updater to work properly
