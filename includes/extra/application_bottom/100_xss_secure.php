<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2014 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  if (defined('XSS_SEND_LOG') && XSS_SEND_LOG === true) {
    $xss_files_array = glob(DIR_FS_LOG.'*.mail', GLOB_BRACE);
    if (count($xss_files_array) > 0) {
      foreach ($xss_files_array as $xss_file) {
        $mail_txt = file_get_contents($xss_file);

        xtc_php_mail(EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     EMAIL_SUPPORT_FORWARDING_STRING,
                     EMAIL_SUPPORT_ADDRESS,
                     EMAIL_SUPPORT_NAME,
                     '',
                     '',
                     'Security Alert - '.STORE_NAME,
                     nl2br($mail_txt),
                     $mail_txt
                     );
      
        unlink($xss_file);
      }
    }
  }
?>