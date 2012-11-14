<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003  nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ----------------------------------------------------------------------------------------- */

include('includes/application_top.php');
require('xajax.common.php');

if( $handle=opendir (IMDXAJAX_MODULE_INCLUDES) ) {
  while (false !== ($file = readdir ($handle))) {
    if( strpos($file, '.xajax.server.inc.php')!==false ) {
      include( IMDXAJAX_MODULE_INCLUDES.'/'.$file);
    }
  }
  closedir($handle);
}

$imdxajax->processRequest();
?>