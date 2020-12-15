<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  
  require('includes/application_top.php');
    
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (xtc_not_null($action)) {
    switch ($action) {
      case 'download':
        $extension = substr($_GET['file'], -3);
        if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'log') ) {
          if ($fp = fopen(DIR_FS_LOG . $_GET['file'], 'rb')) {
            $buffer = fread($fp, filesize(DIR_FS_LOG . $_GET['file']));
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
        if (strpos($_GET['file'], '..')) {
          xtc_redirect(xtc_href_link(FILENAME_LOGS));
        }

        xtc_remove(DIR_FS_LOG . '/' . $_GET['file']);
        if (!$xtc_remove_error) {
          $messageStack->add_session(SUCCESS_LOG_DELETED, 'success');
          xtc_redirect(xtc_href_link(FILENAME_LOGS));
        }
        break;
        
      case 'dellog':
        clear_dir(DIR_FS_CATALOG.'log/');
        $messageStack->add_session(DELETE_LOGS_SUCCESSFUL, 'success');
        xtc_redirect(xtc_href_link(FILENAME_LOGS));
        break;
    }
  }

  // check if the backup directory exists
  $dir_ok = false;
  if (is_dir(DIR_FS_LOG)) {
    $dir_ok = true;
    if (!is_writeable(DIR_FS_LOG)) {
      $messageStack->add(ERROR_LOG_DIRECTORY_NOT_WRITEABLE, 'error');
    }
  } else {
    $messageStack->add(ERROR_LOG_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  require (DIR_WS_INCLUDES.'head.php');
?>  
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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_content.png'); ?></div>
          <div class="pageHeading pdg2 flt-l">
            <?php echo HEADING_TITLE; ?><br />
            <div class="main pdg2 flt-l">Tools</div>
          </div>
          <div class="main pdg2 flt-l" style="padding-left:30px;">
          <?php
            echo xtc_draw_form('configuration', FILENAME_LOGS, 'action=dellog');
            echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE_LOGS . '"/></form>';
          ?>
          </div>
          <table class="tableCenter">
            <tr>
              <td class="boxCenterLeft">
                <table class="tableBoxCenter collapse">
                  <?php
                    if ($dir_ok) {
                      $dir = dir(DIR_FS_LOG);
                      $contents_array = array();
                      $exts = array("log","log.zip","log.gz");
                      while ($file = $dir->read()) {
                        if (!is_dir(DIR_FS_LOG . $file)) {
                          foreach ($exts as $value) {
                            if (xtc_CheckExt($file, $value)) {
                              $contents_array[(date('Y-m-d', filemtime(rtrim($dir->path, '/').'/'.$file)))][] = $file;
                            }
                          }
                        }
                      }
                      ksort($contents_array);
                      $contents_array = array_reverse($contents_array);
                      if (count($contents_array) > 0) {
                        foreach ($contents_array as $date => $contents) {
                          ?>
                          <tr class="dataTableHeadingRow">
                            <td class="dataTableHeadingContent"><?php echo HEADING_TITLE; ?></td>
                            <td class="dataTableHeadingContent txta-c"><?php echo xtc_date_short($date); ?></td>
                            <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_FILE_SIZE; ?></td>
                          </tr>
                          <?php
                          sort($contents);
                          for ($files = 0, $count = sizeof($contents); $files < $count; $files++) {
                            $entry = $contents[$files];
                            if ((!isset($_GET['file']) || ($_GET['file'] == $entry)) && !isset($buInfo)) {
                              $file_array['file'] = $entry;
                              $buInfo = new objectInfo($file_array);
                            }
                            if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
                              echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'">' . "\n";
                              $onclick_link = 'file=' . $buInfo->file . '&action=download';
                            } else {
                              echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
                              $onclick_link = 'file=' . $entry;
                            }
                            ?>
                              <td class="dataTableContent" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_LOGS, $onclick_link); ?>'"><?php echo '<a href="' . xtc_href_link(FILENAME_LOGS, 'action=download&file=' . $entry) . '">' . xtc_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD) . '</a>&nbsp;' . $entry; ?></td>
                              <td class="dataTableContent txta-c" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_LOGS, $onclick_link); ?>'"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_LOG . $entry)); ?></td>
                              <td class="dataTableContent txta-r" onclick="document.location.href='<?php echo xtc_href_link(FILENAME_LOGS, $onclick_link); ?>'"><?php echo number_format(filesize(DIR_FS_LOG . $entry)); ?> bytes</td>
                            </tr>
                            <?php
                          }
                          echo '<tr><td colspan="3">&nbsp;</td></tr>';
                        }
                      } else {
                        ?>
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TITLE; ?></td>
                          <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_FILE_DATE; ?></td>
                          <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_FILE_SIZE; ?></td>
                        </tr>
                        <?php
                      }
                      $dir->close();
                    }
                  ?>
                </table>
                <div class="smallText pdg2 flt-l"><?php echo TEXT_LOG_DIRECTORY . ' ' . DIR_FS_LOG; ?></div>
              </td>
              <?php
                $heading = array();
                $contents = array();
                switch ($action) {
                  case 'delete':
                    $heading[] = array('text' => '<b>' . $buInfo->file . '</b>');
                    $contents = array('form' => xtc_draw_form('delete', FILENAME_LOGS, 'file=' . $buInfo->file . '&action=deleteconfirm'));
                    $contents[] = array('text' => TEXT_DELETE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . $buInfo->file . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LOGS, 'file=' . $buInfo->file) . '">' . BUTTON_CANCEL . '</a><br/><br/>');
                    break;

                  default:
                    if (isset($buInfo) && is_object($buInfo)) {
                      $heading[] = array('text' => '<b>' . $buInfo->file . '</b>');
                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LOGS, 'file=' . $buInfo->file . '&action=delete') . '">' . BUTTON_DELETE . '</a>'. '&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_LOGS, 'file=' . $buInfo->file . '&action=download') . '">' . 'Download' . '</a><br/><br/>');
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