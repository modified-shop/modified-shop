<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_JSON_LD_TEXT_TITLE', 'Structured data (JSON-LD)');
  define('MODULE_JSON_LD_TEXT_DESCRIPTION', 'Outputs the structured data of the shop as JSON-LD in the head of every page, in addition to the rich snippets. Search engines and AI services prefer this format. Own statements can be added through files in the directory includes/extra/json_ld/.');

  define('MODULE_JSON_LD_STATUS_TITLE', 'Enable module?');
  define('MODULE_JSON_LD_STATUS_DESC', 'Output JSON-LD in the head of every page. The rich snippets in the templates stay untouched.');

  define('MODULE_JSON_LD_ORGANIZATION_TITLE', 'Output the shop owner?');
  define('MODULE_JSON_LD_ORGANIZATION_DESC', 'Outputs an Organization object with shop name, logo, email address and VAT ID on every page. Every offer refers to it as its seller.');

  define('MODULE_JSON_LD_WEBSITE_TITLE', 'Output website and search?');
  define('MODULE_JSON_LD_WEBSITE_DESC', 'Outputs a WebSite object with the entry point of the shop search on the start page. Google can offer a search box in the search result from it.');

  define('MODULE_JSON_LD_BREADCRUMB_TITLE', 'Output the breadcrumb?');
  define('MODULE_JSON_LD_BREADCRUMB_DESC', 'Outputs the trail of the current page as a BreadcrumbList as soon as it has at least two stations.');

  define('MODULE_JSON_LD_PRODUCT_TITLE', 'Output product data?');
  define('MODULE_JSON_LD_PRODUCT_DESC', 'Outputs a Product object with description, images, model, EAN, manufacturer, rating and price on the product page. Without the price right of the customer group the offer is left out.');

  define('MODULE_JSON_LD_LISTING_TITLE', 'Output product listings?');
  define('MODULE_JSON_LD_LISTING_DESC', 'Outputs category, search, special offer and new product listings as an ItemList. <b>summary</b> names only link and name per article and keeps the page head small. <b>full</b> repeats image, model and price as well, so a service does not have to call every product page on its own. <b>false</b> switches the output off.');

  define('MODULE_JSON_LD_DESCRIPTION_LENGTH_TITLE', 'Length of the product description');
  define('MODULE_JSON_LD_DESCRIPTION_LENGTH_DESC', 'Maximum number of characters taken from the product description into the JSON-LD. 0 passes the complete description.');

  define('MODULE_JSON_LD_PRETTY_TITLE', 'Format readably?');
  define('MODULE_JSON_LD_PRETTY_DESC', 'Outputs the JSON with line breaks and indentation. That eases the check in the source code but enlarges every page. Switch it off in live operation.');
