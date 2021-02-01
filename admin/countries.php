<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware

   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(countries.php,v 1.26 2003/05/17); www.oscommerce.com
   (c) 2003	nextcommerce (countries.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (countries.php 1123 2005-07-27)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_COUNTRIES_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $_GET['status'] = isset($_GET['status']) && $_GET['status'] != '' ? (int)$_GET['status'] : '';
  $status_param = $_GET['status'] !== '' ? '&status='.$_GET['status'] : '';
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  
  switch ($action) {
    case 'insert':
      $sql_data_array = array(
        'countries_name' => xtc_db_prepare_input($_POST['countries_name']), 
        'countries_iso_code_2' => xtc_db_prepare_input($_POST['countries_iso_code_2']), 
        'countries_iso_code_3' => xtc_db_prepare_input($_POST['countries_iso_code_3']), 
        'address_format_id' => xtc_db_prepare_input($_POST['address_format_id'])
      );
      xtc_db_perform(TABLE_COUNTRIES,$sql_data_array);
      $cID = xtc_db_insert_id();
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page . '&cID=' . $cID));      
      break;
    case 'save':       
      $cID = (int)$_GET['cID'];
      $sql_data_array = array(
        'countries_name' => xtc_db_prepare_input($_POST['countries_name']), 
        'countries_iso_code_2' => xtc_db_prepare_input($_POST['countries_iso_code_2']), 
        'countries_iso_code_3' => xtc_db_prepare_input($_POST['countries_iso_code_3']), 
        'address_format_id' => xtc_db_prepare_input($_POST['address_format_id'])
      );
      xtc_db_perform(TABLE_COUNTRIES, $sql_data_array, 'update', "countries_id = '".$cID."'");
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page . '&cID=' . $cID . $status_param));
      break;
    case 'deleteconfirm':
      $cID = (int)$_GET['cID'];
      xtc_db_query("DELETE FROM " . TABLE_COUNTRIES . " WHERE countries_id = '" . $cID . "'");
      xtc_db_query("DELETE FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . $cID . "'");
      xtc_db_query("DELETE FROM " . TABLE_ZONES_TO_GEO_ZONES . " WHERE zone_country_id = '" . $cID . "'");
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page));
      break;
    case 'setlflag':
      $cID = (int)$_GET['cID'];
      $sql_data_array = array(
        'status' => xtc_db_prepare_input($_GET['flag'])
      );
      xtc_db_perform(TABLE_COUNTRIES, $sql_data_array, 'update', "countries_id = '".$cID."'");       
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page . '&cID=' . $cID));      
    break;
    case 'setzones':
      $cID = (int)$_GET['cID'];
      $sql_data_array = array(
        'required_zones' => xtc_db_prepare_input($_GET['required_zones'])
      );
      xtc_db_perform(TABLE_COUNTRIES, $sql_data_array, 'update', "countries_id = '".$cID."'");
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page . '&cID=' . $cID . $status_param));
    break;
    case 'setallflags':
      $sql_data_array = array(
        'status' => xtc_db_prepare_input($_GET['flag'])
      );
      xtc_db_perform(TABLE_COUNTRIES, $sql_data_array, 'update', "countries_id > '0'");        
      xtc_redirect(xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page));      
    break;
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
        <div class="flt-l">
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
          <div class="main pdg2">Configuration</div>
        </div>
        <div>
          <?php 
            echo '<a class="button" style="margin-left:100px;" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setallflags&flag=1&page='.$page) . '">'.BUTTON_SET.'</a>';
            echo '&nbsp;&nbsp;&nbsp;';
            echo '<a class="button" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setallflags&flag=0&page='.$page) . '">'.BUTTON_UNSET.'</a>';
           ?>
           <?php echo xtc_draw_form('status', FILENAME_COUNTRIES, '', 'get');
              $select_data = array ();
              $select_data = array (
                array ('id' => '', 'text' => TXT_ALL),
                array ('id' => '1', 'text' => BUTTON_STATUS_ON), 
                array ('id' => '0', 'text' => BUTTON_STATUS_OFF)
                );
            ?>
            <div class="main mrg5 flt-l" style="margin-left:50px;"><?php echo  ' ' . xtc_draw_pull_down_menu('status',$select_data, (isset($_GET['status']) ? $_GET['status'] : ''), 'onChange="this.form.submit();"'); ?></div>
            </form>
            
            <div class="clear"></div>
           
        </div>
	      <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
                <tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_COUNTRY_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_REQUIRED_ZONES; ?></td>
                  <td class="dataTableHeadingContent txta-c" colspan="2"><?php echo TABLE_HEADING_COUNTRY_CODES; ?></td>
                  <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_STATUS; ?></td>                
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                  $where = isset($_GET['status']) && $_GET['status'] !== '' ? " WHERE status = ". (int)$_GET['status'] : '';
                  $countries_query_raw = "SELECT * FROM " . TABLE_COUNTRIES . $where . " ORDER BY countries_name";
                  $countries_split = new splitPageResults($page, $page_max_display_results, $countries_query_raw, $countries_query_numrows);
                  $countries_query = xtc_db_query($countries_query_raw);
                  while ($countries = xtc_db_fetch_array($countries_query)) {
                    if ((!isset($_GET['cID']) || ($_GET['cID'] == $countries['countries_id'])) && (!isset($cInfo)) && (substr($action, 0, 3) != 'new')) {
                      $cInfo = new objectInfo($countries);
                    }
                    
                    $zones_query_raw = "SELECT zone_id FROM " . TABLE_ZONES . " WHERE zone_country_id ='". (int)$countries['countries_id'] ."'";
                    $zones_query = xtc_db_query($zones_query_raw);
                    $required_zones = '';
                    if (xtc_db_num_rows($zones_query) > 0) {
                      if ($countries['required_zones'] == '1') {
                        $required_zones = xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setzones&required_zones=0&cID=' . $countries['countries_id'] . '&page='.$page) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                      } else {
                        $required_zones = '<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setzones&required_zones=1&cID=' . $countries['countries_id'].'&page='.$page) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                      }
                    }
                    
                    if ($countries['status'] == '1') {
                      $status = xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setlflag&flag=0&cID=' . $countries['countries_id'] . '&page='.$page) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
                    } else {
                      $status = '<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setlflag&flag=1&cID=' . $countries['countries_id'].'&page='.$page) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
                    }
                    
                    if (isset($cInfo) && is_object($cInfo) && $countries['countries_id'] == $cInfo->countries_id) {
                      $tr_attributes = 'class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('action', 'cID')) . 'cID=' . $cInfo->countries_id . '&action=edit') .'\'"';
                    } else {
                      $tr_attributes = 'class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('action', 'cID')) . 'cID=' . $countries['countries_id']) .'\'"';
                    }
                ?>
                <tr <?php echo $tr_attributes;?>>
                  <td class="dataTableContent"><?php echo $countries['countries_name']; ?></td>
                  <td class="dataTableContent txta-c" style="width:100px">&nbsp;<?php echo $required_zones; ?></td>
                  <td class="dataTableContent txta-c" style="width:40px"><?php echo $countries['countries_iso_code_2']; ?></td>
                  <td class="dataTableContent txta-c" style="width:40px"><?php echo $countries['countries_iso_code_3']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo $status; ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && $countries['countries_id'] == $cInfo->countries_id) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $countries['countries_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
            <?php
              }
            ?>                
            </table>
            
            <div class="smallText pdg2 flt-l"><?php echo $countries_split->display_count($countries_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_COUNTRIES); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $countries_split->display_links($countries_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page,xtc_get_all_get_params(array('page', 'action', 'cID'))); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>

            <?php
            if (!xtc_not_null($action)) {
            ?>
              <div class="smallText pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&action=new') . '">' . BUTTON_NEW_COUNTRY . '</a>'; ?></div>
            <?php
            }
            ?>
          </td>
          <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_COUNTRY . '</b>');

                $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, 'page=' . $page . '&action=insert'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_input_field('countries_name'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . xtc_draw_input_field('countries_iso_code_2'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . xtc_draw_input_field('countries_iso_code_3'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . xtc_draw_pull_down_menu('address_format_id', xtc_get_address_formats()));
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, 'page=' . $page) . '">' . BUTTON_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_COUNTRY . '</b>');

                $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . xtc_draw_input_field('countries_name', $cInfo->countries_name));
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . '<br />' . xtc_draw_input_field('countries_iso_code_2', $cInfo->countries_iso_code_2));
                $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . '<br />' . xtc_draw_input_field('countries_iso_code_3', $cInfo->countries_iso_code_3));
                $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . '<br />' . xtc_draw_pull_down_menu('address_format_id', xtc_get_address_formats(), $cInfo->address_format_id));
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_COUNTRY . '</b>');

                $contents = array('form' => xtc_draw_form('countries', FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $cInfo->countries_name . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id) . '">' . BUTTON_CANCEL . '</a>');
                break;
              default:
                if (isset($cInfo) && is_object($cInfo)) {
                  $heading[] = array('text' => '<b>' . $cInfo->countries_name . '</b>');

                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_COUNTRIES, xtc_get_all_get_params(array('page', 'action', 'cID')).'page=' . $page . '&cID=' . $cInfo->countries_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_NAME . '<br />' . $cInfo->countries_name);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_2 . ' ' . $cInfo->countries_iso_code_2);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_COUNTRY_CODE_3 . ' ' . $cInfo->countries_iso_code_3);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_ADDRESS_FORMAT . ' ' . $cInfo->address_format_id);
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