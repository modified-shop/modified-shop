<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$sofort_code = 'SOFORT_IDEAL';

define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_TITLE', 'iDEAL <br /><img src="https://images.sofort.com/en/ideal/logo_90x30.png" alt="Logo iDEAL"/>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION', '<b>iDEAL</b><br />Once the customer has chosen this method of payment and his bank , he will be forwarded by the SOFORT GmbH on his bench. He spends his payment and then returned back to the shop system . Upon successful payment confirmation takes place through the SOFORT GmbH a callback to the shop system instead of that changes the payment status of the order accordingly changed.<br/>Powered by SOFORT GmbH');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_INFO', 'iDEAL.nl - online payments for e-commerce in the Netherlands. For payment by iDEAL you need an account with one of the banks listed. They will do the transfer directly to your bank. Services / goods are delivered or shipped when available IMMEDIATELY!');

// checkout
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
  <table border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td valign="bottom">
	      <a onclick="javascript:window.open(\'http://www.ideal.nl\',\'Information\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1020, height=900\');" style="float:left; width:auto;">{{image}}</a>
	    </td>
	  </tr>
	  <tr>
	    <td class="main">{{text}}</td>
	  </tr>
	</table>');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'iDeal');
define('MODULE_PAYMENT_'.$sofort_code.'_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', '
  <ul>
    <li>online payments for e-commerce in the Netherlands</li>
    <li>For payment by iDEAL you need an account with one of the banks listed</li>
    <li>They will do the transfer directly to your bank</li>
    <li>Services / goods are delivered or shipped when available IMMEDIATELY</li>
  </ul>');

define('MODULE_PAYMENT_'.$sofort_code.'_SELECTBOX', 'Please choose your Bank');

// admin
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_TITLE', 'activate iDeal Modul');
define('MODULE_PAYMENT_'.$sofort_code.'_STATUS_DESC', 'Activates/deactivates iDeal');

include(DIR_FS_CATALOG.'lang/english/modules/payment/sofort_payment.php');

?>