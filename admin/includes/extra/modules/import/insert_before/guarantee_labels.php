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

  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && $this->FileSheme['p_garan_duration'] == 'Y'
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_validate_product.inc.php');

    $guarantee_labels_data = array(
      'products_garan_duration' => xtc_db_prepare_input($dataArray['p_garan_duration']),
      'products_manufacturers_model' => '',
    );
    $guarantee_labels_post = array('manufacturers_id' => 0);

    // the csv may carry manufacturer and model, otherwise the stored article values decide
    if (isset($products_array['products_manufacturers_model'])) {
      $guarantee_labels_data['products_manufacturers_model'] = $products_array['products_manufacturers_model'];
    }

    if (isset($products_array['manufacturers_id'])) {
      $guarantee_labels_post['manufacturers_id'] = $products_array['manufacturers_id'];
    }

    if (!isset($products_array['products_manufacturers_model']) || !isset($products_array['manufacturers_id'])) {
      $guarantee_labels_query = xtc_db_query("SELECT manufacturers_id,
                                                     products_manufacturers_model
                                                FROM ".TABLE_PRODUCTS."
                                               WHERE products_model = '".xtc_db_input($dataArray['p_model'])."'");

      if (xtc_db_num_rows($guarantee_labels_query) > 0) {
        $guarantee_labels_product = xtc_db_fetch_array($guarantee_labels_query);

        if (!isset($products_array['products_manufacturers_model'])) {
          $guarantee_labels_data['products_manufacturers_model'] = $guarantee_labels_product['products_manufacturers_model'];
        }

        if (!isset($products_array['manufacturers_id'])) {
          $guarantee_labels_post['manufacturers_id'] = $guarantee_labels_product['manufacturers_id'];
        }
      }
    }

    $guarantee_labels_result = guarantee_labels_validate_product($guarantee_labels_data, $guarantee_labels_post);

    // A rejected value never reaches the column. Leaving it out of the array means an update
    // does not touch what the article already holds, and a new article keeps the default of
    // the column. Writing the checked value would replace a good stored duration with NULL.
    if (count($guarantee_labels_result['errors']) < 1) {
      $products_array = array_merge($products_array, array('products_garan_duration' => $guarantee_labels_result['data']['products_garan_duration']));
    }

    // the import has no redirect, so the message belongs to the current request
    foreach ($guarantee_labels_result['errors'] as $guarantee_labels_error) {
      $messageStack->add(sprintf(ERROR_GUARANTEE_LABELS_IMPORT, encode_htmlspecialchars($dataArray['p_model']), $guarantee_labels_error), 'error');
    }
  }
