<?php
/* --------------------------------------------------------------

  modified eCommerce Shopsoftware
  http://www.modified.org-shop

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
  --------------------------------------------------------------
  based on:
  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
  (c) 2002-2003 osCommerce (configure.php,v 1.13 2003/02/10); www.oscommerce.com
  (c) 2003 XT-Commerce (configure.php)

  Released under the GNU General Public License
  --------------------------------------------------------------*/

defined('RUN_MODE_ADMIN') or define('RUN_MODE_ADMIN', true);

// compatibility for modified eCommerce Shopsoftware 1.06 files
$config_path = realpath(dirname(__FILE__) . '/../../') . '/';

if (file_exists($config_path.'includes/local/configure.php')) {
 include($config_path.'includes/local/configure.php');
} else {
 require($config_path.'includes/configure.php');
}
?>