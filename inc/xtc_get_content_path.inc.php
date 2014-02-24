<?php
/* -----------------------------------------------------------------------------------------
   $Id:$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_get_content_path($content_group, $content=array()) {
  $parent_content_query = "SELECT parent_id, 
                                  content_id,
                                  content_group
                             FROM ".TABLE_CONTENT_MANAGER."
                            WHERE languages_id='".(int) $_SESSION['languages_id']."'
                                  ".CONTENT_CONDITIONS."
                              AND content_status='1'
                              AND content_group='".(int)$content_group."'";
  $parent_content_query  = xtDBquery($parent_content_query);
  if (xtc_db_num_rows($parent_content_query, true) > 0) { 
    while ($parent_content = xtc_db_fetch_array($parent_content_query,true)) {
      if ($parent_content['parent_id'] == 0) break;
      $content[] = $parent_content['parent_id'];
      if ($parent_content['content_group'] != $content_group) {
        xtc_get_parent_content($parent_content['content_group'], $content);
      }
    }
  }
  return $content;
}

function xtc_get_content_id($content_group) {
  $content_group_query = xtDBquery("SELECT content_id
                                      FROM ".TABLE_CONTENT_MANAGER."
                                     WHERE content_group='".(int) $content_group."'
                                       AND languages_id='".(int) $_SESSION['languages_id']."'");
  $content_group = xtc_db_fetch_array($content_group_query, true);
  return $content_group['content_id'];
}
?>