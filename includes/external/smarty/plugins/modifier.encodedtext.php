<?php
/**
 * SMARTY PLUGIN: ENCODEDTEXT
 *
 * @version    Release: 1.0
 *
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPLv2
 *
 * Description:
 * Encodes a value for a double-quoted HTML attribute. The visible text stays
 * as it is: only the double quote becomes &quot;, existing entities and all
 * other characters are kept. Unlike onlytext it removes nothing, so a text
 * like 'Cable <10 m' keeps its full length.
 *
 * Example of use:
 * <code>
 * <img src="{$SHIPPING_IMAGE}" alt="{$SHIPPING_NAME|encodedtext}" />
 * </code>
 */


/**
 * Function: encode a value for a double-quoted attribute
 * @param mixed $string
 * @return mixed
 */
function smarty_modifier_encodedtext($string) {
  if (!is_string($string)) {
    return $string;
  }

  return str_replace('"', '&quot;', $string);
}
