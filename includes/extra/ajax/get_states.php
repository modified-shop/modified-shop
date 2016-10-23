<?php
/**
 * $Id: get_states.php 7777 2012-06-15 20:20:00Z h-h-h $
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */

if (isset($_REQUEST['speed'])) {
  // auto include
  require_once (DIR_FS_INC.'auto_include.inc.php');

  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_WS_INCLUDES.'database_tables.php');
}

function get_states() {

  xtc_db_connect() or die('Unable to connect to database server!');

  $country_id = (int)$_REQUEST['country'];

  $query = xtc_db_query("
      SELECT zone_id,zone_name
        FROM ".TABLE_ZONES."
       WHERE zone_country_id = '".$country_id."'
    ORDER BY zone_name");

  $zones = array ();
  if (xtc_db_num_rows($query)) {
    while ($zones_values = xtc_db_fetch_array($query)) {
      $zones[] = array(
        'id' => $zones_values['zone_id'],
        'name' => (DB_SERVER_CHARSET == 'utf8'
        ? $zones_values['zone_name']
        : iconv("ISO-8859-1", "UTF-8", $zones_values['zone_name']))
      );
    }
  }

  return $zones;
}
?>