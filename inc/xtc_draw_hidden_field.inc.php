<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_hidden_field.inc.php 4306 2013-01-14 07:08:36Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_draw_hidden_field.inc.php,v 1.3 2003/08/1); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_draw_hidden_field.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   function xtc_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '" value="';

    if (xtc_not_null($value)) {
      $field .= xtc_parse_input_field_data($value, array('"' => '&quot;'));
    } else {
      $field .= xtc_parse_input_field_data((array_key_exists($name, $GLOBALS) ?  $GLOBALS[$name] : NULL), array('"' => '&quot;'));
    }

    $field .= xtc_not_null($parameters) ? '" '.$parameters : '"';

    $field .= ' />';

    return $field;
   }
?>