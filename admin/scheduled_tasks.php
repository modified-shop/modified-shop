<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');

  // include needed function
  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');

  // set languages
  $languages = xtc_get_languages();

  $cfg_max_display_results_key = 'MAX_DISPLAY_SCHEDULED_TASKS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  $sorting = (isset($_GET['sorting']) ? $_GET['sorting'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'setflag':
        $tasks_id = xtc_db_prepare_input($_GET['tID']);
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          xtc_db_query("UPDATE ".TABLE_SCHEDULED_TASKS."
                           SET status = '".(int)$_GET['flag']."'
                         WHERE tasks_id = '".(int)$tasks_id."'
                           AND edit = 1");
        }
        xtc_redirect(xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action', 'flag')) . 'tID=' . $tasks_id));
        break;
      case 'save':
        $tasks_id = xtc_db_prepare_input($_GET['tID']);
        $time_regularity = xtc_db_prepare_input($_POST['time_regularity']);
        $time_unit = xtc_db_prepare_input($_POST['time_unit']);
        $time_offset = xtc_db_prepare_input($_POST['time_offset']);

        $sql_data_array = array(
          'time_regularity' => $time_regularity, 
          'time_unit' => $time_unit, 
          'time_offset' => strtotime('1970-01-01 '.$time_offset.' UTC'),
        ); 
               
        xtc_db_perform(TABLE_SCHEDULED_TASKS, $sql_data_array, 'update', "tasks_id = '" . (int)$tasks_id . "' AND edit = 1");

        xtc_redirect(xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $tasks_id));
        break;
    }
  }
  
  $unit_array = array(
    'm' => TEXT_TIME_MINUTE,
    'h' => TEXT_TIME_HOUR,
    'd' => TEXT_TIME_DAY,
    'w' => TEXT_TIME_WEEK,
  );
  
  $time_unit_array = array();
  $time_unit_array[] = array('id' => '0', 'text' => PULL_DOWN_DEFAULT);
  foreach ($unit_array as $k => $v) {
    $time_unit_array[] = array('id' => $k, 'text' => $v);
  }
  
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TASKS; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TIME_NEXT.xtc_sorting(FILENAME_SCHEDULED_TASKS, 'next'); ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_INTERVAL.xtc_sorting(FILENAME_SCHEDULED_TASKS, 'unit'); ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS.xtc_sorting(FILENAME_SCHEDULED_TASKS, 'status'); ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                  <?php
                    $tsort = '';
                    switch ($sorting) {
                      case 'next':
                        $tsort = 'time_next ASC';
                        break;
                      case 'next-desc':
                        $tsort = 'time_next DESC';
                        break;
                      case 'unit':
                        $tsort = 'time_unit ASC';
                        break;
                      case 'unit-desc':
                        $tsort = 'time_unit DESC';
                        break;
                      case 'status':
                        $tsort = 'status ASC';
                        break;
                      case 'status-desc':
                        $tsort = 'status DESC';
                        break;
                    }
                    $tsort .= (($tsort != '') ? ', ' : '').'tasks_id ASC';
                    
                    $tasks_query_raw = "SELECT *
                                          FROM ".TABLE_SCHEDULED_TASKS."
                                      ORDER BY ".$tsort;
                    $tasks_split = new splitPageResults($page, $page_max_display_results, $tasks_query_raw, $tasks_query_numrows);
                    $tasks_query = xtc_db_query($tasks_query_raw);
                    while ($tasks = xtc_db_fetch_array($tasks_query)) {
                      $tasks['time_next_plain'] = (($tasks['time_next'] > 0) ? $tasks['time_next'] : CRONJOB_NEXT_EVENT_TIME);

                      if ((!isset($_GET['tID']) || $_GET['tID'] == $tasks['tasks_id']) && !isset($trInfo)) {
                        $tasks['time_run'] = 0;
                        $tasks['time_taken'] = 0;
                        $log_query = xtc_db_query("SELECT *
                                                     FROM ".TABLE_SCHEDULED_TASKS_LOG."
                                                    WHERE tasks_id = '".(int)$tasks['tasks_id']."'
                                                 ORDER BY time_run DESC 
                                                    LIMIT 1");
                        if (xtc_db_num_rows($log_query) > 0) {
                          $log = xtc_db_fetch_array($log_query);
                          $tasks['time_run'] = $log['time_run'];
                          $tasks['time_taken'] = $log['time_taken'];
                        }
                        $trInfo = new objectInfo($tasks);
                      }

                      if (isset($trInfo) && is_object($trInfo) && $tasks['tasks_id'] == $trInfo->tasks_id) {
                        echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" '.(($trInfo->edit == '1') ? 'onclick="document.location.href=\'' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $trInfo->tasks_id . '&action=edit') . '\'"' : '').'>' . "\n";
                      } else {
                        echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $tasks['tasks_id']) . '\'">' . "\n";
                      }
                      ?>
                      <td class="dataTableContent"><?php echo (defined('TEXT_INFO_TASKS_'.strtoupper($tasks['tasks'])) ? constant('TEXT_INFO_TASKS_'.strtoupper($tasks['tasks'])) : $tasks['tasks']); ?></td>
                      <td class="dataTableContent"><?php echo (($tasks['status'] == 1) ? xtc_datetime_short($tasks['time_unit'] == 'm' ? date('Y-m-d H:i:s', $tasks['time_next_plain']) : gmdate('Y-m-d H:i:s', $tasks['time_next_plain'])) : 'n/a'); ?></td>
                      <td class="dataTableContent"><?php echo (($tasks['time_unit'] == 'o') ? TEXT_INFO_ONETIME : sprintf(TEXT_INFO_INTERVALL, gmdate('H:i', $tasks['time_offset']), $tasks['time_regularity'].' '.$unit_array[$tasks['time_unit']])); ?></td>
                      <td class="dataTableContent txta-c">
                        <?php                                      
                          if ($tasks['status'] == '1') {
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 12, 12) . '&nbsp;&nbsp;';
                            if ($tasks['edit'] == '1') {
                              echo '<a href="' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action', 'flag')) . 'tID=' . $tasks['tasks_id'] . '&action=setflag&flag=0') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12) . '</a>';
                            } else {
                              echo xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 12, 12);
                            }
                          } else {
                            if ($tasks['edit'] == '1') {
                              echo '<a href="' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action', 'flag')) . 'tID=' . $tasks['tasks_id'] . '&action=setflag&flag=1') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12) . '</a>&nbsp;&nbsp;';
                            } else {
                              echo xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 12, 12);
                            }
                            echo xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 12, 12);
                          }
                        ?>
                      </td>
                      <td class="dataTableContent txta-r"><?php if (isset($trInfo) && is_object($trInfo) && $tasks['tasks_id'] == $trInfo->tasks_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $tasks['tasks_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                    </tr>
                    <?php
                  }
                ?>
              </table>
                
              <div class="smallText pdg2 flt-l"><?php echo $tasks_split->display_count($tasks_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_SCHEDULED_TASKS); ?></div> 
              <div class="smallText pdg2 flt-r"><?php echo $tasks_split->display_links($tasks_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page); ?></div> 
              <?php echo draw_input_per_page($PHP_SELF, $cfg_max_display_results_key, $page_max_display_results); ?>
            </td>
            <?php
              $heading = array();
              $contents = array();
              switch ($action) {
                case 'edit':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TASKS . '</b>');

                  $contents = array('form' => xtc_draw_form('rates', FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $trInfo->tasks_id  . '&action=save'));
                  $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                  
                  $contents[] = array('text' => '<br />' . TEXT_INFO_TIME_REGULARITY . '<br />' . xtc_draw_input_field('time_regularity', $trInfo->time_regularity));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_TIME_UNIT . '<br />' . xtc_draw_pull_down_menu('time_unit', $time_unit_array, $trInfo->time_unit));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_TIME_OFFSET . '<br />' . xtc_draw_input_field('time_offset', gmdate('H:i', $trInfo->time_offset)));
                                    
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $trInfo->tasks_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                default:
                  if (isset($trInfo) && is_object($trInfo)) {
                    $heading[] = array('text' => '<b>' . (defined('TEXT_HEADING_TASKS_'.strtoupper($trInfo->tasks)) ? constant('TEXT_INFO_TASKS_'.strtoupper($trInfo->tasks)) : $trInfo->tasks) . '</b>');
                    if (defined('TEXT_INFO_TASKS_'.strtoupper($trInfo->tasks))) {
                      $contents[] = array('text' => constant('TEXT_INFO_TASKS_'.strtoupper($trInfo->tasks)));
                    }
                    if ($trInfo->edit == 1) {
                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SCHEDULED_TASKS, xtc_get_all_get_params(array('tID', 'action')) . 'tID=' . $trInfo->tasks_id . '&action=edit') . '">' . BUTTON_EDIT . '</a>');
                    }
                    if ($trInfo->time_run > 0) {
                      $contents[] = array('text' => '<br />' . TEXT_INFO_LAST_EXECUTED . ' ' . xtc_datetime_short(date('Y-m-d H:i:s', $trInfo->time_run)));
                      $contents[] = array('text' => '<br />' . TEXT_INFO_LAST_DURATION . ' ' . $trInfo->time_taken.'s');
                    }
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