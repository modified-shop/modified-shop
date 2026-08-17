<?php
/***************************************************************
* file: tax_eu_maintenance.php
* path: /api/scheduled_tasks/modules/
* use: scheduled task for system module tax_eu
*
* © copyright noRiddle, 07-2026
***************************************************************/

function cron_tax_eu_maintenance() {
  if(defined('MODULE_TAX_EU_STATUS') && MODULE_TAX_EU_STATUS == 'true') {
    if(class_exists('tax_eu') === false) {
      require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/system/tax_eu.php');
    }

    //BOC avoid undefined constants in tax_eu module
    defined('FILENAME_MODULE_EXPORT') OR define('FILENAME_MODULE_EXPORT', '');
    defined('BUTTON_UPDATE') OR define('BUTTON_UPDATE', '');
    defined('TABLE_COUNTRIES') OR define('TABLE_COUNTRIES', 'countries');
    defined('TABLE_GEO_ZONES') OR define('TABLE_GEO_ZONES', 'geo_zones');
    defined('TABLE_ZONES_TO_GEO_ZONES') OR define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
    defined('TABLE_TAX_RATES') OR define('TABLE_TAX_RATES', 'tax_rates');
    defined('TABLE_CONFIGURATION') OR define('TABLE_CONFIGURATION', 'configuration');
    //EOC avoid undefined constants in tax_eu module

    $st_tax_eu = new tax_eu();
    $st_tax_eu->update();
  }

  return true;
}

