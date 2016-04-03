<?php
/* -----------------------------------------------------------------------------------------
   $Id: ap.php 5118 2013-07-18 10:58:36Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ap.php,v 1.05 2003/02/18); www.oscommerce.com 
   (c) 2003	 nextcommerce (ap.php,v 1.11 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions:
   austrian_post_1.05       	Autor:	Copyright (C) 2002 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers | http://www.themedia.at & http://www.oscommerce.at

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  class ap {
    var $code, $title, $description, $icon, $enabled, $num_ap;

    function __construct() {
      global $order;

      $this->code = 'ap';
      $this->title = MODULE_SHIPPING_AP_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_AP_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_AP_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_ap.gif';
      $this->tax_class = MODULE_SHIPPING_AP_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_AP_STATUS == 'True') ? true : false);
      $this->num_zones = defined('MODULE_SHIPPING_AP_NUMBER_ZONES') ? MODULE_SHIPPING_AP_NUMBER_ZONES : '';

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_AP_ZONE > 0) && is_object($order) ) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_AP_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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

      if ($this->check() > 0) {      
        $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_AP_COUNTRIES_%'");
        $check_zones_rows = xtc_db_num_rows($check_zones_query);

        //update compatibility
        if (!defined('MODULE_SHIPPING_AP_NUMBER_ZONES')) {
          $this->num_zones = $check_zones_rows;
          xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_AP_NUMBER_ZONES', '". (int)$this->num_zones ."', '6', '0', now())");
        }

        if ($check_zones_rows != $this->num_zones) {
          $this->install_zones($check_zones_rows);
        }
        //update compatibility
        if (!defined('MODULE_SHIPPING_AP_DISPLAY')) {
          xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_AP_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        }
      }
    }
    
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes;

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_zones; $i++) {
        $countries_table = constant('MODULE_SHIPPING_AP_COUNTRIES_' . $i);
        $countries_table = preg_replace("'[\r\n\s]+'",'',$countries_table);
        $country_zones = explode(",", $countries_table);
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

      $this->quotes = array('id' => $this->code,
                            'module' => $this->title);

      if ($dest_zone == 0) {
        if (MODULE_SHIPPING_AP_DISPLAY == 'True') {
          $this->quotes['error'] = MODULE_SHIPPING_AP_INVALID_ZONE;
        } else {
          $this->enabled = false;
        }
      } else {
        $shipping = -1;
        $ap_cost = constant('MODULE_SHIPPING_AP_COST_' . $dest_zone);

        $ap_table = preg_split("/[:,]/" , $ap_cost);
        for ($i=0; $i<sizeof($ap_table); $i+=2) {
          if ($shipping_weight <= $ap_table[$i]) {
            $shipping = $ap_table[$i+1];
            $shipping_method = MODULE_SHIPPING_AP_TEXT_WAY . ' ' . $dest_country . ' : ' . $shipping_weight . ' ' . MODULE_SHIPPING_AP_TEXT_UNITS;
            break;
          }
        }

        if ($shipping == -1) {
          if (MODULE_SHIPPING_AP_DISPLAY == 'True') {
            $this->quotes['error'] = MODULE_SHIPPING_AP_UNDEFINED_RATE;
          } else {
            $this->enabled = false;
          }
        } else {
          $shipping_cost = (($shipping * $shipping_num_boxes) + constant('MODULE_SHIPPING_AP_HANDLING_' . $dest_zone));
          $this->quotes['methods'] = array(array('id' => $this->code,
                                                 'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_AP_TEXT_UNITS .')',
                                                 'cost'  => $shipping_cost));
        }
      }
      
      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if ($this->enabled)
        return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_AP_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_AP_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_AP_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_AP_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_AP_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_AP_ALLOWED', '', '6', '0', 'xtc_cfg_textarea(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_AP_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_AP_NUMBER_ZONES', '8', '6', '0', now())");

      $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_".strtoupper($this->code)."_COUNTRIES_%'");
      $check_zones_rows_query = xtc_db_num_rows($check_zones_query);

      if ($check_zones_rows_query != 0) {
        $this->install_zones($check_zones_rows_query);
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION." 
                         SET configuration_value = '".(int)$check_zones_rows_query."' 
                       WHERE configuration_key = 'MODULE_SHIPPING_".strtoupper($this->code)."_NUMBER_ZONES'");
      }
  	}

    function install_zones($number_of_zones) {

      // backup old values
      xtc_backup_configuration($this->keys_zones($number_of_zones));

      // add new zone
      if ($number_of_zones <= $this->num_zones) {
        for ($i = (($number_of_zones==0) ? 1 : $number_of_zones); $i <= $this->num_zones; $i ++) {
          $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_".$i."'");
          if (xtc_db_num_rows($check_zones_query) < 1) {
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_AP_COUNTRIES_".$i."', '', '6', '0', 'xtc_cfg_textarea(', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_AP_COST_".$i."', '', '6', '0', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_AP_HANDLING_".$i."', '0', '6', '0', now())");
          }
        }      
      } else {
        // remove zone
        for ($i = $number_of_zones; $i >= $this->num_zones; $i --) {
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_".$i."'");
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_AP_COST_".$i."'");      
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_AP_HANDLING_".$i."'");      
        }
      }

      // set standard values
       for ($i = 1; $i <= $this->num_zones; $i ++) {
        if ($i == 1) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'DE,IT,SM' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_1'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:12.35,2:13.80,3:15.25,4:16.70,5:18.15,6:19.60,7:21.05,8:22.50,9:23.95,10:25.40,11:26.85,12:28.30,13:29.75,14:31.20,15:32.65,16:34.10,17:35.55,18:37.00,19:38.45,20:39.90' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_1'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_1'");
        }
        if ($i == 2) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AD,BE,DK,FO,GL,FI,FR,GR,GB,IE,LI,LU,MC,NL,PT,SE,CH,SK,SI,ES,CZ,HU,VA' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_2'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:13.08,2:15.26,3:17.44,4:19.62,5:21.80,6:23.98,7:26.16,8:28.34,9:30.52,10:32.70,11:34.88,12:37.06,13:39.24,14:41.42,15:43.60,16:45.78,17:47.96,18:50.14,19:52.32,20:54.50' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_2'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_2'");
        }
        if ($i == 3) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'EG,AL,DZ,AM,AZ,BA,BG,EE,GE,GI,IS,IL,YU,HR,LV,LB,LY,LT,MT,MA,MK,MD,NO,PL,RO,RU,SY,TN,TR,UA,CY' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_3'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:14.53,2:18.16,3:21.79,4:25.42,5:29.05,6:32.68,7:36.31,8:39.94,9:43.57,10:47.20,11:50.83,12:54.46,13:58.09,14:61.72,15:65.35,16:68.98,17:72.61,18:76.24,19:79.87,20:83.50' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_3'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_3'");
        }
        if ($i == 4) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'ET,BH,BJ,BF,CI,DJ,ER,GM,GH,GU,GN,GW,IQ,IR,YE,JO,CM,CA,CV,KZ,QA,KG,KW,LR,ML,MH,MR,FM,NE,NG,MP,OM,PR,SA,SN,SL,SO,SD,TJ,TG,TD,TM,UZ,AE,US,UM,CF' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_4'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:17.44,2:23.98,3:30.52,4:37.06,5:43.60,6:50.14,7:56.68,8:63.22,9:69.76,10:76.30,11:82.84,12:89.38,13:95.92,14:102.46,15:109.00,16:115.54,17:122.08,18:128.62,19:135.16,20:141.70' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_4'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_4'");
        }
        if ($i == 5) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AF,AO,AI,AG,GQ,AR,BS,BD,BB,BZ,BM,BT,BO,BW,BR,BN,BI,KY,CL,CN,CR,DM,DO,EC,SV,FK,GF,GA,GD,GP,GT,GY,HT,HN,HK,IN,ID,TP,JM,JP,KH,KE,CO,KM,CG,KP,KR,CU,LA,LS' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_5'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:19.62,2:28.34,3:37.06,4:45.78,5:54.50,6:63.22,7:71.94,8:80.66,9:89.38,10:98.10,11:106.82,12:115.54,13:124.26,14:132.98,15:141.70,16:150.42,17:159.14,18:167.86,19:176.58,20:185.30' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_5'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_5'");
        }
        if ($i == 6) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'MO,MG,MW,MY,MV,MQ,MU,MX,MN,MS,MZ,MM,NA,NP,NI,AN,AW,PK,PA,PY,PE,PH,RE,RW,ZM,ST,SC,ZW,SG,LK,KN,LC,PM,VC,ZA,SR,SZ,TZ,TH,TT,TC,UG,UY,VE,VN,VG' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_6'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:19.62,2:28.34,3:37.06,4:45.78,5:54.50,6:63.22,7:71.94,8:80.66,9:89.38,10:98.10,11:106.82,12:115.54,13:124.26,14:132.98,15:141.70,16:150.42,17:159.14,18:167.86,19:176.58,20:185.30' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_6'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_6'");
        }
        if ($i == 7) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AU,CK,FJ,PF,KI,NR,NC,NZ,PG,PN,SB,TO,TV,VU,WF,WS' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_7'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '1:23.98,2:37.06,3:50.14,4:63.22,5:76.30,6:89.38,7:102.46,8:115.54,9:128.62,10:141.70,11:154.78,12:167.86,13:180.94,14:194.02,15:207.10,16:220.18,17:233.26,18:246.34,19:259.42,20:272.50' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_7'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_7'");
        }
        if ($i == 8) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AT' WHERE configuration_key = 'MODULE_SHIPPING_AP_COUNTRIES_8'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '2:3.56,4:4.36,8:5.45,12:6.90,20:9.08,31.5:12.72' WHERE  configuration_key = 'MODULE_SHIPPING_AP_COST_8'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_AP_HANDLING_8'");
        }
      }
      
      // restore old values
      xtc_restore_configuration($this->keys_zones($this->num_zones));
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys_zones($zones) {
      $keys_zones = array();
      for ($i = 1; $i <= $zones; $i ++) {
        $keys_zones[] = 'MODULE_SHIPPING_AP_COUNTRIES_' . $i;
        $keys_zones[] = 'MODULE_SHIPPING_AP_COST_' . $i;
        $keys_zones[] = 'MODULE_SHIPPING_AP_HANDLING_' . $i;
      }
      return $keys_zones;
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_AP_STATUS', 
                    'MODULE_SHIPPING_AP_ALLOWED', 
                    'MODULE_SHIPPING_AP_TAX_CLASS', 
                    'MODULE_SHIPPING_AP_ZONE', 
                    'MODULE_SHIPPING_AP_SORT_ORDER',
                    'MODULE_SHIPPING_AP_NUMBER_ZONES',
                    'MODULE_SHIPPING_AP_DISPLAY'                    
                    );

      $keys = array_merge($keys, $this->keys_zones($this->num_zones));

      return $keys;
    }
  }
?>