<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_format_filesize.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_format_filesize.inc.php,v 1.1 2003/08/25); www.nextcommerce.org
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// returns human readeable filesize :)

function xtc_format_filesize($size) {
	$a = array("B","KB","MB","GB","TB","PB");
	
	$pos = 0;
	while ($size >= 1024) {
		$size /= 1024;
		$pos++;
	}
	return round($size,2)." ".$a[$pos];
}

?>