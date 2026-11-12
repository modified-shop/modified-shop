<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// verify the browser user agent if the feature is enabled
if (SESSION_CHECK_USER_AGENT == 'True') {
  $http_user_agent  = strtolower($_SERVER['HTTP_USER_AGENT']);
  $http_user_agent2 = strtolower(getenv("HTTP_USER_AGENT"));
  $http_user_agent  = ($http_user_agent == $http_user_agent2) ? $http_user_agent : $http_user_agent.';'.$http_user_agent2;
  if (!isset ($_SESSION['SESSION_USER_AGENT'])) {
    $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
  } elseif ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
    // a session id handed over in an ajax url, e.g. by PayPal, must not end the customer's session
    if (!empty($ajax_session_in_url)) {
      session_abort();
      http_response_code(403);
      exit();
    }
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
    if (!empty($ajax_session_in_url)) {
      session_abort();
      http_response_code(403);
      exit();
    }
    xtc_session_recreate();
    xtc_session_destroy();
    if (defined('RUN_MODE_ADMIN')) {
      xtc_redirect(xtc_catalog_href_link(FILENAME_LOGIN));
    } else {
      xtc_redirect(xtc_href_link(FILENAME_LOGIN));
    }
  }
}

// bind admin sessions to the client network, a taken over admin session id is useless elsewhere
// payment providers call ajax.php with the session id from their own servers, ajax.php grants no admin rights
if (defined('SESSION_CHECK_ADMIN_IP_ADDRESS')
    && SESSION_CHECK_ADMIN_IP_ADDRESS == 'True'
    && empty($ajax_session_in_url)
    // the real status, in the storefront customers_status_id holds the admin's DEFAULT_CUSTOMERS_STATUS_ID_ADMIN group
    && isset($_SESSION['customers_status']['customers_status'])
    && $_SESSION['customers_status']['customers_status'] == '0'
    )
{
  $admin_ip_network = xtc_get_ip_network(xtc_get_ip_address());
  if (!isset($_SESSION['SESSION_ADMIN_IP_NETWORK'])) {
    $_SESSION['SESSION_ADMIN_IP_NETWORK'] = $admin_ip_network;
  } elseif ($_SESSION['SESSION_ADMIN_IP_NETWORK'] !== $admin_ip_network) {
    if (isset($LoggingManager)) {
      $LoggingManager->warning('Admin session of customer ' . (isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0) . ' ended, the client network changed from "' . $_SESSION['SESSION_ADMIN_IP_NETWORK'] . '" to "' . $admin_ip_network . '"');
    }
    // destroy the taken over session directly, the client starts a new one at the login
    xtc_session_destroy();
    $_SESSION = array();
    // in the admin the access check redirects the emptied session to the login
    if (!defined('RUN_MODE_ADMIN')) {
      xtc_redirect(xtc_href_link(FILENAME_LOGIN));
    }
  }
}

// check session_id for valid signs and length
if ($session_started === true
    && !preg_match('/^[a-z0-9]{26}$/i', xtc_session_id()) 
    && !preg_match('/^[a-z0-9]{32}$/i', xtc_session_id())
    && !preg_match('/^[a-z0-9]{40}$/i', xtc_session_id())
    && !preg_match('/^[a-z0-9]{52}$/i', xtc_session_id())
    )
{
  xtc_session_recreate();
}