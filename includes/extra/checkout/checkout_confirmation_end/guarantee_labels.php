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

  // always assigned, so a template can place {$GUARANTEE_NOTICE} without asking whether the
  // module is installed
  $smarty->assign('GUARANTEE_NOTICE', guarantee_labels_notice($_SESSION['cart']->get_content_type()));
