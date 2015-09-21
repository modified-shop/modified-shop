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
  //#################################
  define ('ANZAHL_ZEILEN', 10000); //Anzahl der Zeilen die pro Durchlauf bei der Wiederherstellung aus der SQL-Datei eingelesen werden sollen
  define ('RESTORE_TEST', false); //Standard: false - auf true ändern für Simulation für die Wiederherstellung, die SQL Befehle werden in eine Protokolldatei (log) im Backup-Verzeichnis geschrieben
  //#################################
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

  xtc_db_connect() or die('Unable to connect to database server!');

  //Start Session
  session_name('dbdump');
  if(!isset($_SESSION)) {
    session_start();
  }

  // set the language
  if (!isset($_SESSION['language']) || isset($_GET['language'])) {

    include(DIR_WS_CLASSES . 'language.php');
    $lng = new language($_GET['language']);

    if (!isset($_GET['language']))
      $lng->get_browser_language();

    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
    $_SESSION['language_code'] = $lng->language['code']; //web28 - 2010-09-05 - add $_SESSION['language_code']
    $_SESSION['language_charset'] = $lng->language['language_charset'];
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
  include ('includes/functions/db_restore.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //#### RESTORE ANFANG ########
  if (isset($_SESSION['restore'])) {
    $restore=$_SESSION['restore'];
  }

  if (RESTORE_TEST) $sim = TEXT_SIMULATION; else $sim = '';

  if ($action == 'restorenow') {
    $info_text = TEXT_INFO_DO_RESTORE . $sim;
    
    $restore = array();
    unset($_SESSION['restore']);
    
    $restore['starttime'] = time();
    
    xtc_set_time_limit(0);
    //BOF Disable "STRICT" mode!
    $vers = @xtc_db_get_client_info();
    if(substr($vers,0,1) > 4) {
      @xtc_db_query("SET SESSION sql_mode=''");
    }
    //EOF Disable "STRICT" mode!
    $restore['file'] = DIR_FS_BACKUP . $_GET['file'];

    //Testen ob Backupdatei existiert, bei nein Abbruch
    if (!file_exists($restore['file'])) {
      die('Direct Access to this location is not allowed.');
    }

    //Protokollfatei löschen wenn sie schon existiert
    $extension = substr($restore['file'], -3);
    if($extension == '.gz') {
      $protdatei = substr($restore['file'],0, -3). '.log.gz';
    } else {
      $protdatei = $restore['file'] . '.log';
    }
    if (RESTORE_TEST && file_exists($protdatei) ) {
      unlink ($protdatei);
    }
    $extension = substr($_GET['file'], -3);
    if($extension == 'sql') {
      $restore['compressed'] = false;
    }
    if($extension == '.gz') {
      $restore['compressed'] = true;
    }
    $_SESSION['restore']= isset($restore)?$restore:'';
  }

  //Testen ob Backupdatei existiert, bei nein Abbruch
  if (!file_exists($restore['file'])) {
    die('Direct Access to this location is not allowed.');
  }

  if (!empty($restore['file']) && $action == 'restoredb'){
    $info_text = TEXT_INFO_DO_RESTORE . $sim;
    $restore['filehandle']=($restore['compressed'] == true) ? gzopen($restore['file'],'r') : fopen($restore['file'],'r');
    if (!$restore['compressed'])
      $filegroesse = filesize($restore['file']);
    // Dateizeiger an die richtige Stelle setzen
    ($restore['compressed']) ? gzseek($restore['filehandle'],$restore['offset']) : fseek($restore['filehandle'],$restore['offset']);
    // Jetzt basteln wir uns mal unsere Befehle zusammen...
    $a=0;
    $restore['EOB']=false;
    $config['minspeed'] = ANZAHL_ZEILEN;
    $restore['anzahl_zeilen']= $config['minspeed'];

    // Disable Keys of actual table to speed up restoring
    if (sizeof($restore['tables_to_restore']) == 0 && ($restore['actual_table'] > '' && $restore['actual_table'] != 'unbekannt')) {
      @xtc_db_query('/*!40000 ALTER TABLE `'.$restore['actual_table'].'` DISABLE KEYS */;');
    }
    
    $actual_table = '';
    while (($a < $restore['anzahl_zeilen']) && (!$restore['fileEOF']) && !$restore['EOB']) {
      xtc_set_time_limit(0);
      $sql_command = get_sqlbefehl();
      //Echo $sql_command;
      if ($sql_command > '') {
        $actual_table = $restore['actual_table'];
        if (!RESTORE_TEST) {
          $res = xtc_db_query($sql_command);
          if ($res===false) {
            // Bei MySQL-Fehlern sofort abbrechen und Info ausgeben
            $meldung=((defined('DB_MYSQL_TYPE') && DB_MYSQL_TYPE=='mysqli') ? @xtc_db_error($query, mysqli_errno(${$link}), mysqli_error(${$link})) : @xtc_db_error($query, mysql_errno(${$link}), mysql_error(${$link})));
            if ($meldung!='')
              die($sql_command.' -> '.$meldung);
          }
        } else {
          protokoll($sql_command);
        }
      }
      $a++;
    }
    $restore['offset']=($restore['compressed']) ? gztell($restore['filehandle']) : ftell($restore['filehandle']);
    $restore['compressed'] ? gzclose($restore['filehandle']) : fclose($restore['filehandle']);
    $restore['aufruf']++;

    $_SESSION['restore'] = $restore;
        
    $sec = time() - $restore['starttime']; 
    $time = sprintf('%d:%02d Min.', floor($sec/60), $sec % 60);
    
    $json_output = array();
    $json_output['aufruf'] = $restore['aufruf'];
    $json_output['table_ready'] = ($restore['table_ready'] > 0) ? $restore['table_ready'] : '0';
    $json_output['time'] = $time;
    $json_output['actual_table'] = $restore['fileEOF'] ? '' : $actual_table;
    $json_output['fileEOF'] = $restore['fileEOF'] ? 1 : 0;
    $json_output['filesize'] = filesize($restore['file']);
    $json_output['offset'] = $restore['offset'];
    $json_output[$_SESSION['CSRFName']] = $_SESSION['CSRFToken'];
    
    //$restore['fileEOF'] = true;
    if ($restore['fileEOF'])  {
      $restore= array();
      unset($_SESSION['restore']);
    }
   
    //$json_output = $export;
    $json_output = json_encode($json_output);
    echo $json_output;
    EXIT;
  }

  //#### RESTORE ENDE ########
  
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