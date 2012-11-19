<?php
  /* --------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   --------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (install_step5.php,v 1.25 2003/08/24); www.nextcommerce.org
   (c) 2003 XT-Commerce (configure.php)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application.php');

  //BOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php
  //include('language/'.$_SESSION['language'].'.php');
  include('language/'.$lang.'.php');
  //EOF - web28 - 2010.02.11 - NEW LANGUAGE HANDLING IN application.php

  //BOF - web28 - 2010-06-14 - Fix possible end slash
  $http_server = rtrim($_POST['HTTP_SERVER'], '/');
  $https_server = rtrim($_POST['HTTPS_SERVER'], '/');
  //EOF - web28 - 2010-06-14 - Fix possible end slash

  function phpLinkCheck($url, $r = FALSE) {
  /*  Purpose: Check HTTP Links
   *  Usage:   $var = phpLinkCheck(absoluteURI)
   *           $var["Status-Code"] will return the HTTP status code
   *           (e.g. 200 or 404). In case of a 3xx code (redirection)
   *           $var["Location-Status-Code"] will contain the status
   *           code of the new loaction.
   *           See print_r($var) for the complete result
   *
   *  Author:  Johannes Froemter <j-f@gmx.net>
   *  Date:    2001-04-14
   *  Version: 0.1 (currently requires PHP4)
   */

  $url = trim($url);

  //http oder https entfernen
  $http = array('http://', 'https://');
  $urltest = str_replace($http,'',$url);
  //Auf // testen
  if (strpos($urltest, '//') !== false)
    return false;
  //Auf falsches Installer Verzeichnis testen
  if (strpos($urltest, DIR_MODIFIED_INSTALLER) !== false)
    return false;

  if (!preg_match("=://=", $url))
    $url = "http://$url";
  $url = parse_url($url);
  $http["Parsed_URL"] = $url;
  if (strtolower($url["scheme"]) != "http")
    return FALSE;
  if (!isset($url["port"]))
    $url["port"] = 80;
  if (!isset($url["path"]))
    $url["path"] = "/";
  $fp = @fsockopen($url["host"], $url["port"], $errno, $errstr, 30);
  if (!$fp) {
    $http["Status-Code"] = '550';  // unknown host // FALSE;
    return $http;
  } else {
    $head = "";
    $httpRequest = "HEAD ". $url["path"] ." HTTP/1.1\r\n"
                  ."Host: ". $url["host"] ."\r\n"
                  ."Connection: close\r\n\r\n";
    fputs($fp, $httpRequest);
    while(!feof($fp))
      $head .= fgets($fp, 1024);
    fclose($fp);
    preg_match("=^(HTTP/\d+\.\d+) (\d{3}) ([^\r\n]*)=", $head, $matches);
    $http["Status-Line"] = $matches[0];
    $http["HTTP-Version"] = $matches[1];
    $http["Status-Code"] = $matches[2];
    $http["Reason-Phrase"] = $matches[3];
    if ($r)
      return $http["Status-Code"];
    $rclass = array("Informational", "Success",
                    "Redirection", "Client Error",
                    "Server Error");
    $http["Response-Class"] = $rclass[$http["Status-Code"][0] - 1];
    preg_match_all("=^(.+): ([^\r\n]*)=m", $head, $matches, PREG_SET_ORDER);
    foreach($matches as $line)
      $http[$line[1]] = $line[2];
    if ($http["Status-Code"][0] == 3)
      $http["Location-Status-Code"] = phpLinkCheck($http["Location"], TRUE);
    return $http;
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>modified eCommerce Shopsoftware Installer - STEP 5 / Write Config Files</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
    <table width="800" bgcolor="#f3f3f3" style="border:30px solid #fff;" height="80%" border="0" align="center" cellpadding="0" cellspacing="0">
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
                <img src="images/step5.gif" width="705" height="180" border="0"><br />
                <br />
                <br />
              </td>
            </tr>
          </table>
          <table width="95%" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <div style="border:1px solid #ccc; background:#fff; padding:10px;">
                  <?php
                    $db = array();
                    $db['DB_SERVER'] = trim(stripslashes($_POST['DB_SERVER']));
                    $db['DB_SERVER_USERNAME'] = trim(stripslashes($_POST['DB_SERVER_USERNAME']));
                    $db['DB_SERVER_PASSWORD'] = trim(stripslashes($_POST['DB_SERVER_PASSWORD']));
                    $db['DB_DATABASE'] = trim(stripslashes($_POST['DB_DATABASE']));
                    $db_error = false;
                    xtc_db_connect_installer($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);
                    if (!$db_error) {
                      xtc_db_test_connection($db['DB_DATABASE']);
                    }
                    if ($db_error) {
                  ?>
                  <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <img src="images/icons/error.gif" width="16" height="16"><strong><?php echo TEXT_CONNECTION_ERROR; ?></strong>
                      </td>
                    </tr>
                  </table>
                  <p><strong><?php echo TEXT_DB_ERROR; ?></strong></p>
                  <table border="0" cellpadding="0" cellspacing="0" bgcolor="f3f3f3">
                    <tr>
                      <td><b><?php echo $db_error; ?></b></td>
                    </tr>
                  </table>
                  <p><?php echo TEXT_DB_ERROR_1; ?></p>
                  <p><?php echo TEXT_DB_ERROR_2; ?></p>
                  <form name="install" action="install_step4.php" method="post">
                    <?php draw_hidden_fields(); ?>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                      <tr>
                        <td align="center"><a href="index.php"><img src="buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                        <td align="center"><input type="image" src="buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
                      </tr>
                    </table>
                  </form>
                <?php
                  } else {
                    //Testpfad
                    $url = $http_server . $_POST['DIR_WS_CATALOG'] . 'robots.txt';
                    $link_status = phpLinkCheck($url);
                    if ($link_status['Status-Code'] == 550) {
                      $errmsg = 'HTTP Server: ' . $link_status['Parsed_URL']['host'] . ' unbekannt/unknown' . '   [ERROR: 550]';
                    } else if ($link_status['Status-Code'] == 404) {
                      $errmsg = $link_status['Parsed_URL']['host'] . $link_status['Parsed_URL']['path'] . '   [ERROR: 404]';
                    }
                    if ($link_status['Status-Code'] != 200) {
                      //Fehleranzeige
                      if (trim($errmsg) =='')
                        $errmsg = $url . '   [ERROR: '. $link_status['Status-Code'] .']';
                      ?>
                      <table width="100%" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td>
                            <img src="images/icons/error.gif" width="16" height="16"><strong>
                            <div style="color:#FC0000;"><?php echo TEXT_PATH_ERROR; ?></strong></div>
                          </td>
                        </tr>
                      </table>
                      <p><strong><div style="color:#FC0000;"><?php echo TEXT_PATH_ERROR2; ?></div></strong></p>
                      <table border="0" cellpadding="0" cellspacing="0" bgcolor="f3f3f3">
                        <tr>
                          <td><b><?php echo $errmsg;?></b></td>
                        </tr>
                      </table>
                      <p><?php echo TEXT_PATH_ERROR3;?></p>
                      <form name="install" action="install_step4.php" method="post">
                        <?php draw_hidden_fields(); ?>
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td align="center"><a href="index.php"><img src="buttons/<?php echo $lang;?>/button_cancel.gif" border="0" alt="Cancel"></a></td>
                            <td align="center"><input type="image" src="buttons/<?php echo $lang;?>/button_back.gif" border="0" alt="Back"></td>
                          </tr>
                        </table>
                      </form>
                      <?php
                        } else {
                          $file_contents = '<?php' . "\n" .
                                           '/* --------------------------------------------------------------' . "\n" .
                                           '' . "\n" .
                                           '  modified eCommerce Shopsoftware' . "\n" .
                                           '  http://www.modified.org-shop' . "\n" .
                                           '' . "\n" .
                                           '  Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware' . "\n" .
                                           '  Released under the GNU General Public License (Version 2)' . "\n" .
                                           '  [http://www.gnu.org/licenses/gpl-2.0.html]' . "\n" .
                                           '  --------------------------------------------------------------' . "\n" .
                                           '  based on:' . "\n" .
                                           '  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)' . "\n" .
                                           '  (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com' . "\n" .
                                           '  (c) 2003 XT-Commerce (configure.php)' . "\n" .
                                           '' . "\n" .
                                           '  Released under the GNU General Public License' . "\n" .
                                           '  --------------------------------------------------------------*/' . "\n" .
                                           '' . "\n" .
                                           '// Define the webserver and path parameters' . "\n" .
                                           '// * DIR_FS_* = Filesystem directories (local/physical)' . "\n" .
                                           '// * DIR_WS_* = Webserver directories (virtual/URL)' . "\n" .
                                           '  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost - should not be empty for productive servers' . "\n" .
                                           '  define(\'HTTPS_SERVER\', \'' . $https_server . '\'); // eg, https://localhost - should not be empty for productive servers' . "\n" .
                                           '  define(\'ENABLE_SSL\', ' . (($_POST['ENABLE_SSL'] == 'true') ? 'true' : 'false') . '); // secure webserver for checkout procedure?' . "\n" .
                                           //BOF - web28 - 2010.09.15 - using SSL proxy
                                           '  define(\'USE_SSL_PROXY\', ' . (($_POST['USE_SSL_PROXY'] == 'true') ? 'true' : 'false') . '); // using SSL proxy?' . "\n" .
                                           //EOF - web28 - 2010.09.15 - using SSL proxy
                                           '  define(\'DIR_WS_ADMIN\', \'admin/\');// relative admin path required' . "\n" .
                                           '  define(\'DIR_WS_CATALOG\', \'' . $_POST['DIR_WS_CATALOG'] . '\'); // absolute path required' . "\n" .
                                           //BOF - web28 - 2010.02.18 - STRATO ROOT PATCH
                                           '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . DIR_FS_DOCUMENT_ROOT.$local_install_path  . '\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG\', \'' . DIR_FS_DOCUMENT_ROOT.$local_install_path  . '\');' . "\n" .
                                           //EOF - web28 - 2010.02.18 - STRATO ROOT PATCH
                                           '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                                           '  define(\'DIR_WS_ORIGINAL_IMAGES\', DIR_WS_IMAGES .\'product_images/original_images/\');' . "\n" .
                                           '  define(\'DIR_WS_THUMBNAIL_IMAGES\', DIR_WS_IMAGES .\'product_images/thumbnail_images/\');' . "\n" .
                                           '  define(\'DIR_WS_INFO_IMAGES\', DIR_WS_IMAGES .\'product_images/info_images/\');' . "\n" .
                                           '  define(\'DIR_WS_POPUP_IMAGES\', DIR_WS_IMAGES .\'product_images/popup_images/\');' . "\n" .
                                           '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                                           '  define(\'DIR_WS_INCLUDES\',DIR_FS_DOCUMENT_ROOT. \'includes/\');' . "\n" .
                                           '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                                           '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                                           '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                                           '  define(\'DIR_WS_LANGUAGES\', DIR_FS_CATALOG . \'lang/\');' . "\n" .
                                           //BOF - DokuMan - 2011-01-25 - Add EXTERNAL-directories to installer
                                           '  define(\'DIR_WS_EXTERNAL\', DIR_WS_CATALOG . \'includes/external/\');' . "\n" .
                                           '  define(\'DIR_FS_EXTERNAL\', DIR_FS_CATALOG . \'includes/external/\');' . "\n" .
                                           //EOF - DokuMan - 2011-01-25 - Add EXTERNAL-directories to installer
                                           '' . "\n" .
                                           '  define(\'DIR_WS_DOWNLOAD_PUBLIC\', DIR_WS_CATALOG . \'pub/\');' . "\n" .
                                           '  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
                                           '  define(\'DIR_FS_DOWNLOAD_PUBLIC\', DIR_FS_CATALOG . \'pub/\');' . "\n" .
                                           '  define(\'DIR_FS_INC\', DIR_FS_CATALOG . \'inc/\');' . "\n" .
                                           '' . "\n" .
                                           '// define our database connection' . "\n" .
                                           '  define(\'DB_SERVER\', \'' . $_POST['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . "\n" .
                                           '  define(\'DB_SERVER_USERNAME\', \'' . $_POST['DB_SERVER_USERNAME'] . '\');' . "\n" .
                                           '  define(\'DB_SERVER_PASSWORD\', \'' . $_POST['DB_SERVER_PASSWORD']. '\');' . "\n" .
                                           '  define(\'DB_DATABASE\', \'' . $_POST['DB_DATABASE']. '\');' . "\n" .
                                           '  define(\'USE_PCONNECT\', \'' . (($_POST['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persistent connections?' . "\n" .
                                           '  define(\'STORE_SESSIONS\', \'' . (($_POST['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . "\n" .                     '?>';
                          $fp = fopen(DIR_FS_CATALOG . 'includes/configure.php', 'w');
                          fputs($fp, $file_contents);
                          fclose($fp);

                          // REM - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure

                          //create a configure.php
                          $file_contents = '<?php' . "\n" .
                                           '/* --------------------------------------------------------------' . "\n" .
                                           '' . "\n" .
                                           '  modified eCommerce Shopsoftware' . "\n" .
                                           '  http://www.modified.org-shop' . "\n" .
                                           '' . "\n" .
                                           '  Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware' . "\n" .
                                           '  Released under the GNU General Public License (Version 2)' . "\n" .
                                           '  [http://www.gnu.org/licenses/gpl-2.0.html]' . "\n" .
                                           '  --------------------------------------------------------------' . "\n" .
                                           '  based on:' . "\n" .
                                           '  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)' . "\n" .
                                           '  (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com' . "\n" .
                                           '  (c) 2003 XT-Commerce (configure.php)' . "\n" .
                                           '' . "\n" .
                                           '  Released under the GNU General Public License' . "\n" .
                                           '  --------------------------------------------------------------*/' . "\n" .
                                           '' . "\n" .
                                           '// Define the webserver and path parameters' . "\n" .
                                           '// * DIR_FS_* = Filesystem directories (local/physical)' . "\n" .
                                           '// * DIR_WS_* = Webserver directories (virtual/URL)' . "\n" .
                                           '  define(\'HTTP_SERVER\', \'' . $http_server . '\'); // eg, http://localhost or - https://localhost should not be empty for productive servers' . "\n" .
                                           '  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
                                           '  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n" .
                                           '  define(\'ENABLE_SSL_CATALOG\', \'' . (($_POST['ENABLE_SSL'] == 'true') ? 'true' : 'false') . '\'); // secure webserver for catalog module' . "\n" .
                                           //BOF - web28 - 2010.09.15 - using SSL proxy
                                           '  define(\'USE_SSL_PROXY\', ' . (($_POST['USE_SSL_PROXY'] == 'true') ? 'true' : 'false') . '); // using SSL proxy?' . "\n" .
                                           //EOF - web28 - 2010.09.15 - using SSL proxy
                                           //BOF - web28 - 2010.02.18 - STRATO ROOT PATCH
                                           '  define(\'DIR_FS_DOCUMENT_ROOT\', \'' . DIR_FS_DOCUMENT_ROOT.$local_install_path  . '\'); // where the pages are located on the server' . "\n" .
                                           '  define(\'DIR_WS_ADMIN\', \'' . $_POST['DIR_WS_CATALOG'] .'admin/' . '\'); // absolute path required' . "\n" .
                                           '  define(\'DIR_FS_ADMIN\', \'' . DIR_FS_DOCUMENT_ROOT.$local_install_path .'admin/' . '\'); // absolute pate required' . "\n" .
                                           '  define(\'DIR_WS_CATALOG\', \'' . $_POST['DIR_WS_CATALOG'] . '\'); // absolute path required' . "\n" .
                                           '  define(\'DIR_FS_CATALOG\', \'' . DIR_FS_DOCUMENT_ROOT.$local_install_path  . '\'); // absolute path required' . "\n" .
                                           //EOF - web28 - 2010.02.18 - STRATO ROOT PATCH
                                           '  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_IMAGES\', DIR_FS_CATALOG . \'images/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_ORIGINAL_IMAGES\', DIR_FS_CATALOG_IMAGES .\'product_images/original_images/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_THUMBNAIL_IMAGES\', DIR_FS_CATALOG_IMAGES .\'product_images/thumbnail_images/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_INFO_IMAGES\', DIR_FS_CATALOG_IMAGES .\'product_images/info_images/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_POPUP_IMAGES\', DIR_FS_CATALOG_IMAGES .\'product_images/popup_images/\');' . "\n" .
                                           '  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
                                           '  define(\'DIR_WS_CATALOG_IMAGES\', DIR_WS_CATALOG . \'images/\');' . "\n" .
                                           '  define(\'DIR_WS_CATALOG_ORIGINAL_IMAGES\', DIR_WS_CATALOG_IMAGES .\'product_images/original_images/\');' . "\n" .
                                           '  define(\'DIR_WS_CATALOG_THUMBNAIL_IMAGES\', DIR_WS_CATALOG_IMAGES .\'product_images/thumbnail_images/\');' . "\n" .
                                           '  define(\'DIR_WS_CATALOG_INFO_IMAGES\', DIR_WS_CATALOG_IMAGES .\'product_images/info_images/\');' . "\n" .
                                           '  define(\'DIR_WS_CATALOG_POPUP_IMAGES\', DIR_WS_CATALOG_IMAGES .\'product_images/popup_images/\');' . "\n" .
                                           '  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
                                           '  define(\'DIR_WS_BOXES\', DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
                                           '  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
                                           '  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
                                           '  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
                                           '  define(\'DIR_WS_LANGUAGES\', DIR_WS_CATALOG. \'lang/\');' . "\n" .
                                           '  define(\'DIR_FS_LANGUAGES\', DIR_FS_CATALOG. \'lang/\');' . "\n" .
                                           '  define(\'DIR_FS_CATALOG_MODULES\', DIR_FS_CATALOG . \'includes/modules/\');' . "\n" .
                                           '  define(\'DIR_FS_BACKUP\', DIR_FS_ADMIN . \'backups/\');' . "\n" .
                                           '  define(\'DIR_FS_INC\', DIR_FS_CATALOG . \'inc/\');' . "\n" .
                                           //BOF - DokuMan - 2011-01-25 - Add EXTERNAL-directories to installer
                                           '  define(\'DIR_WS_EXTERNAL\', DIR_WS_CATALOG . \'includes/external/\');' . "\n" .
                                           '  define(\'DIR_FS_EXTERNAL\', DIR_FS_CATALOG . \'includes/external/\');' . "\n" .
                                           //'  define(\'DIR_WS_FILEMANAGER\', DIR_WS_MODULES . \'fckeditor/editor/filemanager/browser/default/\');' . "\n" .
                                           '  define(\'DIR_WS_FILEMANAGER\', DIR_WS_EXTERNAL . \'fckeditor/editor/filemanager/browser/default/\');' . "\n" .
                                           //EOF - DokuMan - 2011-01-25 - Add EXTERNAL-directories to installer
                                           '' . "\n" .
                                           '// define our database connection' . "\n" .
                                           '  define(\'DB_SERVER\', \'' . $_POST['DB_SERVER'] . '\'); // eg, localhost - should not be empty for productive servers' . "\n" .
                                           '  define(\'DB_SERVER_USERNAME\', \'' . $_POST['DB_SERVER_USERNAME'] . '\');' . "\n" .
                                           '  define(\'DB_SERVER_PASSWORD\', \'' . $_POST['DB_SERVER_PASSWORD']. '\');' . "\n" .
                                           '  define(\'DB_DATABASE\', \'' . $_POST['DB_DATABASE']. '\');' . "\n" .
                                           '  define(\'USE_PCONNECT\', \'' . (($_POST['USE_PCONNECT'] == 'true') ? 'true' : 'false') . '\'); // use persisstent connections?' . "\n" .
                                           '  define(\'STORE_SESSIONS\', \'' . (($_POST['STORE_SESSIONS'] == 'files') ? '' : 'mysql') . '\'); // leave empty \'\' for default handler or set to \'mysql\'' . "\n" .
                                           '' . "\n" .
                                           '?>';
                          $fp = fopen(DIR_FS_CATALOG . 'admin/includes/configure.php', 'w');
                          fputs($fp, $file_contents);
                          fclose($fp);

                          // REM - 2011-10-20 - h-h-h - Remove/comment out unneeded secondary configure

                          // BOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL
                          $step = ($_POST['install_db'] == 1) ? 'install_step6' : 'install_finished';
                          //EOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL
                        ?>
                        <center>
                          <font color="#000000" size="2" face="Verdana, Arial, Helvetica, sans-serif">
                            <br />
                            <br />
                            <?php echo TEXT_WS_CONFIGURATION_SUCCESS; ?>
                          </font>
                        </center>
                        <br />
                        <br />
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                          <?php //// BOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL?>
                            <td align="center">
                              <a href="<?php echo $step;?>.php?lg=<?php echo $lang; ?>">
                                <img src="buttons/<?php echo $lang;?>/button_continue.gif" border="0">
                              </a>
                            </td>
                          <?php //// BOF - web28 - 2010-03-18 NEW HANDLING FOR NO DB INSTALL?>
                          </tr>
                        </table>
                        <br />
                        <br />
                      </form>
                    <?php
                    }
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