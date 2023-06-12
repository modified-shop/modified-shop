<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_avalex_update() {
    if (defined('MODULE_AVALEX_STATUS') 
        && MODULE_AVALEX_STATUS == 'True'
        )
    {
      require_once(DIR_FS_EXTERNAL.'avalex/avalex_update.php');
      $avalex = new avalex_update();
      $avalex->check_update();
    }
    
    return true;
  }