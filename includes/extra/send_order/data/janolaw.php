<?php
/* -----------------------------------------------------------------------------------------
   $Id$

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

  // the attachments follow the language of the order mail, not the session that sends it
  $janolaw_lang_code = $_SESSION['language_code'];
  $janolaw_lang_query = xtc_db_query("SELECT code
                                        FROM ".TABLE_LANGUAGES."
                                       WHERE languages_id = '".(int)$order->info['languages_id']."'
                                          OR directory = '".xtc_db_input($order->info['language'])."'
                                    ORDER BY (languages_id = '".(int)$order->info['languages_id']."') DESC,
                                             languages_id
                                       LIMIT 1");
  if (xtc_db_num_rows($janolaw_lang_query) > 0) {
    $janolaw_lang = xtc_db_fetch_array($janolaw_lang_query);
    $janolaw_lang_code = $janolaw_lang['code'];
  }

  foreach ($check_array as $key => $value) {
    if ($value == 'True') {
      $language = $janolaw->get_language($janolaw_lang_code);
      
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