<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

  // always assigned, so a template can place {$module_data.GUARANTEE_LABEL} without asking
  // whether the module is installed
  $module_content[$i]['GUARANTEE_LABEL'] = '';

  if (guarantee_labels_active()) {
    // the cart strips the products_ prefix, order_details_cart puts PRODUCTS_ in front again
    $guarantee_labels_product = array(
      'products_id' => isset($module_content[$i]['PRODUCTS_ID']) ? $module_content[$i]['PRODUCTS_ID'] : 0,
      'products_garan_duration' => isset($module_content[$i]['PRODUCTS_GARAN_DURATION']) ? $module_content[$i]['PRODUCTS_GARAN_DURATION'] : null,
      'products_manufacturers_model' => isset($module_content[$i]['PRODUCTS_MANUFACTURERS_MODEL']) ? $module_content[$i]['PRODUCTS_MANUFACTURERS_MODEL'] : '',
      'manufacturers_id' => isset($module_content[$i]['PRODUCTS_MANUFACTURERS_ID']) ? $module_content[$i]['PRODUCTS_MANUFACTURERS_ID'] : 0,
    );

    // the cart id carries the chosen attributes, so the position decides and not the article
    if (guarantee_labels_candidate($guarantee_labels_product, $guarantee_labels_product['products_id'])) {
      $guarantee_labels_names = guarantee_labels_manufacturer_names(array($guarantee_labels_product['manufacturers_id']));
      $module_content[$i]['GUARANTEE_LABEL'] = guarantee_labels_markup(guarantee_labels_product_label($guarantee_labels_product, $guarantee_labels_names));
    }
  }
