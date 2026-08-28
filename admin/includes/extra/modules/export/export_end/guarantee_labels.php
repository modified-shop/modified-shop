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

  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      )
  {
    // NULL leaves the field empty instead of exporting a zero duration
    $line .= $this->encode(($export_data['products_garan_duration'] === null) ? '' : $export_data['products_garan_duration']);
  }
