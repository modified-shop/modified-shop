<?php
/* --------------------------------------------------------------
   $Id: database.php 4255 2013-01-11 16:04:14Z web28 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.22 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (database.php,v 1.6 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  // File will be deleted...
  die('Deprecated File: '.basename(__FILE__).'. Use db_functions_mysql(i) instead.');
/*
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  
  function xtc_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

    if (USE_PCONNECT == 'true') {
      $$link = mysql_pconnect($server, $username, $password);
    } else {
      $$link = mysql_connect($server, $username, $password);
    }

    if ($$link) mysql_select_db($database);

    return $$link;
  }

  // db connection for Servicedatabase  
  function service_xtc_db_connect($server_service = SERVICE_DB_SERVER, $username_service = SERVICE_DB_SERVER_USERNAME, $password_service = SERVICE_DB_SERVER_PASSWORD, $database_service = SERVICE_DB_DATABASE, $link_service = 'db_link_service') {
    global $$link_service;

    if (SERVICE_USE_PCONNECT == 'true') {
      $$link_service = mysql_pconnect($server_service, $username_service, $password_service);
    } else {
      $$link_service = mysql_connect($server_service, $username_service, $password_service);
    }

    if ($$link_service) mysql_select_db($database_service);

    return $$link_service;
  }

  function xtc_db_close($link = 'db_link') {
    global $$link;

    return mysql_close($$link);
  }

  // db connection for Servicedatabase  
  function service_xtc_db_close($link_service = 'db_link_service') {
    global $$link_service;

    return mysql_close($$link_service);
  }


  function xtc_db_error($query, $errno, $error) { 
    die('<font color="#000000"><strong>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></strong></font>');
  }

  function xtc_db_query($query, $link = 'db_link') {
    global $$link, $logger;

    if (STORE_DB_TRANSACTIONS == 'true') {
      if (!is_object($logger)) $logger = new logger;
      $logger->write($query, 'QUERY');
    }

    $result = mysql_query($query, $$link) or xtc_db_error($query, mysql_errno(), mysql_error());

    if (STORE_DB_TRANSACTIONS == 'true') {
      if (mysql_error()) $logger->write(mysql_error(), 'ERROR');
    }

    return $result;
  }

  // db connection for Servicedatabase 
  function service_xtc_db_query($query, $link_service = 'db_link_service') {
    global $$link_service, $logger_service;

    if (STORE_DB_TRANSACTIONS == 'true') {
      if (!is_object($logger_service)) $logger_service = new logger_service;
      $logger_service->write($query, 'QUERY');
    }

    $result = mysql_query($query, $$link_service) or xtc_db_error($query, mysql_errno(), mysql_error());

    if (STORE_DB_TRANSACTIONS == 'true') {
      if (mysql_error()) $logger_service->write(mysql_error(), 'ERROR');
    }

    return $result;
  }
  
  function xtc_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {
    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . xtc_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . xtc_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return xtc_db_query($query, $link);
  }

  function xtc_db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function xtc_db_result($result, $row, $field = '') {
    return mysql_result($result, $row, $field);
  }

  function xtc_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function xtc_db_data_seek($db_query, $row_number) {
    return mysql_data_seek($db_query, $row_number);
  }

  function xtc_db_insert_id() {
    return mysql_insert_id();
  }

  function xtc_db_free_result($db_query) {
    return mysql_free_result($db_query);
  }

  function xtc_db_fetch_fields($db_query) {
    return mysql_fetch_field($db_query);
  }

  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
  }

  function xtc_db_input($string) {
    return addslashes($string);
  }

  function xtc_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(stripslashes($string));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = xtc_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }
*/
?>