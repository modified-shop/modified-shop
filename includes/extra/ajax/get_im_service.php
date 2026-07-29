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
  include_once('includes/application_top_callback.php');
}


function get_im_service() {
  $im_data = array();

  $format_id = $_GET['format'];
  $language = xtc_input_validation($_GET['language'], 'lang');
  
  if (defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') {
    require_once(DIR_FS_CATALOG.'includes/classes/class.logger.php');
    require_once(DIR_WS_CLASSES.'language.php');
    
    $lng = new language(($language != '') ? $language : DEFAULT_LANGUAGE);
  
    require_once(DIR_WS_LANGUAGES . $lng->language['directory'] . '/extra/admin/internetmarke.php');

  
    require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
    $DHLInternetmarke = new DHLInternetmarke(array());

    $result = $DHLInternetmarke->getPageFormats($format_id, true);

    $row_array = array();
    $column_array = array();
    
    if (isset($result['formats'])
        && is_array($result['formats'])
        && count($result['formats']) > 0
        )
    {
      $id = key($result['formats']);
          
      for($i = 1, $n = $result['formats']['labelY']; $i <= $n; $i ++) {
        $row_array[] = array('id' => $i, 'text' => $i);
      }
 
      for($i = 1, $n = $result['formats']['labelX']; $i <= $n; $i ++) {
        $column_array[] = array('id' => $i, 'text' => constant('TEXT_IM_COLUMN_'.$i));
      }
    }
      
    $im_data = array(
      'row' => $row_array,
      'column' => $column_array,
    );
  }  
  
  return $im_data;
}
