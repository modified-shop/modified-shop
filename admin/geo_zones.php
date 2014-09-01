<?php
/* --------------------------------------------------------------
   $Id: geo_zones.php 1123 2005-07-27 09:00:31Z novalis $   

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

  switch ($_GET['saction']) {
    case 'insert_sub':
      $zID = xtc_db_prepare_input($_GET['zID']);
      $zone_country_id = xtc_db_prepare_input($_POST['zone_country_id']);
      $zone_id = xtc_db_prepare_input($_POST['zone_id']);

      xtc_db_query("insert into " . TABLE_ZONES_TO_GEO_ZONES . " (zone_country_id, zone_id, geo_zone_id, date_added) values ('" . xtc_db_input($zone_country_id) . "', '" . xtc_db_input($zone_id) . "', '" . xtc_db_input($zID) . "', now())");
      $new_subzone_id = xtc_db_insert_id();

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $new_subzone_id));
      break;

    case 'save_sub':
      $sID = xtc_db_prepare_input($_GET['sID']);
      $zID = xtc_db_prepare_input($_GET['zID']);
      $zone_country_id = xtc_db_prepare_input($_POST['zone_country_id']);
      $zone_id = xtc_db_prepare_input($_POST['zone_id']);

      xtc_db_query("update " . TABLE_ZONES_TO_GEO_ZONES . " set geo_zone_id = '" . xtc_db_input($zID) . "', zone_country_id = '" . xtc_db_input($zone_country_id) . "', zone_id = " . ((xtc_db_input($zone_id)) ? "'" . xtc_db_input($zone_id) . "'" : 'null') . ", last_modified = now() where association_id = '" . xtc_db_input($sID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $_GET['sID']));
      break;

    case 'deleteconfirm_sub':
      $sID = xtc_db_prepare_input($_GET['sID']);

      xtc_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where association_id = '" . xtc_db_input($sID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage']));
      break;
  }

  switch ($_GET['action']) {
    case 'insert_zone':
      $geo_zone_name = xtc_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = xtc_db_prepare_input($_POST['geo_zone_description']);

      xtc_db_query("insert into " . TABLE_GEO_ZONES . " (geo_zone_name, geo_zone_description, date_added) values ('" . xtc_db_input($geo_zone_name) . "', '" . xtc_db_input($geo_zone_description) . "', now())");
      $new_zone_id = xtc_db_insert_id();

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $new_zone_id));
      break;

    case 'save_zone':
      $zID = xtc_db_prepare_input($_GET['zID']);
      $geo_zone_name = xtc_db_prepare_input($_POST['geo_zone_name']);
      $geo_zone_description = xtc_db_prepare_input($_POST['geo_zone_description']);

      xtc_db_query("update " . TABLE_GEO_ZONES . " set geo_zone_name = '" . xtc_db_input($geo_zone_name) . "', geo_zone_description = '" . xtc_db_input($geo_zone_description) . "', last_modified = now() where geo_zone_id = '" . xtc_db_input($zID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']));
      break;

    case 'deleteconfirm_zone':
      $zID = xtc_db_prepare_input($_GET['zID']);

      xtc_db_query("delete from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . xtc_db_input($zID) . "'");
      xtc_db_query("delete from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . xtc_db_input($zID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage']));
      break;
  }
require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
<?php
  if ($_GET['zID']  && (($_GET['saction'] == 'edit') || ($_GET['saction'] == 'new'))) {
?>
<script type="text/javascript"><!--
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
//--></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_configuration.gif'); ?></td>
    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
  </tr>
  <tr>
    <td class="main" valign="top">Configuration</td>
  </tr>
</table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if ($_GET['action'] == 'list') {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_ZONE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $rows = 0;
    $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_GEO_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.geo_zone_id = " . $_GET['zID'] . " order by association_id";
    $zones_split = new splitPageResults($_GET['spage'], '20', $zones_query_raw, $zones_query_numrows);
    $zones_query = xtc_db_query($zones_query_raw);
    while ($zones = xtc_db_fetch_array($zones_query)) {
      $rows++;
      if (((!$_GET['sID']) || (@$_GET['sID'] == $zones['association_id'])) && (!$sInfo) && (substr($_GET['saction'], 0, 3) != 'new')) {
        $sInfo = new objectInfo($zones);
      }
      if ( (is_object($sInfo)) && ($zones['association_id'] == $sInfo->association_id) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zones['association_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo (($zones['countries_name']) ? $zones['countries_name'] : TEXT_ALL_COUNTRIES); ?></td>
                <td class="dataTableContent"><?php echo (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT); ?></td>
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($zones['association_id'] == $sInfo->association_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zones['association_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($zones['association_id'] == $sInfo->association_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $zones['association_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $zones_split->display_count($zones_query_numrows, '20', $_GET['spage'], TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['spage'], 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list', 'spage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="3"><?php if (!$_GET['saction']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '">' . BUTTON_BACK . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=new') . '">' . BUTTON_INSERT . '</a>'; ?></td>
              </tr>
            </table>
<?php
  } else {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_ZONES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $zones_query_raw = "select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_GEO_ZONES . " order by geo_zone_name";
    $zones_split = new splitPageResults($_GET['zpage'], '20', $zones_query_raw, $zones_query_numrows);
    $zones_query = xtc_db_query($zones_query_raw);
    while ($zones = xtc_db_fetch_array($zones_query)) {
      if (((!$_GET['zID']) || (@$_GET['zID'] == $zones['geo_zone_id'])) && (!$zInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
        $num_zones_query = xtc_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . $zones['geo_zone_id'] . "' group by geo_zone_id");
        if (xtc_db_num_rows($num_zones_query) > 0) {
          $num_zones = xtc_db_fetch_array($num_zones_query);
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }
        $zInfo = new objectInfo($zones);
      }
      if ( (is_object($zInfo)) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones['geo_zone_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones['geo_zone_id'] . '&action=list') . '">' . xtc_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;' . $zones['geo_zone_name']; ?></td>
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($zInfo)) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones['geo_zone_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($zInfo)) && ($zones['geo_zone_id'] == $zInfo->geo_zone_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zones['geo_zone_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo $zones_split->display_count($zones_query_numrows, '20', $_GET['zpage'], TEXT_DISPLAY_NUMBER_OF_TAX_ZONES); ?></td>
                    <td class="smallText" align="right"><?php echo $zones_split->display_links($zones_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['zpage'], '', 'zpage'); ?></td>
                  </tr>
                </table></td>
              </tr>
              <tr>
                <td align="right" colspan="2"><?php if (!$_GET['action']) echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=new_zone') . '">' . BUTTON_INSERT . '</a>'; ?></td>
              </tr>
            </table>
<?php
  }
?>
            </td>
<?php
  $heading = array();
  $contents = array();

  if ($_GET['action'] == 'list') {
    switch ($_GET['saction']) {
      case 'new':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_SUB_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $_GET['sID'] . '&saction=insert_sub'));
        $contents[] = array('text' => TEXT_INFO_NEW_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries(TEXT_ALL_COUNTRIES), '', 'onChange="update_zone(this.form);"'));
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . xtc_draw_pull_down_menu('zone_id', xtc_prepare_country_zones_pull_down()));
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $_GET['sID']) . '">' . BUTTON_CANCEL . '</a>');
        break;

      case 'edit':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=save_sub'));
        $contents[] = array('text' => TEXT_INFO_EDIT_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY . '<br />' . xtc_draw_pull_down_menu('zone_country_id', xtc_get_countries(TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onChange="update_zone(this.form);"'));
        $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_ZONE . '<br />' . xtc_draw_pull_down_menu('zone_id', xtc_prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id));
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '">' . BUTTON_CANCEL . '</a>');
        break;

      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=deleteconfirm_sub'));
        $contents[] = array('text' => TEXT_INFO_DELETE_SUB_ZONE_INTRO);
        $contents[] = array('text' => '<br /><b>' . $sInfo->countries_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id) . '">' . BUTTON_CANCEL . '</a>');
        break;

      default:
        if (is_object($sInfo)) {
          $heading[] = array('text' => '<b>' . $sInfo->countries_name . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=list&spage=' . $_GET['spage'] . '&sID=' . $sInfo->association_id . '&saction=delete') . '">' . BUTTON_DELETE . '</a>');
          $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($sInfo->date_added));
          if (xtc_not_null($sInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($sInfo->last_modified));
        }
        break;
    }
  } else {
    switch ($_GET['action']) {
      case 'new_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID'] . '&action=insert_zone'));
        $contents[] = array('text' => TEXT_INFO_NEW_ZONE_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . xtc_draw_input_field('geo_zone_name'));
        $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . xtc_draw_input_field('geo_zone_description'));
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $_GET['zID']) . '">' . BUTTON_CANCEL . '</a>');
        break;

      case 'edit_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone'));
        $contents[] = array('text' => TEXT_INFO_EDIT_ZONE_INTRO);
        $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_NAME . '<br />' . xtc_draw_input_field('geo_zone_name', $zInfo->geo_zone_name));
        $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . xtc_draw_input_field('geo_zone_description', $zInfo->geo_zone_description));
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . BUTTON_CANCEL . '</a>');
        break;

      case 'delete_zone':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ZONE . '</b>');

        $contents = array('form' => xtc_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=deleteconfirm_zone'));
        $contents[] = array('text' => TEXT_INFO_DELETE_ZONE_INTRO);
        $contents[] = array('text' => '<br /><b>' . $zInfo->geo_zone_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id) . '">' . BUTTON_CANCEL . '</a>');
        break;

      default:
        if (is_object($zInfo)) {
          $heading[] = array('text' => '<b>' . $zInfo->geo_zone_name . '</b>');

          $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '">' . BUTTON_DELETE . '</a>' . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=list') . '">' . BUTTON_DETAILS . '</a>');
          $contents[] = array('text' => '<br />' . TEXT_INFO_NUMBER_ZONES . ' ' . $zInfo->num_zones);
          $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($zInfo->date_added));
          if (xtc_not_null($zInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($zInfo->last_modified));
          $contents[] = array('text' => '<br />' . TEXT_INFO_ZONE_DESCRIPTION . '<br />' . $zInfo->geo_zone_description);
        }
        break;
    }
  }

  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
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