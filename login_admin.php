<?php
/* -----------------------------------------------------------------------------------------
   $Id: login_admin.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2008 Gambio OHG - login_admin.php 2008-08-10 gambio - http://www.gambio.de

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  // USAGE: /login_admin.php?repair=seo_friendly
  // USAGE: /login_admin.php?repair=sess_write
  // USAGE: /login_admin.php?repair=sess_default
  // USAGE: /login_admin.php?repair=default_template
  // USAGE: /login_admin.php?repair=gzip_off

  // USAGE: /login_admin.php?show_error=none
  // USAGE: /login_admin.php?show_error=all
  // USAGE: /login_admin.php?show_error=shop
  // USAGE: /login_admin.php?show_error=admin

  // further documentation, see also:
  // http://www.modified-shop.org/wiki/Login_in_den_Administrationsbereich_nach_%C3%84nderungen_nicht_mehr_m%C3%B6glich

$error = false;

//allowed repair options
$allwowed_repair_array = array('seo_friendly','sess_write','sess_default','default_template','gzip_off');

if (isset($_GET['repair']) && !empty($_GET['repair']) && !in_array($_GET['repair'],$allwowed_repair_array)) {
  $error = true;
}
if (isset($_POST['repair']) && !empty($_POST['repair']) && !in_array($_POST['repair'],$allwowed_repair_array)) {
  $error = true;
}
//show_error
$allowed_show_error_array = array('none','shop','admin','all');
if (isset($_GET['show_error']) && !empty($_GET['show_error']) && !in_array($_GET['show_error'],$allowed_show_error_array)) {
  $error = true;
}
if (isset($_POST['show_error']) && !empty($_POST['show_error']) && !in_array($_POST['show_error'],$allowed_show_error_array)) {
  $error = true;
}
//parameter error
if ($error) {
  unset($_GET['repair']);
  unset($_GET['show_error']);
  unset($_POST['repair']);
  unset($_POST['show_error']);
}

//set default form action
if(isset($_GET['repair']) || isset($_GET['show_error'])) {
  $action = 'login_admin.php';
} else {
  $action = 'login.php?action=process';
}

if(isset($_POST['repair'])  || isset($_POST['show_error'])) {

  // loading only necessary functions
  // Set the local configuration parameters - mainly for developers or the main-configure
  if (file_exists('includes/local/configure.php')) {
    include('includes/local/configure.php');
  } else {
    require('includes/configure.php');
  }

  // list of project database tables
  require (DIR_WS_INCLUDES.'database_tables.php');

  // Database
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
  
  require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_input.inc.php');
  require_once(DIR_FS_INC . 'xtc_validate_password.inc.php');
  require_once(DIR_WS_CLASSES.'class.inputfilter.php');

  xtc_db_connect() or die('Unable to connect to database server!');

  //$_POST security
  $InputFilter = new InputFilter();
  $_POST = $InputFilter->process($_POST);
  $_POST = $InputFilter->safeSQL($_POST);

  $check_customer_query = xtc_db_query('
                                       SELECT customers_id,
                                              customers_password,
                                              customers_email_address
                                         FROM '. TABLE_CUSTOMERS .'
                                        WHERE customers_email_address = "'. xtc_db_input($_POST['email_address']) .'"
                                          AND customers_status = 0');

  $check_customer = xtc_db_fetch_array($check_customer_query);
  if(!xtc_validate_password(xtc_db_input($_POST['password']),
                            $check_customer['customers_password'],
                            $check_customer['customers_id'])) {
    die('Zugriff verweigert. E-Mail und/oder Passwort falsch!');
  } else {
    if (isset($_POST['repair']) && xtc_not_null($_POST['repair'])) {
      //repair options
      switch($_POST['repair']) {

        // turn off SEO friendy URLs
        case 'seo_friendly':
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "false"
            WHERE  configuration_key   = "SEARCH_ENGINE_FRIENDLY_URLS"
          ');
          die('Report: Die Einstellung "Suchmaschinenfreundliche URLs verwenden" wurde deaktiviert.');
          break;

        // reset session write directory
        case 'sess_write':
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "'.DIR_FS_CATALOG.'cache"
            WHERE  configuration_key   = "SESSION_WRITE_DIRECTORY"
          ');
          die('Report: SESSION_WRITE_DIRECTORY wurde auf das Cache-Verzeichnis zur&uuml;ckgesetzt.');
          break;

        // reset session behaviour to default values
        case 'sess_default':
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "False"
            WHERE  configuration_key   = "SESSION_FORCE_COOKIE_USE"
          ');
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "False"
            WHERE  configuration_key   = "SESSION_CHECK_SSL_SESSION_ID"
          ');
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "False"
            WHERE  configuration_key   = "SESSION_CHECK_USER_AGENT"
          ');
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "False"
            WHERE  configuration_key   = "SESSION_CHECK_IP_ADDRESS"
          ');
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "False"
            WHERE  configuration_key   = "SESSION_RECREATE"
          ');
          die('Report: Die Session-Einstellungen wurden auf die Standardwerte zur&uuml;ckgesetzt.');
          break;

        // set template to default template
        case 'default_template':
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "xtc5"
            WHERE  configuration_key = "CURRENT_TEMPLATE"
          ');
          die('Report: CURRENT_TEMPLATE wurde auf das Standardtemplate zur&uuml;ckgesetzt.');
          break;

        // turn off GZIP compression
        case 'gzip_off':
          xtc_db_query('
            UPDATE configuration
            SET    configuration_value = "false"
            WHERE  configuration_key = "GZIP_COMPRESSION"
          ');
          die('Report: GZIP_COMPRESSION wurde deaktiviert.');
          break;

        // unknown repair option
        default:
          die('Report: repair-Befehl ung&uuml;ltig.');
      }
    }
    //error_reporting
    if (isset($_POST['show_error']) && xtc_not_null($_POST['show_error'])) {

      $error_type = DIR_FS_DOCUMENT_ROOT . 'export/_error_reporting.' . $_POST['show_error'];
      $filenames = scandir(DIR_FS_DOCUMENT_ROOT . 'export/');
      foreach ($filenames as $filename) {
        if (strpos($filename, '_error_reporting')!== false) {
          $actual_reporting = $filename;
        }
      }
      if ($actual_reporting) {
        rename(DIR_FS_DOCUMENT_ROOT . 'export/'.$actual_reporting, $error_type);
        die('Report: error_reporting wurde ge&auml;ndert auf: '. $_POST['show_error']);
      } else {
        $errorHandle = fopen($error_type, 'w') or die('Report: error_reporting kann nicht ver&auml;ndert werden. ('. $_POST['show_error'].')');
        fclose($errorHandle);
        die('Report: error_reporting wurde ge&auml;ndert auf: '. $_POST['show_error']);
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Administator-Login</title>
<meta name="robots" content="noindex, nofollow, noodp" />
<link rel="stylesheet" href="templates/tpl_modified/stylesheet.css" type="text/css" />
</head>

<body>
<div id="layout_adminlogin" class="cf">
  <a class="help_adminlogin" href="http://www.modified-shop.org/wiki/Login_in_den_Administrationsbereich_nach_%C3%84nderungen_nicht_mehr_m%C3%B6glich" target="_blank"><img src="images/icons/question.png" width="32" height="32" title="Eingabehilfe und Repataturoptionen" /></a>
  <form name="login" method="post" action="<?php echo $action; ?>">
    <h1>Administrator-Login</h1>
    <table>
      <tr>
        <td><span class="fieldtext">E-Mail</span><input type="text" name="email_address" maxlength="50" /></td>
      </tr>  
      <tr>
        <td><span class="fieldtext">Passwort</span><input type="password" name="password" maxlength="30" /></td>
      </tr>  
    </table>  
    <input type="submit" class="login" name="Submit" value="Anmelden" />
    <?php
    if (isset($_GET['repair']) && $_GET['repair']!='') {
      echo '<input type="hidden" name="repair" value="'. $_GET['repair'] .'" />';
    } elseif (isset($_GET['show_error']) && $_GET['show_error']!='') {
      echo '<input type="hidden" name="show_error" value="'. $_GET['show_error'] .'" />';
    }
    ?>
  </form>
</div>
</body>
</html>