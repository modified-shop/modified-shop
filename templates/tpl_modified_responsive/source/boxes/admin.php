<?php
/* -----------------------------------------------------------------------------------------
   $Id: admin.php 96 2012-12-02 12:02:56Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (admin.php, v1 2002/08/28 02:14:35); www.oscommerce.com
   (c) 2003 nextcommerce (admin.php,v 1.12 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (admin.php,v 1.12 2006/10/03); www.xtcommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');
  
// newsfeed
require_once(DIR_FS_INC.'get_newsfeed.inc.php');
get_newsfeed();

$admin_access = array();
if ($_SESSION['customers_status']['customers_status'] == '0') {
  $admin_access_query = xtc_db_query("SELECT * FROM " . TABLE_ADMIN_ACCESS . " WHERE customers_id = ".(int)$_SESSION['customer_id']);
  $admin_access = xtc_db_fetch_array($admin_access_query); 
}

// offline/online
require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php'); 
if (xtc_get_shop_conf('SHOP_OFFLINE') == 'checked') {
  $box_smarty->assign('SHOP_OFFLINE', true);
  if ($admin_access['shop_offline'] == '1') {
    $box_smarty->assign('SHOP_OFFLINE_LINK', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'shop_offline.php','', 'NONSSL'));
  }
}

// newsfeed
if ($admin_access['newsfeed'] == '1') {
  $num_news_query = xtc_db_query("SELECT count(*) as total FROM newsfeed WHERE news_date > '".NEWSFEED_LAST_READ."'");
  $num_news = xtc_db_fetch_array($num_news_query);
  $box_smarty->assign('NEWSFEED_COUNT', $num_news['total']);
  $box_smarty->assign('NEWSFEED', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'newsfeed.php','', 'NONSSL'));
}

// update check
if ($admin_access['check_update'] == '1') {
  require_once(DIR_FS_INC.'check_version_update.inc.php');
  $update_array = check_version_update();
  $box_smarty->assign('UPDATE_COUNT', $update_array['update']);
  $box_smarty->assign('UPDATE', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'check_update.php','', 'NONSSL'));
}

// language
if ($admin_access['languages'] == '1' || $admin_access['categories'] == '1') {
  $languages_string = '';
  if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    require_once(DIR_WS_CLASSES . 'language.php');
    $lng = new language;
  }
  if (count($lng->catalog_languages) > 1) {
    reset($lng->catalog_languages);
    while (list($key, $value) = each($lng->catalog_languages)) {
      $lng_link_txt = file_exists('lang/' .  $value['directory'] .'/' . $value['image']) ? xtc_image('lang/' .  $value['directory'] .'/' . $value['image'], $value['name']) : $value['name'];
      $languages_string .= '&nbsp;<a href="' . xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type) . '">' . $lng_link_txt . '</a> ';
    }
  }
  $box_smarty->assign('LANGUAGES', $languages_string);
}

// orders
if ($admin_access['orders'] == '1') {
  $orders_contents = '';
  $orders_status_validating = xtc_db_num_rows(xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS ." WHERE orders_status ='0'"));
  $orders_contents .='<li><a href="'.xtc_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status=0', 'NONSSL').'"><em>'.$orders_status_validating.'</em>'.TEXT_VALIDATING.'</a></li>';

  $orders_status_query = xtc_db_query("SELECT orders_status_name, orders_status_id FROM ".TABLE_ORDERS_STATUS." WHERE language_id = '".(int)$_SESSION['languages_id']."' ORDER BY sort_order");
  while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
    $orders_pending_query = xtc_db_query("SELECT count(*) AS count FROM ".TABLE_ORDERS." WHERE orders_status = '".$orders_status['orders_status_id']."'");
    $orders_pending = xtc_db_fetch_array($orders_pending_query);
    $orders_contents .= '<li><a href="'.xtc_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status='.$orders_status['orders_status_id'], 'NONSSL').'"><em>'.$orders_pending['count'].'</em>'.$orders_status['orders_status_name'].'</a></li>';
  }
  $box_smarty->assign('ORDERS', xtc_href_link_admin(FILENAME_ORDERS,'', 'NONSSL'));
  $box_smarty->assign('ORDERS_CONTENT', $orders_contents);
}

// customers
if ($admin_access['customers'] == '1') {
  $customers_query = xtc_db_query("select count(*) as count from ".TABLE_CUSTOMERS);
  $customers = xtc_db_fetch_array($customers_query);
  $box_smarty->assign('CUSTOMERS_INFO', BOX_ENTRY_CUSTOMERS . ' ' . $customers['count']);
  $box_smarty->assign('CUSTOMERS', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'customers.php','', 'NONSSL'));
}

// categories/product/attributes
if ($admin_access['categories'] == '1') {
  // categories
  if (isset($_GET['cPath'])) {
    $cPath_array = xtc_parse_category_path($_GET['cPath']);
    $cid = end($cPath_array);
    $cpath = xtc_get_category_path($cid);
    $box_smarty->assign('EDIT_CATEGORY', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'categories.php', 'action=edit_category&cPath='.$cpath.'&cID='.$cid));
  }
  // product
  if ($product->isProduct() === true) {
    $box_smarty->assign('EDIT_PRODUCT', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'categories.php', 'cPath='.$cPath.'&pID='.$product->data['products_id'].'&action=new_product'));
    $box_smarty->assign('EDIT_XSELL', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'categories.php', 'cpath='.$cPath.'&current_product_id='.$product->data['products_id'].'&action=edit_crossselling'));
  }
  // attributes
  if ($admin_access['new_attributes'] == '1') {
    if ($product->isProduct() === true) {
      $box_smarty->assign('EDIT_PRODUCT_ATTRIBUTES', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'new_attributes.php', 'cpath='.$cPath.'&current_product_id='.$product->data['products_id'].'&action=edit'));
    }
  }
  // product info
  $products_query = xtc_db_query("select count(*) as count from ".TABLE_PRODUCTS." where products_status = '1'");
  $products = xtc_db_fetch_array($products_query);
  $box_smarty->assign('PRODUCTS_INFO', BOX_ENTRY_PRODUCTS . ' ' . $products['count']);
  // reviews info
  $reviews_query = xtc_db_query("select count(*) as count from ".TABLE_REVIEWS);
  $reviews = xtc_db_fetch_array($reviews_query);
  $box_smarty->assign('REVIEWS_INFO', BOX_ENTRY_REVIEWS . ' ' . $reviews['count']);
  
  $box_smarty->assign('CATEGORIES', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'categories.php','', 'NONSSL'));
}

// content manager
if ($admin_access['content_manager'] == '1') {
  if (isset($_GET['coID'])) {
    $box_smarty->assign('EDIT_CONTENT', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'content_manager.php', 'action=edit&coID='.(int)$_GET['coID']));
  }
  $box_smarty->assign('CONTENT_MANAGER', xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'content_manager.php','', 'NONSSL'));
}

// start
if ($admin_access['start'] == '1') {
  $box_smarty->assign('START', xtc_href_link_admin(FILENAME_START,'', 'NONSSL'));
}

$box_smarty->caching = 0;
$box_admin = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_admin.html');
$smarty->assign('box_ADMIN',$box_admin);
?>