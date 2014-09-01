<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_FS_EXTERNAL.'payone/classes/PayoneModified.php');
  require_once (DIR_FS_EXTERNAL.'payone/lang/'.$_SESSION['language'].'.php');
  $payone = new PayoneModified();
  
  if ($order->info['payment_method'] == 'payone_elv' && empty($_SESSION['payone_elv']['sepa_mandate_id']) == false) {
    if (isset($_SESSION['payone_elv']['sepa_download_pdf']) && $_SESSION['payone_elv']['sepa_download_pdf'] == 'true') {
      $mandate_file = $payone->retrieveSepaMandate($_SESSION['payone_elv']['sepa_mandate_id']);
      if ($mandate_file !== false) {
        if ($email_attachments != '') {
          $email_attachments .= ',';
        }
        $email_attachments .= DIR_FS_DOWNLOAD_PUBLIC.$mandate_file;
      }
    }
  }

  $clearing_data = $payone->getClearingData($order->info['order_id']);
  if ($clearing_data['bankaccountholder'] != '') {
    $payment_info_array = array(CLEARING_INTRO,
                                '',
                                CLEARING_ACCOUNTHOLDER . $clearing_data['bankaccountholder'],
                                CLEARING_BANK . $clearing_data['bankname'] . ' - ' . $clearing_data['bankcity'],
                                CLEARING_ACCOUNT . $clearing_data['bankaccount'],
                                CLEARING_BANKCODE . $clearing_data['bankcode'],
                                CLEARING_IBAN . $clearing_data['bankiban'],
                                CLEARING_BIC . $clearing_data['bankbic'],
                                CLEARING_TEXT . $clearing_data['orders_id'],
                                '',
                                CLEARING_OUTRO);
                                
    $smarty->assign('PAYMENT_INFO_HTML', implode('<br/>', $payment_info_array));
    $smarty->assign('PAYMENT_INFO_TXT', implode("\n", $payment_info_array));
  }

?>