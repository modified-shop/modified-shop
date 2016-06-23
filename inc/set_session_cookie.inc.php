<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function set_session_cookie($lifetime, $path, $domain, $secure = false, $httponly = false) {
    if (function_exists('session_set_cookie_params')) {
      session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
    } elseif (function_exists('ini_set')) {
      ini_set('session.cookie_lifetime', $lifetime);
      ini_set('session.cookie_path', $path);
      ini_set('session.cookie_domain', $domain);
      ini_set('session.cookie_secure', $secure);
      ini_set('session.cookie_httponly', $httponly);
    }
  }
?>