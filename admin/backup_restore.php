<?php
  /**************************************************************
  * XTC Datenbank Manager Version 2.00
  *(c) by  web28 - www.rpa-com.de
  * backup_restore.php
  * Backup pro Tabelle und limitierter Zeilenzahl (Neuladen der Seite) , einstellbar mit ANZAHL_ZEILEN_BKUP
  * Restore mit limitierter Zeilennanzahl aus SQL-Datei (Neuladen der Seite), einstellbar mit ANZAHL_ZEILEN
  * 2014-12-02 - fix TITLE, actual_table
  * 2014-09-14 - jquery ajax handling
  * 2010-09-09 - add set_admin_access
  * 2011-07-02 - Security Fix - PHP_SELF
  * 2011-09-13 - fix some PHP notices
  ***************************************************************/

  define ('VERSION', 'Database Restore Ver. 2.00');

  // ?file=dbd_mod105sp1b-20111123170925.sql.gz&action=restorenow

  define ('_VALID_XTC', true);
  define('RUN_MODE_ADMIN',true);

  // no error reporting
  error_reporting(0);

  //check for modified 2.00
  if(is_file('includes/paths.php')) {
      // Set the local configuration parameters - mainly for developers or the main-configure
      if (file_exists('../includes/local/configure.php')) {
        include('../includes/local/configure.php');
      } else {
        require('../includes/configure.php');
      }

      // include functions
      require_once(DIR_FS_INC.'auto_include.inc.php');
      require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');
      require_once(DIR_FS_ADMIN.DIR_WS_FUNCTIONS.'general.php');

      // Database
      require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
      require_once (DIR_FS_INC.'db_functions.inc.php');
  } else {
      // Set the local configuration parameters - mainly for developers or the main-configure
      if (file_exists('includes/local/configure.php')) {
        include('includes/local/configure.php');
      } else {
        require('includes/configure.php');
      }

      require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');

      require_once('includes/functions/general.php');

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
  
  //#################################
  defined ('ANZAHL_ZEILEN') or define ('ANZAHL_ZEILEN', 10000); //Anzahl der Zeilen die pro Durchlauf bei der Wiederherstellung aus der SQL-Datei eingelesen werden sollen
  defined ('RESTORE_TEST') or define ('RESTORE_TEST', false); //Standard: false - auf true ändern für Simulation für die Wiederherstellung, die SQL Befehle werden in eine Protokolldatei (log) im Backup-Verzeichnis geschrieben
  //#################################

  xtc_db_connect() or die('Unable to connect to database server!');

  //Start Session
  @ini_set('session.use_only_cookies', 1);
  require(DIR_WS_FUNCTIONS . 'sessions.php');
  xtc_session_name('MODsid');
  $session_started = xtc_session_start();
  // verfiy SECURE Token
  if (is_array($_POST) && count($_POST) > 0) {
    if (isset($_POST[$_SESSION['SECName']])) {
      if ($_POST[$_SESSION['SECName']] != $_SESSION['SECToken']) {
        trigger_error("SECToken manipulation.\n".print_r($_POST, true), E_USER_WARNING);
        unset($_POST);
        unset($_GET['action']);
        unset($_GET['saction']);
        die('Direct Access to this location is not allowed.');

      }
    } else {
      trigger_error("SECToken not defined.\n".print_r($_POST, true), E_USER_WARNING);
      unset($_POST);
      unset($_GET['action']);
      unset($_GET['saction']);
      die('Direct Access to this location is not allowed.');
    }
  } elseif (!isset($_SESSION['SECName']) || !isset($_SESSION['SECToken'])) {
    die('Direct Access to this location is not allowed.');
  }
  
  // set the language
  if (!isset($_SESSION['language']) || isset($_GET['language']) || (isset($_SESSION['language']) && !isset($_SESSION['language_charset']))) {
    require_once (DIR_WS_CLASSES.'language.php');
    if (isset($_GET['language'])) {
      $_GET['language'] = xtc_input_validation($_GET['language'], 'char', '');
      $lng = new language($_GET['language']);
    } elseif (isset($_SESSION['language'])) {
      $lng = new language(xtc_input_validation($_SESSION['language'], 'char', ''));
    } else {
      $lng = new language(xtc_input_validation(DEFAULT_LANGUAGE, 'char', ''));
      if (defined('USE_BROWSER_LANGUAGE') && USE_BROWSER_LANGUAGE == 'true') {
        $lng->get_browser_language();
      }
    }
    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
    $_SESSION['language_charset'] = $lng->language['language_charset'];
    $_SESSION['language_code'] = $lng->language['code'];
  }

  // include the language translations
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.$_SESSION['language'] . '.php');
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/buttons.php');
  if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.'backup_db.php')) {
    include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'. 'backup_db.php');
  }

  if (!defined('TITLE')) {
    define('TITLE', HEADING_TITLE);
  }
  include ('includes/functions/db_functions.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  include ('includes/db_actions.php');
  
if(is_file(DIR_WS_INCLUDES.'head.php')) {
    require (DIR_WS_INCLUDES.'head.php');
} else {
    ?>
    <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html <?php echo HTML_PARAMS; ?>>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <?php 
}
?>
<link rel="stylesheet" type="text/css" href="includes/css/backup_db.css">
<script type="text/javascript">
  //Check if jQuery is loaded
  !window.jQuery && document.write('<script src="includes/javascript/jquery-1.8.3.min.js" type="text/javascript"><\/script>');
  $(document).ready(function() {
    document.title = "<?php echo HEADING_TITLE; ?>";
  });
</script>
</head>
  <body>
    <table class="tableBody">
      <tr>
        <!-- body_text //-->
         <td class="boxCenter"> 
           <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></div>
           <div class="main txta-c">
             <div id="info_text" class="pageHeading txta-c mrg10"><?php echo $info_text; ?></div>
             <div id="info_wait" class="pageHeading txta-c mrg10" style="margin-top:20px;"><?php echo $info_wait; ?></div>
             <div style="clear:both;"></div>
             <div class="process_wrapper" style="display:none;">
                  <div class="process_inner_wrapper">
                    <div id="backup_process"></div>
                  </div>
                  <div id="backup_precents">0%</div>
                </div>
             <div id="data_ok" class="main txta-c" style="margin-top:30px;"></div>
             <div id="button_back" class="main txta-c" style="margin-top:20px;"></div>
             <?php //if($button_log != '') ?>
             <div id="button_log" class="main txta-c" style="margin-top:10px;"></div>
             <div style="clear:both"></div>
          </div>       
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <?php
    require (DIR_WS_INCLUDES.'javascript/jquery.backup_restore.js.php');
    ?>
  </body>
</html>