<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_protectedshops_update() {
    if (defined('MODULE_PROTECTEDSHOPS_STATUS') 
        && MODULE_PROTECTEDSHOPS_STATUS == 'true'
        )
    {
      require_once(DIR_FS_EXTERNAL.'protectedshops/protectedshops_update.php');
      $protectedshops = new protectedshops_update();
      $protectedshops->check_update();
    }
    
    return true;
  }