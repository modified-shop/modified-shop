<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');


class paypalcart extends PayPalPayment {
  var $code, $title, $description, $extended_description, $enabled;


  function __construct() {
    global $order;

    PayPalPayment::__construct('paypalcart');

    $this->tmpOrders = false;

    if (isset($_POST['comments'])) {
      $_SESSION['comments'] = xtc_db_prepare_input($_POST['comments']);
    }
  }


  function selection() {
    unset($_SESSION['paypal']);
    xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART, 'payment_error='.$this->code, 'NONSSL'));
  }


  function before_send_order() {
    $this->complete_cart();
  }


  function after_process() {
    unset($_SESSION['paypal']);
  }


  function keys() {
    return array(
      'MODULE_PAYMENT_PAYPALCART_STATUS', 
      'MODULE_PAYMENT_PAYPALCART_ALLOWED', 
      'MODULE_PAYMENT_PAYPALCART_ZONE',
      'MODULE_PAYMENT_PAYPALCART_SORT_ORDER'
    );
  }

}
?>