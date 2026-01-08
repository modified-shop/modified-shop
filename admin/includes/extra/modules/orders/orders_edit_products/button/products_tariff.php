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
    echo '<br><a class="button" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, xtc_get_all_get_params(array('edit_action', 'pID')).'edit_action=custom&subaction=tariff&pID='.$order->products[$i]['id'].'&opID='.$order->products[$i]['opid']).'">' . BUTTON_PRODUCTS_TARIFF . '</a>';
  }