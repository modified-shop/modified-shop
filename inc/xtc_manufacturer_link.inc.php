<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_manufacturer_link.inc.php 899 2005-04-29 02:40:57Z hhgag $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

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