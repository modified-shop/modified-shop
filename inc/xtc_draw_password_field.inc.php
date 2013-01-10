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
   (c) 2003 nextcommerce (xtc_draw_password_field.inc.php,v 1.3 2003/08/1); www.nextcommerce.org 
   (c) 2003 XT-Commerce (xtc_draw_password_field.inc.php); www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Output a form password field

  function xtc_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return xtc_draw_input_field($name, $value, $parameters, 'password', false);
  }

  function xtc_draw_password_fieldNote($name, $value = '', $parameters = 'maxlength="40"') {
    return xtc_draw_input_fieldNote($name, $value, $parameters, 'password', false);
  }

?>