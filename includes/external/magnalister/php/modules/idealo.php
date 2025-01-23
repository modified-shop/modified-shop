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
 * (c) 2010 - 2023 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

global $_MagnaSession;

/* idealo ist ein Clon von guenstiger. */
$_Marketplace = 'idealo';

/* ... allerdings mit eigener CheckinSubmit Klasse */
$_CheckinSubmitClass = DIR_MAGNALISTER_MODULES.'idealo/classes/IdealoCheckinSubmit.php';

/* Kein Direct Buy mehr: Bestellimport immer aus (gibt es nicht mehr) */
setDBConfigValue('idealo.import', $_MagnaSession['mpID'], 'false', true);

MagnaConnector::gi()->setSubsystem($_modules[$_Marketplace]['settings']['subsystem']);
MagnaConnector::gi()->setAddRequestsProps(array(
    'SEARCHENGINE' => $_Marketplace,
    'MARKETPLACEID' => $_MagnaSession['mpID']
));

// Check if checkout token is working otherwise he can not leave config if direct buy is enabled
$checkoutEnabled = getDBConfigValue('idealo.directbuy.active', $_MagnaSession['mpID'], 'false');
$aOldCheckoutEnabled = getDBConfigValue('idealo.checkout.status', $_MagnaSession['mpID'], array('val' => false));
$blOldCheckoutEnabled = isset($aOldCheckoutEnabled['val']) && $aOldCheckoutEnabled['val'] === true;

if ($checkoutEnabled === 'true' && (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax'))) {
    echo '
    <div class="noticeBox" style="font-size: 14px;">
        <p><span style="color: #000">Achtung: idealo beabsichtigt, den idealo Direktkauf zum 31.12.2022 einzustellen</span></p>
        <p>F&uuml;r magnalister User, die den Direktkauf nutzen, bedeutet das:</p>
        <ul>
            <li>
                <p>Produkte k&ouml;nnen ab dem 01.01.2023 nicht mehr im Direktkauf gelistet werden</p>
            </li>
            <li>
                <p>Alle bis zum 31.12.2022 eingehenden Direktkauf-Bestellungen, werden in magnalister importiert und k&ouml;nnen bis zum 31.03.2023 weiterverarbeitet werden.</p>
            </li>
            <li>
                <p>Wir werden die Direktkauf-Funktionalit&auml;ten in der magnalister idealo Schnittstelle am 31.03.2023 entfernen<br><br></p>
            </li>
        </ul>
        <p>Ihr magnalister Team</p>
    </div>
    ';
}

if (($checkoutEnabled === 'true' || $blOldCheckoutEnabled ) && !isset($_POST['conf']['idealo.directbuy.clientid'])) {
    $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'][] = 'idealo.directbuy.clientid';
    $_modules[$_MagnaSession['currentPlatform']]['requiredConfigKeys'][] = 'idealo.directbuy.password';

    $mDirectBuyClientId = getDBConfigValue('idealo.directbuy.clientid', $_MagnaSession['mpID'], empty($_POST['conf']['idealo.directbuy.clientid']) ? : $_POST['conf']['idealo.directbuy.clientid']);
    $mDirectBuyClientPassword = getDBConfigValue('idealo.directbuy.password', $_MagnaSession['mpID'], empty($_POST['conf']['idealo.directbuy.clientid'])? :$_POST['conf']['idealo.directbuy.clientid']);

    if (!empty($mDirectBuyClientId) && !empty($mDirectBuyClientPassword)) {
        try {
            $aResponse = MagnaConnector::gi()->submitRequest(array(
                'SUBSYSTEM' => 'ComparisonShopping',
                'ACTION'    => 'IsAuthed',
            ));
        } catch (MagnaException $e) {
            $e->setCriticalStatus(false);
            $_GET['mode'] = $_magnaQuery['mode'] = 'conf';
            $authError = $e->getErrorArray();
            $mpTimeOut = false;
            $errors = array();
            if (is_array($authError) && !empty($authError)
                && isset($authError['ERRORS']) && !empty($authError['ERRORS'])
            ) {
                foreach ($authError['ERRORS'] as $err) {
                    if(isset($err['ERRORMESSAGE'])) {
                        $errors[] = fixHTMLUTF8Entities($err['ERRORMESSAGE']);
                    }
                    if (isset($err['ERRORCODE']) && ($err['ERRORCODE'] == 'MARKETPLACE_TIMEOUT')) {
                        $mpTimeOut = true;
                    }
                }
            }
            if ($mpTimeOut) {
                return '<p class="errorBox">
            <span class="error bold larger">'.ML_ERROR_LABEL.':</span>
            '.ML_ERROR_MARKETPLACE_TIMEOUT.'
        </p>';
            }
            $errorMessage = '<p class="errorBox">
        <span class="error bold larger">'.ML_ERROR_LABEL.':</span>
        '.sprintf(ML_MAGNACOMPAT_ERROR_ACCESS_DENIED, 'idealo').(
                (!empty($errors))
                    ? '<br /><br />'.implode('<br />', $errors)
                    : ''
                ).'</p>';
        }
    }
}

require_once(DIR_MAGNALISTER_MODULES.'guenstiger.php');
