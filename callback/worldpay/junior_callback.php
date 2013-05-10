<?php
/* -----------------------------------------------------------------------------------------
   $Id: junior_callback.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2011 osCommerce(advanced_search_result.php,v 1.68 2003/05/14); www.oscommerce.com

   UPDATED 17-05-2011
   Added Debugging Support Via Email
   Included Meta Refresh Redirect thanks to user g_p
   Removed whitespace added by user g_p causing PHP Header/Session Errors
   Removed unknown post variables added by user g_p ($_POST['hash'] should be $_POST['M_hash'])
   Updated Documentation

   UPDATED 04-12-2009
   Deprecated $HTTP_POST_VARS replaced with $_POST to enable callback on all platforms/php versions.
   Javascript dependancy for page redirection replaced with form action otherwise Worldpay removes javascript and leaves
   visitors stranded.

   Updated By Pete Batin (petebuzzin in Oscommerce forums)
   Original Contribution by Harald Ponce de Leon

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// Define your debug email address
$RBSPostEmail = ''; // e.g $RBSPostEmail = 'youremail@yourdomain.com';

$RBSDebugActive = false; //Change to false to disable debug emails.

// Build Email
$message = "RBS WorldPay Post Values\n".'\n';
foreach($_POST as $name => $value) {
        $message .= "$name : $value";
}
// In case any of our lines are larger than 70 characters, we should use wordwrap()
$message = wordwrap($message, 70);

if($RBSDebugActive){
        // Send Email
        mail($RBSPostEmail, 'RBS WorldPay Post Values', $message);
}

  if (isset($_POST['M_sid']) && !empty($_POST['M_sid'])) {
    chdir('../../'); //DokuMan - 2011-08-09 - fix directory traversal
    require ('includes/application_top.php');

    if ($_POST['transStatus'] == 'Y') {
      $pass = false;

      if (isset($_POST['M_hash']) && !empty($_POST['M_hash']) && ($_POST['M_hash'] == md5($_POST['M_sid'] . $_POST['M_cid'] . $_POST['cartId'] . $_POST['M_lang'] . number_format($_POST['amount'], 2) . MODULE_PAYMENT_WORLDPAY_JUNIOR_MD5_PASSWORD))) {
        $pass = true;
      }

      if (isset($_POST['callbackPW']) && ($_POST['callbackPW'] != MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD)) {
        $pass = false;
      }

      if (xtc_not_null(MODULE_PAYMENT_WORLDPAY_JUNIOR_CALLBACK_PASSWORD) && !isset($_POST['callbackPW'])) {
        $pass = false;
      }

      if ($pass == true) {
        include('lang/' . basename($_POST['M_lang']) . '/modules/payment/worldpay_junior.php'); //DokuMan - 2011-08-29 - fix language path

        $order_query = xtc_db_query("select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . (int)$_POST['cartId'] . "' and customers_id = '" . (int)$_POST['M_cid'] . "'");
        if (xtc_db_num_rows($order_query) > 0) {
          $order = xtc_db_fetch_array($order_query);

          if ($order['orders_status'] == MODULE_PAYMENT_WORLDPAY_JUNIOR_PREPARE_ORDER_STATUS_ID) {
            $order_status_id = (MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_WORLDPAY_JUNIOR_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID);

            xtc_db_query("update " . TABLE_ORDERS . " set orders_status = '" . $order_status_id . "', last_modified = now() where orders_id = '" . (int)$_POST['cartId'] . "'");

            $sql_data_array = array('orders_id' => $_POST['cartId'],
                                    'orders_status_id' => $order_status_id,
                                    'date_added' => 'now()',
                                    'customer_notified' => '0',
                                    'comments' => 'WorldPay: Transaction Verified');

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            if (MODULE_PAYMENT_WORLDPAY_JUNIOR_TESTMODE == 'True') {
              $sql_data_array = array('orders_id' => $_POST['cartId'],
                                      'orders_status_id' => $order_status_id,
                                      'date_added' => 'now()',
                                      'customer_notified' => '0',
                                      'comments' => MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_WARNING_DEMO_MODE);

              xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            }
?>
<meta http-equiv="refresh" content="5;url=<?php echo xtc_href_link(FILENAME_CHECKOUT_PROCESS, xtc_session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['M_hash'], 'SSL', false); ?>" />
<style>
.pageHeading {
  font-family: Verdana, Arial, sans-serif;
  font-size: 20px;
  font-weight: bold;
  color: #9a9a9a;
}

.main {
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
  line-height: 1.5;
}
</style>

<p class="pageHeading"><?php echo STORE_NAME; ?></p>

<p class="main" align="center"><?php echo MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_SUCCESSFUL_TRANSACTION; ?></p>

<form action="<?php echo xtc_href_link(FILENAME_CHECKOUT_PROCESS, xtc_session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['M_hash'], 'SSL', false); ?>" method="post">
  <div align="center">
    <input name="submit" type="submit" value="<?php echo sprintf(MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON, addslashes(STORE_NAME)); ?>" />
    </div>
</form>

<p>&nbsp;</p>

<WPDISPLAY ITEM=banner>
<?php
          }
        }
      }
    }else{
    include('lang/' . basename($_POST['M_lang']) . '/modules/payment/worldpay_junior.php');
    ?>
<meta http-equiv="refresh" content="5;url=<?php echo xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['M_hash'], 'SSL', false); ?>" />
<style>
.pageHeading {
  font-family: Verdana, Arial, sans-serif;
  font-size: 20px;
  font-weight: bold;
  color: #9a9a9a;
}
.main {
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
  line-height: 1.5;
}
</style>

<p align="center" class="pageHeading"><?php echo STORE_NAME; ?></p>
<p class="main" align="center"><?php echo MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_UNSUCCESSFUL_TRANSACTION;?></p>

<form action="<?php echo xtc_href_link(FILENAME_CHECKOUT_PAYMENT, xtc_session_name() . '=' . $_POST['M_sid'] . '&hash=' . $_POST['M_hash'], 'SSL', false); ?>" method="post">
  <div align="center">
    <input name="submit" type="submit" value="<?php echo sprintf(MODULE_PAYMENT_WORLDPAY_JUNIOR_TEXT_CONTINUE_BUTTON, addslashes(STORE_NAME)); ?>" />
    </div>
</form>
<p align="center">&nbsp;</p>

<div align="center">
  <WPDISPLAY ITEM=banner>
  </div>
    <?php
    }
  }
?>