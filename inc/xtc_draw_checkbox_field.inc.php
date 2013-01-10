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
   (c) 2003	 nextcommerce (xtc_draw_checkbox_field.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

   require_once(DIR_FS_INC . 'xtc_draw_selection_field.inc.php');
   
// Output a form checkbox field
  function xtc_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return xtc_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }
?>