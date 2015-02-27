<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_sqlSafeString.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   by Mario Zanier for neXTCommerce
   
   based on:
   (c) 2003	 nextcommerce (xtc_sqlSafeString.inc.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  die('Deprecated File: '.basename(__FILE__).'. Use xtc_db_input() instead.');
/*
  function xtc_sqlSafeString($param) {
    // Hier wird wg. der grossen Verbreitung auf MySQL eingegangen
	// BOF - Hetfield - 2009-08-18 - deprecated function mysql_escape_string added mysql_real_escape_string to be ready for PHP >= 5.3
    if (function_exists('mysql_real_escape_string')) {
		return (NULL === $param ? "NULL" : '"' . mysql_real_escape_string($param) . '"');
  	} elseif (function_exists('mysql_escape_string')) {
  		return (NULL === $param ? "NULL" : '"' . mysql_escape_string($param) . '"');
  	}
	// EOF - Hetfield - 2009-08-18 - deprecated function mysql_escape_string added mysql_real_escape_string to be ready for PHP >= 5.3
  }
*/
?>