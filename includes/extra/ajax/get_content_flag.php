<?php
/**
 * $Id$
 *
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 *
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 *
 * Released under the GNU General Public License
 */

if (isset($_REQUEST['speed'])) {
  require_once (DIR_FS_INC.'auto_include.inc.php');

  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');
  
  require_once (DIR_WS_INCLUDES.'database_tables.php');
}

function get_content_flag() {

  xtc_db_connect() or die('Unable to connect to database server!');

  $file_flag = (int)$_GET['file_flag'];
  $language = (int)$_GET['language'];
  $content_group = (int)$_GET['content_group'];
                  
  $query = xtc_db_query("SELECT content_id, 
                                content_title
                           FROM ".TABLE_CONTENT_MANAGER."
                          WHERE file_flag = '".$file_flag."'
                            AND parent_id = '0'
                            AND content_group != '".$content_group."'
                            AND languages_id = '".$language."'");

  $content = array ();
  if (xtc_db_num_rows($query)) {
    while ($content_values = xtc_db_fetch_array($query)) {
      $content[] = array(
        'id' => $content_values['content_id'],
        'name' => (DB_SERVER_CHARSET == 'utf8'
        ? $content_values['content_title']
        : iconv("ISO-8859-1", "UTF-8", $content_values['content_title']))
      );
    }
  }

  return $content;
}
?>