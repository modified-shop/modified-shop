<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003	 nextcommerce (xtc_draw_input_field.inc.php,v 1.3 2003/08/13); www.nextcommerce.org 
   (c) 2006 XT-Commerce (xtc_draw_input_field.inc.php 899 2005-04-29)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
/**
 * Output a form input field
 *
 * @param String $name name-value
 * @param String $value value
 * @param String $parameters parameters like class, style etc
 * @param String $type type e.g. text, password, submit etc
 * @param boolean $reinsert_value reinsert values if reloading page, needed for validation
 * @return String complete html string
 */
  function xtc_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . xtc_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if ( (isset($GLOBALS[$name])) && ($reinsert_value == true) ) {
      $field .= ' value="' . xtc_parse_input_field_data($GLOBALS[$name], array('"' => '&quot;')) . '"';
    } elseif (xtc_not_null($value)) {
      $field .= ' value="' . xtc_parse_input_field_data($value, array('"' => '&quot;')) . '"';
    }

    if (xtc_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

/**
 * Output a form input field with an optional note at the end
 *
 * @param Array $data Array containing "name" and "text" (= note)
 * @param String $value value
 * @param String $parameters parameters like class, style etc
 * @param String $type type e.g. text, password, submit etc
 * @param boolean $reinsert_value reinsert values if reloading page, needed for validation
 * @return String complete html string
 */  
    function xtc_draw_input_fieldNote($data, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . xtc_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . xtc_parse_input_field_data($data['name'], array('"' => '&quot;')) . '"';

    if ( (isset($GLOBALS[$data['name']])) && ($reinsert_value == true) ) {
      $field .= ' value="' . xtc_parse_input_field_data($GLOBALS[$data['name']], array('"' => '&quot;')) . '"';
    } elseif (xtc_not_null($value)) {
      $field .= ' value="' . xtc_parse_input_field_data($value, array('"' => '&quot;')) . '"';
    }

    if (xtc_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />'.$data['text'];

    return $field;
  }
?>