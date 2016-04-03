<?php
/* -----------------------------------------------------------------------------------------
   $Id: send_order.php 1510 2010-11-22 13:24:04Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003      nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
  die('Direct Access to this location is not allowed.');
}

// check if customer is allowed to send this order!
$order_query_check = xtc_db_query("SELECT customers_id
                                     FROM ".TABLE_ORDERS."
                                    WHERE orders_id='".(int)$insert_id."'");
$order_check = xtc_db_fetch_array($order_query_check);

if ($_SESSION['customer_id'] == $order_check['customers_id'] || $send_by_admin) { // Send Order by Admin

  $order = new order($insert_id);

  if (isset($send_by_admin)) {
    $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);
  }

  $smarty->assign('address_label_customer', xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'));
  $smarty->assign('address_label_shipping', xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
  $smarty->assign('address_label_payment', xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'));
  $smarty->assign('csID', $order->customer['csID']);

  $order_total = $order->getTotalData($insert_id); //ACHTUNG für Bestellbestätigung  aus Admin Funktion in admin/includes/classes/order.php
  $smarty->assign('order_data', $order->getOrderData($insert_id)); //ACHTUNG für Bestellbestätigung  aus Admin Funktion in admin/includes/classes/order.php
  $smarty->assign('order_total', $order_total['data']);

  // assign language to template for caching
  $smarty->assign('language', $order->info['language']);
  $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
  $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
  $smarty->assign('oID', $order->info['order_id']);

  //shipping method
  $shipping_class = explode('_', $order->info['shipping_class']);
  if ($order->info['shipping_class'] != '' && $shipping_class[0] != 'free') {
    include (DIR_FS_CATALOG . 'lang/'.$order->info['language'].'/modules/shipping/'.$shipping_class[0].'.php');
    $shipping_method = constant(strtoupper('MODULE_SHIPPING_'.$shipping_class[0].'_TEXT_TITLE'));
  } else {
    include (DIR_FS_CATALOG . 'lang/'.$order->info['language'].'/modules/order_total/ot_shipping.php');
    $shipping_method = FREE_SHIPPING_TITLE;
  }
  $smarty->assign('SHIPPING_METHOD', $shipping_method);
  $smarty->assign('SHIPPING_CLASS', $shipping_class[0]);
  
  //payment method
  if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {    
    include_once (DIR_FS_CATALOG . 'lang/'.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
    $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$order->info['payment_method'].'_TEXT_TITLE'));
    $payment_class = $order->info['payment_class'];
  }
  $smarty->assign('PAYMENT_METHOD', $payment_method);
  $smarty->assign('PAYMENT_CLASS', $payment_class);
  
  $smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
  $smarty->assign('NAME', $order->customer['name']);
  $smarty->assign('GENDER', $order->customer['gender']);
  $smarty->assign('CITY', $order->customer['city']);
  $smarty->assign('POSTCODE', $order->customer['postcode']);
  $smarty->assign('STATE', $order->customer['state']);
  $smarty->assign('COUNTRY', $order->customer['country']);
  $smarty->assign('COMPANY', $order->customer['company']);
  $smarty->assign('STREET', $order->customer['street_address']);
  $smarty->assign('FIRSTNAME', $order->customer['firstname']);
  $smarty->assign('LASTNAME', $order->customer['lastname']);

  $smarty->assign('COMMENTS', $order->info['comments']);
  $smarty->assign('EMAIL', $order->customer['email_address']);
  $smarty->assign('PHONE',$order->customer['telephone']);
  $smarty->assign('vatID', $order->customer['vat_id']);

  // PAYMENT MODUL TEXTS
  $payment_method_array = array('eustandardtransfer','moneyorder');
  if (in_array($order->info['payment_method'],$payment_method_array)) {
    $payment_text = defined('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_TEXT_DESCRIPTION') ? constant('MODULE_PAYMENT_'.strtoupper($order->info['payment_method']).'_TEXT_DESCRIPTION') : '';
    $smarty->assign('PAYMENT_INFO_HTML', $payment_text);
    $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", $payment_text));
  }
  
  // Cash on Delivery
  if ($order->info['payment_method'] == 'cod') {
    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_COD_TEXT_INFO);
    $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_COD_TEXT_INFO));
  }
  
  //allow duty-note in email
  if(!is_object($main)) {
    require_once(DIR_FS_CATALOG.'includes/classes/main.php');
    $main = new main();
  }
  $smarty->assign('DELIVERY_DUTY_INFO', $main->getDeliveryDutyInfo($order->delivery['country_iso_2']));

  //absolute image path
  $smarty->assign('img_path', HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.'product_images/'. (defined('SHOW_IMAGES_IN_EMAIL_DIR')? SHOW_IMAGES_IN_EMAIL_DIR : 'thumbnail').'_images/');
  
  // dont allow cache
  $smarty->caching = 0;

  // revocation to email 
  require_once (DIR_FS_INC . 'get_lang_id_by_directory.inc.php');
  $lang_id = ((isset($order->info['languages_id']) && $order->info['languages_id'] != '0')  ? $order->info['languages_id'] : get_lang_id_by_directory($order->info['language']));
  $shop_content_data = $main->getContentData(REVOCATION_ID, $lang_id, $order->info['status']);
  $smarty->assign('REVOCATION_HTML', $shop_content_data['content_text']);
  $smarty->assign('REVOCATION_TXT', $shop_content_data['content_text']);

  // agb to email 
  $shop_content_data = $main->getContentData(3, $lang_id, $order->info['status']);
  $smarty->assign('AGB_HTML', $shop_content_data['content_text']);
  $smarty->assign('AGB_TXT', $shop_content_data['content_text']);

  if (DOWNLOAD_ENABLED == 'true') {
    $send_order = true;
    $_GET['order_id'] = $order->info['order_id'];
    include (DIR_WS_MODULES.'downloads.php');
  }

  //email attachments
  $email_attachments = defined('EMAIL_BILLING_ATTACHMENTS') ? EMAIL_BILLING_ATTACHMENTS : '';

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/send_order/','php') as $file) require ($file);

  $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/order_mail.html');
  $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$order->info['language'].'/order_mail.txt');
  
  // create subject
  $order_subject = str_replace('{$nr}', $insert_id, EMAIL_BILLING_SUBJECT_ORDER);
  $order_subject = str_replace('{$date}', xtc_date_long($order->info['date_purchased']), $order_subject); // Tomcraft - 2011-12-28 - Use date_puchased instead of current date in E-Mail subject
  $order_subject = str_replace('{$lastname}', $order->customer['lastname'], $order_subject);
  $order_subject = str_replace('{$firstname}', $order->customer['firstname'], $order_subject);

  // send mail to admin
  xtc_php_mail(EMAIL_BILLING_ADDRESS,
               EMAIL_BILLING_NAME,
               EMAIL_BILLING_ADDRESS,
               STORE_NAME,
               EMAIL_BILLING_FORWARDING_STRING,
               $order->customer['email_address'],
               $order->customer['firstname'].' '.$order->customer['lastname'],
               $email_attachments,
               '',
               $order_subject,
               $html_mail,
               $txt_mail
               );

  // send mail to customer
  if (SEND_EMAILS == 'true' || $send_by_admin) {
    xtc_php_mail(EMAIL_BILLING_ADDRESS,
                 EMAIL_BILLING_NAME,
                 $order->customer['email_address'],
                 $order->customer['firstname'].' '.$order->customer['lastname'],
                 '',
                 EMAIL_BILLING_REPLY_ADDRESS,
                 EMAIL_BILLING_REPLY_ADDRESS_NAME,
                 $email_attachments,
                 '',
                 $order_subject,
                 $html_mail,
                 $txt_mail
                 );
                 
    if (isset($sepa_html_mail)) {
      xtc_php_mail(EMAIL_BILLING_ADDRESS,
                   EMAIL_BILLING_NAME,
                   $banktransfer_owner_email,
                   '',
                   '',
                   EMAIL_BILLING_REPLY_ADDRESS,
                   EMAIL_BILLING_REPLY_ADDRESS_NAME,
                   '',
                   '',
                   EMAIL_BILLING_SUBJECT,
                   $sepa_html_mail,
                   $sepa_txt_mail
                 );
    }
  }

  if (AFTERBUY_ACTIVATED == 'true') {
    require_once (DIR_WS_CLASSES.'afterbuy.php');
    $aBUY = new xtc_afterbuy_functions($insert_id);
    if ($aBUY->order_send())
      $aBUY->process_order();
  }

  if (isset($send_by_admin)) {
    $customer_notified = '1';
    $orders_status_id = '1';
    //Comment out the next line for setting  the $orders_status_id= '1 '- Auskommentieren der nächste Zeile, um die $orders_status_id = '1' zu setzen
    $orders_status_id = ($order->info['orders_status']  < 1) ? '1' : $order->info['orders_status'];

    $sql_data_array = array(
      'orders_status' => (int)$orders_status_id,
      'last_modified' => 'now()'
    );     
    xtc_db_perform(TABLE_ORDERS,$sql_data_array,'update',"orders_id = '".(int)$insert_id."'");
    
    $sql_data_array = array(
      'orders_id' => (int)$insert_id,
      'orders_status_id' => (int)$orders_status_id,
      'date_added' => 'now()',
      'customer_notified' => $customer_notified,
      'comments' => encode_utf8(COMMENT_SEND_ORDER_BY_ADMIN),
    );  
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$sql_data_array);
    
    $messageStack->add_session(SUCCESS_ORDER_SEND, 'success');

    if (isset($_GET['site']) && $_GET['site'] == 1) {
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.$_GET['oID'].'&action=edit'));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.$_GET['oID']));
    }
  }
} else {
  $smarty->assign('ERROR', 'You are not allowed to view this order!');
  $smarty->display(CURRENT_TEMPLATE.'/module/error_message.html');
}
?>