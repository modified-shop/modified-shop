<?php
   /* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_queue.php,v 1.2.2.5 2003/05/05); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require('includes/application_top.php');
  
  //display per page
  $cfg_max_display_results_key = 'MAX_DISPLAY_GV_QUEUE_RESULTS';
  $page_max_display_results = xtc_cfg_save_max_display_results($cfg_max_display_results_key);
   
  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

  // initiate template engine for mail
  $smarty = new Smarty;

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $page = (isset($_GET['page']) ? (int)$_GET['page'] : 1);
  
  switch ($action) {
    case 'releaseconfirm':
      $gv_query = xtc_db_query("SELECT * 
                                  FROM " . TABLE_COUPON_GV_QUEUE . " 
                                 WHERE unique_id='".(int)$_GET['gid']."'
                                   AND release_flag = 'N'");
      if (xtc_db_num_rows($gv_query) > 0) {
        $gv = xtc_db_fetch_array($gv_query);
                
        $gv_amount = $gv['amount'];
        
        //Let's build a message object using the email class
        $mail_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_CUSTOMERS . " 
                                     WHERE customers_id = '" . $gv['customer_id'] . "'");
        $mail = xtc_db_fetch_array($mail_query);

        // assign language to template for caching
        $smarty->assign('language', $_SESSION['language']);
        $smarty->caching = false;

        // set dirs manual
        $smarty->template_dir = DIR_FS_CATALOG.'templates';
        $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
        $smarty->config_dir = DIR_FS_CATALOG.'lang';

        $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
        $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

        $smarty->assign('AMMOUNT',$currencies->format($gv_amount));
        $smarty->assign('NAME', $mail['customers_firstname'].' '.$mail['customers_lastname']);
        $smarty->assign('GENDER', $mail['customers_gender']);
        $smarty->assign('FIRSTNAME', $mail['customers_firstname']);
        $smarty->assign('LASTNAME', $mail['customers_lastname']);

        $html_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/gift_accepted.html');
        $txt_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/gift_accepted.txt');

        xtc_php_mail(EMAIL_BILLING_ADDRESS,
                     EMAIL_BILLING_NAME,
                     $mail['customers_email_address'], 
                     $mail['customers_firstname'] . ' ' . $mail['customers_lastname'], 
                     '', 
                     EMAIL_BILLING_REPLY_ADDRESS, 
                     EMAIL_BILLING_REPLY_ADDRESS_NAME, 
                     '', 
                     '', 
                     EMAIL_BILLING_SUBJECT, 
                     $html_mail, 
                     $txt_mail);

        
        $check_query = xtc_db_query("SELECT amount 
                                       FROM " . TABLE_COUPON_GV_CUSTOMER . " 
                                      WHERE customer_id = '".$gv['customer_id']."'");
        if (xtc_db_num_rows($check_query) > 0) {
          $check = xtc_db_fetch_array($check_query);
          $gv_amount += $check['amount'];
          xtc_db_query("UPDATE " . TABLE_COUPON_GV_CUSTOMER . " 
                           SET amount = '".$gv_amount."' 
                         WHERE customer_id = '".$gv['customer_id']."'");
        } else {
          $sql_data_array = array(
            'customer_id' => $gv['customer_id'],
            'amount' => $gv_amount
          );
          xtc_db_perform(TABLE_COUPON_GV_CUSTOMER, $sql_data_array);
        }
        
        xtc_db_query("UPDATE " . TABLE_COUPON_GV_QUEUE . " 
                         SET release_flag='Y' 
                       WHERE unique_id='".(int)$_GET['gid']."'");
      }
      xtc_redirect(xtc_href_link('gv_queue.php', xtc_get_all_get_params(array('action', 'gid'))));
      break;
    
    case 'deleteconfirm':
      xtc_db_query("DELETE FROM " . TABLE_COUPON_GV_QUEUE . " WHERE unique_id = '" . (int)$_GET['gid'] . "'");
      xtc_redirect(xtc_href_link('gv_queue.php', xtc_get_all_get_params(array('action', 'gid'))));
      break;
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
        <div class="flt-l">
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>              
        </div>    
        <table class="tableCenter">
          <tr>
            <td class="boxCenterLeft">
              <table class="tableBoxCenter collapse">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent txta-c"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent txta-r"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
              <?php
                $gv_query_raw = "SELECT c.customers_firstname, 
                                        c.customers_lastname, 
                                        gv.unique_id, 
                                        gv.date_created, 
                                        gv.amount, 
                                        gv.order_id 
                                   FROM " . TABLE_CUSTOMERS . " c
                                   JOIN " . TABLE_COUPON_GV_QUEUE . " gv 
                                        ON gv.customer_id = c.customers_id
                                           AND gv.release_flag = 'N'";
                $gv_split = new splitPageResults($page, $page_max_display_results, $gv_query_raw, $gv_query_numrows);
                $gv_query = xtc_db_query($gv_query_raw);
                while ($gv_list = xtc_db_fetch_array($gv_query)) {
                  if ((!isset($_GET['gid']) || ($_GET['gid'] == $gv_list['unique_id'])) && !isset($gInfo)) {
                    $gInfo = new objectInfo($gv_list);
                  }
                  if (isset($gInfo) && is_object($gInfo) && $gv_list['unique_id'] == $gInfo->unique_id) {
                    echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link('gv_queue.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->unique_id . '&action=edit') . '\'">' . "\n";
                  } else {
                    echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link('gv_queue.php', xtc_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list['unique_id']) . '\'">' . "\n";
                  }
                  ?>
                <td class="dataTableContent"><?php echo $gv_list['customers_firstname'] . ' ' . $gv_list['customers_lastname']; ?></td>
                <td class="dataTableContent txta-c"><?php echo $gv_list['order_id']; ?></td>
                <td class="dataTableContent txta-r"><?php echo $currencies->format($gv_list['amount']); ?></td>
                <td class="dataTableContent txta-r"><?php echo xtc_datetime_short($gv_list['date_created']); ?></td>
                <td class="dataTableContent txta-r"><?php if ((!isset($_GET['gid']) || ($_GET['gid'] == $gv_list['unique_id'])) && !isset($gInfo)) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_GV_QUEUE, 'page=' . $page . '&gid=' . $gv_list['unique_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_arrow_grey.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
              <?php
                }
              ?>
            </table>

            <div class="smallText pdg2 flt-l"><?php echo $gv_split->display_count($gv_query_numrows, $page_max_display_results, $page, TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></div>
            <div class="smallText pdg2 flt-r"><?php echo $gv_split->display_links($gv_query_numrows, $page_max_display_results, MAX_DISPLAY_PAGE_LINKS, $page); ?></div>
            <?php echo draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results); ?>
          </td>
          <?php
            if (isset($gInfo) && is_object($gInfo)) {
              $heading = array();
              $contents = array();
              switch ($action) {
                case 'delete':
                  $heading[] = array('text' => '<b>[' . $gInfo->unique_id . '] ' . xtc_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount).'</b>');
                  $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=deleteconfirm&gid='.$gInfo->unique_id,'NONSSL').'">'. BUTTON_CONFIRM . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=cancel&gid=' . $gInfo->unique_id,'NONSSL') . '">' . BUTTON_CANCEL . '</a>');
                  break;
                case 'release':
                  $heading[] = array('text' => '<b>[' . $gInfo->unique_id . '] ' . xtc_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount).'</b>');
                  $contents[] = array('text' => TEXT_INFO_REDEEM_INTRO);
                  $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=releaseconfirm&gid='.$gInfo->unique_id,'NONSSL').'">'. BUTTON_CONFIRM . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=cancel&gid=' . $gInfo->unique_id,'NONSSL') . '">' . BUTTON_CANCEL . '</a>');
                  break;
                default:
                  $heading[] = array('text' => '[' . $gInfo->unique_id . '] ' . xtc_datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount));
                  $contents[] = array('align' => 'center','text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=release&gid=' . $gInfo->unique_id,'NONSSL'). '">' . BUTTON_RELEASE . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link('gv_queue.php','action=delete&gid=' . $gInfo->unique_id,'NONSSL'). '">' . BUTTON_DELETE . '</a>');
                  break;
              }
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