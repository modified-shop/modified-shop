<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

if (defined('MODULE_JANOLAW_STATUS') && MODULE_JANOLAW_STATUS == 'True') {
  $check_array = array('datasecurity' => MODULE_JANOLAW_PDF_DATASECURITY,
                       'terms' => MODULE_JANOLAW_PDF_TERMS,
                       'legaldetails' => MODULE_JANOLAW_PDF_LEGALDETAILS,
                       'revocation' => MODULE_JANOLAW_PDF_REVOCATION,
                       'withdrawal' => MODULE_JANOLAW_PDF_WITHDRAWAL
                       );
  foreach ($check_array as $key => $value) {
    if ($value == 'True') {
      $filename = DIR_FS_CATALOG.'media/content/'.$order->info['language'].'_'.$key.'.pdf';    
      if (is_file($filename)) {
        if ($email_attachments != '') {
          $email_attachments .= ',';
        }
        $email_attachments .= $filename;      
      }
    }
  }
}
?>