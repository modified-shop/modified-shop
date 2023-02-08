<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_status_banners() {
    if (defined('MODULE_BANNER_MANAGER_STATUS')
        && MODULE_BANNER_MANAGER_STATUS == 'true'
        )
    {
      // include needed functions
      require_once(DIR_FS_CATALOG.'inc/xtc_activate_banners.inc.php');
      require_once(DIR_FS_CATALOG.'inc/xtc_expire_banners.inc.php');
    
      xtc_activate_banners();
      xtc_expire_banners();
    }
    
    return true;
  }