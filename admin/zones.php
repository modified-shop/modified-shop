<?php
  /* --------------------------------------------------------------
   $Id: zones.php 5157 2013-07-18 21:38:29Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(zones.php,v 1.21 2002/03/17); www.oscommerce.com
   (c) 2003	 nextcommerce (zones.php,v 1.8 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (zones.php 1123 2005-07-27)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_ZONES_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

$_GET['action'] = (isset($_GET['action']) ? $_GET['action'] : '');

if ($_GET['action']) {
  switch ($_GET['action']) {
    case 'insert':
      $zone_country_id = xtc_db_prepare_input($_POST['zone_country_id']);
      $zone_code = xtc_db_prepare_input($_POST['zone_code']);
      $zone_name = xtc_db_prepare_input($_POST['zone_name']);

      xtc_db_query("insert into " . TABLE_ZONES . " (zone_country_id, zone_code, zone_name) values ('" . (int)$zone_country_id . "', '" . xtc_db_input($zone_code) . "', '" . xtc_db_input($zone_name) . "')");
      xtc_redirect(xtc_href_link(FILENAME_ZONES));
      break;
    case 'save':
      $zone_id = xtc_db_prepare_input($_GET['cID']);
      $zone_country_id = xtc_db_prepare_input($_POST['zone_country_id']);
      $zone_code = xtc_db_prepare_input($_POST['zone_code']);
      $zone_name = xtc_db_prepare_input($_POST['zone_name']);

      xtc_db_query("update " . TABLE_ZONES . " set zone_country_id = '" . (int)$zone_country_id . "', zone_code = '" . xtc_db_input($zone_code) . "', zone_name = '" . xtc_db_input($zone_name) . "' where zone_id = '" . (int)$zone_id . "'");
      xtc_redirect(xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $zone_id));
      break;
    case 'deleteconfirm':
      $zone_id = xtc_db_prepare_input($_GET['cID']);

      xtc_db_query("delete from " . TABLE_ZONES . " where zone_id = '" . (int)$zone_id . "'");
      xtc_redirect(xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page']));
      break;
  }
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
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE_NAME; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_ZONE_CODE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                  $zones_query_raw = "select z.zone_id, c.countries_id, c.countries_name, z.zone_name, z.zone_code, z.zone_country_id from " . TABLE_ZONES . " z, " . TABLE_COUNTRIES . " c where z.zone_country_id = c.countries_id order by c.countries_name, z.zone_name";
                  $zones_split = new splitPageResults($_GET['page'], $page_max_display_results, $zones_query_raw, $zones_query_numrows);
                  $zones_query = xtc_db_query($zones_query_raw);
                  while ($zones = xtc_db_fetch_array($zones_query)) {
                    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $zones['zone_id']))) && !isset($cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                      $cInfo = new objectInfo($zones);
                    }

                    if ( isset($cInfo) && (is_object($cInfo)) && ($zones['zone_id'] == $cInfo->zone_id) ) {
                      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '\'">' . "\n";
                    } else {
                      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $zones['zone_id']) . '\'">' . "\n";
                    }
                ?>
                <td class="dataTableContent"><?php echo $zones['countries_name']; ?></td>
                <td class="dataTableContent"><?php echo $zones['zone_name']; ?></td>
                <td class="dataTableContent txta-c"><?php echo $zones['zone_code']; ?></td>
                <td class="dataTableContent txta-r"><?php if (isset($cInfo) && (is_object($cInfo)) && ($zones['zone_id'] == $cInfo->zone_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $zones['zone_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
            <?php
              }
            ?>
            </table>
            <div class="smallText pdg2 flt-l"><?php echo $zones_split->display_count($zones_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ZONES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $zones_split->display_links($zones_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
            
            <?php
            if (!$_GET['action']) {
            ?>
              <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_NEW_ZONE . '</a>'; ?></div>
            <?php
            }
            ?>
          </td>
            <?php
              $heading = array();
              $contents = array();
              switch ($_GET['action']) {
                case 'new':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');

                  $contents = array('form' => xtc_draw_form('zones', FILENAME_ZONES, 'page=' . $_GET['page'] . '&action=insert'));
                  $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . xtc_draw_input_field('zone_name'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_CODE . '<br />' . xtc_draw_input_field('zone_code'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries()));
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page']) . '">' . BUTTON_CANCEL . '</a>');
                  break;
                case 'edit':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

                  $contents = array('form' => xtc_draw_form('zones', FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=save'));
                  $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . xtc_draw_input_field('zone_name', $cInfo->zone_name));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_CODE . '<br />' . xtc_draw_input_field('zone_code', $cInfo->zone_code));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries(), $cInfo->countries_id));
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;
                case 'delete':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

                  $contents = array('form' => xtc_draw_form('zones', FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=deleteconfirm'));
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br /><b>' . $cInfo->zone_name . '</b>');
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;
                default:
                  if (isset($cInfo) && is_object($cInfo)) {
                    $heading[] = array('text' => '<b>' . $cInfo->zone_name . '</b>');

                    $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ZONES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->zone_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_ZONES_NAME . '<br />' . $cInfo->zone_name . ' (' . $cInfo->zone_code . ')');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . ' ' . $cInfo->countries_name);
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