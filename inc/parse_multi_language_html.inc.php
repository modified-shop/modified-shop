<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'parse_multi_language_value.inc.php');
  require_once(DIR_FS_INC.'clean_html_output.inc.php');

  /**
   * parse_multi_language_html()
   *
   * the language value for HTML output: parse_multi_language_value() decodes entities,
   * so an encoded tag from the database is cleaned only after decoding
   *
   * @param string $text stored multi language value
   * @param string $lang_code language code
   * @param boolean $admin see parse_multi_language_value()
   * @param int $max_length see clean_html_output()
   * @return string
   */
  function parse_multi_language_html($text, $lang_code, $admin = false, $max_length = 0) {
    return clean_html_output(parse_multi_language_value($text, $lang_code, $admin), $max_length);
  }
?>
