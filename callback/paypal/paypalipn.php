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

// get request
$request_input = get_external_content('php://input', 3, false);

// get params
parse_str($request_input, $request);
$request['cmd'] = '_notify-validate';

// set payment
$paypal = new PayPalPayment('paypal');

// get transaction
$transaction = $paypal->get_transaction($request['txn_id']);

// set endpoint
$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
if($paypal->get_config('PAYPAL_MODE') == 'sandbox') {
	$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
}

// validate
$ch = curl_init($paypal_url);
if ($ch === false) {
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
  exit();
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request, '', '&'));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
// of the certificate as shown below. Ensure the file is readable by the webserver.
// This is mandatory for some environments.

//$cert = __DIR__ . "./cacert.pem";
//curl_setopt($ch, CURLOPT_CAINFO, $cert);

// set result
$result = curl_exec($ch);
curl_close($ch);

if (is_object($transaction) && $result == 'VERIFIED') {
  $payment_id = $transaction->getParentPayment();
  
  $check_query = xtc_db_query("SELECT p.orders_id,
                                      o.orders_status
                                 FROM ".TABLE_PAYPAL_PAYMENT." p
                                 JOIN ".TABLE_ORDERS." o
                                      ON o.orders_id = p.orders_id
                                WHERE p.payment_id = '".xtc_db_input($payment_id)."'");

  if (xtc_db_num_rows($check_query) > 0) {
    $check = xtc_db_fetch_array($check_query);
    
    $valid_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_PAYPAL_IPN."
                                  WHERE orders_id = '".$check['orders_id']."'
                                    AND transaction_id = '".xtc_db_input($request['txn_id'])."'
                                    AND payment_status = '".xtc_db_input(strtolower($request['payment_status']))."'");
    
    if (xtc_db_num_rows($valid_query) == 0) {                             
      switch (strtolower($request['payment_status'])) {
        case 'denied':
        case 'failed':
        case 'expired':
        case 'voided':
          $orders_status_id = $paypal->get_config('PAYPAL_ORDER_STATUS_REJECTED_ID');
          break;

        case 'pending':
        case 'reversed':
        case 'created':
          $orders_status_id = $paypal->get_config('PAYPAL_ORDER_STATUS_PENDING_ID');
          break;

        case 'refunded':
          $orders_status_id = $paypal->get_config('PAYPAL_ORDER_STATUS_REFUNDED_ID');
          break;

        case 'processed':
        case 'completed':
        case 'canceled-reversal':
          $orders_status_id = $paypal->get_config('PAYPAL_ORDER_STATUS_SUCCESS_ID');
          break;
      }

      if ($orders_status_id == '' || $orders_status_id < 0) {
        $orders_status_id = $check['orders_status'];
      }
  
      $paypal->update_order('PayPal IPN', $orders_status_id, $check['orders_id']);
      
      $sql_data_array = array(
        'orders_id' => $check['orders_id'],
        'transaction_id' => $request['txn_id'],
        'payment_status' => strtolower($request['payment_status']),
      );
      xtc_db_perform(TABLE_PAYPAL_IPN, $sql_data_array);
    }
  }
} else {
  header("HTTP/1.0 404 Not Found");
  header("Status: 404 Not Found");
}
?>