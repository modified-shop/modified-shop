<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // sends the order confirmation outside of the checkout, for payment callbacks, tasks
  // and the administration. $force sends it even when SEND_EMAILS is off, which is what
  // an explicit request from the administration means.
  function send_order_mail($orders_id, $force = false) {
    $orders_query = xtc_db_query("SELECT customers_id,
                                         customers_status,
                                         customers_country_iso_code_2,
                                         delivery_state,
                                         currency
                                    FROM ".TABLE_ORDERS."
                                   WHERE orders_id = '".(int)$orders_id."'");
    if (xtc_db_num_rows($orders_query) < 1) {
      return false;
    }
    $orders = xtc_db_fetch_array($orders_query);

    require_once(DIR_FS_INC.'get_country_id.inc.php');
    require_once(DIR_WS_CLASSES.'order.php');
    require_once(DIR_WS_CLASSES.'xtcPrice.php');

    // send_order.php builds $order and $main, xtc_php_mail() reads both out of the
    // global scope for the mail language and the signature, order::getOrderData()
    // reads $xtPrice from there as well, and a payment module like banktransfer picks
    // up $insert_id the same way. Inside a function they would stay local and
    // invisible, so they are published with the context of this order and put back
    // afterwards: the caller may be a frontend request with a different currency.
    global $order, $main, $xtPrice, $insert_id;

    // only read, never replaced: the guarantee label hook reports a damaged archive
    // through it, and outside the administration there is none to report to
    global $messageStack;

    $context_backup = array(
      'order' => $order,
      'main' => $main,
      'xtPrice' => $xtPrice,
      'insert_id' => $insert_id,
    );

    $order = null;
    $main = null;
    $xtPrice = new xtcPrice($orders['currency'], $orders['customers_status']);
    $insert_id = (int)$orders_id;

    if ($force === true) {
      $send_by_admin = true;
    }

    $smarty = new Smarty();

    // send_order.php checks the order against the session customer, so a caller
    // that runs with a logged in customer gets its own values back afterwards
    $session_backup = array();
    foreach (array('customer_id', 'customer_country_id', 'customer_zone_id') as $key) {
      if (isset($_SESSION[$key])) {
        $session_backup[$key] = $_SESSION[$key];
      }
    }

    $_SESSION['customer_id'] = $orders['customers_id'];
    $_SESSION['customer_country_id'] = get_country_id($orders['customers_country_iso_code_2']);
    $_SESSION['customer_zone_id'] = -1;
    if ($_SESSION['customer_country_id'] > 0) {
      $zones_query = xtc_db_query("SELECT zone_id
                                     FROM ".TABLE_ZONES."
                                    WHERE zone_name = '".xtc_db_input($orders['delivery_state'])."'
                                      AND zone_country_id = '".(int)$_SESSION['customer_country_id']."'");
      if (xtc_db_num_rows($zones_query) > 0) {
        $zones = xtc_db_fetch_array($zones_query);
        $_SESSION['customer_zone_id'] = $zones['zone_id'];
      }
    }

    include(DIR_FS_CATALOG.'send_order.php');

    $order = $context_backup['order'];
    $main = $context_backup['main'];
    $xtPrice = $context_backup['xtPrice'];
    $insert_id = $context_backup['insert_id'];

    foreach (array('customer_id', 'customer_country_id', 'customer_zone_id') as $key) {
      unset($_SESSION[$key]);
      if (isset($session_backup[$key])) {
        $_SESSION[$key] = $session_backup[$key];
      }
    }

    return true;
  }
