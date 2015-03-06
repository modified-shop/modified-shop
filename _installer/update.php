<?php
/* -----------------------------------------------------------------------------------------
   $Id: update.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   Stand 04.03.2012
   ---------------------------------------------------------------------------------------*/

error_reporting(0);
chdir('../');

// Set the local configuration parameters - mainly for developers or the main-configure
if (file_exists('includes/local/configure.php')) {
  include('includes/local/configure.php');
} else {
  require('includes/configure.php');
}

//check for modified 2.00
if (defined('DB_MYSQL_TYPE')) {
  // include functions
  require_once(DIR_FS_INC.'auto_include.inc.php');
  require_once(DIR_WS_INCLUDES . 'database_tables.php');

  // Database
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
} else {
  // include functions
  require_once(DIR_WS_INCLUDES . 'database_tables.php');

  // Database
  require_once(DIR_FS_INC . 'xtc_db_connect.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_close.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_error.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_query.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_queryCached.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_perform.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_fetch_array.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_num_rows.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_data_seek.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_insert_id.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_free_result.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_fetch_fields.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_output.inc.php');
  require_once(DIR_FS_INC . 'xtc_db_input.inc.php');
}


// include functions
require_once(DIR_FS_DOCUMENT_ROOT.'_installer/includes/functions.php');

// set all files to be deleted
$unlink_file = array();
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/delete_files.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/delete_files.php');
}

// set all directories to be deleted
$unlink_dir = array();                
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/delete_dir.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/delete_dir.php');
}

$error='';
$success='';
$clean = false;
if (isset($_POST['update']) && $_POST['update']=='true') {

  switch ($_GET['action']) {
  
    case 'sql_update':
      foreach ($_POST['sql'] as $sql_update) {
        sql_update($sql_update);
      }    
      break;
    
    case 'sql_manual':
      sql_update($_POST['sql_manual'], true);
      break;
      
    case 'unlink':
      if (count($unlink_file) > 0) {
        foreach ($unlink_file as $unlink) {
          if (trim($unlink) != '' && is_file(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
            @unlink(DIR_FS_DOCUMENT_ROOT.$unlink) ? $success.=$unlink.'<br/>' : $error.=$unlink.'<br/>';
          }
        }
      }
      if (count($unlink_dir) > 0) {
        foreach ($unlink_dir as $unlink) {
          if (trim($unlink) != '' && is_dir(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
            rrmdir(DIR_FS_DOCUMENT_ROOT.$unlink);
          }
        }
      }
      break;  
  
  }

  if (empty($error)) {
    $clean = true;
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>modified eCommerce Shopsoftware Updater</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
<style type="text/css">
body { background: #eee; font-family: Arial, sans-serif; font-size: 12px;}
table,td,div { font-family: Arial, sans-serif; font-size: 12px;}
h1 { font-size: 18px; margin: 0; padding: 0; margin-bottom: 10px; }
a {text-decoration: none;}
</style>
</head>

<body>
<table width="800" style="border:30px solid #fff;" border="0" align="center" cellpadding="20" cellspacing="0">
  <tr>
    <td height="95" colspan="2">
      <table border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td><img src="http://www.modified-shop.org/forum/Themes/modified/images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td colspan="2" height="20px" style="border-top:1px solid #ccc; width:100%;"></td></tr>
  <tr>
    <td colspan="2">
      <table width="100%" border="0" cellpadding="10" cellspacing="0">
        <?php
        switch ($_GET['action']) {
          case 'unlink':
            if (!empty($success)) {
            ?>
            <tr>
              <td valign="top">Erfolgreich gel&ouml;scht:</td>
              <td><?php echo $success; ?></td>
            </tr>
            <?php } elseif ($clean === false && !$_POST) { ?>
            <form name="update" method="post">
            <tr>
              <td valign="top">Diese Dateien m&uuml;ssen gel&ouml;scht werden:</td>
              <td><?php echo implode('<br/>', $unlink_file); ?></td>
            </tr>
            <?php }
            if (!empty($error)) {
            ?>
            <tr>
              <td valign="top">Bitte diese Dateien und Verzeichnisse manuell l&ouml;schen:</td>
              <td><?php echo $error; ?></td>
            </tr>
            <?php } elseif ($clean === false && !$_POST) { ?>
            <tr>
              <td valign="top">Diese Verzeichnisse m&uuml;ssen gel&ouml;scht werden:</td>
              <td><?php echo implode('<br/>', $unlink_dir); ?></td>
            </tr>
            <?php } elseif ($clean === true) { ?>
            <tr>
              <td valign="top" colspan="2" align="center" style="border:1px solid green; width:100%;">Es wurden die Dateien und Verzeichnisse erfolgreich gel&ouml;scht.<br/>Bitte stellen Sie sicher, dass auch die Datei &quot;update.php&quot; vom Server entfernt wurde.</td>
            </tr>
            <?php } 
            break;

          case 'sql_update':
            if (!empty($success)) {
              ?>
              <tr>
                <td valign="top">Erfolgreich ausgef&uuml;hrt:</td>
                <td><?php echo $success; ?></td>
              </tr>
              <?php 
            } else {
              echo '<form name="update" method="post">';
              $sql_files_array = array();
              $d = opendir(DIR_FS_DOCUMENT_ROOT.'_installer/');
              while($f = readdir($d)) {
                if ((strpos($f, '.sql') !== false && strpos($f, 'update') !== false) || $f == 'banktransfer_blz.sql') {
                  $sql_files_array[] = $f;
                }
              }
              sort($sql_files_array);              
              if (count($sql_files_array) > 0) {
                foreach ($sql_files_array as $sql_files) {
                  echo '<input type="checkbox" name="sql[]" value="'.DIR_FS_DOCUMENT_ROOT.'_installer/'.$sql_files.'"> '.$sql_files.'<br>';
                }
              }
            }
            break;
          
          case 'sql_manual':
            if (!empty($success)) {
              unset($_POST['sql_manual']);
              ?>
              <tr>
                <td valign="top">Erfolgreich ausgef&uuml;hrt:</td>
                <td><?php echo $success; ?></td>
              </tr>
              <?php 
            }
            echo '<form name="update" method="post">';
            echo '<tr><td colspan="2"><div style="width:100%; color:red; text-align:center">SQL Befehle m&uuml;ssen mit einem  ;  abgeschlossen werden !</div><br/><textarea name="sql_manual" style="width:100%; height:300px;">'.(isset($_POST['sql_manual']) ? $_POST['sql_manual'] : '').'</textarea></td></tr>';
            break;
              
          default:
            echo '<form name="update" method="get">' .
                 '<input type="radio" name="action" value="unlink"> Dateien und Verzeichnise l&ouml;schen<br>' .
                 '<input type="radio" name="action" value="sql_update"> Datenbank Update<br>' .
                 '<input type="radio" name="action" value="sql_manual"> Manuelle SQL';
          break;
        }
        ?>     
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php
      switch ($_GET['action']) {
        case 'unlink':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          if ($clean === false && !$_POST) {
            echo '<input type="hidden" name="update" value="true" />' .
                 '<input type="submit" value="Ausf&uuml;hren" />' .
                 '</form>';
          }
          break;
          
        case 'sql_update':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          if (!$clean) {
            echo '<input type="hidden" name="update" value="true" />' .
                 '<input type="submit" value="Ausf&uuml;hren" />' .
                 '</form>';
          }
          break;

        case 'sql_manual':
          echo '<a href="'.$_SERVER['PHP_SELF'].'"><input type="button" value="Zur&uuml;ck" /></a>';
          echo '<input type="hidden" name="update" value="true" />' .
               '<input type="submit" value="Ausf&uuml;hren" />' .
               '</form>';
          break;
            
        default:
          echo '<input type="submit" value="Ausf&uuml;hren" />' .
               '</form>';
        
          break;
      }
      ?>   
    </td>
  </tr>
</table>
<br />
<div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo '<a href="http://www.modified-shop.org" target="_blank"><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">' . '&nbsp;' . '&copy;2009-' . date('Y'); ?></div>
</body>
</html>