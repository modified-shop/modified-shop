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

  // The marking of a guarantee document, written after the content manager saved the row. The
  // pull down is only rendered while the module runs, so a stored marking survives an inactive
  // module: without the field there is nothing to write here.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && isset($type, $subaction, $content_file_name)
      && $type == 'products'
      && isset($_POST['content_type'])
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_terms.inc.php');

    // the row exists either way, the insert branch above puts its id into coID
    $guarantee_labels_content_id = ($subaction == 'update') ? (int)$coID : (int)$_GET['coID'];
    $guarantee_labels_type = ($_POST['content_type'] == 'garan_terms') ? 'garan_terms' : '';

    if ($guarantee_labels_type === 'garan_terms') {
      $guarantee_labels_errors = guarantee_labels_check_terms($product,
                                                              $content_language_id,
                                                              $content_file_name,
                                                              $content_link,
                                                              $guarantee_labels_content_id,
                                                              $group_ids);

      // an attachment that cannot serve as guarantee conditions is kept as a normal one
      if (count($guarantee_labels_errors) > 0) {
        $guarantee_labels_type = '';

        foreach ($guarantee_labels_errors as $guarantee_labels_error) {
          $messageStack->add_session($guarantee_labels_error, 'error');
        }
      }

      unset($guarantee_labels_errors);
    }

    xtc_db_query("UPDATE ".TABLE_PRODUCTS_CONTENT."
                     SET content_type = '".xtc_db_input($guarantee_labels_type)."'
                   WHERE content_id = '".$guarantee_labels_content_id."'");

    unset($guarantee_labels_content_id, $guarantee_labels_type);
  }
