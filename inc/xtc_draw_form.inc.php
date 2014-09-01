<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_form.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_draw_form.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Output a form
  function xtc_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form id="' . xtc_parse_input_field_data($name, array('"' => '&quot;')) . '" action="' . xtc_parse_input_field_data($action, array('"' => '&quot;')) . '" method="' . xtc_parse_input_field_data($method, array('"' => '&quot;')) . '"';

    if (xtc_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }
 ?>