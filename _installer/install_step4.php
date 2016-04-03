<?php
  /* --------------------------------------------------------------
   $Id: install_step4.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(install_4.php,v 1.9 2002/08/19); www.oscommerce.com
   (c) 2003 nextcommerce (install_step4.php,v 1.14 2003/08/17); www.nextcommerce.org
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

  require_once(DIR_FS_INC.'xtc_random_charcode.inc.php');
  require_once(DIR_FS_INC.'xtc_rand.inc.php');

  include('language/'.$lang.'.php');

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
                  <li class="active second_line"><span class="number">4.</span> <span class="title"><?php echo NAV_TITLE_STEP4; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP4; ?></span></li>
                  <li class="inactive second_line"><span class="number">5.</span> <span class="title"><?php echo NAV_TITLE_STEP5; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP5; ?></span></li>
                  <li class="inactive second_line"><span class="number">6.</span> <span class="title"><?php echo NAV_TITLE_STEP6; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP6; ?></span></li>
                  <!--
                  <li class="inactive second_line"><span class="number">7.</span> <span class="title"><?php echo NAV_TITLE_STEP7; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP7; ?></span></li>
                  //-->
                  <li class="inactive second_line last"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_FINISHED; ?></span><br /><span class="description"><?php echo NAV_DESC_FINISHED; ?></span></li>
                </ul>
                <br />
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><?php echo TEXT_WELCOME_STEP4; ?></div>
              </td>
            </tr>
          </table>
          <br />
          <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                  <tr>
                    <td>
                      <h1> <?php echo TITLE_WEBSERVER_CONFIGURATION; ?></h1>
                    </td>
                  </tr>
                </table>
                <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                <?php
                  if ( ( (file_exists(DIR_FS_CATALOG . 'includes/configure.php')) && (!is_writeable(DIR_FS_CATALOG . 'includes/configure.php')) ) 
                  //    || ( (file_exists(DIR_FS_CATALOG . 'admin/includes/configure.php')) && (!is_writeable(DIR_FS_CATALOG . 'admin/includes/configure.php')) ) 
                  //    || ( (file_exists(DIR_FS_CATALOG . 'admin/includes/local/configure.php')) && (!is_writeable(DIR_FS_CATALOG . 'admin/includes/local/configure.php')) ) 
                      || ( (file_exists(DIR_FS_CATALOG . 'includes/local/configure.php')) && (!is_writeable(DIR_FS_CATALOG . 'includes/local/configure.php')) )) {
                ?>
                <p>
                  <img src="images/icons/error.png" width="18" height="16">
                  <strong><font color="#A94442" size="2"><?php echo TITLE_STEP4_ERROR; ?></font></strong>
                </p>
                  <div style="border:1px solid #DCA7A7; background:#F2DEDE; color:#A94442; padding:10px;"><?php echo TEXT_STEP4_ERROR; ?>
             <?php /*
                    <ul class="boxMe">
                      <li>cd <?php echo DIR_FS_CATALOG; ?>admin/includes/</li>
                      <li>touch configure.php</li>
                      <li>chmod 706 configure.php</li>
              <?php //<li>chmod 706 configure.org.php</li> - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure ?>
                    </ul>
                  */ ?>
                    <ul class="boxMe">
                      <li>cd <?php echo DIR_FS_CATALOG; ?>includes/</li>
                      <li>touch configure.php</li>
                      <li>chmod 777 configure.php</li>
              <?php //<li>chmod 777 configure.org.php</li> - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure ?>
                    </ul>
                  </div>
                  <p class="noteBox"><?php echo TEXT_STEP4_ERROR_1; ?></p>
                  <p class="noteBox"><font face="Verdana, Arial, Helvetica, sans-serif"><?php echo TEXT_STEP4_ERROR_2; ?></p>
                  <form name="install" action="install_step4.php" method="post">
                  <?php echo $input_lang; 
                        echo draw_hidden_fields(); ?>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="center"><a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                        <td align="center">
                          <input type="image" src="images/buttons/<?php echo $lang;?>/button_retry.gif" border="0" alt="Retry">
                        </td>
                      </tr>
                    </table>
                  </form>
                  <?php
                    } else {
                  ?>
                  <form name="install" action="install_step5.php" method="post">
                  <?php echo $input_lang; 
                        echo draw_hidden_fields(); ?>
                    <p><?php echo TEXT_VALUES; ?><br />
                      <br />
                      includes/configure.php<br />
              <?php //includes/configure.org.php<br /> - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure ?>
              <?php //admin/includes/configure.php<br /> ?>
              <?php //admin/includes/configure.org.php<br /> - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure ?>
                    </p>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="border-top: 1px solid; border-color: #CFCFCF">
                          <b><?php echo TITLE_CHECK_CONFIGURATION; ?></b>
                          <?php //BOF - web28 - 2010.02.09 -  NEW INFO TEXT ?>
                          <div style="border:1px solid #DCA7A7; background:#F2DEDE; color:#A94442; padding:10px;"><b><?php echo TITLE_WEBSERVER_INFO; ?></b></div>
                          <?php //EOF - web28 - 2010.02.09 -  NEW INFO TEXT ?>
                        </td>
                        <td style="border-top: 1px solid; border-color: #CFCFCF">&nbsp;</td>
                      </tr>
                    </table>
                    <p>
                      <b><?php echo TEXT_HTTP; ?></b><br />
                      <?php echo xtc_draw_input_field_installer('HTTP_SERVER', 'http://' . rtrim(getenv('HTTP_HOST'),'/'), '', 'style="width:250px;"'); ?><br />
                      <?php echo TEXT_HTTP_LONG; ?>
                    </p>
                    <p>
                      <b><?php echo TEXT_HTTPS; ?>*</b><br />
                      <?php echo xtc_draw_input_field_installer('HTTPS_SERVER', 'https://' . rtrim(getenv('HTTP_HOST'),'/'), '', 'style="width:250px;"'); ?> <br />
                      <?php echo TEXT_HTTPS_LONG; ?>
                    </p>
                    <p>
                      <?php echo xtc_draw_checkbox_field_installer('ENABLE_SSL', 'true'); ?>
                      <b><?php echo TEXT_SSL; ?></b><br />
                      <?php echo TEXT_SSL_LONG; ?>
                    </p>
                    <p>
                      <?php echo xtc_draw_checkbox_field_installer('USE_SSL_PROXY', 'true'). TEXT_SSL_PROXY_LONG; ?>
                    </p>                    
                    <div style="border: #a3a3a3 1px solid; padding: 3px; background-color: #f4f4f4;">
                      <?php echo TEXT_SSL_PROXY_EXP; ?>
                    </div>
                    <?php //BOF - web28 - 2010.02.20 -  NEW ROOT INFO ?>
                    <p><b><?php echo TEXT_WS_ROOT; ?></b></p>
                    <span style="border: #a3a3a3 1px solid; padding: 3px; background-color: #f4f4f4;">
                      <?php echo DIR_FS_DOCUMENT_ROOT; ?>
                    </span>
                    <p><?php echo TEXT_WS_ROOT_INFO; ?></p>
                    <p><b><?php echo TEXT_WS_CATALOG; ?></b></p>
                    <?php echo xtc_draw_hidden_field_installer('DIR_WS_CATALOG', $_POST['DIR_WS_CATALOG']); ?>
                    <span style="border: #a3a3a3 1px solid; padding: 3px; background-color: #f4f4f4;">
                      <?php echo $_POST['DIR_WS_CATALOG']; ?>
                    </span>
                    <p><?php echo TEXT_WS_ROOT_INFO; ?></p>
                    <?php //EOF - web28 - 2010.02.20 -  NEW ROOT INFO ?>                    
                    <p>
                      <b><?php echo TEXT_ADMIN_DIRECTORY; ?>*</b><br />
                      <?php echo xtc_draw_input_field_installer('admin_directory', trim(DIR_ADMIN, '/'), '', 'style="width:250px;"'); ?> <br />
                      <?php echo TEXT_ADMIN_DIRECTORY_LONG . '<b>'.xtc_random_charcode(10).'</b>'; ?>
                    </p>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                      <tr>                        
                        <td style="border-top: 1px solid; border-color: #CFCFCF">&nbsp;</td>
                      </tr>
                    </table>
                    <?php
                      echo xtc_draw_hidden_field_installer('DB_MYSQL_TYPE', $_POST['DB_MYSQL_TYPE']);
                      echo xtc_draw_hidden_field_installer('DB_SERVER', $_POST['DB_SERVER']);
                      echo xtc_draw_hidden_field_installer('DB_SERVER_USERNAME', $_POST['DB_SERVER_USERNAME']);
                      echo xtc_draw_hidden_field_installer('DB_SERVER_PASSWORD', $_POST['DB_SERVER_PASSWORD']);
                      echo xtc_draw_hidden_field_installer('DB_DATABASE', $_POST['DB_DATABASE']);
                      echo xtc_draw_hidden_field_installer('install_db', $_POST['install_db']);
                      echo xtc_draw_hidden_field_installer('install_cfg', $_POST['install_cfg']);
                      echo xtc_draw_hidden_field_installer('STORE_SESSIONS', 'mysql', true);
                    ?>                     
                  </div>
                  <br />
                  <table border="0" width="100%" cellspacing="0" cellpadding="0">
                    <tr>
                      <td align="right">
                        <a href="index.php?lg=<?php echo $lang .'&char='.INSTALL_CHARSET; ?>"><img src="images/buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel" /></a>
                        <input type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif">
                      </td>
                    </tr>
                  </table>
                  <br />
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