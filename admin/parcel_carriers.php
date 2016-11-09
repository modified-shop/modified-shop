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
        $sql_data_array = array('carrier_name' => $carrier_name,
                                'carrier_tracking_link' => $carrier_tracking_link,
                                'carrier_sort_order' => $carrier_sort_order,
                                'carrier_date_added' => 'now()'
                                );
        xtc_db_perform(TABLE_CARRIERS, $sql_data_array);
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS));
        break;

      case 'save':
        $carrier_id = xtc_db_prepare_input($_GET['cID']);
        $carrier_name = xtc_db_prepare_input($_POST['carrier_name']);
        $carrier_tracking_link = xtc_db_prepare_input($_POST['carrier_tracking_link']);
        $carrier_sort_order = xtc_db_prepare_input($_POST['carrier_sort_order']);
        $sql_data_array = array('carrier_name' => $carrier_name,
                                'carrier_tracking_link' => $carrier_tracking_link,
                                'carrier_sort_order' => $carrier_sort_order,
                                'carrier_last_modified' => 'now()'
                                );
        xtc_db_perform(TABLE_CARRIERS, $sql_data_array, 'update', "carrier_id = '" . (int)$carrier_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carrier_id));
        break;

      case 'deleteconfirm':
        $carrier_id = xtc_db_prepare_input($_GET['cID']);
        xtc_db_query("DELETE FROM " . TABLE_CARRIERS . " WHERE carrier_id = '" . (int)$carrier_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel));
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
        <div class="pageHeading"><?php echo HEADING_TITLE; ?><br /></div>       
        <div class="main pdg2 flt-l">Configuration</div>       
        <table class="tableCenter">      
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CARRIER_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TRACKING_LINK; ?>&nbsp;</td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?>&nbsp;</td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                  $carriers_query_raw = "SELECT carrier_id,
                                                carrier_name,
                                                carrier_tracking_link,
                                                carrier_sort_order,
                                                carrier_date_added,
                                                carrier_last_modified
                                           FROM " . TABLE_CARRIERS . "
                                       ORDER BY carrier_sort_order";
                  $carriers_split = new splitPageResults($page_parcel, '20', $carriers_query_raw, $carriers_query_numrows);
                  $carriers_query = xtc_db_query($carriers_query_raw);
                  while ($carriers = xtc_db_fetch_array($carriers_query)) {
                    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $carriers['carrier_id']))) && !isset($carriersInfo) && (substr($action, 0, 3) != 'new')) {
                      $carriersInfo = new objectInfo($carriers);
                    }
                    if (isset($carriersInfo) && is_object($carriersInfo) && ($carriers['carrier_id'] == $carriersInfo->carrier_id) ) {
                      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id . '&action=edit') . '\'">' . "\n";
                    } else {
                      echo'              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriers['carrier_id']) . '\'">' . "\n";
                    }
                      ?>
                      <td class="dataTableContent"><?php echo $carriers['carrier_name']; ?></td>
                      <td class="dataTableContent"><?php echo $carriers['carrier_tracking_link']; ?></td>
                      <td class="dataTableContent"><?php echo $carriers['carrier_sort_order']; ?></td>
                      <td class="dataTableContent txta-r"><?php if (isset($carriersInfo) && is_object($carriersInfo) && ($carriers['carrier_id'] == $carriersInfo->carrier_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriers['carrier_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                    </tr>
                    <?php
                  }
                  ?>
              </table>
              
              <div class="smallText pdg2 flt-l"><?php echo $carriers_split->display_count($carriers_query_numrows, '20', $page_parcel, TEXT_DISPLAY_NUMBER_OF_CARRIERS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $carriers_split->display_links($carriers_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $page_parcel); ?></div>

              <?php
              if (empty($action)) {
              ?>
              <div class="clear"></div>
              <div class="pdg2 flt-r smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&action=new') . '">' . BUTTON_NEW_CARRIER . '</a>'; ?></div>
              <?php
              }
              ?>
              <div class="clear"></div>
              <br/>
              <div class="pdg2 customers-groups smallText" style="width:100%;"><?php echo TEXT_CARRIER_LINK_DESCRIPTION; ?></div>
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
                $contents = array('form' => xtc_draw_form('carrier', FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_NAME . '<br />' . xtc_draw_input_field('carrier_name', $carriersInfo->carrier_name));
                $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_TRACKING_LINK . '<br />' . xtc_draw_input_field('carrier_tracking_link', $carriersInfo->carrier_tracking_link,'style="width:300px;"'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_SORT_ORDER . '<br />' . xtc_draw_input_field('carrier_sort_order', $carriersInfo->carrier_sort_order));
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CARRIER . '</b>');
                $contents = array('form' => xtc_draw_form('carrier', FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $carriersInfo->carrier_name . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              default:
                if (isset($carriersInfo) && is_object($carriersInfo)) {
                  $heading[] = array('text' => '<b>' . $carriersInfo->carrier_name . '</b>');
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_PARCEL_CARRIERS, 'page=' . $page_parcel . '&cID=' . $carriersInfo->carrier_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($carriersInfo->carrier_date_added));
                  $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($carriersInfo->carrier_last_modified));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CARRIER_NAME . '<br />' . $carriersInfo->carrier_name);
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