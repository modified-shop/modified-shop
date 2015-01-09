<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_draw_radio_field_installer.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.1 2002/01/02); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_draw_radio_field_installer.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
  
  require_once(DIR_FS_INC . 'xtc_draw_selection_field_installer.inc.php');
    
  function xtc_draw_radio_field_installer($name, $value = '', $checked = false) {
    return xtc_draw_selection_field_installer($name, 'radio', $value, $checked);
  }
 ?>