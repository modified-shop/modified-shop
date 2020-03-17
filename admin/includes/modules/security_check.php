<?php
/* --------------------------------------------------------------
   $Id$

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

require_once (DIR_FS_INC.'get_database_version.inc.php');
require_once (DIR_WS_INCLUDES.'file_permissions.php');

$check = array();
$warnings = array();

/*******************************************************************************
 ** check Database Version
 ******************************************************************************/
$db_version_check = get_database_version();
if ($db_version_check['full'] !== constant('DB_VERSION')) {
  $check[] = sprintf(ERROR_DB_VERSION_UPDATE_INFO, $db_version_check['full'], constant('DB_VERSION'));
}

if (!empty($check)) {
  $warnings[] = ERROR_DB_VERSION_UPDATE.'<ul><li>'.implode('</li><li>',$check).'</li></ul>';
}

/*******************************************************************************
 ** check file permissions
 ******************************************************************************/
if (WARN_CONFIG_WRITEABLE == 'true') {
  $check = array();
  foreach($configFiles as $file) {
    if (is_file($file) && is_writable($file)) {
      $check[] = $file;
    }
  }
  if (!empty($check)) {
    $warnings[] = '<p>'.TEXT_FILE_WARNING_WRITABLE.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
  }
}

if (WARN_FILES_WRITEABLE == 'true') {
  $check = array();
  foreach($writeableFiles as $file) {
    if (is_file($file) && !is_writable($file)) {
      $check[] = $file;
    }
  }
  if (!empty($check)) {
    $warnings[] = '<p>'.TEXT_FILE_WARNING.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
  }
}

if(defined('MODULE_JANOLAW_STATUS') && MODULE_JANOLAW_STATUS == 'True' && defined('MODULE_JANOLAW_TYPE') && MODULE_JANOLAW_TYPE == 'File') {
  $check = array();
  foreach($writeableJanolawFiles as $file) {
    if (is_file($file) && !is_writable($file)) {
      $check[] = $file;
    }
  }
  if (!empty($check)) {
    $warnings[] = '<p>'.TEXT_FILE_WARNING.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
  }
}

/*******************************************************************************
 ** check folder permissions
 ******************************************************************************/

// writeable dirs - only check if dir exists
if (WARN_DIRS_WRITEABLE == 'true') {
  $check = array();
  foreach($writeableDirs as $dir) {
    if (is_dir($dir) && !is_writable($dir)) {
      $check[] = $dir;
    }
  }
  if (!empty($check)) {
    $warnings[] = '<p>'.TEXT_FOLDER_WARNING.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
  }
}

/* //for further use
  // non writeable dirs
  $check = array();
  foreach($nonWriteableDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
      $check[] = $dir;
    }
  }
  if (!empty($check)) {
    $warnings[] = '<p>'.TEXT_FOLDER_WARNING_IS_WRITEABLE.'</p><ul><li>'.implode('</li><li>',$check).'</li></ul>';
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
      $warnings[] = '<p>'.sprintf(TEXT_PAYMENT_ERROR,xtc_href_link(FILENAME_MODULES, 'set=payment')).'</p>';
      break;
    case 'MODULE_SHIPPING_INSTALLED' :
      $warnings[] = '<p>'.sprintf(TEXT_SHIPPING_ERROR,xtc_href_link(FILENAME_MODULES, 'set=shipping')).'</p>';
      break;
    }
  }
}

/*******************************************************************************
 ** Email adress check:
 ******************************************************************************/
require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');
$lang_check = xtc_get_languages();

$check = array();
$emails = array('STORE_OWNER_EMAIL_ADDRESS',
                'EMAIL_BILLING_ADDRESS',
                'EMAIL_BILLING_REPLY_ADDRESS',
                'CONTACT_US_EMAIL_ADDRESS',
                'EMAIL_SUPPORT_ADDRESS'
);
foreach($emails as $name) {
  $email = constant($name);
  for ($i=0, $n=count($lang_check); $i<$n; $i++) {
    $email = parse_multi_language_value($email, $lang_check[$i]['code']);
    if (empty($email) or !xtc_validate_email($email)){
      include_once(DIR_FS_LANGUAGES .$_SESSION['language'] . '/admin/configuration.php');
      $checks[] = sprintf(ERROR_EMAIL_CHECK_INFO,constant($name.'_TITLE'), $email);
    }
  }
}
if (!empty($checks)) {
  $warnings[] = ERROR_EMAIL_CHECK.'<ul><li>'.implode('</li><li>', $checks).'</li></ul>';
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
 ** umlaut domain
 ******************************************************************************/
if (filter_var(HTTP_SERVER, FILTER_VALIDATE_URL) === false
    || filter_var(HTTPS_SERVER, FILTER_VALIDATE_URL) === false
    )
{
  $warnings[] = WARNING_DOMAIN_INVALID;
}

/*******************************************************************************
 ** register_globals = off check:
 ******************************************************************************/
$registerGlobals = ini_get('register_globals');
// see notes for boolean values: http://php.net/manual/en/function.ini-get.php
if (($registerGlobals == '1') || (strtolower($registerGlobals) == 'on')) {
  $warnings[] = WARNING_REGISTER_GLOBALS;
}

/*******************************************************************************
 ** duplicate configuration check:
 ******************************************************************************/
if (isset($duplicate_configuration) 
    && count($duplicate_configuration) > 0 
    && isset($_POST['action']) 
    && $_POST['action'] == 'delete_duplicate_configuration'
    ) 
{
  xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION."
                      USING configuration, configuration as duplicate
                  WHERE NOT configuration.configuration_id = duplicate.configuration_id
                        AND configuration.configuration_id > duplicate.configuration_id
                        AND configuration.configuration_key = duplicate.configuration_key");
  
  unset($duplicate_configuration);
}

if (isset($duplicate_configuration) && count($duplicate_configuration) > 0) {
  foreach ($duplicate_configuration as $key) {
    $warnings[] = TEXT_DUPLICATE_CONFIG_ERROR.$key.'<br/>';
  }
  $warnings[] = xtc_draw_form('configuration', basename($PHP_SELF)).xtc_draw_hidden_field('action', 'delete_duplicate_configuration').'<input class="button" type="submit" value="'.BUTTON_DELETE.'"/></form>';
} else {
  $check_unique = xtc_db_query("SHOW INDEX FROM ".TABLE_CONFIGURATION." WHERE key_name = 'idx_configuration_key'");
  if (xtc_db_num_rows($check_unique) < 1) {
    xtc_db_query("ALTER TABLE ".TABLE_CONFIGURATION." ADD UNIQUE idx_configuration_key (configuration_key)");
  }
}

/*******************************************************************************
 ** newsfeed check:
 ******************************************************************************/
if (isset($_POST['action']) 
    && $_POST['action'] == 'delete_newsfeed'
    ) 
{
  $time = time();
  xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".$time."' WHERE configuration_key = 'NEWSFEED_LAST_UPDATE'");
  xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".$time."' WHERE configuration_key = 'NEWSFEED_LAST_UPDATE_TRY'");

} elseif (defined('NEWSFEED_LAST_UPDATE_TRY')
          && abs((int)NEWSFEED_LAST_UPDATE_TRY - (int)NEWSFEED_LAST_UPDATE) >= 3600
          && time() - (int)NEWSFEED_LAST_UPDATE > 86400
          )
{
  $warnings[] = RSS_FEED_NOT_REACHABLE;
  $warnings[] = xtc_draw_form('configuration', basename($PHP_SELF)).xtc_draw_hidden_field('action', 'delete_newsfeed').'<input class="button" type="submit" value="OK"/></form>';
}

/*******************************************************************************
 ** output warnings:
 ******************************************************************************/
if (!empty($warnings)) {
?>
<div id="security_info" style="margin:0 5px 6px">
  <div style="float: left; width: 125px;"><?php echo xtc_image(DIR_WS_ICONS.'big_warning.png', ICON_WARNING, 106, 93); ?></div>
  <div style="float: left; width: 85%;"><?php echo implode('', $warnings) ?></div>
  <div style="clear: both"></div>
</div>
<?php
}
?>