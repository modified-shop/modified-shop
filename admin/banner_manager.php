<?php
  /* --------------------------------------------------------------
   $Id$

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
  $languages = xtc_get_languages();

  $lang_array = array();
  $lang_array_id = array();
  for ($i = 0, $n = count($languages); $i < $n; $i++) {
    $lang_array[] = array('id' => $languages[$i]['id'], 'text' => $languages[$i]['name']);
    $lang_array_id[$languages[$i]['id']] = $languages[$i]['name'];
  }

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
        $new_banners_group = xtc_db_prepare_input(strtolower($_POST['new_banners_group']));
        $banners_group = ((empty($new_banners_group)) ? xtc_db_prepare_input($_POST['banners_group']) : $new_banners_group);
        $banners_sort = (int)$_POST['banners_sort'];
        $banners_group_id = (int)$_POST['banners_group_id'];
      
        $banner_error = false;
        if (empty($banners_group)) {
          $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }
      
        //store banners_image
        $accepted_banners_image_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp","swf","cab");
        $accepted_banners_image_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp","application/x-shockwave-flash");
        
        for ($i = 0, $n = count($languages); $i < $n; $i++) {
          $banners_title = xtc_db_prepare_input($_POST['banners_title'][$languages[$i]['id']]);
          $html_text = xtc_db_prepare_input($_POST['html_text'][$languages[$i]['id']]);
          $banners_image_exist = xtc_db_prepare_input($_POST['banners_image_exist'][$languages[$i]['id']]);
          ${'banners_image_'.$languages[$i]['id']} = xtc_try_upload('banners_image_'.$languages[$i]['id'], DIR_FS_CATALOG_IMAGES.'banner/', '644', $accepted_banners_image_files_extensions, $accepted_banners_image_files_mime_types);
          
          /*          
          if (empty($banners_title)) {
            $messageStack->add(strtoupper($languages[$i]['code']) . ': ' . ERROR_BANNER_TITLE_REQUIRED, 'error');
            $banner_error = true;
          }

          ${'banners_image_'.$languages[$i]['id']} = xtc_try_upload('banners_image_'.$languages[$i]['id'], DIR_FS_CATALOG_IMAGES.'banner/', '644', $accepted_banners_image_files_extensions, $accepted_banners_image_files_mime_types);
          if ($banners_image_exist == '' && $html_text == '' && !${'banners_image_'.$languages[$i]['id']}) {
            $messageStack->add(strtoupper($languages[$i]['code']) . ': ' . ERROR_BANNER_IMAGE_HTML_REQUIRED, 'error');
            $banner_error = true;
          }
          */
        }
        
        if ($banner_error === false) {
          for ($i = 0, $n = count($languages); $i < $n; $i++) {
           
            $banners_id = NULL;
            if (isset($_POST['banners_id'][$languages[$i]['id']])) $banners_id = xtc_db_prepare_input($_POST['banners_id'][$languages[$i]['id']]);
            $banners_title = xtc_db_prepare_input($_POST['banners_title'][$languages[$i]['id']]);
            $banners_url = xtc_db_prepare_input($_POST['banners_url'][$languages[$i]['id']]);
            $html_text = xtc_db_prepare_input($_POST['html_text'][$languages[$i]['id']]);
            $banners_image_exist = xtc_db_prepare_input($_POST['banners_image_exist'][$languages[$i]['id']]);

            if (isset($_POST['del_image_'.$languages[$i]['id']])
                && $_POST['del_image_'.$languages[$i]['id']] != ''
                )
            {
              $image_location = DIR_FS_CATALOG_IMAGES . 'banner/' . $_POST['del_image_'.$languages[$i]['id']];
              if (is_file($image_location)) {
                $banners_image_exist = '';
                @unlink($image_location);
              }
            }
      
            // new banner available & delete old
            if (is_object(${'banners_image_'.$languages[$i]['id']}) && ${'banners_image_'.$languages[$i]['id']}->filename != '') {
              $banners_image_exist = ${'banners_image_'.$languages[$i]['id']}->filename;
            }

            $sql_data_array = array(
              'banners_title' => $banners_title,
              'banners_url' => $banners_url,
              'languages_id' => $languages[$i]['id'],
              'banners_group' => $banners_group,
              'banners_image' => $banners_image_exist,
              'banners_html_text' => $html_text,
              'banners_sort' => $banners_sort,
              'banners_group_id' => $banners_group_id,
              'expires_date' => 'null',
              'expires_impressions' => 'null',
              'date_scheduled' => 'null',
            );
                                    
            if ($action == 'insert') {
              $sql_data_array['date_added'] = 'now()';
              $sql_data_array['status'] = '0';
              xtc_db_perform(TABLE_BANNERS, $sql_data_array);
              $banners_id = xtc_db_insert_id();
            } elseif ($action == 'update') {
              $sql_data_array['date_status_change'] = 'now()';
              xtc_db_perform(TABLE_BANNERS, $sql_data_array, 'update', "banners_id = '" . (int)$banners_id . "'");
            }

            if ($_POST['expires_date'] != '' && $_POST['expires_date'] != '0000-00-00 00:00:00') {          
              $expires_date = date('Y-m-d H:i:s', strtotime($_POST['expires_date']));
              xtc_db_query("update " . TABLE_BANNERS . " set expires_date = '" . xtc_db_input($expires_date) . "', expires_impressions = null where banners_id = '" . (int)$banners_id . "'");
            } elseif ($_POST['expires_impressions'] != '' && $_POST['expires_impressions'] != '0') {
              $expires_impressions = xtc_db_prepare_input($_POST['expires_impressions']);
              xtc_db_query("update " . TABLE_BANNERS . " set expires_impressions = '" . xtc_db_input($expires_impressions) . "', expires_date = null where banners_id = '" . (int)$banners_id . "'");
            }

            if ($_POST['date_scheduled'] != '' && $_POST['date_scheduled'] != '0000-00-00 00:00:00') {
              $date_scheduled = date('Y-m-d H:i:s', strtotime($_POST['date_scheduled']));
              xtc_db_query("update " . TABLE_BANNERS . " set status = '0', date_scheduled = '" . xtc_db_input($date_scheduled) . "' where banners_id = '" . (int)$banners_id . "'");
            }
          }

          if ($action == 'insert') {
            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }
        } else {
          $action = 'new';
          
          // remove uploaded images
          for ($i = 0, $n = count($languages); $i < $n; $i++) {
            if (is_file(DIR_FS_CATALOG_IMAGES.'banner/'.${'banners_image_'.$languages[$i]['id']}->filename)) {
              unlink(DIR_FS_CATALOG_IMAGES.'banner/'.${'banners_image_'.$languages[$i]['id']}->filename);
            }
          }
        }

        if ($action != 'new') {
          xtc_redirect(xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners_group_id));
        }
        break;
      
      case 'deleteconfirm':
        $banners_group_id = xtc_db_prepare_input($_GET['bID']);
        $banner_query = xtc_db_query("SELECT banners_image, 
                                             banners_id 
                                        FROM " . TABLE_BANNERS . " 
                                       WHERE banners_group_id = '" . (int)$banners_group_id . "'");
        while ($banner = xtc_db_fetch_array($banner_query)) {
          if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
            if (is_file(DIR_FS_CATALOG_IMAGES . 'banner/' . $banners_image_target . $banner['banners_image'])) {
              if (is_writeable(DIR_FS_CATALOG_IMAGES . 'banner/' . $banners_image_target . $banner['banners_image'])) {
                unlink(DIR_FS_CATALOG_IMAGES .'banner/'. $banners_image_target . $banner['banners_image']);
              } else {
                $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
              }
            } else {
              $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
            }
          }
          if (function_exists('imagecreate') && xtc_not_null($banner_extension)) {
            if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner['banners_id'] . '.' . $banner_extension)) {
              if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner['banners_id'] . '.' . $banner_extension)) {
                unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner['banners_id'] . '.' . $banner_extension);
              }
            }
            if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner['banners_id'] . '.' . $banner_extension)) {
              if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner['banners_id'] . '.' . $banner_extension)) {
                unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner['banners_id'] . '.' . $banner_extension);
              }
            }
            if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner['banners_id'] . '.' . $banner_extension)) {
              if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner['banners_id'] . '.' . $banner_extension)) {
                unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner['banners_id'] . '.' . $banner_extension);
              }
            }
            if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner['banners_id'] . '.' . $banner_extension)) {
              if (is_writeable(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner['banners_id'] . '.' . $banner_extension)) {
                unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner['banners_id'] . '.' . $banner_extension);
              }
            }
          }

          xtc_db_query("DELETE FROM " . TABLE_BANNERS_HISTORY . " WHERE banners_id = '" . $banner['banners_id'] . "'");
        }
        xtc_db_query("DELETE FROM " . TABLE_BANNERS . " WHERE banners_group_id = '" . (int)$banners_group_id . "'");
        
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

  if (USE_WYSIWYG == 'true') {
    require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');
    if (isset($_GET['action']) && $_GET['action'] == 'new') {
      echo PHP_EOL . (!function_exists('editorJSLink') ? '<script type="text/javascript" src="includes/modules/fckeditor/fckeditor.js"></script>' : '') . PHP_EOL;
      for ($i = 0; $i < count($languages); $i++) {
        echo xtc_wysiwyg('banner_manager', $data['code'], $languages[$i]['id']);
      }
    }
  }

  //jQueryDatepicker
  require (DIR_WS_INCLUDES.'javascript/jQueryDateTimePicker/datepicker.js.php');
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
        <div class="flt-l" style="min-width: 300px;">
          <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
          <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>
          <div class="main pdg2 flt-l">Tools</div>
        </div>
          <?php
          if ($action == 'new') {
            $form_action = 'insert';
            if (xtc_not_null($_POST)) {
              if (isset($_GET['bID'])) {
                $form_action = 'update';
              }
              $bInfo = new objectInfo($_POST);
            } elseif (isset($_GET['bID'])) {
              $form_action = 'update';
              $bID = xtc_db_prepare_input($_GET['bID']);
              $banner_query = xtc_db_query("SELECT *,
                                                   date_format(date_scheduled, '%Y-%m-%d') as date_scheduled, 
                                                   date_format(expires_date, '%Y-%m-%d') as expires_date
                                              FROM " . TABLE_BANNERS . " 
                                             WHERE banners_group_id = '" . (int)$bID . "'");
              $banner = xtc_db_fetch_array($banner_query);
              $bInfo = new objectInfo($banner);
            } else {
              $bInfo = new objectInfo(array());
            }

            $groups_array = array(
              array('id' => 'banner', 'text' => 'BANNER'),
              array('id' => 'slider', 'text' => 'SLIDER'),
            );              

            // banner file
            $files = array();
            if ($dir= opendir(DIR_FS_CATALOG.'images/banner/')) {
              while (($file = readdir($dir)) !== false) {
                if (is_file( DIR_FS_CATALOG.'images/banner/'.$file) and ($file != 'index.html')) {
                  $files[] = array('id' => $file,
                                   'text' => $file);
                }
              }
              closedir($dir);
              sort($files);
            }      

            $groups_query = xtc_db_query("SELECT DISTINCT banners_group 
                                                     FROM " . TABLE_BANNERS . " 
                                                    WHERE banners_group != 'banner'
                                                      AND banners_group != 'slider'
                                                 ORDER BY banners_group");
            while ($groups = xtc_db_fetch_array($groups_query)) {
              $groups_array[] = array('id' => $groups['banners_group'], 'text' => strtoupper($groups['banners_group']));
            }
            
            if ($form_action == 'insert') {
              $group_id_query = xtc_db_query("SELECT max(banners_group_id) as banners_group_id
                                                FROM ".TABLE_BANNERS);
              $group_id = xtc_db_fetch_array($group_id_query);
              $bInfo->banners_group_id = $group_id['banners_group_id'] + 1;
            }
            echo xtc_draw_form('new_banner', FILENAME_BANNER_MANAGER, (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] . '&' : '') . (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'action=' . $form_action, 'post', 'enctype="multipart/form-data"'); 
            echo xtc_draw_hidden_field('banners_group_id', $bInfo->banners_group_id);
              ?>
              <div style="padding:5px 10px 20px 5px;clear:both;">
                <table class="tableConfig">
                  <tr>
                    <td class="dataTableConfig col-left" rowspan="2" style="width: 20%; border-left: 1px solid #ccc;"><?php echo TEXT_BANNERS_NEW_GROUP; ?></td>
                    <td class="dataTableConfig col-middle" style="width:50%"><?php echo xtc_draw_pull_down_menu('banners_group', $groups_array, $bInfo->banners_group); ?></td>
                    <td class="dataTableConfig col-right" rowspan="2" style="border-right: 1px solid #ccc;"><?php echo TEXT_BANNERS_NEW_GROUP_NOTE; ?></td>
                  </tr> 
                  <tr>
                    <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('new_banners_group'); ?></td>
                  </tr> 
                  <tr>
                    <td class="dataTableConfig col-left" style="border-left: 1px solid #ccc;"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?><br /><small><?php echo TEXT_BANNERS_DATE_FORMAT; ?></small></td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('date_scheduled', $bInfo->date_scheduled ,'id="Datepicker1" style="width:155px"'); ?>
                    </td>
                    <td class="dataTableConfig col-right" style="border-right: 1px solid #ccc;">&nbsp;</td>
                  </tr>                     
                  <tr>
                    <td class="dataTableConfig col-left" style="border-left: 1px solid #ccc;"><?php echo TEXT_BANNERS_EXPIRES_ON; ?><br /><small><?php echo TEXT_BANNERS_DATE_FORMAT; ?></small></td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('expires_date', $bInfo->expires_date ,'id="Datepicker2" style="width:155px"'); ?>
                      <?php echo TEXT_BANNERS_OR_AT . '<br />' . xtc_draw_input_field('expires_impressions', $bInfo->expires_impressions, 'style="width:155px"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?>
                    </td>
                    <td class="dataTableConfig col-right" style="border-right: 1px solid #ccc;">&nbsp;</td>
                  </tr>
                  <tr>
                    <td class="dataTableConfig col-left" style="border-left: 1px solid #ccc;"><?php echo TEXT_BANNERS_SORT; ?></td>
                    <td class="dataTableConfig col-middle">
                      <?php echo xtc_draw_input_field('banners_sort', $bInfo->banners_sort, 'style="width:155px"'); ?>
                    </td>
                    <td class="dataTableConfig col-right" style="border-right: 1px solid #ccc;"><?php echo TEXT_BANNERS_SORT_NOTE; ?></td>
                  </tr>                     
                </table>
              </div>
            
              <div style="padding:5px;clear:both;">
                <?php
                include('includes/lang_tabs.php');
                for ($i = 0, $n = count($languages); $i < $n; $i++) {
                  echo ('<div id="tab_lang_' . $i . '">');
                  if (xtc_not_null($_POST)) {
                    $banner = array();
                    foreach ($_POST as $key => $value) {
                      $banner[$key] = ((is_array($value)) ? $value[$languages[$i]['id']] : $value);
                    }
                    $bInfo = new objectInfo($banner);                  
                  } elseif (isset($bInfo->banners_group)) {
                    $banner_query = xtc_db_query("SELECT *,
                                                         banners_image as banners_image_exist,
                                                         date_format(date_scheduled, '%Y-%m-%d') as date_scheduled, 
                                                         date_format(expires_date, '%Y-%m-%d') as expires_date
                                                    FROM " . TABLE_BANNERS . " 
                                                   WHERE banners_group_id = '" . xtc_db_input($bInfo->banners_group_id) . "'
                                                     AND languages_id = '".$languages[$i]['id']."'");
                    $banner = xtc_db_fetch_array($banner_query);
                    $bInfo = new objectInfo($banner);
                  } else {
                    $bInfo = new objectInfo(array());
                  }
                  if ($bInfo->banners_id != '') {
                    echo xtc_draw_hidden_field('banners_id[' . $languages[$i]['id'] . ']', $bInfo->banners_id); 
                  }
                  ?>
                  <table class="tableConfig">
                    <tr>
                      <td class="dataTableConfig col-left" style="width:20%"><?php echo TEXT_BANNERS_TITLE; ?></td>
                      <td class="dataTableConfig col-middle" style="width:50%"><?php echo xtc_draw_input_field('banners_title[' . $languages[$i]['id'] . ']', $bInfo->banners_title); ?></td>
                      <td class="dataTableConfig col-right">&nbsp;</td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_URL; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_input_field('banners_url[' . $languages[$i]['id'] . ']', $bInfo->banners_url); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_URL_NOTE; ?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_IMAGE; ?></td>
                      <td class="dataTableConfig col-middle">
                        <table class="tableConfig borderall">
                          <?php if ($bInfo->banners_image_exist != '') { ?>
                            <tr>
                              <td class="main"><img style="max-width:360px; margin-bottom:10px;" src="<?php echo DIR_WS_CATALOG_IMAGES . 'banner/'.$bInfo->banners_image_exist; ?>" /></td>
                            </tr>
                            <tr>
                              <td class="main"><?php echo xtc_draw_checkbox_field('del_image_'.$languages[$i]['id'], $bInfo->banners_image_exist) . ' ' . TEXT_INFO_DELETE_IMAGE; ?></td>
                            </tr>    
                          <?php } ?>
                          <tr>
                            <td class="main"><?php echo xtc_draw_file_field('banners_image_'.$languages[$i]['id']); ?></td>
                          </tr>    
                          <tr>
                            <td class="main"><?php echo xtc_draw_pull_down_menu('banners_image_exist[' . $languages[$i]['id'] . ']', array_merge(array(array('id' => '','text' => (($bInfo->banners_image_exist != '') ? TEXT_NO_FILE : TEXT_SELECT))), $files), $bInfo->banners_image_exist); ?></td>
                          </tr>
                        </table>
                      </td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_IMAGE_LOCAL;?></td>
                    </tr>
                    <tr>
                      <td class="dataTableConfig col-left"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
                      <td class="dataTableConfig col-middle"><?php echo xtc_draw_textarea_field('html_text[' . $languages[$i]['id'] . ']', 'soft', '40', '5', $bInfo->banners_html_text, 'class="textareaModule"'); ?></td>
                      <td class="dataTableConfig col-right"><?php echo TEXT_BANNERS_HTML_TEXT_NOTE; ?></td>
                    </tr>                      
                  </table>
                  <?php
                  echo ('</div>');
                }
                ?>
              </div>
              <div class="pdg2 flt-r">
                <?php echo (($form_action == 'insert') ? '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>' : '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'). '&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['bID']) ? 'bID=' . $_GET['bID'] : '')) . '">' . BUTTON_CANCEL . '</a>'; ?>
              </div>
        
              <div class="pdg2 customers-groups smallText" style="width:100%;margin-top:10px;">
                <?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?>
              </div>          
            </form>
          <?php              
          } else {
            ?>
            <table class="tableCenter">
              <tr>
                <td class="boxCenterLeft">
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent txta-c" style="width:20%;"><?php echo TABLE_HEADING_IMAGE; ?></td>
                      <td class="dataTableHeadingContent txta-c" style="width:8%;"><?php echo TABLE_HEADING_SORT; ?></td>
                      <td class="dataTableHeadingContent" style="width:40%;"><?php echo TABLE_HEADING_BANNERS; ?></td>
                      <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_GROUPS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_STATISTICS; ?></td>
                      <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS; ?></td>
                      <td class="dataTableHeadingContent txta-r" style="width:5%;"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
                      $banners_query_raw = "SELECT * 
                                              FROM " . TABLE_BANNERS . " 
                                             WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                                          ORDER BY banners_group, banners_sort";
                      $banners_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $banners_query_raw, $banners_query_numrows);
                      $banners_query = xtc_db_query($banners_query_raw);
                      while ($banners = xtc_db_fetch_array($banners_query)) {
                        $info_query = xtc_db_query("SELECT sum(banners_shown) as banners_shown, 
                                                           sum(banners_clicked) as banners_clicked 
                                                      FROM " . TABLE_BANNERS_HISTORY . " 
                                                     WHERE banners_id = '" . $banners['banners_id'] . "'");
                        $info = xtc_db_fetch_array($info_query);
                        if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ($_GET['bID'] == $banners['banners_group_id']))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
                          $bInfo_array = array_merge($banners, $info);
                          $bInfo = new objectInfo($bInfo_array);
                        }
                        $banners_shown = ($info['banners_shown'] != '') ? $info['banners_shown'] : '0';
                        $banners_clicked = ($info['banners_clicked'] != '') ? $info['banners_clicked'] : '0';
                        if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id) ) {
                          $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_group_id) . '\'"';
                        } else {
                          $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_group_id']) . '\'"';
                        }
                        ?>
                        <tr <?php echo $tr_attributes;?>>
                          <td class="dataTableContent"><img style="border:0;max-width:200px;max-height:60px;" src="<?php echo DIR_WS_CATALOG_IMAGES.'banner/'.(($banners['banners_image'] != '') ? $banners['banners_image'] : 'noimage.gif'); ?>" /></td>
                          <td class="dataTableContent txta-c"><?php echo $banners['banners_sort']; ?></td>
                          <td class="dataTableContent"><?php echo $banners['banners_title']; ?></td>
                          <td class="dataTableContent txta-c"><?php echo $banners['banners_group']; ?></td>
                          <td class="dataTableContent txta-r"><?php echo $banners_shown . ' / ' . $banners_clicked; ?></td>
                          <td class="dataTableContent txta-c">
                            <?php                                      
                              if ($banners['status'] == '1') {
                                echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_group_id'] . '&action=setflag&flag=0') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                              } else {
                                echo '<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_group_id'] . '&action=setflag&flag=1') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                              }
                            ?>
                          </td>
                          <td class="dataTableContent txta-r"><?php if (isset($bInfo) && is_object($bInfo) && ($banners['banners_id'] == $bInfo->banners_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners['banners_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
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
                      $contents = array('form' => xtc_draw_form('banners', FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_group_id . '&action=deleteconfirm'));
                      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                      $contents[] = array('text' => '<br /><b>' . $bInfo->banners_title . '</b>');
                      if ($bInfo->banners_image)
                        $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
                      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']) . '">' . BUTTON_CANCEL . '</a>');
                      break;
                    default:
                      if (isset($bInfo) && is_object($bInfo)) {
                        $heading[] = array('text' => '<b>' . $bInfo->banners_title . '</b>');
                        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_group_id . '&action=new') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_group_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                        if ($bInfo->banners_image != '') {
                           $contents[] = array('align' => 'center', 'text' => '<br><img style="max-width:250px; margin-bottom:10px;" src="'.DIR_WS_CATALOG_IMAGES . 'banner/'.$bInfo->banners_image.'" />');
                        }
                        
                        for ($i = 0, $n = count($languages); $i < $n; $i++) {
                          $banner_query = xtc_db_query("SELECT banners_id
                                                          FROM " . TABLE_BANNERS . " 
                                                         WHERE banners_group_id = '" . xtc_db_input($bInfo->banners_group_id) . "'
                                                           AND languages_id = '".$languages[$i]['id']."'");
                          $banner = xtc_db_fetch_array($banner_query);

                          if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {                          
                            $banner_id = $banner['banners_id'];
                            $days = '3';
                            include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
                            $contents[] = array('align' => 'center', 'text' => $languages[$i]['name'].'<br /><a href="' . xtc_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banner_id . '&gID=' . $bInfo->banners_group_id) . '">' . xtc_image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension).'</a>');
                          } else {
                            include_once(DIR_WS_FUNCTIONS . 'html_graphs.php');
                            $contents[] = array('align' => 'center', 'text' => '<br /><a href="' . xtc_href_link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $banner_id . '&gID=' . $bInfo->banners_group_id) . '">' . xtc_banner_graph_infoBox($banner_id, $days).'</a>');
                          }
                        }

                        $contents[] = array('text' => '<br />' . TEXT_BANNERS_DATE_ADDED . ' ' . xtc_date_short($bInfo->date_added));
                        if ($bInfo->date_scheduled) {
                          $contents[] = array('text' => sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, xtc_date_short($bInfo->date_scheduled)));
                        }
                        
                        if ($bInfo->expires_date) {
                          $contents[] = array('text' => sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, xtc_date_short($bInfo->expires_date)));
                        } elseif ($bInfo->expires_impressions) {
                          $contents[] = array('text' => sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
                        }
                        
                        if ($bInfo->date_status_change) {
                          $contents[] = array('text' => sprintf(TEXT_BANNERS_STATUS_CHANGE, xtc_date_short($bInfo->date_status_change)));
                        }

                        $contents[] = array('text' => xtc_image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . xtc_image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);                        
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