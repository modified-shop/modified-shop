<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License 
  --------------------------------------------------------------*/

chdir('../../');
require_once('includes/application_top_callback.php');

// include needed classes
require_once(DIR_WS_CLASSES.'order.php');

// parse callback
$request = json_decode(file_get_contents("php://input"), true);

if (MODULE_SHIPCLOUD_LOG == 'True') {
  error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' ' . print_r($request, true) . "\n", 3, DIR_FS_LOG.'mod_shipcloud_notification_' .date('Y-m-d') .'.log');
}

if (is_array($request) && count($request) > 0) {
  $orders_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_ORDERS_TRACKING." ortr
                                  JOIN ".TABLE_CARRIERS." ca
                                       ON ortr.carrier_id = ca.carrier_id
                                 WHERE ortr.sc_id = '".xtc_db_input($request['data']['id'])."'");
  if (xtc_db_num_rows($orders_query) > 0) {
    $orders = xtc_db_fetch_array($orders_query);
  
    // init order
    $order = new order($orders['orders_id']);
        
    // get order language
    $lang_query = xtc_db_query("SELECT languages_id, 
                                       language_charset,
                                       code
                                  FROM " . TABLE_LANGUAGES . "
                                 WHERE directory = '" . $order->info['language'] . "'");
    $lang_array = xtc_db_fetch_array($lang_query);
    $lang = $lang_array['languages_id'];
    $lang_code = $lang_array['code'];
    $lang_charset = $lang_array['language_charset'];

    // language translations
    require (DIR_WS_LANGUAGES.$order->info['language'].'/'. $order->info['language'] .'.php');
    require (DIR_WS_LANGUAGES.$order->info['language'].'/modules/system/shipcloud.php');
  
    $customer_notified = 0;
    if (MODULE_SHIPCLOUD_EMAIL == 'True' && MODULE_SHIPCLOUD_EMAIL_TYPE == 'Shop') {
      $smarty = new Smarty;
      if (!isset($order->customer['gender']) || empty($order->customer['gender'])) {
        $gender_query = xtc_db_query("SELECT customers_gender
                                        FROM " . TABLE_CUSTOMERS . "
                                       WHERE customers_id = '" .$order->customer['id']. "'");
        $gender_array = xtc_db_fetch_array($gender_query);
        $order->customer['gender'] = $gender_array['customers_gender'];
      } 
      if ($order->customer['gender'] == 'f') {
        $smarty->assign('GENDER', FEMALE);
      } elseif ($order->customer['gender'] == 'm') {
        $smarty->assign('GENDER', MALE);
      } else {
        $smarty->assign('GENDER', '');
      }
      $smarty->assign('LASTNAME', $order->customer['lastname'] != '' ? $order->customer['lastname'] : $order->customer['name']);
    
      // assign language to template for caching
      $smarty->assign('language', $order->info['language']);
      $smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      $smarty->assign('NAME', $order->customer['name']);
      $smarty->assign('ORDER_NR', $order->info['order_id']);
      $smarty->assign('ORDER_ID', $order->info['order_id']);
      //send no order link to customers with guest account
      if ($order->customer['status'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
        $smarty->assign('ORDER_LINK', xtc_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id='.$oID, 'SSL', false));
      }
      // track & trace
      $parcel_link = array(array('carrier_name' => $orders['carrier_name'],
                                 'parcel_id' => $orders['parcel_id'],
                                 'tracking_link' => str_replace(array('$1', '$2'), array($orders['parcel_id'], $lang_code), $orders['carrier_tracking_link']),
                                 'tracking_id' => $orders['tracking_id']));
      $smarty->assign('PARCEL_COUNT', 1);
      $smarty->assign('PARCEL_ARRAY', $parcel_link);
    
      $smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased']));
      $smarty->assign('NOTIFY_COMMENTS', constant(strtoupper($request['type']['value'])));

      $orders_status_query = xtc_db_query("SELECT orders_status_name
                                             FROM ".TABLE_ORDERS_STATUS."
                                            WHERE language_id = '".$lang."'");
      $orders_status = xtc_db_fetch_array($orders_status_query);
      $smarty->assign('ORDER_STATUS', $orders_status['orders_status_name']);
    
      $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.html');
      $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.txt');
      $order_subject_search = array('{$nr}', '{$date}', '{$lastname}', '{$firstname}');
      $order_subject_replace = array($order->info['order_id'], strftime(DATE_FORMAT_LONG), $order->customer['lastname'], $order->customer['firstname']);
      $order_subject = str_replace($order_subject_search, $order_subject_replace, EMAIL_BILLING_SUBJECT);
            
      xtc_php_mail(EMAIL_BILLING_ADDRESS,
                   EMAIL_BILLING_NAME,
                   $order->customer['email_address'],
                   $order->customer['name'],
                   '',
                   EMAIL_BILLING_REPLY_ADDRESS,
                   EMAIL_BILLING_REPLY_ADDRESS_NAME,
                   '',
                   '',
                   $order_subject,
                   $html_mail,
                   $txt_mail
                   );

      $customer_notified = 1;
    }

    $sql_data_array = array('orders_id' => $order->info['orders_id'],
                            'orders_status_id' => $order->info['orders_status_id'],
                            'date_added' => 'now()',
                            'customer_notified' => $customer_notified,
                            'comments' => decode_htmlentities(constant(strtoupper($request['type']['value']))),
                            'comments_sent' => $customer_notified
                            );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  }
}
?>