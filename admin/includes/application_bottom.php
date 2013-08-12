<?php
  /* --------------------------------------------------------------
   $Id: application_bottom.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_bottom.php,v 1.8 2002/03/15); www.oscommerce.com 
   (c) 2003	 nextcommerce (application_bottom.php,v 1.6 2003/08/1); www.nextcommerce.org
   (c) 2006 xt:Commerce (application_bottom.php 1314 2005-10-20); www.xt-commerce.de

   Released under the GNU General Public License 
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  if (STORE_PAGE_PARSE_TIME == 'true') {
    if (!is_object($logger))
      $logger = new logger;
    echo $logger->timer_stop(DISPLAY_PAGE_PARSE_TIME);
  }
?>