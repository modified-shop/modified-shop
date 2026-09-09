<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  // the tables survive an uninstall, so the button follows the module status
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    echo '<br><a class="button" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, xtc_get_all_get_params(array('edit_action', 'pID', 'subaction')).'edit_action=custom&subaction=guarantee&pID='.$order->products[$i]['id'].'&opID='.$order->products[$i]['opid']).'">'.BUTTON_GUARANTEE_LABELS_EDIT.'</a>';
  }
