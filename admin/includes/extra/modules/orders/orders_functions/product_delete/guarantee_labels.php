<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

  // The position leaves the order, its snapshot would stay behind as an orphan. Both values come
  // from the request, so the order travels with it and the delete is as narrow as the core one.
  guarantee_labels_product_snapshot_delete($oID, $data_array['opID']);
