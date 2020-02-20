<?php
/**
 * $Id$
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */

function get_paypal_data() {
  require_once (DIR_WS_CLASSES.'order.php');
  
  if (!isset($_GET['sec'])
      || $_GET['sec'] != MODULE_PAYMENT_PAYPAL_SECRET
      )
  {
    return;
  }
  $order = new order((int)$_GET['oID']);
  
  ob_start();
  include(DIR_FS_EXTERNAL.'paypal/modules/orders_paypal_data.php');
  $output = ob_get_contents();
  ob_end_clean();  
  
  $output = encode_htmlentities($output);
  $output = base64_encode($output);

  return $output;
}

function xtc_datetime_short($raw_datetime) {
  if (($raw_datetime == '0000-00-00 00:00:00') || empty($raw_datetime)) {
    return false;
  }
  $year = (int) substr($raw_datetime, 0, 4);
  $month = (int) substr($raw_datetime, 5, 2);
  $day = (int) substr($raw_datetime, 8, 2);
  $hour = (int) substr($raw_datetime, 11, 2);
  $minute = (int) substr($raw_datetime, 14, 2);
  $second = (int) substr($raw_datetime, 17, 2);

  return strftime(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
}
?>