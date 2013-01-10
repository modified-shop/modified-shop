<?php
  /* --------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(object_info.php,v 1.5 2002/01/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (object_info.php,v 1.5 2003/08/18); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  class objectInfo {

    // class constructor
    function objectInfo($object_array) {
      reset($object_array);
      while (list($key, $value) = each($object_array)) {
        $this->$key = xtc_db_prepare_input($value);
      }
    }
  }
?>