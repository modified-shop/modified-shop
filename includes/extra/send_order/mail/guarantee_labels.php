<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // A damaged archive does not stop the confirmation, but the shop owner has to learn about it.
  // The storefront checkout has nobody to tell, so only a resend from the administration reports.
  // A payment callback boots the storefront and sets $send_by_admin as well, and there the
  // administration language file is never loaded. Without its constant this hook would fatal in
  // exactly the case it exists to report, after the mail has already gone out.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && isset($send_by_admin)
      && $send_by_admin == true
      && isset($messageStack)
      && is_object($messageStack)
      && defined('ERROR_GUARANTEE_LABELS_ORDER_ARCHIVE')
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

    // the mail is sent either way, so this is a warning and not an error
    foreach (guarantee_labels_snapshot_failures() as $guarantee_labels_failure) {
      $messageStack->add_session(sprintf(ERROR_GUARANTEE_LABELS_ORDER_ARCHIVE, encode_htmlspecialchars($guarantee_labels_failure)), 'warning');
    }
  }
