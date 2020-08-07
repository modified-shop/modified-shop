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

global $_MagnaSession;

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
defined('MAGNA_DEV_PRODUCTLIST') OR define('MAGNA_DEV_PRODUCTLIST', true);

require_once('magnacompatible.php');

class GoogleshoppingMarketplace extends MagnaCompatMarketplace {

    const AUTH_SUCCESS = 'SUCCESS';

    const AUTH_FAILED = 'FAILED';

    protected function verifyAuth() {
        try {

            $r = MagnaConnector::gi()->submitRequest(array (
                'ACTION' => 'IsAuthed',
            ));

            if ($r['STATUS'] === self::AUTH_SUCCESS) {
                $auth = array (
                    'state' => true,
                    'expire' => time() + 60 * 30,
                );
            } elseif ($r['STATUS'] === self::AUTH_FAILED) {
                $auth = array (
                    'state' => false,
                    'expire' => time(),
                );
            }
            setDBConfigValue($this->marketplace.'.authed', $this->mpID, $auth, true);

            return $r['STATUS'] === self::AUTH_SUCCESS;
        } catch (MagnaException $e) {
            setDBConfigValue($this->marketplace.'.autherror', $this->mpID, $e->getErrorArray(), false);
            $this->resources['query']['mode'] = 'conf';
            return false;
        }
    }

    /**
     * Decorates core router which relies on global page configuration.
     * If page is injected using $_modules global an extra tab is rendered and we do not want that.
     * Only for ajax requests it loads proper action and the rest is delegated to the core as usual
     */
    public function prepareAvailablePages() {
        if ($this->isShipping()) {
            return;
        }

        parent::prepareAvailablePages();
    }

    protected function determineView() {
        if ($this->isShipping()) {
            $this->pages['conf'] = array (
                'resource' => 'shipping',
                'class' => 'GoogleshoppingShipping',
            );
        } else {
            parent::determineView();
        }
    }

    private function isShipping() {
        return 'shipping' === isset($_GET['mode']) && $_GET['mode'] && $this->isAjax;
    }
}

new GoogleshoppingMarketplace($_MagnaSession['currentPlatform']);
