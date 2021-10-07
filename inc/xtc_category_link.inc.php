<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_INC . 'xtc_get_category_path.inc.php');

  function xtc_category_link($cID, $name = '') {
    $params = 'cPath='.xtc_get_category_path($cID);
    if (SEARCH_ENGINE_FRIENDLY_URLS == 'true' && $name != '') {
      $params .= '&name='.$name;
    }

    return $params;
  }
