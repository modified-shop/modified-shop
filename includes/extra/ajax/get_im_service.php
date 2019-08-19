<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


if (isset($_REQUEST['speed'])) {
  // auto include
  require_once (DIR_FS_INC.'auto_include.inc.php');

  require_once (DIR_FS_INC.'xtc_input_validation.inc.php');
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_WS_INCLUDES.'database_tables.php');
}


function get_im_service() {

  xtc_db_connect() or die('Unable to connect to database server!');

  $configuration_query = xtc_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . '');
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    if (!defined($configuration['cfgKey'])) {
      define($configuration['cfgKey'], stripslashes($configuration['cfgValue']));
    }
  }

  $im_data = array();

  $format_id = $_GET['format'];
  $language = xtc_input_validation($_GET['language'], 'lang', '');
  
  if (defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') {
    require_once (DIR_WS_CLASSES.'language.php');
    $lng = new language(($language != '') ? $language : DEFAULT_LANGUAGE);
  
    require_once(DIR_WS_LANGUAGES . $lng->language['directory'] . '/extra/admin/internetmarke.php');
  
    require_once(DIR_FS_EXTERNAL.'internetmarke/internetmarke.php');
    $internetmarke = new mod_internetmarke($oID);
    if ($internetmarke->getError() === false) {
      $PageFormats = $internetmarke->getPageFormats($format_id, true);

      $row_array = array();
      for($i = 1, $n = $PageFormats['labelY']; $i <= $n; $i ++) {
        $row_array[] = array('id' => $i, 'text' => $i);
      }
  
      $column_array = array();
      for($i = 1, $n = $PageFormats['labelX']; $i <= $n; $i ++) {
        $column_array[] = array('id' => $i, 'text' => constant('TEXT_IM_COLUMN_'.$i));
      }
      
      $im_data = array(
        'row' => $row_array,
        'column' => $column_array,
      );
    }  
  }
  
  return $im_data;
}
?>