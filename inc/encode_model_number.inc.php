<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'get_joined_model_parts.inc.php');

  // a model number is text; $attribute_models are the attribute numbers a joined number can be made of
  function encode_model_number($model, $attribute_models = array(), $flags = ENT_QUOTES) {
    $model = (string)$model;
    // the characters to encode have the same bytes in every charset of the shop, so the bytes of the
    // value stay as stored, whatever the charset of the page or mail is; entities of HTML5 count as existing
    $flags = $flags | ENT_HTML5;
    $charset = 'ISO-8859-1';

    // only the delimiter between the joined attribute numbers stays markup
    $joined = get_joined_model_parts($model, $attribute_models);
    if ($joined !== false) {
      $encoded = array();
      foreach ($joined['parts'] as $part) {
        $encoded[] = htmlspecialchars($part, $flags, $charset, false);
      }

      return implode($joined['delimiter'], $encoded);
    }

    return htmlspecialchars($model, $flags, $charset, false);
  }
