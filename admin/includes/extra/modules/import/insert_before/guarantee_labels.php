<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  // Also runs without p_garan_duration in the file: a row that only changes manufacturer or
  // model identifier can make a stored duration unusable, and that has to be reported too.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_validate_product.inc.php');

    $guarantee_labels_has_duration = (isset($this->FileSheme['p_garan_duration']) && $this->FileSheme['p_garan_duration'] == 'Y');
    $guarantee_labels_stored = false;

    // the row may carry only part of the core data, the article supplies the rest
    if (!$guarantee_labels_has_duration
        || !isset($products_array['products_manufacturers_model'])
        || !isset($products_array['manufacturers_id'])
        )
    {
      $guarantee_labels_query = xtc_db_query("SELECT manufacturers_id,
                                                     products_manufacturers_model,
                                                     products_garan_duration
                                                FROM ".TABLE_PRODUCTS."
                                               WHERE products_model = '".xtc_db_input($dataArray['p_model'])."'");

      if (xtc_db_num_rows($guarantee_labels_query) > 0) {
        $guarantee_labels_stored = xtc_db_fetch_array($guarantee_labels_query);
      }
    }

    $guarantee_labels_duration = $guarantee_labels_has_duration
                               ? xtc_db_prepare_input($dataArray['p_garan_duration'])
                               : (($guarantee_labels_stored !== false) ? $guarantee_labels_stored['products_garan_duration'] : '');

    $guarantee_labels_data = array(
      'products_garan_duration' => $guarantee_labels_duration,
      'products_manufacturers_model' => isset($products_array['products_manufacturers_model'])
                                      ? $products_array['products_manufacturers_model']
                                      : (($guarantee_labels_stored !== false) ? $guarantee_labels_stored['products_manufacturers_model'] : ''),
    );

    $guarantee_labels_post = array(
      'manufacturers_id' => isset($products_array['manufacturers_id'])
                          ? $products_array['manufacturers_id']
                          : (($guarantee_labels_stored !== false) ? $guarantee_labels_stored['manufacturers_id'] : 0),
    );

    $guarantee_labels_result = guarantee_labels_validate_product($guarantee_labels_data, $guarantee_labels_post);

    if (count($guarantee_labels_result['errors']) < 1) {
      if ($guarantee_labels_has_duration && isset($guarantee_labels_result['data']['products_garan_duration'])) {
        $products_array['products_garan_duration'] = $guarantee_labels_result['data']['products_garan_duration'];
      }
    } else {
      // The three core fields belong together. Importing a new manufacturer or model identifier
      // next to a stored duration would leave an incomplete GARAN article behind, which is what
      // the check exists for. The rest of the row is imported as usual.
      unset($products_array['products_garan_duration'],
            $products_array['products_manufacturers_model'],
            $products_array['manufacturers_id']);
    }

    // the import has no redirect, so the message belongs to the current request
    foreach ($guarantee_labels_result['errors'] as $guarantee_labels_error) {
      $messageStack->add(sprintf(ERROR_GUARANTEE_LABELS_IMPORT, encode_htmlspecialchars($dataArray['p_model']), $guarantee_labels_error), 'error');
    }
  }
