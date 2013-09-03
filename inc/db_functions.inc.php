<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (!function_exists('encode_htmlspecialchars')) {
    require_once (DIR_FS_INC.'html_encoding.php'); //new function for PHP5.4
  }


  function xtc_db_output($string) {
    return encode_htmlspecialchars($string);
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


  function xtc_db_perform($table, $data, $action='insert', $parameters='', $link='db_link') {
    global $$link;
    
    reset($data);

    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
         $value = (is_float($value) && defined('PHP4_3_10') && PHP4_3_10 === true) ? sprintf("%.F",$value) : (string)($value);
        switch ($value) {
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
         $value = (is_float($value) && defined('PHP4_3_10') && PHP4_3_10 === true) ? sprintf("%.F",$value) : (string)($value);
        switch ($value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns . ' = null, ';
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
?>