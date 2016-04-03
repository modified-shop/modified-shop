<?php
/* -----------------------------------------------------------------------------------------
   $Id: item.php 5118 2013-07-18 10:58:36Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(item.php,v 1.39 2003/02/05); www.oscommerce.com
   (c) 2003 nextcommerce (item.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2006 XT-Commerce (item.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class item {
    var $code, $title, $description, $icon, $enabled, $num_item;


    function __construct() {
      global $order;

      $this->code = 'item';
      $this->title = MODULE_SHIPPING_ITEM_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_ITEM_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_ITEM_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_ITEM_STATUS == 'True') ? true : false);
      $this->num_item = defined('MODULE_SHIPPING_ITEM_NUMBER_ZONES') ? MODULE_SHIPPING_ITEM_NUMBER_ZONES : '1';

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_ITEM_ZONE > 0) && is_object($order) ) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ITEM_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
        //update compatibility
        if (!defined('MODULE_SHIPPING_ITEM_NUMBER_ZONES')) {
          xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_NUMBER_ZONES', '1', '6', '0', now())");
          if (defined('MODULE_SHIPPING_ITEM_COST')) {
            if (!defined('MODULE_SHIPPING_ITEM_COUNTRIES_1')) {
              xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_COUNTRIES_1', ". MODULE_SHIPPING_ITEM_ALLOWED .", '6', '0', 'xtc_cfg_textarea(', now())");
            }
            if (!defined('MODULE_SHIPPING_ITEM_COST_1')) {
              xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_COST_1', ". MODULE_SHIPPING_ITEM_COST .", '6', '0', now())");
            }
            if (!defined('MODULE_SHIPPING_ITEM_HANDLING_1')) {
              xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_HANDLING_1', ". MODULE_SHIPPING_ITEM_HANDLING .", '6', '0', now())");
            }
          }
        }
        if (!defined('MODULE_SHIPPING_ITEM_ZONE')) {
          xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ITEM_ZONE', '0', '6', '2', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
        }
        if (!defined('MODULE_SHIPPING_ITEM_DISPLAY')) {
          xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
        }
        
        $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_ITEM_COUNTRIES_%'");
        $check_zones_rows_query = xtc_db_num_rows($check_zones_query);

        if ($check_zones_rows_query != $this->num_item) {
          $this->install_zones($check_zones_rows_query);
        }
      }
    }

    function quote($method = '') {
      global $order, $total_count;

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;

      for ($i=1; $i<=$this->num_item; $i++) {
        $countries_table = constant('MODULE_SHIPPING_ITEM_COUNTRIES_' . $i);
        $countries_table  = preg_replace("'[\r\n\s]+'",'',$countries_table);
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
        if (MODULE_SHIPPING_ITEM_DISPLAY == 'True') {
          $this->quotes['error'] = MODULE_SHIPPING_ITEM_INVALID_ZONE;
        } else {
          $this->enabled = false;
        }
      } else {
        $item_cost = constant('MODULE_SHIPPING_ITEM_COST_' . $dest_zone);
        $item_handling = constant('MODULE_SHIPPING_ITEM_HANDLING_' . $dest_zone);

        $this->quotes['methods'] = array(array('id' => $this->code,
                                               'title' => MODULE_SHIPPING_ITEM_TEXT_WAY . ' ' . $dest_country . ': ',
                                               'cost'  => ($item_cost * $total_count) + $item_handling));
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
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ITEM_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_ALLOWED', '', '6', '0', 'xtc_cfg_textarea(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ITEM_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ITEM_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_NUMBER_ZONES', '1', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");

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
      if ($number_of_zones <= $this->num_item) {
        for ($i = (($number_of_zones==0) ? 1 : $number_of_zones); $i <= $this->num_item; $i ++) {
          $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_".$i."'");
          if (xtc_db_num_rows($check_zones_query) < 1) {
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_COUNTRIES_".$i."', '', '6', '0', 'xtc_cfg_textarea(', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_COST_".$i."', '', '6', '0', now())");
            xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_HANDLING_".$i."', '0', '6', '0', now())");
          }
        }      
      } else {
        // remove zone
        for ($i = $number_of_zones; $i >= $this->num_item; $i --) {
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_".$i."'");
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ITEM_COST_".$i."'");      
          xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_".$i."'");      
        }
      }

      // set standard values
      for ($i = 1; $i <= $this->num_item; $i ++) {
        if ($i == 1) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'DE' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_1'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '4.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_1'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_1'");
        }
        if ($i == 2) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AT,BE,BG,CY,CZ,DK,EE,ES,FI,FR,GB,GR,HU,IE,IT,LT,LU,LV,MC,MT,NL,PL,PT,RO,SE,SI,SK' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_2'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '13.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_2'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_2'");
        }
        if ($i == 3) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AD,AL,AM,AZ,BA,BY,CH,FO,GE,GI,GL,HR,IS,KZ,LI,MD,ME,MK,NO,RS,RU,SM,TR,UA,VA' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_3'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '19.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_3'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_3'");
        }
        if ($i == 4) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'CA,DZ,EG,IL,JO,LB,LR,LY,MA,PM,PS,SY,TN,US' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_4'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '29.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_4'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_4'");
        }
        if ($i == 5) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AE,AF,AG,AI,AN,AO,AR,AU,AW,BB,BD,BF,BH,BI,BJ,BM,BN,BO,BR,BS,BT,BW,BZ,CD,CF,CG,CI,CK,CL,CM,CN,CO,CR,CU,CV,DJ,DM,DO,EC,ER,ET,FJ,FK,FM,GA,GD,GF,GH,GM,GN,GP,GQ,GT,GU,GW,GY,HK,HN,HT,ID,IN,IQ,IR,JM,JP,KE,KG,KH,KI,KM,KN,KP,KR,KW,KY,LA,LC,LK,LS,MG,MH,ML,MM,MN,MO,MP,MQ,MR,MS,MU,MV,MW,MX,MY,MZ,NA,NC,NE,NG,NI,NP,NR,NZ,OM,PA,PE,PF,PG,PH,PK,PN,PR,PY,QA,RE,RW,SA,SB,SC,SD,SG,SH,SL,SN,SO,SR,ST,SV,SZ,TC,TD,TG,TH,TJ,TM,TO,TT,TV,TW,TZ,UG,UY,UZ,VC,VE,VN,VU,WF,WS,YE,ZA,ZM,ZW' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_5'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '39.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_5'");
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_5'");
        }
      }
      
      // restore old values
      xtc_restore_configuration($this->keys_zones($this->num_item));
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys_zones($zones) {
      $keys_zones = array();
      for ($i = 1; $i <= $zones; $i ++) {
        $keys_zones[] = 'MODULE_SHIPPING_ITEM_COUNTRIES_' . $i;
        $keys_zones[] = 'MODULE_SHIPPING_ITEM_COST_' . $i;
        $keys_zones[] = 'MODULE_SHIPPING_ITEM_HANDLING_' . $i;
      }
      return $keys_zones;
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_ITEM_STATUS',
                    'MODULE_SHIPPING_ITEM_ALLOWED',
                    'MODULE_SHIPPING_ITEM_TAX_CLASS',
                    'MODULE_SHIPPING_ITEM_ZONE',
                    'MODULE_SHIPPING_ITEM_SORT_ORDER',
                    'MODULE_SHIPPING_ITEM_NUMBER_ZONES',
                    'MODULE_SHIPPING_ITEM_DISPLAY'
                    );
      $keys = array_merge($keys, $this->keys_zones($this->num_item));

      return $keys;
    }
  }
?>