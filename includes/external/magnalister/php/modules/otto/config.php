<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require(DIR_MAGNALISTER_MODULES.'magnacompatible/config.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');

$mpconfig['pages']['conf']['class'] = 'OttoConfigure';
$mpconfig['pages']['prepare']['class'] = 'OttoPrepare';
$mpconfig['pages']['errorlog']['class'] = 'OttoErrorLog';
$mpconfig['auth']['authkeys'] = array('username', 'password');

// Save default return tracking key data for gambio versions >= 4.5
mlPresetTrackingAndReturnTrackingCodeMatching($this->mpID, 'otto.orders.tracking.key.DBMatching');
mlPresetTrackingAndReturnTrackingCodeMatching($this->mpID, 'otto.orders.return.tracking.key.DBMatching');
