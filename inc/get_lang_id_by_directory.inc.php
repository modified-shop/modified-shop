<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2003 XT-Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function get_lang_id_by_directory($directory) 
{
  $directory = preg_replace('/[^0-9a-zA-Z_-]/','',$directory);
  $db_query = xtc_db_query("SELECT languages_id
                              FROM ". TABLE_LANGUAGES ."
                             WHERE directory = '". $directory ."'
                           ");
  $db_array = xtc_db_fetch_array($db_query);
  
  return $db_array['languages_id'];
}