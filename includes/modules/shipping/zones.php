<?php
/* -----------------------------------------------------------------------------------------
   $Id: zones.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(zones.php,v 1.19 2003/02/05); www.oscommerce.com
   (c) 2003	nextcommerce (zones.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2006	xtcommerce (zones.php 899 2005-04-29);

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
 * USAGE
 * By default, the module comes with support for 1 zone.  This can be
 * easily changed by editing the line below in the zones constructor
 * that defines $this->num_zones.
 *
 * Next, you will want to activate the module by going to the Admin screen,
 * clicking on Modules, then clicking on Shipping.  A list of all shipping
 * modules should appear.  Click on the green dot next to the one labeled
 * zones.php.  A list of settings will appear to the right.  Click on the
 * Edit button.
 *
 * PLEASE NOTE THAT YOU WILL LOSE YOUR CURRENT SHIPPING RATES AND OTHER
 * SETTINGS IF YOU TURN OFF THIS SHIPPING METHOD.  Make sure you keep a
 * backup of your shipping settings somewhere at all times.
 *
 * If you want an additional handling charge applied to orders that use this
 * method, set the Handling Fee field.
 *
 * Next, you will need to define which countries are in each zone.  Determining
 * this might take some time and effort.  You should group a set of countries
 * that has similar shipping charges for the same weight.  For instance, when
 * shipping from the US, the countries of Japan, Australia, New Zealand, and
 * Singapore have similar shipping rates.  As an example, one of my customers
 * is using this set of zones:
 *   1: USA
 *   2: Canada
 *   3: Austria, Belgium, Great Britain, France, Germany, Greenland, Iceland,
 *      Ireland, Italy, Norway, Holland/Netherlands, Denmark, Poland, Spain,
 *      Sweden, Switzerland, Finland, Portugal, Israel, Greece
 *   4: Japan, Australia, New Zealand, Singapore
 *   5: Taiwan, China, Hong Kong
 *
 * When you enter these country lists, enter them into the Zone X Countries
 * fields, where "X" is the number of the zone.  They should be entered as
 * two character ISO country codes in all capital letters.  They should be
 * separated by commas with no spaces or other punctuation. For example:
 *   1: US
 *   2: CA
 *   3: AT,BE,GB,FR,DE,GL,IS,IE,IT,NO,NL,DK,PL,ES,SE,CH,FI,PT,IL,GR
 *   4: JP,AU,NZ,SG
 *   5: TW,CN,HK
 *
 * Now you need to set up the shipping rate tables for each zone.  Again,
 * some time and effort will go into setting the appropriate rates.  You
 * will define a set of weight ranges and the shipping price for each
 * range.  For instance, you might want an order than weighs more than 0
 * and less than or equal to 3 to cost 5.50 to ship to a certain zone.
 * This would be defined by this:  3:5.5
 *
 * You should combine a bunch of these rates together in a comma delimited
 * list and enter them into the "Zone X Shipping Table" fields where "X"
 * is the zone number.  For example, this might be used for Zone 1:
 *   1:3.5,2:3.95,3:5.2,4:6.45,5:7.7,6:10.4,7:11.85, 8:13.3,9:14.75,10:16.2,11:17.65,
 *   12:19.1,13:20.55,14:22,15:23.45
 *
 * The above example includes weights over 0 and up to 15.  Note that
 * units are not specified in this explanation since they should be
 * specific to your locale.
 *
 * CAVEATS
 * At this time, it does not deal with weights that are above the highest amount
 * defined.  This will probably be the next area to be improved with the
 * module.  For now, you could have one last very high range with a very
 * high shipping rate to discourage orders of that magnitude.  For
 * instance:  999:1000
 *
 * If you want to be able to ship to any country in the world, you will
 * need to enter every country code into the Country fields. For most
 * shops, you will not want to enter every country.  This is often
 * because of too much fraud from certain places. If a country is not
 * listed, then the module will add a $0.00 shipping charge and will
 * indicate that shipping is not available to that destination.
 * PLEASE NOTE THAT THE ORDER CAN STILL BE COMPLETED AND PROCESSED!
 *
 * It appears that the osC shipping system automatically rounds the
 * shipping weight up to the nearest whole unit.  This makes it more
 * difficult to design precise shipping tables.  If you want to, you
 * can hack the shipping.php file to get rid of the rounding.
 *
 * Lastly, there is a limit of 255 characters on each of the Zone
 * Shipping Tables and Zone Countries.
 *
 *  Released under the GNU General Public License
 *
 */

  class zones {
    var $code, $title, $description, $enabled, $num_zones;

/**
 * class constructor
 */
    function zones() {
      $this->code = 'zones';
      $this->title = MODULE_SHIPPING_ZONES_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_ZONES_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_ZONES_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_ZONES_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_ZONES_STATUS == 'True') ? true : false);
      $this->num_zones = defined('MODULE_SHIPPING_ZONES_NUMBER_ZONES')?MODULE_SHIPPING_ZONES_NUMBER_ZONES:'';

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_ZONES_ZONE > 0) ) {
        $check_flag = false;
        $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ZONES_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
      
      $check_zones_query = xtc_db_query("SELECT * FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SHIPPING_ZONES_COUNTRIES_%'");
      $check_zones_rows_query = xtc_db_num_rows($check_zones_query);

      if ($check_zones_rows_query != $this->num_zones) {
        $this->install_zones($this->num_zones);
      }

    }

/**
 * class methods
 */
    function quote($method = '') {
      global $order, $shipping_weight;

      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      $error = false;

      for ($i=1; $i<=$this->num_zones; $i++) {
        $countries_table = constant('MODULE_SHIPPING_ZONES_COUNTRIES_' . $i);
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
        $zones_cost = constant('MODULE_SHIPPING_ZONES_COST_' . $dest_zone);

        $zones_table = preg_split("/[:,]/" , $zones_cost); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
        $size = sizeof($zones_table);
        for ($i=0; $i<$size; $i+=2) {
          if ($shipping_weight <= $zones_table[$i]) {
            $shipping = $zones_table[$i+1];
            $shipping_method = MODULE_SHIPPING_ZONES_TEXT_WAY . ' ' . $dest_country . ' : ' . $shipping_weight . ' ' . MODULE_SHIPPING_ZONES_TEXT_UNITS;
            break;
          }
        }

        $this->quotes = array('id' => $this->code,
                              'module' => MODULE_SHIPPING_ZONES_TEXT_TITLE);

        if ($shipping == -1) {
          $this->quotes['error'] = MODULE_SHIPPING_ZONES_UNDEFINED_RATE;
        } else {
          $shipping_cost = ($shipping + constant('MODULE_SHIPPING_ZONES_HANDLING_' . $dest_zone));
          $this->quotes['methods'] = array(array('id' => $this->code,
                                                 'title' => $shipping_method,
                                                 'cost'  => $shipping_cost));
        }
      }

      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_ZONES_INVALID_ZONE;

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ZONES_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_ZONES_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ZONES_ALLOWED', '', '6', '0', 'xtc_cfg_textarea(', now())"); //web28 -2011-06-13 - support for textareas
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ZONES_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_ZONES_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ZONES_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ZONES_NUMBER_ZONES', '5', '6', '0', now())");
    }

    function install_zones($number_of_zones = '1') {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ZONES_COUNTRIES_%'");
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ZONES_COST_%'");
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_SHIPPING_ZONES_HANDLING_%'");

      for ($i = 1; $i <= $number_of_zones; $i ++) {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_ZONES_COUNTRIES_".$i."', 'DE', '6', '0', 'xtc_cfg_textarea(', now())"); //web28 -2011-06-13 - support for textareas
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ZONES_COST_".$i."', '5:6.70,10:9.70,20:13.00', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_ZONES_HANDLING_".$i."', '0', '6', '0', now())");
      }

      if ($number_of_zones >=1) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'DE' WHERE configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_1'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '10:6.90,20:11.90,31.5:13.90' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_COST_1'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_HANDLING_1'");
      }
      if ($number_of_zones >=2) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AT,BE,BG,CY,CZ,DK,EE,ES,FI,FR,GB,GR,HU,IE,IT,LT,LU,LV,MC,MT,NL,PL,PT,RO,SE,SI,SK' WHERE configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_2'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '5:17.00,10:22.00,20:32.00,31.5:42.00' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_COST_2'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_HANDLING_2'");
      }
      if ($number_of_zones >=3) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AD,AL,AM,AZ,BA,BY,CH,FO,GE,GI,GL,HR,IS,KZ,LI,MD,ME,MK,NO,RS,RU,SM,TR,UA,VA' WHERE configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_3'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '5:30.00,10:35.00,20:45.00,31.5:55.00' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_COST_3'");
      }
      if ($number_of_zones >=4) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'CA,DZ,EG,IL,JO,LB,LR,LY,MA,PM,PS,SY,TN,US' WHERE configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_4'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '5:35.00,10:45.00,20:65.00,31.5:85.00' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_COST_4'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_HANDLING_4'");
      }
      if ($number_of_zones >=5) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = 'AE,AF,AG,AI,AN,AO,AR,AU,AW,BB,BD,BF,BH,BI,BJ,BM,BN,BO,BR,BS,BT,BW,BZ,CD,CF,CG,CI,CK,CL,CM,CN,CO,CR,CU,CV,DJ,DM,DO,EC,ER,ET,FJ,FK,FM,GA,GD,GF,GH,GM,GN,GP,GQ,GT,GU,GW,GY,HK,HN,HT,ID,IN,IQ,IR,JM,JP,KE,KG,KH,KI,KM,KN,KP,KR,KW,KY,LA,LC,LK,LS,MG,MH,ML,MM,MN,MO,MP,MQ,MR,MS,MU,MV,MW,MX,MY,MZ,NA,NC,NE,NG,NI,NP,NR,NZ,OM,PA,PE,PF,PG,PH,PK,PN,PR,PY,QA,RE,RW,SA,SB,SC,SD,SG,SH,SL,SN,SO,SR,ST,SV,SZ,TC,TD,TG,TH,TJ,TM,TO,TT,TV,TW,TZ,UG,UY,UZ,VC,VE,VN,VU,WF,WS,YE,ZA,ZM,ZW' WHERE configuration_key = 'MODULE_SHIPPING_ZONES_COUNTRIES_5'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '5:40.00,10:55.00,20:85.00,31.5:115.00' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_COST_5'");
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '0' WHERE  configuration_key = 'MODULE_SHIPPING_ZONES_HANDLING_5'");
      }
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_ZONES_STATUS',
                    'MODULE_SHIPPING_ZONES_ALLOWED', 
                    'MODULE_SHIPPING_ZONES_TAX_CLASS',
                    'MODULE_SHIPPING_ZONES_ZONE',
                    'MODULE_SHIPPING_ZONES_SORT_ORDER',
                    'MODULE_SHIPPING_ZONES_NUMBER_ZONES'
                    );

      for ($i=1; $i<=$this->num_zones; $i++) {
        $keys[] = 'MODULE_SHIPPING_ZONES_COUNTRIES_' . $i;
        $keys[] = 'MODULE_SHIPPING_ZONES_COST_' . $i;
        $keys[] = 'MODULE_SHIPPING_ZONES_HANDLING_' . $i;
      }

      return $keys;
    }
  }
?>