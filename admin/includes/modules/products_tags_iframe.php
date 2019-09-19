<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!defined('NEW_TAGS_IFRAME_FILENAME')) {
  define ('NEW_TAGS_IFRAME_FILENAME','products_tags.php');
}

if (!defined('USE_ATTRIBUTES_IFRAME')) {
  define ('USE_ATTRIBUTES_IFRAME','true');
}

if (is_file(DIR_WS_MODULES.'iframe_box.php')) {
  include_once(DIR_WS_MODULES.'iframe_box.php');
}

if (defined('USE_ATTRIBUTES_IFRAME') && USE_ATTRIBUTES_IFRAME == 'true') {

  function tags_iframe_link($pID, $icon=false)
  {
    global $icon_padding;
    $sid = SID ? '&'. SID : '';
    if ($icon) {
      $link = '<a href="javascript:iframeBox_show('. $pID .', \''.TEXT_PRODUCTS_TAGS.'\' , \''.NEW_TAGS_IFRAME_FILENAME.'\',\''.$sid.'\');">' . xtc_image(DIR_WS_ICONS . 'icon_edit_tags.gif', TEXT_PRODUCTS_TAGS,'', '', $icon_padding). '</a>';
    } else {
      $link = '<a href="javascript:iframeBox_show('. $pID .', \''.TEXT_PRODUCTS_TAGS.'\' , \''.NEW_TAGS_IFRAME_FILENAME.'\',\''.$sid.'\');" class="button">'. TEXT_PRODUCTS_TAGS.'</a>';
    }
    return $link;
  }

}