<?php
  /* --------------------------------------------------------------
   $Id: install_step2.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(install_2.php,v 1.4 2002/08/12); www.oscommerce.com
   (c) 2003 nextcommerce (install_step2.php,v 1.16 2003/08/1); www.nextcommerce.org
   (c) 2006 XT-Commerce www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application.php');

  // include needed functions
  require_once(DIR_FS_INC.'xtc_redirect.inc.php');
  require_once(DIR_FS_INC.'xtc_href_link.inc.php');
  require_once(DIR_FS_INC.'xtc_not_null.inc.php');

  include('language/'.$lang.'.php');

  if (!$script_filename = str_replace('\\', '/', getenv('PATH_TRANSLATED'))) {
    $script_filename = getenv('SCRIPT_FILENAME');
  }
  $script_filename = str_replace('//', '/', $script_filename);

  if (!$request_uri = getenv('REQUEST_URI')) {
    if (!$request_uri = getenv('PATH_INFO')) {
      $request_uri = getenv('SCRIPT_NAME');
    }
    if (getenv('QUERY_STRING'))
      $request_uri .=  '?' . getenv('QUERY_STRING');
  }

  $dir_fs_www_root_array = explode('/', dirname($script_filename));
  $dir_fs_www_root = array();
  for ($i=0; $i<sizeof($dir_fs_www_root_array)-2; $i++) {
    $dir_fs_www_root[] = $dir_fs_www_root_array[$i];
  }
  $dir_fs_www_root = implode('/', $dir_fs_www_root);

  $dir_ws_www_root_array = explode('/', dirname($request_uri));
  $dir_ws_www_root = array();
  for ($i=0; $i<sizeof($dir_ws_www_root_array)-1; $i++) {
    $dir_ws_www_root[] = $dir_ws_www_root_array[$i];
  }
  $dir_ws_www_root = implode('/', $dir_ws_www_root);

  //  NEW STEP2-4 Handling
  if(isset($_POST['install_db']) && $_POST['install_db'] == 1) {
   $test_welcome_step2 = TEXT_WELCOME_STEP2;
  } else {
   $test_welcome_step2 = TEXT_WELCOME_STEP2A;
  }

  //connect to database
  $db = array();
  $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
  $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
  $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));

  $db_error = false;
  xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);
  $sql = 'ALTER DATABASE '.$db['DB_DATABASE'].' DEFAULT CHARACTER SET '.$character_set.' COLLATE '.$collation.";";
  @mysql_query($sql);
  $sql = 'SET NAMES '.$character_set.' COLLATE '.$collation.";";
  @mysql_query($sql);

  //check MySQL *server* version
  if (!$db_error) {
    if (function_exists('version_compare')) {
      if(version_compare(mysql_get_server_info(), "4.1.2", "<") && strpos(strtolower(mysql_get_server_info()), 'native')=== false){
        $db_error = '<br /><strong>' . TEXT_DB_SERVER_VERSION_ERROR .  ' 4.1.2. <br /><br />' . TEXT_DB_SERVER_VERSION . mysql_get_server_info() . '</strong>.';
      }
    }
  }
  //check MySQL *client* version
  $db_warning = '';
  if (!$db_error) {
    if (function_exists('version_compare')) {
      preg_match("/[0-9]\.[0-9]\.[0-9]/",mysql_get_client_info(), $client_info);
      if(version_compare($client_info[0], "4.1.2", "<") && strpos(strtolower(mysql_get_client_info()), 'native')=== false){
        $db_warning = '<strong>' . TEXT_DB_CLIENT_VERSION_WARNING .  '<br /><br />' . TEXT_DB_CLIENT_VERSION . mysql_get_client_info() . '</strong>.';
      }
    }
  }
  //check db permission
  if (!$db_error) {    
    xtc_db_test_create_db_permission($db['DB_DATABASE']);
  }

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>modified eCommerce Shopsoftware Installer - STEP 2 / DB Connection</title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
    <style type="text/css">
      body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
      table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
      h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
      <!--
        .messageStackError, .messageStackWarning { font-family: Verdana, Arial, sans-serif; font-weight: bold; font-size: 10px; background-color: #; }
      -->
    </style>
  </head>
  <body>
    <table width="800" style="border:30px solid #fff;" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="95" colspan="2" >
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td><img src="images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align="center" valign="top">
          <br />
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <img src="images/step2.gif" width="705" height="180" border="0"><br />
                <br />
                <br />
                <div style="border:1px solid #ccc; background:#fff; padding:10px;"><?php echo $test_welcome_step2; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <table width="95%" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <?php
                  if ($db_error) {
                ?>
                <br />
                <table width="95%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><h1><?php echo TEXT_CONNECTION_ERROR; ?></h1></td>
                  </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td>
                      <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                        <p><?php echo TEXT_DB_ERROR; ?></p>
                      </div>
                      <p class="boxme">
                        <table border="0" width="100%" cellpadding="0" cellspacing="0" bgcolor="f3f3f3">
                          <tr>
                            <td>
                              <div style="border:1px solid #ccc; background:#ff0000; color:#fff; padding:10px;">
                                <?php echo $db_error; ?>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </p>
                      <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                        <p><?php echo TEXT_DB_ERROR_1; ?></p>
                        <p><?php echo TEXT_DB_ERROR_2; ?></p>
                      </div>
                      <form name="install" action="install_step1.php" method="post">
                        <?php echo draw_hidden_fields(); ?>
                        <br />
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="right"><a href="index.php"><img src="buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a> <input type="image" src="buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
                          </tr>
                        </table>
                      </form>
                      <br />
                    </td>
                  </tr>
                </table>
              <?php
                } else {
              ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><h1><?php echo TEXT_CONNECTION_SUCCESS; ?></h1></td>
                  </tr>
                </table>
                <?php
                  if($_POST['install_db'] == 1) {
                ?>
                  <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                    <p><?php echo TEXT_PROCESS_1; ?></p>
                    <p><?php echo TEXT_PROCESS_2; ?></p>
                    <p><?php echo TEXT_PROCESS_3; ?> <b><?php echo DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/'.MODIFIED_SQL; ?></b>.</p>
                  </div>
                <?php
                  }
                 // DB CLIENT WARNING
                  if ($db_warning != '') {
                ?>
                  <div style="border:1px solid #ccc; background:#ff0000; color:#fff; padding:10px;"><?php echo $db_warning; ?></div>
                <?php
                  }
                  if($_POST['install_db'] == 1) {
                     echo '<form name="install" action="install_step3.php" method="post">';
                     $install_db = 1;
                  } else {
                     echo '<form name="install" action="install_step4.php" method="post">';
                  }
                  if($_POST['install_cfg'] == 1) {
                    $create_config = 1;
                  }
                  echo draw_hidden_fields();
                ?>
                <br />
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="right">
                      <a href="install_step1.php?db=<?php echo $install_db;?>&cfg=<?php echo $create_config;?>"><img src="buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a>
                      <input type="image" src="buttons/<?php echo $lang;?>/button_continue.gif">
                    </td>
                  </tr>
                </table>
              </form>
              <?php
                }
              ?>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
</html>