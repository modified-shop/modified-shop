<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_download.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   by Mario Zanier <webmaster@zanier.at>
   based on:
   (c) 2003	 nextcommerce (xtc_get_download.inc.php,v 1.4 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // safe download function, file get renamed bevor sending to browser!!
  function xtc_get_download($content_id) {
  
    $content_query=xtc_db_query("SELECT content_file,
                                        content_read
                                   FROM ".TABLE_PRODUCTS_CONTENT."
                                  WHERE content_id='".(int)$content_id."'");

    if (xtc_db_num_rows($content_query) > 0) {
          
      $content_data=xtc_db_fetch_array($content_query);

      // update file counter	
      xtc_db_query("UPDATE ".TABLE_PRODUCTS_CONTENT." 
                       SET content_read='".($content_data['content_read']+1)."'
                     WHERE content_id='".(int)$content_id."'");
  
      // original filename
      $filename = DIR_FS_CATALOG.'media/products/'.$content_data['content_file'];
      $backup_filename = DIR_FS_CATALOG.'media/products/backup/'.$content_data['content_file'];

      // create md5 hash id from original file
      $orign_hash_id = md5_file($filename);
  
      clearstatcache();
  
      // create new filename with timestamp
      $timestamp = str_replace('.','',microtime());
      $timestamp = str_replace(' ','',$timestamp);
      $new_filename = DIR_FS_CATALOG.'media/products/'.$timestamp.strstr($content_data['content_file'],'.');
        
      // rename file
      @rename($filename, $new_filename);
  
      if (file_exists($new_filename)) {
    
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=".$new_filename);
        @readfile($new_filename);	
    
        // rename file to original name
        @rename($new_filename, $filename);
        $new_hash_id = md5_file($filename);
    
        clearstatcache();	
  
        // check hash id of file again, if not same, get backup!
        if ($new_hash_id != $orign_hash_id) {
          @copy($backup_filename,$filename);
        }
      }
    }
  }
?>