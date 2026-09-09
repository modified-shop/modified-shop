<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // The module status first: these files ship with the module, the configuration does not.
  // A shop that never installed it must not load module code on every request.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    // The notice belongs to physical goods, so an order of downloads only keeps no snapshot. The
    // language of the order decides which graphic and which texts are held.
    if (guarantee_labels_physical($order->content_type)) {
      guarantee_labels_notice_snapshot($insert_id, $_SESSION['language']);
    }
  }
