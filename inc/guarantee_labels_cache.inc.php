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
   * Empties the shop cache after a data change that alters a rendered label.
   *
   * The block caches of the shop do not know the state of the GARAN data: a renamed
   * manufacturer or a changed duration would stay visible until CACHE_LIFETIME runs out. A
   * shop owner does not recognise that as a cache matter, which is why these changes clear it
   * themselves. A change of the module configuration does not: for that the shop brings the
   * delcache action in admin/configuration.php.
   *
   * The same effect as that action. It runs once per request, so a multi edit or an import
   * does not empty the cache again for every row.
   *
   * @return bool whether this call did the work
   */
  function guarantee_labels_clear_cache() {
    static $cleared = false;

    if ($cleared === true) {
      return false;
    }

    $cleared = true;

    global $modified_cache;

    clear_dir(DIR_FS_CATALOG.'cache/');

    // the admin usually carries the object already, only then is the bootstrap needed
    if (!is_object($modified_cache)) {
      require_once(DIR_FS_CATALOG.'includes/modified_cache.php');
    }

    if (is_object($modified_cache)) {
      $modified_cache->clear();
    }

    return true;
  }
