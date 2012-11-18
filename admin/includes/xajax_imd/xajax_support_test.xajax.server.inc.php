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
   
// this function generates any test output   
function xajax_support_test_get_servertime($clienttime) {
  
    $ctime_text = $clienttime;
    $stime_text = date('D d. M H:i:s');

    $ret.= PROJECT_VERSION.' '.XAJAX_SUPPORT_VERSION."\n";
    $ret.= "\n";
    $ret.= "XAjax in Backend \n";
    $ret.= "XAjax-support is working. You can remove this test now and install your \n";
    $ret.= "own xajax-features. \n";
    $ret.= "\n";
    $ret.= "server time: $stime_text\n";
    $ret.= "client time: $ctime_text\n";
    $ret.= "\n";
    $ret.= "Technical support:\n";
    $ret.= "http://www.modified-shop.org/wiki/\n";
    $ret.= "http://www.modified-shop.org/forum/\n";
  
    $objResponse = new xajaxResponse();
    $objResponse->alert( $ret );
    return $objResponse;
}



  
?>