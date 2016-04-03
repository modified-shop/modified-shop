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
  
  require_once(DIR_FS_EXTERNAL.'janolaw/janolaw.php');
  $janolaw = new janolaw_content();
  
  $check_array = array('datasecurity' => MODULE_JANOLAW_MAIL_DATASECURITY,
                       'terms' => MODULE_JANOLAW_MAIL_TERMS,
                       'legaldetails' => MODULE_JANOLAW_MAIL_LEGALDETAILS,
                       'revocation' => MODULE_JANOLAW_MAIL_REVOCATION,
                       'withdrawal' => MODULE_JANOLAW_MAIL_WITHDRAWAL
                       );
  foreach ($check_array as $key => $value) {
    if ($value == 'True') {
      $language = $janolaw->get_language($_SESSION['language_code']);
      
      $filename = DIR_FS_CATALOG.'media/content/'. $janolaw->document_name[strtoupper($language)][$key] . '.pdf';
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