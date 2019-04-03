<?php
/* --------------------------------------------------------------
   $Id$

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

  function quote_primary_currency($to, $from = DEFAULT_CURRENCY) {
    if ($from === $to) return 1;

    $url = 'https://www.cryptonator.com/api/ticker/'.$from.'-'.$to;
    $currency = get_external_content(urldecode($url), 3, false);
    $currency = json_decode($currency, true);
    
    if (isset($currency['ticker']['price'])) {
      return $currency['ticker']['price'];
    } else {
      return false;
    }
  }

  function quote_secondary_currency($to, $from = DEFAULT_CURRENCY) {
    if ($from === $to) return 1;

    $url = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    $page = get_external_content($url, 3, false);    
    $XML = simplexml_load_string($page);

    $cur = array();        
    foreach($XML->Cube->Cube->Cube as $rate){
      $cur[(string)$rate['currency']] = (float)$rate['rate'];
    }
   
    $cur['EUR'] = 1;
   
    if (!empty($cur[$to]) && !empty($cur[$from])) {    
      return (float)$cur[$to] / $cur[$from];
    } else {
      return false;
    }
  }
?>