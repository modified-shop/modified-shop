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
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog.php');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog/MagnaCompatibleErrorView.php');

class GoogleshoppingErrorLog extends MagnaCompatibleErrorLog {
	public function process() {
		$el = new GoogleshoppingErrorView();
		echo $el->renderView();
	}
}

class GoogleshoppingErrorView extends MagnaCompatibleErrorView {
    public function __construct($settings = array()) {
        $settings = array_merge(array(
            'hasImport' => true,
            'hasOrigin' => true,
        ), $settings);

        parent::__construct($settings);
    }
}
