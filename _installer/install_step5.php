<?php
  /* --------------------------------------------------------------
   $Id: install_step5.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (install_step5.php,v 1.25 2003/08/24); www.nextcommerce.org
   (c) 2003 XT-Commerce (configure.php)

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

  include('language/'.$lang.'.php');

  // Fix possible end slash
  $http_server = rtrim($_POST['HTTP_SERVER'], '/');
  $https_server = rtrim($_POST['HTTPS_SERVER'], '/');  
  
  $admin_error = false;
  if (isset($_POST['admin_directory']) && $_POST['admin_directory'] != trim(DIR_ADMIN, '/')) {
    $new_admin_dir = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['admin_directory']);
    if (!is_dir(DIR_FS_CATALOG.$new_admin_dir)) {
      if (@rename(DIR_FS_CATALOG.trim(DIR_ADMIN, '/'), DIR_FS_CATALOG.$new_admin_dir) === false) {
        $admin_error = true;
      }
    }
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
                  <li class="inactive"><span class="number">2.</span> <span class="title"><?php echo NAV_TITLE_STEP2; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP2; ?></span></li>
                  <li class="inactive last"><span class="number">3.</span> <span class="title"><?php echo NAV_TITLE_STEP3; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP3; ?></span></li>
                  <li class="inactive second_line"><span class="number">4.</span> <span class="title"><?php echo NAV_TITLE_STEP4; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP4; ?></span></li>
                  <li class="active second_line"><span class="number">5.</span> <span class="title"><?php echo NAV_TITLE_STEP5; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP5; ?></span></li>
                  <li class="inactive second_line"><span class="number">6.</span> <span class="title"><?php echo NAV_TITLE_STEP6; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP6; ?></span></li>
                  <!--
                  <li class="inactive second_line"><span class="number">7.</span> <span class="title"><?php echo NAV_TITLE_STEP7; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP7; ?></span></li>
                  //-->
                  <li class="inactive second_line last"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_FINISHED; ?></span><br /><span class="description"><?php echo NAV_DESC_FINISHED; ?></span></li>
                </ul>
                <br />
              </td>
            </tr>
          </table>
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                  <?php
                    $db = array();
                    $db['DB_MYSQL_TYPE'] = trim(stripslashes($_POST['DB_MYSQL_TYPE']));
                    $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
                    $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
                    $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
                    $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));
                    $db_error = false;
                    xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD'], $db['DB_MYSQL_TYPE']);
                    if (!$db_error) {
                      xtc_db_test_connection($db['DB_DATABASE'], $db['DB_MYSQL_TYPE']);
                    }
                    if ($db_error) {
                      ?>
                      <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background:#F2DEDE; color:#a94442; padding:10px; border:1px solid #DCA7A7">
                        <tr>
                          <td>
                            <img src="images/icons/error.png" width="18" height="16">&nbsp;<strong><?php echo TEXT_CONNECTION_ERROR; ?></strong>
                          </td>
                        </tr>
                      
                        <tr>
                          <td><p><strong><?php echo TEXT_DB_ERROR; ?></strong></p><b><?php echo $db_error; ?></b></td>
                        </tr>
                      </table>
                      <p><?php echo TEXT_DB_ERROR_1; ?></p>
                      <p><?php echo TEXT_DB_ERROR_2; ?></p>
                      <form name="install" action="install_step4.php" method="post">
                      <?php echo $input_lang; 
                            echo draw_hidden_fields(); ?>
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="center"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                            <td align="center"><input type="image" src="images/buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
                          </tr>
                        </table>
                      </form>
                      <?php
                  } else {
                    //Testpfad
                    if (defined('DISABLE_PATH_CHECK') && DISABLE_PATH_CHECK) {
                      $link_status['Status-Code'] = 200;
                    } else {
                      $url = $http_server . $_POST['DIR_WS_CATALOG'] . 'robots.txt';
                      $link_status = phpLinkCheck($url);
                    }
                    if ($link_status['Status-Code'] == 550) {
                      $errmsg = 'URL: ' . $url . '<br />';
                      $errmsg = 'PARSED URL: ' . $link_status['Parsed_URL']['host'] . $link_status['Parsed_URL']['path']. '<br />';
                      $errmsg .= 'HTTP Server: ' . $link_status['Parsed_URL']['host'] . ' unbekannt/unknown' . '   [ERROR: 550]';
                    } else if ($link_status['Status-Code'] == 404) {
                      $errmsg = $link_status['Parsed_URL']['host'] . $link_status['Parsed_URL']['path'] . '   [ERROR: 404]';
                    }
                    if ($link_status['Status-Code'] != 200) {
                      //Fehleranzeige
                      if (trim($errmsg) =='')
                        $errmsg = $url . '   [ERROR: '. $link_status['Status-Code'] .']';
                      ?>
                      <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background:#F2DEDE; color:#a94442; padding:10px; border:1px solid #DCA7A7">
                        <tr>
                          <td style="width:40px;">
                            <img src="images/icons/error.png" width="18" height="16" style="position: relative; top: -6px;" />
                          </td>
                          <td style="font-weight:bold; font-size:18px;">
                            <?php echo TEXT_PATH_ERROR; ?>
                          </td>
                        </tr>
                      </table>
                      
                      <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="f4f4f4" style="padding:10px; border:1px solid #DCA7A7">
                        <tr>
                          <td><?php echo TEXT_PATH_ERROR2; ?></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td><b><?php echo $errmsg;?></b></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td><?php echo TEXT_PATH_ERROR3;?></td>
                        </tr>
                        <tr>
                          <td>&nbsp;</td>
                        </tr>
                        <tr>
                          <td align="center">
                            <form name="install" action="install_step4.php" method="post">
                              <?php echo $input_lang; 
                                    echo draw_hidden_fields(); ?>
                              <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td align="center"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                                  <td align="center"><input type="image" src="images/buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
                                </tr>
                              </table>
                            </form>
                          </td>
                        </tr>
                      </table>
                      <?php
                      } 
                          //create  includes/configure.php
                          include ('includes/templates/configure.php');
                          if (file_exists(DIR_FS_CATALOG.'/includes/local/configure.php')) {
                            $fp = fopen(DIR_FS_CATALOG . 'includes/local/configure.php', 'w');
                          } else {
                            $fp = fopen(DIR_FS_CATALOG . 'includes/configure.php', 'w');
                          }
                          fputs($fp, $file_contents);
                          fclose($fp);

                          // REM - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure

                          //create  admin/includes/configure.php
                          /*
                          include ('includes/templates/configure_admin.php');
                          $fp = fopen(DIR_FS_CATALOG . 'admin/includes/configure.php', 'w');
                          fputs($fp, $file_contents);
                          fclose($fp);
                          */
                          // REM - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure

                          //BOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL
                          $step = ($_POST['install_db'] == 1) ? 'install_step6' : 'install_finished';
                          //EOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL
                        ?>
                        <center>
                          <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
                            <br />
                            <br />
                            <?php
                              if ($admin_error === true) {
                                echo TEXT_ADMIN_DIRECTORY_ERROR;
                              }
                            ?>
                            <?php echo TEXT_WS_CONFIGURATION_SUCCESS; ?>
                          </font>
                        </center>
                        <br />
                        <br />
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                          <?php //BOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL ?>
                            <td align="center">
                              <a href="<?php echo $step;?>.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>">
                                <img src="images/buttons/<?php echo $lang;?>/button_continue.gif" border="0">
                              </a>
                            </td>
                          <?php //EOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL ?>
                          </tr>
                        </table>
                        <br />
                        <br />
                    <?php
                    }
                  ?>
                </div>
              </td>
            </tr>
          </table>
          <br />
        </td>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
</html>