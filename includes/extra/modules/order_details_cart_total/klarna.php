<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
if (defined('MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS') 
    && MODULE_PAYMENT_KLARNA_PARTPAYMENT_STATUS=='True' 
    && strpos($_SESSION['customers_status']['customers_status_payment_unallowed'], 'klarna_partPayment') === false
    )
{
  include_once(DIR_WS_INCLUDES.'modules/payment/klarna/display_klarna_cart.php');
}
?>