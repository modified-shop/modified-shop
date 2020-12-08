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

// include needed language
require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/klarna_checkout.php');

// include needed classes
require_once(DIR_WS_MODULES.'payment/klarna_checkout.php');


if (isset($_GET['orders_id'])
    && isset($_GET['type'])
    )
{
  switch ($_GET['type']) {
    case 'push':
      $check_query = xtc_db_query("SELECT orders_id
                                     FROM ".TABLE_KLARNA_PAYMENTS."
                                    WHERE klarna_order_id = '".xtc_db_input($_GET['orders_id'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        $klarna = new klarna_checkout();
        $result = $klarna->cancelOrder($_GET['orders_id']);
      }
      break;
    
    default:
      trigger_error('NOTIFY GET: '.print_r($_GET, true), E_USER_NOTICE);
      break;
  }
}
