<?php
/* -----------------------------------------------------------------------------------------
   $Id: english.php 2721 2012-03-23 20:12:07Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(german.php,v 1.119 2003/05/19); www.oscommerce.com
   (c) 2003 nextcommerce (german.php,v 1.25 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
 *
 *  DATE / TIME
 *
 */

define('TITLE', STORE_NAME);
define('HEADER_TITLE_TOP', 'Main page');
define('HEADER_TITLE_CATALOG', 'Catalogue');
define('HTML_PARAMS','dir="ltr" xml:lang="en"');
@setlocale(LC_TIME, 'en_GB@euro', 'en_GB', 'en-GB', 'en', 'en_GB.ISO_8859-1', 'English','en_GB.ISO_8859-15');

//BOF - Dokuman - 2009-06-03 - correct english date format
define('DATE_FORMAT_SHORT', '%d/%m/%Y');  // this is used for strftime()
define('DATE_FORMAT_LONG', '%A %d %B, %Y'); // this is used for strftime()
define('DATE_FORMAT', 'd/m/Y');  // this is used for strftime()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' %H:%M:%S');
define('DOB_FORMAT_STRING', 'dd/mm/jjjj');
 
function xtc_date_raw($date, $reverse = false) {
  if ($reverse) {
    return substr($date, 3, 2) . substr($date, 0, 2) . substr($date, 6, 4);
  } else {
    return substr($date, 6, 4) . substr($date, 0, 2) . substr($date, 3, 2);
  }
}
//EOF - Dokuman - 2009-06-03 - correct english date format

// BOF - vr - 2009-12-11 - Added language dependent currency code
// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency when changing language, 
// instead of staying with the applications default currency
define('LANGUAGE_CURRENCY', 'EUR');
// EOF - vr - 2009-12-11 - Added language dependent currency code

define('MALE', 'Mr.');
define('FEMALE', 'Ms./Mrs.');

/*
 *
 *  BOXES
 *
 */

// text for gift voucher redeeming
define('IMAGE_REDEEM_GIFT','Redeem Gift Voucher!');

define('BOX_TITLE_STATISTICS','Statistics:');
define('BOX_ENTRY_CUSTOMERS','Customers:');
define('BOX_ENTRY_PRODUCTS','Products:');
define('BOX_ENTRY_REVIEWS','Reviews:');
define('TEXT_VALIDATING','Not validated:');

// manufacturer box text
define('BOX_MANUFACTURER_INFO_HOMEPAGE', '%s Homepage');
define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'More products');

define('BOX_HEADING_ADD_PRODUCT_ID','Add to cart');
  
define('BOX_LOGINBOX_STATUS','Customer group:');     
define('BOX_LOGINBOX_DISCOUNT','Product discount');
define('BOX_LOGINBOX_DISCOUNT_TEXT','Discount');
define('BOX_LOGINBOX_DISCOUNT_OT','');

// reviews box text in includes/boxes/reviews.php
define('BOX_REVIEWS_WRITE_REVIEW', 'Review this product!');
define('BOX_REVIEWS_NO_WRITE_REVIEW', 'No review possible.');
define('BOX_REVIEWS_TEXT_OF_5_STARS', '%s of 5 stars!');

// pull down default text
define('PULL_DOWN_DEFAULT', 'Please choose');

// javascript messages
define('JS_ERROR', 'Missing necessary information!\nPlease fill in correctly.\n\n');

define('JS_REVIEW_TEXT', '* The text must consist of at least ' . REVIEW_TEXT_MIN_LENGTH . ' characters..\n');
define('JS_REVIEW_RATING', '* Enter your review.\n');
define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Please choose a method of payment for your order.\n');
define('JS_ERROR_SUBMITTED', 'This page has already been confirmed. Please click OK and wait until the process has finished.');
define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Please choose a method of payment for your order.');

/*
 *
 * ACCOUNT FORMS
 *
 */

define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_GENDER_ERROR', 'Please select your salutation.');
define('ENTRY_GENDER_TEXT', '*');
define('ENTRY_FIRST_NAME_ERROR', 'Your first name must consist of at least  ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' characters.');
define('ENTRY_FIRST_NAME_TEXT', '*');
define('ENTRY_LAST_NAME_ERROR', 'Your e-mail address must consist of at least ' . ENTRY_LAST_NAME_MIN_LENGTH . ' characters.');
define('ENTRY_LAST_NAME_TEXT', '*');
define('ENTRY_DATE_OF_BIRTH_ERROR', 'Your date of birth needs to be entered in the following form DD/MM/YYYY (e.g. 05/21/1970) '); //Dokuman - 2009-06-03 - correct english date format
define('ENTRY_DATE_OF_BIRTH_TEXT', '* (e.g. 05/21/1970)'); //Dokuman - 2009-06-03 - correct english date format
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Your e-mail address must consist of at least  ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' characters.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'The e-mail address you entered is incorrect - please correct it');
define('ENTRY_EMAIL_ERROR_NOT_MATCHING', 'Your entered e-mail addresses do not match.'); // Hetfield - 2009-08-15 - confirm e-mail at registration
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'The e-mail address you entered already exists in our database - please correct it');
define('ENTRY_EMAIL_ADDRESS_TEXT', '*');
define('ENTRY_STREET_ADDRESS_ERROR', 'Street/No. must consist of at least ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' characters.');
define('ENTRY_STREET_ADDRESS_TEXT', '*');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POST_CODE_ERROR', 'Your postcode must consist of at least ' . ENTRY_POSTCODE_MIN_LENGTH . ' characters.');
define('ENTRY_POST_CODE_TEXT', '*');
define('ENTRY_CITY_ERROR', 'City must consist of at least ' . ENTRY_CITY_MIN_LENGTH . ' characters.');
define('ENTRY_CITY_TEXT', '*');
define('ENTRY_STATE_ERROR', 'Your district must consist of at least ' . ENTRY_STATE_MIN_LENGTH . ' characters.');
define('ENTRY_STATE_ERROR_SELECT', 'Please choose your district from the list.');
define('ENTRY_STATE_TEXT', '*');
define('ENTRY_COUNTRY_ERROR', 'Please choose your country.');
define('ENTRY_COUNTRY_TEXT', '*');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Your phone number must consist of at least ' . ENTRY_TELEPHONE_MIN_LENGTH . ' characters.');
define('ENTRY_TELEPHONE_NUMBER_TEXT', '*');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_PASSWORD_ERROR', 'Your password must consist of at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Your passwords do not match.');
define('ENTRY_PASSWORD_TEXT', '*');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_ERROR','Your password must consist of at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_NEW_TEXT', '*');
define('ENTRY_PASSWORD_NEW_ERROR', 'Your new password must consist of at least ' . ENTRY_PASSWORD_MIN_LENGTH . ' characters.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'Your passwords do not match.');

/*
 *
 *  RESULT PAGES
 *
 */

define('TEXT_RESULT_PAGE', 'Sites:');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'Show <strong>%d</strong> to <strong>%d</strong> (of in total <strong>%d</strong> products)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'Show <strong>%d</strong> to <strong>%d</strong> (of in total <strong>%d</strong> orders)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'Show <strong>%d</strong> to <strong>%d</strong> (of in total <strong>%d</strong> reviews)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW', 'Show <strong>%d</strong> to <strong>%d</strong> (of in total <strong>%d</strong> new products)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'Show <strong>%d</strong> to <strong>%d</strong> (of in total <strong>%d</strong> special offers)');

/*
 *
 * SITE NAVIGATION
 *
 */

define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'previous page');
define('PREVNEXT_TITLE_NEXT_PAGE', 'next page');
define('PREVNEXT_TITLE_PAGE_NO', 'page %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Previous %d pages');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Next %d pages');

/*
 *
 * PRODUCT NAVIGATION
 *
 */

define('PREVNEXT_BUTTON_PREV', '[&lt;&lt;&nbsp;previous]');
define('PREVNEXT_BUTTON_NEXT', '[next&nbsp;&gt;&gt;]');

/*
 *
 * IMAGE BUTTONS
 *
 */

define('IMAGE_BUTTON_ADD_ADDRESS', 'New address');
define('IMAGE_BUTTON_BACK', 'Back');
define('IMAGE_BUTTON_CHANGE_ADDRESS', 'Change address');
define('IMAGE_BUTTON_CHECKOUT', 'Checkout');
define('IMAGE_BUTTON_CONFIRM_ORDER', 'Buy');
define('IMAGE_BUTTON_CONTINUE', 'Next');
define('IMAGE_BUTTON_DELETE', 'Delete');
define('IMAGE_BUTTON_LOGIN', 'Login');
define('IMAGE_BUTTON_IN_CART', 'Add to cart');
define('IMAGE_BUTTON_SEARCH', 'Search');
define('IMAGE_BUTTON_UPDATE', 'Update');
define('IMAGE_BUTTON_UPDATE_CART', 'Update shopping cart');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Write evaluation');
define('IMAGE_BUTTON_ADMIN', 'Admin');
define('IMAGE_BUTTON_PRODUCT_EDIT', 'Edit product');
// BOF - vr - 2010-02-20 removed double definition 
// define('IMAGE_BUTTON_LOGIN', 'Login');
// EOF - vr - 2010-02-20 removed double definition 
define('IMAGE_BUTTON_SEND', 'Send'); //DokuMan - 2010-03-15 - Added button description for contact form
define('IMAGE_BUTTON_CONTINUE_SHOPPING', 'Continue shopping'); //Hendrik - 2010-11-12 - used in default template ...shopping_cart.html

define('SMALL_IMAGE_BUTTON_DELETE', 'Delete');
define('SMALL_IMAGE_BUTTON_EDIT', 'Edit');
define('SMALL_IMAGE_BUTTON_VIEW', 'View');

define('ICON_ARROW_RIGHT', 'Show more');
define('ICON_CART', 'Add to cart');
define('ICON_SUCCESS', 'Success');
define('ICON_WARNING', 'Warning');
define('ICON_ERROR', 'Error');

define('TEXT_PRINT', 'print'); //DokuMan - 2009-05-26 - Added description for 'account_history_info.php'

/*
 *
 *  GREETINGS
 *
 */

define('TEXT_GREETING_PERSONAL', 'Nice to see you again <span class="greetUser">%s!</span> Would you like to view our <a style="text-decoration:underline;" href="%s">new products</a> ?');
define('TEXT_GREETING_PERSONAL_RELOGON', '<small>If you are not %s , please  <a style="text-decoration:underline;" href="%s">login</a>  with your account.</small>');
define('TEXT_GREETING_GUEST', 'Welcome  <span class="greetUser">visitor!</span> Would you like to <a style="text-decoration:underline;" href="%s">login</a>? Or would you like to create a new <a style="text-decoration:underline;" href="%s">account</a> ?');

define('TEXT_SORT_PRODUCTS', 'Sorting of the items is ');
define('TEXT_DESCENDINGLY', 'descending');
define('TEXT_ASCENDINGLY', 'ascending');
define('TEXT_BY', ' after ');

define('TEXT_OF_5_STARS', '%s of 5 Stars!');
define('TEXT_REVIEW_BY', 'from %s');
define('TEXT_REVIEW_WORD_COUNT', '%s words');
define('TEXT_REVIEW_RATING', 'Review: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Date added: %s');
define('TEXT_NO_REVIEWS', 'There are no reviews yet.');
define('TEXT_NO_NEW_PRODUCTS', 'There are no new products for the last '.MAX_DISPLAY_NEW_PRODUCTS_DAYS.' days. Instead of that we will show you the 10 latest arrived products.'); // Tomcraft - 2009-08-11 - changed text for new products_new function
define('TEXT_UNKNOWN_TAX_RATE', 'Unknown tax rate');

/*
 *
 * WARNINGS
 *
 */

define('WARNING_INSTALL_DIRECTORY_EXISTS', 'Warning: The installation directory is still available on: %s. Please delete this directory for security reasons!');
define('WARNING_CONFIG_FILE_WRITEABLE', 'Warning: The modified eCommerce Shopsoftware is able to write to the configuration directory: %s. That represents a possible safety hazard - please correct the user access rights for this directory!');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Warning: Directory for sesssions doesn&acute;t exist: ' . xtc_session_save_path() . '. Sessions will not work until this directory has been created!');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Warning: The modified eCommerce Shopsoftware is not able to write into the session directory: ' . xtc_session_save_path() . '. Sessions will not work until the user access rights for this directory have been changed!');
define('WARNING_SESSION_AUTO_START', 'Warning: session.auto_start is activated (enabled) - Please deactivate (disable) this PHP feature in php.ini and restart your web server!');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Warning: Directory for article download does not exist: ' . DIR_FS_DOWNLOAD . '. This feature will not work until this directory has been created!');

define('SUCCESS_ACCOUNT_UPDATED', 'Your account has been updated successfully.');
define('SUCCESS_PASSWORD_UPDATED', 'Your password has been changed successfully!');
define('ERROR_CURRENT_PASSWORD_NOT_MATCHING', 'The entered password does not match with the stored password. Please try again.');
define('TEXT_MAXIMUM_ENTRIES', '<font color="#ff0000"><strong>Reference:</strong></font> You are able to choose out of %s entries in you address book!');
define('SUCCESS_ADDRESS_BOOK_ENTRY_DELETED', 'The selected entry has been deleted successfully.');
define('SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED', 'Your address book has been updated sucessfully!');
define('WARNING_PRIMARY_ADDRESS_DELETION', 'The standard postal address can not be deleted. Please create another address and define it as standard postal address first. Then this entry can be deleted.');
define('ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY', 'This address book entry is not available.');
define('ERROR_ADDRESS_BOOK_FULL', 'Your adressbook is full. In order to add new addresses, please erase previous ones first.');
define('ERROR_CHECKOUT_SHIPPING_NO_METHOD', 'No shipping method selected.');
define('ERROR_CHECKOUT_SHIPPING_NO_MODULE', 'No shipping method available.');

//  conditions check

define('ERROR_CONDITIONS_NOT_ACCEPTED', 'Please accept our terms and conditions to proceed with your order.');

define('SUB_TITLE_OT_DISCOUNT','Discount:');

define('TAX_ADD_TAX','incl. ');
define('TAX_NO_TAX','plus ');

define('NOT_ALLOWED_TO_SEE_PRICES','You do not have the permission to see the prices ');
define('NOT_ALLOWED_TO_SEE_PRICES_TEXT','You do not have the permission to see the prices, please create an account.');

define('TEXT_DOWNLOAD','Download');
define('TEXT_VIEW','View');

define('TEXT_BUY', '1 x \'');
define('TEXT_NOW', '\' order');
define('TEXT_GUEST','Guest');
define('TEXT_SEARCH_ENGINE_AGENT','Search engine');

/*
 *
 * ADVANCED SEARCH
 *
 */

define('TEXT_ALL_CATEGORIES', 'All categories');
define('TEXT_ALL_MANUFACTURERS', 'All manufacturers');
define('JS_AT_LEAST_ONE_INPUT', '* One of the following fields must be filled out:\n    Keywords\n    Date added from\n    Date added to\n    Price over\n    Price up to\n');
define('AT_LEAST_ONE_INPUT', 'One of the following fields must be filled out:<br />keywords consisting at least 3 characters<br />Price over<br />Price up to<br />');
define('TEXT_SEARCH_TERM','Your search for: ');
define('JS_INVALID_FROM_DATE', '* Invalid from date\n');
define('JS_INVALID_TO_DATE', '* Invalid up to Date\n');
define('JS_TO_DATE_LESS_THAN_FROM_DATE', '* The from date must be larger or same size as up to now\n');
define('JS_PRICE_FROM_MUST_BE_NUM', '* Price over, must be a number\n');
define('JS_PRICE_TO_MUST_BE_NUM', '* Price up to, must be a number\n');
define('JS_PRICE_TO_LESS_THAN_PRICE_FROM', '* Price up to must be larger or same size as Price over.\n');
define('JS_INVALID_KEYWORDS', '* Invalid search key\n');
define('TEXT_LOGIN_ERROR', '<font color="#ff0000"><strong>ERROR:</strong></font> The entered \'e-mail address\' and/or the \'password\' do not match.');
define('TEXT_NO_EMAIL_ADDRESS_FOUND', '<font color="#ff0000"><strong>WARNING:</strong></font> The e-mail address entered is not registered. Please try again.');
define('TEXT_PASSWORD_SENT', 'A new password was sent by e-mail.');
define('TEXT_PRODUCT_NOT_FOUND', 'Product not found!');
define('TEXT_MORE_INFORMATION', 'For further information, please visit the <a style="text-decoration:underline;" href="%s" onclick="window.open(this.href); return false;">homepage</a> of this product.');
define('TEXT_DATE_ADDED', 'This Product was added to our catalogue on %s.');
define('TEXT_DATE_AVAILABLE', '<font color="#ff0000">This Product is expected to be on stock again on %s </font>');
define('SUB_TITLE_SUB_TOTAL', 'Sub-total:');

define('OUT_OF_STOCK_CANT_CHECKOUT', 'The products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' , are not available in the requested quantity.<br />Please decrease quantity for marked products. Thank you');
define('OUT_OF_STOCK_CAN_CHECKOUT', 'The products marked with ' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . ' , are not available in the requested quantity.<br />We will restock the products currently out of stock as soon as possible. Partial delivery upon request.');

define('MINIMUM_ORDER_VALUE_NOT_REACHED_1', 'You need to reach the minimum order value of: ');
define('MINIMUM_ORDER_VALUE_NOT_REACHED_2', ' <br />Please increase order value by at least: ');
define('MAXIMUM_ORDER_VALUE_REACHED_1', 'You ordered more than the allowed amount of: ');
define('MAXIMUM_ORDER_VALUE_REACHED_2', '<br /> Please decrease your order by at least: ');

define('ERROR_INVALID_PRODUCT', 'The product chosen was not found!');

/*
 *
 * NAVBAR TITLE
 *
 */

define('NAVBAR_TITLE_ACCOUNT', 'Your account');
define('NAVBAR_TITLE_1_ACCOUNT_EDIT', 'Your account');
define('NAVBAR_TITLE_2_ACCOUNT_EDIT', 'Changing your personal data');
define('NAVBAR_TITLE_1_ACCOUNT_HISTORY', 'Your account');
define('NAVBAR_TITLE_2_ACCOUNT_HISTORY', 'Your completed orders');
define('NAVBAR_TITLE_1_ACCOUNT_HISTORY_INFO', 'Your account');
define('NAVBAR_TITLE_2_ACCOUNT_HISTORY_INFO', 'Completed orders');
define('NAVBAR_TITLE_3_ACCOUNT_HISTORY_INFO', 'Order number %s');
define('NAVBAR_TITLE_1_ACCOUNT_PASSWORD', 'Your account');
define('NAVBAR_TITLE_2_ACCOUNT_PASSWORD', 'Change password');
define('NAVBAR_TITLE_1_ADDRESS_BOOK', 'Your account');
define('NAVBAR_TITLE_2_ADDRESS_BOOK', 'Address book');
define('NAVBAR_TITLE_1_ADDRESS_BOOK_PROCESS', 'Your account');
define('NAVBAR_TITLE_2_ADDRESS_BOOK_PROCESS', 'Address book');
define('NAVBAR_TITLE_ADD_ENTRY_ADDRESS_BOOK_PROCESS', 'New entry');
define('NAVBAR_TITLE_MODIFY_ENTRY_ADDRESS_BOOK_PROCESS', 'Change entry');
define('NAVBAR_TITLE_DELETE_ENTRY_ADDRESS_BOOK_PROCESS', 'Delete Entry');
define('NAVBAR_TITLE_ADVANCED_SEARCH', 'Advanced Search');
define('NAVBAR_TITLE1_ADVANCED_SEARCH', 'Advanced Search');
define('NAVBAR_TITLE2_ADVANCED_SEARCH', 'Search results');
define('NAVBAR_TITLE_1_CHECKOUT_CONFIRMATION', 'Checkout');
define('NAVBAR_TITLE_2_CHECKOUT_CONFIRMATION', 'Confirmation');
define('NAVBAR_TITLE_1_CHECKOUT_PAYMENT', 'Checkout');
define('NAVBAR_TITLE_2_CHECKOUT_PAYMENT', 'Method of payment');
define('NAVBAR_TITLE_1_PAYMENT_ADDRESS', 'Checkout');
define('NAVBAR_TITLE_2_PAYMENT_ADDRESS', 'Change billing address');
define('NAVBAR_TITLE_1_CHECKOUT_SHIPPING', 'Checkout');
define('NAVBAR_TITLE_2_CHECKOUT_SHIPPING', 'Shipping information');
define('NAVBAR_TITLE_1_CHECKOUT_SHIPPING_ADDRESS', 'Checkout');
define('NAVBAR_TITLE_2_CHECKOUT_SHIPPING_ADDRESS', 'Change shipping address');
define('NAVBAR_TITLE_1_CHECKOUT_SUCCESS', 'Checkout');
define('NAVBAR_TITLE_2_CHECKOUT_SUCCESS', 'Success');
define('NAVBAR_TITLE_CREATE_ACCOUNT', 'Create account');
if (isset($navigation) && $navigation->snapshot['page'] == FILENAME_CHECKOUT_SHIPPING) {
  define('NAVBAR_TITLE_LOGIN', 'Order');
} else {
  define('NAVBAR_TITLE_LOGIN', 'Login');
}
define('NAVBAR_TITLE_LOGOFF','Good bye');
define('NAVBAR_TITLE_PRODUCTS_NEW', 'New products');
define('NAVBAR_TITLE_SHOPPING_CART', 'Shopping cart');
define('NAVBAR_TITLE_SPECIALS', 'Special offers');
define('NAVBAR_TITLE_COOKIE_USAGE', 'Cookie usage');
define('NAVBAR_TITLE_PRODUCT_REVIEWS', 'Reviews');
define('NAVBAR_TITLE_REVIEWS_WRITE', 'Opinions');
define('NAVBAR_TITLE_REVIEWS','Reviews');
define('NAVBAR_TITLE_SSL_CHECK', 'Note on safety');
define('NAVBAR_TITLE_CREATE_GUEST_ACCOUNT','Your customer address');
define('NAVBAR_TITLE_PASSWORD_DOUBLE_OPT','Password forgotten?');
define('NAVBAR_TITLE_NEWSLETTER','Newsletter');
define('NAVBAR_GV_REDEEM', 'Redeem Voucher');
define('NAVBAR_GV_SEND', 'Send Voucher');

/*
 *
 *  MISC
 *
 */

define('TEXT_NEWSLETTER','You want to stay up to date?<br />No problem, receive our newsletter for the latest updates.');
define('TEXT_EMAIL_INPUT','Your e-mail adress has been registered in our system.<br />An e-mail with a confirmation link has been send out. Click the link in order to complete registration!');

define('TEXT_WRONG_CODE','<font color="#ff0000">The security code you entered was not correct. Please try again. <br />The form is not case sensitive.</font>');
define('TEXT_EMAIL_EXIST_NO_NEWSLETTER','<font color="#ff0000">This e-mail address is registered but not yet activated!</font>');
define('TEXT_EMAIL_EXIST_NEWSLETTER','<font color="#ff0000">This e-mail address is already registered for the newsletter!</font>');
define('TEXT_EMAIL_NOT_EXIST','<font color="#ff0000">This e-mail address is not registered for newsletters!</font>');
define('TEXT_EMAIL_DEL','Your e-mail address was deleted successfully from our newsletter-database.');
define('TEXT_EMAIL_DEL_ERROR','<font color="#ff0000">An Error occured, your e-mail address has not been removed from our database!</font>');
define('TEXT_EMAIL_ACTIVE','<font color="#ff0000">Your e-mail address has successfully been registered for the newsletter!</font>');
define('TEXT_EMAIL_ACTIVE_ERROR','<font color="#ff0000">An error occured, your e-mail address has not been registered for the newsletter!</font>');
define('TEXT_EMAIL_SUBJECT','Your newsletter account');

define('TEXT_CUSTOMER_GUEST','Guest');

define('TEXT_LINK_MAIL_SENDED','Your new password request must be confirmed.<br />An e-mail with a confirmation link has been send out. Click the link in order to complete recieve a new password!');
define('TEXT_PASSWORD_MAIL_SENDED','You will receive an e-mail with your new password within minutes.<br />Please change your password after your first login.');
define('TEXT_CODE_ERROR','The security code you entered was not correct.<br />Please try again.');
define('TEXT_EMAIL_ERROR','The e-mail address is not registered in our store.<br />Please try again.');
define('TEXT_NO_ACCOUNT','Your request for a new password is either invalid or timed out.<br />Please try again.');
define('HEADING_PASSWORD_FORGOTTEN','Password renewal?');
define('TEXT_PASSWORD_FORGOTTEN','Change your password in three easy steps.');
define('TEXT_EMAIL_PASSWORD_FORGOTTEN','Confirmation mail for password renewal');
define('TEXT_EMAIL_PASSWORD_NEW_PASSWORD','Your new password');
define('ERROR_MAIL','Please check the data entered in the form');

define('CATEGORIE_NOT_FOUND','Category not found');

define('GV_FAQ', 'Gift voucher FAQ');
define('ERROR_NO_REDEEM_CODE', 'You did not enter a redeem code.');
define('ERROR_NO_INVALID_REDEEM_GV', 'Invalid gift voucher code');
define('TABLE_HEADING_CREDIT', 'Credits available');
define('EMAIL_GV_TEXT_SUBJECT', 'A gift from %s');
define('MAIN_MESSAGE', 'You have decided to send a gift voucher worth %s to %s who\'s e-mail address is %s<br /><br />Following text will be included in the e-mail:<br /><br />Dear %s<br /><br />You have received a Gift voucher worth %s by %s');
define('REDEEMED_AMOUNT','Your gift voucher was successfully added to your account. Gift voucher amount:');
define('REDEEMED_COUPON','Your voucher has been successfully credited to your account and will be cashed automatically on your purchase.');

define('ERROR_INVALID_USES_USER_COUPON','This voucher can only be redeemed ');
define('ERROR_INVALID_USES_COUPON','This coucher can only be redeemed ');
define('TIMES',' times.');
define('ERROR_INVALID_STARTDATE_COUPON','Your coupon is not available yet.');
define('ERROR_INVALID_FINISDATE_COUPON','Your voucher is already expired.');
define('PERSONAL_MESSAGE', '%s writes:');

//Popup Window
// BOF - DokuMan - 2010-02-25 removed double definition 
//define('TEXT_CLOSE_WINDOW', 'Close window.');
// EOF - DokuMan - 2010-02-25 removed double definition 

/*
 *
 * CUOPON POPUP
 *
 */

define('TEXT_CLOSE_WINDOW', 'Close window [x]');
define('TEXT_COUPON_HELP_HEADER', 'Your voucher/coupon has been successfully redeemed.');
define('TEXT_COUPON_HELP_NAME', '<br /><br />Voucher/Coupon name : %s');
define('TEXT_COUPON_HELP_FIXED', '<br /><br />This voucher/coupon is worth %s off your next order');
define('TEXT_COUPON_HELP_MINORDER', '<br /><br />You need to spend at least %s to be able to use the voucher.');
define('TEXT_COUPON_HELP_FREESHIP', '<br /><br />This voucher gives you free shipping on your order');
define('TEXT_COUPON_HELP_DESC', '<br /><br />Voucher description : %s');
define('TEXT_COUPON_HELP_DATE', '<br /><br />This voucher is valid from: %s to %s');
define('TEXT_COUPON_HELP_RESTRICT', '<br /><br />Product / Category Restrictions');
define('TEXT_COUPON_HELP_CATEGORIES', 'Category');
define('TEXT_COUPON_HELP_PRODUCTS', 'Product');
//BOF - DokuMan - 2010-10-28 - Added text-constant for emailing voucher
define('ERROR_ENTRY_AMOUNT_CHECK', 'Invalid amount');
define('ERROR_ENTRY_EMAIL_ADDRESS_CHECK', 'Invalid e-mail address');
//EOF - DokuMan - 2010-10-28 - Added text-constant for emailing voucher

// VAT Reg No
define('ENTRY_VAT_TEXT','* for EU-Countries only'); // anmerkung: besser wenn laden im EU ausland ist
define('ENTRY_VAT_ERROR', 'The chosen VAT Reg No is not valid or cannot be verified at the moment! Please enter a valid VAT Reg No or leave this field empty.');
define('MSRP','MSRP');
define('YOUR_PRICE','your price ');
// BOF - Tomcraft - 2009-10-09 - Added text-constant for unit price
define('UNIT_PRICE','unit price ');
// EOF - Tomcraft - 2009-10-09 - Added text-constant for unit price
define('ONLY',' Now only ');// DokuMan - Werbung mit durchgestrichenen Statt-Preisen ist zulässig
define('FROM','from ');
define('YOU_SAVE','you save ');
define('INSTEAD','Our previous price ');// DokuMan - Werbung mit durchgestrichenen Statt-Preisen ist zulässig
define('TXT_PER',' per ');
define('TAX_INFO_INCL','%s VAT incl.');
define('TAX_INFO_EXCL','%s VAT excl.');
define('TAX_INFO_ADD','%s VAT plus.');
define('SHIPPING_EXCL','excl.');
define('SHIPPING_COSTS','Shipping costs'); 

// changes 3.0.4 SP2
define('SHIPPING_TIME','Shipping time: ');
define('MORE_INFO','[More]');

// changes 3.0.4 SP2.2
define('ENTRY_PRIVACY_ERROR','Please accept our privacy policy!');
define('TEXT_PAYMENT_FEE','Paymentfee');

define('_MODULE_INVALID_SHIPPING_ZONE', 'Unfortunately we do not deliver to the chosen country.');
define('_MODULE_UNDEFINED_SHIPPING_RATE', 'Shipping costs cannot be calculated at the moment, please contact us.');

//Dokuman - 2009-08-21 - Added 'delete account' functionality for customers
define('NAVBAR_TITLE_1_ACCOUNT_DELETE', 'Your account');
define('NAVBAR_TITLE_2_ACCOUNT_DELETE', 'Delete account');	

//contact-form error messages
define('ERROR_EMAIL','<p><b>Your e-mail address:</b> None or invalid input!</p>');
define('ERROR_VVCODE','<p><b>Security code:</b> No match, please enter your security code again!</p>');
define('ERROR_MSG_BODY','<p><b>Your message:</b> No input!</p>');

// BOF - web28 - 2010-05-07 - PayPal API Modul
define('NAVBAR_TITLE_PAYPAL_CHECKOUT','PayPal-Checkout');
define('PAYPAL_ERROR','PayPal abort');
define('PAYPAL_NOT_AVIABLE','PayPal Express is not available.<br />Please select another method of payment<br />or try again later.<br />');
define('ERROR_ADDRESS_NOT_ACCEPTED', 'We are not able to accept your order if you do not accept your address!');
define('PAYPAL_FEHLER','PayPal announced an error to the completion..<br />Your order is stored, is however not implemented.<br />Please enter a new order.<br />Thanks for your understanding.<br />');
define('PAYPAL_WARTEN','PayPal announced an error to the completion.<br />You must pay again to PayPal around the order.<br />Down you see the stored order.<br /> Thanks for it pressing to understanding request you again the button PayPal express.<br />');
define('PAYPAL_NEUBUTTON','Press please again around the order to pay.<br />Every other key leads to the abort of the order.');
define('PAYPAL_GS','Coupon');
define('PAYPAL_TAX','Tax');
define('PAYPAL_EXP_WARN','Note! Possibly resulting forwarding expenses are only computed in the shop finally.');
define('PAYPAL_EXP_VORL','Provisional forwarding expenses');
define('PAYPAL_EXP_VERS','12.90');
// 09.01.11
define('PAYPAL_ADRESSE','The country in your PayPal dispatch address is not registered in our shop.<br />Please contact us.<br />Thanks for you understanding.<br />From PayPal received country: ');
// 17.09.11
define('PAYPAL_AMMOUNT_NULL','The order sum which can be expected (without dispatch) is directly 0.<br />Thus PayPal express is not available.<br />Please select another payment means.<br />Thanks for your understanding.<br />');
// EOF - web28 - 2010-05-07 - PayPal API Modul

define('BASICPRICE_VPE_TEXT','in this volume only '); // Hetfield - 2009-11-26 - Added language definition for vpe at graduated prices
//web - 2010-07-11 - Preisanzeige bei Staffelpreisen (größte Staffel)
define('GRADUATED_PRICE_MAX_VALUE', 'from');

// BOF - DokuMan - 2011-09-20 - E-Mail SQL errors
define('ERROR_SQL_DB_QUERY','We are sorry, but an database error has occurred somewhere on this page!');
define('ERROR_SQL_DB_QUERY_REDIRECT','You will be redirected back to our home page in %s seconds!');
// EOF - DokuMan - 2011-09-20 - E-Mail SQL errors

define('TEXT_AGB_CHECKOUT','Please take note of our General Terms & Conditions %s and Cancellation Policy %s');
?>