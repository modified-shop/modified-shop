<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_adminlog_maintenance() {
    if (defined('MODULE_ADMIN_LOG_SCHEDULED_TASKS')
        && MODULE_ADMIN_LOG_SCHEDULED_TASKS == 'true'
        && defined('MODULE_ADMIN_LOG_TRESHOLD_DAYS')
        && (int)MODULE_ADMIN_LOG_TRESHOLD_DAYS > 0
        )
    {
      xtc_db_query("DELETE FROM `admin_log`
                          WHERE date_modified < '".date('Y-m-d', strtotime(sprintf('-%s days', (int)MODULE_ADMIN_LOG_TRESHOLD_DAYS)))."'");
    }
    
    return true;
  }