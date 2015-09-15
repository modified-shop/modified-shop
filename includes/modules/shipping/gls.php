<?php
/* -----------------------------------------------------------------------------------------
   $Id: gls.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.4 2003/02/18 04:28:00); www.oscommerce.com
   (c) 2003 nextcommerce (dp.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2009 shd-media (gls.php 899 27.05.2009);

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   GLS (German Logistic Service) based on DP (Deutsche Post)
   (c) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at
   GLS contribution made by shd-media (c) 2009 shd-media - www.shd-media.de

   updated version by franky_n

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class gls {
    var $code, $title, $description, $icon, $enabled, $num_gls;

    function __construct() {
      global $order;

      $this->code = 'gls';
      $this->title = MODULE_SHIPPING_GLS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_GLS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_GLS_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_gls.gif';
      $this->tax_class = MODULE_SHIPPING_GLS_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_GLS_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_GLS_ZONE > 0) && is_object($order)) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_GLS_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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

/**
 * CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
 */
      $this->num_gls = 6;
    }

/**
 * class methods
 */
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes;
      require_once(DIR_FS_INC .'xtc_format_price.inc.php');

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_plz = $order->delivery['postcode'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_gls; $i++) {
        $countries_table = constant('MODULE_SHIPPING_GLS_COUNTRIES_' . $i);
        $country_zones = preg_split("/[,]/", $countries_table); // DokuMan - 2010-11-20 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
        // rest of the world
        if ($countries_table == 'WORLD') {
          $dest_zone = $i;
        }
        // rest of the world eof
      }

      $plz_table = constant('MODULE_SHIPPING_GLS_POSTCODE');
      $plz_zones = preg_split("/[,]/",$plz_table); // DokuMan - 2010-11-20 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
      if (in_array($dest_plz, $plz_zones)) {
        $dest_plz_in = $dest_plz;
      } else {
        $dest_plz_in = ''; //DokuMan - 2011-09-06 - set Undefined variable
      }

      if ($dest_zone == 0) {
        $error = true;
      } else {
        $shipping = -1;
        $gls_cost = constant('MODULE_SHIPPING_GLS_COST_' . $dest_zone);
        $gls_table = preg_split("/[:,]/" , $gls_cost); // DokuMan - 2010-11-20 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
        for ($i=0; $i<sizeof($gls_table); $i+=2) {
          if ($shipping_weight <= $gls_table[$i]) {
            $shipping = $gls_table[$i+1];
            if ($dest_plz_in) {
              $shipping_method = MODULE_SHIPPING_GLS_TEXT_WAY . ' ' . $dest_country . ': ';
            } else {
              $shipping_method = MODULE_SHIPPING_GLS_TEXT_WAY . ' ' . $dest_country . ': ';
            }
            break;
          }
        }

        if ($shipping == -1) {
          $shipping_cost = 0;
          $shipping_method = MODULE_SHIPPING_GLS_UNDEFINED_RATE;
        } else {
          if ($dest_plz_in) {
            $shipping_cost_normal = ($shipping + MODULE_SHIPPING_GLS_HANDLING);
            $shipping_cost_extra = MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST;
            $shipping_cost = ($shipping + MODULE_SHIPPING_GLS_HANDLING + $shipping_cost_extra);
          } else {
            $shipping_cost_normal = ($shipping + MODULE_SHIPPING_GLS_HANDLING);
            $shipping_cost = ($shipping + MODULE_SHIPPING_GLS_HANDLING);
          }
        }
      }

      $tax_text = "";
      if ($this->tax_class > 0) { // Tax or not
         $tax_percent = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id'])/100;
         $shipping_cost_normal = $shipping_cost_normal + (round(($shipping_cost_normal * $tax_percent),2));
         $shipping_cost_normal_formatted = xtc_format_price($shipping_cost_normal,1,$_SESSION['currency'],1);
         $shipping_cost_extra = $shipping_cost_extra + (round(($shipping_cost_extra * $tax_percent),2));
         $shipping_cost_extra_formatted = xtc_format_price($shipping_cost_extra,1,$_SESSION['currency'],1);
         $tax_text = str_replace("%s", xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']) . '%', TAX_INFO_INCL);
      }

      if ($dest_plz_in) {
        $this->quotes = array('id' => $this->code,
                              'module' => MODULE_SHIPPING_GLS_TEXT_TITLE,
                              'methods' => array(array('id' => $this->code,
                                                       'title' => $shipping_method.' ('.$shipping_num_boxes.' x '.$shipping_weight.' '.MODULE_SHIPPING_GLS_TEXT_UNITS.' = ' . $shipping_cost_normal_formatted . ' '.$tax_text.')'.' '.MODULE_SHIPPING_GLS_POSTCODE_INFO_TEXT.': ('.$shipping_cost_extra_formatted.' '.$tax_text.')',
                                                       'cost' => $shipping_cost * $shipping_num_boxes)));
      } else {
        $this->quotes = array('id' => $this->code,
                              'module' => MODULE_SHIPPING_GLS_TEXT_TITLE,
                              'methods' => array(array('id' => $this->code,
                                                       'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_GLS_TEXT_UNITS .')',
                                                       'cost' => $shipping_cost * $shipping_num_boxes)));
      }


      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_GLS_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_GLS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

        function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_GLS_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_HANDLING', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_GLS_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_GLS_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_ALLOWED', '', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_1', 'DE', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_1', '2:4.10,5:5.90,10:6.90,15:9.90,20:15.30', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_2', 'BE,NL,LU,DK,AT', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_2', '2:12.60,5:13.70,10:15.90,15:18.00,20:20.80', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_3', 'FR,IT,GB', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_3', '2:15.40,5:17.00,10:19.70,15:22.40,20:25.00', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_4', 'IE,FI,PT,ES,SE,RO', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_4', '2:18.00,5:20.80,10:25.20,15:28.90,20:33.20', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_5', 'PL,CZ,HU,SK,SI', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_5', '2:19.10,5:22.00,10:26.00,15:30.10,20:34.20', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COUNTRIES_6', 'EE,LV,LT,BG', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_COST_6', '2:29.70,5:34.90,10:43.10,15:46.20,20:56.40', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_POSTCODE', '18565,25980,26486,25849,25992,26548,25859,25996,26571,25863,25997,26579,25869,25999,26757,25938,26465,27498,25946,26474,', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST', '13.95', '6', '0', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_GLS_STATUS', 'MODULE_SHIPPING_GLS_HANDLING','MODULE_SHIPPING_GLS_ALLOWED', 'MODULE_SHIPPING_GLS_TAX_CLASS', 'MODULE_SHIPPING_GLS_ZONE', 'MODULE_SHIPPING_GLS_SORT_ORDER', 'MODULE_SHIPPING_GLS_POSTCODE', 'MODULE_SHIPPING_GLS_POSTCODE_EXTRA_COST');

      for ($i = 1; $i <= $this->num_gls; $i ++) {
        $keys[count($keys)] = 'MODULE_SHIPPING_GLS_COUNTRIES_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_GLS_COST_' . $i;
      }

      return $keys;
    }
  }
?>