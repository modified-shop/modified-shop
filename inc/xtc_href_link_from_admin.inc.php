<?php
/*-----------------------------------------------------------------------
   $Id: xtc_href_link_from_admin.inc.php 2539 2011-12-20 15:31:37Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_href_link.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_href_link.inc.php)

   Released under the GNU General Public License

   xtC-SEO-Module by www.ShopStat.com (Hartmut König)
   http://www.shopstat.com - info@shopstat.com
   (c) 2004 ShopStat.com - All Rights Reserved.
   ---------------------------------------------------------------------------------------*/

  function xtc_href_link_from_admin($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = false, $search_engine_safe = true) {    
    return xtc_href_link($page, $parameters, $connection, $add_session, $search_engine_safe, true, true);
  }
?>