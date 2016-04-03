<?php
  /* --------------------------------------------------------------
   $Id: index.php 3426 2012-08-13 15:39:17Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   ----------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (index.php,v 1.18 2003/08/17); www.nextcommerce.org
   (c) 2006 xt:Commerce (index.php 1220 2005-09-16); www.xtcommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application.php');

  //BOF  - web28 - 2011-05-19 - SUPPORT (verbose output)
  $support = '&nbsp;';
  if (isset($_GET['support'])) {
    $support  = 'URL: ' . $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']. '<br />';
    $support .= '$_SERVER[PHP_SELF]: ' . $_SERVER['PHP_SELF']. '<br />';
    $support .= '$_SERVER[DOCUMENT_ROOT]: ' . $_SERVER['DOCUMENT_ROOT']. '<br />';
    $support .= '$_SERVER[SCRIPT_NAME]: ' . $_SERVER['SCRIPT_NAME']. '<br />';
    $support .= '$_SERVER[SCRIPT_FILENAME]: ' . $_SERVER['SCRIPT_FILENAME']. '<br />';
    $support .= 'DIR_FS_DOCUMENT_ROOT: ' . DIR_FS_DOCUMENT_ROOT. '<br />';
  }
  //EOF  - web28 - 2011-05-19 - SUPPORT (verbose output)

  // include needed functions
  require_once(DIR_FS_INC.'xtc_image.inc.php');
  require_once(DIR_FS_INC.'xtc_draw_separator.inc.php');
  require_once(DIR_FS_INC.'xtc_redirect.inc.php');
  require_once(DIR_FS_INC.'xtc_href_link.inc.php');

  //BOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php
  //include('language/english.php');
  include('language/'.$lang.'.php');
  //BOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php
  define('HTTP_SERVER','');
  define('HTTPS_SERVER','');
  define('DIR_WS_CATALOG','');
  define('DIR_WS_BASE',''); //web28 - 2010-12-13 - FIX for $messageStack icons

  //BOF - web28 - 2010-12-13 - redirect to db_upgrade.php, if database is already set up (do an update instead of a new installation)
  include(DIR_FS_CATALOG.'/includes/configure.php');
  $upgrade = true;
  if (DB_SERVER_USERNAME == '' && DB_SERVER_PASSWORD == '' && DB_DATABASE == '') {
    $upgrade = false;
  }
  if (isset($_POST['db_upgrade']) && ($_POST['db_upgrade'] == true)) {
    xtc_redirect(xtc_href_link('update.php?lg='. $lang, '', 'NONSSL'));
  }
  //EOF - web28 - 2010-12-13 - redirect to db_upgrade.php, if database is already set up (do an update instead of a new installation)

  $messageStack = new messageStack();
  $error = false;

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    if ( ($lang != 'german') && ($lang != 'english') ) {
      $error = true;
      $messageStack->add('index', SELECT_LANGUAGE_ERROR);
    }
    if ($error == false) {
      xtc_redirect(xtc_href_link('install_step1.php?lg='. $lang .'&char='.INSTALL_CHARSET, '', 'NONSSL'));
    }
  }

  include ('includes/check_permissions.php');
  include ('includes/check_requirements.php');
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
                <li class="active"><span class="number">&raquo;</span> <span class="title"><?php echo NAV_TITLE_INDEX; ?></span><br /><span class="description"><?php echo NAV_DESC_INDEX; ?></span></li>
                <li class="inactive"><span class="number">1.</span> <span class="title"><?php echo NAV_TITLE_STEP1; ?></span><br /><span class="description"><?php echo NAV_DESC_STEP1; ?></span></li>
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
              <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;"><?php echo TEXT_WELCOME_INDEX; ?></div>
            </td>
          </tr>
          <?php
          if ($error_flag === true) {
            if ($messageStack->size('file_permission') > 0 
                || $messageStack->size('folder_permission') > 0 
                || $messageStack->size('rfolder_permission') > 0
                ) 
            {
            ?>
            <tr>
              <td>
                <br /><h1><?php echo TEXT_CHMOD_REMARK_HEADLINE; ?>:</h1>
                <div style="background:#fff; padding:0 10px; border:1px solid #DCA7A7">
                  <p><?php echo TEXT_CHMOD_REMARK; ?></p>
                </div><br />
                <div style="background:#F2DEDE; color:#a94442; padding:10px; border:1px solid #DCA7A7" class="messageStackError">
                  <?php
                  if ($messageStack->size('file_permission') > 0) {
                    echo TEXT_WRONG_FILE_PERMISSION . '<br/>';
                    echo $messageStack->output('file_permission');
                  }
                  if ($messageStack->size('folder_permission') > 0) {
                    echo TEXT_WRONG_FOLDER_PERMISSION . '<br/>';
                    echo $messageStack->output('folder_permission');
                  }
                  if ($messageStack->size('rfolder_permission') > 0) {
                    echo TEXT_WRONG_RFOLDER_PERMISSION . '<br/>';
                    echo $messageStack->output('rfolder_permission');
                  }
                  ?>
                </div>
              </td>
            </tr>
            <?php
            }
            // BOC flth new permission fix system
            if ($folder_flag || $file_flag || $rfolder_flag) {
              $host = isset($_POST['path']) ? $_POST['host'] : rtrim(getenv('HTTP_HOST'),'/');
              $path = isset($_POST['path']) ? $_POST['path'] : basename(DIR_FS_CATALOG).'/';
              $port = isset($_POST['port']) ? $_POST['port'] : '21';
              $login = isset($_POST['login']) ? $_POST['login'] : '';
              ?>
              <tr>
                <td>
                  <div id="permissions" class="popout">
                      <div class="left" >
                        <?php echo FTP_CHANGE_PERM_EXPLAIN; ?><br />
                      </div>
                      <div class="right">
                        <?php
                        if ($messageStack->size('ftp_message') > 0) {
                          echo '<div style="background:#F2DEDE; color:#a94442; padding:10px; border:1px solid #DCA7A7" class="messageStackError">';
                          echo $messageStack->output('ftp_message');
                          echo '</div>';
                        }
                        ?>
                        <form name="ftp" action="index.php" method="post">
                        <?php echo $input_lang; ?>
                          <label for="host"><?php echo FTP_HOST; ?>:</label>
                            <?php echo xtc_draw_input_field_installer('host', $host, '', 'id="host"'); ?><br />
                          <label for="port"><?php echo FTP_PORT; ?>:</label>
                            <?php echo xtc_draw_input_field_installer('port', $port, '', 'id="port"'); ?><br />
                          <label for="path"><?php echo FTP_PATH; ?>:</label>
                            <?php echo xtc_draw_input_field_installer('path', $path, '', 'id="path"'); ?><br />
                          <label for="login"><?php echo FTP_LOGIN; ?>:</label>
                            <?php echo xtc_draw_input_field_installer('login', $login, '', 'id="login"'); ?><br />
                          <label for="password"><?php echo FTP_PASSWORD; ?>:</label>
                            <?php echo xtc_draw_password_field_installer('password', $password, '', 'id="password"'); ?><br />
                          <?php echo xtc_draw_hidden_field_installer('action', 'ftp'); ?>
                          <input type="submit" value="<?php echo CONNECT_FTP; ?>" />
                        </form>
                      </div>
                    <br style="clear:both;" />
                  </div>
                </td>
              </tr>
              <?php
            }

            if ($messageStack->size('requirement') > 0) {
            ?>
            <tr>
              <td>
                <br />
                <div style="background:#F2DEDE; color:#A94442; padding:10px; border:1px solid #DCA7A7">
                  <div style="float: left; width: 125px;">
                    <img height="93" width="106" style="border:0;" title="Warnung" alt="Warnung" src="images/icons/big_warning.png">
                  </div>
                  <div style="float: left; width: 82%;">
                    <?php
                      echo $messageStack->output('requirement');
                    ?>
                  </div>
                  <div style="clear: both"></div>
                </div>
              </td>
            </tr>
            <?php
            }
            // EOC flth new permission fix system
            ?>
          <?php } ?>
          <?php if ($ok_message != '') { ?>
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td style="border: 1px solid; border-color: #b2dba1; padding:10px; color: #3C763D;" bgcolor="#d4ebcb">
                <strong><?php echo TEXT_CHECKING; ?>:</strong>
                <br /><br />
                <?php
                  echo $ok_message;
                ?>
              </td>
            </tr>
          <?php } ?>
        </table>
        <br/>
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <strong><?php echo TITLE_SELECT_LANGUAGE; ?></strong><br />
              <img src="images/break-el.gif" width="100%" height="1" alt="" /><br />
              <?php if ($messageStack->size('index') > 0) { ?>
                  <br />
                    <table border="0" cellpadding="0" cellspacing="0" bgcolor="f4f4f4">
                      <tr>
                        <td><?php echo $messageStack->output('index'); ?></td>
                      </tr>
                    </table>
              <?php } ?>
              <form name="language" method="post" action="index.php">
                <table width="300" border="0" cellpadding="0" cellspacing="4">
                  <tr>
                    <td width="98"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" /><?php echo TEXT_GERMAN; ?></td>
                    <td width="192"><img src="images/icons/icon-deu.gif" width="30" height="16" alt="" />
                      <?php echo xtc_draw_radio_field_installer('lg', 'german', (($lang=='german')?true:false), 'onclick="self.location.href=\''.xtc_href_link('index.php', 'lg=german', 'NONSSL').'\'"'); ?>
                    </td>
                  </tr>
                  <tr>
                    <td><img src="images/icons/arrow02.gif" width="13" height="6" alt="" /><?php echo TEXT_ENGLISH; ?></td>
                    <td><img src="images/icons/icon-eng.gif" width="30" height="16" alt="" />
                      <?php echo xtc_draw_radio_field_installer('lg', 'english', (($lang=='english')?true:false), 'onclick="self.location.href=\''.xtc_href_link('index.php', 'lg=english', 'NONSSL').'\'"'); ?> 
                    </td>
                  </tr>
                </table>
                <br/>
                <strong><?php echo TITLE_SELECT_CHARSET; ?></strong><br />
                <img src="images/break-el.gif" width="100%" height="1" alt="" /><br />
                <table width="300" border="0" cellpadding="0" cellspacing="4">
                  <tr>
                    <td width="98"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />ISO-8859-15</td>
                    <td width="192">
                      <?php echo xtc_draw_radio_field_installer('char', 'latin1', ((INSTALL_CHARSET=='latin1')?true:false)); ?>
                    </td>
                  </tr>
                  <tr>
                    <td><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />UTF-8 (Beta)</td>
                    <td>
                    <?php echo xtc_draw_radio_field_installer('char', 'utf8', ((INSTALL_CHARSET=='utf8')?true:false)); ?> </td>
                  </tr>
                </table>
                <?php  if($upgrade) { ?>
                  <br/>
                  <strong><?php echo TITLE_UPGRADE; ?></strong><br />
                  <img src="images/break-el.gif" width="100%" height="1" alt="" /><br />
                  <table width="100%" border="0" cellpadding="0" cellspacing="4">
                    <tr>
                      <td style="padding-left:4px"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" /></td>
                      <td  style="padding-right:10px"><?php echo xtc_draw_checkbox_field_installer('db_upgrade','',true); ?></td>
                      <td><?php echo TEXT_DB_UPGRADE; ?></td>
                    </tr>
                  </table>
                  <br/>
                <?php } ?>
                <?php // BOF - web28 - 2010.12.13 - NEW db-upgrade ?>
                  <?php if ($error_flag==false || $continue==true) { ?>
                  <input type="hidden" name="action" value="process" />
                  <table width="95%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td align="right"><input type="image" src="images/buttons/<?php echo $lang;?>/button_continue.gif"></td>
                    </tr>
                  </table>
                  <?php // EOF - web28 - 2010.12.13 - NEW db-upgrade ?>
                <?php } else {
                  echo '<br/><strong>'. TEXT_INSTALLATION_NOT_POSSIBLE .'</strong><br/><br/><input type="image" src="images/buttons/'.$lang.'/button_retry.gif" alt="refresh page">';
                } ?>
                <br />
              </form>
            </td>
          </tr>
        </table>
      </tr>
    </table>
    <br />
    <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
    <div align="center" style="padding-top:5px; font-size:11px;"><?php echo INSTALLER_VERSION; ?></div>
    <div align="center" style="padding-top:5px; font-size:11px;"><?php echo $support; ?></div>
  </body>
</html>