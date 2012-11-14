<?php
  /* --------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2006 xt:Commerce (campaigns.php 1117 2005-07-25)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require ('includes/application_top.php');
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? $_GET['page'] : '');
  $cID = (isset($_GET['cID']) ? $_GET['cID'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'insert' :
      case 'save' :
        $campaigns_id = xtc_db_prepare_input($cID);
        $campaigns_name = xtc_db_prepare_input($_POST['campaigns_name']);
        $campaigns_refID = xtc_db_prepare_input($_POST['campaigns_refID']);
        $sql_data_array = array ('campaigns_name' => $campaigns_name, 'campaigns_refID' => $campaigns_refID);
        if ($action == 'insert') {
          $insert_sql_data = array ('date_added' => 'now()');
          $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_CAMPAIGNS, $sql_data_array);
          $campaigns_id = xtc_db_insert_id();
        } elseif ($action == 'save') {
          $update_sql_data = array ('last_modified' => 'now()');
          $sql_data_array = xtc_array_merge($sql_data_array, $update_sql_data);
          xtc_db_perform(TABLE_CAMPAIGNS, $sql_data_array, 'update', "campaigns_id = '".xtc_db_input($campaigns_id)."'");
        }
        xtc_redirect(xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$campaigns_id));
        break;
      case 'deleteconfirm' :
        $campaigns_id = xtc_db_prepare_input($cID);
        xtc_db_query("delete from ".TABLE_CAMPAIGNS." where campaigns_id = '".xtc_db_input($campaigns_id)."'");
        xtc_db_query("delete from ".TABLE_CAMPAIGNS_IP." where campaign = '".xtc_db_input($campaigns_id)."'");
        if (isset($_POST['delete_refferers']) && $_POST['delete_refferers'] == 'on') {
          xtc_db_query("update ".TABLE_ORDERS." set refferers_id = '' where refferers_id = '".xtc_db_input($campaigns_id)."'");
          xtc_db_query("update ".TABLE_CUSTOMERS." set refferers_id = '' where refferers_id = '".xtc_db_input($campaigns_id)."'");
        }
        xtc_redirect(xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page));
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
              <td width="100%">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                    <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
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
                          <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CAMPAIGNS; ?></td>
                          <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                        </tr>
                        <?php
                          $campaigns_query_raw = "select * from ".TABLE_CAMPAIGNS." order by campaigns_name";
                          $campaigns_split = new splitPageResults($page, MAX_DISPLAY_SEARCH_RESULTS, $campaigns_query_raw, $campaigns_query_numrows);
                          $campaigns_query = xtc_db_query($campaigns_query_raw);
                          while ($campaigns = xtc_db_fetch_array($campaigns_query)) {
                            if ((!isset($cID) || (isset($cID) && ($cID == $campaigns['campaigns_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                              $cInfo = new objectInfo($campaigns);
                            }
                            if (isset($cInfo) && is_object($cInfo) && ($campaigns['campaigns_id'] == $cInfo->campaigns_id)) {
                              echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$campaigns['campaigns_id'].'&action=edit').'\'">'."\n";
                            } else {
                              echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$campaigns['campaigns_id']).'\'">'."\n";
                            }
                            ?>
                              <td class="dataTableContent"><?php echo $campaigns['campaigns_name']; ?></td>
                              <?php /*<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
                              <td class="dataTableContent" align="right"><?php if ( (is_object($cInfo)) && ($campaigns['campaigns_id'] == $cInfo->campaigns_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . xtc_href_link(FILENAME_CAMPAIGNS, 'page=' . $page . '&cID=' . $campaigns['campaigns_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                              */ ?>
                              <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($campaigns['campaigns_id'] == $cInfo->campaigns_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CAMPAIGNS, 'page=' . $page . '&cID=' . $campaigns['campaigns_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                              <?php /*<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons --> */ ?>
                            </tr>
                            <?php
                          }
                        ?>
                        <tr>
                          <td colspan="2">
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                              <tr>
                                <td class="smallText" valign="top"><?php echo $campaigns_split->display_count($campaigns_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $page, TEXT_DISPLAY_NUMBER_OF_CAMPAIGNS); ?></td>
                                <td class="smallText" align="right"><?php echo $campaigns_split->display_links($campaigns_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $page); ?></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                        <?php
                          if (empty($action)) {
                            ?>
                            <tr>
                              <td align="right" colspan="2" class="smallText"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_CAMPAIGNS, 'page=' . $page . '&action=new')); // DokuMan - 2012-08-23 - removed unused '&cID=' . $cInfo->campaigns_id in URL?></td>
                            </tr>
                            <?php
                          }
                        ?>
                      </table>
                    </td>
                    <?php
                      $heading = array ();
                      $contents = array ();
                      switch ($action) {
                        case 'new' :
                          $heading[] = array ('text' => '<b>'.TEXT_HEADING_NEW_CAMPAIGN.'</b>');
                          $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                          $contents[] = array ('text' => TEXT_NEW_INTRO);
                          $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_NAME.'<br />'.xtc_draw_input_field('campaigns_name'));
                          $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_REFID.'<br />'.xtc_draw_input_field('campaigns_refID'));
                          $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_SAVE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cID)));
                          break;
                        case 'edit' :
                          $heading[] = array ('text' => '<b>'.TEXT_HEADING_EDIT_CAMPAIGN.'</b>');
                          $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id.'&action=save', 'post', 'enctype="multipart/form-data"'));
                          $contents[] = array ('text' => TEXT_EDIT_INTRO);
                          $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_NAME.'<br />'.xtc_draw_input_field('campaigns_name', $cInfo->campaigns_name));
                          $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_REFID.'<br />'.xtc_draw_input_field('campaigns_refID', $cInfo->campaigns_refID));
                          $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_SAVE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id)));
                          break;
                        case 'delete' :
                          $heading[] = array ('text' => '<b>'.TEXT_HEADING_DELETE_CAMPAIGN.'</b>');
                          $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id.'&action=deleteconfirm'));
                          $contents[] = array ('text' => TEXT_DELETE_INTRO);
                          $contents[] = array ('text' => '<br /><b>'.$cInfo->campaigns_name.'</b>');
                          if (isset($cInfo->refferers_count) && $cInfo->refferers_count > 0) {
                            $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('delete_refferers').' '.TEXT_DELETE_REFFERERS);
                            $contents[] = array ('text' => '<br />'.sprintf(TEXT_DELETE_WARNING_REFFERERS, $cInfo->refferers_count));
                          }
                          $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_DELETE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id)));
                          break;
                        default :
                          if (isset($cInfo) && is_object($cInfo)) {
                            $heading[] = array ('text' => '<b>'.$cInfo->campaigns_name.'</b>');
                            $contents[] = array ('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id.'&action=edit')).'&nbsp;'.xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$page.'&cID='.$cInfo->campaigns_id.'&action=delete')));
                            $contents[] = array ('text' => '<br />'.TEXT_DATE_ADDED.' '.xtc_date_short($cInfo->date_added));
                            if (xtc_not_null($cInfo->last_modified))
                              $contents[] = array ('text' => TEXT_LAST_MODIFIED.' '.xtc_date_short($cInfo->last_modified));
                            $contents[] = array ('text' => TEXT_REFERER.'?refID='.$cInfo->campaigns_refID);
                          }
                          break;
                      }
                      if ((xtc_not_null($heading)) && (xtc_not_null($contents))) {
                        echo '            <td width="25%" valign="top">'."\n";
                        echo box::infoBoxSt($heading, $contents); // cYbercOsmOnauT - 2011-02-07 - Changed methods of the classes box and tableBox to static
                        echo '            </td>'."\n";
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