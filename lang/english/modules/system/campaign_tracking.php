<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_CAMPAIGN_TRACKING_TEXT_TITLE', 'Campaign tracking');
  define('MODULE_CAMPAIGN_TRACKING_TEXT_DESCRIPTION', 'Defines how long orders are attributed to a campaign. Without this module a campaign only counts for the current visit, but forever for a customer account created through the campaign.<br /><br />With the module active, the campaign link clicked last counts as well.');

  define('MODULE_CAMPAIGN_TRACKING_STATUS_TITLE', 'Status');
  define('MODULE_CAMPAIGN_TRACKING_STATUS_DESC', 'Module status');

  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_TITLE', 'Campaign of the customer account (days)');
  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_DESC', 'For how many days after the account was created the orders of a customer are still attributed to the campaign the account was created through. 0 = unlimited.');
  define('MODULE_CAMPAIGN_TRACKING_ACCOUNT_DAYS_ERROR', 'Campaign of the customer account: Please enter a whole number from 0.');

  define('MODULE_CAMPAIGN_TRACKING_COOKIE_TITLE', 'Set campaign cookie');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DESC', 'Stores the campaign in the cookie &quot;MODcampaign&quot; with the consent of the visitor. A later visit without the campaign link is then attributed to the campaign as well.<br />Requires the module Cookie Consent. On saving, the module adds an entry in the category Marketing there and switches on the readable cookie decision. Update your privacy policy.');

  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_TITLE', 'Validity of the cookie (days)');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_DESC', 'For how many days after the click on the campaign link the cookie is valid. 400 days at most, browsers do not keep a cookie any longer.');
  define('MODULE_CAMPAIGN_TRACKING_COOKIE_DAYS_ERROR', 'Validity of the cookie: Please enter a whole number from 1 to 400.');

  define('MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_MODULE', 'Campaign tracking: The campaign cookie requires the active module Cookie Consent. Without it the shop sets no cookie.');
  define('MODULE_CAMPAIGN_TRACKING_ERROR_CONSENT_ID', 'Campaign tracking: The cookie consent has no free ID from 1 to 24 left. The campaign cookie therefore has no entry, visitors cannot agree to it.');
  define('MODULE_CAMPAIGN_TRACKING_INFO_READABLE_COOKIE', 'Campaign tracking: The readable cookie decision (MODOilTrack) of the cookie consent is switched on now. The module needs it to read the decision of the visitor.');
