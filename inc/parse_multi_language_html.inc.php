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
    return clean_html_output(parse_multi_language_html_value($text, $lang_code, $admin), $max_length);
  }

  /**
   * parse_multi_language_html_value()
   *
   * the language value as HTML source, not cleaned, e.g. for the input field of a form:
   * like parse_multi_language_value(), but an encoded & stays encoded where a page would read the & and
   * the text behind it as a character although no semicolon ends it, e.g. &amp;notices
   *
   * @param string $text stored multi language value
   * @param string $lang_code language code
   * @param boolean $admin see parse_multi_language_value()
   * @return string
   */
  function parse_multi_language_html_value($text, $lang_code, $admin = false) {
    if (is_string($text) && strpos($text, '&') !== false) {
      $parts = preg_split('/(&(?:#[0-9]+|#[xX][0-9a-fA-F]+|[a-zA-Z0-9]+);)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
      if (is_array($parts) && count($parts) > 1) {
        // from the end, so the decoded text behind an entity is known
        $behind = '';
        for ($i = count($parts) - 1; $i >= 0; $i--) {
          $decoded = (($i % 2 != 0) ? decode_htmlentities($parts[$i]) : $parts[$i]);
          if ($i % 2 != 0 && $decoded === '&') {
            $reference = xtc_remove_active_content_reference('&'.$behind);
            if ($reference !== false && substr($behind, $reference[0] - 2, 1) != ';') {
              // comes back from the decoder as &amp;
              $parts[$i] = '&amp;amp;';
            }
          }
          $behind = $decoded.$behind;
        }
        $text = implode('', $parts);
      }
    }

    return parse_multi_language_value($text, $lang_code, $admin);
  }
?>
