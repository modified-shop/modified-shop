<?php
  /* --------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.2 2002/09/01); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.5 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (currencies.php 950 2005-05-14)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  // Class to handle currencies
  // TABLES: currencies
  class currencies {
    var $currencies;
    // class constructor
    function currencies() {
      $this->currencies = array();
      $currencies_query = xtc_db_query("select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES);
      while ($currencies = xtc_db_fetch_array($currencies_query)) {
        $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                                                       'symbol_left' => $currencies['symbol_left'],
                                                       'symbol_right' => $currencies['symbol_right'],
                                                       'decimal_point' => $currencies['decimal_point'],
                                                       'thousands_point' => $currencies['thousands_point'],
                                                       'decimal_places' => $currencies['decimal_places'],
                                                       'value' => $currencies['value']);
      }
    }

    // class methods
    function format($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '') {
      if ($calculate_currency_value) {
        $rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }
      return $format_string;
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format(xtc_add_tax($products_price, $products_tax) * $quantity);
    }
  }
?>