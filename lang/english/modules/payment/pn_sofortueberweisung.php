<?php
/**
 * @version sofortueberweisung.de 4.0 - $Date: 2010-09-09 17:18:09 +0200 (Do, 09 Sep 2010) $
 * @author Payment Network AG (integration@payment-network.com)
 * @link http://www.payment-network.com/
 *
 * @copyright 2006 - 2007 Henri Schmidhuber
 * @link http://www.in-solution.de
 *
 * @link http://www.xt-commerce.com
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 *
 ***********************************************************************************
 * this file contains code based on:
 * (c) 2000 - 2001 The Exchange Project
 * (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions
 * (c) 2003	 nextcommerce (account_history_info.php,v 1.17 2003/08/17); www.nextcommerce.org
 * (c) 2003 - 2006 XT-Commerce
 * Released under the GNU General Public License
 ***********************************************************************************
 *
 * $Id: pn_sofortueberweisung.php 304 2010-09-09 15:18:09Z poser $
 *
 */

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_TITLE', 'DIRECTebanking.com');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_TITLE', 'DIRECTebanking.com with customer protection');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION', '<div align="center">' . (MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS != 'True' ? '<a class="button" href=' . xtc_href_link(FILENAME_MODULES, 'set=payment&module=pn_sofortueberweisung&action=install&autoinstall=1', 'SSL') . '>Autoinstaller (empfohlen)</a><br />' : ''). '<br /><b>DIRECTebanking.com</b></div>');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGE', '
     <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="bottom"><a href="https://www.sofortueberweisung.de/funktionsweise" target="_blank">{{image}}</a></td>
      </tr>
      <tr>
	 <td class="main"><br />%s</td>
      </tr>
    </table>');
	
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', 'DIRECTebanking.com is the free, ISO certified online payment system of the Payment Network AG. Your advantages: no additional registration, automatic debiting from your online bank account, highest safety standards and immediate shipping of stock goods. In order to pay with DIRECTebanking.com you need your eBanking login data, that is bank connection, account number, PIN and TAN.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_TEXT', 'DIRECTebanking.com is the free, ISO certified online payment system of the Payment Network AG. Your advantages: customer protection, no additional registration, automatic debiting from your online bank account, highest safety standards and immediate shipping of stock goods. In order to pay with DIRECTebanking.com you need your eBanking login data, that is bank connection, account number, PIN and TAN.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_DESCRIPTION_CHECKOUT_PAYMENT_IMAGEALT', 'DIRECTebanking.com is the free, ISO certified online payment system of the Payment Network AG. Your advantages: no additional registration, automatic debiting from your online bank account, highest safety standards and immediate shipping of stock goods. In order to pay with DIRECTebanking.com you need your eBanking login data, that is bank connection, account number, PIN and TAN.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED_TITLE' , 'Allowable zones');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ALLOWED_DESC' , 'Please enter <b>separately</b> the zones, which should be allowed for this module. (z.B. AT,DE (if empty, all zones are allowed))');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS_TITLE' , 'Activate DIRECTebanking.com direct module');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_STATUS_DESC' , 'Accept payment via prepayment with integrated DIRECTebanking.com?');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID_TITLE' , 'Customer ID');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_USER_ID_DESC' , 'Your Customer ID at DIRECTebanking.com');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID_TITLE' , 'Project ID');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID_DESC' , 'The responsible project ID at DIRECTebanking.com, to which the payment is affiliate');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD_TITLE' , 'Project password:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_PASSWORD_DESC' , 'The project password (at extended settings / passwords and hash algorithms)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD_TITLE', 'Notification password:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_NOTIF_PASSWORD_DESC', 'The notification password (extended settings / passwords and hash algorithms)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM_TITLE', 'Hashing algorithm:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM_DESC', 'The hashing algorithm (extended settings / passwords and hash algorithms)');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER_TITLE' , 'Sequence of display');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_SORT_ORDER_DESC' , 'Sequence of display. Lowest number is shown first.');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE_TITLE', 'Hashing algorithm: '.MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_HASH_ALGORITHM. '<br /><br />Payment zone');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE_TITLE' , 'Payment zone');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ZONE_DESC' , 'If a zone is selected, the payment method is only valid for this zone.');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID_TITLE' , 'Confirmed order status');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_ORDER_STATUS_ID_DESC' , 'Order status after entry of an order, for which DIRECTebanking.com forwarded a successful payment affirmation');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID_TITLE','Temporary order status');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TMP_STATUS_ID_DESC','Order status for transactions that are not completed yet');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID_TITLE','Unconfirmed order status');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_UNC_STATUS_ID_DESC','Order status after entry of an order, for which no or a faulty payment affirmation has been transfered');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID_TITLE', 'Order status upon receipt of the money');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_RECEIVED_STATUS_ID_DESC', 'Order status following the receipt of the money to your Sofort Bank account');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID_TITLE', 'Order status following no receipt of the money');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_LOSS_STATUS_ID_DESC', 'Order status when no money was credited to your Sofort Bank account');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS_TITLE', 'Customer protection acitvated');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_KS_STATUS_DESC', 'You need a bank account with <u><a href="http://www.sofort-bank.com" target="_blank">Sofort Bank</a></u> and customer protection must be enabled in your project settings. Please check with <u><a href="https://kaeuferschutz.sofort-bank.com/consumerProtections/index/'.MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_PROJECT_ID.'">this link</a></u> if customer protection is activated and enabled before enabling it here.');



define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1_TITLE','Reason line 1');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_REASON_1_DESC', 'In the reason line 1 the following options are available');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2_TITLE','Reason line 2');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_REASON_2_DESC', 'In the reason (max 27 characters) the following placeholders will be replaced:<br /> {{order_id}}<br />{{order_date}}<br />{{customer_id}}<br />{{customer_name}}<br />{{customer_company}}<br />{{customer_email}}');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE_TITLE','Payment selection graphic / text');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_IMAGE_DESC','Shown graphic / text in the selection of the payment options');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_HEADING', 'The following error has been announced by DIRECTebanking.com during the process:');
define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_ERROR_MESSAGE', 'Payment via DIRECTebanking.com is unfortunately not possible or has been cancelled by the customer. Please select another payment method.');

define('MODULE_PAYMENT_PN_SOFORTUEBERWEISUNG_TEXT_INFO', 'Pay easy with the certified and verified online banking system Sofort&uuml;berweisung');
?>
