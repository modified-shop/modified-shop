<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
   
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  if ($order->info['orders_status'] != $status || $comments != '' || $email_preview) {
    if (!$email_preview) {  
      if (defined('MODULE_PAYMENT_BILLPAY_STATUS') && MODULE_PAYMENT_BILLPAY_STATUS == 'True') {
          require_once(DIR_FS_EXTERNAL . 'billpay/utils/billpay_status_requests.php');
      }
      xtc_db_query("UPDATE ".TABLE_ORDERS."
                       SET orders_status = ".$status.",
                           last_modified = now()
                     WHERE orders_id = '".$oID."'");
    }

    $customer_notified = 0;
    if ($_POST['notify'] == 'on' || $email_preview) {
      $notify_comments = ($_POST['notify_comments'] == 'on') ? $comments : '';        
      //fallback gender modified < 2.00
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
      $smarty->assign('FIRSTNAME',$order->customer['firstname'] != '' ? $order->customer['firstname'] : $order->customer['name']);
      $smarty->assign('LASTNAME',$order->customer['lastname'] != '' ? $order->customer['lastname'] : $order->customer['name']);
    
      $smarty->assign('order', $order);
      $smarty->assign('order_data', $order->getOrderData($oID));

      $smarty->assign('tpl_path',DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      $smarty->assign('NAME', $order->customer['name']);
      $smarty->assign('ORDER_NR', $order->info['order_id']);
      $smarty->assign('ORDER_ID', $oID);
      //send no order link to customers with guest account
      if ($order->customer['status'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
        $smarty->assign('ORDER_LINK', xtc_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id='.$oID, 'SSL'));
      }
      // track & trace
      $tracking_array = get_tracking_link($oID, $lang_code, ((isset($_POST['tracking_id']) && is_array($_POST['tracking_id'])) ? $_POST['tracking_id'] : array('0')));
      $smarty->assign('PARCEL_COUNT', count($tracking_array));
      $smarty->assign('PARCEL_ARRAY', $tracking_array);
    
      $smarty->assign('ORDER_DATE', xtc_date_long($order->info['date_purchased']));
      $smarty->assign('NOTIFY_COMMENTS', nl2br($notify_comments));
      $smarty->assign('ORDER_STATUS', $orders_status_array[$status]);

      // assign language
      $smarty->assign('language', $order->info['language']);
    
      // set dirs manual
      $smarty->caching = false;
      $smarty->template_dir = DIR_FS_CATALOG.'templates';
      $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
      $smarty->config_dir = DIR_FS_CATALOG.'lang';
    
      $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.html');
      $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.txt');
      $order_subject_search = array('{$nr}', '{$date}', '{$lastname}', '{$firstname}');
      $order_subject_replace = array($oID, xtc_date_long($order->info['date_purchased']), $order->customer['lastname'], $order->customer['firstname']);
      $order_subject = str_replace($order_subject_search, $order_subject_replace, EMAIL_BILLING_SUBJECT);

      //EMAIL PREVIEW
      include ('includes/modules/email_preview/email_preview.php');
    
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
                 
      //send copy to admin
      if (defined('STATUS_EMAIL_SENT_COPY_TO_ADMIN') && STATUS_EMAIL_SENT_COPY_TO_ADMIN == 'true') {
        xtc_php_mail(EMAIL_BILLING_ADDRESS,
                     EMAIL_BILLING_NAME,
                     EMAIL_BILLING_ADDRESS,
                     STORE_NAME,
                     EMAIL_BILLING_FORWARDING_STRING,
                     $order->customer['email_address'],
                     $order->customer['name'],
                     '',
                     '',
                     $order_subject,
                     $html_mail,
                     $txt_mail
                     );
      }

      $customer_notified = 1;
    }
    
    $sql_data_array = array('orders_id' => $oID,
                            'orders_status_id' => $status,
                            'date_added' => 'now()',
                            'customer_notified' => $customer_notified,
                            'comments' => $comments,
                            'comments_sent' => ($_POST['notify_comments'] == 'on' ? 1 : 0)
                            );
    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY,$sql_data_array);
    $order_updated = true;
  }
?>