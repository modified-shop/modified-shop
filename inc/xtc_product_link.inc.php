<?php

/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

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
	return 'products_id='.xtc_get_prid($pID);
}
?>