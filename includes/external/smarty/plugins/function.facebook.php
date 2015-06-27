<?php
/* -----------------------------------------------------------------------------------------
   $Id: function.facebook.php 2147 2011-09-01 07:15:14Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.facebook.php 1871) http://www.webs.de/

   Add the Facebook tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {facebook id=1234567890}

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
function smarty_function_facebook($params, &$smarty) {
  global $PHP_SELF;
  global $last_order; // from checkout_success.php

  /*
  $query = xtc_db_query("-- function.facebook.php
    SELECT value
    FROM " . TABLE_ORDERS_TOTAL . "
    WHERE orders_id = '" . $last_order . "' AND class='ot_total'");
  */
  $query = xtc_db_query("-- function.facebook.php
    SELECT DISTINCT ot.value,
                     o.currency
               FROM " . TABLE_ORDERS . " o
          LEFT JOIN (" . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS . ")
                 ON (o.orders_id = ot.orders_id)
              WHERE o.orders_id = '" . $last_order . "' AND ot.class='ot_total'");
  $orders_total = xtc_db_fetch_array($query);
  
  $id = isset($params['id']) ? (int)$params['id'] : false;

  if (!$id) {
    return false;
  }

  $beginCode = '<script>(function() {
  var _fbq = window._fbq || (window._fbq = []);
  if (!_fbq.loaded) {
    var fbds = document.createElement(\'script\');
    fbds.async = true;
    fbds.src = \'//connect.facebook.net/en_US/fbds.js\';
    var s = document.getElementsByTagName(\'script\')[0];
    s.parentNode.insertBefore(fbds, s);
    _fbq.loaded = true;
  }
})();
  ';

  $endCode = 'window._fbq = window._fbq || [];
window._fbq.push([\'track\', \''.$id.'\', {\'value\':\''.$orders_total['value'].'\',\'currency\':\''.$orders_total['currency'].'\'}]);
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev='.$id.'&amp;cd[value]='.$orders_total['value'].'&amp;cd[currency]='.$orders_total['currency'].'&amp;noscript=1" /></noscript>
  ';

  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false)) {
    return $beginCode . $endCode;
  }
}