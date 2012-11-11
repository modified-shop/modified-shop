<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   by Mario Zanier for XTcommerce
   
   based on:
   (c) 2003	 nextcommerce (xtc_recalculate_price.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_recalculate_price($price, $discount) 
	{	  
	
	  $price=-100*$price/($discount-100)/100*$discount;
	  return $price;
      
	  }
 ?>