<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

// Set the local configuration parameters - mainly for developers or the main-configure

defined( '_MODIFIED_SHOP_LOGIN' ) or die( 'Direct Access to this location is not allowed.' );
if (file_exists('includes/local/configure.php')) {
  include('includes/local/configure.php');
} else {
  require('includes/configure.php');
}

@ini_set('display_errors', false);
error_reporting(0);

// loading only necessary functions
require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
require_once(DIR_FS_INC . 'xtc_draw_password_field.inc.php');
require_once(DIR_FS_INC . 'xtc_draw_input_field.inc.php');
require_once(DIR_FS_INC . 'xtc_parse_input_field_data.inc.php');
require_once(DIR_FS_INC . 'xtc_redirect.inc.php');


$error = false;

//allowed repair options
$allwowed_repair_array = array('seo_friendly','sess_write','sess_default','default_template','gzip_off','reset_login');

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
if ($error === true) {
  unset($_GET['repair']);
  unset($_GET['show_error']);
  unset($_POST['repair']);
  unset($_POST['show_error']);
}

if(isset($_POST['repair'])  || isset($_POST['show_error'])) {

  // list of project database tables
  require (DIR_WS_INCLUDES.'database_tables.php');

  // Database
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
  
  require_once (DIR_FS_INC.'html_encoding.php');
  require_once (DIR_FS_INC.'xtc_not_null.inc.php');
  require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
  require_once (DIR_FS_INC.'xtc_get_ip_address.inc.php');

  require_once (DIR_WS_CLASSES.'class.inputfilter.php');

  xtc_db_connect() or die('Unable to connect to database server!');

  //$_POST security
  $InputFilter = new InputFilter();
  $_POST = $InputFilter->process($_POST);
  $_POST = $InputFilter->safeSQL($_POST);
  
  $ip_address = xtc_get_ip_address();
  
  // brute force
  $check_login_query = xtc_db_query("SELECT MAX(customers_login_tries) as login_tries
                                       FROM ".TABLE_CUSTOMERS_LOGIN."
                                      WHERE (customers_email_address = '".xtc_db_input($_POST['email_address'])."'
                                             OR customers_ip = '".xtc_db_input($ip_address)."')");
  $check_login = xtc_db_fetch_array($check_login_query);
  if ($check_login['login_tries'] > 0) {
    // update login tries
    xtc_db_query("UPDATE ".TABLE_CUSTOMERS_LOGIN." 
                     SET customers_login_tries = '".($check_login['login_tries'] + 1)."'
                   WHERE (customers_email_address = '".xtc_db_input($_POST['email_address'])."'
                          OR customers_ip = '".xtc_db_input($ip_address)."')");
    
    // wait before continue
    if ($check_login['login_tries'] > 10) {
      $check_login['login_tries'] = 10;
    }
    $wait = 1;
    for ($i=1; $i<=$check_login['login_tries']; $i++) {
      $wait *= $i;
    } 
    sleep($wait);
  } else {
    $sql_data_array = array(
      'customers_ip' => $ip_address,
      'customers_email_address' => $_POST['email_address'],
      'customers_login_tries' => ($check_login['login_tries'] + 1),
    );
    xtc_db_perform(TABLE_CUSTOMERS_LOGIN, $sql_data_array);
  }

  $check_customer_query = xtc_db_query("SELECT customers_id,
                                               customers_password,
                                               customers_email_address
                                          FROM ". TABLE_CUSTOMERS ."
                                         WHERE customers_email_address = '". xtc_db_input($_POST['email_address']) ."'
                                           AND customers_status = '0'");

  $check_customer = xtc_db_fetch_array($check_customer_query);
  if (!xtc_validate_password(xtc_db_input($_POST['password']), $check_customer['customers_password'], $check_customer['customers_id'])) {
    die('Zugriff verweigert. E-Mail und/oder Passwort falsch!');
  } else {
    if (isset($_POST['repair']) && xtc_not_null($_POST['repair'])) {
      // reset login
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_LOGIN."  
                          WHERE customers_email_address = '".xtc_db_input($check_customer['customers_email_address'])."'");
      
      xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_LOGIN."  
                          WHERE customers_ip = '".xtc_db_input($ip_address)."'");
      
      //repair options
      switch($_POST['repair']) {

        // turn off SEO friendy URLs
        case 'seo_friendly':
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'false'
                         WHERE configuration_key = 'SEARCH_ENGINE_FRIENDLY_URLS'");
          die('Report: Die Einstellung "Suchmaschinenfreundliche URLs verwenden" wurde deaktiviert.');
          break;

        // reset session write directory
        case 'sess_write':
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = '".DIR_FS_CATALOG.'cache'."'
                         WHERE configuration_key = 'SESSION_WRITE_DIRECTORY'");
          die('Report: SESSION_WRITE_DIRECTORY wurde auf das Cache-Verzeichnis zur&uuml;ckgesetzt.');
          break;

        // reset session behaviour to default values
        case 'sess_default':
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'False'
                         WHERE configuration_key = 'SESSION_FORCE_COOKIE_USE'");
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'False'
                         WHERE configuration_key = 'SESSION_CHECK_SSL_SESSION_ID'");
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'False'
                         WHERE configuration_key = 'SESSION_CHECK_USER_AGENT'");
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'False'
                         WHERE configuration_key = 'SESSION_CHECK_IP_ADDRESS'");
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'False'
                         WHERE configuration_key = 'SESSION_RECREATE'");
          die('Report: Die Session-Einstellungen wurden auf die Standardwerte zur&uuml;ckgesetzt.');
          break;

        // set template to default template
        case 'default_template':
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'xtc5'
                         WHERE configuration_key = 'CURRENT_TEMPLATE'");
          die('Report: CURRENT_TEMPLATE wurde auf das Standardtemplate zur&uuml;ckgesetzt.');
          break;

        // turn off GZIP compression
        case 'gzip_off':
          xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                           SET configuration_value = 'false'
                         WHERE configuration_key = 'GZIP_COMPRESSION'");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<style type="text/css">
<!--

body {
  font-family: Tahoma, sans-serif;
  font-weight: normal;
  font-size:13px;
  background-color:#fff;
  color:#555;
  line-height:19px;
}
.clearfix, .clear, .clearer {
  line-height:0px;
  height:0px;
  clear:both;   
}
         
.cf:before, .cf:after { content: ""; display: table; }
.cf:after { clear: both; }
.cf { zoom: 1; }

h1 {
  font-family: Tahoma, sans-serif;
  color:#444;
  font-weight:normal;
  font-size:18px;
  margin:20px 0 15px 0;
  padding:0 0 5px;
  border:#ddd solid;
  border-width:0 0 1px 0;
}

.fieldtext, .fieldtext_stern {
  font-size:11px;
  line-height:15px;
  font-weight:bold;
  padding: 0px 0px 2px 0px;
  display:block;
}

input {
  font-family: Tahoma, sans-serif;
  font-size:13px;
}
input[type=text], input[type=password] {
  background-color:#fafafa;
  border-color: #C6C6C6 #DADADA #EAEAEA;
  color: #999999;
  border-style: solid;
  border-width: 1px;
  vertical-align: middle;
	padding: 6px 5px 6px 5px;
	-webkit-border-radius: 2px;
	-moz-border-radius: 2px;
	border-radius: 2px;
  -moz-box-sizing: border-box;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  width:100%;
  height:32px;
}
input[type=text]:hover, input[type=password]:hover {
    background-color:#FFFFFF;
    border-color: #C6C6C6 #DADADA #EAEAEA;
    color: #666666;
}    
input[type=text]:focus, input[type=password]:focus {
    background-color:#FFFFFF;
    border-color: #659EC9 #70AEDD #A8CFEC;
    color: #333333;
    outline: 0 none;
}
table {
  width:100%;
  border-spacing: 0;
  border-collapse:collapse;
}
table td {
  padding:4px 0px;
}

#layout_offline {
  width:80%;
  max-width:700px;
  margin:40px auto;
  padding:20px;
  border: 1px solid #ddd;
}
#layout_adminlogin {
  position:relative;
  margin: 50px auto;
  padding:15px;
  background:#fff;
  border:solid #eee 1px;
  -webkit-box-shadow: 0px 0px 15px #3d3d3d; 
  -moz-box-shadow: 0px 0px 15px #3d3d3d; 
  box-shadow: 0px 0px 15px #3d3d3d;
  max-width:400px;
}
#layout_adminlogin a.help_adminlogin {
  position:absolute;
  width:32px;
  height:32px;
  outline:none;
  top:10px;
  right:10px;  
  display:block;
}
#layout_adminlogin .login {
  float:right;
  margin: 10px 0 0 0;
  font-family: Tahoma, sans-serif;
  outline: none;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  font-size: 16px;
  padding: 2px 20px;
  -webkit-border-radius: 2px;
  -moz-border-radius: 2px;
  border-radius: 2px;
  color: #fff;
  border: solid 1px #101010;
  background: #3a3a3a;
  background: -webkit-gradient(linear, left top, left bottom, from(#494949), to(#242424));
  background: -moz-linear-gradient(top,  #494949,  #242424);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#494949', endColorstr='#242424');
}
#layout_adminlogin .login:hover {
  text-decoration: none;
  background: #3a3a3a;
  background: -webkit-gradient(linear, left top, left bottom, from(#242424), to(#494949));
  background: -moz-linear-gradient(top,  #242424,  #494949);
  filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#242424', endColorstr='#494949');
}
-->
</style>
</head>
<body>
  <div id="layout_adminlogin" class="cf">
    <a class="help_adminlogin" href="http://www.modified-shop.org/wiki/Login_in_den_Administrationsbereich_nach_%C3%84nderungen_nicht_mehr_m%C3%B6glich" target="_blank"><img src="images/icons/question.png" width="32" height="32" title="Eingabehilfe und Reparaturoptionen" /></a>
    <form name="login" method="post" action="<?php echo basename($PHP_SELF); ?>">
      <h1>Administrator-Login</h1>
      <table>
        <tr>
          <td><span class="fieldtext">E-Mail</span><input type="text" name="email_address" maxlength="50" /></td>
        </tr>  
        <tr>
          <td><span class="fieldtext">Passwort</span><?php echo xtc_draw_password_field('password'); ?></td>
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