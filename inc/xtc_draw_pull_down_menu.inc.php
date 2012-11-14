<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
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

    //BOF - DokuMan - 2011-12-19 - precount for performance
    //for ($i=0, $n=sizeof($values); $i<$n; $i++) {
    $n=sizeof($values);
    for ($i=0; $i<$n; $i++) {
    //EOF - DokuMan - 2011-12-19 - precount for performance
      $field .= '<option value="' . xtc_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }
      //BOF - h-h-h - 2011-05-02 - add parameters for options
      if (!empty($values[$i]['parameters'])) $field .= ' ' . $values[$i]['parameters'];
      //EOF - h-h-h - 2011-05-02 - add parameters for options
      $field .= '>' . xtc_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
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

    //BOF - DokuMan - 2011-12-19 - precount for performance
    //for ($i=0, $n=sizeof($values); $i<$n; $i++) {
    $n=sizeof($values);
    for ($i=0; $i<$n; $i++) {
    //EOF - DokuMan - 2011-12-19 - precount for performance
      $field .= '<option value="' . xtc_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }
      $field .= '>' . xtc_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>'.$data['text'];

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
?>