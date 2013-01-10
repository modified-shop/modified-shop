<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

xtc_db_query("ALTER TABLE address_book ADD INDEX idx_address_book_customers_id (customers_id)");
xtc_db_query("ALTER TABLE campaigns ADD INDEX idx_campaigns_name (campaigns_name)");
xtc_db_query("ALTER TABLE banktransfer ADD INDEX idx_orders_id (orders_id)");
xtc_db_query("ALTER TABLE categories ADD INDEX idx_categories_parent_id (parent_id)");
xtc_db_query("ALTER TABLE categories_description ADD INDEX idx_categories_name (categories_name)");
xtc_db_query("ALTER TABLE configuration ADD INDEX idx_configuration_group_id (configuration_group_id)");
xtc_db_query("ALTER TABLE countries ADD INDEX idx_countries_name (countries_name)");
xtc_db_query("ALTER TABLE currencies ADD UNIQUE KEY idx_code (code)");
xtc_db_query("ALTER TABLE customers_ip ADD INDEX idx_customers_id (customers_id)");
xtc_db_query("ALTER TABLE customers_status ADD INDEX idx_orders_status_name (customers_status_name)");
xtc_db_query("ALTER TABLE languages ADD INDEX idx_languages_name (name)");
xtc_db_query("ALTER TABLE manufacturers ADD INDEX idx_manufacturers_name (manufacturers_name)");
xtc_db_query("ALTER TABLE orders ADD INDEX idx_customers_id (customers_id)");
xtc_db_query("ALTER TABLE orders_products ADD INDEX idx_orders_id (orders_id)");
xtc_db_query("ALTER TABLE orders_products ADD INDEX idx_products_id (products_id)");
xtc_db_query("ALTER TABLE orders_status ADD INDEX idx_orders_status_name (orders_status_name)");
xtc_db_query("ALTER TABLE shipping_status ADD INDEX idx_shipping_status_name (shipping_status_name)");
xtc_db_query("ALTER TABLE orders_total ADD INDEX idx_orders_total_orders_id (orders_id)");
xtc_db_query("ALTER TABLE products ADD INDEX idx_products_date_added (products_date_added)");
xtc_db_query("ALTER TABLE products_attributes ADD INDEX idx_products_id (products_id)");
xtc_db_query("ALTER TABLE products_attributes ADD INDEX idx_options (options_id, options_values_id)");
xtc_db_query("ALTER TABLE products_description ADD INDEX idx_products_name (products_name)");
xtc_db_query("ALTER TABLE products_graduated_prices ADD INDEX idx_products_id (products_id)");
xtc_db_query("ALTER TABLE products_to_categories ADD INDEX idx_categories_id (categories_id)");
xtc_db_query("ALTER TABLE specials ADD INDEX idx_specials_products_id (products_id)");
xtc_db_query("ALTER TABLE shop_configuration ADD INDEX idx_configuration_key (configuration_key)");
xtc_db_query("ALTER TABLE content_manager ADD FULLTEXT idx_content_meta (content_meta_title,content_meta_description,content_meta_keywords)");
xtc_db_query("ALTER TABLE coupon_gv_customer ADD INDEX idx_customer_id (customer_id)");
xtc_db_query("ALTER TABLE coupon_gv_queue ADD INDEX idx_uid (unique_id,customer_id,order_id)");
xtc_db_query("ALTER TABLE coupons_description ADD INDEX idx_coupon_id (coupon_id)");
xtc_db_query("ALTER TABLE orders_tracking ADD INDEX idx_ortra_order_id (ortra_order_id)");
?>