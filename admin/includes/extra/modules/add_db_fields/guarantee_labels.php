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

  // the column itself comes with the install schema, the database update and the module
  // installation, this only carries the posted value into the product save
  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      )
  {
    $add_products_fields[] = 'products_garan_duration';
  }
