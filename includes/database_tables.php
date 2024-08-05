<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

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
$database_table_array = array(
  'TABLE_ADDRESS_BOOK' => 'address_book',
  'TABLE_ADDRESS_FORMAT' => 'address_format',
  'TABLE_ADMIN_ACCESS' => 'admin_access',
  'TABLE_BANKTRANSFER' => 'banktransfer',
  'TABLE_BANKTRANSFER_BLZ' => 'banktransfer_blz',
  'TABLE_BANNERS' => 'banners',
  'TABLE_BANNERS_HISTORY' => 'banners_history',
  'TABLE_CAMPAIGNS' => 'campaigns',
  'TABLE_CAMPAIGNS_IP' => 'campaigns_ip',
  'TABLE_CATEGORIES' => 'categories',
  'TABLE_CATEGORIES_DESCRIPTION' => 'categories_description',
  'TABLE_CM_FILE_FLAGS' => 'cm_file_flags',
  'TABLE_CONFIGURATION' => 'configuration',
  'TABLE_CONFIGURATION_GROUP' => 'configuration_group',
  'TABLE_CONTENT_MANAGER' => 'content_manager',
  'TABLE_CONTENT_MANAGER_CONTENT' => 'content_manager_content',
  'TABLE_COUNTRIES' => 'countries',
  'TABLE_COUPON_EMAIL_TRACK' => 'coupon_email_track',
  'TABLE_COUPON_GV_CUSTOMER' => 'coupon_gv_customer',
  'TABLE_COUPON_GV_QUEUE' => 'coupon_gv_queue',
  'TABLE_COUPON_REDEEM_TRACK' => 'coupon_redeem_track',
  'TABLE_COUPONS' => 'coupons',
  'TABLE_COUPONS_DESCRIPTION' => 'coupons_description',
  'TABLE_CURRENCIES' => 'currencies',
  'TABLE_CUSTOMERS' => 'customers',
  'TABLE_CUSTOMERS_BASKET' => 'customers_basket',
  'TABLE_CUSTOMERS_BASKET_ATTRIBUTES' => 'customers_basket_attributes',
  'TABLE_CUSTOMERS_INFO' => 'customers_info',
  'TABLE_CUSTOMERS_IP' => 'customers_ip',
  'TABLE_CUSTOMERS_LOGIN' => 'customers_login',
  'TABLE_CUSTOMERS_MEMO' => 'customers_memo',
  'TABLE_CUSTOMERS_STATUS' => 'customers_status',
  'TABLE_CUSTOMERS_STATUS_HISTORY' => 'customers_status_history',
  'TABLE_DATABASE_VERSION' => 'database_version',
  'TABLE_EMAIL_CONTENT' => 'email_content',
  'TABLE_GEO_ZONES' => 'geo_zones',
  'TABLE_LANGUAGES' => 'languages',
  'TABLE_MANUFACTURERS' => 'manufacturers',
  'TABLE_MANUFACTURERS_INFO' => 'manufacturers_info',
  'TABLE_MODULE_NEWSLETTER' => 'module_newsletter',
  'TABLE_NEWSLETTER_RECIPIENTS' => 'newsletter_recipients',
  'TABLE_NEWSLETTER_RECIPIENTS_HISTORY' => 'newsletter_recipients_history',
  'TABLE_NEWSLETTERS' => 'newsletters',
  'TABLE_NEWSLETTERS_HISTORY' => 'newsletters_history',
  'TABLE_ORDERS' => 'orders',
  'TABLE_ORDERS_PRODUCTS' => 'orders_products',
  'TABLE_ORDERS_PRODUCTS_ATTRIBUTES' => 'orders_products_attributes',
  'TABLE_ORDERS_PRODUCTS_DOWNLOAD' => 'orders_products_download',
  'TABLE_ORDERS_RECALCULATE' => 'orders_recalculate',
  'TABLE_ORDERS_STATUS' => 'orders_status',
  'TABLE_ORDERS_STATUS_HISTORY' => 'orders_status_history',
  'TABLE_ORDERS_TOTAL' => 'orders_total',
  'TABLE_PERSONAL_OFFERS_BY' => 'personal_offers_by_customers_status_', // _0/_1/_2/_3/_4
  'TABLE_PRODUCTS' => 'products',
  'TABLE_PRODUCTS_ATTRIBUTES' => 'products_attributes',
  'TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD' => 'products_attributes_download',
  'TABLE_PRODUCTS_CONTENT' => 'products_content',
  'TABLE_PRODUCTS_DESCRIPTION' => 'products_description',
  'TABLE_PRODUCTS_GEO_ZONES_TO_TAX_CLASS' => 'products_geo_zones_to_tax_class',
  'TABLE_PRODUCTS_GRADUATED_PRICES' => 'products_graduated_prices',
  'TABLE_PRODUCTS_IMAGES' => 'products_images',
  'TABLE_PRODUCTS_IMAGES_DESCRIPTION' => 'products_images_description',
  'TABLE_PRODUCTS_NOTIFICATIONS' => 'products_notifications',
  'TABLE_PRODUCTS_OPTIONS' => 'products_options',
  'TABLE_PRODUCTS_OPTIONS_VALUES' => 'products_options_values',
  'TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS' => 'products_options_values_to_products_options',
  'TABLE_PRODUCTS_TO_CATEGORIES' => 'products_to_categories',
  'TABLE_PRODUCTS_VPE' => 'products_vpe',
  'TABLE_PRODUCTS_XSELL' => 'products_xsell',
  'TABLE_PRODUCTS_XSELL_GROUPS' => 'products_xsell_grp_name',
  'TABLE_REVIEWS' => 'reviews',
  'TABLE_REVIEWS_DESCRIPTION' => 'reviews_description',
  'TABLE_SCHEDULED_TASKS' => 'scheduled_tasks',
  'TABLE_SCHEDULED_TASKS_LOG' => 'scheduled_tasks_log',
  'TABLE_SERVER_TRACKING' => 'server_tracking',
  'TABLE_SESSIONS' => 'sessions',
  'TABLE_SHIPPING_STATUS' => 'shipping_status',
  'TABLE_SPECIALS' => 'specials',
  'TABLE_TAX_CLASS' => 'tax_class',
  'TABLE_TAX_RATES' => 'tax_rates',
  'TABLE_WHOS_ONLINE' => 'whos_online',
  'TABLE_ZONES' => 'zones',
  'TABLE_ZONES_TO_GEO_ZONES' => 'zones_to_geo_zones',
  
  ## External Modules
  
  // track & trace
  'TABLE_CARRIERS' => 'carriers',
  'TABLE_ORDERS_TRACKING' => 'orders_tracking',
  
  // wishlist
  'TABLE_CUSTOMERS_WISHLIST' => 'customers_wishlist',
  'TABLE_CUSTOMERS_WISHLIST_ATTRIBUTES' => 'customers_wishlist_attributes',
  
  // products tags
  'TABLE_PRODUCTS_TAGS' => 'products_tags',
  'TABLE_PRODUCTS_TAGS_VALUES' => 'products_tags_values',
  'TABLE_PRODUCTS_TAGS_OPTIONS' => 'products_tags_options',
  
  // express checkout
  'TABLE_CUSTOMERS_CHECKOUT' => 'customers_checkout',
  
  // trusted shops
  'TABLE_TRUSTEDSHOPS' => 'trustedshops',
  
  // cookie consent
  'TABLE_COOKIE_CONSENT_COOKIES' => 'cookie_consent_cookies',
  'TABLE_COOKIE_CONSENT_CATEGORIES' => 'cookie_consent_categories',
);

require_once(DIR_FS_INC.'auto_include.inc.php');
foreach(auto_include(DIR_FS_CATALOG.'includes/extra/database_tables/','php') as $file) require ($file);

// define 
foreach ($database_table_array as $key => $val) {
  defined($key) or define($key, $val);
}
