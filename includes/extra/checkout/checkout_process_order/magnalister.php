<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_MAGNALISTER_STATUS')
      && MODULE_MAGNALISTER_STATUS == 'True'
      )
  {
    if (function_exists('magnaExecute')) magnaExecute('magnaInsertOrderDetails', array('oID' => $insert_id), array('order_details.php'));
    if (function_exists('magnaExecute')) magnaExecute('magnaInventoryUpdate', array('action' => 'inventoryUpdateOrder'), array('inventoryUpdate.php'));
  }
