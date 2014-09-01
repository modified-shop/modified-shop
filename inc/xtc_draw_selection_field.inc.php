<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_selection_field.inc.php 812 2005-02-27 20:55:34Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce 
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_draw_selection_field.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
  
// Output a selection field - alias function for xtc_draw_checkbox_field() and xtc_draw_radio_field()

  function xtc_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . xtc_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if (xtc_not_null($value)) $selection .= ' value="' . xtc_parse_input_field_data($value, array('"' => '&quot;')) . '"';
    
    //BOF - DokuMan - 2010-08-23 - set undefined index
    //if ( ($checked == true) || ($GLOBALS[$name] == 'on') || ( (isset($value)) && ($GLOBALS[$name] == $value) ) ) {
    if ( ($checked == true) || (isset($GLOBALS[$name]) && $GLOBALS[$name] == 'on') || ( (isset($value)) && (isset($GLOBALS[$name]) && $GLOBALS[$name] == $value) ) ) {
    //EOF - DokuMan - 2010-08-23 - set undefined index
      $selection .= ' checked="checked"';
    }

    if (xtc_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';

    return $selection;
  }
  
    function xtc_draw_selection_fieldNote($data, $type, $value = '', $checked = false, $parameters = '') {
    $selection = $data['suffix'].'<input type="' . xtc_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . xtc_parse_input_field_data($data['name'], array('"' => '&quot;')) . '"';

    if (xtc_not_null($value)) $selection .= ' value="' . xtc_parse_input_field_data($value, array('"' => '&quot;')) . '"';
    //BOF - DokuMan - 2010-09-17 - set undefined index
    //if ( ($checked == true) || ($GLOBALS[$data['name']] == 'on') || ( (isset($value)) && ($GLOBALS[$data['name']] == $value) ) ) {
    if ( ($checked == true) || (isset($GLOBALS[$data['name']]) && ($GLOBALS[$data['name']] == 'on')) || ( (isset($value)) && (isset($GLOBALS[$data['name']]) && ($GLOBALS[$data['name']] == $value) ) ) ) {
    //EOF - DokuMan - 2010-09-17 - set undefined index
      $selection .= ' checked="checked"';
    }

    if (xtc_not_null($parameters)) $selection .= ' ' . $parameters;

    //BOF - DokuMan - 2010-09-17 - set undefined index: text
    //$selection .= ' />'.$data['text'];
    $selection .= ' />';
    if (isset($data['text'])) $selection .= $data['text'];
    //EOF - DokuMan - 2010-09-17 - set undefined index: text

    return $selection;
  }
 ?>