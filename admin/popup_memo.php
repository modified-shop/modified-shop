<?php
/* --------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  require('includes/application_top.php');
  require_once(DIR_FS_INC.'admin_form_posted.inc.php');
  require_once(DIR_FS_INC.'customers_account_locked.inc.php');
  include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/customers.php');

  // the central token check refused the form and removed the POST
  $memo_retry_message = (defined('ERROR_ADMIN_ACTION_TOKEN') ? ERROR_ADMIN_ACTION_TOKEN : 'The form has expired. Reload the page and try again.');
  if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST)) {
    $messageStack->add($memo_retry_message, 'warning');
  }

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {

      case 'save':
        $memo_title = xtc_db_prepare_input($_POST['memo_title']);
        $memo_text = xtc_db_prepare_input($_POST['memo_text']);

        if ($memo_text != '' && $memo_title != '' ) {
          $sql_data_array = array(
            'customers_id' => $_GET['cID'],
            'memo_date' => date("Y-m-d"),
            'memo_title' =>$memo_title,
            'memo_text' => nl2br($memo_text),
            'poster_id' => (int)$_SESSION['customer_id']
          );
          xtc_db_perform(TABLE_CUSTOMERS_MEMO, $sql_data_array);
        }
        xtc_redirect(xtc_href_link('popup_memo.php', xtc_get_all_get_params(array('action'))));
        break;

      case 'edit':
        // deleting needs the form with its token, not a link
        if (isset($_GET['special']) && $_GET['special'] == 'remove_memo') {
          // only a memo of this account, and of an account with admin status only with the right for it
          if (admin_form_posted() && isset($_GET['cID']) && customers_account_locked($_GET['cID'])) {
            $messageStack->add_session((defined('ERROR_CUSTOMERS_ADMIN_ACCOUNT') ? ERROR_CUSTOMERS_ADMIN_ACCOUNT : 'Accounts with admin status can only be changed by administrators with the right to manage admin rights.'), 'warning');
          } elseif (admin_form_posted()) {
            xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_MEMO." where memo_id='".(int)$_GET['mID']."' AND customers_id = '".(isset($_GET['cID']) ? (int)$_GET['cID'] : 0)."'");
          } else {
            $messageStack->add_session($memo_retry_message, 'warning');
          }
        }
        xtc_redirect(xtc_href_link('popup_memo.php', xtc_get_all_get_params(array('action','special','mID'))));
        break;
    }
  }

  require (DIR_WS_INCLUDES.'head.php');
?>
</head>
  <body>
    <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS.'heading/icon_customers.png'); ?></div>
    <div class="flt-l">
      <div class="pageHeading"><?php echo TITLE_MEMO; ?></div>
      <div class="main pdg2"><?php echo BOX_HEADING_CUSTOMERS; ?></div>
    </div>
    <?php
      // the popup has no page header, so a refused action shows its message here
      if (isset($messageStack) && is_object($messageStack) && $messageStack->size > 0) {
        echo '<div class="clear"></div>'.$messageStack->output();
      }
    ?>
    <?php echo xtc_draw_form('customers_memo', 'popup_memo.php', 'action=save&cID='.(int)$_GET['cID'], 'post'); ?>  
      <table class="tableConfig borderall">
        <tr>
        <?php
          require(DIR_WS_INCLUDES.'modules/customer_memo.php');
        ?>
        </tr>
      </table>
    </form>
  </body>
</html>