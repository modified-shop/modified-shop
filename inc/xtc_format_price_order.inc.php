<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   by Mario Zanier for XTcommerce
   
   based on:
   (c) 2003	 nextcommerce (xtc_format_price.inc.php,v 1.7 2003/08/19); www.nextcommerce.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // include needed functions
  require_once (DIR_FS_INC.'xtc_format_price.inc.php');

  function xtc_format_price_order($price_string, $price_special, $currency, $show_currencies = 1) {
    return xtc_format_price($price_string, $price_special, $currency, $show_currencies);
  }
