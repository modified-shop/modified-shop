<?php
/**************************************************************
$Id: backup_db.php 4174 2013-01-04 15:55:13Z web28 $

  * XTC Datenbank Manager Version 1.92
  *(c) by  web28 - www.rpa-com.de
  * Backup pro Tabelle und limitierter Zeilenzahl (Neuladen der Seite) , einstellbar mit ANZAHL_ZEILEN_BKUP
  * Restore mit limitierter Zeilennanzahl aus SQL-Datei (Neuladen der Seite), einstellbar mit ANZAHL_ZEILEN
  * 2011-11-23 - restore in separate file
  * 2010-09-09 - add set_admin_access
  * 2011-07-02 - Security Fix - PHP_SELF
  * 2011-09-13 - fix some PHP notices
  ***************************************************************/

  //#################################
  define ('ANZAHL_ZEILEN_BKUP', 20000); //Anzahl der Zeilen die beim Backup pro Durchlauf maximal aus einer Tabelle  gelesen werden.
  define ('MAX_RELOADS', 600); //Anzahle der maximalen Seitenreloads beim Backup  - falls etwas nicht richtig funktioniert stoppt das Script nach 600 Seitenaufrufen
  //#################################
  define ('VERSION', 'Database Backup Ver. 1.92');

  require('includes/application_top.php');
  include ('includes/functions/db_restore.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Dateiname fuer Selbstaufruf
  $bk_filename =  basename($_SERVER['SCRIPT_NAME']); // web28 - 2011-07-02 - Security Fix - PHP_SELF

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //aktiviert die Ausgabepufferung
  if (!@ob_start("ob_gzhandler")) @ob_start();

  //Start Session
  session_name('dbdump');
  if(!isset($_SESSION)) {
    session_start();
  }


  //#### BACKUP ANFANG #######
  if (isset($_SESSION['dump'])) {
    $dump=$_SESSION['dump'];
  }

  function WriteToDumpFile($data) {
    $df = $_SESSION['dump']['file'];
    if (isset($data) && $data!='') {
      if ($_SESSION['dump']['compress']) {
        if ($data!='') {
          $fp=gzopen($df,'ab');
          gzwrite($fp,$data);
          gzclose($fp);
        }
      } else {
        if ($data!=''){
          $fp=fopen($df,'ab');
          fwrite($fp,$data);
          fclose($fp);
        }
      }
    }
    unset($data);
  }

  function GetTableInfo($table) {
    //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER -  functions_dump.php line 133
    $data = "DROP TABLE IF EXISTS `$table`;\n";
    $res = mysql_query('SHOW CREATE TABLE `'.$table.'`');
    $row = @mysql_fetch_row($res);
    $data .= $row[1].';'."\n\n";
    $data .= "/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n";
    //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER

    WriteToDumpFile($data);

    //Datensaetze feststellen
    $sql="SELECT count(*) as `count_records` FROM `".$table."`";
    $res=@mysql_query($sql);
    $res_array = mysql_fetch_array($res);

    return $res_array['count_records'];
  }

  function GetTableData($table) {
    global $dump;
    // Dump the data
    if ( ($table != TABLE_SESSIONS ) && ($table != TABLE_WHOS_ONLINE) && ($table != TABLE_ADMIN_ACTIVITY_LOG) ) {

      $table_list = array();
      $fields_query = mysql_query("SHOW COLUMNS FROM " . $table);
      while ($fields = mysql_fetch_array($fields_query)) {
        $table_list[] = $fields['Field'];
      }

      $rows_query = mysql_query('select `' . implode('`,`', $table_list) . '` from '.$table . ' limit '.$dump['zeilen_offset'].','.($dump['anzahl_zeilen']));
      $ergebnisse = @mysql_num_rows($rows_query);

      $data = '';

      if ($ergebnisse!== false) {
        if (($ergebnisse + $dump['zeilen_offset']) < $dump['table_records']) {
          //noch nicht fertig - neuen Startwert festlegen
          $dump['zeilen_offset']+= $dump['anzahl_zeilen'];
        } else {
          //Fertig - naechste Tabelle
          $dump['nr']++;
          $dump['table_offset'] = 0;
        }

        //BOF Complete Inserts ja/nein
        if ($_SESSION['dump']['complete_inserts'] == 'yes') {

          while ($rows = mysql_fetch_array($rows_query)) {
            $insert = 'INSERT INTO `'.$table.'` (`' . implode('`, `', $table_list) . '`) VALUES (';
            foreach ($table_list as $column) {
              //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER -functions_dump.php line 186
              if (!isset($rows[$column])) {
                $insert.='NULL,';
              } else if ($rows[$column]!='') {
                $insert.='\''.mysql_real_escape_string($rows[$column]).'\',';
              } else {
                $insert.='\'\',';
              }
              //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
            }
            $data .=substr($insert,0,-1).');'. "\n";
          }
        } else {

          $lines = array();
          while ($rows = mysql_fetch_array($rows_query)) {
            $values=array();
            foreach ($table_list as $column) {
              //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
              if (!isset($rows[$column])) {
                $values[] ='NULL';
              } else if ($rows[$column]!='') {
                $values[] ='\''.mysql_real_escape_string($rows[$column]).'\'';
              } else {
                $values[] ='\'\'';
              }
              //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
            }
            $lines[] = implode(', ', $values);
          }
          $tmp = trim(implode("),\n (", $lines));
          if ($tmp != '') {
            $data = 'INSERT INTO `'.$table.'` (`' . implode('`, `', $table_list) . '`) VALUES'."\n" . ' ('.$tmp.");\n";
          }
        }
        //EOF Complete Inserts ja/nein
        if ($dump['table_offset'] == 0)
          $data.= "/*!40000 ALTER TABLE `$table` ENABLE KEYS */;\n\n";
        //echo nl2br($data);
        WriteToDumpFile($data);

      } // FEHLER
    } else {
      $dump['nr']++;
      $dump['table_offset'] = 0;
    }
  }

  if ($action == 'backupnow') {
    $info_text = TEXT_INFO_DO_BACKUP;

    $restore= array();
    unset($_SESSION['restore']);
    $dump = array();
    unset($_SESSION['dump']);


    @xtc_set_time_limit(0);

    //BOF Disable "STRICT" mode!
    $vers = @mysql_get_client_info();
    if(substr($vers,0,1) > 4) {
      @mysql_query("SET SESSION sql_mode=''");
    }
    //EOF Disable "STRICT" mode!

    if (function_exists('mysql_get_client_info')) {
      $mysql_version = '-- MySQL-Client-Version: ' . mysql_get_client_info() . "\n--\n";
    } else {
      $mysql_verion = '';
    }
    $schema = '-- XT-Commerce & compatible' . "\n" .
              '--' . "\n" .
              '-- ' . VERSION . ' (c) by web28 - www.rpa-com.de' . "\n" .
              '-- ' . STORE_NAME . "\n" .
              '-- ' . STORE_OWNER . "\n" .
              '--' . "\n" .
              '-- Database: ' . DB_DATABASE . "\n" .
              '-- Database Server: ' . DB_SERVER . "\n" .
              '--' . "\n" . $mysql_version .
              '-- Backup Date: ' . date(PHP_DATE_TIME_FORMAT) . "\n\n";
    $backup_file =  'dbd_' . DB_DATABASE . '-' . date('YmdHis');
    $dump['file'] = DIR_FS_BACKUP . $backup_file;

    if ($_POST['compress'] == 'gzip') {
      $dump['compress'] = true;
      $dump['file'] .= '.sql.gz';
    } else {
      $dump['compress'] = false;
      $dump['file'] .= '.sql';
    }

    if ($_POST['complete_inserts'] == 'yes') {
      $dump['complete_inserts']  = 'yes';
    }

    $tabellen = mysql_query('SHOW TABLE STATUS');
    $dump['num_tables'] = mysql_num_rows($tabellen);

    //Tabellennamen in Array einlesen
    $dump['tables'] = Array();
    if ($dump['num_tables'] > 0){
      for ($i=0; $i < $dump['num_tables']; $i++){
        $row = mysql_fetch_array($tabellen);
        $dump['tables'][$i] = $row['Name'];
      }
      $dump['nr'] = 0;
    } //else ERROR

    $dump['table_offset'] = 0;

    $_SESSION['dump']=$dump;
    WriteToDumpFile($schema);
    flush();
    $selbstaufruf='<script language="javascript" type="text/javascript">setTimeout("document.dump.submit()", 3000);</script></div>';
  }
  //Seite neu laden wenn noch nicht alle Tabellen ausgelesen sind
  if ($dump['num_tables'] > 0 && $action != 'backupnow'){

    $info_text = TEXT_INFO_DO_BACKUP;

    @xtc_set_time_limit(0);

    if ($dump['nr'] < $dump['num_tables']) {
      $nr = $dump['nr'];
      $dump['aufruf']++;
      $table_ok = 'Tabellen gesichert: ' . ($nr + 1) .  '<br><br>Zuletzt bearbeitet: ' . $dump['tables'][$nr] . '<br><br>Seitenaufrufe: ' . $dump['aufruf'] ;

      //Neue Tabelle
      if ($dump['table_offset'] == 0) {
        $dump['table_records'] = GetTableInfo($dump['tables'][$nr]);
        $dump['anzahl_zeilen']= ANZAHL_ZEILEN_BKUP;
        $dump['table_offset'] = 1;
        $dump['zeilen_offset'] = 0;
      } else {
        //Daten aus  Tabelle lesen
        GetTableData($dump['tables'][$nr]);
      }

      $_SESSION['dump']= $dump;

      $selbstaufruf='<script language="javascript" type="text/javascript">setTimeout("document.dump.submit()", 10);</script></div>';
      //Verhindert Endlosschleife - Script wir nach MAX_RELOADS beendet
      if ( $dump['aufruf'] > MAX_RELOADS) {
        $selbstaufruf = '';
      }

    } else { //Fertig
      $info_wait = '';
      $info_text = TEXT_INFO_DO_BACKUP_OK;
      $table_ok= 'Tabellen gesichert: ' . $dump['nr'] .  '<br><br>Seitenaufrufe: ' . $dump['aufruf'] ;
      $button_back = '<a href="backup.php" class="button">'. BUTTON_BACK .'</a>';
      $selbstaufruf = '';
      unset ($_SESSION['dump']);
      $button_back = '<a href="backup.php" class="button">'. BUTTON_BACK .'</a>';
      //$selbstaufruf='<script language="javascript" type="text/javascript">window.location.href = "backup.php";</script></div>';
    }
  }
  //#### BACKUP ENDE #######
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>" />
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css" />
  </head>
  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <?php
      echo '<form name="dump" action="'. $bk_filename.'?dbdump='.session_id().'" method="POST"></form>';
    ?>
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
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
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
  //Pufferinhalte an den Client ausgeben
  ob_end_flush();
?>