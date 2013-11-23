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
   (c) 2003	 nextcommerce (xtc_not_null.inc.php,v 1.3 2003/08/13 23:38:05); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   new release:
   (c) 2013 Hacker Solutions - www.hackersolutions.com/modified/xtc_not_null_vs_empty

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function xtc_not_null($value) {
    if ($value == '' || $value == 'NULL' || (is_array($value) ? empty($value) : trim($value) == '')) {
      return false;
    }
    return true;
  }
 ?>