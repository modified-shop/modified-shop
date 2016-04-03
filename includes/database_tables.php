<?php
/* -----------------------------------------------------------------------------------------
   $Id: database_tables.php 1316 2005-10-21 15:30:58Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database_tables.php,v 1.1 2003/03/14); www.oscommerce.com 
   (c) 2003  nextcommerce (database_tables.php,v 1.8 2003/08/24); www.nextcommerce.org 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // define the database table names used in the project
  define('TABLE_ADDRESS_BOOK', 'address_book');
  define('TABLE_ADDRESS_FORMAT', 'address_format');
  define('TABLE_ADMIN_ACCESS', 'admin_access');
  define('TABLE_BANKTRANSFER','banktransfer');
  define('TABLE_BANKTRANSFER_BLZ','banktransfer_blz');
  define('TABLE_BANNERS', 'banners');
  define('TABLE_BANNERS_HISTORY', 'banners_history');
  define('TABLE_CAMPAIGNS', 'campaigns');
  define('TABLE_CAMPAIGNS_IP','campaigns_ip');
  define('TABLE_CATEGORIES', 'categories');
  define('TABLE_CATEGORIES_DESCRIPTION', 'categories_description');
  define('TABLE_CM_FILE_FLAGS', 'cm_file_flags');
  define('TABLE_CONFIGURATION', 'configuration');
  define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
  define('TABLE_CONTENT_MANAGER','content_manager');
  define('TABLE_COUNTRIES', 'countries');
  define('TABLE_COUPON_EMAIL_TRACK', 'coupon_email_track');
  define('TABLE_COUPON_GV_CUSTOMER', 'coupon_gv_customer');
  define('TABLE_COUPON_GV_QUEUE', 'coupon_gv_queue');
  define('TABLE_COUPON_REDEEM_TRACK', 'coupon_redeem_track');
  define('TABLE_COUPONS', 'coupons');
  define('TABLE_COUPONS_DESCRIPTION', 'coupons_description');
  define('TABLE_CURRENCIES', 'currencies');
  define('TABLE_CUSTOMERS', 'customers');
  define('TABLE_CUSTOMERS_BASKET', 'customers_basket');
  define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', 'customers_basket_attributes');
  define('TABLE_CUSTOMERS_INFO', 'customers_info');
  define('TABLE_CUSTOMERS_IP', 'customers_ip');
  define('TABLE_CUSTOMERS_MEMO','customers_memo');
  define('TABLE_CUSTOMERS_STATUS', 'customers_status');
  define('TABLE_CUSTOMERS_STATUS_HISTORY', 'customers_status_history');
  define('TABLE_DATABASE_VERSION', 'database_version');
  define('TABLE_GEO_ZONES', 'geo_zones');
  define('TABLE_LANGUAGES', 'languages');
  define('TABLE_MANUFACTURERS', 'manufacturers');
  define('TABLE_MANUFACTURERS_INFO', 'manufacturers_info');
  define('TABLE_MODULE_NEWSLETTER','module_newsletter');
  define('TABLE_NEWSLETTER_RECIPIENTS', 'newsletter_recipients');
  define('TABLE_NEWSLETTERS', 'newsletters');
  define('TABLE_NEWSLETTERS_HISTORY', 'newsletters_history');
  define('TABLE_ORDERS', 'orders');
  define('TABLE_ORDERS_PRODUCTS', 'orders_products');
  define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
  define('TABLE_ORDERS_RECALCULATE', 'orders_recalculate');
  define('TABLE_ORDERS_STATUS', 'orders_status');
  define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
  define('TABLE_ORDERS_TOTAL', 'orders_total');
  define('TABLE_PERSONAL_OFFERS_BY','personal_offers_by_customers_status_'); // _0/_1/_2/_3/_4
  define('TABLE_PRODUCTS', 'products');
  define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
  define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
  define('TABLE_PRODUCTS_CONTENT','products_content');
  define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
  define('TABLE_PRODUCTS_GRADUATED_PRICES', 'products_graduated_prices');
  define('TABLE_PRODUCTS_IMAGES', 'products_images');
  define('TABLE_PRODUCTS_NOTIFICATIONS', 'products_notifications');
  define('TABLE_PRODUCTS_OPTIONS', 'products_options');
  define('TABLE_PRODUCTS_OPTIONS_VALUES', 'products_options_values');
  define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', 'products_options_values_to_products_options');
  define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
  define('TABLE_PRODUCTS_VPE','products_vpe');
  define('TABLE_PRODUCTS_XSELL','products_xsell');
  define('TABLE_PRODUCTS_XSELL_GROUPS','products_xsell_grp_name');
  define('TABLE_REVIEWS', 'reviews');
  define('TABLE_REVIEWS_DESCRIPTION', 'reviews_description');
  define('TABLE_SERVER_TRACKING', 'server_tracking');
  define('TABLE_SESSIONS', 'sessions');
  define('TABLE_SHIPPING_STATUS', 'shipping_status');
  define('TABLE_SPECIALS', 'specials');
  define('TABLE_TAX_CLASS', 'tax_class');
  define('TABLE_TAX_RATES', 'tax_rates');
  define('TABLE_WHOS_ONLINE', 'whos_online');
  define('TABLE_ZONES', 'zones');
  define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
  
  ## External Modules
  
	// shopgate
	define('TABLE_SHOPGATE_ORDERS', 'orders_shopgate_order');

  // easybill
  define('TABLE_EASYBILL_DATEV', 'easybill_datev');
	define('TABLE_EASYBILL', 'easybill');
  
  // track & trace
  define('TABLE_CARRIERS', 'carriers');
  define('TABLE_ORDERS_TRACKING', 'orders_tracking');
  
  // wishlist
  define('TABLE_CUSTOMERS_WISHLIST', 'customers_wishlist');
  define('TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES', 'customers_wishlist_attributes');
  
  // products tags
  define('TABLE_PRODUCTS_TAGS', 'products_tags');
  define('TABLE_PRODUCTS_TAGS_VALUES', 'products_tags_values');
  define('TABLE_PRODUCTS_TAGS_OPTIONS', 'products_tags_options');
  
  // express checkout
  define('TABLE_CUSTOMERS_CHECKOUT', 'customers_checkout');
  
  // trusted shops
  define('TABLE_TRUSTEDSHOPS', 'trustedshops');
  
  require_once(DIR_FS_INC.'auto_include.inc.php');
  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/database_tables/','php') as $file) require ($file);
?>