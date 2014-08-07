<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id: viewchangelog.php 4337 2014-08-06 12:09:45Z tim.neumann $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

$chlog = file_get_contents(DIR_MAGNALISTER_FS.'ChangeLog');

$chlog = str_replace(array("\r\n", "\r"), "\n", $chlog);
$chlog = fixHTMLUTF8Entities($chlog);
$header = substr($chlog, 0, strpos($chlog, '*/')+2);
//echo print_m($header);
$chlog = substr($chlog, strpos($chlog, '*/')+2);
$chlog = preg_replace('/(=+)\s(.*)\s(=+)/e', "'<h'.strlen('\\1').'>'.'$2'.'</h'.strlen('\\1').'>'", $chlog);
$chlog = preg_replace('/\*\s(.*)/', '<ul><li>$1</li></ul>', $chlog);
$chlog = preg_replace("/<\/li><\/ul>(\s*)<ul><li>/s", "</li>$1<li>", $chlog);

echo ($chlog);

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();