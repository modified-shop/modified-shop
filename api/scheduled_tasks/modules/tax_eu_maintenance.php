<?php
/***************************************************************
* file: tax_eu_maintenance.php
* path: /api/scheduled_tasks/modules/
* use: scheduled task for system module tax_eu
*
* © copyright noRiddle, 07-2026
             ____
            |    |       |     | |
  __   ___  |____/ °  ___|  ___| |  ___
|/  | |   | |   \  | |   | |   | | |___|
|   n |___o |    R i |___d |__ d l |__e

***************************************************************/

function cron_tax_eu_maintenance() {
  if(defined('MODULE_TAX_EU_STATUS') && MODULE_TAX_EU_STATUS == 'true') {
    if(class_exists('tax_eu') === false) {
      require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/system/tax_eu.php');
    }

    //BOC avoid undefined constants in tax_eu module
    defined('FILENAME_MODULE_EXPORT') OR define('FILENAME_MODULE_EXPORT', '');
    defined('BUTTON_UPDATE') OR define('BUTTON_UPDATE', '');
    //EOC avoid undefined constants in tax_eu module

    $st_tax_eu = new tax_eu();
    $st_tax_eu->update();
  }

  return true;
}
