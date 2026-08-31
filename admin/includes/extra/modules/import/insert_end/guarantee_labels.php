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

  // This point runs per imported row. Clearing the cache is limited to one call per request,
  // so a file with thousands of articles empties it once and not once per line.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && $this->FileSheme['p_garan_duration'] == 'Y'
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_cache.inc.php');

    guarantee_labels_clear_cache();
  }
