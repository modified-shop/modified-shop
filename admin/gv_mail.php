<?php
/* -----------------------------------------------------------------------------------------
   $Id: gv_mail.php 4255 2013-01-11 16:04:14Z web28 $

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
   
   Fix html email and error handling  (c) 2011-07-07 by web28 - www.rpa-com.de
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  require('includes/application_top.php');
  
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');
  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  // initiate template engine for mail
  $smarty = new Smarty;

  if ( ($_GET['action'] == 'send_email_to_user') && ($_POST['customers_email_address'] || $_POST['email_to']) && (!$_POST['back']) ) {
    switch ($_POST['customers_email_address']) {
      case '***':
        $mail_query = xtc_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS);
        $mail_sent_to = TEXT_ALL_CUSTOMERS;
        break;
      case '**D':
        $mail_query = xtc_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_newsletter = '1'");
        $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
        break;
      default:
        $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);

        $mail_query = xtc_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . xtc_db_input($customers_email_address) . "'");
        $mail_sent_to = $_POST['customers_email_address'];
        if ($_POST['email_to']) {
          $mail_sent_to = $_POST['email_to'];
        }
        break;
    }

    $from = xtc_db_prepare_input($_POST['from']);
    $subject = xtc_db_prepare_input($_POST['subject']);
    
    while ($mail = xtc_db_fetch_array($mail_query)) {
      $id1 = create_coupon_code($mail['customers_email_address']);

      // assign language to template for caching
      $smarty->assign('language', $_SESSION['language']);
      $smarty->caching = false;

      // set dirs manual
      $smarty->template_dir=DIR_FS_CATALOG.'templates';
      $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
      $smarty->config_dir=DIR_FS_CATALOG.'lang';

      $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

      $smarty->assign('AMMOUNT', $currencies->format($_POST['amount']));
      $smarty->assign('MESSAGE', $_POST['message']);
      $smarty->assign('GIFT_ID', $id1);
      $smarty->assign('WEBSITE', HTTP_SERVER  . DIR_WS_CATALOG);

      $link = HTTP_SERVER  . DIR_WS_CATALOG . 'gv_redeem.php' . '?gv_no='.$id1;

      $smarty->assign('GIFT_LINK',$link);

      $html_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/send_gift.html');
      $txt_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/send_gift.txt');

      if ($subject=='') $subject=EMAIL_BILLING_SUBJECT;
      xtc_php_mail(EMAIL_BILLING_ADDRESS,
                   EMAIL_BILLING_NAME, 
                   $mail['customers_email_address'], 
                   $mail['customers_firstname'] . ' ' . $mail['customers_lastname'], 
                   '', 
                   EMAIL_BILLING_REPLY_ADDRESS, 
                   EMAIL_BILLING_REPLY_ADDRESS_NAME, 
                   '', 
                   '', 
                   $subject, 
                   $html_mail, 
                   $txt_mail);

	    // Now create the coupon main and email entry
      $insert_query = xtc_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ('" . $id1 . "', 'G', '" . $_POST['amount'] . "', now())");
      $insert_id = xtc_db_insert_id();
      $insert_query = xtc_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $insert_id ."', '0', 'Admin', '" . $mail['customers_email_address'] . "', now() )");
    }
    if ($_POST['email_to']) {
      $id1 = create_coupon_code($_POST['email_to']);

      // assign language to template for caching
      $smarty->assign('language', $_SESSION['language']);
      $smarty->caching = false;

      // set dirs manual
      $smarty->template_dir=DIR_FS_CATALOG.'templates';
      $smarty->compile_dir=DIR_FS_CATALOG.'templates_c';
      $smarty->config_dir=DIR_FS_CATALOG.'lang';

      $smarty->assign('tpl_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/');
      $smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');
      $smarty->assign('AMMOUNT', $currencies->format($_POST['amount']));
      $smarty->assign('MESSAGE', stripslashes($_POST['message'])); //web28 2011-07-07 - Fix html email
      $smarty->assign('GIFT_ID', $id1);
      $smarty->assign('WEBSITE', HTTP_SERVER  . DIR_WS_CATALOG);

      $link = HTTP_SERVER  . DIR_WS_CATALOG . 'gv_redeem.php' . '?gv_no='.$id1;

      $smarty->assign('GIFT_LINK',$link);
      $html_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/send_gift.html');
      $txt_mail=$smarty->fetch(CURRENT_TEMPLATE . '/admin/mail/'.$_SESSION['language'].'/send_gift.txt');

      if ($subject == '') $subject = EMAIL_BILLING_SUBJECT; //web28 - 2011-07-07 - Fix email subject
      xtc_php_mail(EMAIL_BILLING_ADDRESS,
                   EMAIL_BILLING_NAME, 
                   $_POST['email_to'], 
                   '', 
                   '', 
                   EMAIL_BILLING_REPLY_ADDRESS, 
                   EMAIL_BILLING_REPLY_ADDRESS_NAME, 
                   '', 
                   '', 
                   $subject, 
                   $html_mail, 
                   $txt_mail); //web28 - 2011-07-07 - Fix email subject
      // Now create the coupon email entry
      $insert_query = xtc_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ('" . $id1 . "', 'G', '" . $_POST['amount'] . "', now())");
      $insert_id = xtc_db_insert_id();
      $insert_query = xtc_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $insert_id ."', '0', 'Admin', '" . $_POST['email_to'] . "', now() )");
    }
    xtc_redirect(xtc_href_link(FILENAME_GV_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to)));
  }
  
  $error = false;
  if ( ($_GET['action'] == 'preview') && (!$_POST['customers_email_address']) && (!$_POST['email_to']) ) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
    $error = true;
  }

  if ( ($_GET['action'] == 'preview') && (!$_POST['amount']) ) {
    $messageStack->add(ERROR_NO_AMOUNT_SELECTED, 'error');
    $error = true;
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']), 'success');
  }

  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG=='true' && ($_GET['action'] != 'preview' || $error== true)) {
    $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
    $data=xtc_db_fetch_array($query);
    echo xtc_wysiwyg('gv_mail',$data['code']);
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
          <div class="pageHeading"><?php echo HEADING_TITLE; ?></div>              
        </div>
        <div class="clear"></div>
      <div class="div_box brd-none pdg2">
        <?php
        if ($_GET['action'] == 'preview' && $error === false) {
          switch ($_POST['customers_email_address']) {
            case '***':
              $mail_sent_to = TEXT_ALL_CUSTOMERS;
              break;
            case '**D':
              $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
              break;
            default:
              $mail_sent_to = $_POST['customers_email_address'];
              if ($_POST['email_to']) {
                $mail_sent_to = $_POST['email_to'];
              }
              break;
          }  
        ?>
        
          <?php echo xtc_draw_form('mail', FILENAME_GV_MAIL, 'action=send_email_to_user'); ?>

              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo $mail_sent_to; ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_FROM; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['from'])); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo encode_htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_AMOUNT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo nl2br(encode_htmlspecialchars(stripslashes($_POST['amount']))); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo stripslashes($_POST['message']); ?></td>
                </tr>                
              </table> 
              <?php
              /* Re-Post all POST'ed variables */
              reset($_POST);
              while (list($key, $value) = each($_POST)) {
                if (!is_array($_POST[$key])) {
                  echo xtc_draw_hidden_field($key, encode_htmlspecialchars(stripslashes($value)));
                }
              }
              ?>
              
              <div class="smallText flt-l mrg5"><?php echo '<input type="submit" class="button" name="back" onclick="this.blur();" value="' . BUTTON_BACK . '"/>'; ?></div>
              <div class="smallText flt-r mrg5"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_GV_MAIL) . '">' . BUTTON_CANCEL . '</a> <input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>

          </form>
        <?php
        } else {

          if ($_GET['cID']) {
            $select='where customers_id='.$_GET['cID'];
          } else {
            $select = '';
            $customers = array();
            $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
            $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
            $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
          }
          $mail_query = xtc_db_query("select customers_id, customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " ".$select." order by customers_lastname");
          while($customers_values = xtc_db_fetch_array($mail_query)) {
            $customers[] = array('id' => $customers_values['customers_email_address'],
                                 'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
          }
          ?>

          <?php echo xtc_draw_form('mail', FILENAME_GV_MAIL, 'action=preview'); ?>

              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('customers_email_address', $customers, $_POST['customers_email_address']);?></td>
                </tr>
                 <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_TO; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('email_to'); ?><?php echo '&nbsp;&nbsp;' . TEXT_SINGLE_EMAIL; ?></td>
                </tr>
                 <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_FROM; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('from', EMAIL_FROM); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('subject', $_POST['subject']); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_AMOUNT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('amount', $_POST['amount']); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('message', 'soft', '100%', '55', $_POST['message']); ?></td>
                </tr>                
              </table> 

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
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>