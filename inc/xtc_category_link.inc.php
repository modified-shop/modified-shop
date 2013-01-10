<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2005 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_category_link($cID,$cName='') {
//-- SHOPSTAT --//
/*
		$cName = xtc_cleanName($cName);
		$link = 'cat=c'.$cID.'_'.$cName.'.html';
		return $link;
*/
    require_once(DIR_FS_INC . 'xtc_get_category_path.inc.php');
    return 'cPath='.xtc_get_category_path($cID);
//-- SHOPSTAT --//
}
?>