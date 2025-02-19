<?php
/**
 * SMARTY PLUGIN: ONLY TEXT
 *
 * @version    Release: 1.0
 *
 * @author     H. H. Hacker <dev@hackersolutions.com>
 * @copyright  2010-2013 Hacker Solutions
 * @link       http://www.hackersolutions.com
 *
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPLv2
 * 
 * Description:
 * removes html code and single and double quotes
 *
 * Example of use:
 * <code>
 * <img src="{$PRODUCTS_IMAGE}" alt="{$PRODUCTS_NAME|onlytext}"..
 * </code>
 */


/**
 * Function: remove quotes and html
 * @param string
 * @return string
 */
function smarty_modifier_onlytext($string) {
  if (xtc_not_null($string)) {
    $string = strip_tags($string);
    $string = preg_replace("'[\r\n\s]+'", ' ', $string);
    $string = str_replace(array('"', "'"), array('&quote;', '&#39;'), $string);
    $string = trim($string);
  }
  
  return $string;
}
