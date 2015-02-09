<?php
/* -----------------------------------------------------------------------------------------
   $Id: easybill.php 4242 2013-01-11 13:56:09Z gtb-modified $

   Modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 Modified
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
define('MODULE_EASYBILL_TEXT_DESCRIPTION', 'Write invoice online');
define('MODULE_EASYBILL_TEXT_TITLE', 'easyBill - Write invoice online');
define('MODULE_EASYBILL_STATUS_DESC','Module status');
define('MODULE_EASYBILL_STATUS_TITLE','Status');
define('MODULE_EASYBILL_API_TITLE','API Key');
define('MODULE_EASYBILL_API_DESC','Key has to be created in easybill admin.');
define('MODULE_EASYBILL_BILLINGID_TITLE','Manage Invoicenumber');
define('MODULE_EASYBILL_BILLINGID_DESC','The settings for easy bill must be made in the provider menu of easy bill.<br/><br/>If the invoice numbers are managed by the shop, then the unique sequential invoice number must be ensured.');
define('MODULE_EASYBILL_BILLCREATE_TITLE','Create Invoice');
define('MODULE_EASYBILL_BILLCREATE_DESC','Create Invoice automatically in checkout ?');
define('MODULE_EASYBILL_BILLSAVE_TITLE','Save Invoice');
define('MODULE_EASYBILL_BILLSAVE_DESC','Save invoices local or at easybill ?');
define('MODULE_EASYBILL_PAYMENT_TITLE','Payment');
define('MODULE_EASYBILL_PAYMENT_DESC','Which payment methods are expected to generate no automatic bill? List separated by a semicolon (;)');
define('MODULE_EASYBILL_DO_STATUS_CHANGE_TITLE','Change order status');
define('MODULE_EASYBILL_DO_STATUS_CHANGE_DESC','Change order status after invoicing ?');
define('MODULE_EASYBILL_DO_AUTO_PAYMENT_TITLE','Mark invoice as paid');
define('MODULE_EASYBILL_DO_AUTO_PAYMENT_DESC','Mark invoice as paid after invoicing ?');
define('MODULE_EASYBILL_NO_AUTO_PAYMENT_TITLE','Mark invoice as paid - Exclusion');
define('MODULE_EASYBILL_NO_AUTO_PAYMENT_DESC','Which payment methods are not to be placed on automatic paid? List separated by a semicolon (;)');
define('MODULE_EASYBILL_STATUS_CHANGE_TITLE','Order status');
define('MODULE_EASYBILL_STATUS_CHANGE_DESC','Setting of final billing to this status.');
define('MODULE_EASYBILL_STANDARD_TAX_CLASS_TITLE','<hr /><b>Tax class:</b>');
define('MODULE_EASYBILL_STANDARD_TAX_CLASS_DESC','Please provide a standard, if no tax class can be determined.');
  
?>