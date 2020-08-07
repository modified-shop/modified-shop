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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

global $_MagnaSession;

/* idealo ist ein Clon von guenstiger. */
$_Marketplace = 'idealo';

/* ... allerdings mit eigener CheckinSubmit Klasse */
$_CheckinSubmitClass = DIR_MAGNALISTER_MODULES.'idealo/classes/IdealoCheckinSubmit.php';

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array(
    'SEARCHENGINE' => $_Marketplace,
    'MARKETPLACEID' => $_MagnaSession['mpID']
));

// Check if checkout token is working otherwise he can not leave config if checkout is enabled
$checkoutEnabled = getDBConfigValue('idealo.checkout.status', $_MagnaSession['mpID'], array('val' => false));
if ($checkoutEnabled['val'] === true) {
    try {
        $aResponse = MagnaConnector::gi()->submitRequest(array(
            'SUBSYSTEM' => 'ComparisonShopping',
            'ACTION' => 'IsAuthed',
        ));
    } catch (MagnaException $ex) {
        $aResponse = array();
    }
    $blDirectBuy = array_key_exists('STATUS', $aResponse) && $aResponse['STATUS'] === 'SUCCESS';
    if (!$blDirectBuy) {
        $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'][] = 'idealo.checkout.test';
    }
}

require_once(DIR_MAGNALISTER_MODULES.'guenstiger.php');