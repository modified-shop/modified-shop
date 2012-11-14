<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_db_insert_id.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_db_insert_id.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  //BOF - DokuMan - 2011-05-09 - pass the connection identifier link to the mysql_insert_id() function
  /*
  function xtc_db_insert_id() {
    return mysql_insert_id();
  }
  */
  function xtc_db_insert_id($link = 'db_link') {
    global $$link;

    return mysql_insert_id($$link);
  }
  //EOF - DokuMan - 2011-05-09 - pass the connection identifier link to the mysql_insert_id() function
?>