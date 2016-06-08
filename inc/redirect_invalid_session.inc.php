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
  $uri = preg_replace("/([^\?]*)(\?.*)/", "$1", $_SERVER['REQUEST_URI']);
  $params = str_replace($uri, '', $_SERVER['REQUEST_URI']);
  $params = ltrim($params, '?');
  parse_str($params,$params);
  $key = xtc_session_name();
  if (isset($params[$key])) unset($params[$key]);
  $key = 'XTCsid';
  if (isset($params[$key])) unset($params[$key]);
  $params = http_build_query($params);
  $location = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $uri . (xtc_not_null($params) ? '?' . $params : '');
  header("HTTP/1.0 301 Moved Permanently");
  header("Location: $location");
  exit();
}
?>