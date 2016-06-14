<?php
  /* --------------------------------------------------------------
   $Id: blz_update.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');

// include needed function
require_once(DIR_FS_INC.'get_external_content.inc.php');

$blz_file_default_link = 'http://www.bundesbank.de/Redaktion/DE/Downloads/Aufgaben/Unbarer_Zahlungsverkehr/Bankleitzahlen/2016_09_04/blz_2016_06_06_txt.txt?__blob=publicationFile';

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
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
        <div class="main pdg2 flt-l">Tools</div>
        <div class="clear main mrg5">
          <?php
            $i ='';
            $button_disabled = '';
            $lines = array();
            $banktransfer = array();
            $blz = array();

            $action = (isset($_GET['action']) ? $_GET['action'] : '');

              switch ($action) {
                case 'do_blz':
                  $blz_file = (isset($_GET['blz_file']) ? $_GET['blz_file'] : '');
                  if (empty($blz_file)) {
                    echo BLZ_LINK_NOT_GIVEN_TEXT;
                    break;
                  } elseif (strpos($blz_file, 'http://www.bundesbank.de/') === false ||
                             strpos($blz_file, '.txt') === false) {
                    echo BLZ_LINK_INVALID_TEXT;
                    break;
                  }

                  echo '<div id="progress" style="width:900px"></div>';
                  echo '<div id="information"></div>';
                  echo '<br/><br/>';

                  // save blz local
                  $blz_file_local = DIR_FS_CATALOG.'cache/blz_update.txt';
                  $blz_file_content = get_external_content($blz_file, 3, false);
                  if (file_put_contents($blz_file_local, $blz_file_content, LOCK_EX) === false) {
                    echo BLZ_LINK_INVALID_TEXT;
                    break;
                  }
                  
                  $i = 0;
                  $estimated_lines = 20000;
                  $handle = @fopen($blz_file_local, "r");
                  if ($handle) {
                     while (!feof($handle)) {
                       $i++;
                       $percent = intval($i/$estimated_lines * 100)."%";
                       $line = stream_get_line($handle, 65535, "\n");
                       $lines[]= $line;
                      // Javascript to update progress bar and information
                      echo '<script language="javascript">
                            document.getElementById("progress").innerHTML="<div style=\"width:'.$percent.';background-color:#9f0;\">&nbsp;</div>"
                            document.getElementById("information").innerHTML="'.$i.BLZ_LINES_PROCESSED_TEXT.'"
                            </script>';

                      flush(); //send output to browser at once
                     }
                     fclose($handle);
                  }

                  if(!$lines) { //Invalid URL
                    echo BLZ_LINK_ERROR_TEXT;
                    break;
                  }
                  foreach ($lines as $line) {
                    // to avoid dublettes, the unique flag
                    // "bankleitzahlführender Zahlungsdienstleister" will be queried
                    if (substr($line, 8, 1) == '1') {                //leading payment provider for bank code number (only one per bank code)
                      $blz['blz'] = substr($line, 0, 8);             //bank code number(8)
                      $blz['bankname'] = encode_utf8(trim(substr($line, 9, 58))); //bank name(58)
                      $blz['prz'] = substr($line, 150, 2);           //checksum(2)
                      $kennzeichen = substr($line, 158, 1); //change code(1)

                      /*
                      // check the change code of the bank code number
                      // "A" = Addition
                      // "D" = Deletion (do not import bank code numbers with this flag)
                      // "M" = Modified
                      // "U" = Unchanged
                      */
                      if ($kennzeichen!= 'D' && ($kennzeichen== 'A' || $kennzeichen == 'U' || $kennzeichen == 'M')) {
                        // Add the bank code number to the import array
                        $banktransfer[] = $blz;
                      }
                    }
                  }
                  // show process information
                  echo '<p>'.BLZ_DOWNLOADED_COUNT_TEXT.'</p>';
                  echo '<p><strong> --> '. count($banktransfer).'/'.$i. '</strong></p>';
                  echo '<p>'.BLZ_SOURCE_TEXT.'<a href="'.$blz_file.'">'.$blz_file.'</a></p><hr/><br/>';

                  // update the table only when the download of the bank code number file was successfull
                  if (count($banktransfer) > 1) {
                    // clear table banktransfer_blz
                    xtc_db_query("DELETE FROM ".TABLE_BANKTRANSFER_BLZ);
                    $j = 0;
                    // and fill it with the the content from the downloaded file
                    foreach ($banktransfer as $sql_data_array) {                      
                      xtc_db_perform(TABLE_BANKTRANSFER_BLZ, $sql_data_array);
                      if(xtc_db_affected_rows() != 0) {
                        $j = $j + xtc_db_affected_rows(); // sum up affected rows
                      }
                    }
                    echo '<span class="messageStackSuccess">'.$j.BLZ_UPDATE_SUCCESS_TEXT.'</span>';
                  } else {
                    echo '<span class="messageStackError">'.BLZ_UPDATE_ERROR_TEXT.'</span>';
                  }
                  echo '<p><a class="button" href="'.FILENAME_BLZ_UPDATE.'">'.BUTTON_BACK.'</a></p>';
                  break;

                default:
                  echo BLZ_INFO_TEXT;
                  echo '<p><a href="'.$blz_file_default_link.'" target="_blank"><b>'.$blz_file_default_link.'</b></a></p>';
                  echo xtc_draw_form('blz_update', 'blz_update.php', '', 'get');
                  echo '<input type="hidden" name="action" value="do_blz">';
                  echo '<table style="empty-cells:collapse; background:#FCFCFC; border-collapse:collapse;">';
                  echo xtc_draw_textarea_field('blz_file','','120%','2','');
                  echo '<tr style="text-align:right;">
                           <td colspan="2">
                             <input type="SUBMIT" class="button" value="'.BUTTON_UPDATE.'"'. $button_disabled .'>
                           </td>
                         </tr>
                         </table>
                         </form>';
                  break;
              }
          ?>
        </div>
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