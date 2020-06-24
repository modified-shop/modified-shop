<?php
  /* --------------------------------------------------------------
   $Id: cookie_consent.js.php $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  define('MODULE_COOKIE_CONSENT_STATUS_TITLE', 'Module status');
  define('MODULE_COOKIE_CONSENT_STATUS_DESC', 'Enable Module?');
  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_TITLE', 'Use a readable cookie');
  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DESC', '');

  define('MODULE_COOKIE_CONSENT_SET_READABLE_COOKIE_DETAIL', 'If you use the Google Tag Manager or other software, they may only insert scripts and cookies if the visitor allows it. An additional, readable cookie can be set so that the tag manager can read out the customer request.<br /><br />Setup in Google Tag Manager:<br />Go to the &quot;Variables&quot; menu and create a new variable under &quot;User-defined variables&quot;. Name it, for example, &quot;Cookie Consent&quot;.<br />Then click on "Configure Variable" and select the type "First-Party-Cookie". Enter the name of the cookie &quot;MODOilTrack&quot;.<br /><br />You can then edit your &quot;trigger&quot;.<br />Set the "Pageview" trigger type and select the &quot;Some pageviews&quot; option.<br />Enter the following as a condition: <em>&quot;Cookieconsent&quot;</em> (name of the variable) contains <em>[&quot;1&quot;: true]</em>. The value contained per cookie can be found in the cookie consent configuration.<br /><br />You can find illustrated instructions here: <a href="https://www.dair-media.net/blog/dsgvo-cookie-einwilligung-im-google-tag-manager-beruecksichtigen/" target="_blank">https://www.dair-media.net/blog/dsgvo-cookie-einwilligung-im-google-tag-manager-beruecksichtigen/</a>');

  define('MODULE_COOKIE_CONSENT_EXTENDED_DESCRIPTION', '<strong><font color="red">ATTENTION:</font></strong> Please setup Cookie Consent configuration under "Configuration" -> <a href="'.xtc_href_link(FILENAME_COOKIE_CONSENT).'"><strong>"Cookie Consent"</strong></a>!');
  define('MODULE_COOKIE_CONSENT_MORE_INFO', 'More Informations:');