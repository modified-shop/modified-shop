<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

chdir('../../');
include('includes/application_top.php');


// include needed classes
require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPayment.php');                                      


if (isset($_GET['oID']) 
    && is_numeric($_GET['oID']) 
    && isset($_GET['key']) 
    && strlen($_GET['key']) == '32'
    ) 
{

  // include needed function
  require_once(DIR_FS_INC.'set_customers_status_by_id.inc.php');

  // include needed classes
  require_once (DIR_WS_CLASSES . 'order.php');

  $order = new order((int)$_GET['oID']);
  $hash = md5($order->customer['email_address']);

  if ($_GET['key'] == $hash) {

    if (!isset($_SESSION['customer_id'])) {
      set_customers_status_by_id($order->info['status']);
    }

    $paypal = new PayPalPayment('paypalpluslink');
    include_once(DIR_WS_LANGUAGES . $order->info['language'] . '/modules/payment/paypalpluslink.php');

		// confirmed
		if (isset($_GET['PayerID']) && $_GET['PayerID'] != '' 
		    && isset($_GET['token']) && $_GET['token'] != '' 
		    && isset($_GET['paymentId']) && $_GET['paymentId'] != '' 
		    && $_GET['paymentId'] == $_SESSION['paypal']['paymentId']		
		    ) 
		{
		  $_SESSION['paypal']['PayerID'] = $_GET['PayerID'];
		  $insert_id = (int)$_GET['oID'];
      $paypal->complete_cart();
      
      if (isset($_SESSION['customer_id'])) {
        $messageStack->add_session('paypalpluslink', MODULE_PAYMENT_PAYPALPLUSLINK_TEXT_COMPLETED);
        xtc_redirect(xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'info=1&order_id='.(int)$_GET['oID'], 'SSL'));
      } else {
        $messageStack->add_session('logoff', MODULE_PAYMENT_PAYPALPLUSLINK_TEXT_COMPLETED);
        xtc_redirect(xtc_href_link(FILENAME_LOGOFF, 'info=1', 'SSL'));
      }
    } else {
      $approval = $paypal->payment_redirect(false, true, true);

      // create smarty elements
      $smarty = new Smarty;

      // include boxes
      require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
    
      $breadcrumb->add(NAVBAR_TITLE_2_CHECKOUT_CONFIRMATION);

      require (DIR_WS_INCLUDES.'header.php');

      $javascript = '<script type="application/javascript">
      var ppp = PAYPAL.apps.PPP({	
        "approvalUrl": "'.$approval.'",
        "placeholder": "ppplus",
        "mode": "'.$paypal->get_config('PAYPAL_MODE').'",
        "language": "'.$_SESSION['language_code'].'_'.$order->billing['country_iso_2'].'",
        "country": "'.$order->billing['country_iso_2'].'",
        "buttonLocation": "outside",
        "preselection": "paypal",
        "useraction": "continue",
        "showLoadingIndicator": "true",
        "showPuiOnSandbox": "true"
      });
      </script>'."\n";
      $smarty->assign('javascript', $javascript);

      if (isset($_GET['payment_error'])) {
        $error = $paypal->get_error();
        $smarty->assign('error',  $error['error']);
      }

      $cancel_link = xtc_href_link(FILENAME_LOGOFF, '', 'SSL');
      if (isset($_SESSION['customer_id'])) {
        $cancel_link = xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.(int)$_GET['oID'], 'SSL');
      }
      $smarty->assign('BUTTON_BACK', '<a href="'.$cancel_link.'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
      $smarty->assign('BUTTON_CONTINUE', '<a href="#" onclick="ppp.doCheckout(); return false;">'.xtc_image_button('button_confirm.gif', IMAGE_BUTTON_CONFIRM_ORDER).'</a>');
    
      $main_content = $smarty->fetch(DIR_FS_EXTERNAL.'paypal/templates/ppp.html');
    
      $smarty->assign('main_content', $main_content);
      $smarty->assign('language', $_SESSION['language']);

      $smarty->caching = 0;
      if (!defined('RM'))
        //$smarty->load_filter('output', 'note');
      $smarty->display(CURRENT_TEMPLATE.'/index.html');
    }
  } else {
    die('Direct Access to this location is not allowed.');
  }
} else {
  die('Direct Access to this location is not allowed.');
}

include ('includes/application_bottom.php'); 
?>