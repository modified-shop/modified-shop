<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function cron_tax_eu_maintenance() {
    if (defined('MODULE_TAX_EU_STATUS')
        && MODULE_TAX_EU_STATUS == 'true'
        )
    {
      // include needed functions
      require_once(DIR_FS_INC.'update_tax_eu_rates.inc.php');

      // an unhandled error would stop the remaining scheduled tasks
      try {
        return update_tax_eu_rates();
      } catch (Throwable $exception) {
        trigger_error('The scheduled EU tax rate update failed: '.$exception->getMessage(), E_USER_WARNING);

        return false;
      }
    }

    return true;
  }
