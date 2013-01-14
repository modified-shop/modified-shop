<?php
/*------------------------------------------------------------------------------
   $Id: gls.php,v 1.1 2004/08/13 10:00:13 HHGAG Exp $

   XTC-GLS Shipping Module - Contribution for XT-Commerce http://www.xt-commerce.com
   modified by http://www.hhgag.com

   Copyright (c) 2004 H.H.G.
   -----------------------------------------------------------------------------
   based on:
   (c) 2003 Deutsche Post Module
   Original written by Marcel Bossert-Schwab (webmaster@wernich.de), Version 1.2b
   Addon Released under GLSL V2.0 by Gunter Sammet (Gunter@SammySolutions.com)

   Contribution based on:

   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2002 - 2003 osCommerce

   Released under the GNU General Public License

   ---------------------------------------------------------------------------*/


  class gls {
    var $code, $title, $description, $enabled, $icon;

// class constructor
    function gls() {
      global $order;

      $this->code = 'gls';
      $this->title = MODULE_SHIPPING_GLS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_GLS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_GLS_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_gls.gif';
      $this->tax_class = MODULE_SHIPPING_GLS_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_GLS_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_GLS_ZONE > 0) ) {
        $check_flag = false;
        $check_query_string = "select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_GLS_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id";
        $check_query = xtc_db_query($check_query_string);
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


    function quote($method = '') {
      global $shipping_quote_gls, $shipping_quote_all, $shipping_weight, $shipping_quoted, $shipping_gls_cost, $shipping_gls_method, $order;

        $error = false;
        $dest_country = $order->delivery['country']['iso_code_2'];
//        $dest_zone = 0;
        $dest_postal_code = $order->delivery['postcode'];
        //get rid of spaces in the postal code (e.g Great Britain or Canada)
        $dest_postal_code = strtoupper(str_replace(' ', '', $dest_postal_code));
//        print('Postal: ' . $dest_postal_code);
        //Don't know exactly how to deal with GB. For now, we check for GB and then only use the 2 leftmost characters.
        if($dest_country == 'GB') {
          $dest_postal_code = substr($dest_postal_code, 0, 2);
        }

//print('Dest. country: ' . $dest_country . ', Postalcode: ' . $dest_postal_code . ', Weight: ' . $shipping_weight);


//Since the postal codes are empty for a lot of countries (all have only 2 chars), we need to determine the length of pw.gls_wiehgt_ref to find out which SQL statement we want to use.
      $string_check_length ="SELECT pw.gls_weight_ref FROM gls_postal_to_weight pw, gls_country_to_postal cp WHERE cp.gls_postal_reference = pw.gls_postal_reference AND cp.gls_country = '" . $dest_country . "'";
      $country_length = xtc_db_query($string_check_length);
      $country_length_result = xtc_db_fetch_array($country_length);

//print('country: ' . $country_length_result['gls_weight_ref'] . ' ');

      if(strlen($country_length_result['gls_weight_ref']) == 3){
      $query_string ="SELECT w.gls_weight_price_string, w.gls_free_shipping_over, w.gls_shipping_subsidized FROM gls_weight w, gls_postal_to_weight pw, gls_country_to_postal cp WHERE cp.gls_postal_reference = pw.gls_postal_reference AND pw.gls_weight_ref = w.gls_weight_ref AND cp.gls_postal_reference = pw.gls_postal_reference AND cp.gls_country = '" . $dest_country . "' AND '" . $dest_postal_code . "' BETWEEN pw.gls_from_postal AND pw.gls_to_postal";

      } else {
      $query_string ="SELECT w.gls_weight_price_string, w.gls_free_shipping_over, w.gls_shipping_subsidized FROM gls_weight w, gls_postal_to_weight pw, gls_country_to_postal cp WHERE cp.gls_postal_reference = pw.gls_postal_reference AND pw.gls_weight_ref = w.gls_weight_ref AND cp.gls_postal_reference = pw.gls_postal_reference AND cp.gls_country = '" . $dest_country . "'";

      }


//print($query_string);

      $country_query = xtc_db_query($query_string);
      $gls_cost = xtc_db_fetch_array($country_query);


      if (!$country_length_result['gls_weight_ref']) {
        $this->quotes = array('id' => $this->code,
                              'module' => MODULE_SHIPPING_GLS_TEXT_TITLE,
                              'error' => MODULE_SHIPPING_GLS_INVALID_ZONE,
                              'methods' => array(array('id' => $this->code,
                                                       'title' => MODULE_SHIPPING_GLS_TEXT_TITLE,
                                                       'cost' => 0)));

        if ($this->tax_class > 0) {
          $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

        return $this->quotes;
      }
      $shipping = -1;
//        $gls_cost = constant('MODULE_SHIPPING_GLS_COST_' . $i);
      $gls_table = preg_split("/[-:,]/" , $gls_cost['gls_weight_price_string']); // Hetfield - 2009-11-19 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
      $n=1;
      $y=2;
      for ($i = 0; $i < count($gls_table); $i ++) {
        if ( ($shipping_weight > $gls_table[$i]) && ($shipping_weight <= $gls_table[$n]) ) {
          $shipping = $gls_table[$y];
          $shipping_gls_method = MODULE_SHIPPING_GLS_TEXT_WAY . ' ' . $dest_country . " : " . $shipping_weight . ' ' . MODULE_SHIPPING_GLS_TEXT_UNITS;
          break;
        }
        $i = $i + 2;
        $n = $n + 3;
        $y = $y + 3;
      }
      if ( $shipping == -1) {
        $shipping_gls_cost = 0;
        $shipping_gls_method = MODULE_SHIPPING_GLS_UNDEFINED_RATE;
        $error = true;
      } else {
        //Check if there is free shipping in the database.
        if($gls_cost['gls_free_shipping_over'] == -1.0000){
          //do normal processing of shipping
          $shipping_gls_cost = ($shipping + MODULE_SHIPPING_GLS_HANDLING + SHIPPING_HANDLING);
        } else if(($gls_cost['gls_free_shipping_over'] != -1.0000) && ($gls_cost['gls_shipping_subsidized'] == -1.0000)){
          //free shipping if over amount
          if($order->info['subtotal'] >= $gls_cost['gls_free_shipping_over']){
//              print('Free Order: ' . $order->info['subtotal'] . ' Cost: ' . $gls_cost['gls_free_shipping_over']);
            //shipping is free
            $shipping_gls_cost = 0;
            $shipping_gls_method = MODULE_SHIPPING_GLS_FREE_SHIPPING;
          } else {
//              print('Free Else Order: ' . $order->info['subtotal'] . ' Cost: ' . $gls_cost['gls_free_shipping_over']);
            //charge for shipping
            $shipping_gls_cost = ($shipping + MODULE_SHIPPING_GLS_HANDLING + SHIPPING_HANDLING);
          }
        //subsidized shipping over amount
        } else {
          if($order->info['subtotal'] >= $gls_cost['gls_free_shipping_over']){
//              print('Sub Order: ' . $order->info['subtotal'] . ' Cost: ' . $gls_cost['gls_free_shipping_over']);
            //shipping is subsidized
            $shipping_gls_cost = (($shipping + MODULE_SHIPPING_GLS_HANDLING + SHIPPING_HANDLING)-$gls_cost['gls_shipping_subsidized']);
            $shipping_gls_method = MODULE_SHIPPING_GLS_SUBSIDIZED_SHIPPING . ' ' .MODULE_SHIPPING_GLS_TEXT_WAY . ' ' . $dest_country . " : " . $shipping_weight . ' ' .             MODULE_SHIPPING_GLS_TEXT_UNITS;
          } else {
//              print('Sub Else Order: ' . $order->info['subtotal'] . ' Cost: ' . $gls_cost['gls_free_shipping_over']);
            //charge for shipping
            $shipping_gls_cost = ($shipping + MODULE_SHIPPING_GLS_HANDLING + SHIPPING_HANDLING);

          }
        }
      }
     $this->quotes = array('id' => $this->code,
                     'module' => MODULE_SHIPPING_GLS_TEXT_TITLE,
                     'methods' => array(array('id' => $this->code,
                                              'title' => $shipping_gls_method,
                                              'cost' => $shipping_gls_cost)));

      if ($this->tax_class > 0) {
       $this->quotes['tax'] = xtc_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
      }

      if (xtc_not_null($this->icon)) $this->quotes['icon'] = xtc_image($this->icon, $this->title);

      if($error){
        $this->quotes['error'] = $shipping_gls_method;
      }
      return $this->quotes;

    }


    function check() {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_GLS_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);

      return $this->_check;
    }

    function install() {
// put out a notice to make sure that the tables are created

//disabled the next one because of some problems: If module is installed and this set to 0, checkout doesn't work.
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_GLS_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_HANDLING', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_ALLOWED', '', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_GLS_SORT_ORDER', '0', '6', '0', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_GLS_TAX_CLASS', '0', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_GLS_ZONE', '0', '6', '0', 'xtc_get_zone_class_title', 'xtc_cfg_pull_down_zone_classes(', now())");


      if (xtc_db_query("DROP TABLE IF EXISTS gls_country_to_postal")){
      } else {
      }
// Table structure for table `gls_country_to_postal`
      if (xtc_db_query("CREATE TABLE gls_country_to_postal (gls_country char(2) NOT NULL default '', gls_postal_reference int(11) NOT NULL default '0', PRIMARY KEY  (gls_country)) ENGINE=MyISAM")){
      } else {
      }

// Dumping data for table `gls_country_to_postal`
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AE', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AI', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AL', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AN', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AO', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AR', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AS', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AT', 11)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AU', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AW', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('AZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BB', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BD', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BE', 222)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BF', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BG', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BH', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BI', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BJ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BN', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BO', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BR', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BS', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BT', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BW', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BY', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('BZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CA', 22)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CH', 15)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CK', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CL', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CN', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CR', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CY', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('CZ', 19)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('DE', 1)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('DJ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('DK', 3)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('DM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('DO', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('EC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('EE', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('EG', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ES', 18)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ET', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('FI', 4)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('FM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('FR', 5)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('GB', 7)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('GL', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('GR', 6)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('HK', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('HR', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('HU', 21)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ID', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('IE', 8)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('IL', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('IN', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('IS', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('IT', 9)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('JO', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('JP', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('KH', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('KN', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('KR', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('KW', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LB', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LK', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LT', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LU', 223)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('LV', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MA', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MD', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MH', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MK', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ML', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MO', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MP', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MQ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MR', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MT', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MU', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MV', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MW', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MX', 22)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MY', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('MZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NA', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NE', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NI', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NL', 224)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NO', 10)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('NZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('OM', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PA', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PE', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PH', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PK', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PL', 12)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PR', 22)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PS', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PT', 13)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PW', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('PY', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('QA', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('RE', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('RO', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('RU', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('RW', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SA', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SE', 14)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SG', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SI', 16)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SK', 17)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SN', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SR', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SV', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SY', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('SZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TD', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TH', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TN', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TR', 20)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TT', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TW', 24)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('TZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('UA', 23)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('UG', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('US', 22)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('UY', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('UZ', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('VC', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('VE', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('VN', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('VU', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('WF', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('YE', 26)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ZA', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ZM', 25)");
      xtc_db_query("insert into gls_country_to_postal (gls_country, gls_postal_reference) values ('ZW', 25)");


      if (xtc_db_query("DROP TABLE IF EXISTS gls_postal_to_weight")){
      } else {
      }
// Table structure for table `gls_postal_to_weight`
      if (xtc_db_query("CREATE TABLE gls_postal_to_weight (gls_postal_reference int(11) NOT NULL default '0', gls_from_postal varchar(10) NOT NULL default '', gls_to_postal varchar(10) NOT NULL default '', gls_weight_ref char(3) NOT NULL default '', PRIMARY KEY  (gls_postal_reference,gls_from_postal)) ENGINE=MyISAM")){
      } else {
      }

// Dumping data for table `gls_postal_to_weight`

// GERMANY

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '00000', '18564', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '18565', '18565', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '18566', '25848', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25849', '25849', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25850', '25858', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25859', '25859', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25860', '25862', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25863', '25863', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25864', '25868', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25869', '25869', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25870', '25937', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25938', '25938', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25939', '25945', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25946', '25946', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25947', '25979', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25980', '25980', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25981', '25991', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25992', '25992', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25993', '25995', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25996', '25997', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25998', '25998', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '25999', '25999', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26000', '26464', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26465', '26465', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26466', '26473', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26474', '26474', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26475', '26485', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26486', '26486', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26487', '26547', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26548', '26548', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26549', '26570', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26571', '26571', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26572', '26578', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26579', '26579', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26580', '26756', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26757', '26757', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '26758', '27497', 'DE1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '27498', '27498', 'DE2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (1, '27499', '99999', 'DE1')");

// NO POSTAL ZONES

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (222, '', '', 'BE')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (223, '', '', 'LU')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (224, '', '', 'NL')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (3, '', '', 'DK')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (4, '', '', 'FI')");

// FRANCE

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (5, '01000', '19999', 'FR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (5, '20000', '20620', 'FR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (5, '20621', '95999', 'FR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (5, '98000', '98999', 'FR1')");

// GREECE

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '10000', '10699', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '11100', '11899', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '12100', '12499', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '13100', '13499', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '14100', '14599', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '15100', '15799', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '16100', '16799', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '17100', '17799', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '18100', '18899', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '19000', '19005', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '19006', '19199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '19200', '19499', 'GR1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '19500', '19599', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '20000', '20099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '20100', '20100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '20101', '21099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '21100', '21100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '21101', '21199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '21200', '21200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '21201', '22099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '22100', '22100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '22101', '22199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '22200', '22200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '22201', '23099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '23100', '23100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '23101', '24099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '24100', '24100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '24101', '24199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '24200', '24200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '24201', '25099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '25100', '25100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '25101', '26220', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '26221', '26443', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '26444', '27099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '27100', '27100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '27101', '27199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '27200', '27200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '27201', '28079', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '28080', '28300', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '29100', '29100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '29101', '30099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30100', '30100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30101', '30199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30200', '30200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30201', '30299', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30300', '30300', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '30301', '31099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '31101', '32099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '32100', '32100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '32101', '32199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '32200', '32200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '32201', '33999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '34100', '34100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '34301', '35099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '35100', '35100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '35101', '37000', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '37002', '37002', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '37100', '38220', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '38221', '38446', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '38447', '40299', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '40300', '40300', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '40301', '41220', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '41221', '41447', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '41448', '42099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '42100', '42100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '42101', '43099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '43100', '43100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '43101', '45220', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '45221', '45445', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '45446', '46099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '46100', '46100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '46101', '47099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '47100', '47100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '47101', '48099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '48100', '48100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '48101', '48999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '49100', '49100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '49200', '50099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '50100', '50100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '50101', '50199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '50200', '50200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '50201', '50299', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '52100', '52100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '52101', '53999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '54000', '54999', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '55000', '55099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '55100', '55599', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '56000', '56099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '56100', '56799', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '56800', '58099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58100', '58100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58101', '58199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58200', '58200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58201', '58399', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58400', '58400', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58401', '58499', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58500', '58500', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '58501', '59099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '59100', '59100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '59101', '59199', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '59200', '59200', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '59201', '60029', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '60100', '60100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '60101', '61099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '61100', '61100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '61101', '62120', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '62121', '62125', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '62126', '63999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '64001', '65200', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '65201', '65404', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '65405', '66099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '66100', '66100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '66101', '67099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '67100', '67100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '67101', '67999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '68001', '68099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '68100', '68100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '68101', '69099', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '69100', '69100', 'GR2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '69101', '69999', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '70007', '70007', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '70014', '70014', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '71201', '71410', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '72053', '72053', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '72100', '72100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '72200', '72200', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '72300', '72300', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '72400', '72400', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '73131', '73136', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '73200', '73200', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '73300', '73300', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '74100', '74100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '81100', '81100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '82100', '82100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '83100', '83100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '84300', '84300', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '84400', '84400', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '84600', '84600', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '84700', '84700', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '85100', '85100', 'GR3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '85101', '85107', 'GR4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (6, '85300', '85300', 'GR3')");

// GREAT BRITAIN

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'A0', 'BS', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'BT', 'BT', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'BU', 'GX', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'GY', 'GY', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'GZ', 'HR', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'HS', 'HS', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'HT', 'IL', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'IM', 'IM', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'IN', 'JD', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'JE', 'JE', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'JF', 'ZD', 'GB1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'ZE', 'ZE', 'GB2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (7, 'ZF', 'ZZ', 'GB1')");

// NO POSTAL ZONES

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (8, '', '', 'IE')");

// ITALY

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (9, '00000', '99999', 'IT')");

// NO POSTAL ZONES

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (10, '', '', 'NO')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (11, '', '', 'AT')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (12, '', '', 'PL')");

// PORTUGAL

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (13, '1000', '8999', 'PT1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (13, '9000', '9999', 'PT2')");

// SPAIN

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '01000', '06999', 'ES1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '07000', '07999', 'ES2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '08000', '11699', 'ES1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '11888', '34999', 'ES1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '35000', '35999', 'ES3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '36000', '37999', 'ES1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '38000', '38999', 'ES3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '39000', '50999', 'ES1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '51000', '52999', 'ES3')");

// NO POSTAL ZONES

       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (13, '', '', 'PT')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (14, '', '', 'SE')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (15, '', '', 'CH')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (16, '', '', 'SI')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (17, '', '', 'SK')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (18, '', '', 'ES')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (19, '', '', 'CZ')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (20, '', '', 'TR')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (21, '', '', 'HU')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (22, '', '', 'G1')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (23, '', '', 'G2')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (24, '', '', 'G3')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (25, '', '', 'G4')");
       xtc_db_query("insert into gls_postal_to_weight (gls_postal_reference, gls_from_postal, gls_to_postal, gls_weight_ref) values (26, '', '', 'G5')");


      if (xtc_db_query("DROP TABLE IF EXISTS gls_weight")){
      } else {
      }
// Table structure for table `gls_weight`
      if (xtc_db_query("CREATE TABLE gls_weight (gls_weight_ref char(3) NOT NULL default '', gls_weight_price_string text NOT NULL, gls_free_shipping_over decimal(15,4) NOT NULL default '-1.0000', gls_shipping_subsidized decimal(15,4) NOT NULL default '-1.0000', PRIMARY KEY  (gls_weight_ref)) ENGINE=MyISAM")){
      } else {
      }

// Dumping data for table `gls_weight`

       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('DE1', '0-5:3.07,5-8:4.19,8-10:4.86,10-15:6.54,15-20:8.44,20-25:9.97,25-32:13.04,32-40:15.34', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('DE2', '0-5:11.23,5-8:11.86,8-10:12.53,10-15:14.21,15-20:16.11,20-25:19.23,25-32:20.71,32-40:23.01', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('BE', '0-5:5.63,5-10:7.16,10-15:8.44,15-25:9.97,25-30:12.02,30-40:14.58,40-50:17.39', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('LU', '0-5:5.63,5-10:7.16,10-15:8.44,15-25:9.97,25-30:12.02,30-40:14.58,40-50:17.39', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('NL', '0-5:5.63,5-10:7.16,10-15:8.44,15-25:9.97,25-30:12.02,30-40:14.58,40-50:17.39', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('DK', '0-5:6.65,5-10:8.70,10-15:10.74,15-25:12.79,25-30:14.32,30-40:16.37,40-50:18.93', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('FI', '0-5:18.98,5-10:23.02,10-15:25.17,15-25:28.39,25-30:29.92,30-40:34.27,40-50:39.39', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('FR1', '0-5:9.51,5-10:12.02,10-15:14.32,15-25:15.35,25-30:17.39,30-40:20.46,40-50:23.02', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('FR2', '0-5:63.94,5-10:66.50,10-15:69.05,15-25:86.96,25-30:89.51,30-40:92.07,40-50:97.19', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GR1', '0-5:19.95,5-10:28.13,10-15:37.34,15-25:53.96,25-30:59.54,30-40:99.74,40-50:115.09', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GR2', '0-5:19.95,5-10:28.13,10-15:37.34,15-25:53.96,25-30:59.54,30-40:99.74,40-50:115.09', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GR3', '0-5:43.48,5-10:57.29,10-15:64.96,15-25:86.45,25-30:99.23,30-40:120.72,40-50:142.20', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GR4', '0-5:43.48,5-10:57.29,10-15:64.96,15-25:86.45,25-30:99.23,30-40:120.72,40-50:142.20', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GB1', '0-5:10.79,5-10:12.79,10-15:14.07,15-25:17.14,25-30:18.47,30-40:20.51,40-50:23.53', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('GB2', '0-5:40.92,5-10:40.92,10-15:43.48,15-25:48.59,25-30:53.71,30-40:58.82,40-50:63.94', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('IE', '0-5:21.02,5-10:25.58,10-15:27.57,15-25:30.66,25-30:31.61,30-40:34.78,40-50:38.36', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('IT', '0-5:11.51,5-10:14.27,10-15:16.24,15-25:18.03,25-30:19.82,30-40:20.97,40-50:23.02', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('NO', '0-5:18.47,5-10:20.41,10-15:21.74,15-25:25.32,25-30:29.00,30-40:30.69,40-50:35.81', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('AT', '0-5:6.65,5-10:8.70,10-15:10.74,15-25:12.79,25-30:14.32,30-40:16.37,40-50:18.93', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('PL', '0-5:13.30,5-10:14.32,10-15:15.35,15-25:19.44,25-30:23.02,30-40:29.67,40-50:37.85', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('PT1', '0-5:21.89,5-10:24.60,10-15:29.16,15-25:34.02,25-30:37.34,30-40:48.34,40-50:53.71', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('PT2', '0-5:46.04,5-10:50.13,10-15:57.80,15-25:74.17,25-30:81.84,30-40:86.96,40-50:97.19', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('SE', '0-5:16.11,5-10:18.93,10-15:21.23,15-25:24.30,25-30:26.34,30-40:29.92,40-50:35.29', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('CH', '0-5:7.57,5-10:9.21,10-15:11.30,15-25:14.58,25-30:15.86,30-40:18.41,40-50:21.74', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('SK', '0-5:15.35,5-10:18.41,10-15:20.97,15-25:25.06,25-30:27.62,30-40:32.23,40-50:36.83', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('SI', '0-5:19.44,5-10:23.02,10-15:25.06,15-25:29.67,25-30:33.25,30-40:38.36,40-50:42.46', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('ES1', '0-5:16.37,5-10:18.93,10-15:20.46,15-25:24.55,25-30:26.60,30-40:34.27,40-50:39.90', '350.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('ES2', '0-5:34.53,5-10:41.43,10-15:45.78,15-25:53.71,25-30:55.75,30-40:65.22,40-50:73.91', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('ES3', '0-5:51.15,5-10:56.27,10-15:61.38,15-25:79.28,25-30:89.51,30-40:102.30,40-50:122.76', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('CZ', '0-5:17.90,5-10:20.46,10-15:22.51,15-25:27.62,25-30:31.20,30-40:36.32,40-50:40.41', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('TR', '0-5:29.67,5-10:45.52,10-15:59.34,15-25:89.51,25-30:98.72,30-40:121.74,40-50:145.27', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('HU', '0-5:23.02,5-10:25.58,10-15:30.69,15-25:35.81,25-30:38.36,30-40:40.92,40-50:43.48', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('G1', '0-1:40.90,1-2:44.48,2-3:48.57,3-4:54.19,4-5:60.33,5-6:67.49,6-7:71.06,7-8:74.13,8-9:77.20,9-10:81.29,10-11:83.85,11-12:86.40,12-13:88.96,13-14:91.52,14-15:94.07,15-16:96.63,16-17:99.19,17-18:101.74,18-19:104.30,19-20:106.86,20-21:108.90,21-22:110.95,22-23:113.50,23-24:115.55,24-25:118.10,25-26:120.15,26-27:122.71,27-28:124.75,28-29:127.31,29-30:129.35,30-31:131.40,31-32:133.95,32-33:134.00,33-34:138.56,34-35:140.60,35-36:143.16,36-37:145.20,37-38:147.76,38-39:149.80,39-40:151.85,40-41:153.89,41-42:155.94,42-43:157.98,43-44:160.55,44-45:162.59,45-46:164.63,46-47:166.68,47-48:168.72,48-49:170.77,49-50:172.81', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('G2', '0-1:47.55,1-2:51.13,2-3:54.20,3-4:29.82,4-5:65.96,5-6:71.58,6-7:75.67,7-8:79.25,8-9:82.83,9-10:86.41,10-11:89.99,11-12:93.06,12-13:69.12,13-14:99.70,14-15:102.77,15-16:105.84,16-17:109.42,17-18:112.48,18-19:115.55,19-20:119.64,20-21:122.20,21-22:124.76,22-23:127.31,23-24:129.87,24-25:133.45,25-26:137.03,26-27:140.61,27-28:144.70,28-29:148.27,29-30:151.85,30-31:155.43,31-32:159.01,32-33:162.59,33-34:166.17,34-35:169.75,35-36:173.33,36-37:176.91,37-38:180.49,38-39:184.07,39-40:186.62,40-41:189.69,41-42:192.25,42-43:195.31,43-44:197.87,44-45:200.94,45-46:203.49,46-47:206.56,47-48:209.12,48-49:212.19,49-50:214.74', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('G3', '0-1:57.77,1-2:63.91,2-3:69.02,3-4:74.64,4-5:80.78,5-6:89.98,6-7:96.63,7-8:103.28,8-9:109.92,9-10:116.57,10-11:120.66,11-12:124.24,12-13:128.33,13-14:132.42,14-15:136.51,15-16:140.60,16-17:144.69,17-18:148.78,18-19:152.87,19-20:156.45,20-21:159.52,21-22:162.59,22-23:165.14,23-24:168.21,24-25:172.30,25-26:176.90,26-27:180.99,27-28:185.08,28-29:189.17,29-30:193.26,30-31:197.87,31-32:201.96,32-33:206.05,33-34:210.14,34-35:214.23,35-36:218.83,36-37:222.92,37-38:227.01,38-39:231.10,39-40:236.21,40-41:240.81,41-42:244.90,42-43:249.51,43-44:254.11,44-45:258.71,45-46:263.31,46-47:267.40,47-48:272.00,48-49:276.60,49-50:281.21', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('G4', '0-1:73.62,1-2:77.71,2-3:82.82,3-4:88.45,4-5:97.14,5-6:104.81,6-7:110.95,7-8:117.08,8-9:123.22,9-10:131.40,10-11:136.00,11-12:140.60,12-13:145.20,13-14:149.80,14-15:154.41,15-16:159.01,16-17:163.61,17-18:168.21,18-19:172.81,19-20:175.37,20-21:178.44,21-22:181.50,22-23:184.06,23-24:187.13,24-25:191.22,25-26:195.82,26-27:199.91,27-28:204.00,28-29:208.09,29-30:212.18,30-31:216.78,31-32:220.87,32-33:224.96,33-34:229.05,34-35:233.14,35-36:237.75,36-37:241.81,37-38:245.93,38-39:250.02,39-40:255.13,40-41:259.73,41-42:264.84,42-43:269.96,43-44:274.56,44-45:279.67,45-46:284.27,46-47:289.39,47-48:294.50,48-49:299.10,49-50:304.21', '-1.0000', '-1.0000')");
       xtc_db_query("insert into gls_weight (gls_weight_ref, gls_weight_price_string, gls_free_shipping_over, gls_shipping_subsidized) values ('G5', '0-1:79.25,1-2:83.85,2-3:90.49,3-4:95.61,4-5:104.30,5-6:110.43,6-7:115.04,7-8:121.68,8-9:128.84,9-10:137.53,10-11:141.62,11-12:145.71,12-13:149.80,13-14:153.89,14-15:158.50,15-16:162.59,16-17:166.68,17-18:170.77,18-19:174.86,19-20:179.46,20-21:183.04,21-22:186.11,22-23:189.68,23-24:193.26,24-25:197.87,25-26:202.47,26-27:207.07,27-28:211.67,28-29:216.27,29-30:220.87,30-31:225.47,31-32:230.59,32-33:235.19,33-34:239.79,34-35:244.39,35-36:248.99,36-37:253.60,37-38:258.20,38-39:263.31,39-40:267.40,40-41:273.02,41-42:278.14,42-43:283.25,43-44:288.87,44-45:293.99,45-46:299.10,46-47:304.73,47-48:309.84,48-49:315.46,49-50:320.58', '-1.0000', '-1.0000')");
    }

// put out a notice to drop the tables or leave them
    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
      xtc_db_query("DROP TABLE IF EXISTS gls_country_to_postal");
      xtc_db_query("DROP TABLE IF EXISTS gls_postal_to_weight");
      xtc_db_query("DROP TABLE IF EXISTS gls_weight");
    }
//disabled the next one because of some problems: If module is installed and this set to 0, checkout doesn't work.
    function keys() {
      $keys = array('MODULE_SHIPPING_GLS_STATUS', 'MODULE_SHIPPING_GLS_HANDLING','MODULE_SHIPPING_GLS_ALLOWED', 'MODULE_SHIPPING_GLS_SORT_ORDER', 'MODULE_SHIPPING_GLS_TAX_CLASS', 'MODULE_SHIPPING_GLS_ZONE');

      return $keys;
    }
  }
?>