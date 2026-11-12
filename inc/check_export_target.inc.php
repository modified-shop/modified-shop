<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // a link or directory under the export name would move the write out of the export directory
  function check_export_target($path) {
    return !is_link($path) && (!file_exists($path) || is_file($path));
  }
