<?php
/* --------------------------------------------------------------
   $Id: orders_status.php 1497 2010-11-15 18:06:58Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders_status.php,v 1.19 2003/02/06); www.oscommerce.com
   (c) 2003	nextcommerce (orders_status.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (orders_status.php 1125 2005-07-28)

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['oID'])) $orders_status_id = xtc_db_prepare_input($_GET['oID']);

        $languages = xtc_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $orders_status_name_array = $_POST['orders_status_name'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('orders_status_name' => xtc_db_prepare_input($orders_status_name_array[$language_id]),
                                  'sort_order' => (int)$_POST['sort_order']
                                  );

          if ($action == 'insert') {
            if (!xtc_not_null($orders_status_id)) {
              $next_id_query = xtc_db_query("SELECT max(orders_status_id) as orders_status_id FROM " . TABLE_ORDERS_STATUS . "");
              $next_id = xtc_db_fetch_array($next_id_query);
              $orders_status_id = $next_id['orders_status_id'] + 1;
            }

            $insert_sql_data = array('orders_status_id' => $orders_status_id,
                                     'language_id' => $language_id);
            $sql_data_array = xtc_array_merge($sql_data_array, $insert_sql_data);
            xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array);
          } elseif ($action == 'save') {
            $orders_status_query = xtc_db_query("SELECT * 
                                                   FROM ".TABLE_ORDERS_STATUS." 
                                                  WHERE language_id = '".(int)$language_id."' 
                                                    AND orders_status_id = '".(int)$orders_status_id."'");
            if (xtc_db_num_rows($orders_status_query) == 0) {
              xtc_db_perform(TABLE_ORDERS_STATUS, array ('orders_status_id' => (int)$orders_status_id, 'language_id' => (int)$language_id));
            }
            xtc_db_perform(TABLE_ORDERS_STATUS, $sql_data_array, 'update', "orders_status_id = '" . (int)$orders_status_id . "' and language_id = '" . (int)$language_id . "'");
          }
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          // update installed payment
          $payment_installed = explode(';', MODULE_PAYMENT_INSTALLED);
          for ($i=0, $n=count($payment_installed); $i<$n; $i++) {
            $class = substr($payment_installed[$i], 0, strrpos($payment_installed[$i], '.'));
            if (file_exists(DIR_FS_CATALOG_MODULES . 'payment/' . $payment_installed[$i])) {
              include(DIR_FS_CATALOG_MODULES . 'payment/' . $payment_installed[$i]);
              $module = new $class();
              if (isset($module->order_status) && $module->order_status == DEFAULT_ORDERS_STATUS_ID) {
                xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                                 SET configuration_value = '" . (int)$orders_status_id . "' 
                               WHERE configuration_key = '".strtoupper('MODULE_PAYMENT_'.$class.'_ORDER_STATUS_ID')."'");
              }
            }
          }
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                           SET configuration_value = '" . xtc_db_input($orders_status_id) . "' 
                         WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        
        }
        xtc_redirect(xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $orders_status_id));
        break;

      case 'deleteconfirm':
        $oID = xtc_db_prepare_input($_GET['oID']);

        $orders_status_query = xtc_db_query("SELECT configuration_value 
                                               FROM " . TABLE_CONFIGURATION . " 
                                              WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        $orders_status = xtc_db_fetch_array($orders_status_query);
        if ($orders_status['configuration_value'] == $oID) {
          xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " 
                           SET configuration_value = '' 
                         WHERE configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        }

        xtc_db_query("DELETE FROM " . TABLE_ORDERS_STATUS . " WHERE orders_status_id = '" . xtc_db_input($oID) . "'");

        xtc_redirect(xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']));
        break;

      case 'delete':
        $oID = xtc_db_prepare_input($_GET['oID']);

        $status_query = xtc_db_query("SELECT count(*) as count 
                                        FROM " . TABLE_ORDERS . " 
                                       WHERE orders_status = '" . (int)$oID . "'");
        $status = xtc_db_fetch_array($status_query);

        $remove_status = true;
        if ($oID == DEFAULT_ORDERS_STATUS_ID) {
          $remove_status = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'error');
        } elseif ($status['count'] > 0) {
          $remove_status = false;
          $messageStack->add(ERROR_STATUS_USED_IN_ORDERS, 'error');
        } else {
          $history_query = xtc_db_query("SELECT count(*) as count 
                                           FROM " . TABLE_ORDERS_STATUS_HISTORY . " 
                                          WHERE orders_status_id = '" . xtc_db_input($oID) . "'");
          $history = xtc_db_fetch_array($history_query);
          if ($history['count'] > 0) {
            $remove_status = false;
            $messageStack->add(ERROR_STATUS_USED_IN_HISTORY, 'error');
          }
        }
        break;
    }
  }
  require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript" src="includes/general.js"></script>
</head>
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
                <td class="dataTableHeadingContent"><?php echo 'ID' ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ORDERS_STATUS; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_SORT; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $orders_status_query_raw = "SELECT * 
                                              FROM " . TABLE_ORDERS_STATUS . " 
                                             WHERE language_id = '" . (int)$_SESSION['languages_id'] . "' 
                                          ORDER BY sort_order";
                $orders_status_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_status_query_raw, $orders_status_query_numrows);
                $orders_status_query = xtc_db_query($orders_status_query_raw);
                while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
                  if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders_status['orders_status_id']))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
                    $oInfo = new objectInfo($orders_status);
                  }

                  if (isset($oInfo) && is_object($oInfo) && ($orders_status['orders_status_id'] == $oInfo->orders_status_id) ) {
                    echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $orders_status['orders_status_id']) . '\'">' . "\n";
                  }
                  echo '<td class="dataTableContent txta-l">'. $orders_status['orders_status_id'] . '</td>' . "\n"; 
                  if (DEFAULT_ORDERS_STATUS_ID == $orders_status['orders_status_id']) {
                    echo '                <td class="dataTableContent"><b>' . $orders_status['orders_status_name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
                  } else {
                    echo '                <td class="dataTableContent">' . $orders_status['orders_status_name'] . '</td>' . "\n";
                  }
                ?>
                <td class="dataTableContent txta-r"><?php echo $orders_status['sort_order']; ?></td>
                <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && ($orders_status['orders_status_id'] == $oInfo->orders_status_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $orders_status['orders_status_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
                }
              ?>
            </table>
                
            <div class="smallText pdg2 flt-l"><?php echo $orders_status_split->display_count($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $orders_status_split->display_links($orders_status_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>

            <?php
            if (empty($action)) {
            ?>
              <div class="clear"></div>      
              <div class="pdg2 flt-r smallText"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_INSERT . '</a>'; ?></div>
           <?php
              }
            ?>               
            </td>
          <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=insert'));
                $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);

                $orders_status_inputs_string = '';
                $languages = xtc_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']');
                }

                $contents[] = array('text' => '<br />' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
                $contents[] = array('text' => '<br />' . TEXT_INFO_ORDERS_STATUS_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', ''));
                $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'edit':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=save'));
                $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

                $orders_status_inputs_string = '';
                $languages = xtc_get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_draw_input_field('orders_status_name[' . $languages[$i]['id'] . ']', xtc_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']));
                }

                $contents[] = array('text' => '<br />' . TEXT_INFO_ORDERS_STATUS_NAME . $orders_status_inputs_string);
                $contents[] = array('text' => '<br />' . TEXT_INFO_ORDERS_STATUS_SORT_ORDER . '<br />' . xtc_draw_input_field('sort_order', $oInfo->sort_order));
                if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDERS_STATUS . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $oInfo->orders_status_name . '</b>');
                if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id) . '">' . BUTTON_CANCEL . '</a>');
                break;

              default:
                if (isset($oInfo) && is_object($oInfo)) {
                  $heading[] = array('text' => '<b>' . $oInfo->orders_status_name . '</b>');

                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');

                  $orders_status_inputs_string = '';
                  $languages = xtc_get_languages();
                  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $orders_status_inputs_string .= '<br />' . xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image']) . '&nbsp;' . xtc_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']);
                  }

                  $contents[] = array('text' => $orders_status_inputs_string);
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