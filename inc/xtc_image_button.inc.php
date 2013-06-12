<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_image_button.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_image_button.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Output a function button in the selected language
  function xtc_image_button($image, $alt = '', $parameters = '', $css_button = true) {
    if ($css_button && defined('USE_CSS_BUTTONS') && USE_CSS_BUTTONS == 'true' && function_exists('xtc_css_button')) {
      return xtc_css_button($image, $alt, $parameters, false);
    } else {    
      return xtc_image('templates/'.CURRENT_TEMPLATE.'/buttons/' . $_SESSION['language'] . '/'. $image, $alt, '', '', $parameters);
    }
  }
 ?>