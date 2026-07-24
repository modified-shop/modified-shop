<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// use always session_id from URL for OAuth providers
define('SESSION_FORCE_COOKIE_USE', 'False');

$oauth_state = isset($_GET['state']) ? (string)$_GET['state'] : '';
if (preg_match(
    '/^[a-z0-9]{32,64}\.([a-z0-9]{26}|[a-z0-9]{32}|[a-z0-9]{40}|[a-z0-9]{52})$/i',
    $oauth_state,
    $matches
    ))
{
  $_GET['MODsid'] = $matches[1];
  $_REQUEST['MODsid'] = $matches[1];
}

chdir('../../');
require_once('includes/application_top_callback.php');

$module = isset($_GET['module']) ? $_GET['module'] : '';

if (!preg_match('/^[a-z0-9_]+$/', $module)
    || !is_file(DIR_FS_CATALOG . DIR_ADMIN . 'includes/modules/system/' . $module . '.php')
    )
{
  http_response_code(400);
  exit('Invalid OAuth callback request.');
}

$oauth_callback = isset($_SESSION['xoauth_callback']) && is_array($_SESSION['xoauth_callback'])
  ? $_SESSION['xoauth_callback']
  : array();

if (!isset($oauth_callback['module'], $oauth_callback['state'], $_GET['state'])
    || !hash_equals((string)$oauth_callback['module'], (string)$module)
    || !hash_equals((string)$oauth_callback['state'], (string)$_GET['state'])
    )
{
  unset($_SESSION['xoauth_callback'], $_SESSION['xoauth_callback_response']);
  http_response_code(400);
  exit('Invalid OAuth callback state.');
}

unset($_SESSION['xoauth_callback']);

$_SESSION['xoauth_callback_response'] = array(
  'module' => $module,
  'state' => (string)$_GET['state'],
);

if (isset($_GET['code']) && $_GET['code'] != '') {
  $_SESSION['xoauth_callback_response']['code'] = (string)$_GET['code'];
}
if (isset($_GET['error']) && $_GET['error'] != '') {
  $_SESSION['xoauth_callback_response']['error'] = (string)$_GET['error'];
}

$parameters = array(
  'set' => 'system',
  'action' => 'custom',
  'module' => $module,
  'oauth_callback' => '1',
);

xtc_redirect(xtc_href_link_admin(DIR_ADMIN . 'module_export.php', http_build_query($parameters), 'SSL'));
