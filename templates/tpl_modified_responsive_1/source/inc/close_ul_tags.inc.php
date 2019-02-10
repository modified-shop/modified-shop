<?php
  /* ---------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   -------------------------------------------------------------------------------------- */   

  function close_ul_tags($level, $oldlevel) {
    $count = 1;
    $ul = '';
    while($count <= $oldlevel - $level) {
      $tab_end = '';
      for ($i = 1; $i <= $oldlevel - $count; $i++) {
        $tab_end .= "\t";
      }      
      $ul .=  $tab_end . "\t". '</ul>'. "\n". $tab_end . '</li>'. "\n";      
      $count++;
    }
    return $ul;
  }
?>