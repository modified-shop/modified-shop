<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function next_scheduled_time($regularity, $unit, $offset) {
    if ($regularity == 0) {
      $regularity = 2;
    }
  
    $curHour = date('H', time());
    $curMin = date('i', time());
    $next_time = 9999999999;

    if ($unit == 'm') {
      $off = date('i', $offset);

      // If it's now just pretend it ain't,
      if ($off == $curMin) {
        $next_time = time() + $regularity;
      } else {
        // Make sure that the offset is always in the past.
        $off = $off > $curMin ? $off - 60 : $off;

        while ($off <= $curMin) {
          $off += $regularity;
        }
      
        $next_time = time() + 60 * ($off - $curMin);
      }
    } else {
      $next_time = mktime(date('H', $offset), date('i', $offset), 0, date('m'), date('d'), date('Y'));

      // Make the time offset in the past!
      if ($next_time > time()) {
        $next_time -= 86400;
      }

      $applyOffset = 3600;
      if ($unit == 'd') {
        $applyOffset = 86400;
      } elseif ($unit == 'w' || $unit == 'o') {
        $applyOffset = 604800;
      }
      $applyOffset *= $regularity;

      while ($next_time <= time()) {
        $next_time += $applyOffset;
      }
    }

    return $next_time;
  }
