<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_magnalister() {
    if (defined('MODULE_MAGNALISTER_STATUS') 
        && MODULE_MAGNALISTER_STATUS == 'True'
        && !defined('MAGNA_CALLBACK_MODE') 
        && file_exists(DIR_FS_CATALOG.'magnaCallback.php')
        )
    {
      ob_start();
      require_once(DIR_FS_CATALOG.'magnaCallback.php');
      magnaExecute('magnaCollectStats');
      ob_end_clean();
    }
    
    return true;
  }