<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // always assigned, so a template can place {$GUARANTEE_LABEL} without asking whether the
  // module is installed
  $guarantee_labels_markup = '';

  // The module status first: these files ship with the module, the configuration does not.
  // The template variable is assigned either way, only the module code stays unloaded.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    if (guarantee_labels_active()) {
      $guarantee_labels_names = guarantee_labels_collect_manufacturers(array($product->data));
      $guarantee_labels_markup = guarantee_labels_markup(guarantee_labels_product_label($product->data, $guarantee_labels_names));
    }
  }

  $info_smarty->assign('GUARANTEE_LABEL', $guarantee_labels_markup);
