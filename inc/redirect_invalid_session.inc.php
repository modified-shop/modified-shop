<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function redirect_invalid_session() {
  $uri = $_SERVER['REQUEST_URI'];
  if (strpos($uri,'?') === false && strpos($uri,'&') !== false) {
      $uri = substr_replace($uri, '?', strpos($uri, '&'), 1);
  }
  $uri = parse_url($uri);
  $params = '';
  if (isset($uri['query'])) {
    $sid_keys = array('modsid','xtcsid');
    $uri['query'] = str_ireplace($sid_keys, $sid_keys, $uri['query']);
    parse_str($uri['query'],$params);
    foreach($sid_keys as $key) {
      if (isset($params[$key])) unset($params[$key]);
    }
    $params = http_build_query($params);
  }
  $location = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $uri['path'] . (xtc_not_null($params) ? '?' . $params : '');
  header("HTTP/1.0 301 Moved Permanently");
  header("Location: $location");
  exit();
}
?>