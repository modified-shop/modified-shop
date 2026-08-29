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

  // the position leaves the order, its snapshot would stay behind as an orphan
  guarantee_labels_product_snapshot_delete($data_array['opID']);
