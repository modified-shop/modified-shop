<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_filesize.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_filesize.inc.php,v 1.1 2003/08/24); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// returns human readeable filesize :)

function xtc_filesize($file) {
	$a = array("B","KB","MB","GB","TB","PB");
	
	$pos = 0;
	$size = filesize(DIR_FS_CATALOG.'media/products/'.$file);
	while ($size >= 1024) {
		$size /= 1024;
		$pos++;
	}
	return round($size,2)." ".$a[$pos];
}

?>