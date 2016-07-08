<?php

/* -----------------------------------------------------------------------------------------
   $Id: downloads.php 4245 2013-01-11 14:26:03Z gtb-modified $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(downloads.php,v 1.2 2003/02/12); www.oscommerce.com 
   (c) 2003	 nextcommerce (downloads.php,v 1.6 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// ibclude the needed functions
if (!function_exists('xtc_date_long')) {
  require_once (DIR_FS_INC.'xtc_date_long.inc.php');
}

$module_smarty = new Smarty;

$customer_id = (int)$_SESSION['customer_id'];
$language = $_SESSION['language'];

if (isset($send_by_admin)) {
  $last_order = $insert_id;
  $orders_query = xtc_db_query("SELECT orders_status,
                                       customers_id,
                                       language
                                  FROM ".TABLE_ORDERS." 
                                 WHERE orders_id = '".$last_order."'");
  $orders = xtc_db_fetch_array($orders_query);
  $customer_id = $orders['customers_id'];
  $language = $orders['language'];
  $order_status = $orders['orders_status'];
} elseif (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
  // Get last order id for checkout_success
  $orders_query = xtc_db_query("SELECT orders_id, 
                                       orders_status 
                                  FROM ".TABLE_ORDERS." 
                                 WHERE customers_id = '".(int)$customer_id."' 
                              ORDER BY orders_id desc limit 1");
  $orders = xtc_db_fetch_array($orders_query);
  $last_order = $orders['orders_id'];
  $order_status = $orders['orders_status'];
} else {
  $last_order = (int)$_GET['order_id'];
  $orders_query = xtc_db_query("SELECT orders_status 
                                  FROM ".TABLE_ORDERS." 
                                 WHERE orders_id = '".$last_order."'");
  $orders = xtc_db_fetch_array($orders_query);
  $order_status = $orders['orders_status'];
}

// check if allowed to download
$allowed_status = explode(',', DOWNLOAD_MIN_ORDERS_STATUS);
if (!in_array($order_status, $allowed_status)) {
  $module_smarty->assign('dl_prevented', 'true');
}

// Get all downloadable products in that order
$downloads_query = xtc_db_query("SELECT o.customers_id,
                                        o.customers_email_address,
                                        op.products_name, 
                                        opd.orders_products_download_id, 
                                        opd.orders_products_filename, 
                                        opd.download_count,
                                        opd.orders_products_id,
                                        if(opd.download_maxdays = 0, current_date, date(o.date_purchased)) + interval opd.download_maxdays + 1 day - interval 1 second download_expiry 
                                   FROM ".TABLE_ORDERS." o
                                   JOIN ".TABLE_ORDERS_PRODUCTS." op 
                                        ON op.orders_id = o.orders_id
                                   JOIN ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                        ON opd.orders_products_id = op.orders_products_id
                                  WHERE o.customers_id = '".(int)$customer_id."' 
                                    AND o.orders_id = '".$last_order."'
                                    AND opd.orders_products_filename != ''");

if (xtc_db_num_rows($downloads_query) > 0) {
  $jj = 0;
  while ($downloads = xtc_db_fetch_array($downloads_query)) {
    // The link will appear only if:
    // - Download remaining count is > 0, AND
    // - The file is present in the DOWNLOAD directory, AND EITHER
    // - No expiry date is enforced (maxdays == 0), OR
    // - The expiry date is not reached
    if ($downloads['download_count'] > 0 && 
        strtotime($downloads['download_expiry']) > time() && 
        file_exists(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']) && 
        in_array($order_status, $allowed_status))
    {
      $dl[$jj]['allowed'] = true;
    }
    if (isset($send_by_admin)) {
      require_once(DIR_FS_INC.'xtc_href_link_from_admin.inc.php');
      $dl[$jj]['pic_link'] = xtc_href_link_from_admin(FILENAME_DOWNLOAD, 'order='.$last_order.'&id='.$downloads['orders_products_download_id'].'&key='.md5($last_order.$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']), 'NONSSL', false);    
    } else {
      $dl[$jj]['pic_link'] = xtc_href_link(FILENAME_DOWNLOAD, 'order='.$last_order.'&id='.$downloads['orders_products_download_id'].'&key='.md5($last_order.$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']));
    }
    $dl[$jj]['download_link'] = '<a href="'.$dl[$jj]['pic_link'].'">'.$downloads['products_name'].'</a>';
    $dl[$jj]['date'] = xtc_date_long($downloads['download_expiry']);
    $dl[$jj]['count'] = $downloads['download_count'];
    $jj ++;
  }
  $module_smarty->assign('dl', (isset($dl) ? $dl : array()));
}

$module_smarty->assign('language', $language);
$module_smarty->caching = 0;

if ($send_order) {
  if (isset($send_by_admin)) {
    $module_smarty->template_dir = DIR_FS_CATALOG.'templates';
    $module_smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
    $module_smarty->config_dir = DIR_FS_CATALOG.'lang';
  }
  $module_smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
  $module_txt = $module_smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$language.'/downloads.txt');
  $module_html = $module_smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$language.'/downloads.html');
  $smarty->assign('downloads_content_html', $module_html);
  $smarty->assign('downloads_content_txt', $module_txt);
} else {
  $module_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/downloads.html');
  $smarty->assign('downloads_content', $module);
}
?>