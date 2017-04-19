<?php
/* --------------------------------------------------------------
   $Id: shipping_status.php 1125 2005-07-28 09:59:44Z novalis $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders_status.php,v 1.19 2003/02/06); www.oscommerce.com
   (c) 2003	 nextcommerce (orders_status.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'insert':
    case 'save':
        
      $shipping_status_id = xtc_db_prepare_input($_GET['oID']);

      $languages = xtc_get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $shipping_status_name_array = $_POST['shipping_status_name'];
        $language_id = $languages[$i]['id'];

        $sql_data_array = array('shipping_status_name' => xtc_db_prepare_input($shipping_status_name_array[$language_id]),
                                'sort_order' => (int)$_POST['sort_order']);

        if ($_GET['action'] == 'insert') {
          if (!xtc_not_null($shipping_status_id)) {
            $next_id_query = xtc_db_query("SELECT max(shipping_status_id) as shipping_status_id FROM " . TABLE_SHIPPING_STATUS . "");
            $next_id = xtc_db_fetch_array($next_id_query);
            $shipping_status_id = $next_id['shipping_status_id'] + 1;
          }

          $insert_sql_data = array('shipping_status_id' => $shipping_status_id,
                                   'language_id' => $language_id);
          $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
          xtc_db_perform(TABLE_SHIPPING_STATUS, $sql_data_array);
        } elseif ($_GET['action'] == 'save') {
          $shipping_status_query = xtc_db_query("select * from ".TABLE_SHIPPING_STATUS." where language_id = '".$language_id."' and shipping_status_id = '".xtc_db_input($shipping_status_id)."'");
          if (xtc_db_num_rows($shipping_status_query) == 0) {
            xtc_db_perform(TABLE_SHIPPING_STATUS, array ('shipping_status_id' => xtc_db_input($shipping_status_id), 'language_id' => $language_id));
          }
          xtc_db_perform(TABLE_SHIPPING_STATUS, $sql_data_array, 'update', "shipping_status_id = '" . xtc_db_input($shipping_status_id) . "' and language_id = '" . $language_id . "'");
        }
      }

      if ($_POST['delete_image'] == 'on') {
        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_IMAGES . $_POST['shipping_image'];        
        if (file_exists($image_location)) {
          @unlink($image_location);
          xtc_db_query("UPDATE " . TABLE_SHIPPING_STATUS . " 
                           SET shipping_status_image = '' 
                         WHERE shipping_status_id = '" . xtc_db_input($shipping_status_id) . "'");
        }
      }

      $accepted_shipping_status_files_extensions = array("jpg","jpeg","jpe","gif","png","bmp","tiff","tif","bmp");
      $accepted_shipping_status_files_mime_types = array("image/jpeg","image/gif","image/png","image/bmp");
      if ($shipping_status_image = xtc_try_upload('shipping_status_image', DIR_FS_DOCUMENT_ROOT.DIR_WS_IMAGES, '644', $accepted_shipping_status_files_extensions, $accepted_shipping_status_files_mime_types)) {
        xtc_db_query("UPDATE " . TABLE_SHIPPING_STATUS . " 
                         SET shipping_status_image = '" . $shipping_status_image->filename . "' 
                       WHERE shipping_status_id = '" . xtc_db_input($shipping_status_id) . "'");
      }

      if ($_POST['default'] == 'on') {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                         SET configuration_value = '" . xtc_db_input($shipping_status_id) . "' 
                       WHERE configuration_key = 'DEFAULT_SHIPPING_STATUS_ID'");
      }

      xtc_redirect(xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $shipping_status_id));
      break;

    case 'deleteconfirm':
      $oID = xtc_db_prepare_input($_GET['oID']);

      $shipping_status_query = xtc_db_query("SELECT configuration_value 
                                               FROM " . TABLE_CONFIGURATION . " 
                                              WHERE configuration_key = 'DEFAULT_SHIPPING_STATUS_ID'");
      $shipping_status = xtc_db_fetch_array($shipping_status_query);
      if ($shipping_status['configuration_value'] == $oID) {
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                         SET configuration_value = '' 
                       WHERE configuration_key = 'DEFAULT_SHIPPING_STATUS_ID'");
      }
      $shipping_status_image_query = xtc_db_query("SELECT shipping_status_image 
                                                     FROM " . TABLE_SHIPPING_STATUS . " 
                                                    WHERE shipping_status_id='".(int)$oID."'");
      $shipping_status_image = xtc_db_fetch_array($shipping_status_image_query);
      $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_IMAGES . $shipping_status_image['shipping_status_image'];        
      if (file_exists($image_location)) 
        @unlink($image_location);
      
      xtc_db_query("DELETE FROM " . TABLE_SHIPPING_STATUS . " WHERE shipping_status_id = '" . xtc_db_input($oID) . "'");

      xtc_redirect(xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page']));
      break;

    case 'delete':
      $oID = xtc_db_prepare_input($_GET['oID']);

      $remove_status = true;
      if ($oID == DEFAULT_SHIPPING_STATUS_ID) {
        $remove_status = false;
        $messageStack->add(ERROR_REMOVE_DEFAULT_SHIPPING_STATUS, 'error');
      } else {

      }
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
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>       
        <div class="main pdg2 flt-l">Configuration</div>       
        <table class="tableCenter">      
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SHIPPING_STATUS_IMAGE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SHIPPING_STATUS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_SORT; ?>&nbsp;</td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $shipping_status_query_raw = "SELECT * 
                                                FROM " . TABLE_SHIPPING_STATUS . " 
                                               WHERE language_id = '" . (int)$_SESSION['languages_id'] . "' 
                                            ORDER BY sort_order, shipping_status_id";
                $shipping_status_split = new splitPageResults($_GET['page'], '20', $shipping_status_query_raw, $shipping_status_query_numrows);
                $shipping_status_query = xtc_db_query($shipping_status_query_raw);
                while ($shipping_status = xtc_db_fetch_array($shipping_status_query)) {
                  if (((!$_GET['oID']) || ($_GET['oID'] == $shipping_status['shipping_status_id'])) && (!$oInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                    $oInfo = new objectInfo($shipping_status);
                  }

                  if ( (is_object($oInfo)) && ($shipping_status['shipping_status_id'] == $oInfo->shipping_status_id) ) {
                    echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $shipping_status['shipping_status_id']) . '\'">' . "\n";
                  }

                  if (DEFAULT_SHIPPING_STATUS_ID == $shipping_status['shipping_status_id']) {
                      echo '<td class="dataTableContent" align="left">';
                   if ($shipping_status['shipping_status_image'] != '') {
                     echo xtc_image(DIR_WS_CATALOG.DIR_WS_IMAGES . $shipping_status['shipping_status_image'] , IMAGE_ICON_INFO);
                   }
                   echo '</td>';
                    echo '                <td class="dataTableContent"><b>' . $shipping_status['shipping_status_name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
                  } else {

                          echo '<td class="dataTableContent">';
                                     if ($shipping_status['shipping_status_image'] != '') {
                                         echo xtc_image(DIR_WS_CATALOG.DIR_WS_IMAGES . $shipping_status['shipping_status_image'] , IMAGE_ICON_INFO);
                                         }
                                         echo '</td>';
                    echo '                <td class="dataTableContent">' . $shipping_status['shipping_status_name'] . '</td>' . "\n";
                  }
                ?>
                <td class="dataTableContent txta-r"><?php echo $shipping_status['sort_order']; ?></td>
                <td class="dataTableContent txta-r"><?php if ( (is_object($oInfo)) && ($shipping_status['shipping_status_id'] == $oInfo->shipping_status_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $shipping_status['shipping_status_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
                }
              ?>
              </table>
              
              <div class="smallText pdg2 flt-l"><?php echo $shipping_status_split->display_count($shipping_status_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SHIPPING_STATUS); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $shipping_status_split->display_links($shipping_status_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>

              <?php
              if (substr($_GET['action'], 0, 3) != 'new') {
              ?>
              <div class="clear"></div>
              <div class="pdg2 flt-r smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>
              <?php
              }
              ?>
            </td>
          <?php
            $heading = array();
            $contents = array();
            switch ($_GET['action']) {
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_SHIPPING_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&action=insert', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

                $shipping_status_inputs_string = '';
                $languages = xtc_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $shipping_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('shipping_status_name[' . $languages[$i]['id'] . ']');
                }
                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_IMAGE . '<br />' . xtc_draw_file_field('shipping_status_image'));
                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_NAME . $shipping_status_inputs_string);
                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', ''));
                $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page']) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'edit':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_SHIPPING_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id  . '&action=save', 'post', 'enctype="multipart/form-data"'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_IMAGE . '<br />' . xtc_draw_file_field('shipping_status_image') . 
                                              '<br />' . $oInfo->shipping_status_image . xtc_draw_hidden_field('shipping_image', $oInfo->shipping_status_image) .
                                              '<br />' . xtc_draw_checkbox_field('delete_image', '', false) . ' ' . TEXT_DELETE_IMAGE);

                $shipping_status_inputs_string = '';
                $languages = xtc_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $shipping_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('shipping_status_name[' . $languages[$i]['id'] . ']', xtc_get_shipping_status_name($oInfo->shipping_status_id, $languages[$i]['id']));
                }

                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_NAME . $shipping_status_inputs_string);
                $contents[] = array('text' => '<br />' . TEXT_INFO_SHIPPING_STATUS_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', $oInfo->sort_order));
                if (DEFAULT_SHIPPING_STATUS_ID != $oInfo->shipping_status_id) $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SHIPPING_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id  . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $oInfo->shipping_status_name . '</b>');
                if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              default:
                if (is_object($oInfo)) {
                  $heading[] = array('text' => '<b>' . $oInfo->shipping_status_name . '</b>');

                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SHIPPING_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->shipping_status_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                  $shipping_status_inputs_string = '';
                  $languages = xtc_get_languages();
                  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $shipping_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_get_shipping_status_name($oInfo->shipping_status_id, $languages[$i]['id']);
                  }

                  $contents[] = array('text' => $shipping_status_inputs_string);
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