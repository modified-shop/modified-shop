<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_connect_installer.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_connect_installer.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  function xtc_db_connect_installer($server, $username, $password, $link = 'db_link') {
    global $$link, $db_error;

    $db_error = false;

    if (!$server) {
      $db_error = 'No Server selected.';
      return false;
    }

    $$link = @mysql_connect($server, $username, $password) or $db_error = mysql_error();

// BOF - Dokuman - 2009-09-02 - Disable "STRICT" mode for MySQL 5!
    $vers = @mysql_get_server_info();
    if(substr($vers,0,1) > 4) @mysql_query("SET SESSION sql_mode='MYSQL40'");
// EOF - Dokuman - 2009-09-02 - Disable "STRICT" mode for MySQL 5!

    return $$link;
  }
 ?>