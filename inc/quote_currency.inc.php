<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed function
  require_once(DIR_FS_INC.'get_external_content.inc.php');

  function quote_currency($to, $from = DEFAULT_CURRENCY) {
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
