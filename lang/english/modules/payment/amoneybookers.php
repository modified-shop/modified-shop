<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneybookers.php,v 1.01 2003/01/20); www.oscommerce.com
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Third Party contributions:
   Moneybookers v1.0                       Autor:    Gabor Mate  <gabor(at)jamaga.hu>

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_TITLE', 'Secure Payment through Moneybookers');
$_var = 'Moneybookers';
if (_PAYMENT_MONEYBOOKERS_EMAILID=='') {
  $_var.='<br /><br /><strong><font color="red">ATTENTION:</font></strong> Please setup moneybookers.com configuration first! (Adv. Configuration -> modified Partner -> <a href="'.xtc_href_link(FILENAME_CONFIGURATION, 'gID=31').'">Moneybookers.com</a>)!';
}
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_DESCRIPTION', $_var);
define('MODULE_PAYMENT_AMONEYBOOKERS_NOCURRENCY_ERROR', 'There\'s no Moneybookers accepted currency installed!');
define('MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT1', 'payment_error=');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO','');
define('MODULE_PAYMENT_AMONEYBOOKERS_ERRORTEXT2', '&error=There was an error during your payment at Moneybookers!');
define('MODULE_PAYMENT_AMONEYBOOKERS_ORDER_TEXT', 'Date of the order: ');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_ERROR', 'Payment error!');
define('MODULE_PAYMENT_AMONEYBOOKERS_CONFIRMATION_TEXT', 'Thank you for your order!');
define('MODULE_PAYMENT_AMONEYBOOKERS_TRANSACTION_FAILED_TEXT', 'Your payment transaction at Moneybookers has failed. Please try again, or select an other payment option!');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_2', '<b>100% security</b> - All data transactions are secure with Moneybookers most modern security technology');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_3', '<b>100% data protection</b> - Your personal data will be stored at Moneybookers and not be passed on to the shop');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_4', '<b>Maximum comfort</b> -  Once you have a Moneybookers account, you only need your e-mail address and password for all future transactions');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_5', '<b>Broad acceptance</b> -  With Moneybookers, you can pay in serveral thousand shops');
define('MODULE_PAYMENT_AMONEYBOOKERS_TEXT_INFO_1', '<br /><br />Pay instantly by...');
//BOF - DokuMan - 2010-09-08 - already defined in english.php
//define('MB_TEXT_MBDATE', 'Last Change:');
//define('MB_TEXT_MBTID', 'TR ID:');
//define('MB_TEXT_MBERRTXT', 'Status:');
//BOF - DokuMan - 2010-09-08 - already defined in english.php

define('MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID_TITLE', 'Order status - Processed');
define('MODULE_PAYMENT_AMONEYBOOKERS_PROCESSED_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID_TITLE', 'Order status - Sheduled');
define('MODULE_PAYMENT_AMONEYBOOKERS_PENDING_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID_TITLE', 'Order status - Canceled');
define('MODULE_PAYMENT_AMONEYBOOKERS_CANCELED_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID_TITLE', 'Order status - Temp');
define('MODULE_PAYMENT_AMONEYBOOKERS_TMP_STATUS_ID_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_ICONS_TITLE', 'Icons');
define('MODULE_PAYMENT_AMONEYBOOKERS_ICONS_DESC', '');

define('MODULE_PAYMENT_AMONEYBOOKERS_STATUS_TITLE', 'Enable Moneybookers');
define('MODULE_PAYMENT_AMONEYBOOKERS_STATUS_DESC', 'Do you want to accept payments through Moneybookers?');
define('MODULE_PAYMENT_AMONEYBOOKERS_EMAILID_TITLE', 'Email Address');
define('MODULE_PAYMENT_AMONEYBOOKERS_EMAILID_DESC', 'Email address you have registered with Moneybookers. <font color="#ff0000">* Required</font>');
define('MODULE_PAYMENT_AMONEYBOOKERS_PWD_TITLE', 'Moneybookers Secret Word');
define('MODULE_PAYMENT_AMONEYBOOKERS_PWD_DESC', 'The secret word can be found in your Moneybookers profile (this is not your password!)');
define('MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID_TITLE', 'Merchant ID');
define('MODULE_PAYMENT_AMONEYBOOKERS_MERCHANTID_DESC', 'Merchant ID of your Moneybookers account <font color="#ff0000">* Required</font>');
define('MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER_TITLE', 'Sort order of display.');
define('MODULE_PAYMENT_AMONEYBOOKERS_SORT_ORDER_DESC', 'Sort order of display. Lowest is displayed first.');
define('MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY_TITLE', 'Transaction Currency');
define('MODULE_PAYMENT_AMONEYBOOKERS_CURRENCY_DESC', 'If the user\'s currency that is not available at Moneybookers this currency will be used for the payment.');
define('MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE_TITLE', 'Transaction Language');
define('MODULE_PAYMENT_AMONEYBOOKERS_LANGUAGE_DESC', 'If the user\'s language is not available at Moneybookers this language will be used for the payment.');
define('MODULE_PAYMENT_AMONEYBOOKERS_ZONE_TITLE', 'Payment Zone');
define('MODULE_PAYMENT_AMONEYBOOKERS_ZONE_DESC', 'If a zone is selected, only enable this payment method for that zone.');
define('MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED_TITLE' , 'Allowed Zones');
define('MODULE_PAYMENT_AMONEYBOOKERS_ALLOWED_DESC' , 'Please enter the zones <b>separately</b> which should be allowed to use this modul (e. g. AT,DE (leave empty if you want to allow all zones))');

// BOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
define('MODULE_PAYMENT_AMONEYBOOKERS_NEG_SHIPPING_TITLE', 'Exclusion in case of shipping');
define('MODULE_PAYMENT_AMONEYBOOKERS_NEG_SHIPPING_DESC', 'deactivate this payment if one of these shippingtypes are selected (list separated by comma)');
// EOF - Hendrik - 2010-08-09 - exlusion config for shipping modules
?>