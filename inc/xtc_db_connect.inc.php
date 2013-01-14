<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_connect.inc.php 1248 2005-09-27 10:27:23Z gwinger $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_connect.inc.php,v 1.3 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
 //  include(DIR_WS_CLASSES.'/adodb/adodb.inc.php');
  function xtc_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (USE_PCONNECT == 'true') {
        $$link = mysql_pconnect($server, $username, $password);
    } else {
        $$link = mysql_connect($server, $username, $password);
    }

// BOF - Dokuman - 2009-09-02 - Disable "STRICT" mode for MySQL 5!
    $vers = @mysql_get_server_info();
// BOF - vr - 2010-01-01 - Disable "STRICT" mode for MySQL 5!
//    if(substr($vers,0,1) > 4) @mysql_query("SET SESSION sql_mode='MYSQL40'");
    if(substr($vers, 0, 1) > 4) @mysql_query("SET SESSION sql_mode=''");
// EOF - vr - 2010-01-01 - Disable "STRICT" mode for MySQL 5!
// EOF - Dokuman - 2009-09-02 - Disable "STRICT" mode for MySQL 5!

// BOF - Dokuman - 2009-05-27 - revised database connection
//    if ($$link) mysql_select_db($database);
  	if ($$link) {
    		@mysql_select_db($database) or die('Database not available');
   	}
// EOF - Dokuman - 2009-05-27 - revised database connection

    return $$link;
  }
?>