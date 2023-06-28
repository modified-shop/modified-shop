<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  define('MODULE_DHL_BUSINESS_TEXT_TITLE', 'DHL Connection');
  define('MODULE_DHL_BUSINESS_TEXT_DESCRIPTION', 'Print DHL Labels.');

  define('MODULE_DHL_BUSINESS_STATUS_TITLE', 'Status');
  define('MODULE_DHL_BUSINESS_STATUS_DESC', 'Module activate');
  define('MODULE_DHL_BUSINESS_USER_TITLE', '<hr noshade>User');
  define('MODULE_DHL_BUSINESS_USER_DESC', 'User from DHL Business Customer Portal');
  define('MODULE_DHL_BUSINESS_SIGNATURE_TITLE', 'Password');
  define('MODULE_DHL_BUSINESS_SIGNATURE_DESC', 'Password from DHL Business Customer Portal');
  define('MODULE_DHL_BUSINESS_EKP_TITLE', 'EKP');
  define('MODULE_DHL_BUSINESS_EKP_DESC', 'DHL Customer number');
  define('MODULE_DHL_BUSINESS_ACCOUNT_TITLE', 'Account');
  define('MODULE_DHL_BUSINESS_ACCOUNT_DESC', 'Account ID, Format ISO2:ID separated by comma (standard WORLD:01).<br>If "Warenpost" has a different ID, add PK (parcel) or WP (Warenpost). Example: WORLD:01PK,WORLD:02WP');
  define('MODULE_DHL_BUSINESS_PREFIX_TITLE', 'Sender reference prefix');
  define('MODULE_DHL_BUSINESS_PREFIX_DESC', 'Enter a prefix for the sender reference. The order number will be added automatically.');
  define('MODULE_DHL_BUSINESS_WEIGHT_CN23_TITLE', 'Weight CN23');
  define('MODULE_DHL_BUSINESS_WEIGHT_CN23_DESC', 'Enter the product weight for the customs declaration if none is stored with the product.');
  
  define('MODULE_DHL_BUSINESS_NOTIFICATION_TITLE', '<hr noshade>Notification');
  define('MODULE_DHL_BUSINESS_NOTIFICATION_DESC', 'Set Notification via DHL preselected as default<br>The customer will be notified by DHL via email about the shipment.<br><b>Note:</b> for this purpose, a declaration of consent to the disclosure of the e-mail address must be available from the customer.');
  define('MODULE_DHL_BUSINESS_STATUS_UPDATE_TITLE', 'Notification &amp; Update status');
  define('MODULE_DHL_BUSINESS_STATUS_UPDATE_DESC', 'The customer will be notified by mail including tracking information and the order will be set to this status.');
  define('MODULE_DHL_BUSINESS_CODING_TITLE', 'Coding');
  define('MODULE_DHL_BUSINESS_CODING_DESC', 'Set Coding preselected as default');
  define('MODULE_DHL_BUSINESS_PRODUCT_TITLE', 'Product');
  define('MODULE_DHL_BUSINESS_PRODUCT_DESC', 'Which product should be preselected as default?');
  define('MODULE_DHL_BUSINESS_DISPLAY_LABEL_TITLE', 'Display Label');
  define('MODULE_DHL_BUSINESS_DISPLAY_LABEL_DESC', 'Should the DHL Label be displayed (popup) after generation?');
  define('MODULE_DHL_BUSINESS_RETOURE_TITLE', 'Returns Label');
  define('MODULE_DHL_BUSINESS_RETOURE_DESC', 'Should a Return Label also be generated?');
  define('MODULE_DHL_BUSINESS_PERSONAL_TITLE', 'Personally');
  define('MODULE_DHL_BUSINESS_PERSONAL_DESC', 'Set Personally preselected as default');
  define('MODULE_DHL_BUSINESS_BULKY_TITLE', 'Bulky goods');
  define('MODULE_DHL_BUSINESS_BULKY_DESC', 'Set Bulky goods preselected as default');
  define('MODULE_DHL_BUSINESS_NO_NEIGHBOUR_TITLE', 'No Neighbour Delivery');
  define('MODULE_DHL_BUSINESS_NO_NEIGHBOUR_DESC', 'Set No Neighbour Delivery preselected as default');
  define('MODULE_DHL_BUSINESS_PARCEL_OUTLET_TITLE', 'Parcel Outlet Routing');
  define('MODULE_DHL_BUSINESS_PARCEL_OUTLET_DESC', 'Set Parcel Outlet Routing preselected as default');
  define('MODULE_DHL_BUSINESS_AVS_TITLE', 'Visual Age Check');
  define('MODULE_DHL_BUSINESS_AVS_DESC', 'Set Visual Age Check preselected as default (0 is disabled)');
  define('MODULE_DHL_BUSINESS_IDENT_TITLE', 'Ident Check');
  define('MODULE_DHL_BUSINESS_IDENT_DESC', 'Set Ident Check preselected as default (0 is disabled)');
  define('MODULE_DHL_BUSINESS_PREMIUM_TITLE', 'Premium');
  define('MODULE_DHL_BUSINESS_PREMIUM_DESC', 'Set Premium preselected as default');
  define('MODULE_DHL_BUSINESS_ENDORSEMENT_TITLE', 'Endorsement');
  define('MODULE_DHL_BUSINESS_ENDORSEMENT_DESC', 'How should international parcels be handled if they cannot be delivered?');
  define('MODULE_DHL_BUSINESS_DUTYPAID_TITLE', 'Postal Delivered Duty Paid');
  define('MODULE_DHL_BUSINESS_DUTYPAID_DESC', 'Postal Delivery Duty Paid Deutsche Post and sender handle import duties instead of consignee');
  define('MODULE_DHL_BUSINESS_DROPPOINT_TITLE', 'Closest Droppoint');
  define('MODULE_DHL_BUSINESS_DROPPOINT_DESC', 'Closest Droppoint Delivery to the droppoint closest to the address of the recipient of the shipment');
  define('MODULE_DHL_BUSINESS_SIGNED_TITLE', 'Recipient Signature');
  define('MODULE_DHL_BUSINESS_SIGNED_DESC', 'Set delivery should be signed by the recipient instead of the DHL driver as default');

  define('MODULE_DHL_BUSINESS_COMPANY_TITLE', '<hr noshade>Customer details<br/>');
  define('MODULE_DHL_BUSINESS_COMPANY_DESC', 'Company:');
  define('MODULE_DHL_BUSINESS_FIRSTNAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_FIRSTNAME_DESC', 'Firstname:');
  define('MODULE_DHL_BUSINESS_LASTNAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_LASTNAME_DESC', 'Lastname:');
  define('MODULE_DHL_BUSINESS_ADDRESS_TITLE', '');
  define('MODULE_DHL_BUSINESS_ADDRESS_DESC', 'Address:');
  define('MODULE_DHL_BUSINESS_POSTCODE_TITLE', '');
  define('MODULE_DHL_BUSINESS_POSTCODE_DESC', 'Postcode:');
  define('MODULE_DHL_BUSINESS_CITY_TITLE', '');
  define('MODULE_DHL_BUSINESS_CITY_DESC', 'City:');
  define('MODULE_DHL_BUSINESS_TELEPHONE_TITLE', '');
  define('MODULE_DHL_BUSINESS_TELEPHONE_DESC', 'Phone:');
  
  define('MODULE_DHL_BUSINESS_ACCOUNT_OWNER_TITLE', '<hr noshade>Bank data<br/>');
  define('MODULE_DHL_BUSINESS_ACCOUNT_OWNER_DESC', 'Account holder:');
  define('MODULE_DHL_BUSINESS_ACCOUNT_NUMBER_TITLE', '');
  define('MODULE_DHL_BUSINESS_ACCOUNT_NUMBER_DESC', 'Kontonummer:');
  define('MODULE_DHL_BUSINESS_BANK_CODE_TITLE', '');
  define('MODULE_DHL_BUSINESS_BANK_CODE_DESC', 'Account number:');
  define('MODULE_DHL_BUSINESS_BANK_NAME_TITLE', '');
  define('MODULE_DHL_BUSINESS_BANK_NAME_DESC', 'Bank name:');
  define('MODULE_DHL_BUSINESS_IBAN_TITLE', '');
  define('MODULE_DHL_BUSINESS_IBAN_DESC', 'IBAN:');
  define('MODULE_DHL_BUSINESS_BIC_TITLE', '');
  define('MODULE_DHL_BUSINESS_BIC_DESC', 'BIC:');
