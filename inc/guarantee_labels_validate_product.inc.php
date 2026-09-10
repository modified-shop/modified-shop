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
   * guarantee_labels_validate_product()
   *
   * Normalises and checks the GARAN data of one article before it is saved. Article
   * administration and import share this function, so both accept exactly the same values.
   *
   * An empty duration is allowed and clears the label. As soon as a duration is entered the
   * whole core data set has to be valid, otherwise the article would show an incomplete label.
   *
   * A rejected input never reaches the columns. All three GARAN core fields are removed from
   * the data instead, so an update leaves the stored ones untouched and a new article keeps the
   * column defaults. The article administration writes the row before it learns about the error;
   * letting a part of the set through would either delete a good stored guarantee or leave the
   * article with a duration and no manufacturer behind it.
   *
   * @param array $sql_data_array the prepared product data
   * @param array $products_data the posted or imported values
   * @return array data with the normalised value and errors as ready to use messages
   */
  function guarantee_labels_validate_product($sql_data_array, $products_data) {
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $errors = array();
    $duration = isset($sql_data_array['products_garan_duration']) ? trim((string)$sql_data_array['products_garan_duration']) : '';

    if ($duration === '') {
      // xtc_db_perform() turns the string null into a real NULL
      $sql_data_array['products_garan_duration'] = 'null';
      return array('data' => $sql_data_array, 'errors' => $errors);
    }

    $renderer = new guarantee_labels_renderer();
    $normalized = $renderer->normalize_duration($duration);

    if ($normalized === false) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_DURATION, encode_htmlspecialchars($duration));
      return array('data' => guarantee_labels_keep_stored($sql_data_array, $products_data), 'errors' => $errors);
    }

    $sql_data_array['products_garan_duration'] = $normalized;

    // exactly two years is stored as documentation and never produces a label, so the data the
    // label would need is not demanded here; an external system may deliver a plain 2
    if (!$renderer->qualifies($normalized)) {
      return array('data' => $sql_data_array, 'errors' => $errors);
    }

    $manufacturers_id = isset($products_data['manufacturers_id']) ? (int)$products_data['manufacturers_id'] : 0;
    $manufacturers_name = '';

    // The one place that knows what an active manufacturer is, and it buffers: an import of five
    // thousand rows asked five thousand times before.
    if ($manufacturers_id > 0) {
      require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

      $manufacturers = guarantee_labels_manufacturer_names(array($manufacturers_id));
      $manufacturers_name = isset($manufacturers[$manufacturers_id]) ? $manufacturers[$manufacturers_id] : '';
    }

    $model = isset($sql_data_array['products_manufacturers_model']) ? trim((string)$sql_data_array['products_manufacturers_model']) : '';

    $errors = array_merge($errors, guarantee_labels_validate_texts($renderer, $manufacturers_name, $model, ERROR_GUARANTEE_LABELS_MANUFACTURER));

    if (count($errors) > 0) {
      $sql_data_array = guarantee_labels_keep_stored($sql_data_array, $products_data);
    }

    return array('data' => $sql_data_array, 'errors' => $errors);
  }

  /**
   * The errors the two texts of a label produce.
   *
   * Article administration, import and the order position mask ask here, so no path accepts a
   * text another one refuses. Only the message for a missing manufacturer differs: the position
   * mask carries the name itself, the article takes it from the manufacturer of the catalogue.
   *
   * @param object $renderer
   * @param string $manufacturers_name already trimmed
   * @param string $model already trimmed
   * @param string $manufacturer_missing the message for an empty manufacturer
   * @return array ready to use messages
   */
  function guarantee_labels_validate_texts($renderer, $manufacturers_name, $model, $manufacturer_missing) {
    $errors = array();

    if ($manufacturers_name === '') {
      $errors[] = $manufacturer_missing;
    }

    if ($model === '') {
      $errors[] = ERROR_GUARANTEE_LABELS_MODEL;
    }

    // Save, import and order editing must use the same shared 9 pt row as the renderer.
    // Checking the old separate columns would still reject identifiers that now fit.
    if ($manufacturers_name !== '' && $model !== '' && $renderer->is_ready()
        && !$renderer->fits_texts($renderer->measurable($manufacturers_name), $renderer->measurable($model))) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_TEXT_WIDTH, encode_htmlspecialchars($manufacturers_name), encode_htmlspecialchars($model));
    }

    if (!$renderer->is_ready()) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_NOT_READY, implode(', ', $renderer->missing_requirements()));
    }

    return $errors;
  }

  /**
   * Takes the GARAN core fields out of a data set so a rejected save leaves them as they are.
   *
   * The three belong together. Writing the manufacturer of a rejected input while keeping the
   * stored duration would turn a complete article into an incomplete one, which is exactly what
   * the check was meant to prevent.
   *
   * @param array $sql_data_array
   * @return array
   */
  function guarantee_labels_keep_stored($sql_data_array, $products_data = array()) {
    // A new article has no stored values to protect. Dropping all three would create it without
    // a manufacturer and without a model identifier, and the admin would have to type them again
    // although only the duration was refused.
    $stored = (isset($products_data['products_id']) && (int)$products_data['products_id'] > 0);

    if ($stored) {
      unset($sql_data_array['products_manufacturers_model'], $sql_data_array['manufacturers_id']);
    }

    unset($sql_data_array['products_garan_duration']);

    return $sql_data_array;
  }
