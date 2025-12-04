<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  include ('includes/application_top.php');
  
  header('content-type: text/plain;');
  
  if (is_file(DIR_FS_CATALOG.'robots.txt')) {
    readfile(DIR_FS_CATALOG.'robots.txt');
    echo chr(10);
  }

  foreach(auto_include(DIR_FS_CATALOG.'includes/extra/robots/','php') as $file) require_once ($file);