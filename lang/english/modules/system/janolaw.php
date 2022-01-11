<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003   nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: billiger.php 950 2005-05-14 16:45:21Z mz $)
   (c) 2008 Gambio OHG (billiger.php 2008-11-11 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

define('MODULE_JANOLAW_TEXT_TITLE', 'janolaw Terms Hosting-Service');
define('MODULE_JANOLAW_TEXT_DESCRIPTION', '<a href="https://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><img src="images/janolaw/janolaw_185x35.png" border=0></a><br /><br />Germany\'s big legal rights portal janolaw offers customized solutions to your legal issues - from the lawyer hotline to individual contracts with attorney warranty. With the AGB hosting service for Internet stores, you can adjust the legal core documents Terms, Conditions, Disclaimer and Privacy Statement tailored to your store and update it continually through the janolaw team. More protection is not possible.<br /><br /><a href="https://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><strong><u>Click here for the offer<u></strong></a>');
define('MODULE_JANOLAW_USER_ID_TITLE', '<hr noshade>User-ID');
define('MODULE_JANOLAW_USER_ID_DESC', 'Your User-ID');
define('MODULE_JANOLAW_SHOP_ID_TITLE', 'Shop-ID');
define('MODULE_JANOLAW_SHOP_ID_DESC', 'Your Shop-ID');
define('MODULE_JANOLAW_STATUS_DESC', 'Enable Module?');
define('MODULE_JANOLAW_STATUS_TITLE', 'Status');
define('MODULE_JANOLAW_TYPE_TITLE', '<hr noshade>Save as');
define('MODULE_JANOLAW_TYPE_DESC', 'Store in a file or in the database?');
define('MODULE_JANOLAW_FORMAT_TITLE', 'Format Type');
define('MODULE_JANOLAW_FORMAT_DESC', 'Save as text or HTML');
define('MODULE_JANOLAW_UPDATE_INTERVAL_TITLE', '<hr noshade>Update Interval');
define('MODULE_JANOLAW_UPDATE_INTERVAL_DESC', 'How often should the data be updated?');
define('MODULE_JANOLAW_ERROR', 'Please check the assignment of documents.');

define('MODULE_JANOLAW_TYPE_DATASECURITY_TITLE', '<hr noshade>Legal text Privacy Notice');
define('MODULE_JANOLAW_TYPE_DATASECURITY_DESC', 'Please specify content of this legal text to be inserted');
define('MODULE_JANOLAW_PDF_DATASECURITY_TITLE', 'PDF as Download');
define('MODULE_JANOLAW_PDF_DATASECURITY_DESC', 'Save document and add a link to the document?<br/><b>Important:</b> This only works with HTML version!');
define('MODULE_JANOLAW_MAIL_DATASECURITY_TITLE', 'E-Mail Attachment');
define('MODULE_JANOLAW_MAIL_DATASECURITY_DESC', 'Send PDF as attachment with order confirmation?');

define('MODULE_JANOLAW_TYPE_TERMS_TITLE', 'Legal text Conditions of Use');
define('MODULE_JANOLAW_TYPE_TERMS_DESC', 'Please specify content of this legal text to be inserted');
define('MODULE_JANOLAW_PDF_TERMS_TITLE', 'PDF as Download');
define('MODULE_JANOLAW_PDF_TERMS_DESC', 'Save document and add a link to the document?<br/><b>Important:</b> This only works with HTML version!');
define('MODULE_JANOLAW_MAIL_TERMS_TITLE', 'E-Mail Attachment');
define('MODULE_JANOLAW_MAIL_TERMS_DESC', 'Send PDF as attachment with order confirmation?');

define('MODULE_JANOLAW_TYPE_LEGALDETAILS_TITLE', 'Legal text Legal Notice');
define('MODULE_JANOLAW_TYPE_LEGALDETAILS_DESC', 'Please specify content of this legal text to be inserted');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_TITLE', 'PDF as Download');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_DESC', 'Save document and add a link to the document?<br/><b>Important:</b> This only works with HTML version!');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_TITLE', 'E-Mail Attachment');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_DESC', 'Send PDF as attachment with order confirmation?');

define('MODULE_JANOLAW_TYPE_REVOCATION_TITLE', 'Legal text Right of revocation');
define('MODULE_JANOLAW_TYPE_REVOCATION_DESC', 'Please specify content of this legal text to be inserted');
define('MODULE_JANOLAW_PDF_REVOCATION_TITLE', 'PDF as Download');
define('MODULE_JANOLAW_PDF_REVOCATION_DESC', 'Save document and add a link to the document?<br/><b>Important:</b> This only works with HTML version!');
define('MODULE_JANOLAW_MAIL_REVOCATION_TITLE', 'E-Mail Attachment');
define('MODULE_JANOLAW_MAIL_REVOCATION_DESC', 'Send PDF as attachment with order confirmation?');

define('MODULE_JANOLAW_TYPE_WITHDRAWAL_TITLE', 'Legal text Withdrawal form');
define('MODULE_JANOLAW_TYPE_WITHDRAWAL_DESC', 'Please specify content of this legal text to be inserted<br/><br/><b>Important:</b> this works from version 3. The changes can be made at Janolaw.');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_TITLE', 'PDF as Download');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_DESC', 'Save document and add a link to the document?<br/><b>Important:</b> This only works with HTML version!');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_TITLE', 'E-Mail Attachment');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_DESC', 'Send PDF as attachment with order confirmation?');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_TITLE', 'Combined Revocation with withdrawal form');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_DESC', 'Create a combined Revocation with withdrawal form');

?>