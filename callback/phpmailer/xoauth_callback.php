<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

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
  unset($_SESSION['xoauth_callback']);
  http_response_code(400);
  exit('Invalid OAuth callback state.');
}

unset($_SESSION['xoauth_callback']);

$parameters = array(
  'set' => 'system',
  'action' => 'custom',
  'module' => $module,
);

if (isset($_GET['state'])) {
  $parameters['state'] = $_GET['state'];
}
if (isset($_GET['code'])) {
  $parameters['code'] = $_GET['code'];
}
if (isset($_GET['error'])) {
  $parameters['oauth_error'] = $_GET['error'];
}
if (isset($_GET['error_description'])) {
  $parameters['oauth_error_description'] = $_GET['error_description'];
}
if (isset($_GET['session_state'])) {
  $parameters['session_state'] = $_GET['session_state'];
}

xtc_redirect(xtc_href_link_admin(DIR_ADMIN . 'module_export.php', http_build_query($parameters), 'SSL', false));
