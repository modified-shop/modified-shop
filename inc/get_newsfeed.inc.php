<?php
/* -----------------------------------------------------------------------------------------
   $Id: get_external_content.inc.php 4202 2013-01-10 20:27:44Z Tomcraft1980 $

   modified eCommerce Shopsoftware - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified eCommerce Shopsoftware
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  
  require_once(DIR_FS_INC.'get_database_version.inc.php');
  require_once(DIR_FS_INC.'get_external_content.inc.php');
  
  function get_newsfeed() {
    // newsfeed
    if (time() - NEWSFEED_LAST_UPDATE > 86400) {
      $db_version = get_database_version();
      $feed = get_external_content('http://www.modified-shop.org/feed/?v='.$db_version['plain'], 2);    
      if ($feed && class_exists('SimpleXmlElement')) {
        $rss = new SimpleXmlElement($feed, LIBXML_NOCDATA);
        $rss->addAttribute('encoding', 'UTF-8');
        for ($i=0; $i<=9; $i++) {
          xtc_db_query("REPLACE INTO newsfeed (news_title, 
                                               news_text, 
                                               news_link, 
                                               news_date) 
                                       VALUES ('".xtc_db_input(decode_htmlentities(trim(decode_utf8($rss->channel->item[$i]->title))))."', 
                                               '".xtc_db_input(decode_htmlentities(trim(decode_utf8($rss->channel->item[$i]->description))))."',
                                               '".xtc_db_input(decode_utf8($rss->channel->item[$i]->link))."',
                                               '".xtc_db_input(strtotime($rss->channel->item[$i]->pubDate))."')");
        }
      }
      xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '".time()."' WHERE configuration_key = 'NEWSFEED_LAST_UPDATE'");
    }
  }
?>