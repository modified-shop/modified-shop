<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce; www.oscommerce.com
   (c) 2003  nextcommerce; www.nextcommerce.org
   (c) 2006      xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ----------------------------------------------------------------------------------------- */

  define('XAJAX_SUPPORT_VERSION', 'XAjax Support v1.3.0 - 08.2010' );
  define('IMDXAJAX_MODULE_INCLUDES', DIR_WS_INCLUDES.'xajax_imd' );

  require_once('includes/xajax_core/xajax.inc.php');
  if (defined('SID') && xtc_not_null(SID)) {
    $sid = '?'.SID;
  }

  $imdxajax = new xajax("xajax.server.php".$sid);
  define( XAJAX_SUPPORT_VERSION_XAJAX, $imdxajax->getVersion() );

  if( $handle=opendir (IMDXAJAX_MODULE_INCLUDES) ) {
    while (false !== ($file = readdir ($handle))) {
      if( strpos($file, '.xajax.common.inc.php')!==false ) {
        include( IMDXAJAX_MODULE_INCLUDES.'/'.$file);
      }
    }
    closedir($handle);
  }
?>