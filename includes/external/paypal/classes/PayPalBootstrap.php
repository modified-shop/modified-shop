<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class PayPalBootstrap {

  public function init() {
    $this->initPaypal();
  }

  protected function initPaypal() {
    require_once (DIR_FS_EXTERNAL.'paypal/classes/PayPalAutoload.php');
    
    new PayPalAutoload();
  }
}
?>