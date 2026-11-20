<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'xtc_remove_active_content.inc.php');

  /**
   * clean_html_output()
   *
   * a stored text for HTML output: active content is removed only here,
   * so the stored value stays usable as a key, e.g. the tax description
   *
   * @param string $value
   * @param int $max_length characters the text may have where it is stored again, e.g. as title of an order total
   * @return string
   */
  function clean_html_output($value, $max_length = 0) {
    if (!is_string($value) || strpos($value, '<') === false) {
      return $value;
    }
    if (!xtc_remove_active_content_available()) {
      return encode_htmlspecialchars($value);
    }

    $removed = false;

    // with $max_length a text that would be longer loses its tags instead of being cut off by the column
    return xtc_remove_active_content_keep($value, $removed, $max_length);
  }

  // characters as the database counts them
  function clean_html_output_length($value) {
    return xtc_remove_active_content_length($value);
  }
?>
