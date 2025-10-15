<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once(DIR_FS_INC.'xtc_datetime_short.inc.php');
  
  
  function get_teambank_data() {
    require_once (DIR_WS_CLASSES.'order.php');
    
    if (!isset($_GET['sec'])
        || $_GET['sec'] != MODULE_PAYMENT_EASYCREDIT_SECRET
        )
    {
      return;
    }
    $order = new order((int)$_GET['oID']);
    
    ob_start();
    include(DIR_FS_EXTERNAL.'Teambank/modules/orders_teambank_data.php');
    $output = ob_get_contents();
    ob_end_clean();  
    //exit();
    
    $output = encode_htmlentities($output);
    $output = base64_encode($output);
  
    return $output;
  }
