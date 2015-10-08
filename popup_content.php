<?php
/* -----------------------------------------------------------------------------------------
   $Id: popup_content.php 4484 2013-02-18 14:04:52Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
  -----------------------------------------------------------------------------------------
   based on:
   (c) 2003 nextcommerce (content_preview.php,v 1.2 2003/08/25); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

$content_data = $main->getContentData((int)$_GET['coID'], '', '', (isset($_GET['preview']) ? true : false));

$popup_smarty = new Smarty;

$popup_smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');
$popup_smarty->assign('html_params', HTML_PARAMS);
$popup_smarty->assign('charset', $_SESSION['language_charset']);
$popup_smarty->assign('title', htmlspecialchars($content_data['content_heading'], ENT_QUOTES, strtoupper($_SESSION['language_charset'])));
if (DIR_WS_BASE == '') {
  $popup_smarty->assign('base', (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG);
}
$popup_smarty->assign('content_heading', $content_data['content_heading']);
$popup_smarty->assign('content_text', $content_data['content_text']);

$popup_smarty->display(CURRENT_TEMPLATE.'/module/popup_content.html');
?>