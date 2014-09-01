<?php
  /**************************************************************
  * XTC Datenbank Manager Version 1.92
  *(c) by  web28 - www.rpa-com.de
  * backup_restore.php
  * Backup pro Tabelle und limitierter Zeilenzahl (Neuladen der Seite) , einstellbar mit ANZAHL_ZEILEN_BKUP
  * Restore mit limitierter Zeilennanzahl aus SQL-Datei (Neuladen der Seite), einstellbar mit ANZAHL_ZEILEN
  * 2010-09-09 - add set_admin_access
  * 2011-07-02 - Security Fix - PHP_SELF
  * 2011-09-13 - fix some PHP notices
  ***************************************************************/

  //#################################
  define ('ANZAHL_ZEILEN', 10000); //Anzahl der Zeilen die pro Durchlauf bei der Wiederherstellung aus der SQL-Datei eingelesen werden sollen
  define ('RESTORE_TEST', false); //Standard: false - auf true ändern für Simulation für die Wiederherstellung, die SQL Befehle werden in eine Protokolldatei (log) im Backup-Verzeichnis geschrieben
  //#################################
  define ('VERSION', 'Database Restore Ver. 1.92');

  // ?file=dbd_mod105sp1b-20111123170925.sql.gz&action=restorenow

  define ('_VALID_XTC', true);

  // Set the local configuration parameters - mainly for developers or the main-configure
  if (file_exists('includes/local/configure.php')) {
    include('includes/local/configure.php');
  } else {
    require('includes/configure.php');
  }

  require_once('../' . DIR_WS_INCLUDES . 'database_tables.php');

  require_once('includes/functions/general.php');

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
  }

  // include the language translations
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.$_SESSION['language'] . '.php');
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/buttons.php');
  if (file_exists(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'.'backup_db.php')) {
    include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/'. 'backup_db.php');
  }

  include ('includes/functions/db_restore.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Dateiname für Selbstaufruf
  $bk_filename =  basename($_SERVER['SCRIPT_NAME']); // web28 - 2011-07-02 - Security Fix - PHP_SELF

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //aktiviert die Ausgabepufferung
  if (!@ob_start("ob_gzhandler")) @ob_start();

  //#### RESTORE ANFANG ########
  if (isset($_SESSION['restore'])) {
    $restore=$_SESSION['restore'];
  }

  if (RESTORE_TEST) $sim = TEXT_SIMULATION; else $sim = '';

  if ($action == 'restorenow') {
    $info_text = TEXT_INFO_DO_RESTORE . $sim;
    $restore = array();
    unset($_SESSION['restore']);
    $dump = array();
    unset($_SESSION['dump']);
    xtc_set_time_limit(0);
    //BOF Disable "STRICT" mode!
    $vers = @mysql_get_client_info();
    if(substr($vers,0,1) > 4) {
      @mysql_query("SET SESSION sql_mode=''");
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
    //echo print_r($_SESSION);
    $selbstaufruf='<script language="javascript" type="text/javascript">setTimeout("document.restore.submit()",3000);</script>';
  }

  //Testen ob Backupdatei existiert, bei nein Abbruch
  if (!file_exists($restore['file'])) {
    die('Direct Access to this location is not allowed.');
  }

  if (!empty($restore['file']) && $action != 'restorenow'){
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
    if (sizeof($restore['tables_to_restore'])==0 && ($restore['actual_table'] > ''&& $restore['actual_table']!='unbekannt'))
      @mysql_query('/*!40000 ALTER TABLE `'.$restore['actual_table'].'` DISABLE KEYS */;');
    while (($a < $restore['anzahl_zeilen']) && (!$restore['fileEOF']) && !$restore['EOB']) {
      xtc_set_time_limit(0);
      $sql_command = get_sqlbefehl();
      //Echo $sql_command;
      if ($sql_command > '') {
        if (!RESTORE_TEST) {
          $res = mysql_query($sql_command);
          if ($res===false) {
            // Bei MySQL-Fehlern sofort abbrechen und Info ausgeben
            $meldung=@mysql_error;
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

    $tabellen_fertig=($restore['table_ready']>0) ? $restore['table_ready'] : '0';
    $table_ok= 'Tabellen wiederhergestellt: ' . $tabellen_fertig  . '<br><br>Aktuell in Bearbeitung: '. $restore['actual_table'] . '<br><br>Seitenaufrufe: ' . $restore['aufruf'] ;
    $_SESSION['restore']=$restore;
    //$restore['fileEOF'] = true;
    if ($restore['fileEOF'])  {
      //FERTIG;
      $info_wait = '';
      $info_text = TEXT_INFO_DO_RESTORE_OK;
      $table_ok= 'Tabellen wiederhergestellt: ' . $tabellen_fertig .  '<br><br>Seitenaufrufe: ' . $restore['aufruf'] ;
      $button_back = '<a href="../login.php" class="button">Login</a>';
      $selbstaufruf = '';
      //echo $restore['test'];
      $restore= array();
      unset($_SESSION['restore']);

    } else {
      $selbstaufruf='<script language="javascript" type="text/javascript">setTimeout("document.restore.submit()",10);</script>';
    }
  }

  //#### RESTORE ENDE ########

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" />
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css" />
  </head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
    <?php
      echo '<form name="restore" action="'. $bk_filename.'?dbdump='.session_id().'" method="POST"></form>';
    ?>
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td align="center" valign="top">
                      <p>&nbsp;</p>
                      <p>&nbsp;</p>
                      <p class="pageHeading">&nbsp;<?php echo $info_text . '<br /> <br />' . $info_wait; ?>&nbsp;</p>
                      <p class="main">&nbsp;<b><?php echo $table_ok; ?><b>&nbsp;</p>
                      <p>&nbsp;<?php echo $button_back; ?>&nbsp;</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <?php
      if ($selbstaufruf != '')
        echo $selbstaufruf;
    ?>
  </body>
</html>
<?php
  //Pufferinhalte an den Client ausgeben
  ob_end_flush();
?>