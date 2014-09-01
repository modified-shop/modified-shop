<?php
/* -----------------------------------------------------------------------------------------
   $Id: dhl.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dhl.php,v 1.02 2003/02/18); www.oscommerce.com 
   (c) 2003	 nextcommerce (dhl.php,v 1.10 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   dhl_austria_1.02       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
   



  class dhl {
    var $code, $title, $description, $icon, $enabled, $num_dhl, $types;

/**
 * class constructor
 */
    function dhl() {
      global $order;

      $this->code = 'dhl';
      $this->title = MODULE_SHIPPING_DHL_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_DHL_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_DHL_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_dhl.gif';
      $this->tax_class = MODULE_SHIPPING_DHL_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_DHL_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_DHL_ZONE > 0) ) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_DHL_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = xtc_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

      $this->types = array('ECX' => 'EU Express Service',
                           'DOX' => 'Document Express Service',
                           'SDX' => 'Start Day Express Service',
                           'MDX' => 'Mid Day Express Service',
                           'WPX' => 'Waren Express Service');

/**
 * CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
 */
      $this->num_dhl = 10;
    }

/**
 * class methods
 */
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes;

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($j=1; $j<=$this->num_dhl; $j++) {
        $countries_table = constant('MODULE_SHIPPING_DHL_COUNTRIES_' . $j);
        $country_zones = explode(",", $countries_table); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $j;
          break;
        }
      }

      if ($dest_zone == 0) {
        $error = true;
      } else {
        $shipping = -1;
        $dhl_cost_ecx = @constant('MODULE_SHIPPING_DHL_COST_ECX_' . $j);
        $dhl_cost_dox = @constant('MODULE_SHIPPING_DHL_COST_DOX_' . $j);
        $dhl_cost_wpx = @constant('MODULE_SHIPPING_DHL_COST_WPX_' . $j);
        $dhl_cost_mdx = @constant('MODULE_SHIPPING_DHL_COST_MDX_' . $j);
        $dhl_cost_sdx = @constant('MODULE_SHIPPING_DHL_COST_SDX_' . $j);

        $methods = array();
        $n == 0;

        if ($dhl_cost_ecx != '') {
          $dhl_table_ecx = preg_split("/[:,]/" , $dhl_cost_ecx); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
          if ( ($shipping_weight > 10) and ($shipping_weight <= 20) ) {
            $shipping_ecx = number_format((($shipping_weight - 10)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_ECX_20_' .$j) + $dhl_table_ecx[count ($dhl_table_ecx)-1];
          } elseif ( ($shipping_weight > 20) and ($shipping_weight <= 30) ) {
            $shipping_ecx = number_format((($shipping_weight - 20)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_ECX_30_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_ECX_20_' .$j) + $dhl_table_ecx[count ($dhl_table_ecx)-1];
          } elseif ( ($shipping_weight > 30) and ($shipping_weight <= 50) ) {
            $shipping_ecx = number_format((($shipping_weight - 30)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_ECX_50_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_ECX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_ECX_30_' .$j) + $dhl_table_ecx[count ($dhl_table_ecx)-1];
          } elseif ($shipping_weight > 50) {
            $shipping_ecx = number_format((($shipping_weight - 50)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_ECX_51_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_ECX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_ECX_30_' .$j) + 40 * constant('MODULE_SHIPPING_DHL_STEP_ECX_50_' .$j) + $dhl_table_ecx[count ($dhl_table_ecx)-1];
          } else {

            for ($i=0; $i<sizeof($dhl_table_ecx); $i+=2) {
              if ($shipping_weight <= $dhl_table_ecx[$i]) {
                $shipping_ecx = $dhl_table_ecx[$i+1];
                break;
              }
            }
          }

          if ($shipping_ecx == -1) {
            $shipping_cost = 0;
            $shipping_method = MODULE_SHIPPING_DHL_UNDEFINED_RATE;
          } else {
            $shipping_cost_1 = ($shipping_ecx + MODULE_SHIPPING_DHL_HANDLING);
          }

          $methods[] = array('id' => 'ECX',
                             'title' => 'EU Express Service',
                             'cost' => (MODULE_SHIPPING_DHL_HANDLING + $shipping_cost_1) * $shipping_num_boxes);
          $n++;
        }

        if ($dhl_cost_dox != '') {          
		  $dhl_table_dox = preg_split("/[:,]/" , $dhl_cost_dox);  // Hetfield - 2009-11-19 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
          if ( ($shipping_weight > 10) and ($shipping_weight <= 20) ) {
            $shipping_dox = number_format((($shipping_weight - 10)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_DOX_20_' .$j) + $dhl_table_dox[count ($dhl_table_dox)-1];
          } elseif ( ($shipping_weight > 20) and ($shipping_weight <= 30) ) {
            $shipping_dox = number_format((($shipping_weight - 20)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_DOX_30_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_DOX_20_' .$j) + $dhl_table_dox[count ($dhl_table_dox)-1];
          } elseif ( ($shipping_weight > 30) and ($shipping_weight <= 50) ) {
            $shipping_dox = number_format((($shipping_weight - 30)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_DOX_50_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_DOX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_DOX_30_' .$j) + $dhl_table_dox[count ($dhl_table_dox)-1];
          } elseif ($shipping_weight > 50) {
            $shipping_dox = number_format((($shipping_weight - 50)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_DOX_51_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_DOX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_DOX_30_' .$j) + 40 * constant('MODULE_SHIPPING_DHL_STEP_DOX_50_' .$j) + $dhl_table_dox[count ($dhl_table_dox)-1];
          } else {

            for ($i=0; $i<sizeof($dhl_table_dox); $i+=2) {
              if ($shipping_weight <= $dhl_table_dox[$i]) {
                $shipping_dox = $dhl_table_dox[$i+1];
                break;
              }
            }
          }

          if ($shipping_dox == -1) {
            $shipping_cost = 0;
            $shipping_method = MODULE_SHIPPING_DHL_UNDEFINED_RATE;
          } else {
            $shipping_cost_2 = ($shipping_dox + MODULE_SHIPPING_DHL_HANDLING);
          }

          $methods[] = array('id' => 'DOX',
                             'title' => 'Document Express Service',
                             'cost' => (MODULE_SHIPPING_DHL_HANDLING + $shipping_cost_2) * $shipping_num_boxes);
          $n++;
        }  

        if ($dhl_cost_wpx != '') {
          $dhl_table_wpx = preg_split("/[:,]/" , $dhl_cost_wpx); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
          if ( ($shipping_weight > 10) and ($shipping_weight <= 20) ) {
            $shipping_wpx = number_format((($shipping_weight - 10)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_WPX_20_' .$j) + $dhl_table_wpx[count ($dhl_table_wpx)-1];
          } elseif ( ($shipping_weight > 20) and ($shipping_weight <= 30) ) {
            $shipping_wpx = number_format((($shipping_weight - 20)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_WPX_30_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_WPX_20_' .$j) + $dhl_table_wpx[count ($dhl_table_wpx)-1];
          } elseif ( ($shipping_weight > 30) and ($shipping_weight <= 50) ) {
            $shipping_wpx = number_format((($shipping_weight - 30)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_WPX_50_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_WPX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_WPX_30_' .$j) + $dhl_table_wpx[count ($dhl_table_wpx)-1];
          } elseif ($shipping_weight > 50) {
            $shipping_wpx = number_format((($shipping_weight - 50)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_WPX_51_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_WPX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_WPX_30_' .$j) + 40 * constant('MODULE_SHIPPING_DHL_STEP_WPX_50_' .$j) + $dhl_table_wpx[count ($dhl_table_wpx)-1];
          } else {

            for ($i=0; $i<sizeof($dhl_table_wpx); $i+=2) {
              if ($shipping_weight <= $dhl_table_wpx[$i]) {
                $shipping_wpx = $dhl_table_wpx[$i+1];
                break;
              }
            }
          }

          if ($shipping_wpx == -1) {
            $shipping_cost = 0;
            $shipping_method = MODULE_SHIPPING_DHL_UNDEFINED_RATE;
          } else {
            $shipping_cost_3 = ($shipping_wpx + MODULE_SHIPPING_DHL_HANDLING);
          }

          $methods[] = array('id' => 'WPX',
                             'title' => 'Waren Express Service',
                             'cost' => (MODULE_SHIPPING_DHL_HANDLING + $shipping_cost_3) * $shipping_num_boxes);
          $n++;
        }

        if ($dhl_cost_mdx != '') {
          $dhl_table_mdx = preg_split("/[:,]/" , $dhl_cost_mdx);  // Hetfield - 2009-11-19 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
          if ( ($shipping_weight > 10) and ($shipping_weight <= 20) ) {
            $shipping_mdx = number_format((($shipping_weight - 10)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_MDX_20_' .$j) + $dhl_table_mdx[count ($dhl_table_mdx)-1];
          } elseif ( ($shipping_weight > 20) and ($shipping_weight <= 30) ) {
            $shipping_mdx = number_format((($shipping_weight - 20)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_MDX_30_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_MDX_20_' .$j) + $dhl_table_mdx[count ($dhl_table_mdx)-1];
          } elseif ( ($shipping_weight > 30) and ($shipping_weight <= 50) ) {
            $shipping_mdx = number_format((($shipping_weight - 30)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_MDX_50_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_MDX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_MDX_30_' .$j) + $dhl_table_mdx[count ($dhl_table_mdx)-1];
          } elseif ($shipping_weight > 50) {
            $shipping_mdx = number_format((($shipping_weight - 50)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_MDX_51_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_MDX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_MDX_30_' .$j) + 40 * constant('MODULE_SHIPPING_DHL_STEP_MDX_50_' .$j) + $dhl_table_mdx[count ($dhl_table_mdx)-1];
          } else {

            for ($i=0; $i<sizeof($dhl_table_mdx); $i+=2) {
              if ($shipping_weight <= $dhl_table_mdx[$i]) {
                $shipping_mdx = $dhl_table_mdx[$i+1];
                break;
              }
            }
          }

          if ($shipping_mdx == -1) {
            $shipping_cost = 0;
            $shipping_method = MODULE_SHIPPING_DHL_UNDEFINED_RATE;
          } else {
            $shipping_cost_4 = ($shipping_mdx + MODULE_SHIPPING_DHL_HANDLING);
          }

          $methods[] = array('id' => 'MDX',
                             'title' => 'Mid Day Express Service',
                             'cost' => (MODULE_SHIPPING_DHL_HANDLING + $shipping_cost_4) * $shipping_num_boxes);
          $n++;
        }

        if ($dhl_cost_sdx != '') {
          $dhl_table_sdx = preg_split("/[:,]/" , $dhl_cost_sdx); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
          if ( ($shipping_weight > 10) and ($shipping_weight <= 20) ) {
            $shipping_sdx = number_format((($shipping_weight - 10)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_SDX_20_' .$j) + $dhl_table_sdx[count ($dhl_table_sdx)-1];
          } elseif ( ($shipping_weight > 20) and ($shipping_weight <= 30) ) {
            $shipping_sdx = number_format((($shipping_weight - 20)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_SDX_30_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_SDX_20_' .$j) + $dhl_table_sdx[count ($dhl_table_sdx)-1];
          } elseif ( ($shipping_weight > 30) and ($shipping_weight <= 50) ) {
            $shipping_sdx = number_format((($shipping_weight - 30)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_SDX_50_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_SDX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_SDX_30_' .$j) + $dhl_table_sdx[count ($dhl_table_sdx)-1];
          } elseif ($shipping_weight > 50) {
            $shipping_sdx = number_format((($shipping_weight - 50)* 2 + 0.5), 0) * constant('MODULE_SHIPPING_DHL_STEP_SDX_51_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_SDX_20_' .$j) + 20 * constant('MODULE_SHIPPING_DHL_STEP_SDX_30_' .$j) + 40 * constant('MODULE_SHIPPING_DHL_STEP_SDX_50_' .$j) + $dhl_table_sdx[count ($dhl_table_sdx)-1];
          } else {

            for ($i=0; $i<sizeof($dhl_table_sdx); $i+=2) {
              if ($shipping_weight <= $dhl_table_sdx[$i]) {
                $shipping_sdx = $dhl_table_sdx[$i+1];
                break;
              }
            }
          }

          if ($shipping_sdx == -1) {
            $shipping_cost = 0;
            $shipping_method = MODULE_SHIPPING_DHL_UNDEFINED_RATE;
          } else {
            $shipping_cost_5 = ($shipping_sdx + MODULE_SHIPPING_DHL_HANDLING);
          }

          $methods[] = array('id' => 'SDX',
                             'title' => 'Start Day Express Service',
                             'cost' => (MODULE_SHIPPING_DHL_HANDLING + $shipping_cost_5) * $shipping_num_boxes);
          $n++;
        }
      }

      $this->quotes = array('id' => $this->code,
                            'module' => $this->title . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_DHL_TEXT_UNITS .')');

      $this->quotes['methods'] = $methods;

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_DHL_INVALID_ZONE;

      if ( (xtc_not_null($method)) && (isset($this->types[$method])) ) {

        for ($i=0; $i<sizeof($methods); $i++) {
          if ($method == $methods[$i]['id']) {
            $methodsc = array();
            $methodsc[] = array('id' => $methods[$i]['id'],
                                'title' => $methods[$i]['title'],
                                'cost' => $methods[$i]['cost']);
            break;
          }
        }
        $this->quotes['methods'] = $methodsc;
      }

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_DHL_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STATUS', 'True', 6, 0, NULL, now(), NULL, 'xtc_cfg_select_option(array(\'True\', \'False\'),')");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_HANDLING', '0', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_TAX_CLASS', '0', 6, 0, NULL, now(), 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(')");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_ZONE', '0', 6, 0, NULL, now(), 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(')");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_SORT_ORDER', '0', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_ALLOWED', '', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_1', 'AT', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_ECX_1', '0.5:31.00,1:31.80,1.5:32.60,2:33.40,2.5:34.20,3:35.00,3.5:35.80,4:36.60,4.5:37.40,5:38.20,5.5:39.00,6:39.80,6.5:40.60,7:41.40,7.5:42.20,8:43.00,8.5:43.80,9:44.60,9.5:45.40,10:46.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_1', '0.5:46.50,1:47.30,1.5:48.10,2:49.90,2.5:49.70,3:50.50,3.5:51.30,4:52.10,4.5:52.90,5:53.70,5.5:54.50,6:55.30,6.5:56.10,7:56.90,7.5:57.70,8:58.50,8.5:59.30,9:60.10,9.5:60.90,10:61.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_1', '0.5:62.00,1:62.80,1.5:63.60,2:64.40,2.5:65.20,3:66.00,3.5:66.80,4:67.60,4.5:68.40,5:69.20,5.5:70.00,6:70.80,6.5:71.60,7:72.40,7.5:73.20,8:74.00,8.5:74.80,9:75.60,9.5:76.40,10:77.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_20_1', '0.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_30_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_50_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_51_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_1', '0.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_1', '0.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_1', '0.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_2', 'BE,GE,IT,LU,NL', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_ECX_2', '0.5:50.50,1:58.10,1.5:65.20,2:72.30,2.5:79.40,3:85.40,3.5:91.40,4:97.40,4.5:103.40,5:109.40,5.5:113.60,6:117.80,6.5:122.00,7:126.20,7.5:130.40,8:134.60,8.5:138.80,9:143.00,9.5:147.20,10:151.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_2', '0.5:69.50,1:77.60,1.5:84.80,2:92.00,2.5:99.20,3:105.60,3.5:112.00,4:118.40,4.5:124.80,5:131.20,5.5:137.20,6:143.20,6.5:149.20,7:155.20,7.5:161.20,8:167.20,8.5:173.20,9:179.20,9.5:185.20,10:191.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_2', '0.5:101.50,1:109.70,1.5:117.10,2:124.50,2.5:131.90,3:138.60,3.5:145.30,4:152.00,4.5:158.70,5:165.40,5.5:171.90,6:178.30,6.5:184.70,7:191.10,7.5:197.50,8:203.90,8.5:210.30,9:216.70,9.5:223.10,10:229.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_20_2', '1.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_30_2', '1.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_50_2', '1.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_51_2', '2.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_2', '2.00', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_2', '1.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_2', '1.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_2', '3.00', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_2', '2.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_2', '2.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_2', '2.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_2', '3.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_3', 'AD,DK,FI,FR,GR,IE,MC,PT,ES,SE,GB', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_ECX_3', '0.5:53.00,1:60.60,1.5:67.70,2:74.80,2.5:81.90,3:87.90,3.5:93.90,4:99.90,4.5:105.90,5:111.90,5.5:116.70,6:121.50,6.5:126.30,7:131.10,7.5:135.90,8:140.70,8.5:145.50,9:150.30,9.5:155.10,10:159.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_3', '0.5:71.00,1:79.20,1.5:86.50,2:93.80,2.5:101.10,3:107.70,3.5:114.30,4:120.90,4.5:127.50,5:134.10,5.5:140.60,6:147.10,6.5:153.60,7:160.10,7.5:166.60,8:173.10,8.5:179.60,9:186.10,9.5:192.60,10:199.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_3', '0.5:103.00,1:111.30,1.5:118.80,2:126.30,2.5:133.80,3:141.10,3.5:148.40,4:155.70,4.5:163.00,5:170.30,5.5:177.50,6:184.70,6.5:191.90,7:199.10,7.5:206.30,8:213.50,8.5:220.70,9:227.90,9.5:235.10,10:242.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_20_3', '1.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_30_3', '1.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_50_3', '1.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_ECX_51_3', '3.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_3', '2.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_3', '2.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_3', '2.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_3', '3.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_3', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_3', '2.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_3', '2.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_3', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_4', 'AL,HR,CZ,HU,LI,SI,SK,CH', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_4', '0.5:54.50,1:61.90,1.5:68.70,2:75.50,2.5:82.30,3:88.10,3.5:93.90,4:99.70,4.5:105.50,5:111.30,5.5:135.10,6:139.90,6.5:144.70,7:149.50,7.5:154.30,8:159.10,8.5:163.90,9:168.70,9.5:173.50,10:178.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_4', '0.5:74.50,1:81.70,1.5:88.40,2:95.10,2.5:101.80,3:107.50,3.5:113.20,4:118.90,4.5:124.60,5:130.30,5.5:135.10,6:139.90,6.5:144.70,7:149.50,7.5:154.30,8:159.10,8.5:163.90,9:168.70,9.5:173.50,10:178.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_4', '0.5:73.50,1:81.70,1.5:89.00,2:96.30,2.5:103.60,3:110.20,3.5:116.80,4:123.40,4.5:130.00,5:136.60,5.5:166.60,6:173.10,6.5:179.60,7:186.10,7.5:192.60,8:199.10,8.5:205.60,9:212.10,9.5:218.60,10:225.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_4', '0.5:106.50,1:114.80,1.5:122.30,2:129.80,2.5:137.30,3:144.60,3.5:151.90,4:159.20,4.5:166.50,5:173.80,5.5:204.30,6:211.30,6.5:218.30,7:225.30,7.5:232.30,8:239.30,8.5:246.30,9:253.30,9.5:260.30,10:267.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_4', '1.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_4', '2.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_4', '2.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_4', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_4', '1.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_4', '2.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_4', '2.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_4', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_4', '2.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_4', '2.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_4', '2.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_4', '3.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_4', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_4', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_4', '3.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_4', '4.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_5', 'BA,BG,CY,EE,FO,GI,GL,IS,LV,LT,MT,MK,NO,PL,RO,TR,YU', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_5', '0.5:56.00,1:63.40,1.5:70.30,2:77.20,2.5:84.10,3:89.90,3.5:95.70,4:101.50,4.5:107.30,5:113.10,5.5:137.50,6:142.30,6.5:147.10,7:151.90,7.5:156.70,8:161.50,8.5:166.30,9:171.10,9.5:175.90,10:180.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_5', '0.5:76.00,1:83.30,1.5:90.10,2:96.90,2.5:103.70,3:109.50,3.5:115.30,4:121.10,4.5:126.90,5:132.70,5.5:137.50,6:142.30,6.5:147.10,7:151.90,7.5:156.70,8:161.50,8.5:166.30,9:171.10,9.5:175.90,10:180.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_5', '0.5:76.00,1:84.20,1.5:91.50,2:98.80,2.5:106.10,3:112.70,3.5:119.30,4:125.90,4.5:132.50,5:139.10,5.5:169.10,6:175.60,6.5:182.10,7:188.60,7.5:195.10,8:201.60,8.5:208.10,9:214.60,9.5:221.10,10:227.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_5', '0.5:109.00,1:117.30,1.5:124.80,2:132.30,2.5:139.80,3:147.10,3.5:154.40,4:161.70,4.5:169.00,5:176.30,5.5:207.30,6:214.30,6.5:221.30,7:228.30,7.5:235.30,8:242.30,8.5:249.30,9:256.30,9.5:263.30,10:270.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_5', '2.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_5', '3.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_5', '3.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_5', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_5', '2.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_5', '3.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_5', '3.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_5', '3.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_5', '3.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_5', '3.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_5', '3.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_5', '3.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_5', '4.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_5', '4.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_5', '4.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_5', '4.60', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_6', 'CA,US', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_6', '0.5:56.00,1:64.40,1.5:72.40,2:80.40,2.5:88.40,3:96.30,3.5:104.20,4:112.10,4.5:120.00,5:127.90,5.5:155.80,6:163.70,6.5:171.60,7:179.50,7.5:187.40,8:195.30,8.5:203.20,9:211.10,9.5:219.00,10:226.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_6', '0.5:76.00,1:84.40,1.5:92.40,2:100.40,2.5:108.40,3:116.30,3.5:124.20,4:132.10,4.5:140.00,5:147.90,5.5:155.80,6:163.70,6.5:171.60,7:179.50,7.5:187.40,8:195.30,8.5:203.20,9:211.10,9.5:219.00,10:226.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_6', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_6', '4.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_6', '4.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_6', '5.00', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_6', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_6', '4.80', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_6', '4.90', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_6', '5.00', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_7', 'BY,HK,MO,SG,TH,UA,RU', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_7', '0.5:63.00,1:82.30,1.5:100.60,2:118.90,2.5:137.20,3:155.00,3.5:172.80,4:190.60,4.5:208.40,5:226.20,5.5:256.40,6:266.60,6.5:276.80,7:287.00,7.5:297.20,8:307.40,8.5:317.60,9:327.80,9.5:338.00,10:348.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_7', '0.5:83.00,1:102.30,1.5:120.60,2:138.90,2.5:157.20,3:175.00,3.5:192.80,4:210.60,4.5:228.40,5:246.20,5.5:256.40,6:266.60,6.5:276.80,7:287.00,7.5:297.20,8:307.40,8.5:317.60,9:327.80,9.5:338.00,10:348.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_7', '5.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_7', '4.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_7', '5.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_8', 'AU,BD,BN,CN,IN,ID,IR,JP,MY,MX,NZ,PH,SA,LK,TW,VE,VN', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_8', '0.5:69.50,1:89.00,1.5:107.40,2:125.80,2.5:144.20,3:162.20,3.5:180.20,4:198.20,4.5:216.20,5:234.20,5.5:264.50,6:274.80,6.5:285.10,7:295.40,7.5:305.70,8:316.00,8.5:326.30,9:336.60,9.5:346.90,10:357.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_8', '0.5:89.50,1:109.00,1.5:127.40,2:145.80,2.5:164.20,3:182.20,3.5:200.20,4:218.20,4.5:236.20,5:254.20,5.5:264.50,6:274.80,6.5:285.10,7:295.40,7.5:305.70,8:316.00,8.5:326.30,9:336.60,9.5:346.90,10:357.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_8', '0.5:94.50,1:119.50,1.5:139.50,2:159.50,2.5:179.50,3:199.00,3.5:218.50,4:238.00,4.5:257.50,5:277.00,5.5:310.25,6:321.25,6.5:332.25,7:343.25,7.5:354.25,8:365.25,8.5:376.25,9:387.25,9.5:398.25,10:409.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_8', '0.5:117.00,1:147.00,1.5:172.50,2:198.00,2.5:223.50,3:248.25,3.5:273.00,4:297.75,4.5:322.50,5:347.25,5.5:387.25,6:401.25,6.5:415.25,7:429.25,7.5:443.25,8:457.25,8.5:471.25,9:485.25,9.5:499.25,10:513.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_8', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_8', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_8', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_8', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_8', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_8', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_8', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_8', '6.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_8', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_8', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_8', '7.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_9', 'AF,DZ,AO,AI,AG,AR,AM,AW,AZ,BS,BH,BB,BZ,BJ,BM,BT,BO,BW,BR,BF,BI,KY,KH,CM,CV,CL,CK,CR,DJ,DM,DO,CG,KM,CO,TD,CF,CU,EC,SV,EG,GQ,ET,ER,FK,FJ,GF,GA,GM,GE,GH,GD,GP,GU,GT,GN,GW,GY,HT,HN,IL,JM,JO,KZ,KE,KG,KI,KP,KR,KW,LA,LS,LB,LR', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_9', '0.5:75.50,1:95.00,1.5:113.40,2:131.80,2.5:150.20,3:168.20,3.5:186.20,4:204.20,4.5:222.20,5:240.20,5.5:270.50,6:280.80,6.5:291.10,7:301.40,7.5:311.70,8:322.00,8.5:332.30,9:342.60,9.5:352.90,10:363.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_9', '0.5:95.50,1:115.00,1.5:133.40,2:151.80,2.5:170.20,3:188.20,3.5:206.20,4:224.20,4.5:242.20,5:260.20,5.5:270.50,6:280.80,6.5:291.10,7:301.40,7.5:311.70,8:322.00,8.5:332.30,9:342.60,9.5:352.90,10:363.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_9', '0.5:103.50,1:128.50,1.5:148.50,2:168.50,2.5:188.50,3:208.00,3.5:227.50,4:247.00,4.5:266.50,5:286.00,5.5:319.25,6:330.25,6.5:341.25,7:352.25,7.5:363.25,8:374.25,8.5:385.25,9:396.25,9.5:407.25,10:418.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_9', '0.5:127.50,1:157.50,1.5:183.00,2:208.50,2.5:234.00,3:258.75,3.5:283.50,4:308.25,4.5:333.00,5:357.75,5.5:397.75,6:411.75,6.5:425.75,7:439.75,7.5:453.75,8:467.75,8.5:481.75,9:495.75,9.5:509.75,10:523.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_9', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_9', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_9', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_9', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_9', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_9', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_9', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_9', '6.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_9', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_9', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_9', '7.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COUNTRIES_10', 'LY,MG,MW,MV,ML,MH,MQ,MR,MU,MD,MN,MS,MZ,MM,NA,NR,NP,NC,NI,NE,NG,NU,OM,PK,PA,PG,PY,PE,PR,RE,RW,KN,LC,VC,QA,WS,SN,SC,SL,ZA,SO,SB,SD,SR,ST,SZ,SY,TJ,TZ,TP,TG,TO,TT,TN,TM,TC,TV,UG,AE,UY,UZ,VU,ZM,YE,ZW,VG,VI', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_DOX_10', '0.5:75.50,1:95.00,1.5:113.40,2:131.80,2.5:150.20,3:168.20,3.5:186.20,4:204.20,4.5:222.20,5:240.20,5.5:270.50,6:280.80,6.5:291.10,7:301.40,7.5:311.70,8:322.00,8.5:332.30,9:342.60,9.5:352.90,10:363.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_WPX_10', '0.5:95.50,1:115.00,1.5:133.40,2:151.80,2.5:170.20,3:188.20,3.5:206.20,4:224.20,4.5:242.20,5:260.20,5.5:270.50,6:280.80,6.5:291.10,7:301.40,7.5:311.70,8:322.00,8.5:332.30,9:342.60,9.5:352.90,10:363.20', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_MDX_10', '0.5:103.50,1:128.50,1.5:148.50,2:168.50,2.5:188.50,3:208.00,3.5:227.50,4:247.00,4.5:266.50,5:286.00,5.5:319.25,6:330.25,6.5:341.25,7:352.25,7.5:363.25,8:374.25,8.5:385.25,9:396.25,9.5:407.25,10:418.25', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_COST_SDX_10', '0.5:127.50,1:157.50,1.5:183.00,2:208.50,2.5:234.00,3:258.75,3.5:283.50,4:308.25,4.5:333.00,5:357.75,5.5:397.75,6:411.75,6.5:425.75,7:439.75,7.5:453.75,8:467.75,8.5:481.75,9:495.75,9.5:509.75,10:523.75', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_20_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_30_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_50_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_DOX_51_10', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_20_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_30_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_50_10', '4.40', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_WPX_51_10', '5.50', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_20_10', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_30_10', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_50_10', '5.10', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_MDX_51_10', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_20_10', '6.70', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_30_10', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_50_10', '6.30', 6, 0, NULL, now(), NULL, NULL)");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('MODULE_SHIPPING_DHL_STEP_SDX_51_10', '7.70', 6, 0, NULL, now(), NULL, NULL)");
    }


    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_DHL_STATUS', 'MODULE_SHIPPING_DHL_HANDLING','MODULE_SHIPPING_DHL_ALLOWED', 'MODULE_SHIPPING_DHL_TAX_CLASS', 'MODULE_SHIPPING_DHL_ZONE', 'MODULE_SHIPPING_DHL_SORT_ORDER');

      for ($i = 1; $i <= $this->num_dhl; $i ++) {
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COUNTRIES_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COST_ECX_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COST_DOX_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COST_WPX_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COST_MDX_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_COST_SDX_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_ECX_20_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_ECX_30_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_ECX_50_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_ECX_51_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_DOX_20_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_DOX_30_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_DOX_50_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_DOX_51_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_WPX_20_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_WPX_30_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_WPX_50_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_WPX_51_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_MDX_20_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_MDX_30_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_MDX_50_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_MDX_51_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_SDX_20_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_SDX_30_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_SDX_50_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_DHL_STEP_SDX_51_' . $i;
      }

      return $keys;
    }
  }
?>
