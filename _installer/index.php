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
  $upgrade = true;;
  if (DB_SERVER_USERNAME == 'root' && DB_SERVER_PASSWORD == 'root' && DB_DATABASE == 'modified-shop') {
    $upgrade = false;
  }
  if (isset($_POST['db_upgrade']) && ($_POST['db_upgrade'] == true)) {
    xtc_redirect('db_upgrade.php?upgrade_redir=1', '', 'NONSSL');
  }
  //EOF - web28 - 2010-12-13 - redirect to db_upgrade.php, if database is already set up (do an update instead of a new installation)

  $messageStack = new messageStack();
  $process = false;

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $process = true;
    $_SESSION['language'] = xtc_db_prepare_input($_POST['LANGUAGE']);
    $error = false;
    if ( ($_SESSION['language'] != 'german') && ($_SESSION['language'] != 'english') ) {
      $error = true;
      $messageStack->add('index', SELECT_LANGUAGE_ERROR);
    }
    if ($error == false) {
      xtc_redirect(xtc_href_link('install_step1.php?lg='. xtc_db_prepare_input($_POST['LANGUAGE']), '', 'NONSSL'));
    }
  }

  include ('includes/check_permissions.php');
  include ('includes/check_requirements.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $charset;?>" />
    <title>modified eCommerce Shopsoftware Installer</title>
    <style type="text/css">
      body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
      table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
      h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
      .popout { border:1px solid #ff0000; margin:0; padding:10px; }
      .popout .left { float: left; width: 300px; padding: 0 10px; text-align: justify; }
      .popout .right { float: right; width: 300px; padding: 0 10px; }
      label { display: block; width: 290px; padding: 5px 0 2px 0; }
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
      <td align="right" valign="top">
        <br />
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td><img src="images/title_index.gif" width="705" height="180" border="0" alt="" /><br />
              <br /><br /><div style="border:1px solid #ccc; background:#fff; padding:10px;"><?php echo TEXT_WELCOME_INDEX; ?><br /><br /><a href="http://www.modified-shop.org/spenden" target="_blank"><img src="https://www.paypal.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" alt="<?php echo TEXT_INFO_DONATIONS_IMG_ALT; ?>" border="0" /></a></div><br /><br />
            </td>
          </tr>
            <?php
              if ($error_flag==true) {
            ?>
            <tr>
              <td>
                <h1><?php echo TEXT_CHMOD_REMARK_HEADLINE; ?>:</h1>
                <div style="background:#fff; padding:10px; border:1px solid #ccc">
                  <?php echo TEXT_CHMOD_REMARK; ?>
                </div><br />
                <div style="background:#ff0000; color:#ffffff; padding:10px; border:1px solid #cf0000">
                  <?php echo $message; ?>
                </div>
              </td>
            </tr>
            <?php
            // BOC flth new permission fix system
            if ($folder_flag || $file_flag) {
              $host = isset($_POST['path']) ? $_POST['host'] : rtrim(getenv('HTTP_HOST'),'/');
              $path = isset($_POST['path']) ? $_POST['path'] : basename(DIR_FS_CATALOG).'/';
              $port = isset($_POST['port']) ? $_POST['port'] : '21';
              $login = isset($_POST['login']) ? $_POST['login'] : '';
              ?>
              <tr>
                <td>
                  <div id="permissions" class="popout">
                      <?php if (!empty($ftp_message)) echo $ftp_message; ?>
                      <div class="left" >
                        <?php echo FTP_CHANGE_PERM_EXPLAIN; ?><br />
                      </div>
                      <div class="right">
                        <form name="ftp" action="index.php" method="post">
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
            // EOC flth new permission fix system
            ?>
          <?php } ?>
          <?php if ($ok_message!='') { ?>
            <tr>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td style="border: 1px solid; border-color: #4CC534; padding:10px;" bgcolor="#C2FFB6">
                <strong><?php echo TEXT_CHECKING; ?>:</strong>
                <br /><br />
                <?php
                  echo $ok_message;
                ?>
              </td>
            </tr>
          <?php } ?>
        </table>
        <p><img src="images/break-el.gif" width="100%" height="1" alt="" /></p>
        <table width="98%" border="0" align="right" cellpadding="0" cellspacing="0">
          <tr>
            <td>
              <strong><?php echo TITLE_SELECT_LANGUAGE; ?></strong><br />
              <img src="images/break-el.gif" width="100%" height="1" alt="" /><br />
              <?php if ($messageStack->size('index') > 0) { ?>
                  <br />
                    <table border="0" cellpadding="0" cellspacing="0" bgcolor="f3f3f3">
                      <tr>
                        <td><?php echo $messageStack->output('index'); ?></td>
                      </tr>
                    </table>
              <?php } ?>
              <form name="language" method="post" action="index.php">
                <table width="300" border="0" cellpadding="0" cellspacing="4">
                  <tr>
                    <td width="98"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />Deutsch</td>
                    <td width="192"><img src="images/icons/icon-deu.gif" width="30" height="16" alt="" />
                      <?php echo xtc_draw_radio_field_installer('LANGUAGE', 'german'); ?>
                    </td>
                  </tr>
                  <tr>
                    <td><img src="images/icons/arrow02.gif" width="13" height="6" alt="" />English</td>
                    <td><img src="images/icons/icon-eng.gif" width="30" height="16" alt="" />
                    <?php echo xtc_draw_radio_field_installer('LANGUAGE', 'english'); ?> </td>
                  </tr>
                </table>
                <?php // BOF - web28 - 2010.12.13 - NEW db-upgrade ?>
                  <?php if ($error_flag==false) { ?>
                  <input type="hidden" name="action" value="process" />
                  <table width="95%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <?php /* if($upgrade) { ?>
                        <td style="padding-left:4px"><img src="images/icons/arrow02.gif" width="13" height="6" alt="" /></td>
                        <td><?php echo TEXT_DB_UPGRADE; ?></td>
                        <td  style="padding-right:10px"><?php echo xtc_draw_checkbox_field_installer('db_upgrade','',true); //enable upgrade by default ?></td>
                      <?php } */ ?>
                      <td align="right"><input type="image" src="buttons/<?php echo $lang;?>/button_continue.gif"></td>
                    </tr>
                  </table>
                  <?php // EOF - web28 - 2010.12.13 - NEW db-upgrade ?>
                <?php } else {
                  echo '<br/><strong>'. TEXT_INSTALLATION_NOT_POSSIBLE .'</strong><br/><br/><a href="index.php"><img src="buttons/'.$lang.'/button_retry.gif" border="0" alt="refresh page"></a>';
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