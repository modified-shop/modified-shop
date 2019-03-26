<?php
/**************************************************************
$Id$

  * XTC Datenbank Manager Version 2.20  UTF-8
  *(c) by  web28 - www.rpa-com.de
  * Convert UTF-8
  * Backup pro Tabelle und limitierter Zeilenzahl (Neuladen der Seite) , einstellbar mit ANZAHL_ZEILEN_BKUP
  * Restore mit limitierter Zeilennanzahl aus SQL-Datei (Neuladen der Seite), einstellbar mit ANZAHL_ZEILEN
  * 2017-09-29 - better remove engine, remove collation
  * 2017-06-07 - remove_engine option
  * 2014-09-14 - jquery ajax handling
  * 2011-11-23 - restore in separate file
  * 2010-09-09 - add set_admin_access
  * 2011-07-02 - Security Fix - PHP_SELF
  * 2011-09-13 - fix some PHP notices
  ***************************************************************/

  define ('VERSION', 'Database Backup Ver. 2.20 UTF-8');

  require('includes/application_top.php');
  
  //#################################
  defined ('ANZAHL_ZEILEN_BKUP') or define ('ANZAHL_ZEILEN_BKUP', 20000); //Anzahl der Zeilen die beim Backup pro Durchlauf maximal aus einer Tabelle  gelesen werden.
  defined ('MAX_RELOADS') or define ('MAX_RELOADS', 600); //Anzahle der maximalen Seitenreloads beim Backup  - falls etwas nicht richtig funktioniert stoppt das Script nach 600 Seitenaufrufen
  defined ('RESTORE_TEST') or define ('RESTORE_TEST', false); //Standard: false - auf true ‰ndern f¸r Simulation f¸r die Wiederherstellung, die SQL Befehle werden in eine Protokolldatei (log) im Backup-Verzeichnis geschrieben
  //#################################
  
  include ('includes/functions/db_functions.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  //Animierte Gif-Datei und Hinweistext
  $info_wait = '<img src="images/loading.gif"> '. TEXT_INFO_WAIT ;
  $button_back = '';

  //aktiviert die Ausgabepufferung
  if (!@ob_start("ob_gzhandler")) @ob_start();

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