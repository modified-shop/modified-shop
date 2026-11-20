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
    $clean = xtc_remove_active_content_keep($value, $removed);

    // the cleaner can write a text longer than it was: rather without its tags than cut off by the column
    if ($max_length > 0 && $clean !== $value && clean_html_output_length($clean) > $max_length) {
      $plain = preg_replace('/&amp;(?![a-z0-9#])/i', '&', strip_tags($clean));
      $clean = ((is_string($plain)) ? $plain : strip_tags($clean));
    }

    return $clean;
  }

  // characters as the database counts them
  function clean_html_output_length($value) {
    if (function_exists('mb_strlen') && function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
      return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
  }
?>
