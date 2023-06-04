<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_shopvote_import() {

    if (defined('MODULE_SHOPVOTE_STATUS')
        && MODULE_SHOPVOTE_STATUS == 'true'
        && MODULE_SHOPVOTE_API_KEY != ''
        && MODULE_SHOPVOTE_API_SECRET != ''
        && defined('MODULE_SHOPVOTE_SCHEDULED_TASKS')
        && MODULE_SHOPVOTE_SCHEDULED_TASKS == 'true'
        )
    {
      // include needed classes
      require_once (DIR_FS_EXTERNAL.'shopvote/shopvote_import.php');

      if (!defined('MODULE_SHOPVOTE_CRONJOB')) {
        define('MODULE_SHOPVOTE_CRONJOB', 0);
        $sql_data_array = array(
          'configuration_key' => 'MODULE_SHOPVOTE_CRONJOB',
          'configuration_value' => 0,
          'configuration_group_id' => 6,
          'date_added' => 'now()'
        );
        xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
      }

      $days = ceil((time() - (int)MODULE_SHOPVOTE_CRONJOB) / 86400);
      if ($days > 365) {
        $days = 365;
      }

      $shopvote = new shopvote_import()
      $response = $shopvote->import($days, '', (int)MODULE_SHOPVOTE_CRONJOB === 0);
    
      if ($response === true) {
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".time()."'
                       WHERE configuration_key = 'MODULE_SHOPVOTE_CRONJOB'");
      }
    }
      
    return true;
  }