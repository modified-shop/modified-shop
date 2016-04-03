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
   (c) 2003 xtCommerce www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_TITLE', 'EU-Standard Bank Transfer');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION', 
          '<br />The cheapest and most simple payment method within the EU is the EU-Standard Bank Transfer using IBAN and BIC.' .
          '<br />Please use the following details to transfer your total order value:<br />' .
          '<br />Bank Name: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKNAM : '') .
          '<br />Branch: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BRANCH : '') .
          '<br />Account Name: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNAM : '') .
          '<br />Account No.: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCNUM : '') .
          '<br />IBAN: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_ACCIBAN : '') .
          '<br />BIC/SWIFT: ' . (defined('MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC') ? MODULE_PAYMENT_EUSTANDARDTRANSFER_BANKBIC : '') .
          '<br /><br />Your order will not be shipped until we receive your payment in the above account.<br />');

  if (MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS == 'True') {
    define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Please transfer the invoice total to our bank account. You will receive the account data in the last step of the checkout.');
  } else {
    define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','Please transfer the invoice total to our bank account. You will receive the account data by e-mail when your order has been confirmed.');
  }
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_TITLE','Allow Bank Transfer Payment');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_STATUS_DESC','Do you want to accept bank transfer order payments?');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_INFO','');
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

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE_TITLE' , 'Payment Zone');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ZONE_DESC' , 'If a zone is selected, only enable this payment method for that zone.');
  
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID_TITLE' , 'Set Order Status');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_ORDER_STATUS_ID_DESC' , 'Set the status of orders made with this payment module to this value');

  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS_TITLE' , 'Display bank data');
  define('MODULE_PAYMENT_EUSTANDARDTRANSFER_SUCCESS_DESC' , 'Display bank data on checkout success?');
?>