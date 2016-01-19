<?php
/* -----------------------------------------------------------------------------------------
   $Id: filenames.php 4149 2012-12-23 14:21:35Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// define the filenames used in the project
define('FILENAME_ACCOUNTING', 'accounting.php');
define('FILENAME_BACKUP', 'backup.php');
define('FILENAME_BANNER_MANAGER', 'banner_manager.php');
define('FILENAME_BANNER_STATISTICS', 'banner_statistics.php');
define('FILENAME_CAMPAIGNS', 'campaigns.php');
define('FILENAME_CATALOG_ACCOUNT_HISTORY_INFO', 'account_history_info.php');
define('FILENAME_CATALOG_NEWSLETTER', 'newsletter.php');
define('FILENAME_CATEGORIES', 'categories.php');
define('FILENAME_CONFIGURATION', 'configuration.php');
define('FILENAME_COUNTRIES', 'countries.php');
define('FILENAME_CURRENCIES', 'currencies.php');
define('FILENAME_CUSTOMERS', 'customers.php');
define('FILENAME_CUSTOMERS_STATUS', 'customers_status.php');
define('FILENAME_DEFAULT', 'index.php');
define('FILENAME_GEO_ZONES', 'geo_zones.php');
define('FILENAME_LANGUAGES', 'languages.php');
define('FILENAME_MAGNALISTER','magnalister.php');
define('FILENAME_MAIL', 'mail.php');
define('FILENAME_MANUFACTURERS', 'manufacturers.php');
define('FILENAME_MODULES', 'modules.php');
define('FILENAME_ORDERS', 'orders.php');
define('FILENAME_ORDERS_STATUS', 'orders_status.php');
define('FILENAME_ORDERS_EDIT', 'orders_edit.php');
define('FILENAME_POPUP_IMAGE', 'popup_image.php');
define('FILENAME_PRODUCTS_ATTRIBUTES', 'products_attributes.php');
define('FILENAME_PRODUCTS_EXPECTED', 'products_expected.php');
define('FILENAME_REVIEWS', 'reviews.php');
define('FILENAME_SERVER_INFO', 'server_info.php');
define('FILENAME_BLZ_UPDATE', 'blz_update.php');
define('FILENAME_SPECIALS', 'specials.php');
define('FILENAME_STATS_CUSTOMERS', 'stats_customers.php');
define('FILENAME_STATS_PRODUCTS_PURCHASED', 'stats_products_purchased.php');
define('FILENAME_STATS_PRODUCTS_VIEWED', 'stats_products_viewed.php');
define('FILENAME_TAX_CLASSES', 'tax_classes.php');
define('FILENAME_TAX_RATES', 'tax_rates.php');
define('FILENAME_WHOS_ONLINE', 'whos_online.php');
define('FILENAME_ZONES', 'zones.php');
define('FILENAME_START', 'start.php');
define('FILENAME_STATS_STOCK_WARNING', 'stats_stock_warning.php');
define('FILENAME_NEW_ATTRIBUTES','new_attributes.php');
define('FILENAME_LOGOUT','logoff.php');
define('FILENAME_LOGIN','login.php');
define('FILENAME_CREATE_ACCOUNT','create_account.php');
define('FILENAME_CUSTOMER_MEMO','customer_memo.php');
define('FILENAME_CONTENT_MANAGER','content_manager.php');
define('FILENAME_CONTENT_PREVIEW','content_preview.php');
define('FILENAME_SECURITY_CHECK','security_check.php');
define('FILENAME_PRINT_ORDER','print_order.php');
define('FILENAME_CREDITS','credits.php');
define('FILENAME_PRINT_PACKINGSLIP','print_packingslip.php');
define('FILENAME_MODULE_NEWSLETTER','module_newsletter.php');
define('FILENAME_GV_QUEUE', 'gv_queue.php');
define('FILENAME_GV_MAIL', 'gv_mail.php');
define('FILENAME_GV_SENT', 'gv_sent.php');
define('FILENAME_COUPON_ADMIN', 'coupon_admin.php');
define('FILENAME_POPUP_MEMO', 'popup_memo.php');
define('FILENAME_SHIPPING_STATUS', 'shipping_status.php');
define('FILENAME_SALES_REPORT','stats_sales_report.php');
define('FILENAME_MODULE_EXPORT','module_export.php');
define('FILENAME_PRODUCTS_VPE','products_vpe.php');
define('FILENAME_CAMPAIGNS_REPORT','stats_campaigns.php');
define('FILENAME_XSELL_GROUPS','cross_sell_groups.php');
define('FILENAME_REMOVEOLDPICS', 'removeoldpics.php');
define('FILENAME_SHOPGATE', 'shopgate.php');
define('FILENAME_JANOLAW','janolaw.php');
define('FILENAME_HAENDLERBUND', 'haendlerbund.php');
define('FILENAME_XTBOOSTER','xtbooster.php');
define('FILENAME_SAFETERMS','safeterms.php');
define('FILENAME_EASYMARKETING','easymarketing.php');
define('FILENAME_GV_CUSTOMERS','gv_customers.php');
define('FILENAME_IT_RECHT_KANZLEI','it_recht_kanzlei.php');
define('FILENAME_PROTECTEDSHOPS','protectedshops.php');
define('FILENAME_PARCEL_CARRIERS','parcel_carriers.php');
define('FILENAME_CSV_BACKEND','csv_backend.php');
define('FILENAME_CLEVERREACH','cleverreach.php');
define('FILENAME_SUPERMAILER','supermailer.php');
define('FILENAME_LOGS', 'logs.php');
define('FILENAME_SHIPCLOUD', 'shipcloud.php');
define('FILENAME_PRODUCTS_TAGS', 'products_tags.php');
define('FILENAME_TRUSTEDSHOPS', 'trustedshops.php');

require_once(DIR_FS_INC.'auto_include.inc.php');
foreach(auto_include(DIR_FS_ADMIN.'includes/extra/filenames/','php') as $file) require ($file);
?>