<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_remove_non_numeric.inc.php 829 2005-03-12 21:34:16Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   by Mario Zanier for XTcommerce
   
   based on:
   (c) 2003	 nextcommerce (xtc_remove_non_numeric.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
//BOF - Hetfield - 2009.08.18 - Update function for PHP 5.3
function xtc_remove_non_numeric($var) {	  
	  $var=preg_replace('![^0-9]!', '', $var);
	  return $var;
}
//EOF - Hetfield - 2009.08.18 - Update function for PHP 5.3
?>