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
    if ($this->FileSheme['p_tariff'] == 'Y')
        $products_array = array_merge($products_array, array ('products_tariff' => (int)$dataArray['p_tariff']));
    if ($this->FileSheme['p_tariff_title'] == 'Y')
        $products_array = array_merge($products_array, array ('products_tariff_title' => (int)$dataArray['p_tariff_title']));
    if ($this->FileSheme['p_origin'] == 'Y')
        $products_array = array_merge($products_array, array ('products_origin' => (int)$dataArray['p_origin']));
  }