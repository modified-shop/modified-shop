<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * guarantee_labels_check_terms()
   *
   * Checks whether an article attachment can serve as the guarantee conditions.
   *
   * The attachment administration accepts more than the mail path can carry, so the marking is
   * only granted to an attachment that really works: one per article and language, a file that
   * exists locally, and a name the attachment list of a mail can hold.
   *
   * @param int $products_id
   * @param int $languages_id
   * @param string $content_file the stored file name
   * @param string $content_link an external address instead of a file
   * @param int $content_id the row being updated, zero for a new one
   * @return array the reasons the marking cannot be granted
   */
  function guarantee_labels_check_terms($products_id, $languages_id, $content_file, $content_link, $content_id = 0, $group_ids = null) {
    require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');

    $errors = array();
    $content_file = trim((string)$content_file);

    // a link is no durable medium, the mail has to carry the document itself
    if ($content_file === '') {
      $errors[] = ERROR_GUARANTEE_LABELS_TERMS_LINK;
    } elseif (guarantee_labels_terms_filename($content_file) === false) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_TERMS_NAME, encode_htmlspecialchars($content_file));
    } elseif (!is_file(DIR_FS_CATALOG.'media/products/'.$content_file)) {
      $errors[] = sprintf(ERROR_GUARANTEE_LABELS_TERMS_FILE, encode_htmlspecialchars($content_file));
    }

    // one per article and language, a unique index cannot express that
    $existing_query = xtc_db_query("SELECT content_id
                                      FROM ".TABLE_PRODUCTS_CONTENT."
                                     WHERE products_id = '".(int)$products_id."'
                                       AND languages_id = '".(int)$languages_id."'
                                       AND content_type = 'garan_terms'
                                       AND content_id != '".(int)$content_id."'");

    if (xtc_db_num_rows($existing_query) > 0) {
      $errors[] = ERROR_GUARANTEE_LABELS_TERMS_DUPLICATE;
    }

    // The document has to reach every group the label is shown to. A restricted attachment would
    // put a label in front of customers who can never see the conditions behind it.
    if ($group_ids !== null) {
      $missing = guarantee_labels_terms_missing_groups($group_ids);

      if (count($missing) > 0) {
        $errors[] = sprintf(ERROR_GUARANTEE_LABELS_TERMS_GROUPS, encode_htmlspecialchars(implode(', ', $missing)));
      }
    }

    return $errors;
  }

  /**
   * The B2C groups a restricted attachment would keep out. An empty selection reaches everyone,
   * which is why it produces no finding.
   *
   * @param mixed $group_ids the posted selection of the attachment administration
   * @return array names of the groups that would not reach the document
   */
  function guarantee_labels_terms_missing_groups($group_ids) {
    require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');

    // the attachment administration stores the selection as "c_<id>_group,"
    $selected = array();

    if (preg_match_all('/c_([0-9]+)_group/', (string)$group_ids, $matches)) {
      $selected = array_map('intval', $matches[1]);
    }

    // no restriction at all, the attachment is visible to every group
    if (count($selected) < 1) {
      return array();
    }

    $missing = array();

    $groups_query = xtc_db_query("SELECT customers_status_id, customers_status_name
                                    FROM ".TABLE_CUSTOMERS_STATUS."
                                   WHERE language_id = '".(int)$_SESSION['languages_id']."'");

    while ($group = xtc_db_fetch_array($groups_query)) {
      $id = (int)$group['customers_status_id'];

      // only the groups the label is shown to matter, a b2b group sees neither
      if (guarantee_labels_active($id) && !in_array($id, $selected, true)) {
        $missing[] = $group['customers_status_name'];
      }
    }

    return $missing;
  }
