<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class tax_eu
{
  var $code;
  var $title;
  var $description;
  var $sort_order;
  var $enabled;
  var $properties;
  var $_check;

  var $additional_countries;

  function __construct() {
    $this->code = 'tax_eu';
    $this->title = MODULE_TAX_EU_TEXT_TITLE;
    $this->description = MODULE_TAX_EU_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_TAX_EU_SORT_ORDER')) ? MODULE_TAX_EU_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_TAX_EU_STATUS') && MODULE_TAX_EU_STATUS == 'true') ? true : false);

    $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE. '</a>';
    
    $this->additional_countries = array(
      'FR' => array(
        'MC', // Monaco
      ),
    );
  }

  function process($file) {
    //do nothing
  }

  function display() {
    return array('text' => '<div align="center">' . MODULE_TAX_EU_TEXT_DESCRIPTION_PROCESSED . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)) . '</div>');
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_TAX_EU_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE configuration_key = 'MODULE_TAX_EU_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function update() {
    global $messageStack;
    
    // include needed classes
    require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'modified_api.php');

    modified_api::reset();
    $tax_rates_array = modified_api::request('modified/tax/');

    if (count($tax_rates_array) > 0) {
      $countries_array = array();
      $countries_query = xtc_db_query("SELECT countries_id,
                                              countries_iso_code_2
                                         FROM ".TABLE_COUNTRIES."
                                     ORDER BY countries_name");
      while ($countries = xtc_db_fetch_array($countries_query)) {
        $countries_array[$countries['countries_iso_code_2']] = $countries['countries_id'];
      }
      
      $geo_zones_array = array();
      if (defined('MODULE_TAX_EU_GEO_ZONES')) {
        $geozones = preg_split("/[:,]/", MODULE_TAX_EU_GEO_ZONES); 
        for ($i=0, $n=count($geozones); $i<$n; $i+=2) {
          $geo_zones_array[$geozones[$i]] = $geozones[$i+1];
        }    
      }
  
      foreach ($tax_rates_array as $tax_rates_country) {
        foreach ($tax_rates_country as $iso_code_2 => $tax_rates_info) {
          
          $action = 'update';
          if (!isset($geo_zones_array[$iso_code_2])) {
            $check_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_GEO_ZONES."
                                          WHERE geo_zone_name LIKE ('%Steuerzone ".$iso_code_2."%')");
            if (xtc_db_num_rows($check_query) == 0) {
              $sql_data_array = array(
                'geo_zone_name' => sprintf('DE::Steuerzone %s||EN::Tax zone %s', $iso_code_2, $iso_code_2),
                'geo_zone_description' => sprintf('DE::Steuerzone %s||EN::Tax zone %s', $iso_code_2, $iso_code_2),
                'date_added' => 'now()'
              );
              xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array);
              $geo_zones_array[$iso_code_2] = xtc_db_insert_id();
              $action = 'insert';
            } else {
              $check = xtc_db_fetch_array($check_query);
              $geo_zones_array[$iso_code_2] = $check['geo_zone_id'];
            }
          }
          
          xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                           SET geo_zone_id = ".$geo_zones_array[$iso_code_2].",
                               last_modified = now()
                         WHERE zone_country_id = ".$countries_array[$iso_code_2]);
          
          if (isset($this->additional_countries[$iso_code_2])) {
            foreach ($this->additional_countries[$iso_code_2] as $iso_code_2_additional) {
              xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                               SET geo_zone_id = ".$geo_zones_array[$iso_code_2].",
                                   last_modified = now()
                             WHERE zone_country_id = ".$countries_array[$iso_code_2_additional]);
            }
          }
                                                  
          foreach ($tax_rates_info as $tax_class_id => $tax_rate) {
            $check_query = xtc_db_query("SELECT *
                                           FROM ".TABLE_TAX_RATES."
                                          WHERE tax_class_id = ".$tax_class_id."
                                            AND tax_zone_id = ".$geo_zones_array[$iso_code_2]);
            if (xtc_db_num_rows($check_query) == 0 && $tax_rate != '') {
              $sql_data_array = array(
                'tax_zone_id' => $geo_zones_array[$iso_code_2],
                'tax_class_id' => $tax_class_id,
                'tax_priority' => '1',
                'tax_rate' => $tax_rate,
                'tax_description' => sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate),
                'date_added' => 'now()'
              );
              xtc_db_perform(TABLE_TAX_RATES, $sql_data_array);
            } else {
              $check = xtc_db_fetch_array($check_query);
              
              if (isset($check['tax_rates_id'])) {       
                if ($tax_rate != '') {
                  xtc_db_query("UPDATE ".TABLE_TAX_RATES."
                                   SET tax_rate = ".$tax_rate.",
                                       tax_description = '".xtc_db_input(sprintf('DE::MwSt. %s%%||EN::VAT %s%%', $tax_rate, $tax_rate))."',
                                       last_modified = now()
                                 WHERE tax_rates_id = ".$check['tax_rates_id']);
                } else {
                  xtc_db_query("DELETE FROM ".TABLE_TAX_RATES." WHERE tax_rates_id = ".$check['tax_rates_id']);
                }
              }
            }
          }
        }
      }
      
      $configuration = array();
      foreach ($geo_zones_array as $key => $val) {
        $configuration[] = $key.':'.$val;
      }
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                       SET configuration_value = '".implode(',', $configuration)."'
                     WHERE configuration_key = 'MODULE_TAX_EU_GEO_ZONES'");    
    } else {
      $messageStack->add_session(MODULE_TAX_EU_ERROR_API);
    }
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_TAX_EU_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_TAX_EU_TAX_CLASS_ID', '',  '6', '1', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_TAX_EU_GEO_ZONES', '',  '6', '1', now())");
    $this->update();
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_TAX_EU_%'");

    $geo_zones_array = array();
    if (defined('MODULE_TAX_EU_GEO_ZONES')) {
      $geozones = preg_split("/[:,]/", MODULE_TAX_EU_GEO_ZONES); 
      for ($i=0, $n=count($geozones); $i<$n; $i+=2) {
        $geo_zones_array[$geozones[$i]] = $geozones[$i+1];
      }    
    }

    $check_query = xtc_db_query("SELECT *
                                   FROM ".TABLE_GEO_ZONES."
                                  WHERE geo_zone_name LIKE ('%Steuerzone EU%')");
    if (xtc_db_num_rows($check_query) == 0) {
      $sql_data_array = array(
        'geo_zone_name' => 'DE::Steuerzone EU||EN::Tax zone EU',
        'geo_zone_description' => 'DE::Steuerzone EU||EN::Tax zone EU',
        'date_added' => 'now()'
      );
      xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array);
      $geo_zone_id = xtc_db_insert_id();
      $action = 'insert';
    } else {
      $check = xtc_db_fetch_array($check_query);
      $geo_zone_id = $check['geo_zone_id'];
    }

    foreach ($geo_zones_array as $iso_code_2 => $tax_zone_id) {
      xtc_db_query("UPDATE ".TABLE_ZONES_TO_GEO_ZONES."
                       SET geo_zone_id = ".$geo_zone_id.",
                           last_modified = now()
                     WHERE geo_zone_id = ".$tax_zone_id);

      xtc_db_query("DELETE FROM ".TABLE_GEO_ZONES." WHERE geo_zone_id = '".$tax_zone_id."'");
      xtc_db_query("DELETE FROM ".TABLE_TAX_RATES." WHERE tax_zone_id = '".$tax_zone_id."'");      
    }    
  }

  // keys
  function keys() {  
    return array();
  }
}
