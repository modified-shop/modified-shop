<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

  // the inline svg of the label needs the Inter weights the official templates address
  if (guarantee_labels_active()) {
    echo '<link rel="stylesheet" href="'.DIR_WS_CATALOG.'images/guarantee_labels/guarantee_labels.css">'.PHP_EOL;
    echo '<script src="'.DIR_WS_CATALOG.'images/guarantee_labels/guarantee_labels.js" defer></script>'.PHP_EOL;
  }
