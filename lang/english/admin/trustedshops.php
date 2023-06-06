<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

define('TEXT_SETTINGS', 'Settings');

define('HEADING_TITLE', 'Trusted Shops');
define('HEADING_FEATURES', 'Features');

define('TABLE_HEADING_TRUSTEDSHOPS_ID', 'TS-ID');
define('TABLE_HEADING_LANGUAGE', 'Language');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Action');

define('HEADING_TRUSTBADGE', 'Trustbadge');
define('HEADING_ADVANCED', 'Advanced');

define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_TRUSTEDSHOPS_STATUS', 'Status:');
define('TEXT_TRUSTEDSHOPS_ID', 'TS-ID:');
define('TEXT_TRUSTEDSHOPS_LANGUAGES', 'Language:');
define('TEXT_TRUSTBADGE_INFO', 'The Trustbadge shows your Trustmark and your customer ratings in your store and can be customized in appearance and positioning. With variant "Standard", only the Trustmark is displayed, the "Reviews" variant additionally shows your customer ratings. Further parameters can be individualized by selecting "Custom" (programming skills are required for this).');

define('TEXT_TRUSTEDSHOPS_BADGE', 'Variant:');
define('TEXT_TRUSTEDSHOPS_POSITION', 'Position:');
define('TEXT_BADGE_DEFAULT', 'Standard');
define('TEXT_BADGE_SMALL', 'Standard (small)');
define('TEXT_BADGE_REVIEWS', 'Reviews');
define('TEXT_BADGE_CUSTOM', 'Custom');
define('TEXT_BADGE_CUSTOM_REVIEWS', 'Custom (Reviews)');
define('TEXT_BADGE_OFFSET', 'Position Y-Axis:');
define('TEXT_BADGE_INSTRUCTION', 'You will find a step-by-step instruction for your shopsoftware in our integration center. <a href="https://help.etrusted.com/hc/en-gb/articles/360045842852-Using-Trusted-Shops-with-modified" target="_blank" style="text-decoration:underline">Click here.</a>');
define('TEXT_BADGE_CUSTOM_CODE', 'Fill in your trustbadge code here:');

define('TEXT_PRODUCT_STICKER_API', 'Product Review API:');
define('TEXT_PRODUCT_STICKER_API_INFO', 'With the product review API the reviews are imported into the store.'.((!defined('TABLE_SCHEDULED_TASKS')) ? '  For this it is necessary that a cronjob is created on URL '.HTTPS_SERVER.DIR_WS_CATALOG.'api/trustedshops/cronjob.php' : ''));
define('TEXT_PRODUCT_STICKER_API_CLIENT', 'Product Review API Client:');
define('TEXT_PRODUCT_STICKER_API_SECRET', 'Product Review API Secret:');
define('TEXT_PRODUCT_STICKER_STATUS', 'Product Review Widget Status:');
define('TEXT_PRODUCT_STICKER', 'Product Review Widget Code edit:');
define('TEXT_PRODUCT_STICKER_INFO', 'The Product Review Widget shows the current product reviews in your shop.<br/>Configure your Product Review Widget using <a target="_blank" href="https://help.etrusted.com/hc/en-gb/articles/360045842852-Using-Trusted-Shops-with-modified" style="text-decoration:underline">our instructions.</a>');
define('TEXT_PRODUCT_STICKER_INTRO', 'Reviews');

define('TEXT_REVIEW_STICKER_STATUS', 'Review Widget Status:');
define('TEXT_REVIEW_STICKER', 'Review Widget Code edit:');
define('TEXT_REVIEW_STICKER_INFO', 'The review widget shows the current ratings for your shop.<br/>Configure your review widget using <a target="_blank" href="https://help.etrusted.com/hc/en-gb/articles/360045842852-Using-Trusted-Shops-with-modified" style="text-decoration:underline">our instructions.</a>');
define('TEXT_REVIEW_STICKER_INTRO', 'Reviews');

define('TEXT_HEADING_DELETE_TRUSTEDSHOPS', 'TS-ID delete');
define('TEXT_DELETE_INTRO', 'Are you sure, deleting this TS-ID?');

define('TEXT_DISABLED', 'disabled');
define('TEXT_ENABLED', 'enabled');

define('TEXT_LEFT', 'left');
define('TEXT_RIGHT', 'right');
define('TEXT_CENTER', 'center');

define('TEXT_DISPLAY_NUMBER_OF_TRUSTEDSHOPS', 'Displaying <b>%d</b> to <b>%d</b> (of <b>%d</b> TS-ID)');

define('TEXT_TS_MAIN_INFO', '
<img src="images/trustedshops/illustration-ts-products-profile-page.png" style="width:160px;float:right;margin-top:30px;padding-left:30px;"/>
<h2>Trusted Shops</h2>
More than 30,000 online shops throughout Europe use the Trusted Shops Trustmark, Buyer Protection and authentic reviews for more traffic, higher sales and better conversion rates. Trusted Shops Reviews Toolkit is the easiest and fastest way to integrate our Trust solutions into your modified eCommerce Shopsoftware.<br/>
<br/>
<b>Building trust - in just 5 minutes!</b><br/>
<br/>
The well-known Trustmark, the Buyer Protection and the authentic reviews from Trusted Shops have stood for trust for over 20 years. More than 30,000 online shops throughout Europe use our Trust solutions for more traffic, higher sales and better conversion rates.
<br/>
Trusted Shops Trustbadge for modified eCommerce Shopsoftware is the easiest and fastest way to convince visitors of the trustworthiness of your online-shop. The simple installation guarantees product use in just 5 minutes and usually requires little to no prior technical knowledge. With our extension you are always technically up to date and have no additional maintenance effort.<br/>
<br/>
<b>Your benefit:</b> With just a few clicks, visitors to your online shop can see trust elements such as the Trustbadge or other on-site widgets, can benefit from buyer protection and are automatically asked for feedback after placing an order.');
define('TEXT_TS_FEATURES_INFO', '
<img src="images/trustedshops/illustration-ts-badge.png" style="width:160px;float:right;margin-top:30px;padding-left:30px;"/>
<h2>Features</h2>
<b>All features at a glance:</b><br/>
<br/>
<ul>
  <li>Show Trustbadge, integrate Buyer Protection & collect shop reviews</li>
  <li>Collect and display Product Reviews</li>
  <li>Configure multi-shops with multiple Trusted Shops IDs</li>
</ul>
<br/>
Please note: To use the extension Trusted Shops Trustbadge for Modfied you need an existing Trusted Shops membership. You can find out more about the products and benefits of Trusted Shops on our website or by calling: +44 23364 5906<br/>
<br/>
Do you need help with the integration? You can find detailed integration instructions in our Help Center.<br/>
<br/>
Link: <a href="https://help.etrusted.com/hc/en-gb/articles/360045842852-Using-Trusted-Shops-with-modified" target="_blank" style="text-decoration:underline">https://help.etrusted.com/hc/en-gb/articles/360045842852-Using-Trusted-Shops-with-modified</a>');
define('TEXT_TS_SPECIAL_INFO', '
<b>Our special offer for you:<br/>Permanently cheaper for modified users!</b><br/>
<a class="btnSmall btnCuracao fitting" target="_blank" href="https://checkout.trustedshops.com/?a_aid=modified-shop">Become a member now!</a>');

?>