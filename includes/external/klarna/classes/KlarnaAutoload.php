<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


class KlarnaAutoload {

  public function __construct() {
    $this->register();
  }

  public function register() {
    spl_autoload_register(array($this, 'loadClass'));
  }

  public function loadClass($class) {
    $class = ltrim($class, '\\');
    if (is_file(DIR_FS_EXTERNAL.'klarna/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php')) {
      require_once(DIR_FS_EXTERNAL.'klarna/' . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
    }
  }
  
}