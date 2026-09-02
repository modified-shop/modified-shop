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

  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && isset($_POST['subaction'])
      && $_POST['subaction'] == 'guarantee'
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');
    require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');

    $guarantee_oID = (int)$_POST['oID'];
    $guarantee_opID = (int)$_POST['opID'];
    $guarantee_errors = array();

    // A failing cache write does not stop anything, but it must not stay in the log alone.
    // Collected here and reported at the end, next to the result of the save.
    $guarantee_warnings = array();

    $guarantee_before = guarantee_labels_order_snapshots($guarantee_oID);
    $guarantee_before = isset($guarantee_before[$guarantee_opID]) ? $guarantee_before[$guarantee_opID] : false;

    $guarantee_input = array(
      'manufacturers_name' => xtc_db_prepare_input($_POST['manufacturers_name']),
      'manufacturers_model' => xtc_db_prepare_input($_POST['manufacturers_model']),
      'garan_duration' => xtc_db_prepare_input($_POST['garan_duration']),
    );

    // the article of the position, never the one the request claims
    $guarantee_pID = guarantee_labels_order_position_product($guarantee_oID, $guarantee_opID);
    $guarantee_from_product = false;

    // an explicit action, never an automatic synchronisation with the catalogue
    if (isset($_POST['guarantee_from_product'])) {
      $guarantee_product = ($guarantee_pID < 1) ? false : guarantee_labels_snapshot_product($guarantee_pID);
      $guarantee_names = ($guarantee_product === false) ? array() : guarantee_labels_manufacturer_names(array($guarantee_product['manufacturers_id']));
      $guarantee_label = ($guarantee_product === false) ? false : guarantee_labels_product_label($guarantee_product, $guarantee_names);

      if ($guarantee_label === false) {
        $guarantee_errors[] = ERROR_GUARANTEE_LABELS_SNAPSHOT_NO_PRODUCT;
      } else {
        $guarantee_from_product = true;
        $guarantee_input = array(
          'manufacturers_name' => $guarantee_label['manufacturer'],
          'manufacturers_model' => $guarantee_product['products_manufacturers_model'],
          'garan_duration' => $guarantee_product['products_garan_duration'],
        );
      }
    }

    // a confirmation is demanded once the customer has been notified about the order
    $guarantee_sent_query = xtc_db_query("SELECT orders_status_history_id
                                            FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                           WHERE orders_id = '".$guarantee_oID."'
                                             AND customer_notified = '1'");

    if (xtc_db_num_rows($guarantee_sent_query) > 0 && !isset($_POST['confirm'])) {
      $guarantee_errors[] = ERROR_GUARANTEE_LABELS_SNAPSHOT_CONFIRM;
    }

    if (count($guarantee_errors) < 1) {
      $guarantee_checked = guarantee_labels_validate_snapshot($guarantee_input);
      $guarantee_errors = $guarantee_checked['errors'];
    }

    // Asked before anything is archived, with the same condition guarantee_labels_write_snapshot()
    // refuses: a removal stays allowed, a save does not. Checking only there would leave the
    // uploaded document in the archive without a row pointing at it, once per attempt.
    if (count($guarantee_errors) < 1
        && $guarantee_checked['values'] !== false
        && !guarantee_labels_order_position_goods($guarantee_oID, $guarantee_opID)
        )
    {
      $guarantee_errors[] = ERROR_GUARANTEE_LABELS_SNAPSHOT_VIRTUAL;
    }

    // the document is archived first, a failure there must not leave a half written snapshot
    if (count($guarantee_errors) < 1 && $guarantee_checked['values'] !== false) {
      $guarantee_terms = isset($_POST['terms_action']) ? $_POST['terms_action'] : 'keep';

      // taking the article data means all of it, otherwise the position would carry current
      // core values next to the guarantee conditions of an older stand
      if ($guarantee_from_product === true) {
        $guarantee_terms = 'keep';

        // the group of the order, not of the administration: it decides whether the customer may
        // see the document at all
        $guarantee_status_query = xtc_db_query("SELECT customers_status
                                                  FROM ".TABLE_ORDERS."
                                                 WHERE orders_id = '".$guarantee_oID."'");
        $guarantee_status = (xtc_db_num_rows($guarantee_status_query) > 0) ? xtc_db_fetch_array($guarantee_status_query) : array('customers_status' => 0);

        // whatever the label rendering left behind belongs to the shop owner, not to the
        // document that is about to be archived
        $guarantee_warnings = array_merge($guarantee_warnings, guarantee_labels_snapshot_failures());

        $guarantee_snapshot_terms = guarantee_labels_terms_snapshot($guarantee_pID,
                                                                   guarantee_labels_order_language_id($guarantee_oID),
                                                                   $guarantee_status['customers_status']);

        // The article may simply have no document, that is no error. A failed archive or a
        // refused visibility is one, and then the existing snapshot has to stay untouched.
        $guarantee_terms_failures = guarantee_labels_snapshot_failures();

        if (count($guarantee_terms_failures) > 0) {
          foreach ($guarantee_terms_failures as $guarantee_terms_failure) {
            $guarantee_errors[] = sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_FAILED, encode_htmlspecialchars($guarantee_terms_failure));
          }
        } else {
          $guarantee_checked['values']['terms_hash'] = ($guarantee_snapshot_terms === false) ? null : $guarantee_snapshot_terms['hash'];
          $guarantee_checked['values']['terms_filename'] = ($guarantee_snapshot_terms === false) ? null : $guarantee_snapshot_terms['filename'];
        }
      }

      if ($guarantee_terms == 'remove') {
        $guarantee_checked['values']['terms_hash'] = null;
        $guarantee_checked['values']['terms_filename'] = null;
      } elseif ($guarantee_terms == 'replace') {
        $guarantee_file = guarantee_labels_archive_upload('terms_file');

        if ($guarantee_file === false) {
          $guarantee_errors[] = sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_TERMS, isset($_FILES['terms_file']['name']) ? encode_htmlspecialchars($_FILES['terms_file']['name']) : '');
        } else {
          $guarantee_checked['values']['terms_hash'] = $guarantee_file['hash'];
          $guarantee_checked['values']['terms_filename'] = $guarantee_file['filename'];
        }
      }
    }

    if (count($guarantee_errors) < 1) {
      $guarantee_errors = guarantee_labels_write_snapshot($guarantee_oID, $guarantee_opID, $guarantee_checked['values']);
    }

    $guarantee_warnings = array_merge($guarantee_warnings, guarantee_labels_snapshot_failures());

    foreach ($guarantee_warnings as $guarantee_warning) {
      $messageStack->add_session(sprintf(ERROR_GUARANTEE_LABELS_SNAPSHOT_FAILED, encode_htmlspecialchars($guarantee_warning)), 'warning');
    }

    if (count($guarantee_errors) > 0) {
      foreach ($guarantee_errors as $guarantee_error) {
        $messageStack->add_session($guarantee_error, 'error');
      }

      // the redirect would lose what was typed, so it travels with the session
      $_SESSION['guarantee_labels_post'] = array('opID' => $guarantee_opID, 'values' => $guarantee_input);
    } else {
      guarantee_labels_snapshot_history($guarantee_oID, $guarantee_opID, $guarantee_before, $guarantee_checked['values']);

      $messageStack->add_session(($guarantee_checked['values'] === false)
                                 ? TEXT_GUARANTEE_LABELS_SNAPSHOT_REMOVED
                                 : TEXT_GUARANTEE_LABELS_SNAPSHOT_SAVED, 'success');
    }

    xtc_redirect(xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action='.((count($guarantee_errors) > 0) ? 'custom&subaction=guarantee&pID='.$guarantee_pID.'&opID='.$guarantee_opID : 'products').'&oID='.$guarantee_oID));
  }
