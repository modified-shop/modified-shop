<?php
/* -----------------------------------------------------------------------------------------
   $Id: chronopost.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(chronopost.php,v 1.0 2002/04/01); www.oscommerce.com 
   (c) 2003	 nextcommerce (chronopost.php,v 1.9 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contributions: 
   chronopost-1.0.1       	Autor:	devteam@e-network.fr | www.oscommerce-fr.info

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
   


  class chronopost {
    var $code, $title, $description, $enabled, $num_chronopost;

    /**
    * class constructor
    */
    function __construct() {
      $this->code        = 'chronopost';
      $this->title       = MODULE_SHIPPING_CHRONOPOST_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_CHRONOPOST_TEXT_DESCRIPTION;
      $this->sort_order  = MODULE_SHIPPING_CHRONOPOST_SORT_ORDER;
      $this->icon        = DIR_WS_ICONS . 'shipping_chronopost.gif';
      $this->tax_class   = MODULE_SHIPPING_CHRONOPOST_TAX_CLASS;
      $this->enabled     = ((MODULE_SHIPPING_CHRONOPOST_STATUS == 'True') ? true : false);

      /**
      * CUSTOMIZE THIS SETTING FOR THE NUMBER OF chronopost NEEDED
      */
      $this->num_chronopost = 10; //NBen je remplace 1 par 10

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_CHRONOPOST_ZONE > 0) && is_object($order)) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_CHRONOPOST_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
    }

    function quote() {
      global $order, $shipping_weight;

      $this->quotes = array('id'      => $this->code,
                            'module'  => MODULE_SHIPPING_CHRONOPOST_TEXT_TITLE,
                            'methods' => array());

      if (xtc_not_null($this->icon)) {
        $this->quotes['icon'] = xtc_image($this->icon, $this->title);
      }

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      for ($i = 1; $i <= $this->num_chronopost; $i ++) {
        $countries_table = constant('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_' . $i);
        $country = explode(",", $countries_table); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
        if (in_array($dest_country, $country)) {
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
        $this->quotes['error'] = MODULE_SHIPPING_CHRONOPOST_INVALID_ZONE;
        return $this->quotes;
      }

      $table = preg_split("/[:,]/" , constant('MODULE_SHIPPING_CHRONOPOST_COST_' . $dest_zone)); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
      $cost = -1;
      for ($i = 0, $n = sizeof($table); $i < $n; $i+=2) {
        if ($shipping_weight <= $table[$i]) {
          $cost = $table[$i+1] + MODULE_SHIPPING_CHRONOPOST_HANDLING;
          break;
        }
      }

      if ($cost == -1) {
        $this->quotes['error'] = MODULE_SHIPPING_CHRONOPOST_UNDEFINED_RATE;
        return $this->quotes;
      }

      $this->quotes['methods'][] = array('id'    => $this->code,
                                         'title' => MODULE_SHIPPING_CHRONOPOST_TEXT_WAY . ' ' . $order->delivery['country']['title'],
                                         'cost'  => $cost + MODULE_SHIPPING_CHRONOPOST_HANDLING);

      return $this->quotes;
    }

    function check() {
      if (!isset($this->check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_CHRONOPOST_STATUS'");
        $this->check = xtc_db_num_rows($check_query);
      }
      return $this->check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) VALUES ('MODULE_SHIPPING_CHRONOPOST_STATUS', 'True', '6', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_HANDLING', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_ALLOWED', '', '6', '0', now())");

      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_1', 'BE,LU', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_1', '0-500:38.85,500-1000:50.17,1000-1500:61.50,1500-2000:72.82,2000-2500:84.15,2500-3000:95.48,3000-3500:98.71,3500-4000:101.94,4000-4500:105.16,4500-5000:108.39', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_2', 'DE,ES,IT,NL,GB', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_2', '0-500:39.96,500-1000:51.64,1000-1500:63.33,1500-2000:75.01,2000-2500:86.70,2500-3000:98.38,3000-3500:101.71,3500-4000:105.04,4000-4500:108.38,4500-5000:111.71', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_3', 'AC,AT,DK,FI,GR,IE,ME,PT,SE', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_3', '0-500:43.46,500-1000:56.61,1000-1500:69.76,1500-2000:82.91,2000-2500:96.06,2500-3000:109.21,3000-3500:113.23,3500-4000:117.26,4000-4500:121.28,4500-5000:125.31', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_4', 'AD,IC,FO,GI,GS,IS,JE,LI,MT,NO,PL,SM,CH,TR,VA', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_4', '0-500:44.76,500-1000:56.47,1000-1500:68.19,1500-2000:79.91,2000-2500:91.62,2500-3000:103.34,3000-3500:106.93,3500-4000:110.52,4000-4500:114.11,4500-5000:117.70', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_5', 'CA,US,MX', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_5', '0-500:52.31,500-1000:64.13,1000-1500:75.95,1500-2000:87.77,2000-2500:99.59,2500-3000:111.42,3000-3500:115.43,3500-4000:119.44,4000-4500:123.45,4500-5000:127.46', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_6', 'MQ,YO,RE,GP,PM', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_6', '0-500:55.26,500-1000:67.29,1000-1500:79.33,1500-2000:91.36,2000-2500:103.40,2500-3000:115.43,3000-3500:119.49,3500-4000:123.54,4000-4500:127.60,4500-5000:131.65', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_7', 'ZA,AL,SA,BH,BY,BX,BG,CM,CY,KR,CI,HR,AE,EE,GA,HK,HU,ID,JP,KW,LV,LB,LT,MK,MY,MA,MU,MD,MB,OM,QA,RO,RU,SN,SX,SG,SK,SI,CZ,TH,TN,UA,YU', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_7', '0-500:65.96,500-1000:81.00,1000-1500:96.05,1500-2000:111.10,2000-2500:126.14,2500-3000:141.19,3000-3500:150.41,3500-4000:159.62,4000-4500:168.84,4500-5000:178.05', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_8', 'DZ,AI,AG,AR,AM,AW,AU,AZ,BS,BB,BJ,BM,AN,BR,BF,BI,KH,CV,KY,CF,CN,KM,CG,CD,CU,AN,DJ,DO,DM,EG,GM,GE,GH,GD,GL,GN,GW,GQ,HT,IN,IL,JM,JO,MO,MG,ML,MR,MS,NE,NG,NC,NZ,PK,PH,PF,PR,AN,KN,VI,VC,LC,LK,SY,TD,TG,TT,TC,VE,VG,VN,YE', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_8', '0-500:75.10,500-1000:93.33,1000-1500:111.55,1500-2000:129.78,2000-2500:148.01,2500-3000:166.23,3000-3500:181.60,3500-4000:196.98,4000-4500:212.35,4500-5000:227.73', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_9', 'AO,BD,BZ,BT,BO,BW,BN,CL,CO,CK,KP,CR,SV,EC,ER,ET,FJ,GU,GT,HN,IR,KZ,KE,KG,LA,LS,LR,LY,MW,MV,MH,FM,MN,MZ,MM,NA,NP,NI,UG,UZ,PW,PS,PA,PG,PY,PE,RW,PC,AS,ST,SC,SL,SD,SR,SZ,TZ,TM,TV,UY,VU,WF,ZM,ZW', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_9', '0-500:83.66,500-1000:103.20,1000-1500:122.74,1500-2000:142.29,2000-2500:161.83,2500-3000:181.38,3000-3500:197.89,3500-4000:214.40,4000-4500:230.91,4500-5000:247.42', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COUNTRIES_10', 'FR,FX', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_COST_10', '0-2000:28.71,2000-5000:34.38,5000-10000:43.83', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_CHRONOPOST_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_CHRONOPOST_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_CHRONOPOST_SORT_ORDER', '0', '6', '0', now())");
    }


    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_CHRONOPOST_STATUS',
                    'MODULE_SHIPPING_CHRONOPOST_HANDLING',
                    'MODULE_SHIPPING_CHRONOPOST_ALLOWED',
                    'MODULE_SHIPPING_CHRONOPOST_TAX_CLASS',
                    'MODULE_SHIPPING_CHRONOPOST_ZONE',
                    'MODULE_SHIPPING_CHRONOPOST_SORT_ORDER'
                   );
      for ($i = 1; $i <= $this->num_chronopost; $i ++) {
        $keys[count($keys)] = 'MODULE_SHIPPING_CHRONOPOST_COUNTRIES_' . $i;
        $keys[count($keys)] = 'MODULE_SHIPPING_CHRONOPOST_COST_' . $i;
      }
      return $keys;
    }
  }
?>
