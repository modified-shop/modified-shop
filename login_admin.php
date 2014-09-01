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
  require_once(DIR_WS_INCLUDES . 'database_tables.php');
  require_once(DIR_FS_INC . 'xtc_db_connect.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_close.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_error.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_query.inc.php');
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
                            $check_customer['customers_email_address'])) {
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Administator-Login</title>
<meta http-equiv="content-language" content="de" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="robots" content="noindex, nofollow" />
<style type="text/css">
html {
  height: 100%;
  background: #fff;
  background: -webkit-gradient(linear, left top, left bottom, from(#ededed), to(#ffffff));
  background: -moz-linear-gradient(top,  #ededed,  #ffffff);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#ffffff');
}
form {
  background:#f0f0f0;
  border:1px #fff solid;
  width:300px;
  height:190px;
  margin:60px auto 0;
  padding: 0 15px;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  border-radius: 4px;
  -webkit-box-shadow: 0 1px 2px rgba(0,0,0,1.2);
  -moz-box-shadow: 0 1px 2px rgba(0,0,0,1.2);
  box-shadow: 0 1px 2px rgba(0,0,0,1.2);
}
form h1 {
  font-size: 16px;
  width: 300px;
  margin: 12px auto 0;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-weight:500;
  letter-spacing: 3px;
  border-bottom: 2px dotted #AF417E;
  text-indent: 10px;
}
form p {
  width: 280px;
  margin: 10px auto;
}
form i {
  width: 80px;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  text-shadow: 0 1px 1px rgba(0,0,0,.3);
  font-size: 13px;
  letter-spacing: 3px;
  display:block;
}
form a {
  float:right;
  margin: 10px;
}
form img {
  border: none;
}
input[type=text], input[type=password] {
  width: 220px;
  background: #dfdfdf;
  letter-spacing:1px;
  padding:2px 5px;
}
input[type=text]:focus, input[type=password]:focus {
  background: #f5f5f5;
}
.login {
  outline: none;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  float:right;
  font: 14px/100% Arial, Helvetica, sans-serif;
  padding: .1em 2em .15em;
  text-shadow: 0 1px 1px rgba(0,0,0,.3);
  -webkit-border-radius: .2em;
  -moz-border-radius: .2em;
  border-radius: .2em;
  -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.2);
  -moz-box-shadow: 0 1px 2px rgba(0,0,0,.2);
  box-shadow: 0 1px 2px rgba(0,0,0,.2);
  color: #606060;
  border: solid 1px #b7b7b7;
  background: #fff;
  background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#ededed));
  background: -moz-linear-gradient(top,  #fff,  #ededed);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#ededed');
}
.login:hover {
  text-decoration: none;
  background: #ededed;
  background: -webkit-gradient(linear, left top, left bottom, from(#fff), to(#dcdcdc));
  background: -moz-linear-gradient(top,  #fff,  #dcdcdc);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#dcdcdc');
}
.login:active {
  position: relative;
  top: 1px;
  color: #999;
  background: -webkit-gradient(linear, left top, left bottom, from(#ededed), to(#fff));
  background: -moz-linear-gradient(top,  #ededed,  #fff);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#ffffff');
}
</style>
</head>
<body>
<form name="login" method="post" action="<?php echo $action; ?>">
  <h1>Administrator-Login</h1>
  <a href="http://www.modified-shop.org/wiki/Login_in_den_Administrationsbereich_nach_%C3%84nderungen_nicht_mehr_m%C3%B6glich" target="_blank"><img src="images/icons/question.png" width="32" height="32" title="Eingabehilfe und Repataturoptionen" /></a>
  <p><i>E-Mail</i>
    <input type="text" name="email_address" maxlength="50" />
  </p>
  <p><i>Passwort</i>
    <input type="password" name="password" maxlength="30" />
  </p>
  <p>
    <input type="submit" class="login" name="Submit" value="Anmelden" />
    <?php
    if (isset($_GET['repair']) && $_GET['repair']!='') {
      echo '<input type="hidden" name="repair" value="'. $_GET['repair'] .'" />';
    } elseif (isset($_GET['show_error']) && $_GET['show_error']!='') {
      echo '<input type="hidden" name="show_error" value="'. $_GET['show_error'] .'" />';
    }
    ?>
  </p>
</form>
</body>
</html>