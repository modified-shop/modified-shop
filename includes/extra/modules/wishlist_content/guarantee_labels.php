<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // always assigned, so a template can place {$module_data.GUARANTEE_LABEL} without asking
  // whether the module is installed
  $module_data[$i]['GUARANTEE_LABEL'] = '';

  // The module status first: these files ship with the module, the configuration does not.
  // The template variables are assigned either way, only the module code stays unloaded.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');
  }

  if (function_exists('guarantee_labels_active') && guarantee_labels_active()) {
    // The whole block is in scope here, so the manufacturers of every position are read
    // once. Without this the buffer still holds each name, but a page full of different
    // manufacturers would ask once per position.
    if (!isset($guarantee_labels_collected)) {
      $guarantee_labels_collected = array();

      foreach ((array)$module_data as $guarantee_labels_row) {
        if (isset($guarantee_labels_row['PRODUCTS_MANUFACTURERS_ID'])) {
          $guarantee_labels_collected[] = (int)$guarantee_labels_row['PRODUCTS_MANUFACTURERS_ID'];
        }
      }

      guarantee_labels_manufacturer_names($guarantee_labels_collected);
    }

    // The wishlist runs on shoppingCart as well, so it carries the same fields ADD_SELECT_CART
    // adds. It offers a direct way into the cart, which is why it is labelled like a list.
    $guarantee_labels_product = array(
      'products_id' => isset($module_data[$i]['PRODUCTS_ID']) ? $module_data[$i]['PRODUCTS_ID'] : 0,
      'products_garan_duration' => isset($module_data[$i]['PRODUCTS_GARAN_DURATION']) ? $module_data[$i]['PRODUCTS_GARAN_DURATION'] : null,
      'products_manufacturers_model' => isset($module_data[$i]['PRODUCTS_MANUFACTURERS_MODEL']) ? $module_data[$i]['PRODUCTS_MANUFACTURERS_MODEL'] : '',
      'manufacturers_id' => isset($module_data[$i]['PRODUCTS_MANUFACTURERS_ID']) ? $module_data[$i]['PRODUCTS_MANUFACTURERS_ID'] : 0,
    );

    // the cart id carries the chosen attributes, so the position decides and not the article
    if (guarantee_labels_candidate($guarantee_labels_product, $guarantee_labels_product['products_id'])) {
      $guarantee_labels_names = guarantee_labels_manufacturer_names(array($guarantee_labels_product['manufacturers_id']));
      $module_data[$i]['GUARANTEE_LABEL'] = guarantee_labels_markup(guarantee_labels_product_label($guarantee_labels_product, $guarantee_labels_names, $guarantee_labels_product['products_id']));
    }
  }
