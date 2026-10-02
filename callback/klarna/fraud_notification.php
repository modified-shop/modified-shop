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
if (is_array($klarna_data) && isset($klarna_data['order_id'])) {
  $klarna_order_id = $klarna_data['order_id'];
} elseif (isset($_GET['order_id'])) {
  $klarna_order_id = $_GET['order_id'];
}

if ($klarna_order_id == '') {
  http_response_code(400);
  exit;
}

$check_query = xtc_db_query("SELECT kp.orders_id,
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

$klarna = new KlarnaPayment($check['payment_method']);
$klarna->resolveFraudStatus((int)$check['orders_id']);

http_response_code(200);
