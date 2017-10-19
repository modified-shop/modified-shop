<?php
/* --------------------------------------------------------------
   $Id: mail.php 4255 2013-01-11 16:04:14Z web28 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(mail.php,v 1.30 2002/03/16 01:07:28); www.oscommerce.com 
   (c) 2003	 nextcommerce (mail.php,v 1.11 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  require_once (DIR_FS_INC.'xtc_php_mail.inc.php');
  require_once (DIR_FS_INC . 'xtc_wysiwyg.inc.php'); 

  if ( ($_GET['action'] == 'send_email_to_user') && ($_POST['customers_email_address']) && (!$_POST['back']) ) {
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
        if (is_numeric($_POST['customers_email_address'])) {
          $mail_query = xtc_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_status = " . $_POST['customers_email_address']);
          $sent_to_query = xtc_db_query("select customers_status_name from " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . $_POST['customers_email_address'] . "' AND language_id='" . (int)$_SESSION['languages_id'] . "'");
          $sent_to = xtc_db_fetch_array($sent_to_query);
          $mail_sent_to = $sent_to['customers_status_name'];
        } else {
          $customers_email_address = xtc_db_prepare_input($_POST['customers_email_address']);
          $mail_query = xtc_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_email_address = '" . xtc_db_input($customers_email_address) . "'");
          $mail_sent_to = $_POST['customers_email_address'];
        }
        break;
    }

    $from = xtc_db_prepare_input($_POST['from']);
    $subject = xtc_db_prepare_input($_POST['subject']);
    $message = xtc_db_prepare_input($_POST['message']);


    while ($mail = xtc_db_fetch_array($mail_query)) {

      xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                   EMAIL_SUPPORT_NAME,
                   $mail['customers_email_address'] ,
                   $mail['customers_firstname'] . ' ' . $mail['customers_lastname'] ,
                   '',
                   EMAIL_SUPPORT_REPLY_ADDRESS,
                   EMAIL_SUPPORT_REPLY_ADDRESS_NAME,
                   '',
                   '',
                   $subject,
                   $message,
                   $message);
    }



    xtc_redirect(xtc_href_link(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to)));
  }
  $error = false;
  if ( ($_GET['action'] == 'preview') && (!$_POST['customers_email_address']) ) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
    $error = true;
  }

  if ($_GET['mail_sent_to']) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']), 'success');
  }
  
  if (isset($_POST['customers_email_address']) && !empty($_POST['customers_email_address'])) {
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
  }

  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG=='true' && ($_GET['action'] != 'preview' || $error== true)) {
    $query=xtc_db_query("SELECT code FROM ". TABLE_LANGUAGES ." WHERE languages_id='".(int)$_SESSION['languages_id']."'");
    $data=xtc_db_fetch_array($query);
    echo xtc_wysiwyg('mail',$data['code']);
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
      <div class="mrg5" style="width:1000px;">
        <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_news.png'); ?></div>
        <div class="flt-l">
          <div class="pageHeading pdg2"><?php echo HEADING_TITLE; ?></div>              
        </div>
        <div class="clear"></div>
        <?php
          if ( ($_GET['action'] == 'preview') && ($_POST['customers_email_address']) ) {
            switch ($_POST['customers_email_address']) {
              case '***':
                $mail_sent_to = TEXT_ALL_CUSTOMERS;
                break;
              case '**D':
                $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
                break;
              default:
                if (is_numeric($_POST['customers_email_address'])) {
                  $sent_to_query = xtc_db_query("select customers_status_name from " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . $_POST['customers_email_address'] . "' AND language_id='" . (int)$_SESSION['languages_id'] . "'");
                  $sent_to = xtc_db_fetch_array($sent_to_query);
                  $mail_sent_to = $sent_to['customers_status_name'];
                } else {
                  $mail_sent_to = $_POST['customers_email_address'];
                }
                break;
            }
        ?>
        <?php echo xtc_draw_form('mail', FILENAME_MAIL, 'action=send_email_to_user'); ?>
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
              <div class="smallText flt-r mrg5"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MAIL) . '">' . BUTTON_CANCEL . '</a> <input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>

          </form>
 
        <?php
          } else {
            $customers = array();
            $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
            $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
            $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
            // Customers Status 1.x
            //    $customers_statuses_array = xtc_get_customers_statuses();
            $customers_statuses_array = xtc_db_query("select customers_status_id , customers_status_name from " . TABLE_CUSTOMERS_STATUS . " WHERE language_id='" . (int)$_SESSION['languages_id'] . "' order by customers_status_name");
            while ($customers_statuses_value = xtc_db_fetch_array($customers_statuses_array)) {
              $customers[] = array('id' => $customers_statuses_value['customers_status_id'],
                                   'text' => $customers_statuses_value['customers_status_name']);
            }
            // End customers Status 1.x
            $mail_query = xtc_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " order by customers_lastname");
            while($customers_values = xtc_db_fetch_array($mail_query)) {
              $customers[] = array('id' => $customers_values['customers_email_address'],
                                   'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')');
            }
          ?>
          <?php echo xtc_draw_form('mail', FILENAME_MAIL, 'action=preview'); ?>
              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('customers_email_address', $customers, ((isset($_GET['customer'])) ? $_GET['customer'] : $_POST['customers_email_address']));?></td>
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
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('message', 'soft', '100%', '55', $_POST['message']); ?></td>
                </tr>                
              </table>
              
            <div class="smallText flt-r mrg5"><?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
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