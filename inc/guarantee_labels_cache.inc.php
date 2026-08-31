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
   * guarantee_labels_clear_cache()
   *
   * Empties the graphic cache of the module after a data change behind a label.
   *
   * Only cache/guarantee_labels/ is touched. The shop cache as a whole stays untouched: it
   * belongs to the shop owner, and emptying it on every article save would throw away the work
   * of every other module as well. For that the shop has the delcache action in
   * admin/configuration.php.
   *
   * clear_dir() keeps the directory itself and its protection files and removes the hash
   * directories below it. The renderer creates what it needs again on the next request.
   *
   * Runs once per request, so a multi edit or an import does not empty it again for every row.
   *
   * @return bool whether this call did the work
   */
  function guarantee_labels_clear_cache() {
    static $cleared = false;

    if ($cleared === true) {
      return false;
    }

    $cleared = true;

    clear_dir(DIR_FS_CATALOG.'cache/guarantee_labels/');

    return true;
  }
