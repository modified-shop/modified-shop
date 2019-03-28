<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class PaypalAutoload {
  
  const CLASS_PREFIX = 'PayPal';
  const CUSTOMCLASS_PREFIX = 'classes';
  
  public function __construct() {
    $this->register();
  }

  public function register() {
    spl_autoload_register(array($this, 'loadClass'));
  }

  public function loadClass($class) {
    $class = ltrim($class, '\\');
    if (substr($class, 0, strlen(self::CLASS_PREFIX)) === self::CLASS_PREFIX) {
      require_once(DIR_FS_EXTERNAL.'paypal/lib/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
    }
    if (substr($class, 0, strlen(self::CUSTOMCLASS_PREFIX)) === self::CUSTOMCLASS_PREFIX) {
      require_once(DIR_FS_EXTERNAL.'paypal/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
    }
  }
  
}
?>