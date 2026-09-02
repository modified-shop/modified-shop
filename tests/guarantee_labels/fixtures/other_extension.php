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
   * A second, unrelated class extension of the type categories.
   *
   * The guarantee label module rebuilds MODULE_CATEGORIES_INSTALLED from the modules that are
   * actually installed. This one is here so the tests can show that installing or removing the
   * module leaves other extensions of a shop alone. It does nothing else.
   */
  class other_extension {

    var $sort_order = 100;

    function check() {
      // the same way every module answers this: by its configuration key
      $check_query = xtc_db_query("SELECT configuration_value
                                     FROM ".TABLE_CONFIGURATION."
                                    WHERE configuration_key = 'MODULE_CATEGORIES_OTHER_EXTENSION_STATUS'");

      return xtc_db_num_rows($check_query);
    }

    function keys() {
      return array('MODULE_CATEGORIES_OTHER_EXTENSION_STATUS');
    }
  }
