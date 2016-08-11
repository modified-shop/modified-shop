<?php
/**************************************************************
$Id: backup_db.php 4174 2013-01-04 15:55:13Z web28 $

  * XTC Datenbank Manager Version 2.00  UTF-8
  *(c) by  web28 - www.rpa-com.de
  * Convert UTF-8
  * Backup pro Tabelle und limitierter Zeilenzahl (Neuladen der Seite) , einstellbar mit ANZAHL_ZEILEN_BKUP
  * Restore mit limitierter Zeilennanzahl aus SQL-Datei (Neuladen der Seite), einstellbar mit ANZAHL_ZEILEN
  * 2014-09-14 - jquery ajax handling
  * 2011-11-23 - restore in separate file
  * 2010-09-09 - add set_admin_access
  * 2011-07-02 - Security Fix - PHP_SELF
  * 2011-09-13 - fix some PHP notices
  ***************************************************************/

  define ('VERSION', 'Database Backup Ver. 2.00 UTF-8');

  require('includes/application_top.php');
  
  //#################################
  defined ('ANZAHL_ZEILEN_BKUP') or define ('ANZAHL_ZEILEN_BKUP', 20000); //Anzahl der Zeilen die beim Backup pro Durchlauf maximal aus einer Tabelle  gelesen werden.
  defined ('MAX_RELOADS') or define ('MAX_RELOADS', 600); //Anzahle der maximalen Seitenreloads beim Backup  - falls etwas nicht richtig funktioniert stoppt das Script nach 600 Seitenaufrufen
  //#################################
  
  include ('includes/functions/db_restore.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //aktiviert die Ausgabepufferung
  if (!@ob_start("ob_gzhandler")) @ob_start();

  //Start Session
  //session_name('dbdump');
  //if(!isset($_SESSION)) {
    //session_start();
  //}


  //#### BACKUP ANFANG #######
  if (isset($_SESSION['dump'])) {
    $dump=$_SESSION['dump'];
  }
  
  //BOC compatility functions
  if (!function_exists('xtc_db_get_client_info')) {
    function xtc_db_get_client_info($link='db_link') {
      global ${$link};

      return mysql_get_client_info();
    }
  }
  
  if (!function_exists('xtc_db_fetch_row')) {
    function xtc_db_fetch_row(&$db_query, $cq=false) {

      if ($db_query === false) {
        return false;
      }
      if (defined('DB_CACHE') && DB_CACHE=='true' && $cq) {
        if (!is_array($db_query) || !count($db_query)) {
          return false;
        }
        $curr = current($db_query);
        next($db_query);
        return $curr;
      } else {
        if (is_array($db_query)) {
          $curr = current($db_query);
          next($db_query);
          return $curr;
        }
        return mysql_fetch_row($db_query);
      }
    }
  }
  
  if (!function_exists('xtc_db_set_charset')) {
    function xtc_db_set_charset($charset, $link='db_link') {
      global ${$link};
    
      if (function_exists('mysql_set_charset')) {
        mysql_set_charset($charset, ${$link});
      } else {
        xtc_db_query('SET NAMES '.$charset);
      }  
    }
  }
  //EOC compatility functions
  
  function WriteToDumpFile($data) {
    $df = $_SESSION['dump']['file'];
    if (isset($data) && $data!='') {
      if (isset($_SESSION['dump']['utf8-convert']) && $_SESSION['dump']['utf8-convert'] == 'yes') {
        $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-15');
        $data = mb_convert_encoding($data, 'UTF-8', 'HTML-ENTITIES');
      }
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

  function remove_collate($table,$data) {
    $table_status = xtc_db_query("SHOW TABLE STATUS WHERE Name='".$table."'");
    $table_status = xtc_db_fetch_array($table_status);
    //echo '<pre>' .print_r($table_status,1) .'</pre>';
    $collation = $table_status['Collation'];
    $data = str_replace(' COLLATE '.$collation,'',$data);
    $data = str_replace(' COLLATE='.$collation,'',$data);
    $collation = explode('_',$collation);
    $data = str_replace(' DEFAULT CHARSET='.$collation[0],'',$data);
    //echo '<pre>' .$data .'</pre>'; EXIT;
    return $data;
  }
  function GetTableInfo($table) {
    //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER -  functions_dump.php line 133
    $data = "DROP TABLE IF EXISTS `$table`;\n";
    $res = xtc_db_query('SHOW CREATE TABLE `'.$table.'`');
    $row = @xtc_db_fetch_row($res);
    if (isset($_SESSION['dump']['remove_collate']) && $_SESSION['dump']['remove_collate'] == 'yes') {
      $row[1] = remove_collate($table,$row[1]);
    }
    $data .= $row[1].';'."\n\n";

    if (isset($_SESSION['dump']['utf8-convert']) && $_SESSION['dump']['utf8-convert'] == 'yes') {
      $check_utf8 = xtc_db_query("SHOW TABLE STATUS WHERE Name='".$table."'");
      $utf8 = xtc_db_fetch_array($check_utf8);
      if (strpos($utf8['Collation'], 'utf8') === false) {
        $data .= "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;\n\n";
      }
    }
    $data .= "/*!40000 ALTER TABLE `$table` DISABLE KEYS */;\n";
    //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER

    WriteToDumpFile($data);

    //Datensaetze feststellen
    $sql="SELECT count(*) as `count_records` FROM `".$table."`";
    $res=@xtc_db_query($sql);
    $res_array = xtc_db_fetch_array($res);

    return $res_array['count_records'];
  }

  function GetTableData($table) {
    global $dump;
    // Dump the data
    if ( ($table != TABLE_SESSIONS ) && ($table != TABLE_WHOS_ONLINE) ) {

      $table_list = array();
      $fields_query = xtc_db_query("SHOW COLUMNS FROM " . $table);
      while ($fields = xtc_db_fetch_array($fields_query)) {
        $table_list[] = $fields['Field'];
      }

      $rows_query = xtc_db_query('select `' . implode('`,`', $table_list) . '` from '.$table . ' limit '.$dump['zeilen_offset'].','.($dump['anzahl_zeilen']));
      $ergebnisse = @xtc_db_num_rows($rows_query);

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

          while ($rows = xtc_db_fetch_array($rows_query)) {
            $insert = 'INSERT INTO `'.$table.'` (`' . implode('`, `', $table_list) . '`) VALUES (';
            foreach ($table_list as $column) {
              //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER -functions_dump.php line 186
              if (!isset($rows[$column])) {
                $insert.='NULL,';
              } else if ($rows[$column]!='') {
                $insert.='\''.xtc_db_input($rows[$column]).'\',';
              } else {
                $insert.='\'\',';
              }
              //BOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
            }
            $data .=substr($insert,0,-1).');'. "\n";
          }
        } else {

          $lines = array();
          while ($rows = xtc_db_fetch_array($rows_query)) {
            $values=array();
            foreach ($table_list as $column) {
              //EOF NEW TABLE  STRUCTURE  - LIKE MYSQLDUMPER
              if (!isset($rows[$column])) {
                $values[] ='NULL';
              } else if ($rows[$column]!='') {
                $values[] ='\''.xtc_db_input($rows[$column]).'\'';
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
    
    if (!isset($dump['$check_utf8'])) {
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
    $backup_file =  'dbd_' . DB_DATABASE . '-' . date('YmdHis');
    $dump['file'] = DIR_FS_BACKUP . $backup_file;

    if ($_POST['compress'] == 'gzip') {
      $dump['compress'] = true;
      $dump['file'] .= '.sql.gz';
    } else {
      $dump['compress'] = false;
      $dump['file'] .= '.sql';
    }

    if ($_POST['remove_collate'] == 'yes') {
      $dump['remove_collate']  = 'yes';
    }
    if ($_POST['complete_inserts'] == 'yes') {
      $dump['complete_inserts']  = 'yes';
    }

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
        $dump['tables'][$i] = $erg['Name'];
        // Get nr of records -> need to do it this way because of incorrect returns when using InnoDBs
        $data_query = xtc_db_query(
            "SELECT count(*) as `count_records` 
               FROM `". $erg['Name'] ."`
            ");
        $data_array = xtc_db_fetch_array($data_query);
        
        $erg['Rows'] = $data_array['count_records'];
		    $table_info .= '-- TABLE|'.$erg['Name'].'|'.(($erg['Name'] != TABLE_SESSIONS && $erg['Name'] != TABLE_WHOS_ONLINE) ? $erg['Rows'] : '0').'|'.(($erg['Name'] != TABLE_SESSIONS && $erg['Name'] != TABLE_WHOS_ONLINE) ? ($erg['Data_length']+$erg['Index_length']) : '0').'|'.$erg['Update_time'].'|'.$erg['Engine']."\n";
        
      }
      $dump['nr'] = 0;
    } //else ERROR
    $table_info .= '-- EOF TABLE-INFO' . "\n";
    $table_info .= '--' . "\n\n";
    
    $dump['ready'] = 0;
    $dump['table_offset'] = 0;

    $_SESSION['dump'] = $dump;
    //echo $schema.$table_info; EXIT;
    WriteToDumpFile($schema.$table_info);
  }
  
  //Seite neu laden wenn noch nicht alle Tabellen ausgelesen sind
  if ($dump['num_tables'] > 0 && $action == 'readdb'){

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

    $_SESSION['dump']= $dump;
    
    $sec = time() - $dump['starttime']; 
    $time = sprintf('%d:%02d Min.', floor($sec/60), $sec % 60);
    
    $json_output = array();
    $json_output['aufruf'] = $dump['aufruf'];
    $json_output['nr'] = $dump['nr'];
    $json_output['num_tables'] = $dump['num_tables'];
    $json_output['time'] = $time;
    $json_output['actual_table'] = $dump['tables'][$nr];
    $json_output[$_SESSION['CSRFName']] = $_SESSION['CSRFToken'];
   
    //$json_output = $export;
    $json_output = json_encode($json_output);
    echo $json_output;
    EXIT;
  }
  //#### BACKUP ENDE #######

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
</script>
</head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table class="tableBody">
      <tr>
      <?php //left_navigation
      if (USE_ADMIN_TOP_MENU == 'false') {
        echo '<td class="columnLeft2">'.PHP_EOL;
        echo '<!-- left_navigation //-->'.PHP_EOL;       
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->'.PHP_EOL; 
        echo '</td>'.PHP_EOL;      
      }
      ?>
      <!-- body_text //--> 
        <td class="boxCenter"> 
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?><span class="smallText"> [<?php echo VERSION; ?>]</span></div>
          <div class="main txta-c">
            <div id="info_text" class="pageHeading txta-c mrg10"><?php echo $info_text; ?></div>
            <div id="info_wait" class="pageHeading txta-c mrg10" style="margin-top:20px;"><?php echo $info_wait; ?></div>
            <div style="clear:both;"></div>
            <div class="process_wrapper">
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
    require (DIR_WS_INCLUDES.'javascript/jquery.backup_db.js.php');
    ?>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>