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

if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
	// Get last order id for checkout_success
	$orders_query = xtc_db_query("select orders_id, orders_status from ".TABLE_ORDERS." where customers_id = '".$_SESSION['customer_id']."' order by orders_id desc limit 1");
	$orders = xtc_db_fetch_array($orders_query);
	$last_order = $orders['orders_id'];
	$order_status = $orders['orders_status'];
} else {
	$last_order = (int)$_GET['order_id'];
	$orders_query = xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS." WHERE orders_id = '".$last_order."'");
	$orders = xtc_db_fetch_array($orders_query);
	$order_status = $orders['orders_status'];
}

// check if allowed to download
$allowed_status = explode(',', DOWNLOAD_MIN_ORDERS_STATUS);
if (!in_array($order_status, $allowed_status)) {
	$module_smarty->assign('dl_prevented', 'true');
}

// Get all downloadable products in that order
$downloads_query = xtc_db_query("select o.customers_id,
                                        o.customers_email_address,
                                        op.products_name, 
                                        opd.orders_products_download_id, 
                                        opd.orders_products_filename, 
                                        opd.download_count,
                                        opd.orders_products_id,
                                        if(opd.download_maxdays = 0, current_date, date(o.date_purchased)) + interval opd.download_maxdays + 1 day - interval 1 second download_expiry 
                                   from ".TABLE_ORDERS." o
                                   join ".TABLE_ORDERS_PRODUCTS." op 
                                        on op.orders_id = o.orders_id
                                   join ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                        on opd.orders_products_id = op.orders_products_id
                                  where o.customers_id = '".$_SESSION['customer_id']."' 
                                    and o.orders_id = '".$last_order."'
                                    and opd.orders_products_filename != ''");

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
    $dl[$jj]['pic_link'] = xtc_href_link(FILENAME_DOWNLOAD, 'order='.$last_order.'&id='.$downloads['orders_products_download_id'].'&key='.md5($last_order.$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']));
    $dl[$jj]['download_link'] = '<a href="'.xtc_href_link(FILENAME_DOWNLOAD, 'order='.$last_order.'&id='.$downloads['orders_products_download_id'].'&key='.md5($last_order.$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename'])).'">'.$downloads['products_name'].'</a>';
    $dl[$jj]['date'] = xtc_date_long($downloads['download_expiry']);
    $dl[$jj]['count'] = $downloads['download_count'];
    $jj ++;
  }
  $module_smarty->assign('dl', (isset($dl) ? $dl : array()));
}

$module_smarty->assign('language', $_SESSION['language']);
$module_smarty->caching = 0;

if ($send_order) {
  $module_smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
  $module_txt = $module_smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'downloads.txt');
  $module_html = $module_smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'downloads.html');
  $smarty->assign('downloads_content_html', $module_html);
  $smarty->assign('downloads_content_txt', $module_txt);
} else {
  $module_smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
  $module = $module_smarty->fetch(CURRENT_TEMPLATE.'/module/downloads.html');
  $smarty->assign('downloads_content', $module);
}
?>