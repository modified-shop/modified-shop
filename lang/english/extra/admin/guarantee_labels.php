<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('TEXT_GUARANTEE_LABELS_HEADING', 'EU durability guarantee:');
  define('TEXT_GUARANTEE_LABELS_MANUFACTURER', 'Manufacturer/guarantor:');
  define('TEXT_GUARANTEE_LABELS_MODEL', 'Manufacturer model identifier:');
  define('TEXT_GUARANTEE_LABELS_DURATION', 'Guarantee duration in years:');
  define('TEXT_GUARANTEE_LABELS_TERMS', 'Guarantee conditions:');

  define('TEXT_GUARANTEE_LABELS_NONE', 'not selected');
  define('TEXT_GUARANTEE_LABELS_TERMS_MISSING', 'no attachment of type GARAN guarantee conditions');

  define('TEXT_GUARANTEE_LABELS_INFO', 'This field is only for a durability guarantee of the manufacturer that goes beyond the legal guarantee. Do not enter the statutory two years here: the shop points them out in the checkout for the whole order without anything maintained on the article. An empty field is therefore the normal case and not a defect.');
  define('TEXT_GUARANTEE_LABELS_INFO_RULES', 'Only enter a duration when the manufacturer offers a guarantee that is free of charge for the customer, covers the whole product, runs longer than two years and applies with the same duration and the same conditions in every country you deliver to. Exactly two years is not enough, because such a guarantee gives the customer nothing beyond the legal guarantee. Whole and half years from 2.5 on are allowed, for example 3 or 2.5.');
  define('TEXT_GUARANTEE_LABELS_INFO_TERMS', 'Maintain the complete guarantee conditions as an article attachment of type GARAN guarantee conditions. Without them the label is still shown, but the conditions are not attached to the order confirmation.');

  define('ERROR_GUARANTEE_LABELS_DURATION', 'EU durability guarantee: &quot;%s&quot; is not a valid guarantee duration. Allowed are whole and half years from 2.5 on, at most 99 whole or 9.5 half years. The legal guarantee of two years does not belong into this field; it is shown in the checkout anyway. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER', 'EU durability guarantee: no active manufacturer is selected. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MODEL', 'EU durability guarantee: the manufacturer model identifier is empty. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH', 'EU durability guarantee: the manufacturer name &quot;%s&quot; does not fit into the editable area of the official template. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MODEL_WIDTH', 'EU durability guarantee: the model identifier &quot;%s&quot; does not fit into the editable area of the official template. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_NOT_READY', 'EU durability guarantee: the module cannot render a label yet (%s). The guarantee duration has not been saved.');

  define('ERROR_GUARANTEE_LABELS_IMPORT', 'Article %s &ndash; %s');

  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE', 'Usage:');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_DEFAULT', 'Standard');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_TERMS', 'GARAN guarantee conditions');
