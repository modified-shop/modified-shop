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
  
  // include needed defaults
  require_once(DIR_FS_EXTERNAL.'trustedshops/trustedshops.php');
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_TRUSTEDSHOPS_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  // languages
  $languages = xtc_get_languages(); 

  // installed
  if (defined('MODULE_TRUSTEDSHOPS_STATUS') && MODULE_TRUSTEDSHOPS_STATUS == 'true') {
    $installed_array = array();
    $installed_query = xtc_db_query("SELECT languages_id
                                       FROM ".TABLE_TRUSTEDSHOPS."
                                      WHERE status = '1'");
    while ($installed = xtc_db_fetch_array($installed_query)) {
      $installed_array[] = $installed['languages_id'];
    }

    $table_array = array(
      array('column' => 'trustbadge_offset_mobile', 'default' => "int(11) NOT NULL DEFAULT '0' AFTER trustbadge_position"),
      array('column' => 'trustbadge_position_mobile', 'default' => "varchar(32) NOT NULL AFTER trustbadge_offset_mobile"),
      array('column' => 'product_sticker_api_client', 'default' => "varchar(128) NOT NULL AFTER product_sticker_api"),
      array('column' => 'product_sticker_api_secret', 'default' => "varchar(128) NOT NULL AFTER product_sticker_api_client"),
    );
    foreach ($table_array as $table) {
      $check_query = xtc_db_query("SHOW COLUMNS FROM ".TABLE_TRUSTEDSHOPS." LIKE '".xtc_db_input($table['column'])."'");
      if (xtc_db_num_rows($check_query) < 1) {
        xtc_db_query("ALTER TABLE ".TABLE_TRUSTEDSHOPS." ADD ".$table['column']." ".$table['default']."");
      }
    }
  }

  $languages_id_array = array();
  for ($i=0, $n=count($languages); $i<$n; $i++) {
    $languages_id_array[] = array('id' => $languages[$i]['id'], 'text' => $languages[$i]['name']);
  }

  $trustedshops_status_array = array(
    array('id' => '1', 'text' => TEXT_ENABLED),
    array('id' => '0', 'text' => TEXT_DISABLED),
  );
  
  $position_array = array(
    array('id' => 'left', 'text' => TEXT_LEFT),
    array('id' => 'right', 'text' => TEXT_RIGHT),
    array('id' => 'center', 'text' => TEXT_CENTER),
  );
  
  $trustbadge_array = array(
    array('id' => 'default', 'text' => TEXT_BADGE_DEFAULT),
    array('id' => 'reviews', 'text' => TEXT_BADGE_REVIEWS),
    array('id' => 'custom', 'text' => TEXT_BADGE_CUSTOM),
  );

  switch ($action) {
    case 'setflag':
      $tID = (int)$_GET['tID'];
      $status = (int)$_GET['flag'];
      $languages_query = xtc_db_query("SELECT languages_id 
                                         FROM ".TABLE_TRUSTEDSHOPS."
                                        WHERE id = '" . $tID . "'"); 
      $languages = xtc_db_fetch_array($languages_query);
      xtc_db_query("UPDATE " . TABLE_TRUSTEDSHOPS . " 
                       SET status = '0' 
                     WHERE languages_id = '" . $languages['languages_id'] . "'
                       AND id != '" . $tID . "'"); 
      xtc_db_query("UPDATE " . TABLE_TRUSTEDSHOPS . " 
                       SET status = '" . $status . "' 
                     WHERE id = '" . $tID . "'"); 
      xtc_redirect(xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tID));
      break;
    
    case 'insert':
    case 'save':
      $tID = (int)$_GET['tID'];
      $trustedshops_id = xtc_db_prepare_input($_POST['trustedshops_id']);
      $languages_id = (int)$_POST['languages_id'];
      $trustbadge_variant = xtc_db_prepare_input($_POST['trustbadge_variant']);
      if (isset($_POST['trustbadge_offset'])) {
        $trustbadge_offset = (int)$_POST['trustbadge_offset'];
      }
      if (isset($_POST['trustbadge_offset_mobile'])) {
        $trustbadge_offset_mobile = (int)$_POST['trustbadge_offset_mobile'];
      }
      $trustbadge_position = xtc_db_prepare_input($_POST['trustbadge_position']);
      $trustbadge_position_mobile = xtc_db_prepare_input($_POST['trustbadge_position_mobile']);
      if (isset($_POST['trustbadge_code'])) {
        $trustbadge_code = xtc_db_prepare_input($_POST['trustbadge_code']);
      }
      $product_sticker = xtc_db_prepare_input($_POST['product_sticker']);
      $product_sticker_status = xtc_db_prepare_input($_POST['product_sticker_status']);

      $product_sticker_api = xtc_db_prepare_input($_POST['product_sticker_api']);
      $product_sticker_api_client = xtc_db_prepare_input($_POST['product_sticker_api_client']);
      $product_sticker_api_secret = xtc_db_prepare_input($_POST['product_sticker_api_secret']);

      $review_sticker = xtc_db_prepare_input($_POST['review_sticker']);
      $review_sticker_status = xtc_db_prepare_input($_POST['review_sticker_status']);
      
      $status = xtc_db_prepare_input($_POST['status']);
      
      $sql_data_array = array(
        'trustedshops_id' => $trustedshops_id,
        'languages_id' => $languages_id,
        'trustbadge_variant' => $trustbadge_variant,
        'trustbadge_position' => $trustbadge_position,
        'trustbadge_position_mobile' => $trustbadge_position_mobile,
        'trustbadge_code' => '',
        'product_sticker' => $product_sticker,
        'product_sticker_api' => $product_sticker_api,
        'product_sticker_status' => $product_sticker_status,
        'product_sticker_api_client' => $product_sticker_api_client,
        'product_sticker_api_secret' => $product_sticker_api_secret,
        'review_sticker' => $review_sticker,
        'review_sticker_status' => $review_sticker_status,
        'status' => $status,
      );
      
      if ($trustbadge_variant == 'custom' && isset($trustbadge_code)) {
        $sql_data_array['trustbadge_code'] = $trustbadge_code;
      }
      if (isset($trustbadge_offset)) {
        $sql_data_array['trustbadge_offset'] = $trustbadge_offset;
      }
      if (isset($trustbadge_offset)) {
        $sql_data_array['trustbadge_offset_mobile'] = $trustbadge_offset_mobile;
      }
      
      if ($action == 'insert') {
        $insert_sql_data = array('date_added' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_TRUSTEDSHOPS, $sql_data_array);
        $tID = xtc_db_insert_id();
      } elseif ($action == 'save') {
        $update_sql_data = array('last_modified' => 'now()');
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_TRUSTEDSHOPS, $sql_data_array, 'update', "id = '" . (int)$tID . "'");
      }
      
      if ($sql_data_array['status'] == '1') {
        $languages_query = xtc_db_query("SELECT languages_id 
                                           FROM ".TABLE_TRUSTEDSHOPS."
                                          WHERE id = '" . $tID . "'"); 
        $languages = xtc_db_fetch_array($languages_query);
        xtc_db_query("UPDATE " . TABLE_TRUSTEDSHOPS . " 
                         SET status = '0' 
                       WHERE languages_id = '" . $languages['languages_id'] . "'
                         AND id != '" . $tID . "'"); 
      }
      xtc_redirect(xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tID));
      break;

    case 'deleteconfirm':
      $tID = xtc_db_prepare_input($_GET['tID']);
      xtc_db_query("DELETE FROM " . TABLE_TRUSTEDSHOPS . " WHERE id = '" . (int)$tID . "'");

      xtc_redirect(xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page']));
      break;
  }
  
require (DIR_WS_INCLUDES.'head.php');
?>
  <style type="text/css">
    .ts_main, .ts_features {
      float:left; 
      padding:30px 0 0;
      margin: 0 30px;
      cursor: pointer;
    }
    .btnSmall, header.mainNavigation nav.signLogin .container .btnWhite {
      font-size: 13px;
      margin-top: 15px;
    }
    .btnSmall.fitting {
      padding-left: 13px;
      padding-right: 13px;
    }
    .btnSmall.btnCuracao {
      padding: 10px 15px;
        padding-right: 15px;
        padding-left: 15px;
    }
    .btnBig.fitting, .btnSmall.fitting {
      display: inline-block;
      padding-left: 20px;
      padding-right: 20px;
      width: auto;
    }
    .btnSmall {
      padding: 10px 13px;
    }
    .btnCuracao {
      background: #0DBEDC;
      color: #FFFFFF !important;
      font-weight: bold !important;
      transition: all .2s;
      border: 2px solid #0DBEDC;
    }
    .btnSmall {
      border-radius: 3px;
      font-size: 13px;
      box-sizing: border-box;
      display: block;
      padding: 3px 10px;
      text-align: center;
      width: 100%;
      cursor: pointer;
      font-family: inherit;
    }
    .btnCuracao:hover, .btnCuracao:active, .btnCuracao:focus {
      background: #FFFFFF;
      transition: all .2s;
      outline: none;
      color: #000000 !important;
      text-decoration: none;
    }
  </style>
  <script type="text/javascript" src="includes/general.js"></script>
  <script type="text/javascript">
    $(function() {
      $('#trustbadge').on('change', function() {
        if (this.value == 'custom' || this.value == 'custom_reviews') {
          $('#offset').hide();
          $('#position').hide();
          $('#title').hide();
          $('#custom').show();
          $('#custom_note').show();
        } else {
          $('#offset').show();
          $('#position').show();
          $('#title').show();
          $('#custom').hide();
          $('#custom_note').hide();
        }
      });
      $('.blog_title').click(function(e) {
        var the_block = $(this).next('.blogentry');
        var the_active_block = $(this);
        
        $('.blog_title + .blogentry').not(the_block).slideUp(300);
        $('.blog_title').not(the_active_block).removeClass('active');
        the_active_block.toggleClass('active');
        
        if (the_active_block.hasClass('active')) {
          the_block.slideDown(300);
        } else {
          the_block.slideUp(300);
        }
      });
      $('.ts_main').click(function(e) {
        $('.ts_main').css("font-weight", "bold");
        $('.ts_features').css("font-weight", "normal");
        $('#ts_main').show();
        $('#ts_features').hide(); 
      });
      $('.ts_features').click(function(e) {
        $('.ts_main').css("font-weight", "normal");
        $('.ts_features').css("font-weight", "bold");
        $('#ts_main').hide();
        $('#ts_features').show();
      });
    });
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_modules.png'); ?></div>
        <div class="pageHeading pdg2 mrg5"><?php echo HEADING_TITLE; ?></div>
        <div class="main">Modules</div>
        <?php
        if (defined('MODULE_TRUSTEDSHOPS_STATUS') && MODULE_TRUSTEDSHOPS_STATUS == 'true') {
          if ($action=='edit' || $action=='new') {
            if ($action == 'new') {
              unset($_GET['tID']);
            } else {
              $trustedshops_query = xtc_db_query("SELECT *
                                                    FROM " . TABLE_TRUSTEDSHOPS . "
                                                WHERE id = '".(int)$_GET['tID']."'");
              $trustedshops = xtc_db_fetch_array($trustedshops_query);

              for ($i=0, $n=count($languages_id_array); $i<$n; $i++) {
                if (isset($trustedshops['languages_id']) 
                    && in_array($languages_id_array[$i]['id'], $installed_array)
                    && $languages_id_array[$i]['id'] != $trustedshops['languages_id']
                    ) 
                {
                  unset($languages_id_array[$i]);
                }
              }
            }

            echo xtc_draw_form('trustedshops', FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . ((isset($_GET['tID'])) ? '&tID=' . (int)$_GET['tID'] : ''). '&action='.(($action=='new') ? 'insert' : 'save'));
            ?>
              <div class="div_box mrg5">
                <table class="tableInput" border="0">
                  <tr>
                    <td class="main" style="width:260px"><b><?php echo TEXT_TRUSTEDSHOPS_STATUS; ?></b></td>
                    <td class="main" colspan="3"><?php echo draw_on_off_selection('status', $trustedshops_status_array, ((isset($trustedshops['status']) && $trustedshops['status'] == '1') ? true : false), 'style="width: 155px"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><b><?php echo TEXT_TRUSTEDSHOPS_ID; ?></b></td>
                    <td class="main"><?php echo xtc_draw_input_field('trustedshops_id', ((isset($trustedshops['trustedshops_id'])) ? $trustedshops['trustedshops_id'] : ''), 'style="width:100%" maxlength="255"'); ?></td>
                    <td class="main" style="width:260px; text-align:right;"><b><?php echo TEXT_TRUSTEDSHOPS_LANGUAGES; ?></b></td>
                    <td class="main" style="width:160px"><?php echo xtc_draw_pull_down_menu('languages_id', $languages_id_array, ((isset($trustedshops['languages_id'])) ? $trustedshops['languages_id'] : ''), 'style="width: 155px"'); ?></td>
                  </tr>
                  <tr>
                    <td class="main" colspan="4">
                    <div class="admin_contentbox blog_container">
                      <div class="blog_title active"><div class="blog_header"><?php echo HEADING_TRUSTBADGE; ?></div></div>
                      <div class="blogentry" style="margin-bottom: 5px;">
                        <div class="blog_desc">
                          <table>
                            <tr>
                              <td class="main" colspan="3" style="padding-bottom: 10px;"><?php echo TEXT_TRUSTBADGE_INFO; ?></td>
                            </tr>
                            <tr id="custom_note" <?php echo ((!isset($trustedshops['trustbadge_variant']) || $trustedshops['trustbadge_variant'] != 'custom') ? 'style="display:none;"' : ''); ?>>
                              <td class="main important_info" colspan="2"><?php echo TEXT_BADGE_INSTRUCTION; ?></td>
                            </tr>
                            <tr><td colspan="3" style="height:10px;"></td></tr>
                            <tr>
                              <td class="main" style="width:260px"><b><?php echo TEXT_TRUSTEDSHOPS_BADGE; ?></b></td>
                              <td class="main" colspan="2">
                              <?php
                              echo xtc_draw_pull_down_menu('trustbadge_variant', $trustbadge_array, ((isset($trustedshops['trustbadge_variant'])) ? $trustedshops['trustbadge_variant'] : 'reviews'), 'style="min-width: 155px" id="trustbadge"');
                              ?>
                              </td>
                            </tr>
                            <tr id="title" <?php echo ((isset($trustedshops['trustbadge_variant']) && $trustedshops['trustbadge_variant'] == 'custom') ? 'style="display:none;"' : ''); ?>>
                              <td class="main"></td>
                              <td class="main" style="padding:20px 5px 10px 5px;"><b>Desktop</b></td>
                              <td class="main" style="padding:20px 5px 10px 5px;"><b>Mobile</b></td>                              
                            </tr>
                            <tr id="position" <?php echo ((isset($trustedshops['trustbadge_variant']) && $trustedshops['trustbadge_variant'] == 'custom') ? 'style="display:none;"' : ''); ?>>
                              <td class="main"><b><?php echo TEXT_TRUSTEDSHOPS_POSITION; ?></b></td>
                              <td class="main">
                              <?php
                              echo xtc_draw_pull_down_menu('trustbadge_position', $position_array, ((isset($trustedshops['trustbadge_position'])) ? $trustedshops['trustbadge_position'] : 'right'), 'style="min-width: 155px"');
                              ?>
                              </td>
                              <td class="main">
                              <?php
                              echo xtc_draw_pull_down_menu('trustbadge_position_mobile', $position_array, ((isset($trustedshops['trustbadge_position_mobile'])) ? $trustedshops['trustbadge_position_mobile'] : 'right'), 'style="min-width: 155px"');
                              ?>
                              </td>
                            </tr>
                            <tr id="offset" <?php echo ((isset($trustedshops['trustbadge_variant']) && $trustedshops['trustbadge_variant'] == 'custom') ? 'style="display:none;"' : ''); ?>>
                              <td class="main"><b><?php echo TEXT_BADGE_OFFSET; ?></b></td>
                              <td class="main"><?php echo xtc_draw_input_field('trustbadge_offset', ((isset($trustedshops['trustbadge_offset'])) ? $trustedshops['trustbadge_offset'] : 0), 'style="width:155px"'); ?> px</td>
                              <td class="main"><?php echo xtc_draw_input_field('trustbadge_offset_mobile', ((isset($trustedshops['trustbadge_offset_mobile'])) ? $trustedshops['trustbadge_offset_mobile'] : 0), 'style="width:155px"'); ?> px</td>
                            </tr>
                            <tr id="custom" <?php echo ((!isset($trustedshops['trustbadge_variant']) || $trustedshops['trustbadge_variant'] != 'custom') ? 'style="display:none;"' : ''); ?>>
                              <td class="main" style="width:260px"><b><?php echo TEXT_BADGE_CUSTOM_CODE; ?></b></td>
                              <td class="main">
                              <?php
                              echo xtc_draw_textarea_field('trustbadge_code', 'soft', '114', '10', ((isset($trustedshops['trustbadge_code']) && $trustedshops['trustbadge_code'] != '') ? $trustedshops['trustbadge_code'] : $custom_trustbadge_code)); 
                              ?>
                              </td>
                            </tr>
                          </table>
                        </div>
                      </div>
                      <div class="blog_title"><div class="blog_header"><?php echo HEADING_ADVANCED; ?></div></div>
                      <div class="blogentry" style="display:none; margin-top:2px; margin-bottom: 5px;">
                        <div class="blog_desc">
                          <table>
                            <tr>
                              <td class="main" style="width:260px;"><b><?php echo TEXT_PRODUCT_STICKER_STATUS; ?></b></td>
                              <td class="main" colspan="2" style="min-width: 110px;"><?php echo draw_on_off_selection('product_sticker_status', $trustedshops_status_array, ((isset($trustedshops['product_sticker_status']) && $trustedshops['product_sticker_status'] == '1') ? true : false), 'style="width: 155px"'); ?></td>
                              <td class="main"><?php echo TEXT_PRODUCT_STICKER_INFO; ?></td>
                            </tr>
                            <tr>
                              <td class="main"><b><?php echo TEXT_PRODUCT_STICKER; ?></b></td>
                              <td class="main" colspan="3"><?php echo xtc_draw_textarea_field('product_sticker', 'soft', '114', '10', ((isset($trustedshops['product_sticker']) && $trustedshops['product_sticker'] != '') ? $trustedshops['product_sticker'] : '')); ?></td>
                            </tr>
                            <tr>
                              <td class="main" style="width:260px;"><b><?php echo TEXT_PRODUCT_STICKER_API; ?></b></td>
                              <td class="main" colspan="2" style="width:250px;"><?php echo draw_on_off_selection('product_sticker_api', $trustedshops_status_array, ((isset($trustedshops['product_sticker_api']) && $trustedshops['product_sticker_api'] == '1') ? true : false), 'style="width: 155px"'); ?></td>
                              <td class="main"><?php echo TEXT_PRODUCT_STICKER_API_INFO; ?></td>
                            </tr>
                            <tr>
                              <td class="main" style="width:260px;"><b><?php echo TEXT_PRODUCT_STICKER_API_CLIENT; ?></b></td>
                              <td class="main" colspan="3"><?php echo xtc_draw_input_field('product_sticker_api_client', ((isset($trustedshops['product_sticker_api_client'])) ? $trustedshops['product_sticker_api_client'] : ''), 'style="width:100%"'); ?></td>
                            </tr>
                            <tr>
                              <td class="main" style="width:260px;"><b><?php echo TEXT_PRODUCT_STICKER_API_SECRET; ?></b></td>
                              <td class="main" colspan="3"><?php echo xtc_draw_input_field('product_sticker_api_secret', ((isset($trustedshops['product_sticker_api_secret'])) ? $trustedshops['product_sticker_api_secret'] : ''), 'style="width:100%"'); ?></td>
                            </tr>
                            <tr>
                              <td colspan="4"><div style="clear:both;width:100%;border-bottom:1px solid #ccc;padding-top:20px;margin-bottom:20px;"></div></td>
                            </tr>
                            <tr>
                              <td class="main" style="width:260px"><b><?php echo TEXT_REVIEW_STICKER_STATUS; ?></b></td>
                              <td class="main" colspan="2"><?php echo draw_on_off_selection('review_sticker_status', $trustedshops_status_array, ((isset($trustedshops['review_sticker_status']) && $trustedshops['review_sticker_status'] == '1') ? true : false), 'style="width: 155px"'); ?></td>
                              <td class="main"><?php echo TEXT_REVIEW_STICKER_INFO; ?></td>
                            </tr>
                            <tr>
                              <td class="main"><b><?php echo TEXT_REVIEW_STICKER; ?></b></td>
                              <td class="main" colspan="3"><?php echo xtc_draw_textarea_field('review_sticker', 'soft', '114', '10', ((isset($trustedshops['review_sticker']) && $trustedshops['review_sticker'] != '') ? $trustedshops['review_sticker'] : '')); ?></td>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </div>
                    </td>
                  </tr>
                </table>

              <!-- BOF Save block //-->
              <div style="clear:both;"></div>
              <div class="txta-r">
                <?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . (int)$_GET['tID'])) . '&nbsp;' . xtc_button(BUTTON_SAVE); ?>
              </div>
              <!-- EOF Save block //-->
            </div>
          <?php } else { ?>
            <table class="tableCenter">
              <tr>
                <td class="boxCenterLeft">
                  <table class="tableBoxCenter collapse">
                    <tr>
                      <td valign="middle" class="dataTableHeadingContent">
                        <?php echo HEADING_TITLE; ?>
                      </td>
                      <td valign="middle" colspan="3" class="dataTableHeadingContent" style="width:250px;">
                        <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=trustedshops'); ?>"><u><?php echo TEXT_SETTINGS; ?></u></a>
                      </td>
                    </tr>
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRUSTEDSHOPS_ID; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE; ?></td>
                      <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS; ?></td>
                      <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <?php
                    $trustedshops_query_raw = "SELECT t.*,
                                                      l.name 
                                                 FROM " . TABLE_TRUSTEDSHOPS . " t
                                                 JOIN " . TABLE_LANGUAGES . " l
                                                      ON t.languages_id = l.languages_id
                                             ORDER BY id";
                    $trustedshops_split = new splitPageResults($_GET['page'], $page_max_display_results, $trustedshops_query_raw, $trustedshops_query_numrows);
                    $trustedshops_query = xtc_db_query($trustedshops_query_raw);
                    while ($trustedshops = xtc_db_fetch_array($trustedshops_query)) {
                      if ((!isset($_GET['tID']) || $_GET['tID'] == $trustedshops['id']) && !isset($tInfo) && (substr($action, 0, 3) != 'new')) {
                        $tInfo = new objectInfo($trustedshops);
                      }

                      if ( (is_object($tInfo)) && ($trustedshops['id'] == $tInfo->id) ) {
                        echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $trustedshops['id'] . '&action=edit') . '\'">' . "\n";
                      } else {
                        echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $trustedshops['id']) . '\'">' . "\n";
                      }
                    ?>
                    <td class="dataTableContent"><?php echo $trustedshops['trustedshops_id']; ?></td>
                    <td class="dataTableContent"><?php echo $trustedshops['name']; ?></td>
                    <td class="dataTableContent">
                      <?php
                      if ($trustedshops['status'] == 1) {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_TRUSTEDSHOPS, xtc_get_all_get_params(array('action', 'tID')) . 'action=setflag&flag=0&tID='.$trustedshops['id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_TRUSTEDSHOPS, xtc_get_all_get_params(array('action', 'tID')) . 'action=setflag&flag=1&tID='.$trustedshops['id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                      }
                      ?>
                    </td>
                    <td class="dataTableContent txta-r"><?php if ( (is_object($tInfo)) && ($trustedshops['id'] == $tInfo->id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $trustedshops['id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                  </tr>
                  <?php
                    }
                  ?>              
                  </table>
                  <div class="smallText pdg2 flt-l"><?php echo $trustedshops_split->display_count($trustedshops_query_numrows, $page_max_display_results, (int)$_GET['page'], TEXT_DISPLAY_NUMBER_OF_TRUSTEDSHOPS); ?></div>
                  <div class="smallText pdg2 flt-r"><?php echo $trustedshops_split->display_links($trustedshops_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, (int)$_GET['page']); ?></div>
                  <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
                  <?php
                  if ($action != 'new') {
                  ?>
                    <div class="smallText pdg2 flt-r"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&action=new')); ?></div>
                  <?php
                  }
                  ?>
                </td>
                <?php
                  $heading = array();
                  $contents = array();
                  switch ($action) {
                              
                    case 'delete':
                      $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_TRUSTEDSHOPS . '</b>');
                      $contents = array('form' => xtc_draw_form('trustedshops', FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tInfo->id . '&action=deleteconfirm'));
                      $contents[] = array('text' => TEXT_DELETE_INTRO);
                      $contents[] = array('text' => '<br /><b>' . $tInfo->trustedshops_id . '</b>');
                      $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_button(BUTTON_DELETE) . '&nbsp;' . xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tInfo->id)));
                      break;

                    default:
                      if (is_object($tInfo)) {
                        $heading[] = array('text' => '<b>' . $tInfo->trustedshops_id . '</b>');
                        $contents[] = array('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tInfo->id . '&action=edit')) . '&nbsp;' . xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_TRUSTEDSHOPS, 'page=' . (int)$_GET['page'] . '&tID=' . $tInfo->id . '&action=delete')));
                        $contents[] = array('text' => '<br />' . TEXT_DATE_ADDED . ' ' . xtc_date_short($tInfo->date_added));
                        if (xtc_not_null($tInfo->last_modified)) {
                          $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . xtc_date_short($tInfo->last_modified));
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
          <?php 
          }
        } else {
          ?>
          <table class="tableCenter">
            <tr>
              <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
                <?php echo HEADING_TITLE; ?>
              </td>
              <td valign="middle" class="dataTableHeadingContent">
                <a href="<?php echo xtc_href_link('module_export.php', 'set=system&module=trustedshops'); ?>"><u><?php echo TEXT_SETTINGS; ?></u></a>
              </td>
            </tr>
            <tr style="background-color: #FFFFFF;">
              <td colspan="2" style="font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; padding: 0px 10px 11px 10px; text-align: justify">
                <table class="tableCenter">
                  <tr>
                    <td style="vertical-align:top;">
                      <div>
                        <img src="images/trustedshops/Trusted-Shops_Logo_black100px.png" style="width:100px;float:left;margin-top: 10px;padding-right: 30px;"/>
                        <div class="ts_main" style="font-weight:bold;"><?php echo HEADING_TITLE; ?></div>
                        <div class="ts_features"><?php echo HEADING_FEATURES; ?></div>
                      </div>
                      <div style="clear:both;width:100%;border-bottom:1px solid #0DBEDC;padding-top:10px;margin-bottom:20px;"></div>                      
                      <div id="ts_main">
                        <?php echo TEXT_TS_MAIN_INFO; ?>
                      </div>                      
                      <div id="ts_features" style="display:none;">
                        <?php echo TEXT_TS_FEATURES_INFO; ?>
                      </div>                     
                      <div style="margin-top:20px;clear:both;">
                        <?php echo TEXT_TS_SPECIAL_INFO; ?>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
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