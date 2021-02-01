<?php
/* --------------------------------------------------------------
   $Id$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(geo_zones.php,v 1.27 2003/05/07); www.oscommerce.com 
   (c) 2003	 nextcommerce (geo_zones.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  //display per page
  $cfg_max_display_tax_key = 'MAX_DISPLAY_NUMBER_OF_TAX_ZONES';
  $page_max_display_tax_results = xtc_cfg_save_max_display_results($cfg_max_display_tax_key);

  $cfg_max_display_countries_key = 'MAX_DISPLAY_NUMBER_OF_COUNTRIES';
  $page_max_display_countries_results = xtc_cfg_save_max_display_results($cfg_max_display_countries_key);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $saction = (isset($_GET['saction']) ? $_GET['saction'] : '');

  $zpage = (isset($_GET['zpage']) ? (int)$_GET['zpage'] : 1);
  $spage = (isset($_GET['spage']) ? (int)$_GET['spage'] : 1);

  // include needed function
  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');

  // set languages
  $languages = xtc_get_languages();

  switch ($saction) {
    case 'insert_sub':
      $zID = (int)$_GET['zID'];
      $zone_country_id = (int)$_POST['zone_country_id'];
      $zone_id = (int)$_POST['zone_id'];
      
      $sql_data_array = array(
        'zone_country_id' => $zone_country_id,
        'zone_id' => $zone_id,
        'geo_zone_id' => $zID,
        'date_added' => 'now()',
      );
      xtc_db_perform(TABLE_ZONES_TO_GEO_ZONES, $sql_data_array);
      $sID = xtc_db_insert_id();

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zID . '&action=list&spage=' . $spage . '&sID=' . $sID));
      break;

    case 'save_sub':
      $sID = (int)$_GET['sID'];
      $zID = (int)$_GET['zID'];
      $zone_country_id = (int)$_POST['zone_country_id'];
      $zone_id = (int)$_POST['zone_id'];

      $sql_data_array = array(
        'zone_country_id' => $zone_country_id,
        'zone_id' => $zone_id,
        'geo_zone_id' => $zID,
        'last_modified' => 'now()',
      );
      xtc_db_perform(TABLE_ZONES_TO_GEO_ZONES, $sql_data_array, 'update', "association_id = '" . $sID . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zID . '&action=list&spage=' . $spage . '&sID=' . $sID));
      break;

    case 'deleteconfirm_sub':
      $sID = (int)$_GET['sID'];
      $zID = (int)$_GET['zID'];

      xtc_db_query("DELETE FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE association_id = '" . $sID . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zID . '&action=list&spage=' . $spage));
      break;
  }

  
  switch ($action) {
    case 'insert_zone':
      $geo_zone_name = xtc_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = xtc_db_prepare_input($_POST['geo_zone_description']);
      $geo_zone_info = ((isset($_POST['geo_zone_info'])) ? '1' : '0');

      $geo_zone_name_array = array();
      foreach ($geo_zone_name as $key => $value) {
        if (xtc_not_null($value)) {
          $geo_zone_name_array[] =  $key . '::' . $value;
        }
      }
      $geo_zone_name = implode('||', $geo_zone_name_array);

      $geo_zone_description_array = array();
      foreach ($geo_zone_description as $key => $value) {
        if (xtc_not_null($value)) {
          $geo_zone_description_array[] =  $key . '::' . $value;
        }
      }
      $geo_zone_description = implode('||', $geo_zone_description_array);
      
      $sql_data_array = array(
        'geo_zone_name' => $geo_zone_name,
        'geo_zone_description' => $geo_zone_description,
        'geo_zone_info' => $geo_zone_info,
        'date_added' => 'now()',
      );
      xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array);
      $zID = xtc_db_insert_id();

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zID));
      break;

    case 'save_zone':
      $zID = (int)$_GET['zID'];
      $geo_zone_name = xtc_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = xtc_db_prepare_input($_POST['geo_zone_description']);
      $geo_zone_info = ((isset($_POST['geo_zone_info'])) ? '1' : '0');

      $geo_zone_name_array = array();
      foreach ($geo_zone_name as $key => $value) {
        if (xtc_not_null($value)) {
          $geo_zone_name_array[] =  $key . '::' . $value;
        }
      }
      $geo_zone_name = implode('||', $geo_zone_name_array);

      $geo_zone_description_array = array();
      foreach ($geo_zone_description as $key => $value) {
        if (xtc_not_null($value)) {
          $geo_zone_description_array[] =  $key . '::' . $value;
        }
      }
      $geo_zone_description = implode('||', $geo_zone_description_array);
      
      $sql_data_array = array(
        'geo_zone_name' => $geo_zone_name,
        'geo_zone_description' => $geo_zone_description,
        'geo_zone_info' => $geo_zone_info,
        'last_modified' => 'now()',
      );
      xtc_db_perform(TABLE_GEO_ZONES, $sql_data_array, 'update', "geo_zone_id = '" . $zID . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zID));
      break;

    case 'deleteconfirm_zone':
      $zID = (int)$_GET['zID'];

      xtc_db_query("DELETE FROM " . TABLE_GEO_ZONES . " WHERE geo_zone_id = '" . $zID . "'");
      xtc_db_query("DELETE FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE geo_zone_id = '" . $zID . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage));
      break;
  }

require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<?php
if (isset($_GET['zID']) && ($saction == 'edit' || $saction == 'new')) {
  ?>
  <script type="text/javascript">
    function resetZoneSelected(theForm) {
      if (theForm.state.value != '') {
        theForm.zone_id.selectedIndex = '0';
        if (theForm.zone_id.options.length > 0) {
          theForm.state.value = '<?php echo JS_STATE_SELECT; ?>';
        }
      }
    }

    function update_zone(theForm) {
      var NumState = theForm.zone_id.options.length;
      var SelectedCountry = "";

      while(NumState > 0) {
        NumState--;
        theForm.zone_id.options[NumState] = null;
      }         

      SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

    <?php echo xtc_js_zone_list('SelectedCountry', 'theForm', 'zone_id'); ?>

    }
  </script>
  <?php
}
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
            <?php
            if ($action == 'list') {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $rows = 0;
              $zones_query_raw = "SELECT a.association_id, 
                                         a.zone_country_id, 
                                         c.countries_name, 
                                         a.zone_id, 
                                         a.geo_zone_id, 
                                         a.last_modified, 
                                         a.date_added, 
                                         z.zone_name 
                                    FROM " . TABLE_ZONES_TO_GEO_ZONES . " a 
                               LEFT JOIN " . TABLE_COUNTRIES . " c 
                                         ON a.zone_country_id = c.countries_id 
                               LEFT JOIN " . TABLE_ZONES . " z 
                                         ON a.zone_id = z.zone_id 
                                   WHERE a.geo_zone_id = " . (int)$_GET['zID'] . " 
                                ORDER BY c.countries_name";
              $zones_split = new splitPageResults($spage, $page_max_display_countries_results, $zones_query_raw, $zones_query_numrows);
              $zones_query = xtc_db_query($zones_query_raw);
              while ($zones = xtc_db_fetch_array($zones_query)) {
                $rows++;
                if ((!isset($_GET['sID']) || ($_GET['sID'] == $zones['association_id'])) && !isset($sInfo) && (substr($saction, 0, 3) != 'new')) {
                  $sInfo = new objectInfo($zones);
                }
                if (isset($sInfo) && is_object($sInfo) && $zones['association_id'] == $sInfo->association_id) {
                  echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">' . "\n";
                } else {
                  echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $zones['association_id']) . '\'">' . "\n";
                }
                ?>
                <td class="dataTableContent"><?php echo (($zones['countries_name'] != '') ? $zones['countries_name'] : '---'); ?></td>
                <td class="dataTableContent"><?php echo (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT); ?></td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($sInfo)) && ($zones['association_id'] == $sInfo->association_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $zones['association_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $zones_split->display_count($zones_query_numrows, $page_max_display_countries_results, $spage, TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $zones_split->display_links($zones_query_numrows, $page_max_display_countries_results, MAX_DISPLAY_PAGE_LINKS, $spage, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list', 'spage'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF.'?'.xtc_get_all_get_params(array('zpage')),$cfg_max_display_countries_key,$page_max_display_countries_results); ?>
            <div class="smallText pdg2 flt-r"><?php if (!xtc_not_null($saction)) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '')) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>
            <?php
            } else {
            ?>
            <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $zones_query_raw = "SELECT geo_zone_id, 
                                           geo_zone_name, 
                                           geo_zone_description, 
                                           geo_zone_info, 
                                           last_modified, 
                                           date_added 
                                      FROM " . TABLE_GEO_ZONES . " 
                                  ORDER BY geo_zone_name";
                $zones_split = new splitPageResults($zpage, $page_max_display_tax_results, $zones_query_raw, $zones_query_numrows);
                $zones_query = xtc_db_query($zones_query_raw);
                while ($zones = xtc_db_fetch_array($zones_query)) {
                  if (((!isset($_GET['zID'])) || ($_GET['zID'] == $zones['geo_zone_id'])) && (!isset($zInfo)) && (substr($action, 0, 3) != 'new')) {
                    $num_zones_query = xtc_db_query("SELECT count(*) as num_zones 
                                                       FROM " . TABLE_ZONES_TO_GEO_ZONES . " 
                                                      WHERE geo_zone_id = '" . $zones['geo_zone_id'] . "' 
                                                   GROUP BY geo_zone_id");
                    if (xtc_db_num_rows($num_zones_query) > 0) {
                      $num_zones = xtc_db_fetch_array($num_zones_query);
                      $zones['num_zones'] = $num_zones['num_zones'];
                    } else {
                      $zones['num_zones'] = 0;
                    }
                    $zInfo = new objectInfo($zones);
                  }
                  if (isset($zInfo) && is_object($zInfo) && $zones['geo_zone_id'] == $zInfo->geo_zone_id) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zones['geo_zone_id']) . '\'">' . "\n";
                  }
                ?>
                <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zones['geo_zone_id'] . '&action=list') . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . parse_multi_language_value($zones['geo_zone_name'], $_SESSION['language_code']); ?></td>
                <td class="dataTableContent txta-r"><?php if (isset($zInfo) && is_object($zInfo) && $zones['geo_zone_id'] == $zInfo->geo_zone_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zones['geo_zone_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
                <?php
                }
                ?>
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $zones_split->display_count($zones_query_numrows, $page_max_display_tax_results, $zpage, TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $zones_split->display_links($zones_query_numrows, $page_max_display_tax_results, MAX_DISPLAY_PAGE_LINKS, $zpage, '', 'zpage'); ?></div>
            <div class="clear"></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_tax_key,$page_max_display_tax_results); ?> 
            <div class="smallText pdg2 flt-r"><?php if (!xtc_not_null($action)) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone') . '">' . BUTTON_INSERT . '</a>'; ?></div>
            <?php
            }
            ?>
            </td>
              <?php
              $heading = array();
              $contents = array();

              if ($action == 'list') {
                switch ($saction) {
                  case 'new':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . ((isset($_GET['sID'])) ? '&sID=' . (int)$_GET['sID'] : '') . '&saction=insert_sub'));
                    $contents[] = array('text' => TEXT_INFO_NEW_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries(TEXT_ALL_COUNTRIES), '', 'onChange="update_zone(this.form);"'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . xtc_draw_pull_down_menu('zone_id', xtc_prepare_country_zones_pull_down()));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . ((isset($_GET['sID'])) ? '&sID=' . (int)$_GET['sID'] : '')) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=save_sub'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries(TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onChange="update_zone(this.form);"'));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . xtc_draw_pull_down_menu('zone_id', xtc_prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . $sInfo->countries_name . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  default:
                    if (is_object($sInfo)) {
                      $heading[] = array('text' => '<b>' . $sInfo->countries_name . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=list&spage=' . $spage . '&sID=' . $sInfo->association_id . '&saction=delete') . '">' . BUTTON_DELETE . '</a>');
                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($sInfo->date_added));
                      if (xtc_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($sInfo->last_modified));
                    }
                    break;
                }
              } else {
                switch ($action) {
                  case 'new_zone':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '') . '&action=insert_zone'));
                    $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);

                    $geo_zone_name = '';
                    $geo_zone_description = '';
                    for ($i=0, $n=count($languages); $i<$n; $i++) {
                      $geo_zone_name .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                      $geo_zone_name .= xtc_draw_input_field('geo_zone_name[' . strtoupper($languages[$i]['code']) . ']', '', 'style="margin-left:2px; width:200px;"').'<br>';
                    
                      $geo_zone_description .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                      $geo_zone_description .= xtc_draw_input_field('geo_zone_description[' . strtoupper($languages[$i]['code']) . ']', '', 'style="margin-left:2px; width:200px;"').'<br>';
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . $geo_zone_name);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . $geo_zone_description);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_INFO . '<br />' . xtc_draw_checkbox_field('geo_zone_info', '1', false));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . ((isset($_GET['zID'])) ? '&zID=' . (int)$_GET['zID'] : '')) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'edit_zone':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone'));
                    $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);

                    $geo_zone_name = '';
                    $geo_zone_description = '';
                    for ($i=0, $n=count($languages); $i<$n; $i++) {
                      $geo_zone_name .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                      $geo_zone_name .= xtc_draw_input_field('geo_zone_name[' . strtoupper($languages[$i]['code']) . ']', parse_multi_language_value($zInfo->geo_zone_name, $languages[$i]['code'], true), 'style="margin-left:2px; width:200px;"').'<br>';
                    
                      $geo_zone_description .= xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name'], '18px');
                      $geo_zone_description .= xtc_draw_input_field('geo_zone_description[' . strtoupper($languages[$i]['code']) . ']', parse_multi_language_value($zInfo->geo_zone_description, $languages[$i]['code'], true), 'style="margin-left:2px; width:200px;"').'<br>';
                    }
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . $geo_zone_name);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . $geo_zone_description);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_INFO . '<br />' . xtc_draw_checkbox_field('geo_zone_info', '1', (($zInfo->geo_zone_info == '1') ? true : false)));
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  case 'delete_zone':
                    $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

                    $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=deleteconfirm_zone'));
                    $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
                    $contents[] = array('text' => '<br /><b>' . parse_multi_language_value($zInfo->geo_zone_name, $_SESSION['language_code']) . '</b>');
                    $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id) . '">' . BUTTON_CANCEL . '</a>');
                    break;

                  default:
                    if (is_object($zInfo)) {
                      $heading[] = array('text' => '<b>' . parse_multi_language_value($zInfo->geo_zone_name, $_SESSION['language_code']) . '</b>');

                      $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '">' . BUTTON_DELETE . '</a>' . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $zpage . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '">' . BUTTON_DETAILS . '</a>');
                      $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones);
                      $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($zInfo->date_added));
                      if ($zInfo->geo_zone_info == '1') $contents[] = array('text' => TEXT_INFO_ZONE_INFO_DEFAULT);
                      if (xtc_not_null($zInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($zInfo->last_modified));
                      $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . parse_multi_language_value($zInfo->geo_zone_description, $_SESSION['language_code']));
                    }
                    break;
                }
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