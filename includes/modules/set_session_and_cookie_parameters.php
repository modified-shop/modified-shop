<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

@ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// set the session name and save path
xtc_session_name('MODsid');
if (STORE_SESSIONS != 'mysql') {
  xtc_session_save_path(SESSION_WRITE_DIRECTORY);
}

// set the session cookie parameters
if (function_exists('session_set_cookie_params')) {
  session_set_cookie_params(0, DIR_WS_CATALOG, (xtc_not_null($current_domain) ? '.'.$current_domain : ''));
} elseif (function_exists('ini_set')) {
  ini_set('session.cookie_lifetime', '0');
  ini_set('session.cookie_path', DIR_WS_CATALOG);
  ini_set('session.cookie_domain', (xtc_not_null($current_domain) ? '.'.$current_domain : ''));
}

// set the session ID if it exists
if (isset ($_POST[xtc_session_name()])) {
  xtc_session_id($_POST[xtc_session_name()]);
}
elseif ($request_type == 'SSL' && isset ($_GET[xtc_session_name()])) {
  xtc_session_id($_GET[xtc_session_name()]);
}

// start the session
$session_started = false;
$truncate_session_id = false;
if (SESSION_FORCE_COOKIE_USE == 'True') {
  xtc_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, DIR_WS_CATALOG, (xtc_not_null($current_domain) ? $current_domain : ''));
  if (isset($_COOKIE['cookie_test'])) {
    $session_started = xtc_session_start();
  }
} elseif (CHECK_CLIENT_AGENT == 'true' && xtc_check_agent() == 1) {
  $truncate_session_id = true;
  $session_started = false;
  // Redirect search engines with session id to the same url without session id to prevent indexing session id urls
  if (strpos($_SERVER['REQUEST_URI'], xtc_session_name()) !== false || preg_match('/XTCsid/i', $_SERVER['REQUEST_URI'])) {
    $location = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(), 'NONSSL', false);
    header("HTTP/1.0 301 Moved Permanently");
    header("Location: $location");
    exit();
  }
} else {
  $session_started = xtc_session_start();
}

// check for Cookie usage
$cookie = false;
if (isset($_COOKIE[xtc_session_name()])) {

  // Reset the old/deprecated cookie
  if ($current_domain != $current_domain_old) {
    xtc_setcookie(xtc_session_name(), $_COOKIE[xtc_session_name()], (time() - 3600), '/', (xtc_not_null($current_domain_old) ? '.'.$current_domain_old : ''));
  }

  if ($http_domain == $https_domain || ENABLE_SSL === false) {
    $cookie = true;
  }
}