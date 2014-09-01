<?php
  /*-------------------------------------------------------------
   $Id: orders.php 3554 2012-08-29 09:48:14Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(shopping_cart.php,v 1.71 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (shopping_cart.php,v 1.24 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   OSC German Banktransfer v0.85a Autor:  Dominik Guder <osc@guder.org>
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr
   credit card encryption functions for the catalog module
   BMC 2003 for the CC CVV Module

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');
require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'class.phpmailer.php');
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
require_once (DIR_FS_INC.'xtc_add_tax.inc.php');
require_once (DIR_FS_INC.'changedataout.inc.php');
require_once (DIR_FS_INC.'xtc_validate_vatid_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_attributes_model.inc.php');

//split page results
if(!defined('MAX_DISPLAY_ORDER_RESULTS')) {
  define('MAX_DISPLAY_ORDER_RESULTS', 30);
}
//New function
function get_payment_name($payment_method) {
  if (file_exists(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php')){
    include(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php');
    $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$payment_method.'_TEXT_TITLE'));
  }
  return $payment_method;
}

// initiate template engine for mail
$smarty = new Smarty;
require (DIR_WS_CLASSES.'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? xtc_db_prepare_input($_GET['action']) : '');
$oID = isset($_GET['oID']) ? (int) $_GET['oID'] : '';

if (($action == 'edit' || $action == 'update_order') && $oID) {
  $orders_query = xtc_db_query("-- /admin/orders.php
                                  SELECT orders_id
                                    FROM ".TABLE_ORDERS."
                                   WHERE orders_id = '".xtc_db_input($oID)."'");
  $order_exists = true;
  if (!xtc_db_num_rows($orders_query)) {
    $order_exists = false;
    $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
  }
}

//select default fields
$order_select_fields = 'o.orders_id,
                        o.customers_id,
                        o.customers_name,
                        o.customers_company,
                        o.payment_method,
                        o.last_modified,
                        o.date_purchased,
                        o.orders_status,
                        o.currency,
                        o.currency_value,
                        o.afterbuy_success,
                        o.afterbuy_id,
                        o.language,
                        o.delivery_country,
                        o.delivery_country_iso_code_2,
                        ot.text as order_total
                        ';

//admin search bar
if ($action == 'search' && $oID) {
  $orders_query_raw = "-- /admin/orders.php
                     SELECT ".$order_select_fields.",
                            s.orders_status_name
                       FROM ".TABLE_ORDERS." o
                  LEFT JOIN (".TABLE_ORDERS_TOTAL." ot, ".TABLE_ORDERS_STATUS." s)
                         ON (o.orders_id = ot.orders_id AND o.orders_status = s.orders_status_id)
                      WHERE s.language_id = '".(int)$_SESSION['languages_id']."'
                        AND o.orders_id LIKE '%".$oID."%'
                        AND ot.class = 'ot_total'
                   ORDER BY o.orders_id DESC";
  $orders_query = xtc_db_query($orders_query_raw);
  $order_exists = false;
  if (xtc_db_num_rows($orders_query) == 1) {
     $order_exists = true;
     $oID_array = xtc_db_fetch_array($orders_query);
     $oID = $oID_array['orders_id'];
     $_GET['action'] = 'edit';
     $action = 'edit';
     $_GET['oID'] = $oID;
     //$messageStack->add('1 Treffer: ' . $oID, 'notice');
  }
}

require (DIR_WS_CLASSES.'order.php');
if (($action == 'edit' || $action == 'update_order') && $order_exists) {
  $order = new order($oID);
}
// Trying to get property of non-object $order->info
if (isset($order) && is_object($order)) {
  $lang_query = xtc_db_query("-- /admin/orders.php
                                SELECT languages_id, code, image
                                  FROM " . TABLE_LANGUAGES . "
                                 WHERE directory = '" . $order->info['language'] . "'");
  $lang_array = xtc_db_fetch_array($lang_query);
  $lang = $lang_array['languages_id'];
  $lang_code = $lang_array['code'];
}

  if (isset($order) && trim($order->info['language']) == '') $order->info['language'] = $_SESSION['language'];
if (!isset($lang)) $lang = $_SESSION['languages_id'];
if (!isset($lang_code)) $lang_code = $_SESSION['language_code'];

$orders_statuses = array ();
$orders_status_array = array ();
$orders_status_query = xtc_db_query("-- /admin/orders.php
                                       SELECT orders_status_id,
                                              orders_status_name
                                         FROM ".TABLE_ORDERS_STATUS."
                                        WHERE language_id = '".$lang."'");
while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
  $orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
  $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

switch ($action) {
  //BOF - web28 - 2010-03-20 - Send Order by Admin
  case 'send':
    $smarty->template_dir = DIR_FS_CATALOG.'templates';
    $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
    $smarty->config_dir = DIR_FS_CATALOG.'lang';
    $send_by_admin = true;
    $insert_id = $oID;
    define('SEND_BY_ADMIN_PATH', DIR_FS_CATALOG);
    require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
    require_once(DIR_FS_INC.'xtc_href_link_from_admin.inc.php'); //-web28 - 2011-01-20 - LINKFIX
    include (DIR_FS_CATALOG .'send_order.php');
    break;
  //EOF - web28 - 2010-03-20 - Send Order by Admin
  case 'update_order' :
    $status = (int) $_POST['status'];
    $comments = xtc_db_prepare_input($_POST['comments']);
    $order_updated = false;
    $check_status_query = xtc_db_query("-- /admin/orders.php
                                        SELECT customers_name,
                                               customers_email_address,
                                               orders_status,
                                               date_purchased,
                                               customers_id
                                          FROM ".TABLE_ORDERS."
                                         WHERE orders_id = ".$oID
                                      );
    $check_status = xtc_db_fetch_array($check_status_query);
    if ($check_status['orders_status'] != $status || $comments != '') {
      xtc_db_query("-- /admin/orders.php
                    UPDATE ".TABLE_ORDERS."
                       SET orders_status = ".$status.",
                           last_modified = now()
                     WHERE orders_id = ".$oID
                  );
      $customer_notified = 0;
      if ($_POST['notify'] == 'on') {
        $notify_comments = ($_POST['notify_comments'] == 'on') ? $comments : '';        
        $gender_query = xtc_db_query("-- /admin/orders.php
                                      SELECT customers_gender,
                                             customers_lastname
                                        FROM " . TABLE_CUSTOMERS . "
                                       WHERE customers_id = ".$check_status['customers_id']
                                    );
        $gender = xtc_db_fetch_array($gender_query);
        if ($gender['customers_gender']=='f') {
          $smarty->assign('GENDER', FEMALE);
        } elseif ($gender['customers_gender']=='m') {
          $smarty->assign('GENDER', MALE);
        } else {
          $smarty->assign('GENDER', '');
        }
        $smarty->assign('LASTNAME',$gender['customers_lastname']);

        // assign language to template for caching
        $smarty->assign('language', $order->info['language']);
        $smarty->caching = false;
        // set dirs manual
        $smarty->template_dir = DIR_FS_CATALOG.'templates';
        $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
        $smarty->config_dir = DIR_FS_CATALOG.'lang';
        $smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
        $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
        $smarty->assign('NAME', $check_status['customers_name']);
        $smarty->assign('ORDER_NR', $order->info['order_id']);
        $smarty->assign('ORDER_ID', $oID);
        //send no order link to customers with guest account
        if ($check_status['customers_status'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
          $smarty->assign('ORDER_LINK', xtc_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id='.$oID, 'SSL'));
        }
        $smarty->assign('ORDER_DATE', xtc_date_long($check_status['date_purchased']));
        $smarty->assign('NOTIFY_COMMENTS', nl2br($notify_comments));
        $smarty->assign('ORDER_STATUS', $orders_status_array[$status]);
        $html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.html');
        $txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.txt');
        $order_subject_search = array('{$nr}', '{$date}', '{$lastname}', '{$firstname}');
        $order_subject_replace = array($oID, strftime(DATE_FORMAT_LONG), $order->customer['lastname'], $order->customer['firstname']);
        $order_subject = str_replace($order_subject_search, $order_subject_replace, EMAIL_BILLING_SUBJECT);

        xtc_php_mail(EMAIL_BILLING_ADDRESS,
                     EMAIL_BILLING_NAME,
                     $check_status['customers_email_address'],
                     $check_status['customers_name'],
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
    if ($order_updated) {
        if(strpos(MODULE_PAYMENT_INSTALLED, 'shopgate.php') !== false){
          /******* SHOPGATE **********/
          include_once DIR_FS_CATALOG.'includes/external/shopgate/base/admin/orders.php';
          setShopgateOrderStatus($oID, $status);
          /******* SHOPGATE **********/
        }
      $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
    } else {
      $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
    }
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('action')).'action=edit'));
    break;

  case 'resendordermail':
    break;

  case 'deleteconfirm' :
    xtc_remove_order($oID, xtc_db_prepare_input($_POST['restock']));
    // Paypal Express Modul
    if(isset($_POST['paypaldelete'])) {
      $query = xtc_db_query("-- /admin/orders.php
                             SELECT *
                               FROM " . TABLE_PAYPAL . "
                              WHERE xtc_order_id = ".$oID
                            );
      while ($values = xtc_db_fetch_array($query)) {
        xtc_db_query("-- /admin/orders.php
                      DELETE FROM " . TABLE_PAYPAL_STATUS_HISTORY . "
                            WHERE paypal_ipn_id = '".$values['paypal_ipn_id']."'
                     ");
      }
      xtc_db_query("-- /admin/orders.php
                    DELETE FROM " . TABLE_PAYPAL . "
                          WHERE xtc_order_id = ".$oID
                  );
    }

    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action'))));
    break;

  // Remove CVV Number
  case 'deleteccinfo' :
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET cc_cvv = null WHERE orders_id = ".$oID);
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET cc_number = '0000000000000000' WHERE orders_id = ".$oID);
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET cc_expires = null WHERE orders_id = ".$oID);
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET cc_start = null WHERE orders_id = ".$oID);
    xtc_db_query("UPDATE ".TABLE_ORDERS." SET cc_issue = null WHERE orders_id = ".$oID);

    xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=edit'));
    break;

  case 'afterbuy_send' :
    require_once (DIR_FS_CATALOG.'includes/classes/afterbuy.php');
    $aBUY = new xtc_afterbuy_functions($oID);
    if ($aBUY->order_send()) {
      $aBUY->process_order();
    }
    break;
    
	/* easyBill */
	case 'easybill':	
    include (DIR_WS_MODULES.'easybill.action.php');
		xtc_redirect( xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')).'action=edit'));
		break;
}

  require (DIR_WS_INCLUDES.'head.php');
?>
<?php //BOF web28 2010-12-09 add table style ?>
<style type="text/css">
<!--
.table{width: 850px; border: 1px solid #a3a3a3; margin-bottom:20px; background: #f3f3f3; padding:2px;}
.heading{font-family: Verdana, Arial, sans-serif; font-size: 12px; font-weight: bold; padding:2px; }
.last_row{background-color: #D9E9FF;}
-->
</style>
<?php //EOF web28 2010-12-09 add table style ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php
require (DIR_WS_INCLUDES.'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
      <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
        <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        <!-- left_navigation_eof //-->
      </table>
    </td>
<!-- body_text //-->
    <td  class="boxCenter" width="100%" valign="top">
      <?php
      // ACTION EDIT - START
      if ($action == 'edit' && ($order_exists)) {
      ?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td width="100%">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
                <td class="pageHeading"><?php echo HEADING_TITLE . '<span style="font-size:13px"> - '.TABLE_HEADING_ORDERS_ID.': ' . $oID . ' - ' . $order->info['date_purchased'] ; ?></span></td>
              </tr>
              <tr>
                <td class="main" valign="top"><?php echo TABLE_HEADING_CUSTOMERS ?></td>
              </tr>
            </table>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')));?>"><?php echo BUTTON_BACK; ?></a>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS_EDIT, 'oID='.$oID.'&cID=' . $order->customer['ID']);?>"><?php echo BUTTON_EDIT ?></a>
          </td>
        </tr>
      </table>
      <br />

      <!-- BOC CUSTOMERS INFO BLOCK -->
      <table cellspacing="0" cellpadding="2" class="table">
        <tr>
          <td valign="top" style="border-right: 1px solid #a3a3a3;">
            <table width="100%" border="0" cellspacing="0" cellpadding="2">
              <?php if ($order->customer['csID']!='') { ?>
              <tr>
                <td class="main" valign="top" bgcolor="#FFCC33"><b><?php echo ENTRY_CID; ?></b></td>
                <td class="main" bgcolor="#FFCC33"><?php echo $order->customer['csID']; ?></td>
              </tr>
              <?php } ?>
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                <td class="main"><b><?php echo ENTRY_CUSTOMERS_ADDRESS; ?></b><br /><?php echo xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main" valign="top"><b><?php echo CUSTOMERS_MEMO; ?></b></td>
              <?php
                // memo query
                $memo_query = xtc_db_query("-- /admin/orders.php
                                           SELECT count(*) AS count
                                             FROM ".TABLE_CUSTOMERS_MEMO."
                                            WHERE customers_id=".$order->customer['ID']);
                $memo_count = xtc_db_fetch_array($memo_query);
              ?>
                <td class="main"><b><?php echo $memo_count['count'].'</b>'; ?>  <a style="cursor:pointer; font-size: 11px;" onclick="javascript:window.open('<?php echo xtc_href_link(FILENAME_POPUP_MEMO,'ID='.$order->customer['ID']); ?>', 'popup', 'scrollbars=yes, width=500, height=500')">(<?php echo DISPLAY_MEMOS; ?>)</a></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_TELEPHONE; ?></b></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '" style="font-size: 11px;">' . $order->customer['email_address'] . '</a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_CUSTOMERS_VAT_ID; ?></b></td>
                <td class="main"><?php echo $order->customer['vat_id']; ?></td>
              </tr>
              <tr>
                <td class="main" valign="top" bgcolor="#FFCC33"><b><?php echo IP; ?></b></td>
                <td class="main" bgcolor="#FFCC33"><b><?php echo $order->customer['cIP']; ?></b></td>
              </tr>
            </table>
          </td>
            <?php
            if ($order->delivery['name'] != $order->customer['name'] ||
                $order->delivery['postcode'] != $order->customer['postcode'] ||
                $order->delivery['city'] != $order->customer['city'] ||
                $order->delivery['street_address'] != $order->customer['street_address']) {
              $address_bgcolor = ' bgcolor="#FFCC33"';
            }
            ?>
          <td class="main" valign="top" style="border-right: 1px solid #a3a3a3;"<?php if (isset($address_bgcolor)) echo $address_bgcolor; ?>>
            <b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b><br />
             <?php echo xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?>
          </td>
          <td valign="top" class="main">
            <b><?php echo ENTRY_BILLING_ADDRESS; ?></b><br />
            <?php echo xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?>
          </td>
        </tr>
      </table>
      <!-- EOC CUSTOMERS INFO BLOCK -->

      <!-- BOC PAYMENT BLOCK -->
      <table border="0" cellspacing="0" cellpadding="2" class="table">
        <tr>
          <td>
            <table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo ENTRY_LANGUAGE; ?></b></td>
                <td class="main"><?php echo $lang_img = xtc_image(DIR_WS_LANGUAGES . $order->info['language'].'/admin/images/'.$lang_array['image'], $order->info['language']) .'&nbsp;&nbsp;'. $order->info['language']; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
                <td class="main"><?php echo get_payment_name($order->info['payment_method']) . ' ('.$order->info['payment_method'].')'; ?></td>
              </tr>
              <?php
              
              /* easyBill */
              include (DIR_WS_MODULES.'easybill.info.php');
              
              // CC - START
              if ($order->info['cc_type'] || $order->info['cc_owner'] || $order->info['cc_number']) {
                ?>
                <tr>
                  <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
                  <td class="main"><?php echo $order->info['cc_type']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
                  <td class="main"><?php echo $order->info['cc_owner']; ?></td>
                </tr>
                <?php
                // BMC CC Mod Start
                if ($order->info['cc_number'] != '0000000000000000') {
                  if (strtolower(CC_ENC) == 'true') {
                    $cipher_data = $order->info['cc_number'];
                    $order->info['cc_number'] = changedataout($cipher_data, CC_KEYCHAIN);
                  }
                }
                // BMC CC Mod End
                ?>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
                  <td class="main"><?php echo $order->info['cc_number']; ?></td>
                </tr>
                <tr>
                <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
                <td class="main"><?php echo $order->info['cc_cvv']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
                  <td class="main"><?php echo $order->info['cc_expires']; ?></td>
                </tr>
                <?php
              }
              // CC - END

              // Paypal Express Modul
              if ($order->info['payment_method']=='paypal_directpayment' or $order->info['payment_method']=='paypal' or $order->info['payment_method']=='paypalexpress') {
                require('../includes/classes/paypal_checkout.php');
                require('includes/classes/class.paypal.php');
                $paypal = new paypal_admin();
                $paypal->admin_notification($oID);
              }

              // Banktransfer - START
              $banktransfer_query = xtc_db_query("-- /admin/orders.php
                                                   SELECT banktransfer_owner,
                                                         banktransfer_number,
                                                         banktransfer_bankname,
                                                         banktransfer_blz,
                                                         banktransfer_iban,
                                                         banktransfer_bic,
                                                         banktransfer_status,
                                                         banktransfer_prz,
                                                         banktransfer_fax,
                                                         banktransfer_owner_email
                                                    FROM ".TABLE_BANKTRANSFER."
                                                   WHERE orders_id = ".$oID);
              $banktransfer = xtc_db_fetch_array($banktransfer_query);
              if ($banktransfer['banktransfer_bankname'] || $banktransfer['banktransfer_blz'] || $banktransfer['banktransfer_number'] || $banktransfer['banktransfer_iban'] ) {
                ?>
                <tr>
                  <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_OWNER; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_owner']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_NUMBER; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_number']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_BLZ; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_blz']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_IBAN; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_iban']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_BIC; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_bic']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_NAME; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_bankname']; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_OWNER_EMAIL; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_owner_email']; ?></td>
                </tr>
                                 
                <?php  if ($banktransfer['banktransfer_status'] == 0) { ?>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
                  <td class="main"><?php echo "OK"; ?></td>
                </tr>
                <?php } else { ?>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_status']; ?></td>
                </tr>
                <?php
                $bt_status = (int) $banktransfer['banktransfer_status'];
                $error_val = defined('TEXT_BANK_ERROR_'.$bt_status) ? constant('TEXT_BANK_ERROR_'.$bt_status) : '';
                ?>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_ERRORCODE; ?></td>
                  <td class="main"><?php echo $error_val; ?></td>
                </tr>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_PRZ; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_prz']; ?></td>
                </tr>
                <?php }
              }
              if ($banktransfer['banktransfer_fax']) {
              ?>
                <tr>
                  <td class="main"><?php echo TEXT_BANK_FAX; ?></td>
                  <td class="main"><?php echo $banktransfer['banktransfer_fax']; ?></td>
                </tr>
              <?php
              }
              // Banktransfer - END

              // Moneybookers
              if ($order->info['payment_method'] == 'amoneybookers') {
                if (file_exists(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php')) {
                  include(DIR_FS_CATALOG.DIR_WS_MODULES.'payment/'.$order->info['payment_method'].'.php');
                  include(DIR_FS_CATALOG.'lang/'.$order->info['language'].'/modules/payment/'.$order->info['payment_method'].'.php');
                  $class = $order->info['payment_method'];
                  $payment = new $class();
                  $payment->admin_order($oID);
                }
              }
              ?>
            </table>
          </td>
        </tr>
      </table>
      <!-- EOC PAYMENT BLOCK -->

      <!-- BOC ORDER BLOCK -->
      <div class="heading"><?php echo TEXT_ORDER; ?></div>
      <table cellspacing="0" cellpadding="2" class="table">
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
          <?php if ($order->products[0]['allow_tax'] == 1) { ?>
          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          <?php  } else { ?>
          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
          <?php } ?>
        </tr>
        <?php
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
          echo '          <tr class="dataTableRow">'.PHP_EOL;
          echo '            <td class="dataTableContent" valign="top" align="right">'.$order->products[$i]['qty'].'&nbsp;x&nbsp;</td>'.PHP_EOL;
          echo '            <td class="dataTableContent" valign="top">'.PHP_EOL;
          echo '              <a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$order->products[$i]['id'].'" target="_blank">'.$order->products[$i]['name'].'</a>';
          if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
            for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
              echo '<br /><nobr><i>&nbsp; - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></nobr> '; //web28- 2010-03-21 - format correction
            }
          }
          echo '            </td>'.PHP_EOL;
          echo '            <td class="dataTableContent" valign="top">';
          echo ($order->products[$i]['model'] != '') ? $order->products[$i]['model'] : '<br />';
          // attribute models
          if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
            for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
              $model = xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option'],$lang); //web28 Fix attribute model  language problem
              echo !empty($model) ? $model.'<br />' : '<br />';
            }
          }
          echo '&nbsp;</td>'.PHP_EOL;
          echo '            <td class="dataTableContent" align="right" valign="top">'.format_price($order->products[$i]['price'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax']).'</td>'.PHP_EOL;
          if ($order->products[$i]['allow_tax'] == 1) {
            echo '            <td class="dataTableContent" align="right" valign="top">'.xtc_display_tax_value($order->products[$i]['tax']).'%</td>'.PHP_EOL;
            echo '            <td class="dataTableContent" align="right" valign="top"><b>'.format_price($order->products[$i]['price'], 1, $order->info['currency'], 0, 0).'</b></td>'.PHP_EOL;
          }
            echo '            <td class="dataTableContent" align="right" valign="top"><b>'.format_price(($order->products[$i]['final_price']), 1, $order->info['currency'], 0, 0).'</b></td>'.PHP_EOL;
            echo '          </tr>'.PHP_EOL;
        }
        ?>
        <tr>
          <td align="right" colspan="10">
             <table border="0" cellspacing="0" cellpadding="2">
              <?php
                for ($i = 0, $n = sizeof($order->totals); $i < $n; $i ++) {
                  echo '                <tr>'.PHP_EOL.'                  <td align="right" class="smallText">'.$order->totals[$i]['title'].'</td>'.PHP_EOL;
                  echo '                  <td align="right" class="smallText">'.$order->totals[$i]['text'].'</td>'.PHP_EOL;
                  echo '                </tr>'.PHP_EOL;
                }
              ?>
            </table>
          </td>
        </tr>
      </table>
      <!-- EOC ORDER BLOCK -->

      <!-- BOC ORDER HISTORY BLOCK -->
      <div class="heading"><?php echo TEXT_ORDER_HISTORY; ?></div>
      <table cellspacing="0" cellpadding="2" class="table">
        <tr>
          <td class="main">
            <table border="1" cellspacing="0" cellpadding="5">
              <tr>
                <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
                <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
                <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
                <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
                <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS_SENT; ?></b></td>
              </tr>
              <?php
              $orders_history_query = xtc_db_query("-- /admin/orders.php
                                                    SELECT orders_status_id,
                                                           date_added,
                                                           customer_notified,
                                                           comments,
                                                           comments_sent
                                                      FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                                     WHERE orders_id = ".$oID."
                                                  ORDER BY date_added");
              $count = xtc_db_num_rows($orders_history_query);
              if ($count) {
                while ($orders_history = xtc_db_fetch_array($orders_history_query)) {
                  $count--;
                  $class = ($count == 0) ? ' last_row' : '';
                  echo '                <tr>'.PHP_EOL;
                  echo '                  <td class="smallText'.$class.'" align="center">'.xtc_datetime_short($orders_history['date_added']).'</td>'.PHP_EOL;
                  echo '                  <td class="smallText'.$class.'" align="center">';
                  if ($orders_history['customer_notified'] == '1') {
                    echo xtc_image(DIR_WS_ICONS.'tick.gif', ICON_TICK).'</td>'.PHP_EOL;
                  } else {
                    echo xtc_image(DIR_WS_ICONS.'cross.gif', ICON_CROSS).'</td>'.PHP_EOL;
                  }
                  echo '            <td class="smallText'. $class.'">';
                  if($orders_history['orders_status_id']!='0') {
                    echo $orders_status_array[$orders_history['orders_status_id']];
                  }else{
                    echo '<font color="#FF0000">'.TEXT_VALIDATING.'</font>';
                  }
                  echo '</td>'.PHP_EOL;
                  echo '                  <td class="smallText'.$class.'">'.nl2br(xtc_db_output($orders_history['comments'])).'&nbsp;</td>'. PHP_EOL;                 
                  echo '                  <td class="smallText'.$class.'" align="center">';
                  if ($orders_history['comments_sent'] == '1') {
                    echo xtc_image(DIR_WS_ICONS.'tick.gif', ICON_TICK).'</td>'.PHP_EOL;
                  } else {
                    echo xtc_image(DIR_WS_ICONS.'cross.gif', ICON_CROSS).'</td>'.PHP_EOL;
                  }
                  echo '</tr>'.PHP_EOL;
                 }
              } else {
                echo '                <tr>'.PHP_EOL.'            <td class="smallText" colspan="5">'.TEXT_NO_ORDER_HISTORY.'</td>'.PHP_EOL.'                </tr>'.PHP_EOL;
              }
              ?>
            </table>
          </td>
        </tr>
      </table>
      <!-- EOC ORDER HISTORY BLOCK -->

      <!-- BOC ORDER STATUS BLOCK -->
      <div class="heading"><?php echo TEXT_ORDER_STATUS; ?></div>
      <table cellspacing="0" cellpadding="2" class="table">
        <tr>
          <td class="main"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
        </tr>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
        </tr>
        <?php echo xtc_draw_form('status', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <tr>
          <td class="main"><?php echo xtc_draw_textarea_field('comments', 'soft', '60', '5', $order->info['comments']); ?></td>
        </tr>
        <tr>
          <td><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo xtc_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
        </tr>
        <tr>
          <td>
            <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b></td>
              <td class="main"><?php echo xtc_draw_checkbox_field('notify', '', true); ?></td>
              <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b></td>
              <td class="main"><?php echo xtc_draw_checkbox_field('notify_comments', '', true); ?></td>
              <td valign="bottom">&nbsp;&nbsp;&nbsp;<input type="submit" class="button" value="<?php echo BUTTON_UPDATE; ?>"></td>
            </tr>
          </table>
          </td>
        </tr>
        </form>
      </table>
      <!-- EOC ORDER STATUS BLOCK -->

      <!-- BOC BUTTONS BLOCK -->
      <table cellspacing="0" cellpadding="2" style="width:850px; margin-bottom:10px;">
        <tr>
          <td align="right">
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oID.'&action=send&sta=0&stc=1&site=1'); ?>"><?php echo BUTTON_ORDER_CONFIRMATION; ?></a>
            <?php
              if (ACTIVATE_GIFT_SYSTEM == 'true') {
                echo '<a class="button" href="'.xtc_href_link(FILENAME_GV_MAIL, xtc_get_all_get_params(array ('cID', 'action')).'cID='.$order->customer['ID']).'">'.BUTTON_SEND_COUPON.'</a>';
              }
            ?>
            <a class="button" href="Javascript:void()" onclick="window.open('<?php echo xtc_href_link(FILENAME_PRINT_ORDER,'oID='.$oID); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=750')"><?php echo BUTTON_INVOICE; ?></a>
            <a class="button" href="Javascript:void()" onclick="window.open('<?php echo xtc_href_link(FILENAME_PRINT_PACKINGSLIP,'oID='.$oID); ?>', 'popup', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no, width=800, height=750')"><?php echo BUTTON_PACKINGSLIP; ?></a>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&action=deleteccinfo'); ?>"><?php echo BUTTON_REMOVE_CC_INFO;?></a>
            <a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'page='.$_GET['page'].'&oID='.$oID); ?>"><?php echo BUTTON_BACK;?></a>
          </td>
        </tr>
        <?php
        	/* easyBill */
          include (DIR_WS_MODULES.'easybill.button.php');
        ?>
      </table>
      <!-- EOC BUTTONS BLOCK -->
<?php
  // ACTION EDIT END
}
  // ACTION CUSTOM
elseif ($action == 'custom_action') {
  include ('orders_actions.php');
  // ACTION ELSE - START
} else {
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td width="100%">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td class="pageHeading" align="right">
                  <?php echo xtc_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
                  <?php echo HEADING_TITLE_SEARCH . ' ' . xtc_draw_input_field('oID', '', 'size="12"') . xtc_draw_hidden_field('action', 'edit').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?>
                  </form>
                </td>
              </tr>
              <tr>
                <td class="main" valign="top"><?php echo TABLE_HEADING_CUSTOMERS ?></td>
                <td class="main" valign="top" align="right">
                  <?php echo xtc_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
                  <?php echo HEADING_TITLE_STATUS . ' ' . xtc_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)),array(array('id' => '0', 'text' => TEXT_VALIDATING)), $orders_statuses),(isset($_GET['status']) && xtc_not_null($_GET['status']) ? (int)$_GET['status'] : ''),'onchange="this.form.submit();"').xtc_draw_hidden_field(xtc_session_name(), xtc_session_id()); ?>
                  </form>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td valign="top">
                  <!-- BOC ORDERS LISTING -->
                  <table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                      <td class="dataTableHeadingContent" align="right" style="width:120px"><?php echo TEXT_SHIPPING_TO; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                      <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                      <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_AFTERBUY; ?></td>
                      <?php } ?>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
                    if (isset($_GET['cID'])) {
                      $cID = (int) $_GET['cID'];
                      $orders_query_raw = "-- /admin/orders.php
                                           SELECT ".$order_select_fields.",
                                                  s.orders_status_name
                                             FROM ".TABLE_ORDERS." o
                                        LEFT JOIN (".TABLE_ORDERS_TOTAL." ot, ".TABLE_ORDERS_STATUS." s)
                                               ON (o.orders_id = ot.orders_id
                                                   AND (o.orders_status = s.orders_status_id
                                                        OR (o.orders_status = '0' AND s.orders_status_id = '1')
                                                       )
                                                  )
                                            WHERE o.customers_id = '".xtc_db_input($cID)."'
                                              AND ot.class = 'ot_total'
                                              AND s.language_id = '".(int)$_SESSION['languages_id']."'
                                         ORDER BY orders_id DESC";

                    } elseif (isset($_GET['status']) && $_GET['status']=='0') {
                        $orders_query_raw = "-- /admin/orders.php
                                             SELECT ".$order_select_fields."
                                               FROM ".TABLE_ORDERS." o
                                          LEFT JOIN ".TABLE_ORDERS_TOTAL." ot ON (o.orders_id = ot.orders_id)
                                               WHERE o.orders_status = '0'
                                                 AND ot.class = 'ot_total'
                                            ORDER BY o.orders_id DESC";

                    } elseif (isset($_GET['status']) && xtc_not_null($_GET['status'])) { //web28 - 2012-04-14  - FIX xtc_not_null($_GET['status'])
                        $status = xtc_db_prepare_input($_GET['status']);
                        $orders_query_raw = "-- /admin/orders.php
                                             SELECT ".$order_select_fields.",
                                                    s.orders_status_name
                                               FROM ".TABLE_ORDERS." o
                                          LEFT JOIN (".TABLE_ORDERS_TOTAL." ot, ".TABLE_ORDERS_STATUS." s)
                                                 ON (o.orders_id = ot.orders_id AND o.orders_status = s.orders_status_id)
                                               WHERE s.language_id = '".(int)$_SESSION['languages_id']."'
                                                 AND s.orders_status_id = '".xtc_db_input($status)."'
                                                 AND ot.class = 'ot_total'
                                            ORDER BY o.orders_id DESC";

                    } elseif ($action == 'search' && $oID) {
                         // ADMIN SEARCH BAR $orders_query_raw moved it to the top
                    } else {
                          $orders_query_raw = "-- /admin/orders.php
                                               SELECT ".$order_select_fields.",
                                                      s.orders_status_name
                                                 FROM ".TABLE_ORDERS." o
                                            LEFT JOIN (".TABLE_ORDERS_TOTAL." ot, ".TABLE_ORDERS_STATUS." s)
                                                   ON (o.orders_id = ot.orders_id AND o.orders_status = s.orders_status_id)
                                                WHERE (s.language_id = '".(int)$_SESSION['languages_id']."'
                                                        AND ot.class = 'ot_total')
                                                   OR (o.orders_status = '0'
                                                        AND ot.class = 'ot_total'
                                                        AND s.orders_status_id = '1'
                                                        AND s.language_id = '".(int)$_SESSION['languages_id']."')
                                             ORDER BY o.orders_id DESC";
                    }
                    $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_ORDER_RESULTS, $orders_query_raw, $orders_query_numrows);
                    $orders_query = xtc_db_query($orders_query_raw);
                    while ($orders = xtc_db_fetch_array($orders_query)) {
                      if ((!xtc_not_null($oID) || (isset($oID) && $oID == $orders['orders_id'])) && !isset($oInfo)) { //web28 - 2012-04-14 - FIX !xtc_not_null($oID)
                        $oInfo = new objectInfo($orders);
                      }
                      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
                        echo '                      <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'\'">'.PHP_EOL;
                      } else {
                        echo '                      <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID')).'oID='.$orders['orders_id']).'\'">'.PHP_EOL;
                      }
                      $orders_link = xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit');
                      $orders_image_preview = xtc_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW);
                      $orders['customers_name'] = (isset($orders['customers_company']) && $orders['customers_company'] != '') ? $orders['customers_company'] : $orders['customers_name'];
                      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id) ) {
                        $orders_action_image = xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT);
                      } else {
                        $orders_action_image = '<a href="' . xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                      }
                      ?>
                      <td class="dataTableContent"><?php echo '<a href="' . $orders_link . '">' . $orders_image_preview . '</a>&nbsp;' . $orders['customers_name']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $orders['orders_id']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $orders['delivery_country']; ?>&nbsp;</td>
                      <td class="dataTableContent" align="right"><?php !empty($orders['order_total'])? print_r(strip_tags($orders['order_total'])) : print_r('0,00 '.$orders['currency']); ?></td>
                      <td class="dataTableContent" align="center"><?php echo xtc_datetime_short($orders['date_purchased']); ?></td>
                      <td class="dataTableContent" align="right"><?php if($orders['orders_status']!='0') { echo $orders['orders_status_name']; }else{ echo '<font color="#FF0000">'.TEXT_VALIDATING.'</font>';}?></td>
                      <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                      <td class="dataTableContent" align="right"><?php  echo ($orders['afterbuy_success'] == 1) ? $orders['afterbuy_id'] : 'TRANSMISSION_ERROR'; ?></td>
                      <?php } ?>
                      <td class="dataTableContent" align="right"><?php echo $orders_action_image; ?>&nbsp;</td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                      <td colspan="5">
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr>
                          <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_ORDER_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                          <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_ORDER_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                        </tr>
                      </table>
                      </td>
                    </tr>
                  </table>
                  <!-- EOC ORDERS LISTING -->
              </td>
              <?php
                $heading = array ();
                $contents = array ();
                switch ($action) {
                  case 'delete' :
                    $heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_ORDER.'</b>');
                    $contents = array ('form' => xtc_draw_form('orders', FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=deleteconfirm'));
                    $contents[] = array ('text' => TEXT_INFO_DELETE_INTRO.'<br /><br /><b>'.$oInfo->customers_name.'</b><br /><b>'.TABLE_HEADING_ORDERS_ID.'</b>: '.$oInfo->orders_id);
                    $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('restock').' '.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
                    // Paypal Express Modul
                    if(defined('TABLE_PAYPAL')) {
                      $db_installed = false;
                      $tables = mysql_query('SHOW TABLES FROM `' . DB_DATABASE . '`');
                      while ($row = mysql_fetch_row($tables)) {
                        if ($row[0] == TABLE_PAYPAL) $db_installed = true;
                      }
                      if ($db_installed) {
                        $query = "-- /admin/orders.php
                                  SELECT *
                                    FROM " . TABLE_PAYPAL . "
                                   WHERE xtc_order_id = '" . $oInfo->orders_id . "'";
                        $query = xtc_db_query($query);
                        if(xtc_db_num_rows($query)>0) {
                          $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('paypaldelete').' '.TEXT_INFO_PAYPAL_DELETE);
                        }
                      }
                    }
                    $contents[] = array ('align' => 'center', 'text' => '<br /><input type="submit" class="button" value="'. BUTTON_DELETE .'"><a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id).'">' . BUTTON_CANCEL . '</a>');
                    break;
                  default :
                    if (isset($oInfo) && is_object($oInfo)) {
                      $heading[] = array ('text' => '<b>['.$oInfo->orders_id.']&nbsp;&nbsp;'.xtc_datetime_short($oInfo->date_purchased).'</b>');
                      $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'">'.BUTTON_EDIT.'</a> <a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=delete').'">'.BUTTON_DELETE.'</a>');
                      //BOF - Dokuman - 2012-06-19 - BILLSAFE payment module
                      if ($oInfo->payment_method === 'billsafe_2') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="billsafe_orders_2.php?oID='.$oInfo->orders_id.'">BillSAFE Details</a>');
                      } elseif ($oInfo->payment_method === 'billsafe_2hp') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="billsafe_orders_2hp.php?oID='.$oInfo->orders_id.'">BillSAFE Details</a>');
                      }
                      //EOF - Dokuman - 2012-06-19 - BILLSAFE payment module
                      if (AFTERBUY_ACTIVATED == 'true') {
                        $contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=afterbuy_send').'">'.BUTTON_AFTERBUY_SEND.'</a>');
                      }
                      $contents[] = array ('text' => '<br />'.TEXT_DATE_ORDER_CREATED.' '.xtc_date_short($oInfo->date_purchased));
                        if (xtc_not_null($oInfo->last_modified)) {
                        $contents[] = array ('text' => TEXT_DATE_ORDER_LAST_MODIFIED.' '.xtc_date_short($oInfo->last_modified));
                      }
                      $contents[] = array ('text' => '<br />'.TEXT_INFO_PAYMENT_METHOD.' '.get_payment_name($oInfo->payment_method).' ('.$oInfo->payment_method.')');
                      $order = new order($oInfo->orders_id);
                      $contents[] = array ('text' => '<br /><br />'.sizeof($order->products).'&nbsp;'.TEXT_PRODUCTS);
                      for ($i = 0; $i < sizeof($order->products); $i ++) {
                        $contents[] = array ('text' => $order->products[$i]['qty'].'&nbsp;x&nbsp;'.$order->products[$i]['name']);
                        if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
                          for ($j = 0; $j < sizeof($order->products[$i]['attributes']); $j ++) {
                            $contents[] = array ('text' => '<small>&nbsp;<i> - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></small></nobr>');
                          }
                        }
                      }
                      if ($order->info['comments']<>'') {
                        $contents[] = array ('text' => '<br><strong>'.TABLE_HEADING_COMMENTS.':</strong><br>'.$order->info['comments']);
                      }
                    }
                    break;
                }
                // display right box
                if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
                  echo '            <td width="25%" valign="top">'."\n";
                  $box = new box;
                  echo $box->infoBox($heading, $contents);
                  echo '          </td>'."\n";
                }
              ?>
              </tr>
            </table>
          </td>
        </tr>
      </table>
<?php
// ACTION ELSE - END
}
?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require (DIR_WS_INCLUDES.'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
