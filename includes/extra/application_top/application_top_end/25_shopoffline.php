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
  require_once(DIR_FS_INC . 'xtc_get_shop_conf.inc.php');
  
  $shop_is_offline = get_shop_offline_status();
  if ($shop_is_offline 
      && !defined('_MODIFIED_SHOP_LOGIN')
      && basename($PHP_SELF) != FILENAME_LOGIN
      && basename($PHP_SELF) != FILENAME_LOGOFF
      )
  {
    // create smarty elements
    $smarty = new Smarty();
    
    // include header
    require (DIR_WS_INCLUDES.'header.php');
  }
