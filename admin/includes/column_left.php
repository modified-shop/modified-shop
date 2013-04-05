<?php
  /* --------------------------------------------------------------
   $Id: column_left.php 4298 2013-01-13 20:04:19Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(column_left.php,v 1.15 2002/01/11); www.oscommerce.com
   (c) 2003 nextcommerce (column_left.php,v 1.25 2003/08/19); www.nextcommerce.org
   (c) 2006 XT-Commerce (content_manager.php 1304 2005-10-12)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
 //#############################
 // HINWEIS FÜR MODULE EINBAU
 // Das muss beim Hinzufügen neuer Menüpunkte beachtet werden:
 // Die Menüpunkte wurden auf "LISTE" geändert: <li>...</li>
 // Die Reihenfolge weicht von einem xtc Standard ab
 // Rubrik Konfiguration wurde in 2 Teile aufgeteilt
 // Durch die neuen Kommentare ist alles übersichtlicher 
 //#############################
 
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  $admin_access_query = xtc_db_query("select * from " . TABLE_ADMIN_ACCESS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
  $admin_access = xtc_db_fetch_array($admin_access_query); 
 
// BOF - Tomcraft - 2009-11-02 - NEW LISTSTYLE MENU
echo '<div id="cssmenu" class="suckertreemenu">';
echo '<ul id="treemenu1">';
//---------------------------Ausgewählte Admin Sprache als Flagge
echo ('<li><div id="lang_flag">' . xtc_image('../lang/' .  $_SESSION['language'] .'/admin/images/' . 'icon.gif', $_SESSION['language']). '</div></li>');
//---------------------------STARTSEITE
echo ('<li><a href="' . xtc_href_link('start.php', '', 'NONSSL') . '" id="current"><b>' . TEXT_ADMIN_START . '</b></a></li>'); 
//---------------------------KUNDEN
echo ('<li>');
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_CUSTOMERS.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['customers'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['customers_status'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS_STATUS . '</a></li>';
// BOF - Tomcraft - 2009-11-02 - set global customers-group-permissions
  if (GROUP_CHECK=='true') {
    if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['customers_group'] == '1')) echo '<li><a href="' . xtc_href_link('customers_group.php', '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS_GROUP . '</a></li>';
  }
// EOF - Tomcraft - 2009-11-02 - set global customers-group-permissions
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['orders'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_ORDERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS . '</a></li>';
echo ('</ul>');
echo ('</li>');

//---------------------------ARTIKELKATALOG
echo ('<li>');
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_PRODUCTS.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['categories'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CATEGORIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CATEGORIES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['new_attributes'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_NEW_ATTRIBUTES, '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_ATTRIBUTES_MANAGER.'</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['products_attributes'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_ATTRIBUTES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['manufacturers'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MANUFACTURERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MANUFACTURERS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['reviews'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_REVIEWS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_REVIEWS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['specials'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_SPECIALS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SPECIALS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['products_expected'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_EXPECTED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_EXPECTED . '</a></li>';
echo ('</ul>');
echo ('</li>');

/******** SHOPGATE **********/
include_once (DIR_FS_CATALOG.'includes/external/shopgate/base/admin/includes/column_left.php');
/******** SHOPGATE **********/

// BOF - Tomcraft - 2009-11-28 - Included xs:booster
//---------------------------XSBOOSTER
if (defined('MODULE_XTBOOSTER_STATUS') && MODULE_XTBOOSTER_STATUS =='True') {
echo ('<li>');
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_XSBOOSTER.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=list", '', 'NONSSL') . '" class="menuBoxContentLink"> - '.BOX_XSBOOSTER_LISTAUCTIONS.'</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=add", '', 'NONSSL') . '" class="menuBoxContentLink"> - '.BOX_XSBOOSTER_ADDAUCTIONS.'</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=conf", '', 'NONSSL') . '" class="menuBoxContentLink"> - '.BOX_XSBOOSTER_CONFIG.'</a></li>';
echo ('</ul>');
echo ('</li>');
}
// EOF - Tomcraft - 2009-11-28 - Included xs:booster

//---------------------------MODULE
echo ('<li>');
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_MODULES.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0')) echo '<li><a href="' . xtc_href_link(FILENAME_GOOGLE_SITEMAP, 'auto=true&ping=true') . '" class="menuBoxContentLink"> -' . BOX_GOOGLE_SITEMAP . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['modules'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=payment', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PAYMENT . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['modules'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=shipping', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SHIPPING . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['modules'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=ordertotal', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDER_TOTAL . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['module_export'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_EXPORT) . '" class="menuBoxContentLink"> -' . BOX_MODULE_EXPORT . '</a></li>';
  // BOF - Tomcraft - 2011-06-17 - Added janolaw AGB hosting service  
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['janolaw'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_JANOLAW, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_JANOLAW . '</a></li>';
  // EOF - Tomcraft - 2011-06-17 - Added janolaw AGB hosting service
  // BOF - Tomcraft - 2012-12-08 - Added haendlerbund AGB interface  
  if (($_SESSION['customers_status']['customers_status_id'] == '0' && $admin_access['haendlerbund'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_HAENDLERBUND, '') . '" class="menuBoxContentLink"> -' . BOX_HAENDLERBUND . '</a></li>';
  // EOF - Tomcraft - 2012-12-08 - Added haendlerbund AGB interface
echo ('</ul>');
echo ('</li>');

//---------------------------STATISTIKEN
echo ('<li>');
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_STATISTICS.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['stats_products_viewed'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_VIEWED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_VIEWED . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['stats_products_purchased'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_PURCHASED . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['stats_customers'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_STATS_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_STATS_CUSTOMERS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['stats_sales_report'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_SALES_REPORT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SALES_REPORT . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['stats_campaigns'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CAMPAIGNS_REPORT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CAMPAIGNS_REPORT . '</a></li>';
echo ('</ul>');
echo ('</li>');

//---------------------------PARTNER

//BOF - Dokuman - 2009-11-03 - Remove "partner" links
/*
echo ('<li>');  
  echo ('<div class="dataTableHeadingContent"><strong>Partner</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['econda'] == '1')) echo '<li><a href="' . xtc_href_link('econda.php') . '" class="menuBoxContentLink"> -ECONDA Shop Monitor' . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['cleverreach'] == '1')) echo '<li><a href="' . xtc_href_link('cleverreach.php') . '" class="menuBoxContentLink"> -CleverReach Newsletter' . '</a></li>';
echo ('</ul>');
echo ('</li>');
*/
//EOF - Dokuman - 2009-11-03 - Remove "partner" links

//---------------------------HILFSPROGRAMME
echo ('<li>');   
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_TOOLS.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['module_newsletter'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_NEWSLETTER) . '" class="menuBoxContentLink"> -' . BOX_MODULE_NEWSLETTER . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['content_manager'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER) . '" class="menuBoxContentLink"> -' . BOX_CONTENT . '</a></li>';
  //if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['blacklist'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_BLACKLIST, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_TOOLS_BLACKLIST . '</a></li>'; //removed blacklist
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['removeoldpics'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_REMOVEOLDPICS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_REMOVEOLDPICS . '</a></li>'; // franky_n - remove old pictures
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['backup'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_BACKUP) . '" class="menuBoxContentLink"> -' . BOX_BACKUP . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['banner_manager'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_BANNER_MANAGER) . '" class="menuBoxContentLink"> -' . BOX_BANNER_MANAGER . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['server_info'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_SERVER_INFO) . '" class="menuBoxContentLink"> -' . BOX_SERVER_INFO . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['blz_update'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_BLZ_UPDATE) . '" class="menuBoxContentLink"> -' . BOX_BLZ_UPDATE . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['whos_online'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_WHOS_ONLINE) . '" class="menuBoxContentLink"> -' . BOX_WHOS_ONLINE . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['csv_backend'] == '1')) echo '<li><a href="' . xtc_href_link('csv_backend.php') . '" class="menuBoxContentLink"> -' . BOX_IMPORT . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && (isset($admin_access['paypal']) && $admin_access['paypal'] == '1')) echo '<li><a href="' . xtc_href_link('paypal.php') . '" class="menuBoxContentLink"> -' . BOX_PAYPAL . '</a></li>'; //Tomcraft - 2009-10-03 - Paypal Express Modul in admin access
echo ('</ul>');
echo ('</li>');

//---------------------------GUTSCHEINE
  if (ACTIVATE_GIFT_SYSTEM=='true') {
echo ('<li>'); 
    echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_GV_ADMIN.'</strong></div>');
  echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['coupon_admin'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_COUPON_ADMIN . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['gv_queue'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_GV_QUEUE, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_QUEUE . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['gv_mail'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_GV_MAIL, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_MAIL . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['gv_sent'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_GV_SENT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_SENT . '</a></li>';
echo ('</ul>');
echo ('</li>');  
  }

//---------------------------LAND / STEUER
echo ('<li>');  
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_ZONE.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['languages'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_LANGUAGES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_LANGUAGES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['countries'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_COUNTRIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_COUNTRIES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['currencies'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CURRENCIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CURRENCIES. '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['zones'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ZONES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['geo_zones'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_GEO_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GEO_ZONES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['tax_classes'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_TAX_CLASSES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_TAX_CLASSES . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['tax_rates'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_TAX_RATES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_TAX_RATES . '</a></li>';
echo ('</ul>');
echo ('</li>');

//---------------------------KONFIGURATION
echo ('<li>');  
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_CONFIGURATION.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=1', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_1 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=1000', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_1000 . '</a></li>'; //web28 - 2012-08-27 - added My Admin
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=2', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_2 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=3', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_3 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=4', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_4 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=5', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_5 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=7', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_7 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=8', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_8 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=9', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_9 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=12', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_12 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=13', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_13 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['orders_status'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_ORDERS_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS_STATUS . '</a></li>';
  if (ACTIVATE_SHIPPING_STATUS=='true') {
    if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shipping_status'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SHIPPING_STATUS . '</a></li>';
  }
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['products_vpe'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_VPE, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_VPE . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['campaigns'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CAMPAIGNS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CAMPAIGNS . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['cross_sell_groups'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_XSELL_GROUPS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS_XSELL_GROUP . '</a></li>';
echo ('</ul>');
echo ('</li>');

//---------------------------KONFIGURATION2
echo ('<li>');  
  echo ('<div class="dataTableHeadingContent"><strong>'.BOX_HEADING_CONFIGURATION2.'</strong></div>');
echo ('<ul>');
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['shop_offline'] == '1')) echo '<li><a href="' . xtc_href_link('shop_offline.php', '', 'NONSSL') . '" class="menuBoxContentLink"> -'.'Shop online/offline'.'</a></li>'; //web28  -2010-07-07- SHOP OFFLINE/ONLINE
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=10', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_10 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=11', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_11 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=14', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_14 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=15', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_15 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=16', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_16 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=17', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_17 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=18', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_18 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=19', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_19 . '</a></li>';
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=22', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_22 . '</a></li>';
  //BOX_CONFIGURATION_23 - was Econda module
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=40', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_40 . '</a></li>'; //web28 - -2012-09-05 - added popop window configuration
  if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['configuration'] == '1')) echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=24', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_24 . '</a></li>'; //Dokuman - 2012-08-27 - added entries for new google analytics & piwik tracking
  echo ('</ul>');
echo ('</li>');
echo ('</ul>'); 
echo ('</div>');
// EOF - Tomcraft - 2009-11-02 - NEW LISTSTYLE MENU