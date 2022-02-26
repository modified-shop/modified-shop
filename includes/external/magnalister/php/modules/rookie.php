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
include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

if (isset($_POST['Tariff'])) {
    try {
        $result = MagnaConnector::gi()->submitRequest(array(
            'SUBSYSTEM' => 'Core',
            'ACTION' => 'BookTariffUpgrade',
            'DATA' => array('Tariff' => $_POST['Tariff'])
        ));
        if ($result['STATUS'] === 'SUCCESS') {
            // clean maranon cache - pulls new getShopInfo
            loadMaranonCacheConfig(true);

            echo '
            <div class="successBox">Die Buchung des Tarifs war erfolgreich.</div>
            ';
            include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
            include_once(DIR_WS_INCLUDES . 'application_bottom.php');
            #redirect start page
            echo '<meta http-equiv="refresh" content="5; url=magnalister.php" />';
            exit();
        } else {
            echo '
            <div class="errorBox">Die Buchung hat leider nicht geklappt, bitte wenden Sie sich an den Support.</div>
            ';
        }
    } catch (MagnaException $e) {
        echo 'This didn\'t work, please contact our support';
        echo print_m($e);
    }
}

try {
    $result = MagnaConnector::gi()->submitRequest(array(
        'SUBSYSTEM' => 'Core',
        'ACTION' => 'GetRookieTariffInfo',
        'V2RESPONSE' => true,
    ));
    $result = $result['DATA'];
    echo $result;
} catch (MagnaException $e) {
    echo print_m($e);
}


include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();