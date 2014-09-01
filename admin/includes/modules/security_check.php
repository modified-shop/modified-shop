<?php
/* --------------------------------------------------------------
   $Id: security_check.php 3561 2012-08-29 18:11:38Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (security_check.php,v 1.2 2003/08/23); www.nextcommerce.org
   (c) 2006 xt-commerce (security_check.php 1221 2005-09-20); www.xt-commerce.com
   (c) 2011 WEB-Shop Software http://www.webs.de/

   Released under the GNU General Public License
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once DIR_WS_INCLUDES.'file_permissions.php';

$warnings = array();

/*******************************************************************************
 ** check Database Version
 ******************************************************************************/
$query = xtc_db_query('select * from database_version');
while ($row = xtc_db_fetch_array($query)) {
  $db_version_check = $row['version'];
}
$check = array();
if ($db_version_check !== constant('DB_VERSION')) {
  $check[] = sprintf(ERROR_DB_VERSION_UPDATE_INFO, $db_version_check, constant('DB_VERSION'));
}

if (!empty($check)) {
  $warnings[] = ERROR_DB_VERSION_UPDATE.'<ul><li>'.implode('</li><li>',$check).'</li></ul>';
}

/*******************************************************************************
 ** check file permissions
 ******************************************************************************/
$check = array();
foreach($configFiles as $file) {
  if (is_writable($file)) {
    $check[] = $file;
  }
}
if (!empty($check)) {
  $warnings[] = '<p>'.TEXT_FILE_WARNING.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
}

/*******************************************************************************
 ** check folder permissions
 ******************************************************************************/

// writeable dirs
$check = array();
foreach($writeableDirs as $dir) {
  if (!is_writable($dir)) {
    $check[] = $dir;
  }
}
if (!empty($check)) {
  $warnings[] = TEXT_FOLDER_WARNING.'<ul><li>'.implode('</li><li>',$check).'</li></ul>';
}

/* //for further use
  // non writeable dirs
  $check = array();
  foreach($nonWriteableDirs as $dir) {
    if (is_writable($dir)) {
      $check[] = $dir;
    }
  }
  if (!empty($check)) {
    $warnings[] = TEXT_FOLDER_WARNING_IS_WRITEABLE.'<ul><li>'.implode('</li><li>',$check).'</li></ul>';
  }
*/ //for further use

/*******************************************************************************
 ** check for configured payment and shipping modules
 ******************************************************************************/
$query = xtc_db_query('-- security_check payment
                      select configuration_key, configuration_value
                      from '.TABLE_CONFIGURATION.'
                      where configuration_key in (\'MODULE_PAYMENT_INSTALLED\', \'MODULE_SHIPPING_INSTALLED\')');
while ($check = xtc_db_fetch_array($query)) {
  if ($check['configuration_value'] == '') {
    switch($check['configuration_key']) {
    case 'MODULE_PAYMENT_INSTALLED' :
      $warnings[] = '<p>'.TEXT_PAYMENT_ERROR.'</p>';
      break;
    case 'MODULE_SHIPPING_INSTALLED' :
      $warnings[] = '<p>'.TEXT_SHIPPING_ERROR.'</p>';
      break;
    }
  }

  //BOF - DokuMan - 2012-05-31 - show warning if PayPal payment module activated, but not configured for live mode yet
  if (strpos($check['configuration_value'], 'paypal') !== false 
  && defined('PAYPAL_API_USER') && PAYPAL_API_USER == '') {
    $warnings[] = '<p>'.sprintf(TEXT_PAYPAL_CONFIG,xtc_href_link(FILENAME_CONFIGURATION, 'gID=111125')).'</p>';
  }
  //EOF - DokuMan - 2012-05-31 - show warning if PayPal payment module activated, but not configured for live mode yet
}

/*******************************************************************************
 ** Email adress check:
 ******************************************************************************/
$check = array();
$emails = array('STORE_OWNER_EMAIL_ADDRESS',
                'EMAIL_BILLING_ADDRESS',
                'EMAIL_BILLING_REPLY_ADDRESS',
                'CONTACT_US_EMAIL_ADDRESS',
                'EMAIL_SUPPORT_ADDRESS'
);
foreach($emails as $name) {
  $email = constant($name);
  if (empty($email) or !xtc_validate_email($email)){
    include(DIR_FS_LANGUAGES .$_SESSION['language'] . '/admin/configuration.php');
    $checks[] = sprintf(ERROR_EMAIL_CHECK_INFO,constant($name.'_TITLE'), $email);
  }
}
if (!empty($check)) {
  $warnings[] = ERROR_EMAIL_CHECK.'<ul><li>'.implode('</li><li>', $check).'</li></ul>';
}

/** ----------------------------------------------------------------------------
 ** Check for enabled FILE options on MySQL database - possible injection
 ** ------------------------------------------------------------------------- */
/* //for further use
$sql = '-- admin/includes/modules/security_check FILE perms
  show grants';
$stmt = xtc_db_query($sql);
while ($row = xtc_db_fetch_array($stmt)) {
  $key = key($row);
  if (strpos($row[$key], 'ALL PRIVILEGES') !== false or
      strpos($row[$key], 'FILE') !== false and
      strpos($row[$key], 'FILE') < strpos($row[$key], ' TO ')) {
    $warnings[] = WARNING_DB_FILE_PRIVILEGES;
    break;
  }
  // we are only interested in the user privileges - not for the DB
  break;
}
*/

/*******************************************************************************
 ** register_globals = off check:
 ******************************************************************************/
$registerGlobals = ini_get('register_globals');
// see notes for boolean values: http://php.net/manual/en/function.ini-get.php
if (($registerGlobals == '1') || (strtolower($registerGlobals) == 'on')) {
  $warnings[] = WARNING_REGISTER_GLOBALS;
}

/*******************************************************************************
 ** output warnings:
 ******************************************************************************/
if (!empty($warnings)) {
?>
<div id="security_info">
  <div style="float: left; margin-right: 10px;"><?php echo xtc_image(DIR_WS_ICONS.'big_warning.gif', 'Warning', 106, 93); ?></div>
  <div style="float: left;"><?php echo implode('', $warnings) ?></div>
  <div style="clear: both"></div>
</div>
<?php
}
?>