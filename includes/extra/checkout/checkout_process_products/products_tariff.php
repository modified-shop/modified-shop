<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_PRODUCTS_TARIFF_STATUS')
      && MODULE_PRODUCTS_TARIFF_STATUS == 'true'
      )
  {
    $sql_data_array['products_origin'] = $order->products[$i]['origin'];
    $sql_data_array['products_tariff'] = $order->products[$i]['tariff'];
    $sql_data_array['products_tariff_title'] = $order->products[$i]['tariff_title'];
  }
