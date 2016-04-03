<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_pull_down_menu.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $


   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_draw_pull_down_menu.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_draw_pull_down_menu.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
// Output a form pull down menu
  function xtc_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if (xtc_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = $GLOBALS[$name];
    
    if (is_array($values) && count($values) > 0) {
      foreach ($values as $value) {
        $field .= '<option value="' . xtc_parse_input_field_data($value['id'], array('"' => '&quot;')) . '"';
        if ($default == $value['id']) {
          $field .= ' selected="selected"';
        }

        $field .= '>' . xtc_parse_input_field_data($value['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
      }
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
  
  function xtc_draw_pull_down_menuNote($data, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . xtc_parse_input_field_data($data['name'], array('"' => '&quot;')) . '"';

    if (xtc_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$data['name']])) $default = $GLOBALS[$data['name']];

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . xtc_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . xtc_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>'. (isset($data['text']) ? $data['text'] : '');

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

 ?>