<?php
/**
 * $Id$
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */

if (isset($_REQUEST['speed'])) {
  require_once (DIR_FS_INC.'auto_include.inc.php');
  require_once (DIR_FS_INC.'xtc_input_validation.inc.php');
  
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');

  require_once (DIR_WS_INCLUDES.'database_tables.php');
}

function get_sc_service() {

  xtc_db_connect() or die('Unable to connect to database server!');

  $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . '');
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    if (!defined($configuration['cfgKey'])) {
      define($configuration['cfgKey'], stripslashes($configuration['cfgValue']));
    }
  }

  $sc_carriers = array();

  $carrier = $_GET['carrier'];
  $language = xtc_input_validation($_GET['language'], 'lang');
  
  if (defined('MODULE_SHIPCLOUD_STATUS') && MODULE_SHIPCLOUD_STATUS == 'True') {
    require_once (DIR_WS_CLASSES.'language.php');
    $lng = new language(($language != '') ? $language : DEFAULT_LANGUAGE);
  
    require_once(DIR_WS_LANGUAGES . $lng->language['directory'] . '/admin/orders.php');
  
    require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');
    $shipcloud = new shipcloud();
  
    $sc_carriers_array = $shipcloud->get_carriers();
  
    if (is_array($sc_carriers_array)) {
      foreach ($sc_carriers_array as $sc_data) {
        if ($sc_data['name'] == $carrier) {
          for ($i=0, $n=count($sc_data['services']); $i<$n; $i++) {
            $sc_carriers['carrier'][] = array(
              'id' => $sc_data['services'][$i],
              'text' => constant('TEXT_SHIPCLOUD_'.strtoupper($sc_data['services'][$i])),
            );
          }

          for ($i=0, $n=count($sc_data['package_types']); $i<$n; $i++) {
            $sc_carriers['parcel'][] = array(
              'id' => $sc_data['package_types'][$i],
              'text' => constant('TEXT_SHIPCLOUD_'.strtoupper($sc_data['package_types'][$i])),
            );
          }
        }
      }
    }
  }
  
  return $sc_carriers;
}
?>