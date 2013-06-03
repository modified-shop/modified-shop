<?php
/* -----------------------------------------------------------------------------------------
   $Id: freeamount.php 4855 2013-06-03 12:15:20Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(freeamount.php,v 1.01 2002/01/24); www.oscommerce.com 
   (c) 2003	 nextcommerce (freeamount.php,v 1.12 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

/**
 * CUSTOMIZE THIS SETTING FOR THE NUMBER OF ZONES NEEDED
 *
 * + CUSTOMIZE THE SETTING IN lang/LANGUAGE/modules/shipping/freeamount.php
 */
/*##################################*/ 
 define('NUM_ZONES_MODULE_FREEAMOUNT', 2); 
/*##################################*/

  class freeamount {
    var $code, $title, $description, $icon, $enabled;

    function freeamount() {
      $this->code = 'freeamount';
      $this->title = MODULE_SHIPPING_FREEAMOUNT_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FREEAMOUNT_TEXT_DESCRIPTION;
      $this->icon ='';   // change $this->icon =  DIR_WS_ICONS . 'shipping_ups.gif'; to some freeshipping icon
      $this->sort_order = MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER;
      $this->enabled = ((MODULE_SHIPPING_FREEAMOUNT_STATUS == 'True') ? true : false);
      $this->num_zones = NUM_ZONES_MODULE_FREEAMOUNT;
    }

    function quote($method = '') {
    	global $xtPrice, $order;
	
      $dest_country = $order->delivery['country']['iso_code_2'];
      $dest_zone = 0;
      
      for ($i=1; $i<=$this->num_zones; $i++) {
        $countries_table = constant('MODULE_SHIPPING_FREEAMOUNT_COUNTRIES_' . $i);
        $country_zones = explode(',', $countries_table);
        if (in_array($dest_country, $country_zones)) {
          $dest_zone = $i;
          break;
        }
      }

      if ($dest_zone == 0) {
        $this->enabled = false;
      } else {
        $freeamount_zone = constant('MODULE_SHIPPING_FREEAMOUNT_AMOUNT_' . $dest_zone);

        if (( $xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < $freeamount_zone) && MODULE_SHIPPING_FREEAMOUNT_DISPLAY == 'False') {
          $this->enabled = false;
        }

        $this->quotes = array('id' => $this->code,
                              'module' => MODULE_SHIPPING_FREEAMOUNT_TEXT_TITLE);

        if ($xtPrice->xtcRemoveCurr($_SESSION['cart']->show_total()) < $freeamount_zone) {
          $this->quotes['error'] = sprintf(MODULE_SHIPPING_FREEAMOUNT_TEXT_WAY, $xtPrice->xtcFormat($freeamount_zone, true, 0, true));
        } else {
          $this->quotes['methods'] = array(array('id' => $this->code,
                                                 'title' => sprintf(MODULE_SHIPPING_FREEAMOUNT_TEXT_WAY, $xtPrice->xtcFormat($freeamount_zone, true, 0, true)),
                                                 'cost'  => 0));
        }
        
        if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);
      }
      
      if ($this->enabled)
        return $this->quotes;
    }

    function check() {
      $check = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FREEAMOUNT_STATUS'");
      $check = xtc_db_num_rows($check);

      return $check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_STATUS', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_ALLOWED', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_DISPLAY', 'True', '6', '7', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_AMOUNT', '50.00', '6', '8', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER', '0', '6', '4', now())");
      for ($i=1; $i<=$this->num_zones; $i++) {
        $default_countries = '';
        $default_amount = '';
        if ($i == 1) {
          $default_countries = 'DE';
          $default_amount = '50';
        }
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_COUNTRIES_" . $i ."', '" . $default_countries . "', '6', '0', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_FREEAMOUNT_AMOUNT_" . $i ."', '" . $default_amount . "', '6', '0', now())");
      }
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      $keys = array('MODULE_SHIPPING_FREEAMOUNT_STATUS',
                    'MODULE_SHIPPING_FREEAMOUNT_DISPLAY',
                    'MODULE_SHIPPING_FREEAMOUNT_SORT_ORDER');
      for ($i=1; $i<=$this->num_zones; $i++) {
        $keys[] = 'MODULE_SHIPPING_FREEAMOUNT_COUNTRIES_' . $i;
        $keys[] = 'MODULE_SHIPPING_FREEAMOUNT_AMOUNT_' . $i;
      }
      return $keys;
    }
  }
?>