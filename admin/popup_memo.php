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
  include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/customers.php');

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
        if (isset($_GET['special']) && $_GET['special'] == 'remove_memo') {
          xtc_db_query("DELETE FROM ".TABLE_CUSTOMERS_MEMO." where memo_id='".(int)$_GET['mID']."'");
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