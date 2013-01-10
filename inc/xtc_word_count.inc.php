<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org] 
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_word_count.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // Get the number of times a word/character is present in a string
  function xtc_word_count($string, $needle) {
    $temp_array = explode($needle, $string); // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3

    return sizeof($temp_array);
  }
?>