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

// default time zone
if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
  date_default_timezone_set('Europe/Berlin');
}

// new error handling
if (is_file(DIR_WS_INCLUDES.'error_reporting.php')) {
  require_once (DIR_WS_INCLUDES.'error_reporting.php');
}

// turn off magic-quotes support, for both runtime and sybase, as both will cause problems if enabled
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);
if (version_compare(PHP_VERSION, 5.4, '<') && @ini_get('magic_quotes_sybase') != 0) @ini_set('magic_quotes_sybase', 0);

// include functions
require_once(DIR_FS_INC.'auto_include.inc.php');
require_once(DIR_WS_INCLUDES . 'database_tables.php');

// Database
defined('DB_MYSQL_TYPE') OR define('DB_MYSQL_TYPE', 'mysql');
require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
require_once (DIR_FS_INC.'db_functions.inc.php');

// make a connection to the database... now
xtc_db_connect() or die('Unable to connect to database server!');

// load configuration
$configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
while ($configuration = xtc_db_fetch_array($configuration_query)) {
  defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
}

// include functions
require_once(DIR_FS_DOCUMENT_ROOT.'_installer/includes/functions.php');

// set all files to be deleted
$unlink_file = array();
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/includes/delete_files.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/includes/delete_files.php');
}

// set all directories to be deleted
$unlink_dir = array();                
if (is_file(DIR_FS_DOCUMENT_ROOT.'_installer/includes/delete_dirs.php')) {
  include(DIR_FS_DOCUMENT_ROOT.'_installer/includes/delete_dirs.php');
}

$lang = '';
if (isset($_GET['lg']) && $_GET['lg'] != '') {
  $lang = $_GET['lg'];
}
if (isset($_POST['lg']) && $_POST['lg'] != '') {
  $lang = $_POST['lg'];
}
if ($lang == '' || ($lang != 'german' && $lang != 'english')) {
  preg_match("/^([a-z]+)-?([^,;]*)/i", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $browser_lang);
  switch (strtolower($browser_lang[1])) {
    case 'de':
      $lang = 'german';
      break;
    default:
      $lang = 'english';
      break;
  }
}
include(DIR_FS_DOCUMENT_ROOT.'_installer/language/'.$lang.'.php');

$error='';
$success='';
$clean = false;
if (isset($_POST['update']) && $_POST['update']=='true') {

  switch ($_GET['action']) {
  
    case 'db_update':
      include(DIR_FS_DOCUMENT_ROOT.'_installer/includes/update_action.php');
      break;
    
    case 'sql_update':
      foreach ($_POST['sql'] as $sql_update) {
        sql_update($sql_update);
      }
      clear_dir(DIR_FS_DOCUMENT_ROOT.'cache/');
      clear_dir(DIR_FS_DOCUMENT_ROOT.'templates_c/');
      break;

    case 'sql_manual':
      sql_update($_POST['sql_manual'], true);
      clear_dir(DIR_FS_DOCUMENT_ROOT.'cache/');
      clear_dir(DIR_FS_DOCUMENT_ROOT.'templates_c/');
      break;
      
    case 'unlink':
      if (count($unlink_file) > 0) {
        foreach ($unlink_file as $unlink) {
          if (trim($unlink) != '' && is_file(DIR_FS_DOCUMENT_ROOT.$unlink)) {  
            @unlink(DIR_FS_DOCUMENT_ROOT.$unlink) ? $success.=$unlink.'<br />' : $error.=$unlink.'<br />';
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
      clear_dir(DIR_FS_DOCUMENT_ROOT.'cache/');
      clear_dir(DIR_FS_DOCUMENT_ROOT.'templates_c/');
      break;  
  
  }

  if (empty($error)) {
    $clean = true;
  }
}

  $charset = 'iso-8859-15';
  // set default charset
  @ini_set('default_charset', $charset);
  require (DIR_FS_DOCUMENT_ROOT.'_installer/includes/header.php');
?>
  <table width="803" style="border:10px solid #fff;" bgcolor="#ffffff" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="95" colspan="2">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td><img src="http://www.modified-shop.org/forum/Themes/modified/images/logo.png" alt="modified eCommerce Shopsoftware" /></td>
            <td style="vertical-align:top;"><a style="float:right;margin-top: 9px;" href="<?php echo dirname($_SERVER['PHP_SELF']).'/index.php?lg='.$lang; ?>"><img style="border: 0;" src="images/buttons/<?php echo $lang;?>/button_installer.gif" /></a></td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td height="20px" colspan="2">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2" height="20px" style="border-top:0px solid #ccc; width:100%;">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2">
              <div style="border:1px solid #ccc; background:#f4f4f4; padding:10px;">
                <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                  <?php
                  switch ($_GET['action']) {
                    case 'unlink':
                      if (!empty($success)) {
                      ?>
                      <tr>
                        <td valign="top"><?php echo TITLE_DELETE_SUCCESS; ?></td>
                        <td><?php echo $success; ?></td>
                      </tr>
                      <?php } elseif ($clean === false && !$_POST) { ?>
                      <form name="update" method="post">
                      <tr>
                        <td width="20%" valign="top"><?php echo TITLE_DELETE_FILES; ?></td>
                        <td><?php echo implode('<br />', $unlink_file); ?></td>
                      </tr>
                      <?php }
                      if (!empty($error)) {
                      ?>
                      <tr>
                        <td width="20%" valign="top"><?php echo TITLE_DELETE_MANUALLY; ?></td>
                        <td><?php echo $error; ?></td>
                      </tr>
                      <?php } elseif ($clean === false && !$_POST) { ?>
                      <tr>
                        <td width="20%" valign="top"><?php echo TITLE_DELETE_DIRS; ?></td>
                        <td><?php echo implode('<br />', $unlink_dir); ?></td>
                      </tr>
                      <?php } elseif ($clean === true) { ?>
                      <tr>
                        <td valign="top" colspan="2" align="center" bgcolor="#d4ebcb" style="border: 1px solid; border-color: #b2dba1; padding:10px; color: #3C763D;"><?php echo TEXT_DELETE_SUCCESS; ?></td>
                      </tr>
                      <?php } 
                      break;

                    case 'db_update':
                      if (!empty($success)) {
                        ?>
                        <tr>
                          <td width="20%" valign="top"><?php echo TITLE_PERFORM_SUCCESS; ?></td>
                          <td><?php echo $success; ?></td>
                        </tr>
                        <?php 
                      } else {
                        echo '<form name="update" method="post">';
                        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                          echo '<p style="text-align:center;">'.TEXT_FINISHED_DB_STRUCTURE_UPDATE.'</p>';
                        } else {
                          echo '<p style="text-align:center;">'.TEXT_START_DB_STRUCTURE_UPDATE.'</p>';
                        }
                      }
                      break;

                    case 'sql_update':
                      if (!empty($success)) {
                        ?>
                        <tr>
                          <td width="20%" valign="top"><?php echo TITLE_PERFORM_SUCCESS; ?></td>
                          <td><?php echo $success; ?></td>
                        </tr>
                        <?php 
                      } else {
                        echo '<form name="update" method="post">';
                        $sql_files_array = array();
                        $d = opendir(DIR_FS_DOCUMENT_ROOT.'_installer/update/');
                        while($f = readdir($d)) {
                          //if ((strpos($f, '.sql') !== false && strpos($f, 'update') !== false) || $f == 'banktransfer_blz.sql') {
                          if (strpos($f, '.sql') !== false && strpos($f, 'update') !== false) {
                            $sql_files_array[] = $f;
                          }
                        }
                        sort($sql_files_array);              
                        if (count($sql_files_array) > 0) {
                          foreach ($sql_files_array as $sql_files) {
                            echo '<input type="checkbox" name="sql[]" value="'.DIR_FS_DOCUMENT_ROOT.'_installer/update/'.$sql_files.'"> '.$sql_files.'<br />';
                          }
                        }
                      }
                      break;
          
                    case 'sql_manual':
                      if (!empty($success)) {
                        unset($_POST['sql_manual']);
                        ?>
                        <tr>
                          <td valign="top"><?php echo TITLE_PERFORM_SUCCESS; ?></td>
                          <td><?php echo $success; ?></td>
                        </tr>
                        <?php 
                      }
                      echo '<form name="update" method="post">';
                      echo '<tr><td colspan="2"><div style="background:#F2DEDE; color:#A94442; padding:10px; border:1px solid #DCA7A7">'.TEXT_PERFORM_MANUAL_SQL_UPDATE.'</div><br /><textarea name="sql_manual" style="width:100%; height:300px;">'.(isset($_POST['sql_manual']) ? $_POST['sql_manual'] : '').'</textarea></td></tr>';
                      break;
              
                    default:
                      echo '<form name="update" method="get">' .
                           '<input type="hidden" name="lg" value="'.$lang.'" />' .
                           '<input type="radio" name="action" value="unlink">' . TITLE_PERFORM_DELETE_FILES_AND_DIRS .
                           '<input type="radio" name="action" value="db_update">' . TITLE_PERFORM_DB_STRUCTURE_UPDATE .
                           '<input type="radio" name="action" value="sql_update">' . TITLE_PERFORM_DB_UPDATE .
                           '<input type="radio" name="action" value="sql_manual">' . TITLE_PERFORM_MANUAL_SQL_UPDATE;
                    break;
                  }
                  ?>
                </table>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td height="20px" colspan="2">
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2" height="20px" style="border-top:0px solid #ccc; width:100%;">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <table width="95%" cellspacing="0" cellpadding="0" border="0" align="center">
          <tr>
            <td>
              <?php
              switch ($_GET['action']) {
                case 'unlink':
                  echo '<a href="'.$_SERVER['PHP_SELF'].'?lg='.$lang.'"><img style="border: 0;" src="images/buttons/'.$lang.'/button_cancel.gif" /></a>';
                  if ($clean === false && !$_POST) {
                    echo '<input type="hidden" name="update" value="true" />' .
                         '<input style="float:right" type="image" src="images/buttons/'.$lang.'/button_execute.gif">' .
                         '</form>';
                  }
                  break;
          
                case 'db_update':
                  echo '<a href="'.$_SERVER['PHP_SELF'].'?lg='.$lang.'"><img style="border: 0;" src="images/buttons/'.$lang.'/button_cancel.gif" /></a>';
                  if (!$clean) {
                    echo '<input type="hidden" name="update" value="true" />' .
                         '<input style="float:right" type="image" src="images/buttons/'.$lang.'/button_execute.gif">' .
                         '</form>';
                  }
                  break;

                case 'sql_update':
                  echo '<a href="'.$_SERVER['PHP_SELF'].'?lg='.$lang.'"><img style="border: 0;" src="images/buttons/'.$lang.'/button_cancel.gif" /></a>';
                  if (!$clean) {
                    echo '<input type="hidden" name="update" value="true" />' .
                         '<input style="float:right" type="image" src="images/buttons/'.$lang.'/button_execute.gif">' .
                         '</form>';
                  }
                  break;

                case 'sql_manual':
                  echo '<a href="'.$_SERVER['PHP_SELF'].'?lg='.$lang.'"><img style="border: 0;" src="images/buttons/'.$lang.'/button_cancel.gif" /></a>';
                  echo '<input type="hidden" name="update" value="true" />' .
                       '<input style="float:right" type="image" src="images/buttons/'.$lang.'/button_execute.gif">' .
                       '</form>';
                  break;
            
                default:
                  echo '<input type="image" src="images/buttons/'.$lang.'/button_continue.gif">' .
                       '</form>';
        
                  break;
              }
              ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
  </table>
  <br />
  <div align="center" style="font-family:Arial, sans-serif; font-size:11px;"><?php echo TEXT_FOOTER; ?></div>
  </body>
</html>