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

  // the order editing shows the label of a position, it needs the same styles and the overlay
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    echo '<link rel="stylesheet" href="'.DIR_WS_CATALOG.'images/guarantee_labels/guarantee_labels.css">'.PHP_EOL;
  }
