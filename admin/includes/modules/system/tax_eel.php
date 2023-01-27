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

  function __construct() {
    $this->code = 'tax_eel';
    $this->title = MODULE_TAX_EEL_TEXT_TITLE;
    $this->description = MODULE_TAX_EEL_TEXT_DESCRIPTION;
    $this->enabled = ((defined('MODULE_TAX_EEL_STATUS') && MODULE_TAX_EEL_STATUS == 'true') ? true : false);
    $this->sort_order = '';
    
    $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE. '</a>';
  }
 
  function process() {
    $this->update();
  }

  // display
  function display() {
    return array('text' => '<div align="center">' . MODULE_TAX_EEL_TEXT_DESCRIPTION_PROCESSED . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)) . '</div>');
  }

  // check
  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_TAX_EEL_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_TAX_EEL_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }
  
  function update() {
    // include needed classes
    require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'modified_api.php');

    modified_api::reset();
    $response = modified_api::request('modified/tax/');
    
    $tax_array = array();
    foreach ($response as $data) {
      $key = key($data);
      $tax_array[$key] = $data[$key][1];
    }

    if (!defined('MODULE_TAX_EEL_TAX_CLASS_ID') || MODULE_TAX_EEL_TAX_CLASS_ID == '') {
      $sql_data_array = array(
        'tax_class_title' => 'DE::Standardsatz VP||EN::Default rate VP',
        'tax_class_description' => 'DE::elektronisch erbrachte Leistungen||EN::Services provided electronically',
        'date_added' => 'now()',
        'sort_order' => '99',
      );
      xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array);                       
      $tax_class_id = xtc_db_insert_id();
    
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                       SET configuration_value = '".$tax_class_id."'
                     WHERE configuration_key = 'MODULE_TAX_EEL_TAX_CLASS_ID'");
    } else {
      $tax_class_id = MODULE_TAX_EEL_TAX_CLASS_ID;
    }
    
    $geo_zones_array = array();
    if (defined('MODULE_TAX_EEL_GEO_ZONES')) {
      $geozones = preg_split("/[:,]/", MODULE_TAX_EEL_GEO_ZONES); 
      for ($i=0, $n=count($geozones); $i<$n; $i+=2) {
        $geo_zones_array[$geozones[$i]] = $geozones[$i+1];
      }    
    }
    
    foreach ($tax_array as $iso_code_2 => $tax_rate) {
      $countries_query = xtc_db_query("SELECT countries_id 
                                         FROM ".TABLE_COUNTRIES." 
                                        WHERE countries_iso_code_2 = '".$iso_code_2."'");
      if (xtc_db_num_rows($countries_query) == 1) {
        $countries = xtc_db_fetch_array($countries_query);
        
        $action = 'update';
        if (!isset($geo_zones_array[$iso_code_2])) {
          $sql_data_array = array(
            'geo_zone_name' => sprintf('DE::Steuerzone VP - %s||EN::Tax zone VP - %s', $iso_code_2, $iso_code_2),
            'date_added' => 'now()'
          );
          xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array);
          $geo_zones_array[$iso_code_2] = xtc_db_insert_id();
          $action = 'insert';
        }
        
        $sql_data_array = array(
          'zone_country_id' => $countries['countries_id'],
          'zone_id' => '0',
          'geo_zone_id' => $geo_zones_array[$iso_code_2],
        );
        
        if ($action == 'insert') {
          $sql_data_array['date_added'] = 'now()';
        } else {
          $sql_data_array['last_modified'] = 'now()';        
        }
        
        xtc_db_perform(TABLE_ZONES_TO_GEO_ZONES, $sql_data_array, $action, "zone_country_id = '".$sql_data_array['zone_country_id']."' AND geo_zone_id = '".$sql_data_array['geo_zone_id']."'");
  
        $sql_data_array = array(
          'tax_zone_id' => $geo_zones_array[$iso_code_2],
          'tax_class_id' => $tax_class_id,
          'tax_priority' => '99',
          'tax_rate' => $tax_rate,
          'tax_description' => sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate),
        );

        if ($action == 'insert') {
          $sql_data_array['date_added'] = 'now()';
        } else {
          $sql_data_array['last_modified'] = 'now()';        
        }

        xtc_db_perform(TABLE_TAX_RATES, $sql_data_array, $action, "tax_zone_id = '".$sql_data_array['tax_zone_id']."' AND tax_class_id = '".$sql_data_array['tax_class_id']."'");
      }
    }
    
    $configuration = array();
    foreach ($geo_zones_array as $key => $val) {
      $configuration[] = $key.':'.$val;
    }
    xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                     SET configuration_value = '".implode(',', $configuration)."'
                   WHERE configuration_key = 'MODULE_TAX_EEL_GEO_ZONES'");
  }
  
  // install
  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_TAX_EEL_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_TAX_EEL_TAX_CLASS_ID', '',  '6', '1', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_TAX_EEL_GEO_ZONES', '',  '6', '1', now())");
    $this->update();
  }
    
  // remove
  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_TAX_EEL_%'");
    
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