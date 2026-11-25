<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // the authorization is settled before stock, specials, vouchers and coupons are booked
  if (isset($payment_modules->selected_module)
      && isset($GLOBALS[$payment_modules->selected_module])
      && $GLOBALS[$payment_modules->selected_module] instanceof TeambankPayment
      && method_exists($GLOBALS[$payment_modules->selected_module], 'authorize_order')
      )
  {
    $GLOBALS[$payment_modules->selected_module]->authorize_order($insert_id);
  }
