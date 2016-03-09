<?php
/* -----------------------------------------------------------------------------------------
   $Id: new_products.php 1571 2010-12-10 12:38:05Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2016 [www.modified-shop.org]

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require_once (DIR_FS_INC.'xtc_customer_greeting.inc.php');

$default_smarty->assign('text', str_replace('{$greeting}', xtc_customer_greeting(), $shop_content_data['content_text']));

?>