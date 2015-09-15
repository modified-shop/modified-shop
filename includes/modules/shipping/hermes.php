<?php
/* -----------------------------------------------------------------------------------------
   $Id: hermes.php 5134 2013-07-18 14:23:20Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(flat.php,v 1.40 2003/02/05); www.oscommerce.com
   (c) 2003	 nextcommerce (flat.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com
   (c) 2008 Leonid Lezner - www.waaza.eu

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  class hermes {
    var $code, $title, $description, $icon, $enabled;


    function __construct() {
      global $order, $shipping_weight;

      $this->code = 'hermes';
      $this->title = MODULE_SHIPPING_HERMES_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_HERMES_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_HERMES_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_hermes.gif';
      $this->tax_class = MODULE_SHIPPING_HERMES_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_HERMES_STATUS == 'True') ? true : false);

      if ( $this->enabled == true && is_object($order) && count($order->products) > 0) {
        $check_flag = false;
        $gew = 0;
        foreach($order->products as $prod) {
          $gew += (float)$prod['weight']*$prod['qty'];
        }
        if($gew <= MODULE_SHIPPING_HERMES_MAXGEWICHT) {
          $check_flag = true;
        }
        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function quote($method = '') {
      global $order, $shipping_weight;
	  	$gew = 0;

      foreach($order->products as $prod) {
        $gew += (float)$prod['weight']*$prod['qty'];
      }

      if($order->delivery['country']['iso_code_2'] == 'DE') {
        $preise = preg_split("/;/", MODULE_SHIPPING_HERMES_NATIONAL); 
      } else {
        $preise = preg_split("/;/", MODULE_SHIPPING_HERMES_INTERNATIONAL); 
      }
    
      $gewichte = preg_split("/;/", MODULE_SHIPPING_HERMES_GEWICHT); 

      $price_id = 0;
      foreach($gewichte as $g) {
        if($gew <= $g)
          break;
        $price_id++;
      }
    
      if($order->delivery['country']['iso_code_2'] == 'DE') {
        $stitle = MODULE_SHIPPING_HERMES_TEXT_WAY_DE . $shipping_weight . ' ' . MODULE_SHIPPING_HERMES_TEXT_UNITS;
      } else {
        $stitle = MODULE_SHIPPING_HERMES_TEXT_WAY_EU . $shipping_weight . ' ' . MODULE_SHIPPING_HERMES_TEXT_UNITS;
      }
    
      $this->quotes = array('id' => $this->code,
                            'module' => MODULE_SHIPPING_HERMES_TEXT_TITLE,
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $stitle,
                                                     'cost' => $preise[$price_id])));
      if ($this->tax_class > 0) {
        $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }
    
      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_HERMES_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_SHIPPING_HERMES_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_HERMES_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_NATIONAL', '3.90;5.90;8.90', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_INTERNATIONAL', '13.90;18.90;28.90', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_GEWICHT', '5;10;25', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_MAXGEWICHT', '25', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_HERMES_ALLOWED', 'DE,BE,DK,EE,FI,FR,IT,LU,NL,AT,SE,SK,SL,ES,CZ,HU', '6', '0', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_HERMES_STATUS', 
                   'MODULE_SHIPPING_HERMES_TAX_CLASS', 
                   'MODULE_SHIPPING_HERMES_NATIONAL',
                   'MODULE_SHIPPING_HERMES_INTERNATIONAL', 
                   'MODULE_SHIPPING_HERMES_GEWICHT', 
                   'MODULE_SHIPPING_HERMES_MAXGEWICHT', 
                   'MODULE_SHIPPING_HERMES_SORT_ORDER', 
                   'MODULE_SHIPPING_HERMES_ALLOWED');
    }
  }
?>