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
   * get_image_manipulator_file()
   *
   * the setting names a file in admin/includes/classes/ and becomes an include path,
   * so only a plain file name counts, anything else falls back to the shop's own file
   *
   * @return string
   */
  function get_image_manipulator_file() {
    $file = (defined('FILENAME_IMAGEMANIPULATOR') ? FILENAME_IMAGEMANIPULATOR : (defined('IMAGE_MANIPULATOR') ? IMAGE_MANIPULATOR : ''));

    if (!is_string($file) || !preg_match('/^[A-Za-z0-9_]+\.php$/D', $file)) {
      $file = 'image_manipulator.php';
    }

    return $file;
  }
