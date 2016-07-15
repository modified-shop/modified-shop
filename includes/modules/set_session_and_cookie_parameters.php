<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed function
require_once(DIR_FS_INC.'set_session_cookie.inc.php');
require_once(DIR_FS_INC.'redirect_invalid_session.inc.php');

@ini_set('session.use_only_cookies', (SESSION_FORCE_COOKIE_USE == 'True') ? 1 : 0);

// set the session name and save path
xtc_session_name('MODsid');
if (STORE_SESSIONS != 'mysql') {
  xtc_session_save_path(SESSION_WRITE_DIRECTORY);
}

if (STORE_SESSIONS == 'mysql') {
  // check valid session_id
  if (isset($_GET[xtc_session_name()]) && $_GET[xtc_session_name()] != '') {
    $check_query = xtc_db_query("SELECT sesskey 
                                   FROM ".TABLE_SESSIONS." 
                                  WHERE sesskey = '".xtc_db_input(preg_replace('/[^0-9a-zA-Z]/', '', $_GET[xtc_session_name()]))."'");
    if (xtc_db_num_rows($check_query) < 1) {
      redirect_invalid_session();
    }
  }
  // delete old cookies
  if (isset($_COOKIE[xtc_session_name()])) {
    $check_query = xtc_db_query("SELECT expiry 
                                   FROM ".TABLE_SESSIONS." 
                                  WHERE sesskey = '".xtc_db_input(preg_replace('/[^0-9a-zA-Z]/', '', $_COOKIE[xtc_session_name()]))."'");
    $check = xtc_db_fetch_array($check_query);
    if (($check['expiry'] + (int)$SESS_LIFE) < time()) {
      $cookie_params = session_get_cookie_params();      
      xtc_setcookie(xtc_session_name(), '', time()-3600, '/', (xtc_not_null($current_domain_old) ? '.'.$current_domain_old : ''));
      xtc_setcookie(xtc_session_name(), '', time()-3600, DIR_WS_CATALOG, (xtc_not_null($current_domain_old) ? '.'.$current_domain_old : ''));
      xtc_setcookie(xtc_session_name(), '', time()-3600, DIR_WS_CATALOG, (xtc_not_null($current_domain) ? '.'.$current_domain : ''));
      xtc_setcookie(xtc_session_name(), '', time()-3600, $cookie_params['path'], $cookie_params['domain']);
    }
  }
}

// set the session cookie
set_session_cookie(0, DIR_WS_CATALOG, (xtc_not_null($current_domain) ? '.'.$current_domain : ''), true, ((HTTP_SERVER == HTTPS_SERVER && $request_type == 'SSL') ? true : false));

// set the session ID if it exists
if (isset($_POST[xtc_session_name()])) {
  xtc_session_id($_POST[xtc_session_name()]);
}
elseif ($request_type == 'SSL' && isset($_GET[xtc_session_name()])) {
  if (!isset($_COOKIE[xtc_session_name()]) || $_GET[xtc_session_name()] != $_COOKIE[xtc_session_name()]) {
    xtc_session_id($_GET[xtc_session_name()]);
  }
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
  if (stripos($_SERVER['REQUEST_URI'], xtc_session_name()) !== false || preg_match('/XTCsid/i', $_SERVER['REQUEST_URI'])) {
    redirect_invalid_session();
  }
} else {
  $session_started = xtc_session_start();
}

// check for Cookie usage
$cookie = false;
if (isset($_COOKIE[xtc_session_name()])) {
  if ($http_domain == $https_domain || ENABLE_SSL === false) {
    $cookie = true;
  }
}