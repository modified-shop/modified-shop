<?php

/* -----------------------------------------------------------------------------------------
   $Id: xtc_product_link.inc.php 779 2005-02-19 17:19:28Z novalis $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 XT-Commerce


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function xtc_product_link($pID, $name='') {
//-- SHOPSTAT --//
/*
	$pName = xtc_cleanName($name);
	$link = 'info=p'.$pID.'_'.$pName.'.html';
	return $link;
*/
//-- SHOPSTAT --//
	return 'products_id='.$pID;
}
?>