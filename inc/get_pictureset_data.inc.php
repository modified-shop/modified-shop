<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function get_pictureset_data($dataset) {
    $picture_sets_array = array();
    $picture_sets = preg_split("/[:,]/", preg_replace("'[\r\n\s]+'", '', $dataset)); 
    for ($i=0, $n=count($picture_sets); $i<$n; $i+=2) {
      $picture_sets_array[] = array(
        'SIZE' => $picture_sets[$i],
        'IMAGE' => $picture_sets[$i +1].'_images',
      );
    }
    
    return $picture_sets_array;
  }