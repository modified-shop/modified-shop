<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function cron_trustedshops_import() {

    if (defined('MODULE_TRUSTEDSHOPS_STATUS') 
        && MODULE_TRUSTEDSHOPS_STATUS == 'true'
        )
    {
      // include needed classes
      require_once(DIR_FS_EXTERNAL.'trustedshops/etrusted.php');

      // load configuration
      $trustedshops_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_TRUSTEDSHOPS."
                                           WHERE status = '1'");
      if (xtc_db_num_rows($trustedshops_query) > 0) {
        while ($trustedshops = xtc_db_fetch_array($trustedshops_query)) {      
          foreach ($trustedshops as $key => $value) {
            defined('MODULE_TS_'.strtoupper($key).'_'.$trustedshops['languages_id']) OR define('MODULE_TS_'.strtoupper($key).'_'.$trustedshops['languages_id'], $value);
          }

          if (!defined('MODULE_TRUSTEDSHOPS_CRONJOB_'.$trustedshops['languages_id'])) {
            define('MODULE_TRUSTEDSHOPS_CRONJOB_'.$trustedshops['languages_id'], 0);
            $sql_data_array = array(
              'configuration_key' => 'MODULE_TRUSTEDSHOPS_CRONJOB_'.$trustedshops['languages_id'],
              'configuration_value' => 0,
              'configuration_group_id' => 6,
              'date_added' => 'now()'
            );
            xtc_db_perform(TABLE_CONFIGURATION, $sql_data_array);
          }
      
          if ($trustedshops['product_sticker_api'] == 1
              && !empty($trustedshops['product_sticker_api_client'])
              && !empty($trustedshops['product_sticker_api_secret'])
              )
          {
            $eTrusted = new eTrusted($trustedshops['languages_id']);
            $eTrusted->getReviews('', constant('MODULE_TRUSTEDSHOPS_CRONJOB_'.$trustedshops['languages_id']) == 0);

            xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                             SET configuration_value = '".time()."'
                           WHERE configuration_key = 'MODULE_TRUSTEDSHOPS_CRONJOB_".$trustedshops['languages_id']."'");
          }
        }
      }
    }
    
    return true;
  }