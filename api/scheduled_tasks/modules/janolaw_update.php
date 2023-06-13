<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_janolaw_update() {
    if (defined('MODULE_JANOLAW_STATUS') 
        && MODULE_JANOLAW_STATUS == 'True'
        )
    {
      require_once(DIR_FS_EXTERNAL.'janolaw/janolaw.php');
      $janolaw = new janolaw_content();
    }
    
    return true;
  }