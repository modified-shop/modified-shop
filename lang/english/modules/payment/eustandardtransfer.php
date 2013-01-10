<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2006 XT-Commerce (eustandardtransfer.php 998 2005-07-07)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE', 'EU-Standard Bank Transfer');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION', 
          '<br />The cheapest and most simple payment method within the EU is the EU-Standard Bank Transfer using IBAN and BIC.' .
          '<br />Please use the details on the right to transfer your total order value.<br />' .
          '<br /><br />As soon as we receive your payment in the bank account mentioned above, we will ship your order.<br />');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION2', 
          '<br />Bank Name: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM .
          '<br />Branch: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH .
          '<br />Account Name: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM .
          '<br />Account No.: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM .
          '<br />IBAN:: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN .
          '<br />BIC/SWIFT: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC .
//        '<br />Sort Code: ' . MODULE_PAYMENT_EUSTANDARDTRANSFER_SORTCODE .
          '');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Please transfer the invoice total amount to our bank account.<br />You will receive the account data in the next step and by e-mail when your order has been confirmed.');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_TITLE','Allow Bank Transfer Payment');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_DESC','Do you want to accept bank transfer order payments?');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH_TITLE','Branch Location');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH_DESC','The brach where you have your account.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM_TITLE','Bank Name');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM_DESC','Your full bank name');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM_TITLE','Bank Account Name');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM_DESC','The name associated with the account.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM_TITLE','Bank Account No.');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM_DESC','Your account number.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN_TITLE','Bank Account IBAN');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN_DESC','International account id.<br />(ask your bank if you don\'t know it)');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC_TITLE','Bank Bic');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC_DESC','International bank id.<br />(ask your bank if you don\'t know it)');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER_TITLE','Module Sort order of display.');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SORT_ORDER_DESC','Sort order of display. Lowest is displayed first.');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_TITLE' , 'Allowed zones');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

  // BOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING_TITLE', 'Exclusion in case of shipping');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_NEG_SHIPPING_DESC', 'deactivate this payment if one of these shippingtypes are selected (list separated by commas)');
  // EOF - Hendrik - 2010-07-15 - exlusion config for shipping modules
?>