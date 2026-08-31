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
  function guarantee_labels_check_terms($products_id, $languages_id, $content_file, $content_link, $content_id = 0) {
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

    return $errors;
  }
