<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2005 XT-Commerce


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_manufacturer_link($mID,$mName='') {
//-- SHOPSTAT --//
/*
		$mName = xtc_cleanName($mName);
		$link = 'manu=m'.$mID.'_'.$mName.'.html';
		return $link;
*/
		return 'manufacturers_id='.$mID;
//-- SHOPSTAT --//	
}
?>