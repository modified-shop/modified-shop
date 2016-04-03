<?php
  /* --------------------------------------------------------------
   $Id: install_step1.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(install.php,v 1.7 2002/08/14); www.oscommerce.com
   (c) 2003 nextcommerce (install_step1.php,v 1.10 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application.php');

  include('language/'.$lang.'.php');

  if (!$script_filename = str_replace('\\', '/', getenv('PATH_TRANSLATED'))) {
    $script_filename = getenv('SCRIPT_FILENAME');
  }
  $script_filename = str_replace('//', '/', $script_filename);

  if (!$request_uri = getenv('REQUEST_URI')) {
    if (!$request_uri = getenv('PATH_INFO')) {
      $request_uri = getenv('SCRIPT_NAME');
    }

    if (getenv('QUERY_STRING')) $request_uri .=  '?' . getenv('QUERY_STRING');
  }

  $dir_fs_www_root_array = explode('/', dirname($script_filename));
  $dir_fs_www_root = array();
  for ($i=0; $i<sizeof($dir_fs_www_root_array)-2; $i++) {
    $dir_fs_www_root[] = $dir_fs_www_root_array[$i];
  }
  $dir_fs_www_root = implode('/', $dir_fs_www_root);

  //DIR_WS_CATALOG
  $dir_ws_www_root_array = explode('/', dirname($request_uri));
  $dir_ws_www_root = array();
  for ($i=0; $i<sizeof($dir_ws_www_root_array)-1; $i++) {
    if ($dir_ws_www_root_array[$i] != '.' && $dir_ws_www_root_array[$i] != '..') { // web28 - 2010-03-18 - Fix Dir
      $dir_ws_www_root[] = $dir_ws_www_root_array[$i];
    }
  }
  $dir_ws_www_root = implode('/', $dir_ws_www_root);

  //BOF - web28 - 2010-03-18 - RESTORE POST  & GET DATA
  $inst_db = true;
  $config = true;
  if(isset($_POST['DB_SERVER'])){
    //echo 'TEST' . $_POST['install_db'];
    if($_POST['install_db'] == 1) $inst_db = true; else $inst_db = false;
    if($_POST['install_cfg'] == 1) $config = true; else $config = false;
  }
  if(isset($_GET['insdb']) && $_GET['insdb'] !=1 ) $inst_db = false;
  if(isset($_GET['cfg']) && $_GET['cfg'] !=1 ) $config = false;
  //EOF - web28 - 2010-03-18 - RESTORE POST  & GET DATA

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
                  <li class="active"><span class="number">1.</span> <span class="title"><?php echo NAV_TITLE_STEP1; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP1; ?></span></li>
                  <li class="inactive"><span class="number">2.</span> <span class="title"><?php echo NAV_TITLE_STEP2; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP2; ?></span></li>
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
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><?php echo TEXT_WELCOME_STEP1; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <form name="install" method="post" action="install_step2.php">
            <?php echo $input_lang; 
                  echo draw_hidden_fields(); ?>
            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td><h1><?php echo TITLE_CUSTOM_SETTINGS; ?></h1></td>
                    </tr>
                  </table>
                  <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                    <?php //BOF - web28 - 2010-03-18 - change install[]  to install_db and install_cfg - restore data - 2010-07-07 FIX for PHP5.3?>
                    <p><?php echo xtc_draw_checkbox_field_installer('install_db', 1, $inst_db); ?>
                    <b><?php echo TEXT_IMPORT_DB; ?></b><br />
                    <?php echo TEXT_IMPORT_DB_LONG; ?></p>
                    <p><?php echo xtc_draw_checkbox_field_installer('install_cfg', 1, $config); ?>
                    <?php //BOF - web28 - 2010-03-18 - change install[]  to install_db and install_cfg - restore data - 2010-07-07 FIX for PHP5.3?>
                    <b><?php echo TEXT_AUTOMATIC; ?></b><br />
                    <?php echo TEXT_AUTOMATIC_LONG; ?></p>
                  </div>
                </td>
              </tr>
            </table>
            <br />
            <br />
            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td><h1><?php echo TITLE_DATABASE_SETTINGS; ?></h1></td>
                    </tr>
                  </table>
                  <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                    <p><b><?php echo TEXT_DATABASE_TYPE; ?></b><br />
                    <table width="300" border="0" cellpadding="0" cellspacing="4">
                      <tr>
                        <td width="98"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />mysql</td>
                        <td width="192">
                          <?php echo xtc_draw_radio_field_installer('DB_MYSQL_TYPE', 'mysql', ((INSTALL_CHARSET=='latin1')?true:false)); ?>
                        </td>
                      </tr>
                      <tr>
                        <td><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />mysqli (Beta)</td>
                        <td>
                        <?php echo xtc_draw_radio_field_installer('DB_MYSQL_TYPE', 'mysqli', ((INSTALL_CHARSET=='utf8')?true:false)); ?> </td>
                      </tr>
                    </table>
                    <?php echo TEXT_DATABASE_TYPE_LONG; ?></p>
                    <p><b><?php echo TEXT_DATABASE_SERVER; ?></b><br />
                    <?php echo xtc_draw_input_field_installer('DB_SERVER'); ?><br />
                    <?php echo TEXT_DATABASE_SERVER_LONG; ?></p>
                    <p><b><?php echo TEXT_USERNAME; ?></b><br />
                    <?php echo xtc_draw_input_field_installer('DB_SERVER_USERNAME'); ?><br />
                    <?php echo TEXT_USERNAME_LONG; ?></p>
                    <p><b><?php echo TEXT_PASSWORD; ?></b><br />
                    <?php echo xtc_draw_password_field_installer('DB_SERVER_PASSWORD'); ?><br />
                    <?php echo TEXT_PASSWORD_LONG; ?></p>
                    <p><b><?php echo TEXT_DATABASE; ?></b><br />
                    <?php echo xtc_draw_input_field_installer('DB_DATABASE'); ?><br />
                    <?php echo TEXT_DATABASE_LONG; ?></p>
                  </div>
                </td>
              </tr>
            </table>
            <br />
            <br />
            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td><h1><?php echo TITLE_WEBSERVER_SETTINGS; ?> </h1></td>
                    </tr>
                  </table>
                  <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                    <?php //BOF - web28 - 2010.02.20 -  NEW ROOT INFO ?>
                    <p><b><?php echo TEXT_WS_ROOT; ?></b></p>
                    <?php echo xtc_draw_hidden_field_installer('DIR_FS_DOCUMENT_ROOT', DIR_FS_DOCUMENT_ROOT); ?>
                    <span style="border: #a3a3a3 1px solid; padding: 3px; background-color: #f4f4f4;"><?php echo DIR_FS_DOCUMENT_ROOT; ?></span>
                    <p><?php echo TEXT_WS_ROOT_INFO; ?></p>
                    <p><b><?php echo TEXT_WS_CATALOG; ?></b></p>
                    <?php echo xtc_draw_hidden_field_installer('DIR_WS_CATALOG', $dir_ws_www_root . '/'); ?>
                    <span style="border: #a3a3a3 1px solid; padding: 3px; background-color: #f4f4f4;"><?php echo $dir_ws_www_root . '/'; ?></span>
                    <p><?php echo TEXT_WS_ROOT_INFO; ?></p>
                    <?php //EOF - web28 - 2010.02.20 -  NEW ROOT INFO ?>                    
                  </div>
                </td>
              </tr>
            </table>
            <br />
            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
              <tr>
                <td align="right"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a> <input type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif" border="0" alt="Continue"></td>
              </tr>
            </table>
          </form>
        </td>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
</html>