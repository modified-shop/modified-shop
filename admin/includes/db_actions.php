<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  if (isset($_SESSION['dump'])) {
    $dump = $_SESSION['dump'];
  }
  
  if (isset($_SESSION['restore'])) {
    $restore = $_SESSION['restore'];
  }
  
  if (RESTORE_TEST) $sim = TEXT_SIMULATION; else $sim = '';
  
  switch ($action) {
    case 'backupnow':
      $info_text = TEXT_INFO_DO_BACKUP;

      $restore = array();
      if (isset($_SESSION['restore'])) {
        unset($_SESSION['restore']);
      }
      $dump = array();
      if (isset($_SESSION['dump'])) {
        unset($_SESSION['dump']);
      }
    
      if (!isset($dump['check_utf8'])) {
        $utf8_query = xtc_db_query("SHOW TABLE STATUS WHERE Name='customers'");
        $utf8_array = xtc_db_fetch_array($utf8_query);
        $check_utf8 = strpos($utf8_array['Collation'], 'utf8') === false ? false : true;
      }
      $charset = $check_utf8 ? 'utf8' : 'latin1';
      xtc_db_set_charset($charset);
    
      $dump['starttime'] = time();

      @xtc_set_time_limit(0);

      //BOF Disable "STRICT" mode!
      $vers = @xtc_db_get_client_info();
      if(substr($vers,0,1) > 4) {
        @xtc_db_query("SET SESSION sql_mode=''");
      }
      //EOF Disable "STRICT" mode!

      if (function_exists('xtc_db_get_client_info')) {
        $mysql_version = '-- MySQL-Client-Version: ' . xtc_db_get_client_info() . "\n--\n";
      } else {
        $mysql_verion = '';
      }
      $schema = '-- Modified-Shop & compatible' . "\n" .
                '--' . "\n" .
                '-- ' . VERSION . ' (c) by web28 - www.rpa-com.de' . "\n" .
                '-- ' . STORE_NAME . "\n" .
                '-- ' . STORE_OWNER . "\n" .
                '--' . "\n" .
                '-- Database: ' . DB_DATABASE . "\n" .
                '-- Database Server: ' . DB_SERVER . "\n" .
                '--' . "\n" . $mysql_version .
                '-- Backup Date: ' . date(PHP_DATE_TIME_FORMAT) . "\n";
              
      if (isset($_POST['utf8-convert']) && $_POST['utf8-convert'] == 'yes') {
        $dump['utf8-convert']	= 'yes';
      }
      $backup_file = 'dbd_' . DB_DATABASE . '-' . date('YmdHis');
      $dump['file'] = DIR_FS_BACKUP . $backup_file;

      if ($_POST['compress'] == 'gzip') {
        $dump['compress'] = true;
        $dump['file'] .= '.sql.gz';
      } else {
        $dump['compress'] = false;
        $dump['file'] .= '.sql';
      }

      if (isset($_POST['remove_collate']) && $_POST['remove_collate'] == 'yes') {
        $dump['remove_collate'] = 'yes';
      }
    
      if (isset($_POST['remove_engine']) && $_POST['remove_engine'] == 'yes') {
        $dump['remove_engine'] = 'yes';
      }
    
      if (isset($_POST['complete_inserts']) && $_POST['complete_inserts'] == 'yes') {
        $dump['complete_inserts'] = 'yes';
      }
      
      $table_collations = $table_engines = array();

      $tables_query = xtc_db_query('SHOW TABLE STATUS');
      $dump['num_tables'] = xtc_db_num_rows($tables_query);

      $table_info = '--' . "\n";
      $table_info .= '-- TABLE-INFO' . "\n";
      //Tabellennamen in Array einlesen
      $dump['tables'] = array();
      if ($dump['num_tables'] > 0){
        for ($i=0; $i < $dump['num_tables']; $i++){
          $erg = xtc_db_fetch_array($tables_query);
          //echo '<pre>'.print_r($erg,1).'</pre>';
          if ($erg['Collation'] != '') {
            $table_collations[$erg['Collation']] = 1;
          }
          if ($erg['Engine'] != '') {
            $table_engines[$erg['Engine']] = 1;
          }
          $data_query = xtc_db_query(
             "SHOW FULL COLUMNS FROM `". $erg['Name'] ."`
               WHERE Collation != ''
               AND Collation != '". $erg['Collation']."'
             ");
          while ($fields = xtc_db_fetch_array($data_query)) {
              $table_collations[$fields['Collation']] = 1;
          }
          $dump['tables'][$i] = $erg['Name'];
          // Get nr of records -> need to do it this way because of incorrect returns when using InnoDBs
          $data_query = xtc_db_query(
              "SELECT count(*) as `count_records` 
                 FROM `". $erg['Name'] ."`
              ");
          $data_array = xtc_db_fetch_array($data_query);
          
          $erg['Rows'] = $data_array['count_records'];
          $table_info .= '-- TABLE|'.$erg['Name'].'|'.(($erg['Name'] != TABLE_SESSIONS && $erg['Name'] != TABLE_WHOS_ONLINE) ? $erg['Rows'] : '0').'|'.(($erg['Name'] != TABLE_SESSIONS && $erg['Name'] != TABLE_WHOS_ONLINE) ? ($erg['Data_length']+$erg['Index_length']) : '0').'|'.$erg['Update_time']. (!isset($_POST['remove_engine']) ? '|'.$erg['Engine'] :'') ."\n";
          
        }
        $dump['nr'] = 0;
      } //else ERROR
      $table_info .= '-- EOF TABLE-INFO' . "\n";
      $table_info .= '--' . "\n\n";
      
      $dump['collations'] = array_keys($table_collations);
      $dump['engines'] = array_keys($table_engines);
      
      $dump['ready'] = 0;
      $dump['table_offset'] = 0;

      $_SESSION['dump'] = $dump;

      WriteToDumpFile($schema.$table_info);
      break;
  
    case 'readdb':
      if ($dump['num_tables'] > 0) {
        $info_text = TEXT_INFO_DO_BACKUP;
        @xtc_set_time_limit(0);
        $nr = $dump['nr'];
        $dump['aufruf']++;
    
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
    
        if (isset($_SESSION['dump'])) {
          $_SESSION['dump'] = $dump;
        }
    
        $sec = time() - $dump['starttime']; 
        $time = sprintf('%d:%02d Min.', floor($sec/60), $sec % 60);
    
        $json_output = array();
        $json_output['aufruf'] = $dump['aufruf'];
        $json_output['nr'] = $dump['nr'];
        $json_output['num_tables'] = $dump['num_tables'];
        $json_output['time'] = $time;
        $json_output['actual_table'] = $dump['tables'][$nr];
        $json_output['dump'] = base64_encode(serialize($dump));
    
        if (isset($_SESSION['CSRFName']) && isset($_SESSION['CSRFToken'])) {
          $json_output[$_SESSION['CSRFName']] = $_SESSION['CSRFToken'];
        }
    
        $json_output = json_encode($json_output);
        echo $json_output;
        exit();
      }
      break;

    case 'restorenow':
      $info_text = TEXT_INFO_DO_RESTORE . $sim;
    
      $restore = array();
      if (isset($_SESSION['restore'])) {
        unset($_SESSION['restore']);
      }
      $restore['starttime'] = time();
    
      xtc_set_time_limit(0);
      //BOF Disable "STRICT" mode!
      $vers = @xtc_db_get_client_info();
      if(substr($vers,0,1) > 4) {
        @xtc_db_query("SET SESSION sql_mode=''");
      }
      //EOF Disable "STRICT" mode!
      $_GET['file'] = isset($_GET['file']) ? basename($_GET['file']) : '';
      $_GET['file'] = preg_replace('/[^0-9a-zA-Z._-]/','',$_GET['file']);
      $restore['file'] = DIR_FS_BACKUP . $_GET['file'];

      //Testen ob Backupdatei existiert, bei nein Abbruch
      if (!is_file($restore['file'])) {
        die('Direct Access to this location is not allowed.');
      }

      //Protokollfatei löschen wenn sie schon existiert
      $extension = substr($restore['file'], -3);
      if($extension == '.gz') {
        $protdatei = substr($restore['file'],0, -3). '.log.gz';
      } else {
        $protdatei = $restore['file'] . '.log';
      }
      if (RESTORE_TEST && is_file($protdatei) ) {
        unlink ($protdatei);
      }
      $extension = substr($_GET['file'], -3);
      if($extension == 'sql') {
        $restore['compressed'] = false;
      }
      if($extension == '.gz') {
        $restore['compressed'] = true;
      }
      
      $restore['utf8'] = false;
      if(isset($_GET['convert']) && $_GET['convert'] == 'utf-8') {
        $restore['utf8'] = true;
      }
    
      //Testen ob Backupdatei existiert, bei nein Abbruch
      if (!is_file($restore['file'])) {
        die('Direct Access to this location is not allowed.');
      }
      
      $_SESSION['restore'] = isset($restore) ? $restore : '';
      break;
    
    case 'restoredb':
      //Testen ob Backupdatei existiert, bei nein Abbruch
      if (!is_file($restore['file'])) {
        die('Direct Access to this location is not allowed.');
      }
      $info_text = TEXT_INFO_DO_RESTORE . $sim;
      $restore['filehandle']=($restore['compressed'] == true) ? gzopen($restore['file'],'r') : fopen($restore['file'],'r');
      if (!$restore['compressed'])
        $filegroesse = filesize($restore['file']);
      // Dateizeiger an die richtige Stelle setzen
      ($restore['compressed']) ? gzseek($restore['filehandle'],$restore['offset']) : fseek($restore['filehandle'],$restore['offset']);
      // Jetzt basteln wir uns mal unsere Befehle zusammen...
      $a=0;
      $restore['EOB'] = false;
      $config['minspeed'] = ANZAHL_ZEILEN;
      $restore['anzahl_zeilen'] = $config['minspeed'];

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
            if ($restore['utf8'] == true) {
              $sql_command = mb_convert_encoding($sql_command, 'utf-8', 'ISO-8859-15');
            }
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
    
      if (isset($_SESSION['restore'])) {
        $_SESSION['restore'] = $restore;
      }
    
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
    
      if (isset($_SESSION['SECName']) && isset($_SESSION['SECToken'])) {
        $json_output[$_SESSION['SECName']] = $_SESSION['SECToken'];
      }
    
      if ($restore['fileEOF'])  {
        $restore = array();
        if (isset($_SESSION['restore'])) {
          unset($_SESSION['restore']);
        }
      }
   
      $json_output = json_encode($json_output);
      echo $json_output;
      exit();
      break;
  }
?>