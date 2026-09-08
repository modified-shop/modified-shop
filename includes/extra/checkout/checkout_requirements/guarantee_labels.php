<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // This extension point runs on every checkout page and on the payment callbacks, the notice
  // belongs on the order confirmation alone. The callbacks bring no smarty object at all.
  // the module status as well: these files ship with the module, the configuration does not
  if (isset($smarty)
      && basename($PHP_SELF) === FILENAME_CHECKOUT_CONFIRMATION
      && defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    // Always assigned, so a template can place the notice without asking whether the module
    // is installed. GUARANTEE_NOTICE is the complete block, title and body are the same
    // content for a template that builds its own box around it.
    $guarantee_labels_parts = guarantee_labels_notice_parts($_SESSION['cart']->get_content_type());

    $smarty->assign('GUARANTEE_NOTICE', guarantee_labels_notice_wrap($guarantee_labels_parts));
    $smarty->assign('GUARANTEE_NOTICE_TITLE', ($guarantee_labels_parts === false) ? '' : $guarantee_labels_parts['title']);
    $smarty->assign('GUARANTEE_NOTICE_BODY', ($guarantee_labels_parts === false) ? '' : $guarantee_labels_parts['body']);

    unset($guarantee_labels_parts);
  }
