<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


  function formatter_date($pattern1, $pattern2, $time = '') {
    static $formatter;
  
    if (function_exists('datefmt_create')) {
      if (!isset($formatter)) {
        $formatter = datefmt_create(DATE_LOCALE, IntlDateFormatter::MEDIUM, IntlDateFormatter::MEDIUM);
      }
  
      $formatter->setPattern($pattern1);
      $dateTime = date_create(date('Y-m-d H:i:s', $time), timezone_open(DEFAULT_TIMEZONE));
  
      $date = $formatter->format($dateTime);
    } else {
      $date = date($pattern2, $time);
    }
  
    return decode_utf8($date);
  }