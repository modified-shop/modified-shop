<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function set_paypal_contact() {
    if (isset($_POST['shippingContact'])) {
      $_SESSION['paypal']['contact']['shipping'] = json_decode($_POST['shippingContact'], true);
    }

    if (isset($_POST['billingContact'])) {
      $_SESSION['paypal']['contact']['billing'] = json_decode($_POST['billingContact'], true);
    }

    return array('success' => true);
  }
