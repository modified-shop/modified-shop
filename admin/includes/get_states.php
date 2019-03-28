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
    $check_query = xtc_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$_POST['countryid'] . "'");
    $check = xtc_db_fetch_array($check_query);
    $entry_state_has_zones = ($check['total'] > 0);
    
    $zone_name = isset($_POST['zone']) ? $_POST['zone'] : '';
    $zone_name =  DB_SERVER_CHARSET == 'latin1' ? utf8_decode($zone_name) : $zone_name;
    
    $dbQuery = xtc_db_query(
      "SELECT * FROM ".TABLE_COUNTRIES."
         WHERE countries_id ='". (int)$_POST['countryid'] ."' 
         AND required_zones = 1
      ");
      
    $zone_check = xtc_db_num_rows($dbQuery);

    if ($check['total'] > 0)
    {
      //if ($zone_check > 0) {
          $zones_array = array();
          $zones_query = xtc_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$_POST['countryid'] . "' order by zone_name");
          while ($zones_values = xtc_db_fetch_array($zones_query)) {
            $zones_array[] = array('id' => ($zones_values['zone_name']), 'text' => ($zones_values['zone_name']));
          }
          $t_output =  xtc_draw_pull_down_menu('entry_state', $zones_array, (isset($_POST['zone']) ? $zone_name : ''), 'class="select_states"' );
      //} else {
          //$t_output = xtc_draw_hidden_field('entry_state', '0');
      //}        
      
    }
    else
    {
      $t_output =  xtc_draw_input_field('entry_state', (isset($_POST['zone']) && !isset($_POST['type']) ? $zone_name : ''));
    }
    $json_output = $t_output;
    echo $json_output;
    EXIT;
}