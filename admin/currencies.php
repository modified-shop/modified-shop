<?php
/* --------------------------------------------------------------
   $Id: currencies.php 5201 2013-07-22 11:57:22Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.46 2003/05/02); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.9 2003/08/18); www.nextcommerce.org
   (c) 2006 XT-Commerce (currencies.php 1123 2005-07-27)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  $_GET['action'] = (isset($_GET['action']) ? $_GET['action'] : '');
  $_GET['page'] = (isset($_GET['page']) ? $_GET['page'] : '');

  if ($_GET['action']) {
    switch ($_GET['action']) {

      case 'setcflag':
        $currency_id = xtc_db_prepare_input($_GET['cID']);
        $status = xtc_db_prepare_input($_GET['flag']);
        xtc_db_query("UPDATE " . TABLE_CURRENCIES . " set status = '" . xtc_db_input($status) . "' where currencies_id = '" . (int)$currency_id . "'");
        xtc_redirect(xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . (int)$currency_id));
        break;

      case 'insert':
      case 'save':
        $currency_id = xtc_db_prepare_input($_GET['cID']);
        $title = xtc_db_prepare_input($_POST['title']);
        $code = xtc_db_prepare_input($_POST['code']);
        $symbol_left = xtc_db_prepare_input($_POST['symbol_left']);
        $symbol_right = xtc_db_prepare_input($_POST['symbol_right']);
        $decimal_point = xtc_db_prepare_input($_POST['decimal_point']);
        $thousands_point = xtc_db_prepare_input($_POST['thousands_point']);
        $decimal_places = xtc_db_prepare_input($_POST['decimal_places']);
        $value = xtc_db_prepare_input($_POST['value']);
        
        $sql_data_array = array('title' => $title,
                                'code' => $code,
                                'symbol_left' => $symbol_left,
                                'symbol_right' => $symbol_right,
                                'decimal_point' => $decimal_point,
                                'thousands_point' => $thousands_point,
                                'decimal_places' => $decimal_places,
                                'value' => $value,
                                );

        if ($_GET['action'] == 'insert') {
          xtc_db_perform(TABLE_CURRENCIES, $sql_data_array);
          $currency_id = xtc_db_insert_id();
        } elseif ($_GET['action'] == 'save') {
          xtc_db_perform(TABLE_CURRENCIES, $sql_data_array, 'update', "currencies_id = '" . (int)$currency_id . "'");
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($code) . "' where configuration_key = 'DEFAULT_CURRENCY'");
        }
        xtc_redirect(xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . (int)$currency_id));
        break;

      case 'deleteconfirm':
        $currencies_id = xtc_db_prepare_input($_GET['cID']);

        $currency_query = xtc_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . DEFAULT_CURRENCY . "'");
        $currency = xtc_db_fetch_array($currency_query);
        if ($currency['currencies_id'] == $currencies_id) {
          xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
        }

        xtc_db_query("delete from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");

        xtc_redirect(xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page']));
        break;

      case 'update':
        $currency_query = xtc_db_query("select currencies_id, code, title from " . TABLE_CURRENCIES);
        while ($currency = xtc_db_fetch_array($currency_query)) {
          $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency'; //default quote_yahooapis_currency() in localization.php
          $rate = $quote_function($currency['code']);
          if ( empty($rate) && (xtc_not_null(CURRENCY_SERVER_BACKUP) )) {
            $messageStack->add_session(sprintf(WARNING_PRIMARY_SERVER_FAILED, CURRENCY_SERVER_PRIMARY, $currency['title'], $currency['code']), 'warning');
            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency'; //default quote_cryptonator_currency() in localization.php
            $rate = $quote_function($currency['code']);
          }
          if (is_numeric($rate) && $rate > 0) {
            xtc_db_query("update " . TABLE_CURRENCIES . " set value = '" . $rate . "', last_updated = now() where currencies_id = '" . (int)$currency['currencies_id'] . "'");
            $messageStack->add_session(sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency['title'], $currency['code']), 'success');
          } else {
            $messageStack->add_session(sprintf(ERROR_CURRENCY_INVALID, $currency['title'], $currency['code']), 'error');
          }
        }
        xtc_redirect(xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']));
        break;

      case 'delete':
        $currencies_id = xtc_db_prepare_input($_GET['cID']);

        $currency_query = xtc_db_query("select code from " . TABLE_CURRENCIES . " where currencies_id = '" . (int)$currencies_id . "'");
        $currency = xtc_db_fetch_array($currency_query);

        $remove_currency = true;
        if ($currency['code'] == DEFAULT_CURRENCY) {
          $remove_currency = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_CURRENCY, 'error');
        }
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
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_NAME; ?></td>
                  <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CURRENCY_CODES; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_CURRENCY_VALUE; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_CURRENCY_STATUS; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TEXT_INFO_CURRENCY_LAST_UPDATED; ?></td>
                  <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                </tr>
                <?php
                  $currency_query_raw = "-- admin/currencies.php
                                               SELECT currencies_id,
                                                      code,
                                                      title,
                                                      symbol_left,
                                                      symbol_right,
                                                      decimal_point,
                                                      thousands_point,
                                                      decimal_places,
                                                      value,
                                                      status,
                                                      last_updated
                                                 FROM " . TABLE_CURRENCIES . "
                                             ORDER BY title";
                  $currency_split = new splitPageResults($_GET['page'], '20', $currency_query_raw, $currency_query_numrows);
                  $currency_query = xtc_db_query($currency_query_raw);
                  while ($currency = xtc_db_fetch_array($currency_query)) {
                    if ((!isset($_GET['cID']) || (isset($_GET['cID'])  && ($_GET['cID'] == $currency['currencies_id']))) && !isset($cInfo) && (substr($_GET['action'], 0, 3) != 'new')) {
                      $cInfo = new objectInfo($currency);
                    }
                    
                    if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) {
                      echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '\'">' . "\n";
                    } else {
                      echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency['currencies_id']) . '\'">' . "\n";
                    }

                    if (DEFAULT_CURRENCY == $currency['code']) {
                      echo '                <td class="dataTableContent"><b>' . $currency['title'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
                    } else {
                      echo '                <td class="dataTableContent">' . $currency['title'] . '</td>' . "\n";
                    }
                ?>
                  <td class="dataTableContent"><?php echo $currency['code']; ?></td>
                  <td class="dataTableContent txta-r"><?php echo number_format($currency['value'], 8); ?></td>
                    <td class="dataTableContent txta-r">
                      <?php
                      if ($currency['status'] == '1') {
                        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10, 'style="margin-left: 5px;"') . '<a href="' . xtc_href_link(FILENAME_CURRENCIES, xtc_get_all_get_params(array('page', 'action', 'cID')) . 'action=setcflag&flag=0&cID=' . $currency['currencies_id'] . '&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>';
                      } else {
                        echo '<a href="' . xtc_href_link(FILENAME_CURRENCIES, xtc_get_all_get_params(array('page', 'action', 'lID')) . 'action=setcflag&flag=1&cID=' . $currency['currencies_id'].'&page='.$_GET['page']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10, 'style="margin-left: 5px;"') . '</a>' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10, 'style="margin-left: 5px;"');
                      }
                      ?>
                    </td>
                  <td class="dataTableContent txta-r"><?php if (isset($currency['last_updated'])) { echo xtc_date_short($currency['last_updated']);} else {echo '&nbsp;';} ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($cInfo) && is_object($cInfo) && ($currency['currencies_id'] == $cInfo->currencies_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $currency['currencies_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
              <?php
                }
              ?>
              </table>
                            
              <div class="smallText pdg2 flt-l"><?php echo $currency_split->display_count($currency_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_CURRENCIES); ?></div>
              <div class="smallText pdg2 flt-r"><?php echo $currency_split->display_links($currency_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></div>

              <?php
              if (!$_GET['action']) {
              ?>
              <div class="clear"></div>
              <div class="pdg2 flt-l"><?php if (CURRENCY_SERVER_PRIMARY) { echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=update') . '">' . BUTTON_CURRENCY_UPDATE . '</a>'; } ?></div>
              <div class="pdg2 flt-r"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=new') . '">' . BUTTON_NEW_CURRENCY . '</a>'; ?></div>
              
              <?php
              }
              ?>
              </td>
            <?php
              $heading = array();
              $contents = array();
              switch ($_GET['action']) {
                case 'new':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CURRENCY . '</b>');

                  $contents = array('form' => xtc_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=insert'));
                  $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . xtc_draw_input_field('title'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . xtc_draw_input_field('code','','size="3" maxlength="3"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . xtc_draw_input_field('symbol_left'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . xtc_draw_input_field('symbol_right'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . xtc_draw_input_field('decimal_point','','size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . xtc_draw_input_field('thousands_point','','size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . xtc_draw_input_field('decimal_places','','size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . xtc_draw_input_field('value'));
                  $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $_GET['cID']) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                case 'edit':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CURRENCY . '</b>');

                  $contents = array('form' => xtc_draw_form('currencies', FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=save'));
                  $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . '<br />' . xtc_draw_input_field('title', $cInfo->title));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_CODE . '<br />' . xtc_draw_input_field('code', $cInfo->code, 'size="3" maxlength="3"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . '<br />' . xtc_draw_input_field('symbol_left', $cInfo->symbol_left));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_RIGHT . '<br />' . xtc_draw_input_field('symbol_right', $cInfo->symbol_right));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . '<br />' . xtc_draw_input_field('decimal_point', $cInfo->decimal_point,'size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_THOUSANDS_POINT . '<br />' . xtc_draw_input_field('thousands_point', $cInfo->thousands_point,'size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_PLACES . '<br />' . xtc_draw_input_field('decimal_places', $cInfo->decimal_places,'size="1" maxlength="1"'));
                  $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_VALUE . '<br />' . xtc_draw_input_field('value', $cInfo->value));
                  if (DEFAULT_CURRENCY != $cInfo->code) 
                    $contents[] = array('text' => '<br />' . xtc_draw_checkbox_field('default') . ' ' . TEXT_INFO_SET_AS_DEFAULT);
                  $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                case 'delete':
                  $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CURRENCY . '</b>');

                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('text' => '<br /><b>' . $cInfo->title . '</b>');
                  $contents[] = array('align' => 'center', 'text' => '<br />' . (($remove_currency) ? '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=deleteconfirm') . '">' . BUTTON_DELETE . '</a>' : '') . ' <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id) . '">' . BUTTON_CANCEL . '</a>');
                  break;

                default:
                  if (isset($cInfo) && is_object($cInfo)) {
                    $heading[] = array('text' => '<b>' . $cInfo->title . '</b>');

                    $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_CURRENCIES, 'page=' . $_GET['page'] . '&cID=' . $cInfo->currencies_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_TITLE . ' ' . $cInfo->title);
                    $contents[] = array('text' => TEXT_INFO_CURRENCY_CODE . ' ' . $cInfo->code);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_SYMBOL_LEFT . ' ' . $cInfo->symbol_left);
                    $contents[] = array('text' => TEXT_INFO_CURRENCY_SYMBOL_RIGHT . ' ' . $cInfo->symbol_right);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_DECIMAL_POINT . ' ' . $cInfo->decimal_point);
                    $contents[] = array('text' => TEXT_INFO_CURRENCY_THOUSANDS_POINT . ' ' . $cInfo->thousands_point);
                    $contents[] = array('text' => TEXT_INFO_CURRENCY_DECIMAL_PLACES . ' ' . $cInfo->decimal_places);
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_LAST_UPDATED . ' ' . xtc_date_short($cInfo->last_updated));
                    $contents[] = array('text' => TEXT_INFO_CURRENCY_VALUE . ' ' . number_format($cInfo->value, 8));
                    $contents[] = array('text' => '<br />' . TEXT_INFO_CURRENCY_EXAMPLE . '<br />' . $currencies->format('30', false, DEFAULT_CURRENCY) . ' = ' . $currencies->format('30', true, $cInfo->code));
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