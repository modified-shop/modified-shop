<?php
/* -----------------------------------------------------------------------------------------
   $Id: function.piwik.php 2147 2011-09-01 07:15:14Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.piwik.php 1871) http://www.webs.de/

   Add the Piwik tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {piwik url=piwik.example.com id=1} or
   {piwik url=piwik.example.com id=1 goal=1}
   where "id=1" is the domain-ID you want to track (see your Piwik configuration for details)

   Asynchronous Piwik tracking is possible from Piwik version 1.1 and higher

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
function smarty_function_piwik($params, &$smarty) {
  global $PHP_SELF;
  
  $url = isset($params['url']) ? $params['url'] : false;
  $id = isset($params['id']) ? (int)$params['id'] : false;
  $goal = isset($params['goal']) ? (int)$params['goal'] : false;

  if (!$url || !$id) {
    return false;
  }

  $url = str_replace(array('http://', 'https://'), '', $url);
  $url = trim($url, '/');

  $beginCode = '<script type="text/javascript">
    var _paq = _paq || [];
    (function(){
      var u=(("https:" == document.location.protocol) ? "https://'.$url.'/" : "http://'.$url.'/");
      _paq.push([\'setSiteId\', '.$id.']);
      _paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
      _paq.push([\'trackPageView\']);
      _paq.push([\'enableLinkTracking\']);
  ';

  $endCode = '    var d=document,
        g=d.createElement(\'script\'),
        s=d.getElementsByTagName(\'script\')[0];
        g.type=\'text/javascript\';
        g.defer=true;
        g.async=true;
        g.src=u+\'piwik.js\';
        s.parentNode.insertBefore(g,s);
    })();
    </script>
    <noscript><p><img src="http://'.$url.'/piwik.php?idsite=1&rec=1" style="border:0" alt="" /></p></noscript>
  ';

  $orderCode = null;
  if ((strpos($PHP_SELF, FILENAME_CHECKOUT_SUCCESS) !== false) && ($goal > 0)) {
    $orderCode = getOrderDetailsPiwik($goal);
  }
  return $beginCode . $orderCode . $endCode;
}

/**
 * Get the order details
 *
 * @global <type> $last_order
 * @param mixed $goal
 * @return string Code for the eCommerce tracking
 */
function getOrderDetailsPiwik($goal) {
  global $last_order; // from checkout_success.php

  $query = xtc_db_query("-- function.piwik.php
    SELECT value
    FROM " . TABLE_ORDERS_TOTAL . "
    WHERE orders_id = '" . $last_order . "' AND class='ot_total'");
  $orders_total = xtc_db_fetch_array($query);

  return "_paq.push(['trackGoal', '" . $goal . "', '" . $orders_total['value'] . "' ]);\n";
}