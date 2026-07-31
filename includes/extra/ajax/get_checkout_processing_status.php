<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_WS_CLASSES.'checkout.php');

function get_checkout_processing_status()
{
  $response = array('status' => 'unknown');

  if (isset($_SESSION['customer_id'], $_SESSION['checkout_processing_key'])
      && preg_match('/^[a-f0-9]{64}$/', $_SESSION['checkout_processing_key'])
      )
  {
    $checkout = new checkout($_SESSION['customer_id']);
    $checkout->expire();
    $processing = $checkout->find();
    if (is_array($processing)) {
      $response['status'] = $processing['processing_status'];

      if ($processing['processing_status'] === 'completed' && (int)$processing['orders_id'] > 0) {
        $_SESSION['checkout_completed_order_id'] = (int)$processing['orders_id'];
        $response['redirect'] = xtc_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
      }
    }
  }

  return $response;
}
