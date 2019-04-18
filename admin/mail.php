<?php
/* --------------------------------------------------------------
   $Id$   

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

  // include needed functions
  require_once(DIR_FS_INC . 'xtc_wysiwyg.inc.php');
  require_once(DIR_FS_INC . 'xtc_php_mail.inc.php');

  $error = false;
  if (isset($_GET['action']) && $_GET['action'] != '') {
    switch ($_GET['action']) {
      case 'send_email_to_user':
        if (isset($_POST['customers_email_address']) 
            && $_POST['customers_email_address'] != ''
            && !isset($_POST['back'])
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
              
              $where = "WHERE customers_email_address = '" . xtc_db_input($mail_sent_to) . "'";
              if (is_numeric($mail_sent_to)) {
                $where = "WHERE customers_status = '" . (int)$mail_sent_to . "'";
              }
              $mail_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . " 
                                                 " . $where . "
                                        GROUP BY customers_email_address");
              if (isset($_POST['email_to']) && $_POST['email_to'] != '') {
                $mail_sent_to = $_POST['email_to'];
              }
              break;
          }

          $subject = xtc_db_prepare_input($_POST['subject']);
          $message = xtc_db_prepare_input($_POST['message']);
  
          if (xtc_db_num_rows($mail_query) > 0) {
            while ($mail = xtc_db_fetch_array($mail_query)) {
              $mail['subject'] = $subject; 
              $mail['message'] = $message; 
      
              send_gv_mail($data);
            }
          }
  
          if (isset($_POST['email_to']) && $_POST['email_to'] != '') {
            $mail['subject'] = $subject; 
            $mail['message'] = $message; 
            $mail['customers_email_address'] = $_POST['email_to'];
  
            send_gv_mail($data);
          }
  
          $messageStack->add_session(sprintf(NOTICE_EMAIL_SENT_TO, $mail_sent_to), 'success');
          if (isset($_GET['cID']) && $_GET['cID'] != '') {
            xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action'))));
          }
          xtc_redirect(xtc_href_link(FILENAME_MAIL, xtc_get_all_get_params(array('action', 'cID'))));
        }
        break;
      
      case 'preview':
        if (!isset($_POST['customers_email_address']) || $_POST['customers_email_address'] == '') {
          $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
          $error = true;
        }
        break;
    }
  }

  require (DIR_WS_INCLUDES.'head.php');

  if (USE_WYSIWYG == 'true' && ($_GET['action'] != 'preview' || $error == true)) {
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
                if (is_numeric($mail_sent_to)) {
                  $status = xtc_get_customers_statuses(true);
                  $mail_sent_to = $status[$mail_sent_to]['text'];
                }
                break;
            }  

            echo xtc_draw_form('mail', FILENAME_MAIL, 'action=send_email_to_user');
              ?>
              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo $mail_sent_to; ?></td>
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
                reset($_POST);
                foreach ($_POST as $key => $value) {
                  if (!is_array($_POST[$key])) {
                    echo xtc_draw_hidden_field($key, encode_htmlspecialchars(stripslashes($value)));
                  }
                }
              ?>
              <div class="smallText flt-l mrg5"><?php echo '<input type="submit" class="button" name="back" onclick="this.blur();" value="' . BUTTON_BACK . '"/>'; ?></div>
              <div class="smallText flt-r mrg5"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MAIL, xtc_get_all_get_params(array('action'))) . '">' . BUTTON_CANCEL . '</a> <input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SEND_EMAIL . '"/>'; ?></div>
            </form>
            <?php
          } else {

            $customers = array();
            $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
            $customers[] = array('id' => '***', 'text' => TEXT_ALL_CUSTOMERS);
            $customers[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_CUSTOMERS);
            $customers = array_merge($customers, xtc_get_customers_statuses());

            $selected_customer = urldecode((isset($_GET['customer'])) ? $_GET['customer'] : ((isset($_POST['customers_email_address'])) ? $_POST['customers_email_address'] : ''));
            
            if ($selected_customer != '') {
              $mail_query = xtc_db_query("SELECT *
                                            FROM " . TABLE_CUSTOMERS . " 
                                           WHERE customers_email_address = '".xtc_db_input($selected_customer)."'
                                        GROUP BY customers_email_address
                                        ORDER BY customers_lastname");
              while($customers_values = xtc_db_fetch_array($mail_query)) {
                $customers[] = array(
                  'id' => $customers_values['customers_email_address'],
                  'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')'
                );
              }
            }
            
            echo xtc_draw_form('mail', FILENAME_MAIL, xtc_get_all_get_params(array('action')).'action=preview');
              ?>
              <table class="tableConfig borderall">
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_CUSTOMER; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_pull_down_menu('customers_email_address', $customers, $selected_customer);?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_SUBJECT; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_input_field('subject', ((isset($_POST['subject'])) ? $_POST['subject'] : ''), 'style="width: 100%;"'); ?></td>
                </tr>
                <tr>
                  <td class="dataTableConfig col-left"><?php echo TEXT_MESSAGE; ?></td>
                  <td class="dataTableConfig col-single-right"><?php echo xtc_draw_textarea_field('message', 'soft', '100%', '55', ((isset($_POST['message'])) ? $_POST['message'] : '')); ?></td>
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>