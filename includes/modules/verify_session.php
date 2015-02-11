<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// verify the ssl_session_id if the feature is enabled
if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true)) {
  $ssl_session_id  = $_SERVER['SSL_SESSION_ID'];
  $ssl_session_id2 = getenv('SSL_SESSION_ID');
  $ssl_session_id  = ($ssl_session_id == $ssl_session_id2) ? $ssl_session_id : $ssl_session_id.';'.$ssl_session_id2;
  if (!isset($_SESSION['SSL_SESSION_ID'])) {
    $_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
  }
  if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
    xtc_session_recreate();
    xtc_session_destroy();
    if (defined('RUN_MODE_ADMIN')) {
      xtc_redirect(xtc_catalog_href_link('ssl_check.php'));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_SSL_CHECK));
    }
  }
}

// verify the browser user agent if the feature is enabled
if (SESSION_CHECK_USER_AGENT == 'True') {
  $http_user_agent  = strtolower($_SERVER['HTTP_USER_AGENT']);
  $http_user_agent2 = strtolower(getenv("HTTP_USER_AGENT"));
  $http_user_agent  = ($http_user_agent == $http_user_agent2) ? $http_user_agent : $http_user_agent.';'.$http_user_agent2;
  if (!isset ($_SESSION['SESSION_USER_AGENT'])) {
    $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
  } elseif ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
    xtc_session_recreate();
    xtc_session_destroy();
    if (defined('RUN_MODE_ADMIN')) {
      xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_LOGIN));
    }
  }
}

// verify the IP address if the feature is enabled
if (SESSION_CHECK_IP_ADDRESS == 'True') {
  $ip_address = xtc_get_ip_address();
  if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
    $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
  } elseif ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
    xtc_session_recreate();
    xtc_session_destroy();
    if (defined('RUN_MODE_ADMIN')) {
      xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_LOGIN));
    }
  }
}

// check session_id for valid signs and length
if (!(preg_match('/^[a-z0-9]{26}$/i', session_id()) 
      || preg_match('/^[a-z0-9]{32}$/i', session_id())
      || preg_match('/^[a-z0-9]{52}$/i', session_id()))) {
  xtc_session_recreate(); // Thanks to HHGAG ;-)
}