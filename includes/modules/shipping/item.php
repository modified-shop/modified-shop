<?php
/* -----------------------------------------------------------------------------------------
   $Id: item.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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


    function item() {
      global $order;

      $this->code = 'item';
      $this->title = MODULE_SHIPPING_ITEM_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_ITEM_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_ITEM_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_ITEM_STATUS == 'True') ? true : false);
      $this->num_item = defined('MODULE_SHIPPING_ITEM_NUMBER_ZONES')?MODULE_SHIPPING_ITEM_NUMBER_ZONES:'';

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_ITEM_ZONE > 0) ) {
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

      $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_ITEM_COUNTRIES_%'");
      $check_zones_rows_query = xtc_db_num_rows($check_zones_query);

      if ($check_zones_rows_query != $this->num_item) {
        $this->install_zones($this->num_item);
      }

    }

    function quote($method = '') {
      global $order, $total_count;

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_item; $i++) {
        $countries_table = constant('MODULE_SHIPPING_ITEM_COUNTRIES_' . $i);
        $countries_table  = preg_replace("'[\r\n\s]+'",'',$countries_table); //web28 -2011-06-13 - support for textareas
        $country_zones = explode(",", $countries_table); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
               
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }

      if ($dest_zone == 0) {
        $error = true;
      } else {
        $shipping = -1;
        $item_cost = constant('MODULE_SHIPPING_ITEM_COST_' . $i);

        $item_table = preg_split("/[:,]/" , $item_cost); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
        for ($i=0; $i<sizeof($item_table); $i+=2) {
          if ($shipping_weight <= $item_table[$i]) {
            $shipping = $item_table[$i+1];
            $shipping_method = MODULE_SHIPPING_ITEM_TEXT_WAY . ' ' . $dest_country . ': ';
            break;
          }
        }

      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_ITEM_TEXT_TITLE);

        if ($shipping == -1) {
          $this->quotes['error'] = MODULE_SHIPPING_ITEM_UNDEFINED_RATE;
        } else {
          $shipping_cost = (($shipping * $total_count) + MODULE_SHIPPING_ITEM_HANDLING);
          $this->quotes['methods'] = array(array('id' => $this->code,
                                                 'title' => $shipping_method,
                                                 'cost'  => $shipping_cost));
        }
      }

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_ITEM_INVALID_ZONE;

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
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_ALLOWED', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ITEM_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ITEM_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_NUMBER_ZONES', '1', '6', '0', now())");
    }

    function install_zones($number_of_zones = '1') {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ITEM_COUNTRIES_%'");
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ITEM_COST_%'");
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ITEM_HANDLING_%'");

      for ($i = 1; $i <= $number_of_zones; $i ++) {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ITEM_COUNTRIES_".$i."', 'DE', '6', '0', 'xtc_cfg_textarea(', now())"); //web28 -2011-06-13 - support for textareas
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_COST_".$i."', '5:6.70,10:9.70,20:13.00', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ITEM_HANDLING_".$i."', '0', '6', '0', now())");
      }

      if ($number_of_zones >=1) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'DE' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_1'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '4.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_1'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_1'");
      }
      if ($number_of_zones >=2) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AT,BE,BG,CY,CZ,DK,EE,ES,FI,FR,GB,GR,HU,IE,IT,LT,LU,LV,MC,MT,NL,PL,PT,RO,SE,SI,SK' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_2'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '13.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_2'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_2'");
      }
      if ($number_of_zones >=3) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AD,AL,AM,AZ,BA,BY,CH,FO,GE,GI,GL,HR,IS,KZ,LI,MD,ME,MK,NO,RS,RU,SM,TR,UA,VA' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_3'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '19.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_3'");
      }
      if ($number_of_zones >=4) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'CA,DZ,EG,IL,JO,LB,LR,LY,MA,PM,PS,SY,TN,US' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_4'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '29.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_4'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_4'");
      }
      if ($number_of_zones >=5) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AE,AF,AG,AI,AN,AO,AR,AU,AW,BB,BD,BF,BH,BI,BJ,BM,BN,BO,BR,BS,BT,BW,BZ,CD,CF,CG,CI,CK,CL,CM,CN,CO,CR,CU,CV,DJ,DM,DO,EC,ER,ET,FJ,FK,FM,GA,GD,GF,GH,GM,GN,GP,GQ,GT,GU,GW,GY,HK,HN,HT,ID,IN,IQ,IR,JM,JP,KE,KG,KH,KI,KM,KN,KP,KR,KW,KY,LA,LC,LK,LS,MG,MH,ML,MM,MN,MO,MP,MQ,MR,MS,MU,MV,MW,MX,MY,MZ,NA,NC,NE,NG,NI,NP,NR,NZ,OM,PA,PE,PF,PG,PH,PK,PN,PR,PY,QA,RE,RW,SA,SB,SC,SD,SG,SH,SL,SN,SO,SR,ST,SV,SZ,TC,TD,TG,TH,TJ,TM,TO,TT,TV,TW,TZ,UG,UY,UZ,VC,VE,VN,VU,WF,WS,YE,ZA,ZM,ZW' WHERE configuration_key = 'MODULE_SHIPPING_ITEM_COUNTRIES_5'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '39.90' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_COST_5'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ITEM_HANDLING_5'");
      }
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_ITEM_STATUS',
                    'MODULE_SHIPPING_ITEM_ALLOWED',
                    'MODULE_SHIPPING_ITEM_TAX_CLASS',
                    'MODULE_SHIPPING_ITEM_ZONE',
                    'MODULE_SHIPPING_ITEM_SORT_ORDER',
                    'MODULE_SHIPPING_ITEM_NUMBER_ZONES'
                    );

      for ($i=1; $i<=$this->num_item; $i++) {
        $keys[] = 'MODULE_SHIPPING_ITEM_COUNTRIES_' . $i;
        $keys[] = 'MODULE_SHIPPING_ITEM_COST_' . $i;
        $keys[] = 'MODULE_SHIPPING_ITEM_HANDLING_' . $i;
      }

      return $keys;
    }
  }
?>
