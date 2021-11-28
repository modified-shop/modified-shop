<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_DHL_TEXT_TITLE', 'DHL Connection');
  define('MODULE_DHL_TEXT_DESCRIPTION', 'Print DHL Labels.');

  define('MODULE_DHL_STATUS_TITLE', 'Status');
  define('MODULE_DHL_STATUS_DESC', 'Module activate');
  define('MODULE_DHL_USER_TITLE', '<hr noshade>User');
  define('MODULE_DHL_USER_DESC', 'User from DHL Business Customer Portal');
  define('MODULE_DHL_SIGNATURE_TITLE', 'Password');
  define('MODULE_DHL_SIGNATURE_DESC', 'Password from DHL Business Customer Portal');
  define('MODULE_DHL_EKP_TITLE', 'EKP');
  define('MODULE_DHL_EKP_DESC', 'DHL Customer number');
  define('MODULE_DHL_ACCOUNT_TITLE', 'Account');
  define('MODULE_DHL_ACCOUNT_DESC', 'Account ID, Format ISO2:ID separated by comma (standard WORLD:01).<br>If "Warenpost" has a different ID, add PK (parcel) or WP (Warenpost). Example: WORLD:01PK,WORLD:02WP');
  
  define('MODULE_DHL_NOTIFICATION_TITLE', '<hr noshade>Notification');
  define('MODULE_DHL_NOTIFICATION_DESC', 'Set Notification via DHL preselected as default<br>The customer will be notified by DHL via email about the shipment.<br><b>Note:</b> for this purpose, a declaration of consent to the disclosure of the e-mail address must be available from the customer.');
  define('MODULE_DHL_STATUS_UPDATE_TITLE', 'Notification &amp; Update status');
  define('MODULE_DHL_STATUS_UPDATE_DESC', 'The customer will be notified by mail including tracking information and the order will be set to this status.');
  define('MODULE_DHL_CODING_TITLE', 'Coding');
  define('MODULE_DHL_CODING_DESC', 'Set Coding preselected as default');
  define('MODULE_DHL_PRODUCT_TITLE', 'Product');
  define('MODULE_DHL_PRODUCT_DESC', 'Which product should be preselected as default?');
  define('MODULE_DHL_DISPLAY_LABEL_TITLE', 'Display Label');
  define('MODULE_DHL_DISPLAY_LABEL_DESC', 'Should the DHL Label be displayed (popup) after generation?');
  define('MODULE_DHL_RETOURE_TITLE', 'Returns Label');
  define('MODULE_DHL_RETOURE_DESC', 'Should a Return Label also be generated?');
  define('MODULE_DHL_PERSONAL_TITLE', 'Personally');
  define('MODULE_DHL_PERSONAL_DESC', 'Set Personally preselected as default');
  define('MODULE_DHL_BULKY_TITLE', 'Bulky goods');
  define('MODULE_DHL_BULKY_DESC', 'Set Bulky goods preselected as default');
  define('MODULE_DHL_NO_NEIGHBOUR_TITLE', 'No Neighbour Delivery');
  define('MODULE_DHL_NO_NEIGHBOUR_DESC', 'Set No Neighbour Delivery preselected as default');
  define('MODULE_DHL_PARCEL_OUTLET_TITLE', 'Parcel Outlet Routing');
  define('MODULE_DHL_PARCEL_OUTLET_DESC', 'Set Parcel Outlet Routing preselected as default');
  define('MODULE_DHL_AVS_TITLE', 'Visual Age Check');
  define('MODULE_DHL_AVS_DESC', 'Set Visual Age Check preselected as default (0 is disabled)');
  define('MODULE_DHL_IDENT_TITLE', 'Ident Check');
  define('MODULE_DHL_IDENT_DESC', 'Set Ident Check preselected as default (0 is disabled)');
  define('MODULE_DHL_PREMIUM_TITLE', 'Premium');
  define('MODULE_DHL_PREMIUM_DESC', 'Set Premium preselected as default');

  define('MODULE_DHL_COMPANY_TITLE', '<hr noshade>Customer details<br/>');
  define('MODULE_DHL_COMPANY_DESC', 'Company:');
  define('MODULE_DHL_FIRSTNAME_TITLE', '');
  define('MODULE_DHL_FIRSTNAME_DESC', 'Firstname:');
  define('MODULE_DHL_LASTNAME_TITLE', '');
  define('MODULE_DHL_LASTNAME_DESC', 'Lastname:');
  define('MODULE_DHL_ADDRESS_TITLE', '');
  define('MODULE_DHL_ADDRESS_DESC', 'Address:');
  define('MODULE_DHL_POSTCODE_TITLE', '');
  define('MODULE_DHL_POSTCODE_DESC', 'Postcode:');
  define('MODULE_DHL_CITY_TITLE', '');
  define('MODULE_DHL_CITY_DESC', 'City:');
  define('MODULE_DHL_TELEPHONE_TITLE', '');
  define('MODULE_DHL_TELEPHONE_DESC', 'Phone:');
  
  define('MODULE_DHL_ACCOUNT_OWNER_TITLE', '<hr noshade>Bank data<br/>');
  define('MODULE_DHL_ACCOUNT_OWNER_DESC', 'Account holder:');
  define('MODULE_DHL_ACCOUNT_NUMBER_TITLE', '');
  define('MODULE_DHL_ACCOUNT_NUMBER_DESC', 'Kontonummer:');
  define('MODULE_DHL_BANK_CODE_TITLE', '');
  define('MODULE_DHL_BANK_CODE_DESC', 'Account number:');
  define('MODULE_DHL_BANK_NAME_TITLE', '');
  define('MODULE_DHL_BANK_NAME_DESC', 'Bank name:');
  define('MODULE_DHL_IBAN_TITLE', '');
  define('MODULE_DHL_IBAN_DESC', 'IBAN:');
  define('MODULE_DHL_BIC_TITLE', '');
  define('MODULE_DHL_BIC_DESC', 'BIC:');
