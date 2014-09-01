<?php
/*------------------------------------------------------------------------------
  $Id: cc.php 1136 2005-08-07 13:19:54Z mz $

  XTC-CC - Contribution for XT-Commerce http://www.xt-commerce.com
  modified by http://www.netz-designer.de

  Copyright (c) 2003 netz-designer
  -----------------------------------------------------------------------------
  based on:
  $Id: cc.php 1136 2005-08-07 13:19:54Z mz $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_CC_TEXT_TITLE', 'Credit Card');
define('MODULE_PAYMENT_CC_TEXT_DESCRIPTION', 'Credit Card Test Info:<br /><br />CC#: 4111111111111111<br />Expiry: Any');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_START', 'Credit Card Start Date:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_ISSUE', 'Credit Card Issue Number:');
define('MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV', '3 or 4 Digit Security Code:');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
define('MODULE_PAYMENT_CC_TEXT_ERROR', 'Credit Card Error!');
define('TEXT_CARD_NOT_ACZEPTED','Sorry, we do not accept <b>%s</b> cards, please use another card type!<br />We accept following credit cards: ');
define('MODULE_PAYMENT_CC_TEXT_JS_CC_CVV', 'The CVV number is a required field and must be included.\n Orders cannot be submitted without it.\n The CVV number is the final 3 or 4 (American Express) digits printed on the signature strip on the reverse of your card.');
define('MODULE_PAYMENT_CC_TEXT_CVV_LINK', '<u>[help?]</u>');
define('HEADING_CVV', 'Security Code Help Screen');
define('TEXT_CVV', '<table align="center" cellspacing="2" cellpadding="5" width="400"><tr><td><span class="tableHeading"><b>Visa, Mastercard, Discover 3 Digit Card Verification Number</b></span></td></tr><tr><td><span class="boxText">For your safety and security, we require that you enter your card\'s verification number. The verification number is a 3-digit number printed on the back of your card. It appears after and to the right of your card number\'s last four digits.</span></td></tr><tr><td align="center"><IMG src="images/cv_card.gif"></td></tr></table><hr /><table align="center" cellspacing="2" cellpadding="5" width="400"><tr><td><span class="main"><b>American Express 4 Digit Card Verification Number</b> </span></td></tr><tr><td><span class="boxText">For your safety and security, we require that you enter your card\'s verification number. The American Express verification number is a 4-digit number printed on the front of your card. It appears after and to the right of your card number.</span></td></tr><tr><td align="center"><IMG src="images/cv_amex_card.gif"></td></tr></table>');
define('TEXT_CLOSE_WINDOW', '<u>Close Window</u> [x]');
define('MODULE_PAYMENT_CC_ACCEPTED_CARDS','We accept following cards:');
define('MODULE_PAYMENT_CC_TEXT_INFO','');
define('MODULE_PAYMENT_CC_STATUS_TITLE', 'Enable Credit Card Module');
define('MODULE_PAYMENT_CC_STATUS_DESC', 'Do you want to accept card payments?');
define('MODULE_PAYMENT_CC_ALLOWED_TITLE' , 'Allowed zones');
define('MODULE_PAYMENT_CC_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');
define('CC_VAL_TITLE', 'Enable CC Validation');
define('CC_VAL_DESC', 'Do you want to enable CC validation and identify cards?');
define('CC_BLACK_TITLE', 'Enable CC Blacklist Check');
define('CC_BLACK_DESC', 'Do you want to enable CC blacklist check?');
define('CC_ENC_TITLE', 'Encrypt CC Info');
define('CC_ENC_DESC', 'Do you want to encypt cc info?');
define('MODULE_PAYMENT_CC_SORT_ORDER_TITLE', 'Sort order of display.');
define('MODULE_PAYMENT_CC_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_CC_ZONE_TITLE', 'Payment Zone');
define('MODULE_PAYMENT_CC_ZONE_DESC', 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_CC_ORDER_STATUS_ID_TITLE', 'Set Order Status');
define('MODULE_PAYMENT_CC_ORDER_STATUS_ID_DESC', 'Set the status of orders made with this payment module to this value');
define('USE_CC_CVV_TITLE', 'Collect CVV Number');
define('USE_CC_CVV_DESC', 'Do you want to collect CVV Number?');
define('USE_CC_ISS_TITLE', 'Collect Issue Number');
define('USE_CC_ISS_DESC', 'Do you want to collect Issue Number?');
define('USE_CC_START_TITLE', 'Collect Start Date');
define('USE_CC_START_DESC', 'Do you want to collect the Start Date?');
define('CC_CVV_MIN_LENGTH_TITLE', 'CVV Number Length');
define('CC_CVV_MIN_LENGTH_DESC', 'Define CVV length. The default is 3 and should not be changed unless the industry standard changes.');
define('MODULE_PAYMENT_CC_EMAIL_TITLE', 'Split Card E-Mail Address');
define('MODULE_PAYMENT_CC_EMAIL_DESC', 'If an E-Mail address is entered, the middle digits of the card number will be sent to the E-Mail address (the outside digits are stored in the database with the middle digits censored');
define('TEXT_CCVAL_ERROR_INVALID_DATE', 'The "valid to" date ist invalid.<br />Please correct your information.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'The "Credit card number", you entered, is invalid.<br />Please correct your information.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'The first 4 digits of your Credit Card are: %s<br />If this information is correct, your type of card is not accepted.<br />Please correct your information.');

define('MODULE_PAYMENT_CC_ACCEPT_DINERSCLUB_TITLE', 'Accept DINERS CLUB cards');
define('MODULE_PAYMENT_CC_ACCEPT_DINERSCLUB_DESC', 'Accept DINERS CLUB cards');
define('MODULE_PAYMENT_CC_ACCEPT_AMERICANEXPRESS_TITLE', 'Accept AMERICAN EXPRESS cards');
define('MODULE_PAYMENT_CC_ACCEPT_AMERICANEXPRESS_DESC', 'Accept AMERICAN EXPRESS cards');
define('MODULE_PAYMENT_CC_ACCEPT_CARTEBLANCHE_TITLE', 'Accept CARTE BLANCHE cards');
define('MODULE_PAYMENT_CC_ACCEPT_CARTEBLANCHE_DESC', 'Accept CARTE BLANCHE cards');
define('MODULE_PAYMENT_CC_ACCEPT_OZBANKCARD_TITLE', 'Accept AUSTRALIAN BANKCARD cards');
define('MODULE_PAYMENT_CC_ACCEPT_OZBANKCARD_DESC', 'Accept AUSTRALIAN BANKCARD cards');
define('MODULE_PAYMENT_CC_ACCEPT_DISCOVERNOVUS_TITLE', 'Accept DISCOVER/NOVUS cards');
define('MODULE_PAYMENT_CC_ACCEPT_DISCOVERNOVUS_DESC', 'Accept DISCOVER/NOVUS cards');
define('MODULE_PAYMENT_CC_ACCEPT_DELTA_TITLE', 'Accept DELTA cards');
define('MODULE_PAYMENT_CC_ACCEPT_DELTA_DESC', 'Accept DELTA cards');
define('MODULE_PAYMENT_CC_ACCEPT_ELECTRON_TITLE', 'Accept ELECTRON cards');
define('MODULE_PAYMENT_CC_ACCEPT_ELECTRON_DESC', 'Accept ELECTRON cards');
define('MODULE_PAYMENT_CC_ACCEPT_MASTERCARD_TITLE', 'Accept MASTERCARD cards');
define('MODULE_PAYMENT_CC_ACCEPT_MASTERCARD_DESC', 'Accept MASTERCARD cards');
define('MODULE_PAYMENT_CC_ACCEPT_SWITCH_TITLE', 'Accept SWITCH cards');
define('MODULE_PAYMENT_CC_ACCEPT_SWITCH_DESC', 'Accept SWITCH cards');
define('MODULE_PAYMENT_CC_ACCEPT_SOLO_TITLE', 'Accept SOLO cards');
define('MODULE_PAYMENT_CC_ACCEPT_SOLO_DESC', 'Accept SOLO cards');
define('MODULE_PAYMENT_CC_ACCEPT_JCB_TITLE', 'Accept JCB cards');
define('MODULE_PAYMENT_CC_ACCEPT_JCB_DESC', 'Accept JCB cards');
define('MODULE_PAYMENT_CC_ACCEPT_MAESTRO_TITLE', 'Accept MAESTRO cards');
define('MODULE_PAYMENT_CC_ACCEPT_MAESTRO_DESC', 'Accept MAESTRO cards');
define('MODULE_PAYMENT_CC_ACCEPT_VISA_TITLE', 'Accept VISA cards');
define('MODULE_PAYMENT_CC_ACCEPT_VISA_DESC', 'Accept VISA cards');
?>