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
  define('TEXT_GUARANTEE_LABELS_DURATION', 'Guarantee duration in years:');
  define('TEXT_GUARANTEE_LABELS_TERMS', 'Guarantee conditions:');

  define('TEXT_GUARANTEE_LABELS_NONE', 'not entered');
  define('TEXT_GUARANTEE_LABELS_TERMS_MISSING', 'no attachment of type GARAN guarantee conditions');
  define('TEXT_GUARANTEE_LABELS_TERMS_FILE_MISSING', '%s &ndash; file is missing, it will not travel with the order confirmation');
  define('TEXT_GUARANTEE_LABELS_MANUFACTURER_INACTIVE', 'manufacturer is not active, so no label is created');
  define('TEXT_GUARANTEE_LABELS_VIRTUAL', 'This article holds digital content only. The guarantee of durability belongs to goods, so no label is created.');

  define('TEXT_GUARANTEE_LABELS_INFO', 'This field holds the duration of the durability guarantee of the manufacturer, in whole or half years. It is independent of the legal guarantee: the shop points that one out in the checkout for the whole order without anything maintained on the article.');
  define('TEXT_GUARANTEE_LABELS_INFO_RULES', 'A GARAN label starts at 2.5 years. Shorter periods are stored and record what the manufacturer grants; they produce no label, because a guarantee of up to two years gives the customer nothing on top of the legal guarantee. A label also requires the guarantee to be free of charge, to cover the whole product and to apply with the same duration and the same conditions in every country you deliver to. An empty field means that nothing is recorded about a manufacturer guarantee.');
  define('TEXT_GUARANTEE_LABELS_INFO_TERMS', 'Maintain the complete guarantee conditions as an article attachment of type GARAN guarantee conditions. Without them the label is still shown, but the conditions are not attached to the order confirmation.');

  define('ERROR_GUARANTEE_LABELS_DURATION', 'EU durability guarantee: &quot;%s&quot; is not a valid guarantee duration. Allowed are whole and half years from 0.5 up to 99.5. The practical guidelines do not provide for decimals other than 5. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER', 'EU durability guarantee: no active manufacturer is selected. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MODEL', 'EU durability guarantee: the manufacturer model identifier is empty. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MANUFACTURER_WIDTH', 'EU durability guarantee: the manufacturer name &quot;%s&quot; does not fit into the editable area of the official template. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_MODEL_WIDTH', 'EU durability guarantee: the model identifier &quot;%s&quot; does not fit into the editable area of the official template. The guarantee duration has not been saved.');
  define('ERROR_GUARANTEE_LABELS_NOT_READY', 'EU durability guarantee: the module cannot render a label yet (%s). The guarantee duration has not been saved.');

  define('ERROR_GUARANTEE_LABELS_IMPORT', 'Article %s &ndash; %s');

  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE', 'Usage:');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_DEFAULT', 'Standard');
  define('TEXT_GUARANTEE_LABELS_CONTENT_TYPE_TERMS', 'GARAN guarantee conditions');

  define('BUTTON_GUARANTEE_LABELS_EDIT', 'EU durability guarantee');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HEADING', 'EU durability guarantee of the order position');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_INFO', 'These values belong to the order and are kept with it. A change does not touch the catalogue article. Empty manufacturer, model identifier and duration to take the guarantee off the position.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER', 'Manufacturer/guarantor:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_MODEL', 'Manufacturer model identifier:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_DURATION', 'Guarantee duration in years:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS', 'Guarantee conditions:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_NONE', 'none stored');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_KEEP', 'leave unchanged');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REMOVE', 'remove');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REPLACE', 'replace with:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW', 'Current label of the order position:');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW_NONE', 'This position carries no label.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_FROM_PRODUCT', 'Take over from the article');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_CONFIRM', 'The order confirmation has been sent already. Confirm the change explicitly.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_SAVED', 'The EU durability guarantee of the order position was saved.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_REMOVED', 'The EU durability guarantee was taken off the order position.');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY', 'EU durability guarantee of position %1$s changed: %2$s');
  define('TEXT_GUARANTEE_LABELS_SNAPSHOT_HISTORY_FIELD', '%1$s from &quot;%2$s&quot; to &quot;%3$s&quot;');

  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_DURATION', 'EU durability guarantee: &quot;%s&quot; is not an allowed duration. A label needs whole and half years above two up to 99.5.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER', 'EU durability guarantee: the manufacturer/guarantor is missing.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_UNKNOWN', 'EU durability guarantee: the order position was not found.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_RENDER', 'EU durability guarantee: the label could not be built (%s). The previous state is kept.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_ARCHIVE', 'EU durability guarantee: the label could not be archived (%s). The previous state is kept.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_CONFIRM', 'EU durability guarantee: the change was not confirmed and therefore not saved.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_TERMS', 'EU durability guarantee: the guarantee conditions could not be archived (%s). The previous state is kept.');
  define('ERROR_GUARANTEE_LABELS_SNAPSHOT_NO_PRODUCT', 'EU durability guarantee: the catalogue article carries no complete GARAN data.');

  define('ERROR_GUARANTEE_LABELS_TERMS_LINK', 'GARAN guarantee conditions: a link alone is no durable medium. Store a file instead. The attachment was saved as an ordinary one.');
  define('ERROR_GUARANTEE_LABELS_TERMS_NAME', 'GARAN guarantee conditions: the file name &quot;%s&quot; cannot be used as a mail attachment. The attachment was saved as an ordinary one.');
  define('ERROR_GUARANTEE_LABELS_TERMS_FILE', 'GARAN guarantee conditions: the file &quot;%s&quot; is not stored under media/products/. The attachment was saved as an ordinary one.');
  define('ERROR_GUARANTEE_LABELS_TERMS_DUPLICATE', 'GARAN guarantee conditions: this article and language already carry such an attachment. The attachment was saved as an ordinary one.');
