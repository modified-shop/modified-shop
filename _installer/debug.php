<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   Released under the GNU General Public License
   --------------------------------------------------------------*/

$support  = 'URL: ' . $_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']. '<br />';
   $support .= '$_SERVER[PHP_SELF]: ' . $_SERVER['PHP_SELF']. '<br />';
   $support .= '$_SERVER[DOCUMENT_ROOT]: ' . $_SERVER['DOCUMENT_ROOT']. '<br />';
   $support .= '$_SERVER[SCRIPT_NAME]: ' . $_SERVER['SCRIPT_NAME']. '<br />';
   $support .= '$_SERVER[SCRIPT_FILENAME]: ' . $_SERVER['SCRIPT_FILENAME']. '<br />';

echo $support;
?>