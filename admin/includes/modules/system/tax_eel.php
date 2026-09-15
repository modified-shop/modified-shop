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
  var $description;
  var $sort_order;
  var $enabled;
  var $properties;
  var $_check;

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
    global $messageStack;

    // include needed functions
    require_once(DIR_FS_INC.'get_tax_eu_rates.inc.php');

    // the response is fetched and checked for both tax modules in one place
    $api_rates_array = get_tax_eu_rates($success);

    if ($api_rates_array === false) {
      $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

      return false;
    }

    $countries_array = array();
    $countries_query = xtc_db_query("SELECT countries_id,
                                            countries_iso_code_2
                                       FROM ".TABLE_COUNTRIES);
    if ($countries_query === false) {
      $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

      return false;
    }

    while ($countries = xtc_db_fetch_array($countries_query)) {
      $countries_array[$countries['countries_iso_code_2']] = (int)$countries['countries_id'];
    }

    // this module only uses the standard rate, it carries it in its own tax class
    $tax_array = array();
    foreach ($api_rates_array as $iso_code_2 => $rates_array) {
      if (!isset($rates_array[1]) || $rates_array[1] == '' || !isset($countries_array[$iso_code_2])) {
        $success = false;
        continue;
      }

      $tax_array[$iso_code_2] = $rates_array[1];
    }

    if (count($tax_array) == 0) {
      $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

      return false;
    }

    if (!defined('MODULE_TAX_EEL_TAX_CLASS_ID') || MODULE_TAX_EEL_TAX_CLASS_ID == '') {
      $sql_data_array = array(
        'tax_class_title' => 'DE::Standardsatz VP||EN::Default rate VP',
        'tax_class_description' => 'DE::elektronisch erbrachte Leistungen||EN::Services provided electronically',
        'date_added' => 'now()',
        'sort_order' => '99',
      );
      if (xtc_db_perform(TABLE_TAX_CLASS, $sql_data_array) === false) {
        $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

        return false;
      }

      $tax_class_id = (int)xtc_db_insert_id();

      if (xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = '".$tax_class_id."'
                         WHERE configuration_key = 'MODULE_TAX_EEL_TAX_CLASS_ID'") === false) {
        $success = false;
      }
    } else {
      $tax_class_id = (int)MODULE_TAX_EEL_TAX_CLASS_ID;
    }

    $geo_zone_ids_array = array();
    $geo_zones_query = xtc_db_query("SELECT geo_zone_id
                                       FROM ".TABLE_GEO_ZONES);
    if ($geo_zones_query === false) {
      $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

      return false;
    }

    while ($geo_zones = xtc_db_fetch_array($geo_zones_query)) {
      $geo_zone_ids_array[] = (int)$geo_zones['geo_zone_id'];
    }

    // a truncated or stale configuration value must not point at a wrong zone
    $geo_zones_array = array();
    if (defined('MODULE_TAX_EEL_GEO_ZONES') && MODULE_TAX_EEL_GEO_ZONES != '') {
      $geozones = preg_split("/[:,]/", MODULE_TAX_EEL_GEO_ZONES);
      for ($i=0, $n=count($geozones); $i+1<$n; $i+=2) {
        if (preg_match('/^[A-Z]{2}$/D', $geozones[$i]) === 1
            && ctype_digit($geozones[$i+1])
            && in_array((int)$geozones[$i+1], $geo_zone_ids_array)
            )
        {
          $geo_zones_array[$geozones[$i]] = (int)$geozones[$i+1];
        }
      }
    }

    foreach ($tax_array as $iso_code_2 => $tax_rate) {
      $action = 'update';
      if (!isset($geo_zones_array[$iso_code_2])) {
        $sql_data_array = array(
          'geo_zone_name' => sprintf('DE::Steuerzone VP - %s||EN::Tax zone VP - %s', $iso_code_2, $iso_code_2),
          'date_added' => 'now()'
        );
        if (xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array) === false) {
          $success = false;
          continue;
        }

        $geo_zones_array[$iso_code_2] = (int)xtc_db_insert_id();
        $action = 'insert';
      }

      $sql_data_array = array(
        'zone_country_id' => $countries_array[$iso_code_2],
        'zone_id' => '0',
        'geo_zone_id' => $geo_zones_array[$iso_code_2],
      );

      if ($action == 'insert') {
        $sql_data_array['date_added'] = 'now()';
      } else {
        $sql_data_array['last_modified'] = 'now()';
      }

      if (xtc_db_perform(TABLE_ZONES_TO_GEO_ZONES, $sql_data_array, $action, "zone_country_id = '".(int)$sql_data_array['zone_country_id']."' AND geo_zone_id = '".(int)$sql_data_array['geo_zone_id']."'") === false) {
        $success = false;
      }

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

      if (xtc_db_perform(TABLE_TAX_RATES, $sql_data_array, $action, "tax_zone_id = '".(int)$sql_data_array['tax_zone_id']."' AND tax_class_id = '".(int)$sql_data_array['tax_class_id']."'") === false) {
        $success = false;
      }
    }

    $configuration = array();
    foreach ($geo_zones_array as $key => $val) {
      $configuration[] = $key.':'.$val;
    }
    if (xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                         SET configuration_value = '".xtc_db_input(implode(',', $configuration))."'
                       WHERE configuration_key = 'MODULE_TAX_EEL_GEO_ZONES'") === false) {
      $success = false;
    }

    // module_export.php prints a non-empty return value instead of MODULE_UPDATE_CONFIRM
    if ($success === false) {
      $messageStack->add_session(MODULE_TAX_EEL_ERROR_API);

      return false;
    }
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
