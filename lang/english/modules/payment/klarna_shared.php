<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

$lang_array = array(
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_TITLE' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_DESCRIPTION' => 'Before you can set up Klarna Payments payment methods, it is necessary to open a merchant account with Klarna. You will then receive information and login details needed to set up the account. If you already have a Klarna customer number but it is not in the Kxxxxxx scheme, please send an e-mail to <a href="mailto:vertrieb@klarna.com">vertrieb@klarna.com</a>.<br /><br />
    <img src="../lang/english/admin/images/icon.gif" border="0" />
    <a href="https://www.klarna.com/uk/business/" target="_blank" style="text-decoration: underline; font-weight: bold;">Create Klarna account now.</a>
    <img src="images/icon_popup.gif" border="0" />',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_INFO' => '',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_TITLE' => 'Allowed zones',
  'MODULE_PAYMENT_'.$klarna_code.'_ALLOWED_DESC' => 'Please enter the zones <b>separately</b> which should be allowed to use this module (e.g. AT,DE (leave empty if you want to allow all zones))',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_TITLE' => 'Enable Module',
  'MODULE_PAYMENT_'.$klarna_code.'_STATUS_DESC' => 'Do you want to accept payments through this module?',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_TITLE' => 'Sort order',
  'MODULE_PAYMENT_'.$klarna_code.'_SORT_ORDER_DESC' => 'Sort order of display. Lowest is displayed first.',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_TITLE' => 'Payment zone',
  'MODULE_PAYMENT_'.$klarna_code.'_ZONE_DESC' => 'If a zone is chosen, the payment method will be valid for this zone only.',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_TITLE' => 'Set Order Status',
  'MODULE_PAYMENT_'.$klarna_code.'_ORDER_STATUS_ID_DESC' => 'Set the status of orders made with this payment module to this value',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_TITLE' => 'Activate',
  'MODULE_PAYMENT_'.$klarna_code.'_CAPTURE_DESC' => 'Shall the order be activated automatically?',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_HEADING' => 'Klarna',
  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_ERROR_MESSAGE' => 'The payment was cancelled.',

  'MODULE_PAYMENT_'.$klarna_code.'_TEXT_VERSION' => '<b>Module version</b><br/>',

  // how the customer paid inside Klarna, appended to the title of the order
  'MODULE_PAYMENT_KLARNA_METHOD_INVOICE' => 'Invoice',
  'MODULE_PAYMENT_KLARNA_METHOD_FINANCING' => 'Financing',
  'MODULE_PAYMENT_KLARNA_METHOD_DIRECT_DEBIT' => 'Direct debit',
  'MODULE_PAYMENT_KLARNA_METHOD_BANK_TRANSFER' => 'Bank transfer',
  'MODULE_PAYMENT_KLARNA_METHOD_CARD' => 'Card',

  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_TITLE' => 'Username',
  'MODULE_PAYMENT_KLARNA_MERCHANT_ID_DESC' => 'Klarna API Username',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_TITLE' => 'Password',
  'MODULE_PAYMENT_KLARNA_SHARED_SECRET_DESC' => 'Klarna API Password',
  'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID_TITLE' => 'Order status fraud review',
  'MODULE_PAYMENT_KLARNA_PENDING_STATUS_ID_DESC' => 'Set orders that Klarna is still reviewing for fraud to this status. The order is captured only after Klarna accepts it.',
  'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID_TITLE' => 'Order status rejection',
  'MODULE_PAYMENT_KLARNA_REJECTED_STATUS_ID_DESC' => 'Set orders that Klarna rejects after the review to this status.',
  'MODULE_PAYMENT_KLARNA_MODE_TITLE' => 'Mode',
  'MODULE_PAYMENT_KLARNA_MODE_DESC' => 'Klarna Mode',
  'MODULE_PAYMENT_KLARNA_TEXT' => 'Klarna',
  'MODULE_PAYMENT_KLARNA_CHECK_BUTTON' => 'Check Klarna',
  'MODULE_PAYMENT_KLARNA_CHECK_HEADING' => 'Klarna payment method categories',
  'MODULE_PAYMENT_KLARNA_CHECK_SCOPE' => 'The result applies to the store country %s, the currency %s, the test amount %s and the mode %s only.',
  'MODULE_PAYMENT_KLARNA_CHECK_COL_CATEGORY' => 'Category from Klarna',
  'MODULE_PAYMENT_KLARNA_CHECK_COL_USED' => 'Use in the checkout',
  'MODULE_PAYMENT_KLARNA_CHECK_USED_YES' => 'is used',
  'MODULE_PAYMENT_KLARNA_CHECK_USED_NO' => 'is not used',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_ACTIVE' => 'installed and active',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_INACTIVE' => 'installed, but not active',
  'MODULE_PAYMENT_KLARNA_CHECK_STATE_MISSING' => 'not installed',
  'MODULE_PAYMENT_KLARNA_CHECK_MODULE' => 'Module klarna: %s',
  'MODULE_PAYMENT_KLARNA_CHECK_CATEGORY' => 'The module uses the category &quot;%s&quot;. It takes &quot;klarna&quot; if Klarna returns that category, otherwise the first one returned.',
  'MODULE_PAYMENT_KLARNA_CHECK_NONE' => 'Klarna returns no payment method category. The module klarna does not show up in the checkout.',
  'MODULE_PAYMENT_KLARNA_CHECK_WARN_OLD' => '<b>Note:</b> old Klarna modules are active next to Klarna (%s). The checkout hides them while Klarna is active. You can deactivate or uninstall them.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_CREDENTIALS' => 'The user name or the password of the Klarna API is missing. Enter both in the module settings.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_COUNTRY' => 'The store country is not in the country list.',
  'MODULE_PAYMENT_KLARNA_CHECK_ERROR_API' => 'The check failed. Klarna reports: %s',

  'MODULE_PAYMENT_KLARNA_CHECK_WARN_PENDING' => '<b>Note:</b> The order status for the fraud review is 0. Orders Klarna still reviews then look like normal new orders. Set a status of its own.',
  'MODULE_PAYMENT_KLARNA_CHECK_WARN_REJECTED' => '<b>Note:</b> The order status for rejected orders is 0. A rejected order then keeps its status and only gets an entry in its history.',
  'MODULE_PAYMENT_KLARNA_CHECK_RULES_HEADING' => '<b>Note:</b> These rules name old Klarna modules, but not klarna. They do not apply to klarna. Add klarna as well:',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_GROUP' => 'Customer groups, not allowed payment methods: %1$s (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_CUSTOMERS' => 'Customers, unallowed payment modules: %1$s customers (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_DOWNLOAD' => 'Disallowed Download Payment Modules (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_GV' => 'Coupon, disallowed payment modules (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_SHIPPING' => 'Payment methods depending on shipping method, payment methods no. %1$s (%2$s)',
  'MODULE_PAYMENT_KLARNA_CHECK_RULE_FEE' => 'Payment type discount &amp; surcharge, payment type no. %1$s (%2$s)',

  // shown in the backup, restore and uninstall dialog of the module list
  'MODULE_PAYMENT_KLARNA_BACKUP_NOTE' => '<b>Note:</b> The backup also contains the credentials and the shared settings of all Klarna modules.',
  'MODULE_PAYMENT_KLARNA_RESTORE_NOTE' => '<b>Note:</b> The restore also resets the credentials and the shared settings of all Klarna modules to the state of the backup.',
  'MODULE_PAYMENT_KLARNA_REMOVE_NOTE' => '<b>Note:</b> Open Klarna orders need the order status and the capture setting of klarna, including the orders of old Klarna modules that are already uninstalled. Uninstall klarna only when no Klarna order is open any more.',
);
