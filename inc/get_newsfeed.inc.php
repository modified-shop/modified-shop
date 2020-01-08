<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  require_once(DIR_FS_INC.'get_database_version.inc.php');
  require_once(DIR_FS_CATALOG.'includes/classes/modified_api.php');
  
  function get_newsfeed() {
    $time = time();

    if (!defined('NEWSFEED_LAST_UPDATE_TRY')) {
      xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('NEWSFEED_LAST_UPDATE_TRY', '0', '6', '1', now())");
    }
    
    if (($time - (int)NEWSFEED_LAST_UPDATE > 86400
         && $time - (int)NEWSFEED_LAST_UPDATE_TRY > 3600
         ) || ((int)NEWSFEED_LAST_UPDATE_TRY != (int)NEWSFEED_LAST_UPDATE
               && $time - (int)NEWSFEED_LAST_UPDATE_TRY > 3600
               )
        )
    {
      $db_version = get_database_version();
      
      modified_api::reset();
      $response = modified_api::request('modified/news/'.$db_version['plain']);

      if ($response != null && is_array($response) && isset($response['channel'])) {
        $feed = $response['channel'];
        
        if (isset($feed['item'])
            && count($feed['item']) > 0
            )
        {
          foreach ($feed['item'] as $item) {
            xtc_db_query("INSERT INTO newsfeed (news_title, 
                                                news_text, 
                                                news_link, 
                                                news_date)
                                        VALUES ('".xtc_db_input(decode_htmlentities(trim(decode_utf8($item['title']))))."', 
                                                '".xtc_db_input(decode_htmlentities(trim(decode_utf8($item['description']))))."', 
                                                '".xtc_db_input(decode_utf8($item['link']))."', 
                                                '".xtc_db_input(strtotime($item['pubDate']))."')
                        ON DUPLICATE KEY UPDATE news_title = '".xtc_db_input(decode_htmlentities(trim(decode_utf8($item['title']))))."', 
                                                news_text = '".xtc_db_input(decode_htmlentities(trim(decode_utf8($item['description']))))."', 
                                                news_date = '".xtc_db_input(strtotime($item['pubDate']))."'");
          }

          xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".$time."' WHERE configuration_key = 'NEWSFEED_LAST_UPDATE'");
        }
      }
      
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".$time."' WHERE configuration_key = 'NEWSFEED_LAST_UPDATE_TRY'");
    }
  }
?>