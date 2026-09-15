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

      return update_tax_eu_rates();
    }

    return true;
  }
