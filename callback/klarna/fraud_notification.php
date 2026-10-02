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

include('includes/application_top_callback.php');

// include needed classes
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');

$json = file_get_contents('php://input');
$klarna_data = json_decode($json, true);

$klarna_order_id = '';
$klarna_event_type = '';
$klarna_token = ((isset($_GET['token']) && is_string($_GET['token'])) ? $_GET['token'] : '');
if (is_array($klarna_data)) {
  if (isset($klarna_data['order_id']) && is_string($klarna_data['order_id'])) {
    $klarna_order_id = $klarna_data['order_id'];
  }
  if (isset($klarna_data['event_type']) && is_string($klarna_data['event_type'])) {
    $klarna_event_type = $klarna_data['event_type'];
  }
}

if ($klarna_order_id === '') {
  http_response_code(400);
  exit;
}

$check_query = xtc_db_query("SELECT kp.orders_id,
                                    kp.notify_token,
                                    o.payment_method
                               FROM ".TABLE_KLARNA_PAYMENTS." kp
                               JOIN ".TABLE_ORDERS." o
                                    ON o.orders_id = kp.orders_id
                              WHERE kp.klarna_order_id = '".xtc_db_input($klarna_order_id)."'");
if (xtc_db_num_rows($check_query) < 1) {
  // unknown order, let Klarna retry later
  http_response_code(404);
  exit;
}
$check = xtc_db_fetch_array($check_query);

// Klarna does not sign the push, the token in the registered URL identifies the sender
if ($klarna_token === '' || $check['notify_token'] === '' || !hash_equals($check['notify_token'], $klarna_token)) {
  http_response_code(403);
  exit;
}

$klarna = new KlarnaPayment($check['payment_method']);
if ($klarna->resolveFraudStatus((int)$check['orders_id'], $klarna_event_type) === false) {
  // status request or capture failed, let Klarna retry later
  http_response_code(503);
  exit;
}

http_response_code(200);
