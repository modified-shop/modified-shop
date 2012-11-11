<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   xtcModified - community made shopping
   http://www.xtc-modified.org

   Copyright (c) 2009 - 2012 xtcModified
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(general.php,v 1.225 2003/05/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_parse_category_path.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

 // Parse and secure the cPath parameter values
  function xtc_parse_category_path($cPath) {

    // make sure the category IDs are integers
    $cPath_array = array_map('intval', explode('_', $cPath));

    // make sure no duplicate category IDs exist which could lock the server in a loop
	return array_unique($cPath_array);
  }
?>