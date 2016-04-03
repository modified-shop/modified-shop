<?php
  /* --------------------------------------------------------------
   $Id: column_left.php 5354 2013-08-08 14:36:06Z Tomcraft $

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
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

$admin_access = array();
if (($_SESSION['customers_status']['customers_status_id'] == '0')) {
  $admin_access_query = xtc_db_query("SELECT * FROM " . TABLE_ADMIN_ACCESS . " WHERE customers_id = ".(int)$_SESSION['customer_id']);
  $admin_access = xtc_db_fetch_array($admin_access_query); 
}

//begin----------------------------functions----------------------------------------------------------------------------------

// mainMenue($box_title);
if (!function_exists('mainMenue')) { // erste ebene
  function mainMenue($box_title) {
    $html  = '<li>';            
    if (defined('NEW_ADMIN_STYLE')) {
      $html .= '<div class="dataNavHeadingContent"><a href="#"><strong>'.$box_title.'</strong></a></div>';
    } else {
      $html .= '<div class="dataNavHeadingContent"><strong>'.$box_title.'</strong></div>';
    }
    $html .= PHP_EOL .'<ul>'.PHP_EOL;
    return $html;
  }
}

// endMenue($box_title);
if (!function_exists('endMenue')) { // menue schliessen
  function endMenue($box_title) {    
    $html = '</ul>'.PHP_EOL;
    $html .= '</li>'.PHP_EOL;
    // extra menu
    if (function_exists('dynamicsAdds')) {
      $html  = dynamicsAdds($box_title) . $html;
    }
    return $html;
  }
}

//end----------------------------functions----------------------------------------------------------------------------------

// extra menu
if(file_exists(DIR_WS_INCLUDES.'extra_menu.php')) {
  require_once(DIR_WS_INCLUDES.'extra_menu.php');
}

//begin--------------------------HTML----------------------------------------------------------------------------------

echo '<div id="cssmenu" class="suckertreemenu">';
echo '<ul id="treemenu1">';

//---------------------------Ausgewaehlte Admin Sprache als Flagge
echo '<li><div id="lang_flag">' . xtc_image('../lang/' .  $_SESSION['language'] .'/admin/images/' . 'icon.gif', $_SESSION['language']). '</div></li>';

//---------------------------STARTSEITE
echo '<li><a href="' . xtc_href_link('start.php', '', 'NONSSL') . '" id="current"><b>' . TEXT_ADMIN_START . '</b></a></li>'; 

//---------------------------KUNDEN
echo mainMenue(BOX_HEADING_CUSTOMERS);
    if ($admin_access['customers'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS . '</a></li>';
    if ($admin_access['customers_status'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CUSTOMERS_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS_STATUS . '</a></li>';
    if ($admin_access['customers_group'] == '1' && GROUP_CHECK == 'true') echo '<li><a href="' . xtc_href_link('customers_group.php', '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CUSTOMERS_GROUP . '</a></li>';
    if ($admin_access['orders'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_ORDERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS . '</a></li>';
echo endMenue(BOX_HEADING_CUSTOMERS);

//---------------------------ARTIKELKATALOG
echo mainMenue(BOX_HEADING_PRODUCTS);
    if ($admin_access['categories'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CATEGORIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CATEGORIES . '</a></li>';
    if ($admin_access['new_attributes'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_NEW_ATTRIBUTES, '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_ATTRIBUTES_MANAGER.'</a></li>';
    if ($admin_access['products_attributes'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_ATTRIBUTES . '</a></li>';
    if ($admin_access['products_tags'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_TAGS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_TAGS . '</a></li>';
    if ($admin_access['manufacturers'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MANUFACTURERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MANUFACTURERS . '</a></li>';
    if ($admin_access['reviews'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_REVIEWS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_REVIEWS . '</a></li>';
    if ($admin_access['specials'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SPECIALS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SPECIALS . '</a></li>';
    if ($admin_access['products_expected'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_EXPECTED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_EXPECTED . '</a></li>';
    if ($admin_access['stats_stock_warning'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_STATS_STOCK_WARNING, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_STOCK_WARNING . '</a></li>';
echo endMenue(BOX_HEADING_PRODUCTS);

//---------------------------MODULE
echo mainMenue(BOX_HEADING_MODULES);
    if ($admin_access['modules'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=payment', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PAYMENT . '</a></li>';
    if ($admin_access['modules'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=shipping', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SHIPPING . '</a></li>';
    if ($admin_access['modules'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=ordertotal', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDER_TOTAL . '</a></li>';
    if ($admin_access['modules'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULES, 'set=categories', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MODULE_TYPE . '</a></li>';
    if ($admin_access['module_export'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=sitemaporg', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GOOGLE_SITEMAP . '</a></li>';
    if ($admin_access['module_export'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MODULE_SYSTEM . '</a></li>';
    if ($admin_access['module_export'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=export', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MODULE_EXPORT . '</a></li>';
echo endMenue(BOX_HEADING_MODULES);

//---------------------------PARTNER
echo mainMenue(BOX_HEADING_PARTNER_MODULES);
    if (isset($admin_access['janolaw']) && $admin_access['janolaw'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_JANOLAW, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_JANOLAW . '</a></li>';
    if (isset($admin_access['it_recht_kanzlei']) && $admin_access['it_recht_kanzlei'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_IT_RECHT_KANZLEI, '') . '" class="menuBoxContentLink"> -' . BOX_IT_RECHT_KANZLEI . '</a></li>';
    if (isset($admin_access['haendlerbund']) && $admin_access['haendlerbund'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_HAENDLERBUND, '') . '" class="menuBoxContentLink"> -' . BOX_HAENDLERBUND . '</a></li>';
    if (isset($admin_access['safeterms']) && $admin_access['safeterms'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SAFETERMS, '') . '" class="menuBoxContentLink"> -' . BOX_SAFETERMS . '</a></li>';
    if (isset($admin_access['easymarketing']) && $admin_access['easymarketing'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_EASYMARKETING, '') . '" class="menuBoxContentLink"> -' . BOX_EASYMARKETING . '</a></li>';
    if (isset($admin_access['protectedshops']) && $admin_access['protectedshops'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PROTECTEDSHOPS, '') . '" class="menuBoxContentLink"> -' . BOX_PROTECTEDSHOPS . '</a></li>';
    if (isset($admin_access['cleverreach']) && $admin_access['cleverreach'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CLEVERREACH, '') . '" class="menuBoxContentLink"> -' . BOX_CLEVERREACH . '</a></li>';
    if (isset($admin_access['supermailer']) && $admin_access['supermailer'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SUPERMAILER, '') . '" class="menuBoxContentLink"> -' . BOX_SUPERMAILER . '</a></li>';
    if (isset($admin_access['trustedshops']) && $admin_access['trustedshops'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_TRUSTEDSHOPS, '') . '" class="menuBoxContentLink"> -' . BOX_TRUSTEDSHOPS . '</a></li>';

    ## PayPal
    include(DIR_FS_EXTERNAL.'paypal/modules/column_left.php');

    ## shipcloud
    include(DIR_FS_EXTERNAL.'shipcloud/column_left.php');
    
    ## Magnalister
    if(defined('MODULE_MAGNALISTER_STATUS') && MODULE_MAGNALISTER_STATUS=='True') {
      if (isset($admin_access['magnalister']) && $admin_access['magnalister'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MAGNALISTER."", '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_MAGNALISTER.'</a></li>';
    } else {
      if ($admin_access['modules'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=magnalister', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_MAGNALISTER . '</a></li>';
    }
    
    ## Payone
    include(DIR_FS_EXTERNAL.'payone/modules/column_left.php');
    
    ## Shopgate
    if(defined('MODULE_PAYMENT_SHOPGATE_STATUS') && MODULE_PAYMENT_SHOPGATE_STATUS=='True') {
      include_once (DIR_FS_CATALOG.'includes/external/shopgate/base/admin/includes/column_left.php');
    } else {
      if ($admin_access['shopgate'] == '1') echo '<li><a href="' . xtc_href_link('shopgate.php', 'sg_option=info', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SHOPGATE . '</a></li>';
    }

    ## xs:booster
    if (defined('MODULE_XTBOOSTER_STATUS') && MODULE_XTBOOSTER_STATUS =='True') {
      echo '<li><a href="#" class="menuBoxContentLinkSub"> -'.BOX_HEADING_XSBOOSTER.'</a><ul>';
        if ($admin_access['configuration'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=list", '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_XSBOOSTER_LISTAUCTIONS.'</a></li>';
        if ($admin_access['configuration'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=add", '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_XSBOOSTER_ADDAUCTIONS.'</a></li>';
        if ($admin_access['configuration'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_XTBOOSTER."?xtb_module=conf", '', 'NONSSL') . '" class="menuBoxContentLink"> -'.BOX_XSBOOSTER_CONFIG.'</a></li>';
      echo '</ul></li>';
    }
echo endMenue(BOX_HEADING_PARTNER_MODULES);

//---------------------------STATISTIKEN
echo mainMenue(BOX_HEADING_STATISTICS);
    if ($admin_access['stats_products_viewed'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_VIEWED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_VIEWED . '</a></li>';
    if ($admin_access['stats_products_purchased'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_PURCHASED . '</a></li>';
    if ($admin_access['stats_customers'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_STATS_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_STATS_CUSTOMERS . '</a></li>';
    if ($admin_access['stats_sales_report'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SALES_REPORT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SALES_REPORT . '</a></li>';
    if ($admin_access['stats_campaigns'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CAMPAIGNS_REPORT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CAMPAIGNS_REPORT . '</a></li>';
echo endMenue(BOX_HEADING_STATISTICS);

//---------------------------HILFSPROGRAMME
echo mainMenue(BOX_HEADING_TOOLS);
    if ($admin_access['module_newsletter'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_MODULE_NEWSLETTER) . '" class="menuBoxContentLink"> -' . BOX_MODULE_NEWSLETTER . '</a></li>';
    if ($admin_access['content_manager'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CONTENT_MANAGER) . '" class="menuBoxContentLink"> -' . BOX_CONTENT . '</a></li>';
    if ($admin_access['removeoldpics'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_REMOVEOLDPICS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_REMOVEOLDPICS . '</a></li>';
    if ($admin_access['backup'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_BACKUP) . '" class="menuBoxContentLink"> -' . BOX_BACKUP . '</a></li>';
    if ($admin_access['banner_manager'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_BANNER_MANAGER) . '" class="menuBoxContentLink"> -' . BOX_BANNER_MANAGER . '</a></li>';
    if ($admin_access['server_info'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SERVER_INFO) . '" class="menuBoxContentLink"> -' . BOX_SERVER_INFO . '</a></li>';
    if ($admin_access['blz_update'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_BLZ_UPDATE) . '" class="menuBoxContentLink"> -' . BOX_BLZ_UPDATE . '</a></li>';
    if ($admin_access['whos_online'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_WHOS_ONLINE) . '" class="menuBoxContentLink"> -' . BOX_WHOS_ONLINE . '</a></li>';
    if ($admin_access['csv_backend'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CSV_BACKEND) . '" class="menuBoxContentLink"> -' . BOX_IMPORT . '</a></li>';
    if ($admin_access['parcel_carriers'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS) . '" class="menuBoxContentLink"> -' . BOX_PARCEL_CARRIERS . '</a></li>';
    if ($admin_access['logs'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_LOGS) . '" class="menuBoxContentLink"> -' . BOX_LOGS . '</a></li>';
echo endMenue(BOX_HEADING_TOOLS);

//---------------------------GUTSCHEINE
if (ACTIVATE_GIFT_SYSTEM=='true') {
echo mainMenue(BOX_HEADING_GV_ADMIN);
    if ($admin_access['coupon_admin'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_COUPON_ADMIN, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_COUPON_ADMIN . '</a></li>';
    if ($admin_access['gv_queue'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_GV_QUEUE, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_QUEUE . '</a></li>';
    if ($admin_access['gv_mail'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_GV_MAIL, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_MAIL . '</a></li>';
    if ($admin_access['gv_sent'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_GV_SENT, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_ADMIN_SENT . '</a></li>';
    if ($admin_access['gv_customers'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_GV_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GV_CUSTOMERS . '</a></li>';
echo endMenue(BOX_HEADING_GV_ADMIN); 
}

//---------------------------LAND / STEUER
echo mainMenue(BOX_HEADING_ZONE);
    if ($admin_access['languages'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_LANGUAGES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_LANGUAGES . '</a></li>';
    if ($admin_access['countries'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_COUNTRIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_COUNTRIES . '</a></li>';
    if ($admin_access['currencies'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CURRENCIES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CURRENCIES. '</a></li>';
    if ($admin_access['zones'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ZONES . '</a></li>';
    if ($admin_access['geo_zones'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_GEO_ZONES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_GEO_ZONES . '</a></li>';
    if ($admin_access['tax_classes'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_TAX_CLASSES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_TAX_CLASSES . '</a></li>';
    if ($admin_access['tax_rates'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_TAX_RATES, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_TAX_RATES . '</a></li>';
echo endMenue(BOX_HEADING_ZONE);

//---------------------------KONFIGURATION
echo mainMenue(BOX_HEADING_CONFIGURATION);
    if ($admin_access['configuration'] == '1') {
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=1', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_1 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=1000', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_1000 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=2', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_2 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=3', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_3 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=4', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_4 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=5', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_5 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=7', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_7 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=8', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_8 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=9', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_9 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=12', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_12 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=13', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_13 . '</a></li>';
    }
    if ($admin_access['orders_status'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_ORDERS_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS_STATUS . '</a></li>';
    if (ACTIVATE_SHIPPING_STATUS=='true' && $admin_access['shipping_status'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_SHIPPING_STATUS . '</a></li>';
    if ($admin_access['products_vpe'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_PRODUCTS_VPE, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_PRODUCTS_VPE . '</a></li>';
    if ($admin_access['campaigns'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_CAMPAIGNS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CAMPAIGNS . '</a></li>';
    if ($admin_access['cross_sell_groups'] == '1') echo '<li><a href="' . xtc_href_link(FILENAME_XSELL_GROUPS, '', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_ORDERS_XSELL_GROUP . '</a></li>';
echo endMenue(BOX_HEADING_CONFIGURATION);

//---------------------------KONFIGURATION 2
echo mainMenue(BOX_HEADING_CONFIGURATION2);
    if ($admin_access['shop_offline'] == '1') echo '<li><a href="' . xtc_href_link('shop_offline.php', '', 'NONSSL') . '" class="menuBoxContentLink"> -'.'Shop online/offline'.'</a></li>';
    if ($admin_access['configuration'] == '1') {
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=10', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_10 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=11', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_11 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=14', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_14 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=15', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_15 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=16', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_16 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=17', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_17 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=18', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_18 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=19', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_19 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=22', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_22 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=40', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_40 . '</a></li>'; 
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=24', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_24 . '</a></li>';
      echo '<li><a href="' . xtc_href_link(FILENAME_CONFIGURATION, 'gID=25', 'NONSSL') . '" class="menuBoxContentLink"> -' . BOX_CONFIGURATION_25 . '</a></li>';
    }
echo endMenue(BOX_HEADING_CONFIGURATION2);

echo '</ul>'; 
echo '</div>';

//end----------------------------HTML----------------------------------------------------------------------------------