<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // always assigned, so a template can place {$module_content.GUARANTEE_LABEL} without asking
  // whether the module is installed
  $module_content[$i]['GUARANTEE_LABEL'] = '';

  // The module status first: these files ship with the module, the configuration does not.
  // The template variable is assigned either way, only the module code stays unloaded.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    // the mapping is shared with the other cart hook, see guarantee_labels_cart_row_label()
    $module_content[$i]['GUARANTEE_LABEL'] = guarantee_labels_cart_row_label($module_content[$i]);
  }
