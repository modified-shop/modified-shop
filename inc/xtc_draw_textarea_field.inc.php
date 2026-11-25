<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_textarea_field.inc.php 1300 2005-10-10 20:40:33Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_draw_textarea_field.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Output a form textarea field
  function xtc_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '" id="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '" cols="' . xtc_parse_input_field_data($width, array('"' => '&quot;')) . '" rows="' . xtc_parse_input_field_data($height, array('"' => '&quot;')) . '"';

    if (xtc_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    // the content of a textarea is text, so markup and entities are encoded and come back as typed;
    // an invalid byte becomes U+FFFD instead of emptying the text
    if ( (isset($GLOBALS[$name])) && !is_array($GLOBALS[$name]) && !is_object($GLOBALS[$name]) && ($reinsert_value == true) ) {
      $field .= encode_htmlspecialchars((string)$GLOBALS[$name], ENT_COMPAT | ENT_SUBSTITUTE);
    } elseif (xtc_not_null($text)) {
      $field .= encode_htmlspecialchars((string)$text, ENT_COMPAT | ENT_SUBSTITUTE);
    }

    $field .= '</textarea>';

    return $field;
  }
 ?>