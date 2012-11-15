<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (xtc_add_tax.inc.php,v 1.4 2003/08/24); www.nextcommerce.org 
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
function xtc_add_tax($price, $tax) 
	{ 
	  $price=$price+$price/100*$tax;
	  return $price;
	  }
 ?>