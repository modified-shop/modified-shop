<?php
  /* --------------------------------------------------------------
   $Id: banner_manager.php 4564 2013-04-02 15:14:54Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner_manager.php,v 1.70 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (banner_manager.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (banner_manager.php 1030 2005-07-14)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $banner_extension = xtc_banner_image_extension();

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          xtc_set_banner_status($_GET['bID'], $_GET['flag']);
          $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }
        xtc_redirect(xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . (int)$_GET['bID']));
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['banners_id'])) $banners_id = xtc_db_prepare_input($_POST['banners_id']);
        $banners_title = xtc_db_prepare_input($_POST['banners_title']);
        $banners_url = xtc_db_prepare_input($_POST['banners_url']);
        $new_banners_group = xtc_db_prepare_input($_POST['new_banners_group']);
        $banners_group = (empty($new_banners_group)) ? xtc_db_prepare_input($_POST['banners_group']) : $new_banners_group;
        $html_text = xtc_db_prepare_input($_POST['html_text']);
        $banners_image_local = xtc_db_prepare_input($_POST['banners_image_local']);
        $banners_image_target = xtc_db_prepare_input($_POST['banners_image_target']);
        $db_image_location = '';
        $banner_error = false;
        if (empty($banners_title)) {
          $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }
        if (empty($banners_group)) {
          $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }
        if (empty($banners_image_local)) {
          $accepted_banners_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp","swf","cab");
          $accepted_banners_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp","application/x-shockwave-flash");
          //if (!$banners_image = xtc_try_upload('banners_image', DIR_FS_CATALOG_IMAGES.'banner/' . $banners_image_target, '', $accepted_banners_image_files_extensions, $accepted_banners_image_files_mime_types) && $_POST['banners_image_local'] == '') {
          if (!$banners_image = xtc_try_upload('banners_image', DIR_FS_CATALOG_IMAGES.'banner/' . $banners_image_target, '', $accepted_banners_image_files_extensions, $accepted_banners_image_files_mime_types)) { //Fix Dokuman - save banner image name im DB
            $messageStack->add(ERROR_BANNER_IMAGE_REQUIRED, 'error');
            $banner_error = true;
          }
        }
        if ($banner_error == false) {
          $db_image_location = (xtc_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                    'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                              'banners_html_text' => $html_text);
          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                         'status' => '1');
            $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
            xtc_db_perform(TABLE_BANNERS, $sql_data_array);
            $banners_id = xtc_db_insert_id();
            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            xtc_db_perform(TABLE_BANNERS, $sql_data_array, 'update', 'banners_id = \'' . (int)$banners_id . '\'');
            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }

          if ($_POST['expires_date']) {
            $expires_date = xtc_db_prepare_input($_POST['expires_date']);
            // BOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
            //list($day, $month, $year) = explode('/', $expires_date);
            list($year, $month, $day) = explode('-', $expires_date);
            // EOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format

            $expires_date = $year .
                            ((strlen($month) == 1) ? '0' . $month : $month) .
                            ((strlen($day) == 1) ? '0' . $day : $day);

            xtc_db_query("update " . TABLE_BANNERS . " set expires_date = '" . xtc_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
          } elseif ($_POST['impressions']) {
            $impressions = xtc_db_prepare_input($_POST['impressions']);
            xtc_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . xtc_db_input($impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
          }

          if ($_POST['date_scheduled']) {
            $date_scheduled = xtc_db_prepare_input($_POST['date_scheduled']);
            // BOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
            //list($day, $month, $year) = explode('/', $date_scheduled);
            list($year, $month, $day) = explode('-', $date_scheduled);
            // EOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format

            $date_scheduled = $year .
                              ((strlen($month) == 1) ? '0' . $month : $month) .
                              ((strlen($day) == 1) ? '0' . $day : $day);
            xtc_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . xtc_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
          }
          xtc_redirect(xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners_id));
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $banners_id = xtc_db_prepare_input($_GET['bID']);
        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $banner_query = xtc_db_query("select banners_image from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
          $banner = xtc_db_fetch_array($banner_query);
          if (is_file(DIR_FS_CATALOG_IMAGES . 'banner/' . $banners_image_target . $banner['banners_image'])) { //DokuMan - 2012-07-02 - Added missing path to subdirectory 'banner/' and $banners_image_target
            if (is_writeable(DIR_FS_CATALOG_IMAGES . 'banner/' . $banners_image_target . $banner['banners_image'])) { //DokuMan - 2012-07-02 - Added missing path to subdirectory 'banner/' and $banners_image_target
              unlink(DIR_FS_CATALOG_IMAGES .'banner/'. $banners_image_target . $banner['banners_image']); //DokuMan - 2012-07-02 - Added missing path to subdirectory 'banner/' and $banners_image_target
            } else {
              $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
            }
          } else {
            $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
          }
        }
        xtc_db_query("delete from " . TABLE_BANNERS . " where banners_id = '" . (int)$banners_id . "'");
        xtc_db_query("delete from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$banners_id . "'");
        if (function_exists('imagecreate') && xtc_not_null($banner_extension)) {
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
            if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
            if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
            if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banners_id . '.' . $banner_extension);
            }
          }
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
            if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banners_id . '.' . $banner_extension);
            }
          }
        }
        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');
        xtc_redirect(xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']));
        break;
    }
  }
  // check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && xtc_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

require (DIR_WS_INCLUDES.'head.php');
?>
    <script type="text/javascript">
      <!--
        function popupImageWindow(url) {
          window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
        }
      //-->
    </script>
    <?php
    //jQueryDatepicker
    require (DIR_WS_INCLUDES.'javascript/jQueryDatepicker/datepicker.js.php');
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
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
          <div class="flt-l">
            <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
          </div>
          <div class="clear"></div>          
            <?php
              if ($action == 'new') {
                $form_action = 'insert';
                if (isset($_GET['bID'])) {
                  $bID = xtc_db_prepare_input($_GET['bID']);
                  $form_action = 'update';
                  // BOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
                  //$banner_query = xtc_db_query("select banners_title, banners_url, banners_image, banners_group, banners_html_text, status, date_format(date_scheduled, '%d/%m/%Y') as date_scheduled, date_format(expires_date, '%d/%m/%Y') as expires_date, expires_impressions, date_status_change from " . TABLE_BANNERS . " where banners_id = '" . xtc_db_input($bID) . "'");
                  $banner_query = xtc_db_query("select banners_title, banners_url, banners_image, banners_group, banners_html_text, status, date_format(date_scheduled, '%Y-%m-%d') as date_scheduled, date_format(expires_date, '%Y-%m-%d') as expires_date, expires_impressions, date_status_change from " . TABLE_BANNERS . " where banners_id = '" . (int)$bID . "'");
                  // EOF - Tomcraft - 2009-11-06 - Use "iso 8601" for the date format
                  $banner = xtc_db_fetch_array($banner_query);
                  $bInfo = new objectInfo($banner);
                } elseif (xtc_not_null($_POST)) {
                  $bInfo = new objectInfo($_POST);
                } else {
                  $bInfo = new objectInfo(array());
                }
                $groups_array = array();
                $groups_query = xtc_db_query("select distinct banners_group from " . TABLE_BANNERS . " order by banners_group");
                while ($groups = xtc_db_fetch_array($groups_query)) {
                  $groups_array[] = array('id' => $groups['banners_group'], 'text' => $groups['banners_group']);
                }
              ?>              
              <script type="text/javascript">
                /* set Datepicker for dateExpires (1) and dateScheduled (2) */
                $(function() {
                  $('#Datepicker1').datepick();
                  $('#Datepicker2').datepick();
                });
              </script>              
             
                  <?php echo xtc_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo xtc_draw_hidden_field('banners_id', $bID); ?>
                
                    <table class="tableConfig">
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_TITLE; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('banners_title', $bInfo->banners_title, '', true); ?></td>
                        <td class="dataTableConfig col-right">&nbsp;</td>
                      </tr>
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_URL; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('banners_url', $bInfo->banners_url); ?></td>
                        <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_URL_NOTE; ?></td>
                      </tr>
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_GROUP; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group) . '<br />' . xtc_draw_input_field('new_banners_group', '', '', ((sizeof($groups_array) > 0) ? false : true)); ?></td>
                        <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_NEW_GROUP; ?></td>
                      </tr>                      
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_IMAGE; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_file_field('banners_image') . ' ' . '<br />' . xtc_draw_input_field('banners_image_local', $bInfo->banners_image, '', true); ?></td>
                        <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_IMAGE_LOCAL;?></td>
                      </tr>                      
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('banners_image_target'); ?></td>
                        <td class="dataTableConfig col-right"><?php echo DIR_FS_CATALOG_IMAGES.'banner/'; ?></td>
                      </tr>                      
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
                        <td class="dataTableConfig col-middle"><?php echo xtc_draw_textarea_field('html_text', 'soft', '40', '5', $bInfo->banners_html_text); ?></td>
                        <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_HTML_TEXT_NOTE; ?></td>
                      </tr>                      
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?><br /><small><?php echo TEXT_BANNERS_DATE_FORMAT; ?></small></td>
                        <td class="dataTableConfig col-middle">
                          <?php echo xtc_draw_input_field('dateScheduled', $bInfo->date_scheduled ,'id="Datepicker1"'); ?>
                        </td>
                        <td class="dataTableConfig col-right">&nbsp;</td>
                      </tr>                     
                      <tr>
                        <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_EXPIRES_ON; ?><br /><small><?php echo TEXT_BANNERS_DATE_FORMAT; ?></small></td>
                        <td class="dataTableConfig col-middle">
                          <?php echo xtc_draw_input_field('dateExpires', $bInfo->expires_date ,'id="Datepicker2"'); ?>
                          <?php echo TEXT_BANNERS_OR_AT . '<br />' . xtc_draw_input_field('impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?>
                        </td>
                        <td class="dataTableConfig col-right">&nbsp;</td>
                      </tr>
                    </table>            
                    
                    <div class="main" style="padding:6px;border-bottom: 1px solid #a3a3a3;">
                      <?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?>
                    </div>
                    <div class="main mrg5 nobr">
                      <?php echo (($form_action == 'insert') ? '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>' : '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>'). '&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] : '')) . '">' . BUTTON_CANCEL . '</a>'; ?>
                    </div>
                
                </form>
            </td>
            <?php              
            } else {
            ?>
            <table class="tableCenter">
              <tr>
                <td class="boxCenterLeft">
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BANNERS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_GROUPS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_STATISTICS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_STATUS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
                      $banners_query_raw = "select banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added from " . TABLE_BANNERS . " order by banners_title, banners_group";
                      $banners_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
                      $banners_query = xtc_db_query($banners_query_raw);
                      while ($banners = xtc_db_fetch_array($banners_query)) {
                        $info_query = xtc_db_query("select sum(banners_shown) as banners_shown, sum(banners_clicked) as banners_clicked from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . $banners['banners_id'] . "'");
                        $info = xtc_db_fetch_array($info_query);
                        if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ($_GET['bID'] == $banners['banners_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
                          $bInfo_array = xtc_array_merge($banners, $info);
                          $bInfo = new objectInfo($bInfo_array);
                        }
                        $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
                        $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';
                        if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id) ) {
                          $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '\'"';
                        } else {
                          $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '\'"';
                        }
                        ?>
                        <tr <?php echo $tr_attributes;?>>
                          <td class="dataTableContent"><?php echo '<a href="javascript:popupImageWindow(\'' . FILENAME_POPUP_IMAGE . '?banner=' . $banners['banners_id'] . '\')">' . xtc_image(DIR_WS_IMAGES . 'icon_popup.gif', ICON_POPUP) . '</a>&nbsp;' . $banners['banners_title']; ?></td>
                          <td class="dataTableContent txta-r"><?php echo $banners['banners_group']; ?></td>
                          <td class="dataTableContent txta-r"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                          <td class="dataTableContent txta-r">
                            <?php                                      
                              if ($banners['status'] == '1') {
                                echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=0') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                              } else {
                                echo '<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id'] . '&action=setflag&flag=1') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                              }
                            ?>
                          </td>
                          <td class="dataTableContent txta-r"><?php echo '<a href="' . xtc_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '">' . xtc_image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS) . '</a>&nbsp;'; if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                        </tr>
                        <?php
                      }
                      ?>
                    <tr>                      
                  </table>
                  
                  <div class="smallText pdg2 flt-l"><?php echo $banners_split->display_count($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></div>
                  <div class="smallText pdg2 flt-r"><?php echo $banners_split->display_links($banners_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
                  <div class="clear"></div>
                  <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'action=new') . '">' . BUTTON_NEW_BANNER . '</a>'; ?></div>
                
                </td>
                <?php
                  $heading = array();
                  $contents = array();
                  switch ($action) {
                    case 'delete':
                      $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');
                      $contents = array('form' => xtc_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm'));
                      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                      $contents[] = array('text' => '<br /><b>' . $bInfo->banners_title . '</b>');
                      if ($bInfo->banners_image)
                        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . BUTTON_CANCEL . '</a>');
                      break;
                    default:
                      if (isset($bInfo) && is_object($bInfo)) {
                        $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');
                        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                        $contents[] = array('text' => '<br />' . TEXT_BANNERS_DATE_ADDED . ' ' . xtc_date_short($bInfo->date_added));
                        if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
                          $banner_id = $bInfo->banners_id;
                          $days = '3';
                          include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
                          $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
                        } else {
                          include(DIR_WS_FUNCTIONS . 'html_graphs.php');
                          $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_banner_graph_infoBox($bInfo->banners_id, '3'));
                        }
                        $contents[] = array('text' => xtc_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . xtc_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);
                        if ($bInfo->date_scheduled)
                          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, xtc_date_short($bInfo->date_scheduled)));
                        if ($bInfo->expires_date) {
                          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, xtc_date_short($bInfo->expires_date)));
                        } elseif ($bInfo->expires_impressions) {
                          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
                        }
                        if ($bInfo->date_status_change)
                          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_STATUS_CHANGE, xtc_date_short($bInfo->date_status_change)));
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
          <?php
          }
          ?>
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
