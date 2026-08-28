<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * guarantee_labels_active()
   *
   * Whether the shop outputs EU labels for the current visitor. Guests count as B2C until
   * their customer group is listed as B2B.
   *
   * @return bool
   */
  function guarantee_labels_active() {
    if (!defined('MODULE_GUARANTEE_LABELS_STATUS') || MODULE_GUARANTEE_LABELS_STATUS != 'true') {
      return false;
    }

    if (!defined('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS') || trim(MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS) === '') {
      return true;
    }

    $status = isset($_SESSION['customers_status']['customers_status_id']) ? (int)$_SESSION['customers_status']['customers_status_id'] : 0;
    $b2b = array_map('intval', array_filter(array_map('trim', explode(',', MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS)), 'strlen'));

    return !in_array($status, $b2b, true);
  }

  /**
   * guarantee_labels_candidate()
   *
   * Whether a product row could carry a label at all. Checked before the manufacturer names
   * are loaded, so a page without qualifying articles causes no extra query.
   *
   * @param array $product one product row of a storefront query
   * @return bool
   */
  function guarantee_labels_candidate($product) {
    if (!isset($product['products_garan_duration']) || $product['products_garan_duration'] === null) {
      return false;
    }

    if (empty($product['manufacturers_id']) || !isset($product['products_manufacturers_model']) || trim((string)$product['products_manufacturers_model']) === '') {
      return false;
    }

    require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    return $renderer->qualifies($product['products_garan_duration']);
  }

  /**
   * guarantee_labels_manufacturer_names()
   *
   * Loads the names of active manufacturers for a whole result block with one query instead of
   * one query per article. Names already delivered by another query are deliberately not used:
   * only this lookup guarantees that the manufacturer is active.
   *
   * @param array $manufacturers_ids
   * @return array manufacturer id to name, missing for an unknown or inactive manufacturer
   */
  function guarantee_labels_manufacturer_names($manufacturers_ids) {
    static $known = array();

    $names = array();
    $missing = array();

    foreach ((array)$manufacturers_ids as $manufacturers_id) {
      $manufacturers_id = (int)$manufacturers_id;

      if ($manufacturers_id < 1) {
        continue;
      }

      if (array_key_exists($manufacturers_id, $known)) {
        if ($known[$manufacturers_id] !== false) {
          $names[$manufacturers_id] = $known[$manufacturers_id];
        }
        continue;
      }

      $missing[$manufacturers_id] = $manufacturers_id;
    }

    if (count($missing) > 0) {
      $manufacturers_query = xtc_db_query("SELECT manufacturers_id,
                                                  manufacturers_name
                                             FROM ".TABLE_MANUFACTURERS."
                                            WHERE manufacturers_id IN (".implode(', ', $missing).")
                                              AND manufacturers_status = '1'");
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
        $names[(int)$manufacturers['manufacturers_id']] = $manufacturers['manufacturers_name'];
        $known[(int)$manufacturers['manufacturers_id']] = $manufacturers['manufacturers_name'];
      }

      // remember the misses as well, so a block full of inactive manufacturers asks only once
      foreach ($missing as $manufacturers_id) {
        if (!array_key_exists($manufacturers_id, $known)) {
          $known[$manufacturers_id] = false;
        }
      }
    }

    return $names;
  }

  /**
   * guarantee_labels_collect_manufacturers()
   *
   * Gathers the manufacturers of every article of a result block that could carry a label.
   *
   * @param array $products rows of a storefront query
   * @return array manufacturer id to name
   */
  function guarantee_labels_collect_manufacturers($products) {
    $manufacturers_ids = array();

    foreach ((array)$products as $product) {
      if (guarantee_labels_candidate($product)) {
        $manufacturers_ids[(int)$product['manufacturers_id']] = (int)$product['manufacturers_id'];
      }
    }

    if (count($manufacturers_ids) < 1) {
      return array();
    }

    return guarantee_labels_manufacturer_names($manufacturers_ids);
  }

  /**
   * guarantee_labels_product_label()
   *
   * Builds both label variants for one article of a result block.
   *
   * @param array $product one product row
   * @param array $names the manufacturer names of the block
   * @return mixed array with hash, colour.svg and nested.svg, false when no label applies
   */
  function guarantee_labels_product_label($product, $names) {
    if (!guarantee_labels_candidate($product)) {
      return false;
    }

    $manufacturers_id = (int)$product['manufacturers_id'];

    if (!isset($names[$manufacturers_id])) {
      return false;
    }

    require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    return $renderer->label($names[$manufacturers_id], $product['products_manufacturers_model'], $product['products_garan_duration']);
  }
