<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


chdir('../../');
include('includes/application_top.php');

// include needed functions
require_once(DIR_FS_INC.'get_external_content.inc.php');

// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');

$request_json = get_external_content('php://input', 3, false);
$request = json_decode($request_json, true);

$check_query = xtc_db_query("SELECT p.orders_id,
                                    o.orders_status
                               FROM ".TABLE_PAYPAL_PAYMENT." p
                               JOIN ".TABLE_ORDERS." o
                                    ON o.orders_id = p.orders_id
                              WHERE p.payment_id = '".xtc_db_input($request['resource']['parent_payment'])."'");

if (xtc_db_num_rows($check_query) > 0) {
  $check = xtc_db_fetch_array($check_query);
  
  $paypal = new PayPalPayment('paypal');
  
  $orders_status_id = $paypal->get_config($request['event_type']);
  if ($orders_status_id < 0) {
    $orders_status_id = $check['orders_status'];
  }
  
  $paypal->update_order($request['summary'], $orders_status_id, $check['orders_id']);
} else {
  // order is missing
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
}
?>