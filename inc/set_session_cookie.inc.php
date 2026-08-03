<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function set_session_cookie($lifetime, $path, $domain, $secure = false, $httponly = false, $samesite = 'Lax') {

    $user_agent = '';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
      $user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    if ($samesite == 'Lax'
        && stripos($user_agent, 'safari') !== false
        && stripos($user_agent, 'chrome') === false
        )
    {
      // these old WebKit/Safari versions mishandle SameSite=None (and may
      // require Secure to accept it), so omit the attribute entirely instead
      $samesite = '';
    }

    if (!$secure && strcasecmp($samesite, 'None') === 0) {
      // browsers drop SameSite=None cookies that aren't also Secure
      $samesite = 'Lax';
    }

    $cookie_options = array (
      'lifetime' => $lifetime,
      'path' => $path,
      'domain' => $domain,
      'secure' => $secure,
      'httponly' => $httponly,
      'samesite' => $samesite
    );
    session_set_cookie_params($cookie_options);
  }
