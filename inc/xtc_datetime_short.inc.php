<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
 
 
  function xtc_datetime_short($raw_datetime) {
    if (($raw_datetime == '0000-00-00 00:00:00') || empty($raw_datetime)) {
      return false;
    }
    $year = (int) substr($raw_datetime, 0, 4);
    $month = (int) substr($raw_datetime, 5, 2);
    $day = (int) substr($raw_datetime, 8, 2);
    $hour = (int) substr($raw_datetime, 11, 2);
    $minute = (int) substr($raw_datetime, 14, 2);
    $second = (int) substr($raw_datetime, 17, 2);

    return date(DATE_TIME_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
  }
