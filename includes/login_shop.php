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
   
defined( '_MODIFIED_SHOP_LOGIN' ) or die( 'Direct Access to this location is not allowed.' );

include ('includes/application_top.php');

defined('MODULE_CAPTCHA_LOGIN_NUM') OR define('MODULE_CAPTCHA_LOGIN_NUM', 2);
defined('MODULE_CAPTCHA_CODE_LENGTH') OR define('MODULE_CAPTCHA_CODE_LENGTH', 6);

// include needed classes
require_once (DIR_WS_CLASSES.'modified_captcha.php');

$mod_captcha = $_mod_captcha_class::getInstance();

if (!isset($_SESSION['customers_login_tries'])) {
  $_SESSION['customers_login_tries'] = 0;
}

if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/offline/login_shop.html')) {

  // create smarty elements
  $smarty = new Smarty;

  if (isset($_GET['info_message']) && xtc_not_null($_GET['info_message'])) {
    $messageStack->add('login', get_message('info_message'));
  }

  if ($messageStack->size('login') > 0) {
    $smarty->assign('info_message', $messageStack->output('login'));
  }

  $smarty->assign('FORM_ACTION', xtc_draw_form('login', xtc_href_link(FILENAME_LOGIN, xtc_get_all_get_params().'action=process', 'SSL')));
  $smarty->assign('INPUT_MAIL', xtc_draw_input_field('email_address'));
  $smarty->assign('INPUT_PASSWORD', xtc_draw_password_field('password'));
  $smarty->assign('LINK_LOST_PASSWORD', xtc_href_link(FILENAME_PASSWORD_DOUBLE_OPT, '', 'SSL'));
  $smarty->assign('FORM_END', '</form>');

  // captcha
  if ($_SESSION['customers_login_tries'] >= MODULE_CAPTCHA_LOGIN_NUM) {
    $smarty->assign('VVIMG', $mod_captcha->get_image_code());
    $smarty->assign('INPUT_CODE', $mod_captcha->get_input_code());
  }

  $smarty->assign('charset', $_SESSION['language_charset']);
  $smarty->assign('language', $_SESSION['language']);
  $smarty->caching = 0;

  $smarty->display(CURRENT_TEMPLATE.'/module/offline/login_shop.html');
  exit();
}

//Fallback for missing template file
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="<?php echo $_SESSION['language_charset'];?>" />
<title>Shop-Login</title>
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
input[type=text], input[type=password], input[type=email] {
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
input[type=text]:hover, input[type=password]:hover, input[type=email]:hover {
    background-color:#FFFFFF;
    border-color: #C6C6C6 #DADADA #EAEAEA;
    color: #666666;
}    
input[type=text]:focus, input[type=password]:focus, input[type=email]:focus {
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
#layout_login {
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
#layout_login a.help_login {
  position:absolute;
  width:32px;
  height:32px;
  outline:none;
  top:10px;
  right:10px;  
  display:block;
}
#layout_login .login {
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
#layout_login .login:hover {
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
  <div id="layout_login" class="cf">
    <form name="login" method="post" action="<?php echo xtc_href_link(FILENAME_LOGIN, xtc_get_all_get_params().'action=process', 'SSL'); ?>">
      <h1>Shop-Login</h1>
      <table>
        <tr>
          <td><span class="fieldtext">E-Mail</span><input type="email" name="email_address" maxlength="50" /></td>
        </tr>  
        <tr>
          <td><span class="fieldtext">Passwort</span><?php echo xtc_draw_password_field('password'); ?></td>
        </tr>
        <?php
        // captcha
        if ($_SESSION['customers_login_tries'] >= MODULE_CAPTCHA_LOGIN_NUM) {
          ?>
          <tr>
            <td><span class="fieldtext">Sicherheitscode</span><?php echo $mod_captcha->get_image_code(); ?></td>
          </tr>
          <tr>
            <td><span class="fieldtext">Sicherheitscode</span><?php echo $mod_captcha->get_input_code(); ?></td>
          </tr>
        <?php
        }
        ?>         
      </table>  
      <input type="submit" class="login" name="Submit" value="Anmelden" />
    </form>
  </div>
</body>
</html>