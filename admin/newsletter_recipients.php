<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');

  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_NEWSLETTER_RECIPIENTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $customers_statuses_array = xtc_get_customers_statuses();
  $mail_statuses_array = array(
    array('id' => '', 'text' => TXT_ALL), 
    array('id' => '1', 'text' => TXT_SUBSCRIBED), 
    array('id' => '0', 'text' => TXT_UNSUBSCRIBED), 
  );  

  $where = '';
  if (isset($_GET['cgroup']) && $_GET['cgroup'] != '') {
    $where .= " AND nr.customers_status = '".(int)$_GET['cgroup']."' ";
  }
  if (isset($_GET['status']) && $_GET['status'] != '') {
    $where .= " AND nr.mail_status = '".(int)$_GET['status']."' ";
  }
  if (isset($_GET['search']) && $_GET['search'] != '') {
    $where .= " AND (nr.customers_firstname LIKE '%".xtc_db_input($_GET['search'])."%' 
                     OR nr.customers_lastname LIKE '%".xtc_db_input($_GET['search'])."%'
                     OR nr.customers_email_address LIKE '%".xtc_db_input($_GET['search'])."%')";
  }
  $where = strpos($where,' AND') !== false ? substr_replace($where,' WHERE',0,strlen(' AND')) : '';

  $newsletter_query_raw = "SELECT nr.*,
                                  cs.customers_status_name
                             FROM " . TABLE_NEWSLETTER_RECIPIENTS . " nr
                        LEFT JOIN ".TABLE_CUSTOMERS_STATUS." cs
                                  ON cs.customers_status_id = nr.customers_status
                                     AND cs.language_id = '".(int)$_SESSION['languages_id']."'
                                  ".$where."
                         ORDER BY customers_email_address";

  if (xtc_not_null($action)) {
    switch ($action) {
      case 'deleteconfirm':
        $mail = xtc_db_prepare_input($_GET['mail']);

        require_once (DIR_FS_CATALOG.DIR_WS_CLASSES.'class.newsletter.php');
        $newsletter = new newsletter();
        $newsletter->remove = true;
        $newsletter->RemoveFromList('', $mail);
        $messageStack->add_session($newsletter->message, (($newsletter->message_class == 'info') ? 'success' : $newsletter->message_class));
        xtc_redirect(xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, xtc_get_all_get_params(array('action'))));
        break;
        
      case 'export':
        $newsletter_query = xtc_db_query($newsletter_query_raw);
        if (xtc_db_num_rows($newsletter_query) > 0) {
          header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=newsletter_recipients.csv');

          $i = 0;
          while ($newsletter = xtc_db_fetch_array($newsletter_query)) {
            if ($i == 0) {
              $header = array();
              foreach ($newsletter as $k => $v) {
                $header[] = $k;
              }
              echo implode(';', $header) . "\n";
            }
            echo implode(';', $newsletter) . "\n";
            $i ++;
          }
          exit();
        }
        xtc_redirect(xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, xtc_get_all_get_params(array('action'))));
        break;
    }
  }
  require (DIR_WS_INCLUDES.'head.php');
?>
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
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="pageHeading flt-l"><?php echo HEADING_TITLE; ?>
          <div class="main pdg2">Configuration</div>
        </div>

        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('cgroup', FILENAME_NEWSLETTER_RECIPIENTS, '', 'get'); ?>
          <?php echo ENTRY_CUSTOMERS_STATUS . ' ' . xtc_draw_pull_down_menu('cgroup', array_merge(array (array ('id' => '', 'text' => TXT_ALL)), $customers_statuses_array), isset($_GET['cgroup']) ? $_GET['cgroup'] : '', 'onChange="this.form.submit();"'); ?>
          <?php echo ((isset($_GET['status']) && $_GET['status'] != '') ? xtc_draw_hidden_field('status', $_GET['status']) : '')?>
          <?php echo ((isset($_GET['search']) && $_GET['search'] != '') ? xtc_draw_hidden_field('search', $_GET['search']) : '')?>
          </form>
        </div>

        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('cgroup', FILENAME_NEWSLETTER_RECIPIENTS, '', 'get'); ?>
          <?php echo ENTRY_MAIL_STATUS . ' ' . xtc_draw_pull_down_menu('status', $mail_statuses_array , isset($_GET['status']) ? $_GET['status'] : '', 'onChange="this.form.submit();"'); ?>
          <?php echo ((isset($_GET['cgroup']) && $_GET['cgroup'] != '') ? xtc_draw_hidden_field('cgroup', $_GET['cgroup']) : '')?>
          <?php echo ((isset($_GET['search']) && $_GET['search'] != '') ? xtc_draw_hidden_field('search', $_GET['search']) : '')?>
          </form>
        </div>

        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo xtc_draw_form('search', FILENAME_NEWSLETTER_RECIPIENTS, '', 'get'); ?>
          <?php echo ENTRY_SEARCH_CUSTOMER . ' ' . xtc_draw_input_field('search', isset($_GET['search']) ? $_GET['search'] : '', 'size="12"'); ?>
          <?php echo ((isset($_GET['status']) && $_GET['status'] != '') ? xtc_draw_hidden_field('status', $_GET['status']) : '')?>
          <?php echo ((isset($_GET['cgroup']) && $_GET['cgroup'] != '') ? xtc_draw_hidden_field('cgroup', $_GET['cgroup']) : '')?>
          </form>
        </div>
        
        <div class="main flt-l pdg2 mrg5" style="margin-left:20px;">
          <?php echo '<a class="button" style="margin-top:1px;" onclick="this.blur();" href="' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, xtc_get_all_get_params(array('action')).'action=export') . '">' . BUTTON_EXPORT . '</a>'; ?>
        </div>

        <div class="clear"></div>      
        <table class="tableCenter">      
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWSLETTER ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FIRSTNAME ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LASTNAME ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_STATUS ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_STATUS ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $newsletter_split = new splitPageResults($_GET['page'], $page_max_display_results, $newsletter_query_raw, $newsletter_query_numrows);
                $newsletter_query = xtc_db_query($newsletter_query_raw);
                while ($newsletter = xtc_db_fetch_array($newsletter_query)) {
                  if ((!isset($_GET['mail']) || (isset($_GET['mail']) && $_GET['mail'] == md5($newsletter['customers_email_address']))) && !isset($oInfo)) {                  
                    $oInfo = new objectInfo($newsletter);                    
                  }

                  if (isset($oInfo) && is_object($oInfo) && ($newsletter['customers_email_address'] == $oInfo->customers_email_address) ) {
                    echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($oInfo->customers_email_address) . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($newsletter['customers_email_address'])) . '\'">' . "\n";
                  }
                  ?>
                  <td class="dataTableContent txta-l"><?php echo $newsletter['customers_email_address']; ?></td>
                  <td class="dataTableContent txta-l"><?php echo $newsletter['customers_firstname']; ?></td>
                  <td class="dataTableContent txta-l"><?php echo $newsletter['customers_lastname']; ?></td>
                  <td class="dataTableContent txta-l"><?php echo $newsletter['customers_status_name']; ?></td>
                  <td class="dataTableContent txta-c"><?php echo xtc_image(DIR_WS_ICONS.(($newsletter['mail_status'] == '1') ? 'tick.gif' : 'cross.gif')); ?></td>
                  <td class="dataTableContent txta-r"><?php if (isset($oInfo) && is_object($oInfo) && ($newsletter['customers_email_address'] == $oInfo->customers_email_address) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($newsletter['customers_email_address'])) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
                </tr>
                <?php
                }
              ?>
            </table>
              
            <div class="smallText pdg2 flt-l"><?php echo $newsletter_split->display_count($newsletter_query_numrows, $page_max_display_results, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS_STATUS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $newsletter_split->display_links($newsletter_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], xtc_get_all_get_params(array('page'))); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
          </td>
          <?php
            $heading = array();
            $contents = array();
            switch ($action) {
              case 'delete':
                $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_NEWSLETTER . '</b>');

                $contents = array('form' => xtc_draw_form('status', FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($oInfo->customers_email_address)  . '&action=deleteconfirm'));
                $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                $contents[] = array('text' => '<br /><b>' . $oInfo->customers_email_address . '</b>');
                $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UNSUBSCRIBE . '"/> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($oInfo->customers_email_address)) . '">' . BUTTON_CANCEL . '</a>');
                break;

              default:
                if (isset($oInfo) && is_object($oInfo)) {
                  $heading[] = array('text' => '<b>' . $oInfo->customers_email_address . '</b>');
                                  
                  $contents[] = array('text' => '<b>' . TEXT_INFO_HISTORY_NEWSLETTER . '</b>');
                  $newsletter_history_string = '';
                  $newsletter_history_query = xtc_db_query("SELECT *
                                                              FROM " . TABLE_NEWSLETTER_RECIPIENTS_HISTORY . " 
                                                             WHERE customers_email_address = '".xtc_db_input($oInfo->customers_email_address)."'
                                                          ORDER BY date_added ASC");
                  if (xtc_db_num_rows($newsletter_history_query) > 0) {
                    $newsletter_history_string = '<table>';
                    while ($newsletter_history = xtc_db_fetch_array($newsletter_history_query)) {
                      $newsletter_history_string .= '<tr>';
                      $newsletter_history_string .= ' <td>'.xtc_date_short($newsletter_history['date_added']).' '.date('H:i:s', strtotime($newsletter_history['date_added'])).'</td>';
                      $newsletter_history_string .= ' <td>'.$newsletter_history['customers_action'].'</td>';
                      $newsletter_history_string .= ' <td>'.$newsletter_history['ip_address'].'</td>';                      
                      $newsletter_history_string .= '</tr>';
                    }
                    $newsletter_history_string .= '</table>';
                    $contents[] = array('text' => $newsletter_history_string);
                  } else {
                    $contents[] = array('text' => TEXT_INFO_HISTORY_NEWSLETTER_NONE);
                  }

                  if ($oInfo->mail_status == '1') {
                    $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_NEWSLETTER_RECIPIENTS, 'page=' . $_GET['page'] . '&mail=' . md5($oInfo->customers_email_address) . '&action=delete') . '">' . BUTTON_UNSUBSCRIBE . '</a>');
                  }
                }
                break;
            }

            if (xtc_not_null($heading) && xtc_not_null($contents)) {
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