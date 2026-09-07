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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require(DIR_MAGNALISTER_MODULES.'magnacompatible/config.php');

$mpconfig['auth']['authkeys'] = array('oauth.token');

$mpconfig['pages']['conf']['class'] = 'TemuConfigure';
$mpconfig['pages']['prepare']['class'] = 'TemuPrepare';
$mpconfig['pages']['shippinglabel'] = array(
	'resource' => 'shippinglabel',
	'class' => 'TemuShippingLabel',
);

$mpconfig['checkin']['Categories']['Marketplace'] = 'yes';
$mpconfig['checkin']['Variations'] = 'yes';

// Only persist lazy defaults when a marketplace is selected. This file is also
// require()d by MagnaCompatibleCheckinSubmit::loadMPConfig() during the checkin
// submit, where $_MagnaSession['mpID'] can be empty — writing config with a NULL
// mpID violates the NOT NULL column and pollutes the submit output.
if (!empty($_MagnaSession['mpID'])) {
	if (!getDBConfigValue('temu.imagepath', $_MagnaSession['mpID'], false)) {
		// Product images live under the shop's popup-images URL (e.g.
		// .../images/product_images/popup_images/), NOT the shop images root.
		// Mirrors Amazon's imagepath default (amazonConfig.php).
		setDBConfigValue('temu.imagepath', $_MagnaSession['mpID'], SHOP_URL_POPUP_IMAGES, true);
	}

	if (!getDBConfigValue('temu.match.status', $_MagnaSession['mpID'], false)) {
		setDBConfigValue('temu.match.status', $_MagnaSession['mpID'], 'n', true);
	}
}
