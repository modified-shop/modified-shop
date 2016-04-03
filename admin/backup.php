<?php
  /* --------------------------------------------------------------
   $Id: backup.php 1780 2011-02-08 02:09:45Z cybercosmonaut $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(backup.php,v 1.57 2003/03/22); www.oscommerce.com
   (c) 2003  nextcommerce (backup.php,v 1.11 2003/08/2); www.nextcommerce.org
   (c) 2006  xt-commerce (backup.php 1023 2005-07-14); www.xt-commerce.com

   Released under the GNU General Public License

   Datenbank Backup Ver. 2.00
   modified by web28 - www.rpa-com.de 14.09.2014
   //jquery ajax handling
   
   Datenbank Backup Ver. 1.92
   modified by web28 - www.rpa-com.de 23.11.2011
   //new restore_backup.php - no admin restrictions

   Datenbank Backup Ver. 1.91f
   modified by DokuMan - 31.10.2010
   //kleinere Anpassungen, um PHP-Notices zu vermeiden

   Datenbank Backup Ver. 1.91e
   modified by web28 - www.rpa-com.de 06.10.2010
   //upload mit xtc_try_upload beschraenkt auf sql und gz Dateien

   Datenbank Backup Ver. 1.91d
   modified by web28 - www.rpa-com.de 09.09.2010
   //Admin Rechte automatisch neu setzen bei Restore -> kompatibel mit alten Backups

   Datenbank Backup Ver. 1.91b
   modified by web28 - www.rpa-com.de 05.06.2010
   //Rechte automatisch setzen fuer Unteradmin

   Datenbank Backup Ver. 1.91a
   modified by web28 - www.rpa-com.de 28.04.2010
   New Restore Method:  like MySqlDumper
   New Backup Method:  single tables with reload site
   Backup includes - depends on database version - engine, auto_increment, default charset, collate
   no exec needed
   used upzip.lib.php , zip.lib.php for zip compress/uncompress
   --------------------------------------------------------------*/
  define('BK_FILENAME', 'backup_db.php'); //BACKUP
  define('RS_FILENAME', 'backup_restore.php'); //RESTORE

  define ('VERSION', 'Database Backup/Restore Ver. 2.00');

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $utf8_query = xtc_db_query("SHOW TABLE STATUS WHERE Name='customers'");
  $utf8_array = xtc_db_fetch_array($utf8_query);
  $check_utf8 = (strpos($utf8_array['Collation'], 'utf8') === false ? false : true);
  $check_utf8 = (!$check_utf8 && isset($_GET['utf8convert']) && $_GET['utf8convert'] == '1' ? false : true);
  
  //Adminrechte automatisch für backup_db setzen
  $result = xtc_db_query("select * from ".TABLE_ADMIN_ACCESS."");
  if ($result_array = xtc_db_fetch_array($result)) {
    if (!isset($result_array['backup_db'])) {
      xtc_db_query("ALTER TABLE `". TABLE_ADMIN_ACCESS."` ADD `backup_db` INT( 1 ) DEFAULT '0' NOT NULL");
      xtc_db_query("UPDATE `".TABLE_ADMIN_ACCESS."` SET `backup_db` = '1' WHERE `customers_id` = '1' LIMIT 1");
      //Rechte automatisch setzen fuer Unteradmin
      if ($_SESSION['customer_id'] > 1) {
        xtc_db_query("UPDATE `".TABLE_ADMIN_ACCESS."` SET `backup_db` = '1' WHERE `customers_id` = '".$_SESSION['customer_id']."' LIMIT 1") ;
      }
    }
  }

  if (!function_exists('xtc_copy_uploaded_file')){
    function xtc_copy_uploaded_file($filename, $target) {
      if (substr($target, -1) != '/') {
        $target .= '/';
      }
      $target .= $filename['name'];
      move_uploaded_file($filename['tmp_name'], $target);
    }
  }
  
  function createDBTableList($data)
  {
    if(!is_array($data)) return $data;
    //echo '<pre>'.print_r($data,1).'</pre>';
    $newdata = array();
    foreach($data as $keys) {
      $newdata[] = $keys['name'] . ($keys['rows'] != '' ? ' ['.$keys['rows'].']' : '');
    }
    return implode('<br>',$newdata);
  }

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'forget':
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
        $messageStack->add_session(SUCCESS_LAST_RESTORE_CLEARED, 'success');
        xtc_redirect(xtc_href_link(FILENAME_BACKUP));
        break;
      case 'download':
        $extension = substr($_GET['file'], -3);
        if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'sql') ) {
          if ($fp = fopen(DIR_FS_BACKUP . $_GET['file'], 'rb')) {
            $buffer = fread($fp, filesize(DIR_FS_BACKUP . $_GET['file']));
            fclose($fp);
            header('Content-type: application/x-octet-stream');
            header('Content-disposition: attachment; filename=' . $_GET['file']);
            echo $buffer;
            exit;
          }
        } else {
          $messageStack->add(ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE, 'error');
        }
        break;
      case 'deleteconfirm':
        if (strstr($_GET['file'], '..')) {
          xtc_redirect(xtc_href_link(FILENAME_BACKUP));
        }

        xtc_remove(DIR_FS_BACKUP . '/' . $_GET['file']);
        if (!$xtc_remove_error) {
          $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');
          xtc_redirect(xtc_href_link(FILENAME_BACKUP));
        }
        break;
      case 'restorelocalnow':
        $file = xtc_try_upload('sql_file', DIR_FS_BACKUP, '777', array('sql','gz'));
        xtc_redirect(xtc_href_link(FILENAME_BACKUP));
        break;
    }
  }

  // check if the backup directory exists
  $dir_ok = false;
  if (is_dir(DIR_FS_BACKUP)) {
    $dir_ok = true;
    if (!is_writeable(DIR_FS_BACKUP)) {
      $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
    }
  } else {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
  }
  
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
            <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_backup.png'); ?></div>
            <div class="pageHeading"><?php echo HEADING_TITLE; ?>
              <span class="smallText"> [<?php echo VERSION; ?>]</span>
            <br /></div>
            <div class="main pdg2 flt-l">Tools</div>
            <table class="tableCenter">
              <tr>
                <td class="boxCenterLeft">
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TITLE; ?></td>
                      <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_FILE_DATE; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_FILE_SIZE; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
                      if ($dir_ok) {
                        $dir = dir(DIR_FS_BACKUP);
                        $contents = array();
                        $exts = array("sql","sql.zip","sql.gz");
                        while ($file = $dir->read()) {
                          if (!is_dir(DIR_FS_BACKUP . $file)) {
                            foreach ($exts as $value) {
                              if (xtc_CheckExt($file, $value)) {
                                $contents[] = $file;
                              }
                            }
                          }
                        }
                        if (count($contents) > 0) {
                          rsort($contents);
                          for ($files = 0, $count = sizeof($contents); $files < $count; $files++) {
                            $entry = $contents[$files];
                            $check = 0;
                            if ((!isset($_GET['file']) || ($_GET['file'] == $entry)) && !isset($buInfo) && ($action != 'backup') && ($action != 'restorelocal')) {
                              $file_array['file'] = $entry;
                              $file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry));
                              $file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';
                              $file_array['table_list'] = array();
                              $file_array['tables_row_count'] = 0;
                              switch (substr($entry, -3)) {
                                case 'zip': 
                                  $file_array['compression'] = 'ZIP'; 
                                  break;
                                case '.gz': 
                                  $file_array['compression'] = 'GZIP';
                                  if ($fp = gzopen(DIR_FS_BACKUP . $entry, 'r')) {
                                    while ( ! gzeof($fp) ) {
                                      $line = gzgets($fp, 4096);
                                      if (substr($line, 0, 2) != "--") break; // backed up tables are in head of file
                                      if (substr($line, 0, 9) == "-- TABLE|") {
                                        $table_info = explode('|',trim(substr($line, 9)));
                                        $file_array['table_list'][]  = array('name' => $table_info[0] ,'rows' => $table_info[1]);
                                        $file_array['tables_row_count'] += $table_info[1];
                                      }
                                    }
                                  }                          
                                  break;
                                default: 
                                  $file_array['compression'] = TEXT_NO_EXTENSION; 
                                  if ($fp = fopen(DIR_FS_BACKUP . $entry, 'r')) {
                                    while ( ! feof($fp) ) {
                                      $line = fgets($fp, 4096);
                                      if (substr($line, 0, 2) != "--") break; // backed up tables are in head of file
                                      if (substr($line, 0, 9) == "-- TABLE|") {
                                        $table_info = explode('|',trim(substr($line, 9)));
                                        $file_array['table_list'][]  = array('name' => $table_info[0] ,'rows' => $table_info[1]);
                                        $file_array['tables_row_count'] += $table_info[1];
                                      }
                                    }
                                  }
                                  break;
                              }
                              $file_array['table_list'] = !$file_array['table_list'] ? TEXT_INFO_NO_INFORMATION : $file_array['table_list'];
                              $buInfo = new objectInfo($file_array);
                            }
                            if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
                              echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'">' . "\n";
                              $onclick_link = 'file=' . $buInfo->file . '&action=restore';
                            } else {
                              echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                              $onclick_link = 'file=' . $entry;
                            }
                            ?>
                              <td class="dataTableContent" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo '<a href="' . xtc_href_link(FILENAME_BACKUP, 'action=download&file=' . $entry) . '">' . xtc_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD) . '</a>&nbsp;' . $entry; ?></td>
                              <td class="dataTableContent txta-c" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry)); ?></td>
                              <td class="dataTableContent txta-r" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo number_format(filesize(DIR_FS_BACKUP . $entry)); ?> bytes</td>
                              <td class="dataTableContent txta-r" ><?php if ( (isset($buInfo) && is_object($buInfo)) && ($entry == $buInfo->file) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $entry) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                            </tr>
                            <?php
                          }
                        }
                        $dir->close();
                      }
                    ?>
                  </table>
                  <div class="smallText pdg2 flt-l"><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP; ?></div>
                  <div class="smallText pdg2 flt-r"><?php if ( ($action != 'backup') && (isset($dir))) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'action=backup') . '">' . BUTTON_BACKUP . '</a>'; if ( ($action != 'restorelocal') && ($dir) ) echo '&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'action=restorelocal') . '">' . BUTTON_RESTORE . '</a>'; ?></div>
               
                  <?php
                  if (defined('DB_LAST_RESTORE')) {
                  ?>
                    <div class="clear"></div>
                    <div class="smallText pdg2 flt-r"><?php echo TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'action=forget') . '">' . TEXT_FORGET . '</a>'; ?></div>

                  <?php
                  }
                  ?>
                  
                </td>
                <?php
                  $heading = array();
                  $contents = array();
                  $info_heading = '';
                  if (isset($buInfo) && is_object($buInfo)) {
                      $info_heading = '<b>Backup Dated: ' . $buInfo->date . ' '. ($buInfo->table_list != TEXT_INFO_NO_INFORMATION ? ' (' . count($buInfo->table_list) . ' tables)' : ''). '</b>';
                  }
                  switch ($action) {
                    case 'backup':
                      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BACKUP . '</b>');

                      $contents = array('form' => xtc_draw_form('backup', BK_FILENAME, 'action=backupnow'));
                      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);
                      if ($messageStack->size > 0) {
                        $contents[] = array('text' => '<br />' . xtc_draw_radio_field('compress', 'no', true) . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
                      } else {
                        if(function_exists('gzopen')) {
                          $contents[] = array('text' => '<br />' . xtc_draw_radio_field('compress', 'gzip', true) . ' ' . TEXT_INFO_USE_GZIP);
                        }
                        $contents[] = array('text' => xtc_draw_radio_field('compress', 'no') . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
                        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('remove_collate', 'yes', false) . ' ' . TEXT_REMOVE_COLLATE);
                        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('complete_inserts', 'yes', true) . ' ' . TEXT_COMPLETE_INSERTS);
                        if (!$check_utf8) {
                          $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('utf8-convert', 'yes', false) . ' ' . TEXT_CONVERT_TO_UTF);
                        }
                      }
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_BACKUP . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP) . '">' . BUTTON_CANCEL . '</a>');
                      break;
                    case 'restore':
                      $heading[] = array('text' => $info_heading);
                      $contents = array('form' => xtc_draw_form('restore', RS_FILENAME, 'action=restorenow&file=' . $buInfo->file));
                      //$heading[] = array('text' => '<b>' . $buInfo->date . '</b>');
                      $contents[] = array('text' => xtc_break_string(sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''), 35, ' '));
                      if (!$check_utf8) {
                        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('utf8-convert', 'yes', false) . ' ' . TEXT_IMPORT_UTF);
                      }
                      require_once (DIR_FS_INC . 'xtc_create_password.inc.php'); // needed for xtc_RandomString
                      $_SESSION['SECName'] = xtc_RandomString(6);
                      $_SESSION['SECToken'] = xtc_RandomString(32);
                      $contents[] = array('text' => '<input type="hidden" name="'.$_SESSION['SECName'].'" value="'.$_SESSION['SECToken'].'">');
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_RESTORE . '"/>&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . BUTTON_CANCEL . '</a>');
                      break;

                    case 'restorelocal':
                      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</b>');
                      $contents = array('form' => xtc_draw_form('restore', FILENAME_BACKUP, 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
                      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br /><br />' . TEXT_INFO_BEST_THROUGH_HTTPS);
                      $contents[] = array('text' => '<br />' . xtc_draw_file_field('sql_file'));
                      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPLOAD . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP) . '">' . BUTTON_CANCEL . '</a>');
                      break;

                    case 'delete':
                      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');
                      $contents = array('form' => xtc_draw_form('delete', FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=deleteconfirm'));
                      $contents[] = array('text' => TEXT_DELETE_INTRO);
                      $contents[] = array('text' => '<br /><b>' . $buInfo->file . '</b>');
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . BUTTON_CANCEL . '</a>');
                      break;

                    default:
                      if (isset($buInfo) && is_object($buInfo)) {
                        $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');
                        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restore') . '">' . BUTTON_RESTORE . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=delete') . '">' . BUTTON_DELETE . '</a>'. '&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=download') . '">' . 'Download' . '</a>');
                        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE . ' ' . $buInfo->date);
                        $contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
                        $contents[] = array('text' => '<br />' . TEXT_INFO_COMPRESSION . ' ' . $buInfo->compression);
                        $contents[] = array('text' => TEXT_INFO_TABLES_IN_BACKUP . ($buInfo->table_list != TEXT_INFO_NO_INFORMATION ? count($buInfo->table_list) : '') . '<br>' . createDBTableList($buInfo->table_list));
                      }
                      break;
                  }
                  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                    echo '            <td class="boxRight">' . "\n";

                    $box = new box;
                    echo $box->infoBox($heading, $contents);
                    echo '            </td>' . "\n";
                  }
                ?>
              </tr>                
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <br />
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>