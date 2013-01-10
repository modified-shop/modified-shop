<?php
/* -----------------------------------------------------------------------------------------
   $Id: print_order.php 3792 2012-10-18 11:26:51Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (print_order.php,v 1.1 2003/08/19); www.nextcommerce.org
   (c) 2006 XT-Commerce (print_order.php 1166 2005-08-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');
  // include needed functions
  require_once(DIR_FS_INC .'xtc_get_attributes_model.inc.php');
  require_once(DIR_FS_INC .'xtc_not_null.inc.php');
  require_once(DIR_FS_INC .'xtc_format_price_order.inc.php');

  $smarty = new Smarty;

  // BOF - DokuMan - 2011-12-08 - get store name for display in letter box of packing slip
  $query_store_name=xtc_db_query("-- admin/print_order.php
                                  SELECT configuration_value AS store_name
                                    FROM " . TABLE_CONFIGURATION . "
                                   WHERE configuration_key='STORE_NAME'
                                   LIMIT 1");

  while($row = xtc_db_fetch_array($query_store_name)){
    $smarty->assign('store_name', $row['store_name']);
  }
  // EOF - DokuMan - 2011-12-08 - get store name for display in letter box of packing slip

  // get order data
  include(DIR_WS_CLASSES . 'order.php');
  $order = new order((int)$_GET['oID']);

  $smarty->assign('address_label_customer',xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));
  $smarty->assign('address_label_shipping',xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
  $smarty->assign('address_label_payment',xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));
  $smarty->assign('csID',$order->customer['csID']);
  $smarty->assign('vat_id',$order->customer['vat_id']);

  // get products data
  include_once(DIR_FS_CATALOG.DIR_WS_CLASSES .'xtcPrice.php');
  $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);

  $order_total = $order->getTotalData($order->info['order_id']);
  $order_data = $order->getOrderData($order->info['order_id']);

  $smarty->assign('order_data', $order_data);
  $smarty->assign('order_total', $order_total['data']);

  // assign language to template for caching
  $languages_query = xtc_db_query("select code, language_charset from " . TABLE_LANGUAGES . " WHERE directory ='". $order->info['language'] ."'");
  $langcode = xtc_db_fetch_array($languages_query);
  $smarty->assign('langcode', $langcode['code']);
  $smarty->assign('charset', $langcode['language_charset']);
  $smarty->assign('language', $order->info['language']);

  $smarty->assign('logo_path',HTTP_SERVER . DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
  $smarty->assign('oID',$order->info['order_id']);
  if ($order->info['payment_method']!='' && $order->info['payment_method']!='no_payment') {
    include(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
    $payment_method=constant(strtoupper('MODULE_PAYMENT_'.$order->info['payment_method'].'_TEXT_TITLE'));
    $smarty->assign('PAYMENT_METHOD',$payment_method);
  }
  $smarty->assign('COMMENTS', $order->info['comments']);
  $smarty->assign('DATE',xtc_date_long($order->info['date_purchased']));

  // dont allow cache
  $smarty->caching = false;
  $smarty->template_dir=DIR_FS_CATALOG.'templates';
  $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
  $smarty->config_dir=DIR_FS_CATALOG.'lang';
  $smarty->display(CURRENT_TEMPLATE . '/admin/print_order.html');
?>