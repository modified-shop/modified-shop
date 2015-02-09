<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class tax_eel {
  var $code;
  var $title;
  var $sort_order;
  var $enabled;
  var $description;
  var $extended_description;
  var $tax_array = array('BE' => '21', 
                         'BG' => '20', 
                         'DK' => '25', 
                         'DE' => '19', 
                         'EE' => '20', 
                         'FI' => '24', 
                         'FR' => '20', 
                         'GR' => '23', 
                         'IE' => '23', 
                         'IT' => '22', 
                         'HR' => '25', 
                         'LV' => '21', 
                         'LT' => '21', 
                         'LU' => '17', 
                         'MT' => '18', 
                         'NL' => '21', 
                         'AT' => '20', 
                         'PL' => '23', 
                         'PT' => '23', 
                         'RO' => '24', 
                         'SE' => '25', 
                         'SK' => '20', 
                         'SI' => '22', 
                         'ES' => '21', 
                         'CZ' => '21', 
                         'HU' => '27', 
                         'GB' => '20', 
                         'CY' => '19');

  function __construct() {
    $this->code = 'tax_eel';
    $this->title = MODULE_TAX_EEL_TEXT_TITLE;
    $this->description = MODULE_TAX_EEL_TEXT_DESCRIPTION;
    $this->enabled = ((MODULE_TAX_EEL_STATUS == 'true') ? true : false);
  }
 
  function process() {
  }

  // display
  function display() {
    return array('text' => '<div align="center">' . MODULE_TAX_EEL_TEXT_DESCRIPTION_PROCESSED . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=tax_eel')) . '</div>');
  }

  // check
  function check() {    
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_TAX_EEL_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  // install
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_TAX_EEL_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    
    $sql_data_array = array('tax_class_title' => 'Standardsatz VP',
                            'tax_class_description' => 'elektronisch erbrachte Leistungen',
                            'date_added' => 'now()');
    xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array);                       
    $tax_class_id = xtc_db_insert_id();
    
    foreach ($this->tax_array as $iso => $ust) {
      $countries_query = xtc_db_query("SELECT countries_id 
                                         FROM ".TABLE_COUNTRIES." 
                                        WHERE countries_iso_code_2 = '".$iso."'");
      if (xtc_db_num_rows($countries_query) == 1) {
        $countries = xtc_db_fetch_array($countries_query);
  
        $sql_data_array = array('geo_zone_name' => 'Steuerzone VP - '.$iso,
                                'date_added' => 'now()');
        xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array);
        $insert_id = xtc_db_insert_id();

        $sql_data_array = array('zone_country_id' => $countries['countries_id'],
                                'zone_id' => '0',
                                'geo_zone_id' => $insert_id,
                                'date_added' => 'now()');
        xtc_db_perform(TABLE_ZONES_TO_GEO_ZONES, $sql_data_array);
  
        $sql_data_array = array('tax_zone_id' => $insert_id,
                                'tax_class_id' => $tax_class_id,
                                'tax_priority' => '99',
                                'tax_rate' => $ust,
                                'tax_description' => 'MwSt. '.$ust.'%',
                                'date_added' => 'now()');
        xtc_db_perform(TABLE_TAX_RATES, $sql_data_array);
      }
    }      
  }
    
  // remove
  function remove() {
    $keys = $this->keys();
    $keys[] = 'MODULE_TAX_EEL_STATUS';
    
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $keys) . "')");
    
    $tax_query = xtc_db_query("SELECT tax_zone_id, 
                                      tax_class_id
                                 FROM ".TABLE_TAX_RATES."
                                WHERE tax_priority = '99'");
    while ($tax = xtc_db_fetch_array($tax_query)) {
      xtc_db_query("DELETE FROM ".TABLE_GEO_ZONES." WHERE geo_zone_id = '".$tax['tax_zone_id']."'");
      xtc_db_query("DELETE FROM ".TABLE_ZONES_TO_GEO_ZONES." WHERE geo_zone_id = '".$tax['tax_zone_id']."'");      
      xtc_db_query("DELETE FROM ".TABLE_TAX_CLASS." WHERE tax_class_id = '".$tax['tax_class_id']."'");      
    }
    
    xtc_db_query("DELETE FROM ".TABLE_TAX_RATES." WHERE tax_priority = '99'");
  }

  // keys
  function keys() {  
    return array();
  }
}
?>