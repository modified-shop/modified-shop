<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_INC.'auto_include.inc.php');
foreach(auto_include(DIR_FS_ADMIN.'includes/extra/filenames/', 'php') as $file) require ($file);

$filename_array = array(
  'FILENAME_ACCOUNTING' => 'accounting.php',
  'FILENAME_BACKUP' => 'backup.php',
  'FILENAME_BANNER_MANAGER' => 'banner_manager.php',
  'FILENAME_BANNER_STATISTICS' => 'banner_statistics.php',
  'FILENAME_CAMPAIGNS' => 'campaigns.php',
  'FILENAME_CATALOG_ACCOUNT_HISTORY_INFO' => 'account_history_info.php',
  'FILENAME_CATALOG_NEWSLETTER' => 'newsletter.php',
  'FILENAME_CATEGORIES' => 'categories.php',
  'FILENAME_CONFIGURATION' => 'configuration.php',
  'FILENAME_COUNTRIES' => 'countries.php',
  'FILENAME_CURRENCIES' => 'currencies.php',
  'FILENAME_CUSTOMERS' => 'customers.php',
  'FILENAME_CUSTOMERS_STATUS' => 'customers_status.php',
  'FILENAME_DEFAULT' => 'index.php',
  'FILENAME_GEO_ZONES' => 'geo_zones.php',
  'FILENAME_LANGUAGES' => 'languages.php',
  'FILENAME_MAGNALISTER' => 'magnalister.php',
  'FILENAME_MAIL' => 'mail.php',
  'FILENAME_MANUFACTURERS' => 'manufacturers.php',
  'FILENAME_MODULES' => 'modules.php',
  'FILENAME_ORDERS' => 'orders.php',
  'FILENAME_ORDERS_STATUS' => 'orders_status.php',
  'FILENAME_ORDERS_EDIT' => 'orders_edit.php',
  'FILENAME_PRODUCTS_ATTRIBUTES' => 'products_attributes.php',
  'FILENAME_PRODUCTS_EXPECTED' => 'products_expected.php',
  'FILENAME_REVIEWS' => 'reviews.php',
  'FILENAME_SERVER_INFO' => 'server_info.php',
  'FILENAME_SPECIALS' => 'specials.php',
  'FILENAME_STATS_CUSTOMERS' => 'stats_customers.php',
  'FILENAME_STATS_PRODUCTS_PURCHASED' => 'stats_products_purchased.php',
  'FILENAME_STATS_PRODUCTS_VIEWED' => 'stats_products_viewed.php',
  'FILENAME_TAX_CLASSES' => 'tax_classes.php',
  'FILENAME_TAX_RATES' => 'tax_rates.php',
  'FILENAME_WHOS_ONLINE' => 'whos_online.php',
  'FILENAME_ZONES' => 'zones.php',
  'FILENAME_START' => 'start.php',
  'FILENAME_STATS_STOCK_WARNING' => 'stats_stock_warning.php',
  'FILENAME_NEW_ATTRIBUTES' => 'new_attributes.php',
  'FILENAME_LOGOFF' => 'logoff.php',
  'FILENAME_LOGIN' => 'login.php',
  'FILENAME_CREATE_ACCOUNT' => 'create_account.php',
  'FILENAME_CUSTOMER_MEMO' => 'customer_memo.php',
  'FILENAME_CONTENT_MANAGER' => 'content_manager.php',
  'FILENAME_CONTENT_PREVIEW' => 'content_preview.php',
  'FILENAME_SECURITY_CHECK' => 'security_check.php',
  'FILENAME_PRINT_ORDER' => 'print_order.php',
  'FILENAME_CREDITS' => 'credits.php',
  'FILENAME_PRINT_PACKINGSLIP' => 'print_packingslip.php',
  'FILENAME_MODULE_NEWSLETTER' => 'module_newsletter.php',
  'FILENAME_GV_QUEUE' => 'gv_queue.php',
  'FILENAME_GV_MAIL' => 'gv_mail.php',
  'FILENAME_GV_SENT' => 'gv_sent.php',
  'FILENAME_COUPON_ADMIN' => 'coupon_admin.php',
  'FILENAME_POPUP_MEMO' => 'popup_memo.php',
  'FILENAME_SHIPPING_STATUS' => 'shipping_status.php',
  'FILENAME_SALES_REPORT' => 'stats_sales_report.php',
  'FILENAME_MODULE_EXPORT' => 'module_export.php',
  'FILENAME_PRODUCTS_VPE' => 'products_vpe.php',
  'FILENAME_CAMPAIGNS_REPORT' => 'stats_campaigns.php',
  'FILENAME_XSELL_GROUPS' => 'cross_sell_groups.php',
  'FILENAME_REMOVEOLDPICS' => 'removeoldpics.php',
  'FILENAME_SHOPGATE' => 'shopgate.php',
  'FILENAME_JANOLAW' => 'janolaw.php',
  'FILENAME_HAENDLERBUND' => 'haendlerbund.php',
  'FILENAME_GV_CUSTOMERS' => 'gv_customers.php',
  'FILENAME_IT_RECHT_KANZLEI' => 'it_recht_kanzlei.php',
  'FILENAME_PROTECTEDSHOPS' => 'protectedshops.php',
  'FILENAME_PARCEL_CARRIERS' => 'parcel_carriers.php',
  'FILENAME_CSV_BACKEND' => 'csv_backend.php',
  'FILENAME_CLEVERREACH' => 'cleverreach.php',
  'FILENAME_SUPERMAILER' => 'supermailer.php',
  'FILENAME_LOGS' => 'logs.php',
  'FILENAME_SHIPCLOUD' => 'shipcloud.php',
  'FILENAME_PRODUCTS_TAGS' => 'products_tags.php',
  'FILENAME_TRUSTEDSHOPS' => 'trustedshops.php',
  'FILENAME_DOWNLOAD' => 'download.php',
  'FILENAME_BLACKLIST_LOGS' => 'blacklist_logs.php',
  'FILENAME_NEWSLETTER_RECIPIENTS' => 'newsletter_recipients.php',
  'FILENAME_CHECKOUT_PROCESS' => '../checkout_process.php',
  'FILENAME_NEWSLETTER' => 'newsletter.php',
  'FILENAME_GV_REDEEM' => 'gv_redeem.php',
  'FILENAME_COOKIE_CONSENT' => 'cookie_consent.php',
);

// define 
foreach ($filename_array as $key => $val) {
  defined($key) or define($key, $val);
}
?>