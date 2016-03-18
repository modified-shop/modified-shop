<?php
/* --------------------------------------------------------------
   $Id: localization.php 950 2005-05-14 16:45:21Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(localization.php,v 1.12 2003/06/25); www.oscommerce.com
   (c) 2003	 nextcommerce (localization.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  // include needed function
  require_once(DIR_FS_INC.'get_external_content.inc.php');

  function quote_yahooapis_currency($to, $from = DEFAULT_CURRENCY) {
    $url = 'https://query.yahooapis.com/v1/public/yql?q=select%20Rate%20from%20yahoo.finance.xchange%20where%20pair%20%3D%20%22'.$from.$to.'%22&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
    $currency = get_external_content($url, 3, false);
    $currency = json_decode($currency, true);

    if (isset($currency['query']['results']['rate']['Rate'])) {
      return $currency['query']['results']['rate']['Rate'];
    } else {
      return false;
    }
  }

  function quote_cryptonator_currency($to, $from = DEFAULT_CURRENCY) {
    $url = 'https://www.cryptonator.com/api/ticker/'.$from.'-'.$to;
    $currency = get_external_content(urldecode($url), 3, false);
    $currency = json_decode($currency, true);
    
    if (isset($currency['ticker']['price'])) {
      return $currency['ticker']['price'];
    } else {
      return false;
    }
  }
?>