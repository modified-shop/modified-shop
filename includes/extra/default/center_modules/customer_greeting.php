<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2016 [www.modified-shop.org]

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once (DIR_FS_INC.'xtc_customer_greeting.inc.php');
  
  if (!empty($shop_content_data['content_text'])) {
    $shop_content_data['content_text'] = str_replace('{$greeting}', xtc_customer_greeting(), $shop_content_data['content_text']);
    $default_smarty->assign('text', $shop_content_data['content_text']);
  }
