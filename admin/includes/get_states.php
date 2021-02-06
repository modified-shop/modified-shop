<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
   
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if (isset($_POST['action']) && ($_POST['action'] == 'get_states')) {
    $check_query = xtc_db_query("SELECT count(*) as total 
                                   FROM " . TABLE_ZONES . " 
                                  WHERE zone_country_id = '" . (int)$_POST['countryid'] . "'");
    $check = xtc_db_fetch_array($check_query);
    $entry_state_has_zones = ($check['total'] > 0);
  
    $zone_name = isset($_POST['zone']) ? $_POST['zone'] : '';
    $zone_name =  DB_SERVER_CHARSET == 'latin1' ? utf8_decode($zone_name) : $zone_name;
  
    $field = 'entry_state';
    if (isset($_POST['field'])) {
      $field = $_POST['field'];
    }
  
    if ($check['total'] > 0) {
      $zones_array = array();
      $zones_query = xtc_db_query("SELECT zone_name,
                                          zone_code 
                                     FROM " . TABLE_ZONES . " 
                                    WHERE zone_country_id = '" . (int)$_POST['countryid'] . "' 
                                 ORDER BY zone_name");
      while ($zones_values = xtc_db_fetch_array($zones_query)) {
        $zones_array[] = array('id' => ($zones_values['zone_code']), 'text' => ($zones_values['zone_name']));
      }
      $t_output =  xtc_draw_pull_down_menu($field, $zones_array, (isset($_POST['zone']) ? $zone_name : ''), 'class="select_states"'.((isset($styles)) ? $styles : ''));
    } else {
      $t_output =  xtc_draw_input_field($field, (isset($_POST['zone']) && !isset($_POST['type']) ? $zone_name : ''), ((isset($styles)) ? $styles : ''));
    }

    echo $t_output;
    exit();
  }