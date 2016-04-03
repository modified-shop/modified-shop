<?php
/* --------------------------------------------------------------
$Id$

Estelco - Ebusiness & more
http://www.estelco.de

Copyright (c) 2008 Estelco
--------------------------------------------------------------
Released under the GNU General Public License
--------------------------------------------------------------*/

require('includes/application_top.php');

switch ($_GET['action']) {
    case 'send':
        //var_dump($_POST);
        if (isset($_POST['cg']) && is_array($_POST['cg'])) {
            if (isset($_POST['categories']) || isset($_POST['products']) || isset($_POST['content'])) {
                if (isset($_POST['categories'])) {
                    foreach ($_POST['cg'] as $cgID=>$value) {
                        xtc_db_query('UPDATE ' . TABLE_CATEGORIES . ' SET group_permission_' . (int)$cgID . '=' . ($_POST['permission'] == 'true'?'1':'0'));
                    }
                    $messageStack->add(constant('TEXT_CATEGORIES_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
                if (isset($_POST['products'])) {
                    foreach ($_POST['cg'] as $cgID=>$value) {
                        xtc_db_query('UPDATE ' . TABLE_PRODUCTS . ' SET group_permission_' . (int)$cgID . '=' . ($_POST['permission'] == 'true'?'1':'0'));
                    }
                    $messageStack->add(constant('TEXT_PRODUCTS_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
                if (isset($_POST['content'])) {
                    $content_query = xtc_db_query('SELECT content_id, group_ids FROM ' . TABLE_CONTENT_MANAGER . ' ORDER BY content_id');
                    while ($result = xtc_db_fetch_array($content_query)) {
                        $values = explode(',', $result['group_ids']);
                        if (in_array('', $values)) {
                            unset($values[array_search('', $values)]);
                        }
                        if ($_POST['permission'] == 'true') {
                            foreach ($_POST['cg'] as $cgID=>$value) {
                                if (!in_array('c_' . $cgID . '_group', $values)) {
                                    $values[] = 'c_' . $cgID . '_group';
                                }
                            }
                            $group_ids = implode(',', $values);
                            xtc_db_query('UPDATE ' . TABLE_CONTENT_MANAGER . ' SET group_ids=\'' . $group_ids . '\' WHERE content_id=' . $result['content_id']);
                        } else {
                            foreach ($_POST['cg'] as $cgID=>$value) {
                                if (in_array('c_' . $cgID . '_group', $values)) {
                                    unset($values[array_search('c_' . $cgID . '_group', $values)]);
                                }
                            }
                            $group_ids = implode(',', $values);
                            xtc_db_query('UPDATE ' . TABLE_CONTENT_MANAGER . ' SET group_ids=\'' . $group_ids . '\' WHERE content_id=' . $result['content_id']);
                        }
                    }
                    $messageStack->add(constant('TEXT_CONTENT_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
            } else {
                $messageStack->add(ERROR_PLEASE_SELECT_SHOP_AREA);
            }
        } else {
            $messageStack->add(ERROR_PLEASE_SELECT_CUSTOMER_GROUP);
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_customers.png'); ?></div>
        <div class="pageHeading"><?php echo HEADING_TITLE; ?></div> 
        <div class="main pdg2 flt-l"><?php echo BOX_HEADING_CUSTOMERS; ?></div>        
          <table class="tableCenter">      
            <tr>
              <td class="main">
                <div class="customers-groups pdg2">
                  <?php
                  echo xtc_draw_form('customers_group', 'customers_group.php', 'action=send', 'post');
                  $group_query = xtc_db_query('SELECT customers_status_id,
                                                      customers_status_name
                                               FROM ' . TABLE_CUSTOMERS_STATUS . '
                                               WHERE language_id=' . (int)$_SESSION['languages_id'] . '
                                               ORDER BY customers_status_id ASC');
                  while ($result = xtc_db_fetch_array($group_query)) {
                      echo xtc_draw_checkbox_field('cg[' . $result['customers_status_id'].']', '1') . ' ' . $result['customers_status_name'] . '<br />';
                  }
                  echo '<br /><br />';
                  echo xtc_draw_checkbox_field('categories', '1') . ' ' . TEXT_CATEGORIES . '<br />';
                  echo xtc_draw_checkbox_field('products', '1') . ' ' . TEXT_PRODUCTS . '<br />';
                  echo xtc_draw_checkbox_field('content', '1') . ' ' . TEXT_CONTENT . '<br />';
                  echo '<br /><br />';
                  echo '<strong>&nbsp;' . TEXT_PERMISSION . ':</strong> ' . TEXT_SET . ' ' . xtc_draw_radio_field('permission', 'true', true) . ' ' . TEXT_UNSET . ' ' . xtc_draw_radio_field('permission', 'false', false) . '<br />';
                  echo '<br /><br />&nbsp;<input class="button" type="submit" value="'.TEXT_SEND.'" name="'.TEXT_SEND.'">';
                  echo '<br /><br />';
                  ?>
                  </form>
                </div>
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