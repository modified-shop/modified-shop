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

  // include Database functions for installer
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_connect_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_select_db.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_query_installer.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_test_create_db_permission.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_test_connection.inc.php');
  require_once(DIR_FS_INC_INSTALLER.'xtc_db_install.inc.php');

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

  $messageStack = new messageStack();

  //connect to database
  $db = array();
  $db['DB_MYSQL_TYPE'] = trim(stripslashes($_POST['DB_MYSQL_TYPE']));
  $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
  $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
  $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));

  $db_error = false;
  xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_MYSQL_TYPE']);
  
  $check_query = xtc_db_query_installer("SHOW TABLES FROM ".$db['DB_DATABASE'], $db['DB_MYSQL_TYPE']);
  if (xtc_db_num_row_installer($check_query, $db['DB_MYSQL_TYPE']) > 0 && (isset($_POST['install_db']) && $_POST['install_db'] == 1)) {
    $messageStack->add('db_warning', '<strong>' . TEXT_DB_NOT_EMPTY . '</strong>');
  }
  
  @xtc_db_query_installer('ALTER DATABASE '.$db['DB_DATABASE'].' DEFAULT CHARACTER SET '.$character_set.' COLLATE '.$collation, $db['DB_MYSQL_TYPE']);
  @xtc_db_query_installer('SET NAMES '.$character_set.' COLLATE '.$collation, $db['DB_MYSQL_TYPE']);

  //check MySQL *server* version
  if (!$db_error) {
    if (function_exists('version_compare')) {
      if(version_compare(xtc_db_get_server_info($db['DB_MYSQL_TYPE']), "5.0.0", "<=") && strpos(strtolower(xtc_db_get_server_info($db['DB_MYSQL_TYPE'])), 'native')=== false){
        $messageStack->add('db_warning', '<strong>' . TEXT_DB_SERVER_VERSION_ERROR . ' 5.0.0.<br/>' . TEXT_DB_SERVER_VERSION . xtc_db_get_server_info($db['DB_MYSQL_TYPE']) . '</strong>');
      }
    }
  }
  
  //check MySQL *client* version
  if (!$db_error) {
    if (function_exists('version_compare')) {
      preg_match("/[0-9]\.[0-9]\.[0-9]/",xtc_db_get_client_info($db['DB_MYSQL_TYPE']), $client_info);
      if(version_compare($client_info[0], "5.0.0", "<=") && strpos(strtolower(xtc_db_get_client_info($db['DB_MYSQL_TYPE'])), 'native') === false){
        $messageStack->add('db_warning', '<strong>' . TEXT_DB_CLIENT_VERSION_WARNING . ' 5.0.0.<br/>' . TEXT_DB_CLIENT_VERSION . xtc_db_get_client_info($db['DB_MYSQL_TYPE']) . '</strong>');
        $messageStack->add('db_warning', '<strong>' . TEXT_DB_CLIENT_VERSION_NOTE . '</strong>');
      }
    }
  }
      
  //check db permission
  if (!$db_error) {    
    xtc_db_test_create_db_permission($db['DB_DATABASE'], $db['DB_MYSQL_TYPE']);
  }

  require ('includes/header.php');
?>
    <table width="803" style="border:10px solid #fff;" bgcolor="#ffffff" border="0" align="center" cellpadding="0" cellspacing="0">
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
        <td align="left" valign="top">
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <ul id="navigation" class="cf">
                  <li class="inactive"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_INDEX; ?></span><br /><span class="description"><?php echo NAV_DESC_INDEX; ?></span></li>
                  <li class="inactive"><span class="number">1.</span> <span class="title"><?php echo NAV_TITLE_STEP1; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP1; ?></span></li>
                  <li class="active"><span class="number">2.</span> <span class="title"><?php echo NAV_TITLE_STEP2; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP2; ?></span></li>
                  <li class="inactive last"><span class="number">3.</span> <span class="title"><?php echo NAV_TITLE_STEP3; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP3; ?></span></li>
                  <li class="inactive second_line"><span class="number">4.</span> <span class="title"><?php echo NAV_TITLE_STEP4; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP4; ?></span></li>
                  <li class="inactive second_line"><span class="number">5.</span> <span class="title"><?php echo NAV_TITLE_STEP5; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP5; ?></span></li>
                  <li class="inactive second_line"><span class="number">6.</span> <span class="title"><?php echo NAV_TITLE_STEP6; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP6; ?></span></li>
                  <!--
                  <li class="inactive second_line"><span class="number">7.</span> <span class="title"><?php echo NAV_TITLE_STEP7; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP7; ?></span></li>
                  //-->
                  <li class="inactive second_line last"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_FINISHED; ?></span><br /><span class="description"><?php echo NAV_DESC_FINISHED; ?></span></li>
                </ul>
                <br />
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><?php echo $test_welcome_step2; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <?php
                  if ($db_error) {
                ?>
                <br />
                <table width="95%" border="0" align="left" cellpadding="0" cellspacing="0">
                  <tr>
                    <td><h1><?php echo TEXT_CONNECTION_ERROR; ?></h1></td>
                  </tr>
                </table>
                <table width="100%" cellpadding="0" cellspacing="0">
                  <tr>
                    <td>
                      <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                        <p><?php echo TEXT_DB_ERROR; ?></p>
                      </div>
                      <p class="boxme">
                        <table border="0" width="100%" cellpadding="0" cellspacing="0" bgcolor="f4f4f4">
                          <tr>
                            <td>
                              <div style="border:1px solid #DCA7A7; background:#F2DEDE; color:#A94442; padding:10px;">
                                <?php echo $db_error; ?>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </p>
                      <div style="border:1px solid #DCA7A7; background:#F2DEDE; color:#A94442; padding:10px;">
                        <p><?php echo TEXT_DB_ERROR_1; ?></p>
                        <p><?php echo TEXT_DB_ERROR_2; ?></p>
                      </div>
                      <form name="install" action="install_step1.php" method="post">
                      <?php echo $input_lang; 
                            echo draw_hidden_fields(); ?>
                        <br />
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="right"><a href="index.php?lg=<?php echo $lang.'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a> <input type="image" src="images/buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
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
                  <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                    <p><?php echo TEXT_PROCESS_1; ?></p>
                    <p><?php echo TEXT_PROCESS_2; ?></p>
                    <p><?php echo TEXT_PROCESS_3; ?> <b><?php echo DIR_FS_CATALOG . DIR_MODIFIED_INSTALLER.'/'.MODIFIED_SQL; ?></b>.</p>
                  </div>
                <?php
                  }
                 // DB CLIENT WARNING
                  if ($messageStack->size('db_warning') > 0 ) {
                ?>
                  <div style="border:1px solid #DCA7A7; background:#F2DEDE; color:#A94442; padding:10px;"><?php echo $messageStack->output('db_warning'); ?></div>
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
                  echo $input_lang; 
                  echo draw_hidden_fields(); 
                ?>
                <br />
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="right">
                      <a href="install_step1.php?lg=<?php echo $lang.'&char='.INSTALL_CHARSET; ?>&db=<?php echo $install_db;?>&cfg=<?php echo $create_config;?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a>
                      <input type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif">
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