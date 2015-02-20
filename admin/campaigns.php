<?php
  /* --------------------------------------------------------------
   $Id: campaigns.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2006 xt:Commerce (campaigns.php 1117 2005-07-25)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');

//display per page
$cfg_max_display_results_key = 'MAX_DISPLAY_CAMPAIGNS_RESULTS';
$page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

$_GET['action'] = (isset($_GET['action']) ? $_GET['action'] : '');
$_GET['page'] = (isset($_GET['page']) ? $_GET['page'] : '');
$_GET['cID'] = (isset($_GET['cID']) ? $_GET['cID'] : '');

if (xtc_not_null($_GET['action'])) {
  switch ($_GET['action']) {
    case 'insert' :
    case 'save' :
      $campaigns_id = xtc_db_prepare_input($_GET['cID']);
      $campaigns_name = xtc_db_prepare_input($_POST['campaigns_name']);
      $campaigns_refID = xtc_db_prepare_input($_POST['campaigns_refID']);
      $sql_data_array = array ('campaigns_name' => $campaigns_name, 'campaigns_refID' => $campaigns_refID);
      if ($_GET['action'] == 'insert') {
        $insert_sql_data = array ('date_added' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
        xtc_db_perform(TABLE_CAMPAIGNS, $sql_data_array);
        $campaigns_id = xtc_db_insert_id();
      }	elseif ($_GET['action'] == 'save') {
        $update_sql_data = array ('last_modified' => 'now()');
        $sql_data_array = xtc_array_merge($sql_data_array, $update_sql_data);
        xtc_db_perform(TABLE_CAMPAIGNS, $sql_data_array, 'update', "campaigns_id = '".xtc_db_input($campaigns_id)."'");
      }
      xtc_redirect(xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$campaigns_id));
      break;
    case 'deleteconfirm' :
      $campaigns_id = xtc_db_prepare_input($_GET['cID']);
      xtc_db_query("delete from ".TABLE_CAMPAIGNS." where campaigns_id = '".xtc_db_input($campaigns_id)."'");
      xtc_db_query("delete from ".TABLE_CAMPAIGNS_IP." where campaign = '".xtc_db_input($campaigns_id)."'");
       if (isset($_POST['delete_refferers']) && $_POST['delete_refferers'] == 'on') {
        xtc_db_query("update ".TABLE_ORDERS." set refferers_id = '' where refferers_id = '".xtc_db_input($campaigns_id)."'");
        xtc_db_query("update ".TABLE_CUSTOMERS." set refferers_id = '' where refferers_id = '".xtc_db_input($campaigns_id)."'");
      }
      xtc_redirect(xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page']));
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CAMPAIGNS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
              $campaigns_query_raw = "select * from ".TABLE_CAMPAIGNS." order by campaigns_name";
              $campaigns_split = new splitPageResults($_GET['page'], $page_max_display_results, $campaigns_query_raw, $campaigns_query_numrows);
              $campaigns_query = xtc_db_query($campaigns_query_raw);
              while ($campaigns = xtc_db_fetch_array($campaigns_query)) {
                if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ($_GET['cID'] == $campaigns['campaigns_id']))) && !isset($cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                  $cInfo = new objectInfo($campaigns);
                }
                if (isset($cInfo) && is_object($cInfo) && ($campaigns['campaigns_id'] == $cInfo->campaigns_id)) {
                  echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$campaigns['campaigns_id'].'&action=edit').'\'">'."\n";
                } else {
                  echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\''.xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$campaigns['campaigns_id']).'\'">'."\n";
                }
              ?>
                <td class="dataTableContent"><?php echo $campaigns['campaigns_name']; ?></td>
                <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($campaigns['campaigns_id'] == $cInfo->campaigns_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CAMPAIGNS, 'page=' . $_GET['page'] . '&cID=' . $campaigns['campaigns_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
              }
              ?>
            </table>
              
            <div class="smallText pdg2 flt-l"><?php echo $campaigns_split->display_count($campaigns_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CAMPAIGNS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $campaigns_split->display_links($campaigns_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
            <?php
            if (!$_GET['action']) {
            ?>
              <div class="clear"></div>
              <div class="pdg2 flt-r smallText"><?php echo xtc_button_link(BUTTON_INSERT, xtc_href_link(FILENAME_CAMPAIGNS, 'page=' . $_GET['page'] . '&cID=' . $cInfo->campaigns_id . '&action=new')); ?></div>

            <?php
            }
            ?>
            </td>
          <?php
            $heading = array ();
            $contents = array ();
            switch ($_GET['action']) {
              case 'new' :
                $heading[] = array ('text' => '<b>'.TEXT_HEADING_NEW_CAMPAIGN.'</b>');
                $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array ('text' => TEXT_NEW_INTRO);
                $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_NAME.'<br />'.xtc_draw_input_field('campaigns_name'));
                $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_REFID.'<br />'.xtc_draw_input_field('campaigns_refID'));
                $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_SAVE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$_GET['cID'])));
                break;
              case 'edit' :
                $heading[] = array ('text' => '<b>'.TEXT_HEADING_EDIT_CAMPAIGN.'</b>');
                $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id.'&action=save', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array ('text' => TEXT_EDIT_INTRO);
                $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_NAME.'<br />'.xtc_draw_input_field('campaigns_name', $cInfo->campaigns_name));
                $contents[] = array ('text' => '<br />'.TEXT_CAMPAIGNS_REFID.'<br />'.xtc_draw_input_field('campaigns_refID', $cInfo->campaigns_refID));
                $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_SAVE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id)));
                break;
              case 'delete' :
                $heading[] = array ('text' => '<b>'.TEXT_HEADING_DELETE_CAMPAIGN.'</b>');
                $contents = array ('form' => xtc_draw_form('campaigns', FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id.'&action=deleteconfirm'));
                $contents[] = array ('text' => TEXT_DELETE_INTRO);
                $contents[] = array ('text' => '<br /><b>'.$cInfo->campaigns_name.'</b>');
                if (isset($cInfo->refferers_count) && $cInfo->refferers_count > 0) {
                  $contents[] = array ('text' => '<br />'.xtc_draw_checkbox_field('delete_refferers').' '.TEXT_DELETE_REFFERERS);
                  $contents[] = array ('text' => '<br />'.sprintf(TEXT_DELETE_WARNING_REFFERERS, $cInfo->refferers_count));
                }
                $contents[] = array ('align' => 'center', 'text' => '<br />'.xtc_button(BUTTON_DELETE).'&nbsp;'.xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id)));
                break;
              default :
                if (isset($cInfo) && is_object($cInfo)) {
                  $heading[] = array ('text' => '<b>'.$cInfo->campaigns_name.'</b>');
                  $contents[] = array ('align' => 'center', 'text' => xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id.'&action=edit')).'&nbsp;'.xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_CAMPAIGNS, 'page='.$_GET['page'].'&cID='.$cInfo->campaigns_id.'&action=delete')));
                  $contents[] = array ('text' => '<br />'.TEXT_DATE_ADDED.' '.xtc_date_short($cInfo->date_added));
                  if (xtc_not_null($cInfo->last_modified))
                    $contents[] = array ('text' => TEXT_LAST_MODIFIED.' '.xtc_date_short($cInfo->last_modified));
                  $contents[] = array ('text' => TEXT_REFERER.'?refID='.$cInfo->campaigns_refID);
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