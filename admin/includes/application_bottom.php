<?php
/* --------------------------------------------------------------
   $Id: application_bottom.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(application_bottom.php,v 1.8 2002/03/15); www.oscommerce.com 
   (c) 2003	 nextcommerce (application_bottom.php,v 1.6 2003/08/1); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  if (STORE_PAGE_PARSE_TIME == 'true') {
    if (!is_object($logger)) $logger = new logger;
    echo $logger->timer_stop(DISPLAY_PAGE_PARSE_TIME);
  }
?>