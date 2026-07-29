<?php
/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
require_once (DIR_WS_INCLUDES.'functions/checkout_processing.php');

$response = array('status' => 'unknown');

if (isset($_SESSION['customer_id'], $_SESSION['checkout_processing_key'])
    && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_key'])
    )
{
  $processing = checkout_processing_find($_SESSION['checkout_processing_key'], $_SESSION['customer_id']);
  if (is_array($processing)) {
    $response['status'] = $processing['processing_status'];

    if ($processing['processing_status'] === 'completed' && (int)$processing['orders_id'] > 0) {
      $_SESSION['checkout_completed_order_id'] = (int)$processing['orders_id'];
      $response['redirect'] = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
    }
  }
}

session_write_close();
xtc_db_close();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
echo json_encode($response);
exit();
