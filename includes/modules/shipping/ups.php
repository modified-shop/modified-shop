<?php
/* -----------------------------------------------------------------------------------------
   $Id: ups.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(dp.php,v 1.36 2003/03/09 02:14:35); www.oscommerce.com 
   (c) 2003	 nextcommerce (dp.php,v 1.12 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   German Post (Deutsche Post WorldNet)
   Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at
   Changes for personal use: Copyright (C) 2004 Comm4All, Bernd Blazynski | http://www.comm4all.com & http://www.cheapshirt.de

   Released under the GNU General Public License 
   UPS - Modul  Price Table for Germany 3.1.2005
   ---------------------------------------------------------------------------------------*/
   
   

 
  class ups {
    var $code, $title, $description, $icon, $enabled, $num_ups;


    function __construct() {
      global $order;

      $this->code = 'ups';
      $this->title = MODULE_SHIPPING_UPS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_UPS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_UPS_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_ups.gif';
      $this->tax_class = MODULE_SHIPPING_UPS_TAX_CLASS;
      $this->free = MODULE_SHIPPING_UPS_TEXT_FREE;
      $this->enabled = ((MODULE_SHIPPING_UPS_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_UPS_ZONE > 0) && is_object($order)) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_UPS_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      $this->num_ups = 7;
    }

/**
 * class methods
 */
function quote($method = '') {
	global $order, $shipping_weight, $shipping_num_boxes;

	$dest_country = $order->delivery['country']['iso_code_2'];
	$dest_zone = 0;
	$error = false;
	$freeship = false;
	$lowship = false;

	for ($i=1; $i<=$this->num_ups; $i++) {
		$countries_table = constant('MODULE_SHIPPING_UPS_COUNTRIES_' . $i);
		$country_zones = explode(",", $countries_table); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
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

	if ($dest_zone == 0) {
		$error = true;
		} elseif (($dest_zone == 1) && ((round($_SESSION['cart']->show_total())) >= MODULE_SHIPPING_UPS_FREEAMOUNT)) {
			$freeship = true;
			$shipping = 0;
			$shipping_method = MODULE_SHIPPING_UPS_TEXT_WAY . ' ' . $dest_country . ': ';
		} elseif (($dest_zone > 1) && ((round($_SESSION['cart']->show_total())) >= MODULE_SHIPPING_UPS_FREEAMOUNT)) {
			$lowship = true;
			$shipping = -1;
			$ups_cost = constant('MODULE_SHIPPING_UPS_COST_' . $dest_zone);
			$ups_table = preg_split("/[:,]/" , $ups_cost); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
			for ($i=0; $i<sizeof($ups_table); $i+=2) {
				if ($shipping_weight <= $ups_table[$i]) {
					$shipping = $ups_table[$i+1];
					$shipping_method = MODULE_SHIPPING_UPS_TEXT_WAY . ' ' . $dest_country . ': ';
					break;
				}
			}
			$i = 1;
			$ups_cost = constant('MODULE_SHIPPING_UPS_COST_' . $dest_zone);
			$ups_table = preg_split("/[:,]/" , $ups_cost); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
			for ($i=0; $i<sizeof($ups_table); $i+=2) {
				if ($shipping_weight <= $ups_table[$i]) {
					$diff = $ups_table[$i+1];
					break;
				}
			}
			$shipping = $shipping - $diff;
		} else {
			$shipping = -1;
			$ups_cost = constant('MODULE_SHIPPING_UPS_COST_' . $dest_zone);
			$ups_table = preg_split("/[:,]/" , $ups_cost); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
			for ($i=0; $i<sizeof($ups_table); $i+=2) {
				if ($shipping_weight <= $ups_table[$i]) {
					$shipping = $ups_table[$i+1];
					$shipping_method = MODULE_SHIPPING_UPS_TEXT_WAY . ' ' . $dest_country . ': ';
					break;
				}
			}
	}

	if ($shipping == -1) {
		$shipping_cost = 0;
		$shipping_method = MODULE_SHIPPING_UPS_UNDEFINED_RATE;
		} else {
		$shipping_cost = ($shipping + MODULE_SHIPPING_UPS_HANDLING);
	}


	if ($freeship == true) {
		$this->quotes = array('id' => $this->code,
			'module' => MODULE_SHIPPING_UPS_TEXT_TITLE,
			'methods' => array(array('id' => $this->code,
			'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_UPS_TEXT_UNITS .')<br />' . MODULE_SHIPPING_UPS_TEXT_FREE,
			'cost' => $shipping_cost * $shipping_num_boxes,)));
	} elseif ($lowship == true) {
		$this->quotes = array('id' => $this->code,
			'module' => MODULE_SHIPPING_UPS_TEXT_TITLE,
			'methods' => array(array('id' => $this->code,
			'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_UPS_TEXT_UNITS .')<br />' . MODULE_SHIPPING_UPS_TEXT_LOW,
			'cost' => $shipping_cost * $shipping_num_boxes,)));
	} else {
		$this->quotes = array('id' => $this->code,
			'module' => MODULE_SHIPPING_UPS_TEXT_TITLE,
			'methods' => array(array('id' => $this->code,
			'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_UPS_TEXT_UNITS .')',
			'cost' => $shipping_cost * $shipping_num_boxes,)));
	}

	if ($this->tax_class > 0) {
		$this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	}

	if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

	if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_UPS_INVALID_ZONE;
//	if ($lowship == true) $this->quotes['error'] = $ups_cost;

	return $this->quotes;
}

function check() {
	if (!isset($this->_check)) {
		$check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_UPS_STATUS'");
		$this->_check = xtc_db_num_rows($check_query);
	}
	return $this->_check;
}

function install() {
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_UPS_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_HANDLING', '0', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_UPS_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_UPS_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_SORT_ORDER', '0', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_ALLOWED', '', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_FREEAMOUNT', '0', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_1', 'DE', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_1', '4:5.35,7:6.45,10:7.50,14:10.10,20:12.20,22:14.40,24:15.40,26:16.50,28:17.60,30:18.70,32:22.40,34:24.00,36:26.60,38:27.20,40:28.80,42:29.85,44:30.90,46:31.95,48:33.00,50:34.05,55:35.10,60:36.15,65:37.20,70:38.25', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_2', 'BE,DK,LU,NL', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_2', '4:14.20,7:15.80,10:17.40,14:19.00,20:23.90,22:25.60,24:27.30,26:29.00,28:30.70,30:32.40,32:34.65,34:36.90,36:39.15,38:41.40,40:43.65,42:45.90,44:48.15,46:50.40,48:52.65,50:58.25,55:63.85,60:69.45,65:75.05,70:80.65', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_3', 'PL,SK,SI', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_3', '4:24.30,7:25.90,10:27.50,14:29.40,20:34.90,22:37.10,24:39.30,26:41.50,28:43.70,30:45.90,32:48.50,34:50.90,36:53.30,38:55.70,40:58.10,42:61.80,44:65.20,46:68.60,48:72.00,50:76.50,55:83.10,60:89.70,65:96.30,70:102.90', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_4', 'AT,FI,FR,MC,SE,GB', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_4', '4:26.30,7:27.80,10:29.30,14:31.30,20:37.30,22:40.00,24:42.70,26:45.40,28:48.10,30:50.80,32:54.40,34:57.10,36:59.80,38:62.50,40:65.20,42:70.15,44:74.95,46:79.75,48:84.55,50:89.35,55:97.95,60:106.55,65:115.15,70:123.75', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_5', 'EE,LV,LT,HU', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_5', '4:31.00,7:32.60,10:34.20,14:37.30,20:44.30,22:47.30,24:50.30,26:53.30,28:56.30,30:59.30,32:62.80,34:66.30,36:69.80,38:73.30,40:76.80,42:81.40,44:86.00,46:90.60,48:95.20,50:99.80,55:108.80,60:117.80,65:126.80,70:135.80', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_6', 'GR,IE,IT,PT,ES', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_6', '4:35.60,7:37.20,10:38.80,14:42.80,20:51.80,22:54.80,24:57.80,26:60.80,28:63.80,30:66.80,32:70.80,34:74.80,36:78.80,38:82.80,40:86.80,42:91.10,44:95.40,46:99.70,48:104.00,50:108.30,55:116.80,60:125.30,65:133.80,70:142.30', '6', '0', now())");

xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COUNTRIES_7', 'AD,LI,NO,SM,CH', '6', '0', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_UPS_COST_7', '4:38.20,7:39.80,10:41.40,14:45.85,20:58.20,22:62.85,24:67.50,26:72.15,28:76.80,30:81.75,32:86.40,34:91.35,36:96.30,38:101.35,40:106.20,42:109.30,44:112.40,46:115.50,48:118.60,50:121.70,55:127.90,60:134.10,65:140.30,70:146.50', '6', '0', now())");
}

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_UPS_STATUS', 'MODULE_SHIPPING_UPS_HANDLING','MODULE_SHIPPING_UPS_ALLOWED', 'MODULE_SHIPPING_UPS_FREEAMOUNT', 'MODULE_SHIPPING_UPS_TAX_CLASS', 'MODULE_SHIPPING_UPS_ZONE', 'MODULE_SHIPPING_UPS_SORT_ORDER');

      for ($i = 1; $i <= $this->num_ups; $i ++) {
        $keys[count($keys)] = 'MODULE_SHIPPING_UPS_COUNTRIES_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_UPS_COST_' . $i;
      }

      return $keys;
    }
  }
?>
