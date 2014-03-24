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
 * $Id: viewchangelog.php 1271 2011-09-27 22:32:14Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$tools = array (
	'genSKU' => 'Liste aller Produkte und deren SKUs',
);
$_url['module'] = 'toolbox';

if (!isset($_GET['tool']) || !isset($tools[$_GET['tool']])) {
	include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	echo '<ul>';
	foreach ($tools as $key => $desc) {
		echo '<li><a href="'.toURL($_url, array('tool' => $key)).'">'.$desc.'</a></li>';
	}
	echo '</ul>';
} else {
	$_url['tool'] = $_GET['tool'];
	include_once(DIR_MAGNALISTER_MODULES.'toolbox/'.$_GET['tool'].'.php');
}

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();