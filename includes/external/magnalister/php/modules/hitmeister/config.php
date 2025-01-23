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
 * $Id: init.php 1283 2011-09-30 22:52:17Z derpapsst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if (!isset($_MagnaSession)) {
	global $_MagnaSession;
}

require(DIR_MAGNALISTER_MODULES.'magnacompatible/config.php');

$mpconfig['auth']['authkeys'] = array('clientkey', 'secretkey');

$mpconfig['pages']['conf']['class'] = 'HitmeisterConfigure';
$mpconfig['pages']['prepare']['class'] = 'HitmeisterPrepare';

$mpconfig['checkin']['Categories']['Marketplace'] = 'no';
$mpconfig['checkin']['Variations'] = 'yes';

if (false === getDBConfigValue('hitmeister.imagepath', $_MagnaSession['mpID'], false)) {
	$form['prepare']['fields']['imagepath']['default'] = defined('DIR_WS_CATALOG_POPUP_IMAGES')
		? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
		: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
	setDBConfigValue('hitmeister.imagepath', $_MagnaSession['mpID'], $form['prepare']['fields']['imagepath']['default'], true);
}

# Status filter for matching = for apply
setDBConfigValue('hitmeister.match.status', $_MagnaSession['mpID'], getDBConfigValue('hitmeister.apply.status', $_MagnaSession['mpID'], false), true);
