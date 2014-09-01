<?php
/* -----------------------------------------------------------------------------------------
   $Id: shipping.php 2807 2012-04-29 18:11:28Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(shipping.php,v 1.22 2003/05/08); www.oscommerce.com
   (c) 2003 nextcommerce (shipping.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (shipping.php 1305 2005-10-14)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  //web28 ignore shipping modules
  define ('IGNORE_CHEAPEST_MODULES', 'selfpickup');

  class shipping {
    var $modules;

    // class constructor
    function shipping($module = '') {
      global $PHP_SELF,$order;

      if (defined('MODULE_SHIPPING_INSTALLED') && xtc_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

        if ( (xtc_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }
        // load unallowed modules into array - remove spaces and line breaks by web28
        $unallowed_modules = preg_replace("'[\r\n\s]+'",'',$_SESSION['customers_status']['customers_status_shipping_unallowed'].','.$order->customer['shipping_unallowed']);
        $unallowed_modules = explode(',',$unallowed_modules);
        for ($i = 0, $n = sizeof($include_modules); $i < $n; $i++) {
          if (!in_array(str_replace('.php', '', $include_modules[$i]['file']), $unallowed_modules)) {
            // check if zone is alowed to see module
            if (constant('MODULE_SHIPPING_' . strtoupper(str_replace('.php', '', $include_modules[$i]['file'])) . '_ALLOWED') != '') {
              $unallowed_zones = explode(',', constant('MODULE_SHIPPING_' . strtoupper(str_replace('.php', '', $include_modules[$i]['file'])) . '_ALLOWED'));
            } else {
              $unallowed_zones = array();
            }
            if (in_array($_SESSION['delivery_zone'], $unallowed_zones) == true || count($unallowed_zones) == 0) {
              include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/' . $include_modules[$i]['file']);
              include(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
              $GLOBALS[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
            }
          }
        }
      }
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

      $quotes_array = array();

      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
          $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
        } else {
          $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }

        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
          $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
          $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        $include_quotes = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (xtc_not_null($module) && isset($GLOBALS[$class])) {
            if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
            $include_quotes[] = $class;
          }
        }

         for ($i=0, $size = sizeof($include_quotes); $i<$size; $i++) {
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          // BOF - Tomcraft - 2011-02-01 - Paypal Express Modul
          // if (is_array($quotes)) $quotes_array[] = $quotes;
          if (!isset ($quotes['error'])) {
            if (is_array($quotes)) $quotes_array[] = $quotes;
          }
          // EOF - Tomcraft - 2011-02-01 - Paypal Express Modul
        }
      }

      return $quotes_array;
    }

    function cheapest() {

      if (is_array($this->modules)) {
        $rates = array();

        $ignore_cheapest_array = explode(',',IGNORE_CHEAPEST_MODULES); //web28 ignore shipping modules

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
           //BOF - Dokuman - 2009-10-02 - set undefined index
            //$size = sizeof($quotes['methods']);
            $size = isset($quotes['methods']) && is_array($quotes['methods']) ? sizeof($quotes['methods']) : 0;
            //BOF - Dokuman - 2009-10-02 - set undefined index
            for ($i=0; $i<$size; $i++) {
              // BOF - Tomcraft - 2011-02-01 - Paypal Express Modul
              //if(array_key_exists("cost",$quotes['methods'][$i])) {
              if(array_key_exists("cost",$quotes['methods'][$i]) && !isset ($quotes['error'][$i]) && !in_array($quotes['id'],$ignore_cheapest_array)) { //web28 ignore shipping modules
              // EOF - Tomcraft - 2011-02-01 - Paypal Express Modul
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
                                // echo $quotes['methods'][$i]['cost'];
              }
            }
          }
        }

        $cheapest = false;
        for ($i=0, $size = sizeof($rates); $i<$size; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }
        return $cheapest;

      }

    }
  }
?>