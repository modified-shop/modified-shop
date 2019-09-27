<?php
  /*-------------------------------------------------------------
   $Id$

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

require_once (DIR_FS_INC.'xtc_add_tax.inc.php');
require_once (DIR_FS_INC.'xtc_validate_vatid_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_attributes_model.inc.php');
require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
require_once (DIR_FS_INC.'get_tracking_link.inc.php');
require_once (DIR_FS_INC.'get_order_total.inc.php');

/* magnalister v1.0.1 */
if (function_exists('magnaExecute')) magnaExecute('magnaSubmitOrderStatus', array(), array('order_details.php'));
/* END magnalister */

//split page results
if(!defined('MAX_DISPLAY_ORDER_RESULTS')) {
  define('MAX_DISPLAY_ORDER_RESULTS', 30);
}
function get_shipping_name($shipping_class) {
  $shipping_class_array = explode('_', $shipping_class);
  $shipping_class = $shipping_class_array[0];
  $shipping_method = $shipping_class;
  if (file_exists(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/shipping/'.$shipping_class.'.php')){
    include(DIR_FS_CATALOG.'lang/'.$_SESSION['language'].'/modules/shipping/'.$shipping_class.'.php');
    $shipping_method = constant(strtoupper('MODULE_SHIPPING_'.$shipping_class.'_TEXT_TITLE'));
  }
  return $shipping_method;
}

// initiate template engine for mail
$smarty = new Smarty;
require (DIR_WS_CLASSES.'currencies.php');
$currencies = new currencies();

$action = (isset($_GET['action']) ? xtc_db_prepare_input($_GET['action']) : '');
$oID = isset($_GET['oID']) ? (int) $_GET['oID'] : '';
$customer = (isset($_GET['customer']) ? xtc_db_prepare_input($_GET['customer']) : '');

// EMAIL PREVIEW
include('includes/modules/email_preview/email_preview_tabs.php');

if (($action == 'edit' || $action == 'update_order') && $oID) {
  $orders_query = xtc_db_query("SELECT orders_id
                                  FROM ".TABLE_ORDERS."
                                 WHERE orders_id = '".$oID."'");
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
                        o.shipping_method,
                        o.shipping_class,
                        o.last_modified,
                        o.date_purchased,
                        o.orders_status,
                        o.currency,
                        o.currency_value,
                        o.afterbuy_success,
                        o.afterbuy_id,
                        o.language,
                        o.delivery_country,
                        o.delivery_country_iso_code_2
                        ';

// invoice number and date
include(DIR_WS_MODULES.'invoice_number/invoice_number_functions.php');
$order_select_fields = add_select_ibillnr($order_select_fields);

// track & trace
$carriers = array();
$carriers_query = xtc_db_query("SELECT carrier_id, 
                                       carrier_name 
                                  FROM ".TABLE_CARRIERS." 
                              ORDER BY carrier_sort_order ASC");
while ($carrier = xtc_db_fetch_array($carriers_query)) {
	$carriers[] = array('id' => $carrier['carrier_id'], 'text' => $carrier['carrier_name']);
}

//admin search bar
if ($action == 'search' && $oID && $customer == '') {
  $orders_query_raw = "SELECT ".$order_select_fields.",
                              s.orders_status_name
                         FROM ".TABLE_ORDERS." o
                    LEFT JOIN ".TABLE_ORDERS_STATUS." s
                              ON (o.orders_status = s.orders_status_id 
                                  AND s.language_id = '".(int)$_SESSION['languages_id']."')
                        WHERE o.orders_id LIKE '%".$oID."%'
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
     //$messageStack->add('1 Treffer: ' . $oID, 'success');
  }
}

require_once (DIR_WS_CLASSES.'order.php');
require_once (DIR_FS_CATALOG.DIR_WS_CLASSES . 'payment.php');
if (($action == 'edit' || $action == 'update_order') && $order_exists) {
  $order = new order($oID);
  require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
  $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);
}

// invoice number and date
if (isset($order) && is_object($order)) {
  action_next_ibillnr($order,$oID);
}

// Trying to get property of non-object $order->info
if (isset($order) && is_object($order)) {
  $lang_query = xtc_db_query("SELECT languages_id, 
                                     language_charset,
                                     code,
                                     image
                                FROM " . TABLE_LANGUAGES . "
                               WHERE directory = '" . $order->info['language'] . "'");
  $lang_array = xtc_db_fetch_array($lang_query);
  $lang = $lang_array['languages_id'];
  $lang_code = $lang_array['code'];
  $lang_charset = $lang_array['language_charset'];
}

if (isset($order) && trim($order->info['language']) == '') $order->info['language'] = $_SESSION['language'];
if (!isset($lang)) $lang = $_SESSION['languages_id'];
if (!isset($lang_code)) $lang_code = $_SESSION['language_code'];
if (!isset($lang_charset)) $lang_charset = $_SESSION['language_charset'];

$orders_statuses = array();
$orders_status_array = array();
$orders_status_query = xtc_db_query("SELECT orders_status_id,
                                            orders_status_name
                                       FROM ".TABLE_ORDERS_STATUS."
                                      WHERE language_id = '".$lang."'
                                   ORDER BY sort_order");
while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
  $orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
  $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

switch ($action) {
  case 'send':
  case 'send_order_mail':
    $smarty->template_dir = DIR_FS_CATALOG.'templates';
    $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
    $smarty->config_dir = DIR_FS_CATALOG.'lang';
    $send_by_admin = true;
    $send_confirmation = false;
    $insert_id = $oID;
    require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'xtcPrice.php');
    require_once(DIR_FS_INC.'xtc_href_link_from_admin.inc.php');
    include (DIR_FS_CATALOG .'send_order.php');
    break;
  case 'update_order':
    $status = (int) $_POST['status'];
    $comments = xtc_db_prepare_input($_POST['comments']);
    $order_updated = false;
    include (DIR_WS_MODULES.'orders_update.php');
    if ($order_updated) {
        if(defined('MODULE_PAYMENT_SHOPGATE_STATUS') && MODULE_PAYMENT_SHOPGATE_STATUS=='True'){
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

  case 'deleteconfirm':
    xtc_remove_order($oID, xtc_db_prepare_input($_POST['restock']), ((STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true') ? true : false));
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array ('oID', 'action'))));
    break;

  case 'stornoconfirm':
    xtc_reverse_order($oID, xtc_db_prepare_input($_POST['restock']), xtc_db_prepare_input($_POST['status_storno']));
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action'))));
    break;
    
	case 'inserttracking':
		$oID = (int)$_GET['oID'];
		$carrier_id = xtc_db_prepare_input($_POST['carrier_id']);
		$parcel_id = xtc_db_prepare_input($_POST['parcel_id']);
    $sql_data_array = array(
      'orders_id' => $oID,
      'carrier_id' => $carrier_id,
      'parcel_id' => $parcel_id,
      'date_added' => 'now()'
    );
    xtc_db_perform(TABLE_ORDERS_TRACKING, $sql_data_array);
		xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')).'action=edit'));              
		break;
		
	case 'deletetracking':
		$tracking_id = (int)$_GET['tID'];
		xtc_db_query("DELETE FROM ".TABLE_ORDERS_TRACKING." WHERE tracking_id = '".(int)$tracking_id."'");
    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')).'action=edit'));
		break;

	case 'downloads':
	  $sql_data_array = array('download_count' => (int)$_POST['download_count'],
	                          'download_maxdays' => floor((strtotime('+'.(int)$_POST['download_maxdays'].' day') - (int)$_POST['date_purchased']) / 86400)
	                          );
	  xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array, 'update', "orders_products_download_id = '".(int)$_POST['orders_products_download_id']."'");

    xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action')).'action=edit'));
		break;

  case 'custom':
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/modules/orders/orders_action/','php') as $file) require ($file);
    break;
}

  require (DIR_WS_INCLUDES.'head.php');
?>
<style type="text/css">
.table{width: 100%; border: 1px solid #a3a3a3; margin-bottom:20px; background: #f3f3f3; padding:2px;}
.heading{font-family: Verdana, Arial, sans-serif; font-size: 12px; font-weight: bold; padding:2px; }
.last_row{background-color: #fff0cf;}
textarea#comments{width:99%;}
</style>

<script type="text/javascript" src="includes/modules/email_preview/email_preview.js"></script>

</head>
<body>
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->
  <!-- body //-->
  <table class="tableBody">
    <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //-->
      <td class="boxCenter">
      <?php      
      if ($action == 'edit' && ($order_exists)) {
        include (DIR_WS_MODULES.'orders_info_blocks.php'); // ACTION EDIT - START
      } elseif ($action == 'custom_action') {
        include ('orders_actions.php'); // ACTION CUSTOM
      } else {
        include (DIR_WS_MODULES.'orders_listing.php');
      }
      ?>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
  <br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
