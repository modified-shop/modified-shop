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
  $page_parcel = (isset($_GET['page']) ? $_GET['page'] : '');
  if (xtc_not_null($action)) {
    switch ($action) {
      case 'insert':
        $carrier_name = xtc_db_prepare_input($_POST['carrier_name']);
        $carrier_tracking_link = xtc_db_prepare_input($_POST['carrier_tracking_link']);
        $carrier_sort_order = xtc_db_prepare_input($_POST['carrier_sort_order']);
        $date_added = xtc_db_prepare_input($_POST['carrier_date_added']);
        xtc_db_query("insert into " . TABLE_CARRIERS . " (carrier_name, carrier_tracking_link, carrier_sort_order, carrier_date_added) values ('" . xtc_db_input($carrier_name) . "', '" . xtc_db_input($carrier_tracking_link) . "', '" . xtc_db_input($carrier_sort_order) . "', now())");
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS));
        break;

      case 'save':
        $carrier_id = xtc_db_prepare_input($_GET['carrierID']);
        $carrier_name = xtc_db_prepare_input($_POST['carrier_name']);
        $carrier_tracking_link = xtc_db_prepare_input($_POST['carrier_tracking_link']);
        $carrier_sort_order = xtc_db_prepare_input($_POST['carrier_sort_order']);
        $last_modified = xtc_db_prepare_input($_POST['carrier_last_modified']);
        xtc_db_query("update " . TABLE_CARRIERS . " set carrier_id = '" . (int)$carrier_id . "', carrier_name = '" . xtc_db_input($carrier_name) . "', carrier_tracking_link = '" . xtc_db_input($carrier_tracking_link) . "', carrier_sort_order = '" . xtc_db_input($carrier_sort_order) . "', carrier_last_modified = now() where carrier_id = '" . (int)$carrier_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carrier_id));
        break;

      case 'deleteconfirm':
        $carrier_id = xtc_db_prepare_input($_GET['carrierID']);
        xtc_db_query("delete from " . TABLE_CARRIERS . " where carrier_id = '" . (int)$carrier_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel));
        break;
    }
  }


require (DIR_WS_INCLUDES.'head.php');
?>
  <script type="text/javascript" src="includes/general.js"></script>
</head>
<body onload="SetFocus();">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <table border="0" width="100%" cellspacing="2" cellpadding="2">
      <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
          <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
            <!-- left_navigation //-->
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
            <!-- left_navigation_eof //-->
          </table>
        </td>
        <!-- body_text //-->
        <td class="boxCenter" width="100%" valign="top">
          <table border="0" width="100%" cellspacing="0" cellpadding="2">
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_configuration.png'); ?></td>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                  </tr>
                  <tr>
                    <td class="main" valign="top">Configuration</td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td valign="top">
                      <table border="0" width="100%" cellspacing="0" cellpadding="2">
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CARRIER_NAME; ?></td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRACKING_LINK; ?>&nbsp;</td>
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?>&nbsp;</td>
                          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                        </tr>
                        <?php
                        $carriers_query_raw = "select
                                                     carrier_id,
                                                     carrier_name,
                                                     carrier_tracking_link,
                                                     carrier_sort_order,
                                                     carrier_date_added,
                                                     carrier_last_modified
                                                from " . TABLE_CARRIERS . "
                                            order by carrier_sort_order";
                        $carriers_split = new splitPageResults($page_parcel, MAX_DISPLAY_SEARCH_RESULTS, $carriers_query_raw, $carriers_query_numrows);
                        $carriers_query = xtc_db_query($carriers_query_raw);
                        while ($carriers = xtc_db_fetch_array($carriers_query)) {
                          if ((!isset($_GET['carrierID']) || (isset($_GET['carrierID']) && ($_GET['carrierID'] == $carriers['carrier_id']))) && !isset($carriersInfo) && (substr($action, 0, 3) != 'new')) {
                            $carriersInfo = new objectInfo($carriers);
                          }
                          if (isset($carriersInfo) && is_object($carriersInfo) && ($carriers['carrier_id'] == $carriersInfo->carrier_id) ) {
                            echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id . '&action=edit') . '\'">' . "\n";
                          } else {
                            echo'              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriers['carrier_id']) . '\'">' . "\n";
                          }
                            ?>
                            <td class="dataTableContent"><?php echo $carriers['carrier_name']; ?></td>
                            <td class="dataTableContent"><?php echo $carriers['carrier_tracking_link']; ?></td>
                            <td class="dataTableContent"><?php echo $carriers['carrier_sort_order']; ?></td>
                            <td class="dataTableContent" align="right"><?php if (isset($carriersInfo) && is_object($carriersInfo) && ($carriers['carrier_id'] == $carriersInfo->carrier_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriers['carrier_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                          </tr>
                          <?php
                        }
                        ?>
                        <tr>
                          <td colspan="4">
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr>
                                <td class="smallText" valign="top"><?php echo $carriers_split->display_count($carriers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $page_parcel, TEXT_DISPLAY_NUMBER_OF_CARRIERS); ?></td>
                                <td class="smallText" align="right"><?php echo $carriers_split->display_links($carriers_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $page_parcel); ?></td>
                              </tr>
                              <?php
                              if (empty($action)) {
                                ?>
                                <tr>
                                  <td colspan="2" align="right"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&action=new') . '">' . BUTTON_NEW_CARRIER . '</a>'; ?></td>
                                </tr>
                                <?php
                              }
                              ?>
                              <tr>
                                <td colspan="2" class="smallText"><?php echo TEXT_CARRIER_LINK_DESCRIPTION; ?></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                    <?php
                    $heading = array();
                    $contents = array();
                    switch ($action) {
                      case 'new':
                        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CARRIER . '</b>');
                        $contents = array('form' => xtc_draw_form('carrier', FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&action=insert'));
                        $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_NAME . '<br />' . xtc_draw_input_field('carrier_name'));
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_TRACKING_LINK . '<br />' . xtc_draw_input_field('carrier_tracking_link','','style="width:300px;"'));
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_SORT_ORDER . '<br />' . xtc_draw_input_field('carrier_sort_order', $carriersInfo->carrier_sort_order));
                        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel) . '">' . BUTTON_CANCEL . '</a>');
                        break;
                      case 'edit':
                        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CARRIER . '</b>');
                        $contents = array('form' => xtc_draw_form('carrier', FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id . '&action=save'));
                        $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_NAME . '<br />' . xtc_draw_input_field('carrier_name', $carriersInfo->carrier_name));
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_TRACKING_LINK . '<br />' . xtc_draw_input_field('carrier_tracking_link', $carriersInfo->carrier_tracking_link,'style="width:300px;"'));
                        $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_SORT_ORDER . '<br />' . xtc_draw_input_field('carrier_sort_order', $carriersInfo->carrier_sort_order));
                        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id) . '">' . BUTTON_CANCEL . '</a>');
                        break;
                      case 'delete':
                        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CARRIER . '</b>');
                        $contents = array('form' => xtc_draw_form('carrier', FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id . '&action=deleteconfirm'));
                        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                        $contents[] = array('text' => '<br /><b>' . $carriersInfo->carrier_name . '</b>');
                        $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id) . '">' . BUTTON_CANCEL . '</a>');
                        break;
                      default:
                        if (isset($carriersInfo) && is_object($carriersInfo)) {
                          $heading[] = array('text' => '<b>' . $carriersInfo->carrier_name . '</b>');
                          $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&carrierID=' . $carriersInfo->carrier_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                          $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($carriersInfo->carrier_date_added));
                          $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($carriersInfo->carrier_last_modified));
                          $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_NAME . '<br />' . $carriersInfo->carrier_name);
                        }
                        break;
                    }
                    if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
                      echo '            <td width="25%" valign="top">' . "\n";
                      echo box::infoBoxSt($heading, $contents); // cYbercOsmOnauT - 2011-02-07 - Changed methods of the classes box and tableBox to static
                      echo '            </td>' . "\n";
                    }
                    ?>
                  </tr>
                </table>
              </td>
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