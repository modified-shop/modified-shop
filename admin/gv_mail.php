<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (gv_mail.php,v 1.3.2.4 2003/05/12); www.oscommerce.com
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c) Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require('includes/application_top.php');

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');
  require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');

  // include needed classes
  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  // initiate template engine for mail
  $smarty = new Smarty;

  // set dirs manual
  $smarty->template_dir = DIR_FS_CATALOG.'templates';
  $smarty->compile_dir = DIR_FS_CATALOG.'templates_c';
  $smarty->config_dir = DIR_FS_CATALOG.'lang';


  function send_gv_mail($data) {
    global $currencies, $smarty;
    
    $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
    $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

    $smarty->assign('MESSAGE', $data['message']);
    $smarty->assign('WEBSITE', HTTP_SERVER.DIR_WS_CATALOG);
    
    if (isset($_GET['cid']) && $_GET['cid'] != '') {
      $template = 'send_coupon';
      
      $smarty->assign('COUPON_ID', $data['coupon_code']);
      $smarty->assign('COUPON_AMOUNT', $data['coupon_amount']);
    } else {
      $template = 'send_gift';
      
      $coupon_code = create_coupon_code($data['customers_email_address']);
      $link = HTTP_SERVER.DIR_WS_CATALOG.'gv_redeem.php?gv_no='.$coupon_code;
      
      $smarty->assign('AMMOUNT', $currencies->format($data['coupon_amount']));
      $smarty->assign('GIFT_ID', $coupon_code);
      $smarty->assign('GIFT_LINK', $link);     
    }
    
    // assign language to template for caching
    $smarty->assign('language', $_SESSION['language']);
    $smarty->caching = false;

    $html_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/'.$template.'.html');
    $txt_mail = $smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/'.$template.'.txt');
    $txt_mail = strip_tags($txt_mail);
    
    xtc_php_mail(EMAIL_BILLING_ADDRESS,
                 EMAIL_BILLING_NAME, 
                 $data['customers_email_address'], 
                 $data['customers_firstname'] . ' ' . $data['customers_lastname'], 
                 '', 
                 EMAIL_BILLING_REPLY_ADDRESS, 
                 EMAIL_BILLING_REPLY_ADDRESS_NAME, 
                 '', 
                 '', 
                 $data['subject'], 
                 $html_mail, 
                 $txt_mail);
    
    if (!isset($_GET['cid']) || $_GET['cid'] == '') {
      $sql_data_array = array(
        'coupon_code' => $coupon_code,
        'coupon_type' => 'G',
        'coupon_amount' => $data['coupon_amount'],
        'date_created' => 'now()',
      );
      xtc_db_perform(TABLE_COUPONS, $sql_data_array);
      $insert_id = xtc_db_insert_id();

      $sql_data_array = array(
        'coupon_id' => $insert_id,
        'customer_id_sent' =>(int)$_SESSION['customer_id'],
        'sent_firstname' => 'Admin',
        'emailed_to' => $data['customers_email_address'],
        'date_sent' => 'now()',
      );
      xtc_db_perform(TABLE_COUPON_EMAIL_TRACK, $sql_data_array);
    }
  }

  $error = false;
  switch ($action) {
    case 'send_email_to_user':
      if (((isset($_POST['customers_email_address']) && $_POST['customers_email_address'] != '')
           || (isset($_POST['email_to']) && $_POST['email_to'] != '')
           ) && !isset($_POST['back'])
          )
      {
        switch ($_POST['customers_email_address']) {
          case '***':
            $mail_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_CUSTOMERS . " 
                                      GROUP BY customers_email_address");
            $mail_sent_to = TEXT_ALL_CUSTOMERS;
            break;
          case '**D':
            $mail_query = xtc_db_query("SELECT *
                                          FROM " . TABLE_NEWSLETTER_RECIPIENTS . " 
                                         WHERE mail_status = '1'");
            $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
            break;
          default:
            $mail_sent_to = xtc_db_prepare_input($_POST['customers_email_address']);
            if ($mail_sent_to != '') {
              $mail_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . " 
                                           WHERE customers_status = '" . (int)$mail_sent_to . "'
                                        GROUP BY customers_email_address");
                                                        
              $customers_status = xtc_get_customers_statuses(true);
              $mail_sent_to = $customers_status[$mail_sent_to]['text'];
            }
            if (isset($_POST['email_to']) && $_POST['email_to'] != '') {
              $mail_sent_to = $_POST['email_to'];
            }
            break;
        }

        $subject = xtc_db_prepare_input($_POST['subject']);
        $message = xtc_db_prepare_input($_POST['message']);
        $coupon_amount = xtc_db_prepare_input($_POST['coupon_amount']);
        $coupon_code = '';
        
        if (isset($_GET['cid']) && $_GET['cid'] != '') {
          $coupon_query = xtc_db_query("SELECT * 
                                          FROM " . TABLE_COUPONS . " 
                                         WHERE coupon_id = '" . (int)$_GET['cid'] . "'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon_code = $coupon['coupon_code'];

          $coupon_amount = '';
          if ($coupon_result['coupon_type'] == 'S') {
            $coupon_amount = COUPON_INFO . COUPON_FREE_SHIPPING;
          } else {
            $coupon_amount = COUPON_INFO . $currencies->format($coupon['coupon_amount']) . ' ';
          }
          if ($coupon_result['coupon_type'] == 'P') {
            $coupon_amount = COUPON_INFO . number_format($coupon['coupon_amount'], 2) . '% ';
          }
          if ($coupon_result['coupon_type'] == 'T') {
            $coupon_amount = COUPON_INFO . COUPON_FREE_SHIPPING . ' | '. number_format($coupon['coupon_amount'], 2) . '% ';
          }
          if ($coupon_result['coupon_minimum_order'] > 0) {
            $coupon_amount .= COUPON_MINORDER_INFO . $currencies->format($coupon['coupon_minimum_order']) . ' ';
          }
          if (trim($coupon_result['restrict_to_products']) != '' || trim($coupon['restrict_to_categories']) != '') {
            $coupon_amount .= COUPON_RESTRICT_INFO;
          }
          
          $coupon_amount = nl2br($coupon_amount);
        }
        
        if (isset($mail_query) 
            && is_object($mail_query)
            && xtc_db_num_rows($mail_query) > 0
            )
        {
          while ($mail = xtc_db_fetch_array($mail_query)) {
            $mail['subject'] = $subject; 
            $mail['message'] = $message; 
            $mail['coupon_amount'] = $coupon_amount;
            $mail['coupon_code'] = $coupon_code;
    
            send_gv_mail($mail);
          }
        }

        if (isset($_POST['email_to']) && $_POST['email_to'] != '') {
          $mail['subject'] = $subject; 
          $mail['message'] = $message;
          $mail['coupon_code'] = $coupon_code;
          $mail['coupon_amount'] = $coupon_amount;
          $mail['customers_email_address'] = $_POST['email_to'];
          $mail['customers_firstname'] = $_POST['email_to'];
          $mail['customers_lastname'] = '';

          send_gv_mail($mail);
        }

        $messageStack->add_session(sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to), 'success');
        if (isset($_GET['oID']) && $_GET['oID'] != '') {
          xtc_redirect(xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'cID')).'action=edit'));
        }
        if (isset($_GET['cid']) && $_GET['cid'] != '') {
          xtc_redirect(xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'cid')).'cid='.(int)$_GET['cid']));
        }
        xtc_redirect(xtc_href_link(FILENAME_GV_MAIL, xtc_get_all_get_params(array('action', 'cID'))));
      }
      break;
    
    case 'preview':
      if ((!isset($_POST['customers_email_address']) || $_POST['customers_email_address'] == '')
          && (!isset($_POST['email_to']) || $_POST['email_to'] == '')
          )
      {
        $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
        $error = true;
      }
    
      if ((!isset($_POST['coupon_amount']) || $_POST['coupon_amount'] == '')
          && (!isset($_GET['cid']) || $_GET['cid'] == '')
          )
      {
        $messageStack->add(ERROR_NO_AMOUNT_SELECTED, 'error');
        $error = true;
      }
      break;
  }

  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG == 'true' && ($action != 'preview' || $error == true)) {
    $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
    $data=xtc_db_fetch_array($query);
    echo xtc_wysiwyg('gv_mail', $data['code']);
  } 
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
          <div class="pageHeading"><?php echo ((isset($_GET['cid']) && $_GET['cid'] != '') ? HEADING_COUPON_TITLE : HEADING_TITLE); ?></div>              
        </div>
        <div class="clear"></div>
        <div class="div_box brd-none pdg2">
          <?php
          if ($action == 'preview' && $error === false) {
            switch ($_POST['customers_email_address']) {
              case '***':
                $mail_sent_to = TEXT_ALL_CUSTOMERS;
                break;
              case '**D':
                $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
                break;
              default:
                $mail_sent_to = $_POST['customers_email_address'];
                if (isset($_POST['email_to']) && $_POST['email_to'] != '') {
                  $mail_sent_to = $_POST['email_to'];
                } else {
                  $customers_status = xtc_get_customers_statuses(true);
                  $mail_sent_to = $customers_status[$mail_sent_to]['text'];
                }
                break;
            }  

            echo xtc_draw_form('mail', FILENAME_GV_MAIL, xtc_get_all_get_params(array('action')).'action=send_email_to_user');
              ?>
              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER_GROUP; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo $mail_sent_to; ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
                </tr>
                <?php if (!isset($_GET['cid']) || $_GET['cid'] == '') { ?>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_AMOUNT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['coupon_amount'])); ?></td>
                </tr>
                <?php } ?>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo stripslashes($_POST['message']); ?></td>
                </tr>                
              </table> 
              <?php
                reset($_POST);
                foreach ($_POST as $key => $value) {
                  if (!is_array($_POST[$key])) {
                    echo xtc_draw_hidden_field($key, encode_htmlspecialchars(stripslashes($value)));
                  }
                }
              ?>
              <div class="smallText flt-l mrg5"><?php echo '<input type="submit" class="button" name="back" onclick="this.blur();" value="' . BUTTON_BACK . '"/>'; ?></div>
              <div class="smallText flt-r mrg5"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GV_MAIL, xtc_get_all_get_params(array('action'))) . '">' . BUTTON_CANCEL . '</a> <input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
            </form>
            <?php
          } else {

            $select = '';
            $customers = array();
            $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER_GROUP);
            $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
            $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
            $customers = array_merge($customers, xtc_get_customers_statuses());
            
            $selected_customer = ((isset($_POST['customers_email_address'])) ? $_POST['customers_email_address'] : '');
            
            if (isset($_GET['cID']) && $_GET['cID'] != '') {
              $mail_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . " 
                                           WHERE customers_id = '".(int)$_GET['cID']."'
                                        GROUP BY customers_email_address
                                        ORDER BY customers_lastname");
              if (xtc_db_num_rows($mail_query) > 0) {
                $customers_values = xtc_db_fetch_array($mail_query);
                $_POST['email_to'] = $customers_values['customers_email_address'];
              }
            }

            echo xtc_draw_form('mail', FILENAME_GV_MAIL, xtc_get_all_get_params(array('action')).'action=preview');
              ?>
              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER_GROUP; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('customers_email_address', $customers, $selected_customer);?></td>
                </tr>
                 <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_TO; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('email_to', ((isset($_POST['email_to'])) ? $_POST['email_to'] : '')); ?><?php echo '&nbsp;&nbsp;' . TEXT_SINGLE_EMAIL; ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('subject', ((isset($_POST['subject'])) ? $_POST['subject'] : ''), 'style="width: 100%;"'); ?></td>
                </tr>
                <?php if (!isset($_GET['cid']) || $_GET['cid'] == '') { ?>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_AMOUNT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('coupon_amount', ((isset($_POST['coupon_amount'])) ? $_POST['coupon_amount'] : '')); ?></td>
                </tr>
                <?php } ?>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('message', 'soft', '100%', '55', ((isset($_POST['message'])) ? $_POST['message'] : '')); ?></td>
                </tr>                
              </table> 

              <?php if (isset($_GET['oID']) && $_GET['oID'] != '') { ?>
              <div class="smallText flt-l mrg5"><?php echo '<a class="button" href="' . xtc_href_link(FILENAME_ORDERS, xtc_get_all_get_params(array('action', 'cID')).'action=edit') . '"/>' . BUTTON_BACK . '</a>'; ?></div>
              <?php } ?>

              <?php if (isset($_GET['cid']) && $_GET['cid'] != '') { ?>
              <div class="smallText flt-l mrg5"><?php echo '<a class="button" href="' . xtc_href_link(FILENAME_COUPON_ADMIN, xtc_get_all_get_params(array('action', 'cid')).'cid='.(int)$_GET['cid']) . '"/>' . BUTTON_BACK . '</a>'; ?></div>
              <?php } ?>
              
              <div class="smallText mrg5 txta-r"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
            </form>
          <?php
          }
        ?>
        </div>
      </td>
      <!-- body_text_eof //-->
    </tr>
  </table>
  <!-- body_eof //-->
  <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
  <!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>