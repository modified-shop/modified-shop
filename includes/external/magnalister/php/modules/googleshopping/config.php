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

require(DIR_MAGNALISTER_MODULES.'magnacompatible/config.php');

global $_MagnaSession;

$mpconfig['auth']['authkeys'] = array('merchantid');

$mpconfig['pages']['conf']['class'] = 'GoogleshoppingConfigure';
$mpconfig['pages']['prepare']['class'] = 'GoogleshoppingPrepare';

$mpconfig['checkin']['Variations'] = getDBConfigValue(array($_MagnaSession['currentPlatform'].'.usevariations', 'val'), $_MagnaSession['mpID'], true) ? 'yes' : 'no';
$mpconfig['checkin']['Categories']['Marketplace'] = 'no';

if (!function_exists('magnaGoogleShoppingUpdateSupportedLanguages')) {
    /**
     * On Target Country change event
     *
     * @param $args
     * @return string
     */
    function magnaGoogleShoppingUpdateSupportedLanguages($args)
    {
        global $magnaConfig;
        $languages = $magnaConfig['googleshopping']['languages'];
        $ret = '';
        if (array_key_exists($args['value'], $languages)) {
            foreach ($languages[$args['value']] as $val) {
                $ret .= '<option value="' . $val['code'] . '">' . $val['title'] . " ({$val['code']}) " . '</option>';
            }
            if ($args['value'] !== 'UA') {
                $ret .= '<option value="' . $languages['GB'][0]['code'] . '">' . $languages['GB'][0]['title'] . " ({$languages['GB'][0]['code']}) " . '</option>';
            }
        } else {
            $ret = 'FAILURE';
        }
        return $ret;
    }
}
