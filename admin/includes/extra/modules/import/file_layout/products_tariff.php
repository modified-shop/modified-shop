<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_PRODUCTS_TARIFF_STATUS')
      && MODULE_PRODUCTS_TARIFF_STATUS == 'true'
      )
  {
    $file_layout = array_merge($file_layout, array ('p_tariff' => ''));
    $file_layout = array_merge($file_layout, array ('p_tariff_title' => ''));
    $file_layout = array_merge($file_layout, array ('p_origin' => ''));
  }